<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ===================================================
        // 0. Roles (Spatie) — sẽ chạy sau khi cài Spatie
        // ===================================================
        $this->call([
            RolePermissionSeeder::class,
            HouseRoomSeeder::class,
            ServiceSeeder::class,
            TenantContractSeeder::class,
            InvoicePaymentSeeder::class,
            AnnouncementSeeder::class,
            MaintenanceTicketSeeder::class,
        ]);
    }
}
