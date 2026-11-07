<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class RentalReminder extends Notification
{
    public function __construct(public array $message) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return $this->message;
    }
}
