<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();

        // Quét hết hạn các yêu cầu đặt phòng đã duyệt nhưng chưa thanh toán cọc trong 48h.
        $schedule->command('bookings:expire-overdue')->hourly();

        // Đồng bộ trạng thái hóa đơn trước khi admin mở dashboard buổi sáng.
        $schedule->command('invoices:mark-overdue')->dailyAt('01:00');
        $schedule->command('rentals:send-reminders')->dailyAt('08:00')
            ->timezone(config('rentals.reminder_timezone'))->withoutOverlapping();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
