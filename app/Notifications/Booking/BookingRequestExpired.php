<?php

namespace App\Notifications\Booking;

use App\Models\BookingRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Thông báo gửi cho owner khi yêu cầu đặt phòng hết hạn giữ chỗ
 * (đã được duyệt nhưng quá 48h chưa hoàn tất đặt cọc).
 */
class BookingRequestExpired extends Notification
{
    use Queueable;

    public function __construct(public BookingRequest $bookingRequest) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $br = $this->bookingRequest;
        $roomName = optional($br->room)->name ?? ('#' . $br->room_id);

        return [
            'type'               => 'booking_request_expired',
            'icon'               => 'fas fa-hourglass-end',
            'color'              => 'text-warning',
            'booking_request_id' => $br->id,
            'title'              => 'Yêu cầu đặt phòng đã hết hạn giữ chỗ',
            'message'            => 'Yêu cầu đặt phòng ' . $roomName . ' đã hết hạn giữ chỗ do chưa hoàn tất đặt cọc.',
            'action_url'         => url('/booking/my-requests/' . $br->id),
        ];
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
