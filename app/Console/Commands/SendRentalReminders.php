<?php

namespace App\Console\Commands;

use App\Services\RentalReminderService;
use Illuminate\Console\Command;

class SendRentalReminders extends Command
{
    protected $signature = 'rentals:send-reminders';

    protected $description = 'Gửi nhắc hạn hóa đơn và hợp đồng, chống gửi trùng.';

    public function handle(RentalReminderService $reminders): int
    {
        $this->info('Đã gửi '.$reminders->send().' thông báo nhắc hạn.');

        return self::SUCCESS;
    }
}
