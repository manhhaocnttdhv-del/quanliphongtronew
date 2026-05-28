<?php

namespace App\Notifications\Booking;

use App\Models\BookingRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Thông báo gửi cho owner (khách hàng đặt phòng) khi admin duyệt yêu cầu.
 */
class BookingRequestApproved extends Notification
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
            'type'               => 'booking_request_approved',
            'icon'               => 'fas fa-check-circle',
            'color'              => 'text-success',
            'booking_request_id' => $br->id,
            'title'              => 'Yêu cầu đặt phòng đã được duyệt',
            'message'            => 'Yêu cầu đặt phòng ' . $roomName . ' của bạn đã được duyệt. Vui lòng hoàn tất hồ sơ và đặt cọc trong vòng 48 giờ.',
            'action_url'         => url('/booking/my-requests/' . $br->id),
        ];
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
