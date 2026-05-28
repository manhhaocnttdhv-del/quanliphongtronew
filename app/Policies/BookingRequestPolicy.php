<?php

namespace App\Policies;

use App\Models\BookingRequest;
use App\Models\User;

class BookingRequestPolicy
{
    /**
     * Cho phép xem chi tiết yêu cầu đặt thuê: chủ yêu cầu hoặc admin.
     */
    public function view(User $user, BookingRequest $br): bool
    {
        return $user->id === $br->user_id || $user->hasRole('admin');
    }

    /**
     * Chỉ chủ yêu cầu mới được hủy, và chỉ khi yêu cầu đang ở trạng thái pending.
     */
    public function cancel(User $user, BookingRequest $br): bool
    {
        return $user->id === $br->user_id && $br->status === 'pending';
    }

    /**
     * Chỉ chủ yêu cầu mới được upload CCCD, và chỉ khi yêu cầu đã được duyệt.
     */
    public function uploadDocuments(User $user, BookingRequest $br): bool
    {
        return $user->id === $br->user_id && $br->status === 'approved';
    }

    /**
     * Chỉ chủ yêu cầu mới được ký hợp đồng, sau khi đã upload đầy đủ giấy tờ.
     */
    public function sign(User $user, BookingRequest $br): bool
    {
        return $user->id === $br->user_id
            && $br->status === 'approved'
            && $br->hasUploadedDocuments();
    }

    /**
     * Chỉ chủ yêu cầu mới được thanh toán đặt cọc, sau khi đã ký hợp đồng.
     */
    public function pay(User $user, BookingRequest $br): bool
    {
        return $user->id === $br->user_id
            && $br->status === 'approved'
            && $br->isSigned();
    }
}
