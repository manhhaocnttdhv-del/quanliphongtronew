<?php

namespace App\Notifications;

use App\Models\MaintenanceTicket;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewMaintenanceTicketNotification extends Notification
{
    use Queueable;

    public function __construct(public MaintenanceTicket $ticket) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $priorityLabel = match ($this->ticket->priority) {
            'high'   => 'Cao',
            'medium' => 'Trung bình',
            default  => 'Thấp',
        };

        $room = $this->ticket->contract->room->name ?? 'N/A';
        $tenant = $this->ticket->contract->tenant->user->name ?? 'Khách thuê';

        return [
            'type'    => 'new_maintenance',
            'icon'    => 'fas fa-tools',
            'color'   => 'text-warning',
            'title'   => 'Báo cáo sự cố mới từ ' . $tenant,
            'message' => 'Phòng ' . $room . ' — ' . $this->ticket->title . ' — Mức độ: ' . $priorityLabel,
            'url'     => route('admin.maintenance-tickets.show', $this->ticket),
        ];
    }
}
