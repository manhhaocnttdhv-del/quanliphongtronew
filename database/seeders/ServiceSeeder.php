<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            ['name' => 'Tiền điện',   'price' => 3500,    'unit' => 'kWh',  'type' => 'electricity'],
            ['name' => 'Tiền nước',   'price' => 15000,   'unit' => 'm³',   'type' => 'water'],
            ['name' => 'Phí rác',     'price' => 20000,   'unit' => 'tháng','type' => 'fixed'],
            ['name' => 'Phí Wifi',    'price' => 100000,  'unit' => 'tháng','type' => 'fixed'],
            ['name' => 'Giữ xe máy', 'price' => 100000,  'unit' => 'xe',   'type' => 'other'],
            ['name' => 'Giữ xe đạp', 'price' => 30000,   'unit' => 'xe',   'type' => 'other'],
        ];

        foreach ($services as $s) {
            DB::table('services')->insert([
                'name'        => $s['name'],
                'price'       => $s['price'],
                'unit'        => $s['unit'],
                'type'        => $s['type'],
                'is_active'   => true,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }

        // Lấy ID để các seeder khác dùng
        $electricId = DB::table('services')->where('type', 'electricity')->value('id');
        $waterId    = DB::table('services')->where('type', 'water')->value('id');
        cache()->put('seeder_service_electric', $electricId, 300);
        cache()->put('seeder_service_water', $waterId, 300);

        // Gán dịch vụ mặc định cho tất cả phòng (điện + nước + rác + wifi)
        $rooms     = DB::table('rooms')->pluck('id');
        $fixedIds  = DB::table('services')->whereIn('type', ['fixed'])->pluck('id');
        $meterIds  = DB::table('services')->whereIn('type', ['electricity', 'water'])->pluck('id');
        $allIds    = $fixedIds->merge($meterIds);

        foreach ($rooms as $roomId) {
            foreach ($allIds as $serviceId) {
                DB::table('room_services')->insert([
                    'room_id'    => $roomId,
                    'service_id' => $serviceId,
                    'is_active'  => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $this->command->info('✅ Đã tạo 6 dịch vụ và gán cho tất cả phòng.');
    }
}
