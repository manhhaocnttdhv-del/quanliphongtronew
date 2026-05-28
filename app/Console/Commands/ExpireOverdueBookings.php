<?php

namespace App\Console\Commands;

use App\Services\Booking\BookingExpiryService;
use Illuminate\Console\Command;

/**
 * Console command: `php artisan bookings:expire-overdue`.
 *
 * Quét các yêu cầu đặt phòng đã duyệt nhưng chưa thanh toán cọc trong
 * 48 giờ và đánh dấu chúng là `expired` thông qua BookingExpiryService.
 * Được lập lịch chạy mỗi giờ trong App\Console\Kernel::schedule().
 */
class ExpireOverdueBookings extends Command
{
    /**
     * @var string
     */
    protected $signature = 'bookings:expire-overdue';

    /**
     * @var string
     */
    protected $description = 'Đánh dấu hết hạn các yêu cầu đặt phòng đã duyệt nhưng chưa thanh toán cọc trong 48h.';

    public function handle(BookingExpiryService $svc): int
    {
        $n = $svc->expireOverdue();
        $this->info("Đã đánh dấu hết hạn {$n} yêu cầu đặt phòng.");

        return self::SUCCESS;
    }
}
