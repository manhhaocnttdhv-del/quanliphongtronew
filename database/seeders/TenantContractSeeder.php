<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Carbon\Carbon;

class TenantContractSeeder extends Seeder
{
    // Danh sách họ tên Việt Nam mẫu
    private array $names = [
        'Nguyễn Văn An', 'Trần Thị Bình', 'Lê Văn Cường', 'Phạm Thị Dung',
        'Hoàng Văn Em', 'Đỗ Thị Phương', 'Vũ Văn Giang', 'Bùi Thị Hoa',
        'Đặng Văn Hùng', 'Ngô Thị Kim',
    ];

    private array $hometowns = [
        'Nghệ An', 'Thanh Hóa', 'Hà Tĩnh', 'Quảng Bình',
        'Bình Định', 'Quảng Ngãi', 'Đà Nẵng', 'Huế', 'Ninh Bình', 'Nam Định',
    ];

    public function run(): void
    {
        $tenantRole   = Role::where('name', 'tenant')->first();
        $rentedRooms  = DB::table('rooms')->where('status', 'rented')->pluck('id')->toArray();

        // Cập nhật demo tenant đầu tiên (đã tạo ở RolePermissionSeeder)
        $demoUser = User::where('email', 'tenant@demo.com')->first();

        foreach ($rentedRooms as $i => $roomId) {
            // Dùng demoUser cho room đầu tiên, còn lại tạo mới
            if ($i === 0) {
                $user = $demoUser;
            } else {
                $name  = $this->names[$i % count($this->names)];
                $email = 'tenant' . ($i + 1) . '@demo.com';
                $user  = User::firstOrCreate(
                    ['email' => $email],
                    ['name' => $name, 'password' => Hash::make('password')]
                );
                $user->assignRole($tenantRole);
            }

            // Tạo hồ sơ Tenant
            $tenantId = DB::table('tenants')->insertGetId([
                'user_id'   => $user->id,
                'cccd'      => '0' . rand(11, 99) . rand(1000000, 9999999),
                'phone'     => '09' . rand(10000000, 99999999),
                'address'   => rand(10, 999) . ' Đường số ' . rand(1, 50) . ', TP.HCM',
                'hometown'  => $this->hometowns[$i % count($this->hometowns)],
                'birthday'  => Carbon::now()->subYears(rand(19, 30))->subDays(rand(0, 365))->toDateString(),
                'gender'    => ($i % 2 === 0) ? 'male' : 'female',
                'created_at'=> now(),
                'updated_at'=> now(),
            ]);

            // Tạo Hợp đồng
            $room       = DB::table('rooms')->where('id', $roomId)->first();
            $startDate  = Carbon::now()->subMonths(rand(2, 8))->startOfMonth();
            $endDate    = $startDate->copy()->addYear();

            DB::table('contracts')->insertGetId([
                'room_id'       => $roomId,
                'tenant_id'     => $tenantId,
                'start_date'    => $startDate->toDateString(),
                'end_date'      => $endDate->toDateString(),
                'deposit'       => $room->price,
                'monthly_price' => $room->price,
                'occupants'     => rand(1, 3),
                'status'        => 'active',
                'created_at'    => $startDate,
                'updated_at'    => $startDate,
            ]);
        }

        $this->command->info('✅ Đã tạo ' . count($rentedRooms) . ' khách thuê và hợp đồng.');
    }
}
