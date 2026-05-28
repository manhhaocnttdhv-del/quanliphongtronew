<?php

namespace App\Notifications\Booking;

use App\Models\BookingRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Thông báo gửi cho owner và các admin khi khách hàng đã hoàn tất đặt cọc.
 */
class BookingDepositCompleted extends Notification
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
        $userName = optional($br->user)->name ?? 'Khách hàng';
        $roomName = optional($br->room)->name ?? ('#' . $br->room_id);

        return [
            'type'               => 'booking_deposit_completed',
            'icon'               => 'fas fa-money-check-alt',
            'color'              => 'text-success',
            'booking_request_id' => $br->id,
            'title'              => 'Đã hoàn tất đặt cọc',
            'message'            => 'Khách hàng ' . $userName . ' đã hoàn tất đặt cọc cho phòng ' . $roomName . '.',
            'action_url'         => url('/booking/my-requests/' . $br->id),
        ];
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
