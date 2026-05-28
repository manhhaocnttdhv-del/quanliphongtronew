<?php

namespace App\Services\Booking;

use App\Models\BookingRequest;
use App\Models\BookingRequestAudit;
use App\Notifications\Booking\BookingRequestExpired;
use Illuminate\Support\Facades\DB;

/**
 * Service phụ trách quét và đánh dấu hết hạn các yêu cầu đặt phòng đã được
 * admin duyệt nhưng khách hàng chưa hoàn tất thanh toán cọc trong cửa sổ
 * giữ chỗ (HOLD_HOURS = 48h kể từ thời điểm `approved_at`).
 *
 * Xem design.md mục 4.4 và requirements.md Requirement 7.
 */
class BookingExpiryService
{
    /**
     * Số giờ tối đa giữ chỗ kể từ khi duyệt cho tới khi yêu cầu phải bị hết hạn.
     */
    public const HOLD_HOURS = 48;

    /**
     * Quét và đánh dấu hết hạn các BookingRequest đã duyệt nhưng chưa thanh
     * toán cọc đúng hạn. Trả về số bản ghi bị expired trong lượt chạy này.
     *
     * Mỗi yêu cầu được xử lý trong một transaction riêng để một lỗi cục bộ
     * không làm hỏng toàn bộ batch.
     */
    public function expireOverdue(): int
    {
        $cutoff = now()->subHours(self::HOLD_HOURS);

        $candidates = BookingRequest::query()
            ->where('status', 'approved')
            ->whereNull('deposit_paid_at')
            ->where('approved_at', '<', $cutoff)
            ->with(['contract', 'room', 'user'])
            ->get();

        $count = 0;
        foreach ($candidates as $br) {
            DB::transaction(function () use ($br, &$count) {
                // 1. Booking → expired
                $br->update([
                    'status'                 => 'expired',
                    'expired_at'             => now(),
                    'last_status_changed_at' => now(),
                ]);

                // 2. Contract draft → cancelled
                if ($br->contract && $br->contract->status === 'draft') {
                    $br->contract->update(['status' => 'cancelled']);
                }

                // 3. Room → available (chỉ khi đang reserved)
                if ($br->room && $br->room->status === 'reserved') {
                    $br->room->update(['status' => 'available']);
                }

                // 4. Audit
                BookingRequestAudit::create([
                    'booking_request_id' => $br->id,
                    'event'              => 'expired',
                    'actor_user_id'      => null,
                    'ip_address'         => null,
                    'user_agent'         => null,
                    'metadata'           => ['hold_hours' => self::HOLD_HOURS],
                    'created_at'         => now(),
                ]);

                // 5. Notify owner
                if ($br->user) {
                    $br->user->notify(new BookingRequestExpired($br->fresh(['room', 'user'])));
                }

                $count++;
            });
        }

        return $count;
    }
}
