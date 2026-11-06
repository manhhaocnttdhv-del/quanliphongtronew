<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AdditionalRoomsSeeder extends Seeder
{
    public function run(): void
    {
        $houses = DB::table('houses')
            ->whereIn('name', ['Dãy trọ Bình Minh', 'Chung cư mini Ánh Dương'])
            ->get(['id', 'name'])
            ->keyBy('name');

        $roomTypes = DB::table('room_types')->pluck('id', 'name');
        $serviceIds = DB::table('services')->whereIn('name', [
            'Tiền điện',
            'Tiền nước',
            'Phí rác',
            'Phí Wifi',
        ])->pluck('id');

        $photos = [
            'https://pt123.cdn.static123.com/images/thumbs/450x300/fit/2026/03/30/phong-tro-thanh-pho-vinh-nghe-an_1774844076.jpg',
            'https://pt123.cdn.static123.com/images/thumbs/450x300/fit/2025/08/02/1_1754121247.jpg',
            'https://pt123.cdn.static123.com/images/thumbs/450x300/fit/2025/07/12/1000076287_1752333326.jpg',
            'https://pt123.cdn.static123.com/images/thumbs/450x300/fit/2025/06/04/z6671573033028-0e2e7d082e9b1ba0c7c10dc18d51fed6_1749032875.jpg',
            'https://pt123.cdn.static123.com/images/thumbs/450x300/fit/2025/03/03/z6368800725545-754ab41777ec750f290a6703e1466fa8_1740970489.jpg',
        ];

        $rooms = [
            ['house' => 'Dãy trọ Bình Minh', 'name' => 'P301', 'floor' => 3, 'price' => 2500000, 'area' => 20, 'max_occupants' => 2, 'status' => 'available', 'type' => 'Phòng đơn', 'description' => 'Phòng tầng 3 thoáng, có cửa sổ lớn và khu để xe chung.'],
            ['house' => 'Dãy trọ Bình Minh', 'name' => 'P302', 'floor' => 3, 'price' => 2500000, 'area' => 20, 'max_occupants' => 2, 'status' => 'available', 'type' => 'Phòng đơn', 'description' => 'Phòng sạch sẽ, phù hợp một đến hai người ở.'],
            ['house' => 'Dãy trọ Bình Minh', 'name' => 'P303', 'floor' => 3, 'price' => 2800000, 'area' => 24, 'max_occupants' => 2, 'status' => 'rented', 'type' => 'Phòng đơn', 'description' => 'Phòng có ban công, gần khu sinh hoạt chung.'],
            ['house' => 'Dãy trọ Bình Minh', 'name' => 'P304', 'floor' => 3, 'price' => 3000000, 'area' => 25, 'max_occupants' => 2, 'status' => 'available', 'type' => 'Phòng đôi', 'description' => 'Phòng rộng, nhiều ánh sáng tự nhiên, có chỗ nấu ăn nhỏ.'],
            ['house' => 'Dãy trọ Bình Minh', 'name' => 'P305', 'floor' => 3, 'price' => 3000000, 'area' => 25, 'max_occupants' => 2, 'status' => 'rented', 'type' => 'Phòng đôi', 'description' => 'Phòng đôi rộng rãi, an ninh và yên tĩnh.'],
            ['house' => 'Dãy trọ Bình Minh', 'name' => 'P306', 'floor' => 3, 'price' => 3200000, 'area' => 28, 'max_occupants' => 3, 'status' => 'available', 'type' => 'Phòng đôi', 'description' => 'Phòng cuối dãy, thoáng hai mặt và có khu phơi đồ.'],
            ['house' => 'Chung cư mini Ánh Dương', 'name' => 'C01', 'floor' => 3, 'price' => 4200000, 'area' => 34, 'max_occupants' => 4, 'status' => 'available', 'type' => 'Phòng đôi', 'description' => 'Căn hộ mini có nội thất cơ bản, thang máy và máy giặt chung.'],
            ['house' => 'Chung cư mini Ánh Dương', 'name' => 'C02', 'floor' => 3, 'price' => 4200000, 'area' => 34, 'max_occupants' => 4, 'status' => 'rented', 'type' => 'Phòng đôi', 'description' => 'Căn hộ mini sáng thoáng, phù hợp gia đình nhỏ.'],
            ['house' => 'Chung cư mini Ánh Dương', 'name' => 'C03', 'floor' => 3, 'price' => 4500000, 'area' => 38, 'max_occupants' => 4, 'status' => 'available', 'type' => 'Phòng đôi', 'description' => 'Phòng có khu bếp riêng và không gian sinh hoạt rộng.'],
            ['house' => 'Chung cư mini Ánh Dương', 'name' => 'C04', 'floor' => 4, 'price' => 4500000, 'area' => 38, 'max_occupants' => 4, 'status' => 'available', 'type' => 'Phòng đôi', 'description' => 'Phòng tầng cao, yên tĩnh, có ban công nhìn ra thành phố.'],
            ['house' => 'Chung cư mini Ánh Dương', 'name' => 'C05', 'floor' => 4, 'price' => 4800000, 'area' => 42, 'max_occupants' => 4, 'status' => 'rented', 'type' => 'Phòng đôi', 'description' => 'Căn hộ mini đầy đủ nội thất cơ bản.'],
            ['house' => 'Chung cư mini Ánh Dương', 'name' => 'C06', 'floor' => 4, 'price' => 5000000, 'area' => 45, 'max_occupants' => 4, 'status' => 'available', 'type' => 'Phòng đôi', 'description' => 'Căn góc rộng, có hai cửa sổ và không gian làm việc riêng.'],
        ];

        $created = 0;
        foreach ($rooms as $index => $room) {
            $houseId = $houses->get($room['house'])?->id;
            $roomTypeId = $roomTypes->get($room['type']);

            if (!$houseId || !$roomTypeId) {
                continue;
            }

            $roomId = DB::table('rooms')
                ->where('house_id', $houseId)
                ->where('name', $room['name'])
                ->value('id');

            if (!$roomId) {
                $roomId = DB::table('rooms')->insertGetId([
                    'house_id' => $houseId,
                    'room_type_id' => $roomTypeId,
                    'name' => $room['name'],
                    'floor' => $room['floor'],
                    'price' => $room['price'],
                    'area' => $room['area'],
                    'max_occupants' => $room['max_occupants'],
                    'status' => $room['status'],
                    'description' => $room['description'],
                    'images' => json_encode([$photos[$index % count($photos)], $photos[($index + 1) % count($photos)]]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $created++;
            }

            foreach ($serviceIds as $serviceId) {
                DB::table('room_services')->updateOrInsert(
                    ['room_id' => $roomId, 'service_id' => $serviceId],
                    ['is_active' => true, 'updated_at' => now(), 'created_at' => now()]
                );
            }
        }

        $this->command?->info("Đã bổ sung {$created} phòng mẫu mới.");
    }
}
