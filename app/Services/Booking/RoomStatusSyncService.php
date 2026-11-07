<?php

namespace App\Services\Booking;

use App\Models\Room;
use App\Models\Tenant;
use Illuminate\Validation\ValidationException;

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

        $hasActive = $room->contracts()->where('status', 'active')->exists();
        if ($hasActive) {
            if ($room->status !== 'rented') {
                $room->update(['status' => 'rented']);
            }
            return;
        }

        $hasDraft = $room->contracts()->where('status', 'draft')->exists();
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

    public function ensureAvailable(Room $room, ?int $exceptContractId = null): void
    {
        $contracts = $room->contracts()->whereIn('status', ['active', 'draft']);
        if ($exceptContractId) {
            $contracts->where('id', '!=', $exceptContractId);
        }
        if ((! $exceptContractId && $room->status !== 'available') || $room->status === 'maintenance' || ! $room->house?->is_active || $contracts->exists()) {
            throw ValidationException::withMessages(['room_id' => 'Phòng đang được thuê, giữ chỗ hoặc không còn khả dụng.']);
        }
    }

    public function syncTenantRole(Tenant $tenant): void
    {
        $user = $tenant->user;
        if ($user && ! $user->hasRole('admin')) {
            $hasRentalAccess = $tenant->contracts()->where(function ($query) {
                $query->whereIn('status', ['active', 'draft'])
                    ->orWhereHas('invoices', fn ($invoices) => $invoices->whereNull('voided_at')->where('debt', '>', 0))
                    ->orWhereHas('settlement');
            })->exists();
            $user->syncRoles([$hasRentalAccess ? 'tenant' : 'customer']);
        }
    }
}
