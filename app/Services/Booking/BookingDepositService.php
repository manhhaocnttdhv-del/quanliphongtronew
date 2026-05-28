<?php

namespace App\Services\Booking;

use App\Models\BookingRequest;
use App\Models\BookingRequestAudit;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use App\Notifications\Booking\BookingDepositCompleted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

/**
 * Service xử lý hóa đơn đặt cọc cho luồng booking online.
 *
 * Tham chiếu: design.md mục 4.3 và 6.
 *
 * Hai trách nhiệm chính:
 *  1. `createDepositInvoice()` — phát hành Invoice type=deposit cho một
 *     `BookingRequest` đã được duyệt (đã có `contract_id`). Idempotent
 *     theo cặp (contract_id, type=deposit) để tránh phát sinh hóa đơn cọc
 *     trùng lặp khi customer mở lại trang thanh toán nhiều lần.
 *  2. `markDepositPaid()` — gọi từ PayOS webhook / return URL khi nhận
 *     được kết quả thanh toán. Bao toàn bộ thao tác kích hoạt Contract
 *     và đổi Room sang `rented` trong một transaction. Hỗ trợ partial
 *     payment: nếu số tiền chưa đủ, chỉ ghi nhận Invoice partial mà
 *     KHÔNG kích hoạt Contract.
 */
class BookingDepositService
{
    /**
     * Tạo Invoice đặt cọc cho một BookingRequest đã duyệt.
     *
     * Idempotent: nếu đã tồn tại Invoice deposit cho `contract_id` này
     * (do customer đã mở trang deposit trước đó), trả về Invoice cũ.
     *
     * @throws \RuntimeException khi BookingRequest chưa có contract_id
     *                           (chưa được admin duyệt).
     */
    public function createDepositInvoice(BookingRequest $br): Invoice
    {
        if (! $br->contract_id) {
            throw new \RuntimeException(
                'BookingRequest chưa có contract_id; cần duyệt trước khi tạo invoice cọc.'
            );
        }

        $existing = Invoice::where('contract_id', $br->contract_id)
            ->where('type', 'deposit')
            ->first();
        if ($existing) {
            return $existing;
        }

        return Invoice::create([
            'contract_id'     => $br->contract_id,
            'type'            => 'deposit',
            'month'           => null,
            'year'            => null,
            'room_fee'        => 0,
            'electricity_fee' => 0,
            'water_fee'       => 0,
            'service_fee'     => 0,
            'total'           => $br->deposit_amount,
            'paid_amount'     => 0,
            'debt'            => $br->deposit_amount,
            'due_date'        => now()->addDays(2)->toDateString(),
            'status'          => 'unpaid',
            'notes'           => 'Hóa đơn đặt cọc cho BookingRequest #' . $br->id,
        ]);
    }

    /**
     * Đánh dấu Invoice cọc đã thanh toán (toàn phần hoặc một phần).
     *
     * - Toàn phần (paid_amount >= total) → kích hoạt Contract draft → active,
     *   đổi Room sang rented, set `deposit_paid_at`, ghi audit, notify owner
     *   + tất cả admin.
     * - Partial (paid_amount < total) → chỉ cập nhật Invoice sang status
     *   `partial`, ghi nhận Payment, không đụng Contract/Room/BookingRequest.
     *
     * Toàn bộ thao tác bọc trong `DB::transaction()` để tránh state lệch
     * giữa Invoice / Contract / Room nếu một bước fail.
     *
     * @param  Invoice  $invoice        Invoice type=deposit cần ghi nhận thanh toán.
     * @param  int      $amount         Số tiền (VNĐ) PayOS xác nhận đã nhận.
     * @param  string   $referenceCode  Mã giao dịch PayOS (orderCode hoặc transaction id).
     */
    public function markDepositPaid(Invoice $invoice, int $amount, string $referenceCode): void
    {
        // Chỉ phục vụ deposit; bảo vệ tránh gọi nhầm trên invoice monthly.
        if ($invoice->type !== 'deposit') {
            return;
        }

        DB::transaction(function () use ($invoice, $amount, $referenceCode) {
            $newPaid = (int) $invoice->paid_amount + $amount;
            $isFull  = $newPaid >= (int) $invoice->total;
            $debt    = max(0, (int) $invoice->total - $newPaid);

            // 1. Cập nhật Invoice — paid_amount cộng dồn để hỗ trợ partial.
            $invoice->update([
                'paid_amount' => $newPaid,
                'debt'        => $debt,
                'status'      => $isFull ? 'paid' : 'partial',
                'notes'       => trim(
                    ($invoice->notes ?? '')
                    . "\n[PayOS] Thanh toán: " . number_format($amount) . "đ (Mã GD: $referenceCode)"
                ),
            ]);

            // 2. Tạo Payment ghi nhận giao dịch.
            Payment::create([
                'invoice_id'     => $invoice->id,
                'amount'         => $amount,
                'method'         => 'transfer',
                'reference_code' => $referenceCode,
                'paid_at'        => now(),
                'received_by'    => 'PayOS',
                'note'           => 'Đặt cọc qua PayOS',
            ]);

            // Partial → dừng tại đây, KHÔNG kích hoạt Contract.
            if (! $isFull) {
                return;
            }

            // 3. Kích hoạt Contract: draft → active.
            $contract = $invoice->contract;
            if ($contract && $contract->status === 'draft') {
                $contract->update(['status' => 'active']);
            }

            // 4. Đổi Room sang rented (chỉ khi chưa rented để tránh ghi đè vô nghĩa).
            $room = $contract?->room;
            if ($room && $room->status !== 'rented') {
                $room->update(['status' => 'rented']);
            }

            // 5. Cập nhật BookingRequest gắn với contract này.
            $br = BookingRequest::where('contract_id', $contract?->id)->first();
            if ($br) {
                $br->update([
                    'deposit_paid_at'        => now(),
                    'last_status_changed_at' => now(),
                ]);

                // 6. Audit log — append-only.
                BookingRequestAudit::create([
                    'booking_request_id' => $br->id,
                    'event'              => 'deposit_paid',
                    'actor_user_id'      => $br->user_id,
                    'ip_address'         => request()->ip(),
                    'user_agent'         => substr((string) request()->userAgent(), 0, 255),
                    'metadata'           => [
                        'invoice_id'     => $invoice->id,
                        'amount'         => $amount,
                        'reference_code' => $referenceCode,
                    ],
                    'created_at'         => now(),
                ]);

                // 7. Notify owner + tất cả admin.
                $br->loadMissing(['user', 'room']);
                if ($br->user) {
                    $br->user->notify(new BookingDepositCompleted($br));
                }

                $admins = User::role('admin')->get();
                if ($admins->isNotEmpty()) {
                    Notification::send($admins, new BookingDepositCompleted($br));
                }
            }
        });
    }
}
