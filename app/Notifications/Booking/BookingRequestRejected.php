<?php

namespace App\Notifications\Booking;

use App\Models\BookingRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Thông báo gửi cho owner (khách hàng đặt phòng) khi admin từ chối yêu cầu.
 */
class BookingRequestRejected extends Notification
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
        $reason = $br->rejected_reason ?: 'Không có lý do cụ thể';

        return [
            'type'               => 'booking_request_rejected',
            'icon'               => 'fas fa-times-circle',
            'color'              => 'text-danger',
            'booking_request_id' => $br->id,
            'title'              => 'Yêu cầu đặt phòng đã bị từ chối',
            'message'            => 'Yêu cầu đặt phòng ' . $roomName . ' đã bị từ chối: ' . $reason,
            'action_url'         => url('/booking/my-requests/' . $br->id),
        ];
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
