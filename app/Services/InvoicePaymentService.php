<?php

namespace App\Services;

use App\Models\BookingRequest;
use App\Models\BookingRequestAudit;
use App\Models\Contract;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentOrder;
use App\Models\Room;
use App\Models\User;
use App\Notifications\Booking\BookingDepositCompleted;
use App\Services\Booking\RoomStatusSyncService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use InvalidArgumentException;

class InvoicePaymentService
{
    public function record(Invoice $invoice, int $amount, string $reference, ?PaymentOrder $order = null): void
    {
        if ($amount <= 0 || $reference === '' || strlen($reference) > 150) {
            throw new InvalidArgumentException('Invalid payment amount or reference.');
        }

        DB::transaction(function () use ($invoice, $amount, $reference, $order) {
            // All rental transitions lock the room before its contract and invoice.
            $contractId = $invoice->contract_id;
            $roomId = Contract::findOrFail($contractId)->room_id;
            $room = Room::lockForUpdate()->findOrFail($roomId);
            $contract = Contract::lockForUpdate()->findOrFail($contractId);
            $booking = BookingRequest::where('contract_id', $contractId)->lockForUpdate()->first();
            $invoice = Invoice::lockForUpdate()->findOrFail($invoice->id);
            $order = $order ? PaymentOrder::lockForUpdate()->findOrFail($order->id) : null;

            if ($order && $order->invoice_id !== $invoice->id) {
                throw new InvalidArgumentException('Payment order does not belong to invoice.');
            }

            $gatewayReference = 'payos:'.$reference;
            if (Payment::where('gateway_reference', $gatewayReference)->exists()) {
                return;
            }

            $newPaid = (int) $invoice->paid_amount + $amount;
            $canActivate = $invoice->type === 'deposit'
                && $contract->status === 'draft'
                && $contract->signed_at
                && $booking?->status === 'approved'
                && $booking->isWithinHold()
                && $room->status === 'reserved'
                && $room->house?->is_active
                && ! $room->contracts()->where('id', '!=', $contract->id)->whereIn('status', ['active', 'draft'])->exists();
            $alreadyActive = $contract->status === 'active' && (! $booking || $booking->deposit_paid_at);
            $requiresReview = $invoice->voided_at || $newPaid - (int) $invoice->refunded_amount > (int) $invoice->total
                || ($invoice->type === 'deposit' && ! $canActivate && ! $alreadyActive);

            Payment::create([
                'invoice_id' => $invoice->id,
                'payment_order_id' => $order?->id,
                'amount' => $amount,
                'method' => 'transfer',
                'reference_code' => $reference,
                'gateway_reference' => $gatewayReference,
                'requires_review' => $requiresReview,
                'paid_at' => now(),
                'received_by' => 'PayOS',
                'note' => $requiresReview ? 'Cần đối soát hoặc hoàn tiền; không tự kích hoạt hợp đồng.' : 'Thanh toán qua PayOS',
            ]);

            $invoice->fill($invoice->balanceAttributes((int) $invoice->total, $newPaid));
            $invoice->notes = trim(($invoice->notes ?? '')."\n[PayOS] ".$reference.': '.number_format($amount).'đ'
                .($requiresReview ? ' - Cần đối soát hoặc hoàn tiền.' : ''));
            $invoice->save();

            if ($order) {
                $orderPaid = (int) $order->paid_amount + $amount;
                $order->update([
                    'paid_amount' => $orderPaid,
                    'status' => $requiresReview ? 'review' : ($orderPaid >= (int) $order->amount ? 'paid' : 'pending'),
                ]);
            }

            if (! $canActivate || $newPaid < (int) $invoice->total) {
                return;
            }

            $contract->update(['status' => 'active']);
            app(RoomStatusSyncService::class)->recompute($room);
            $booking->update(['deposit_paid_at' => now(), 'last_status_changed_at' => now()]);
            BookingRequestAudit::create([
                'booking_request_id' => $booking->id,
                'event' => 'deposit_paid',
                'actor_user_id' => $booking->user_id,
                'ip_address' => request()->ip(),
                'user_agent' => substr((string) request()->userAgent(), 0, 255),
                'metadata' => ['invoice_id' => $invoice->id, 'amount' => $amount, 'reference_code' => $reference],
                'created_at' => now(),
            ]);

            $booking->load(['user', 'room']);
            $booking->user?->notify(new BookingDepositCompleted($booking));
            Notification::send(User::role('admin')->get(), new BookingDepositCompleted($booking));
        }, 3);
    }
}
