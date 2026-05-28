<?php

namespace App\Notifications\Booking;

use App\Models\BookingRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Thông báo gửi cho admin khi customer tạo yêu cầu đặt phòng mới.
 */
class BookingRequestSubmitted extends Notification
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
            'type'               => 'booking_request_submitted',
            'icon'               => 'fas fa-clipboard-list',
            'color'              => 'text-primary',
            'booking_request_id' => $br->id,
            'title'              => 'Yêu cầu đặt phòng mới',
            'message'            => 'Có yêu cầu đặt phòng mới từ ' . $userName . ' cho phòng ' . $roomName,
            'action_url'         => url('/admin/booking-requests/' . $br->id),
        ];
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
