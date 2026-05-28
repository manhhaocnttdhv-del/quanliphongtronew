<?php

namespace App\Services\Booking;

use App\Models\Room;

class RoomStatusSyncService
{
    /**
     * Tính lại trạng thái Room dựa trên các Contract liên quan.
     *
     * Quy tắc (Requirement 11):
     *  - Nếu Room có ít nhất 1 contract.status = 'active' → Room.status = 'rented'.
     *  - Nếu Room có ít nhất 1 contract.status = 'draft' → Room.status = 'reserved'.
     *  - Ngược lại → Room.status = 'available'.
     *  - Trường hợp Room hiện đang 'maintenance' do Admin đặt thủ công → giữ nguyên.
     */
    public function recompute(Room $room): void
    {
        if ($room->status === 'maintenance') {
            return;
        }

        $room->loadMissing('contracts');

        $hasActive = $room->contracts->contains(fn ($c) => $c->status === 'active');
        if ($hasActive) {
            if ($room->status !== 'rented') {
                $room->update(['status' => 'rented']);
            }
            return;
        }

        $hasDraft = $room->contracts->contains(fn ($c) => $c->status === 'draft');
        if ($hasDraft) {
            if ($room->status !== 'reserved') {
                $room->update(['status' => 'reserved']);
            }
            return;
        }

        if ($room->status !== 'available') {
            $room->update(['status' => 'available']);
        }
    }
}
