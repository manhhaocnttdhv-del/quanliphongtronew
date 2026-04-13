<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HouseRoomSeeder extends Seeder
{
    public function run(): void
    {
        // ── 2 Dãy trọ ────────────────────────────────────────
        $house1 = DB::table('houses')->insertGetId([
            'name'        => 'Dãy trọ Bình Minh',
            'address'     => '12 Nguyễn Trãi, Phường 2, Quận 5, TP.HCM',
            'phone'       => '0901234567',
            'description' => 'Phòng trọ sạch sẽ, an ninh, gần trường ĐH Sư Phạm.',
            'is_active'   => true,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        $house2 = DB::table('houses')->insertGetId([
            'name'        => 'Chung cư mini Ánh Dương',
            'address'     => '88 Lê Văn Sỹ, Phường 11, Quận 3, TP.HCM',
            'phone'       => '0912345678',
            'description' => 'Phòng full nội thất, thang máy, máy giặt chung.',
            'is_active'   => true,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        // ── 2 Loại phòng ────────────────────────────────────
        $typeSingle = DB::table('room_types')->insertGetId([
            'name'          => 'Phòng đơn',
            'default_price' => 2500000,
            'description'   => 'Phòng cho 1-2 người, khoảng 20m²',
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        $typeDouble = DB::table('room_types')->insertGetId([
            'name'          => 'Phòng đôi',
            'default_price' => 3500000,
            'description'   => 'Phòng cho 3-4 người, khoảng 30m²',
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        // ── Phòng Dãy trọ Bình Minh (12 phòng) ─────────────
        $rooms1 = [
            ['name' => 'P101', 'floor' => 1, 'price' => 2500000, 'area' => 20, 'status' => 'rented',    'type' => $typeSingle],
            ['name' => 'P102', 'floor' => 1, 'price' => 2500000, 'area' => 20, 'status' => 'rented',    'type' => $typeSingle],
            ['name' => 'P103', 'floor' => 1, 'price' => 2500000, 'area' => 20, 'status' => 'available', 'type' => $typeSingle],
            ['name' => 'P104', 'floor' => 1, 'price' => 3500000, 'area' => 30, 'status' => 'rented',    'type' => $typeDouble],
            ['name' => 'P201', 'floor' => 2, 'price' => 2500000, 'area' => 20, 'status' => 'rented',    'type' => $typeSingle],
            ['name' => 'P202', 'floor' => 2, 'price' => 2500000, 'area' => 20, 'status' => 'maintenance','type' => $typeSingle],
            ['name' => 'P203', 'floor' => 2, 'price' => 3500000, 'area' => 30, 'status' => 'rented',    'type' => $typeDouble],
            ['name' => 'P204', 'floor' => 2, 'price' => 2500000, 'area' => 20, 'status' => 'rented',    'type' => $typeSingle],
        ];

        $roomIds1 = [];
        foreach ($rooms1 as $r) {
            $roomIds1[] = DB::table('rooms')->insertGetId([
                'house_id'     => $house1,
                'room_type_id' => $r['type'],
                'name'         => $r['name'],
                'floor'        => $r['floor'],
                'price'        => $r['price'],
                'area'         => $r['area'],
                'max_occupants'=> ($r['type'] === $typeDouble) ? 4 : 2,
                'status'       => $r['status'],
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        }

        // ── Phòng Chung cư mini Ánh Dương (8 phòng) ─────────
        $rooms2 = [
            ['name' => 'A01', 'floor' => 1, 'price' => 4000000, 'area' => 35, 'status' => 'rented',    'type' => $typeDouble],
            ['name' => 'A02', 'floor' => 1, 'price' => 4000000, 'area' => 35, 'status' => 'rented',    'type' => $typeDouble],
            ['name' => 'A03', 'floor' => 1, 'price' => 4000000, 'area' => 35, 'status' => 'available', 'type' => $typeDouble],
            ['name' => 'B01', 'floor' => 2, 'price' => 4500000, 'area' => 40, 'status' => 'rented',    'type' => $typeDouble],
            ['name' => 'B02', 'floor' => 2, 'price' => 4500000, 'area' => 40, 'status' => 'rented',    'type' => $typeDouble],
            ['name' => 'B03', 'floor' => 2, 'price' => 4500000, 'area' => 40, 'status' => 'available', 'type' => $typeDouble],
        ];

        $roomIds2 = [];
        foreach ($rooms2 as $r) {
            $roomIds2[] = DB::table('rooms')->insertGetId([
                'house_id'     => $house2,
                'room_type_id' => $r['type'],
                'name'         => $r['name'],
                'floor'        => $r['floor'],
                'price'        => $r['price'],
                'area'         => $r['area'],
                'max_occupants'=> 4,
                'status'       => $r['status'],
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        }

        // Lưu room IDs vào config tạm để seeder khác dùng
        cache()->put('seeder_room_ids_house1', $roomIds1, 300);
        cache()->put('seeder_room_ids_house2', $roomIds2, 300);

        $this->command->info('✅ Đã tạo 2 dãy trọ với 14 phòng.');
    }
}
