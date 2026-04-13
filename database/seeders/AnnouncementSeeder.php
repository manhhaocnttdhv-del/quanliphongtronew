<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AnnouncementSeeder extends Seeder
{
    public function run(): void
    {
        $house1 = DB::table('houses')->where('name', 'like', '%Bình Minh%')->value('id');
        $house2 = DB::table('houses')->where('name', 'like', '%Ánh Dương%')->value('id');

        $announcements = [
            [
                'house_id'     => null, // Toàn hệ thống
                'title'        => 'Chào mừng đến với hệ thống Quản lý Phòng Trọ!',
                'content'      => 'Đây là thông báo chào mừng đến với hệ thống quản lý phòng trọ trực tuyến. Từ nay, bạn có thể xem hóa đơn, thanh toán và báo sự cố trực tiếp qua ứng dụng này.',
                'type'         => 'notice',
                'is_pinned'    => true,
                'published_at' => Carbon::now()->subDays(30),
            ],
            [
                'house_id'     => $house1,
                'title'        => '⚠️ Cúp nước ngày 15/04 từ 8h - 12h',
                'content'      => 'Công ty cấp nước thông báo sẽ cúp nước để sửa chữa đường ống chính vào thứ Ba ngày 15/04 từ 8h sáng đến 12h trưa. Bà con vui lòng trữ nước trước.',
                'type'         => 'warning',
                'is_pinned'    => true,
                'published_at' => Carbon::now()->subDays(3),
            ],
            [
                'house_id'     => $house1,
                'title'        => 'Nhắc nhở: Đóng cổng sau 22h',
                'content'      => 'Để đảm bảo an ninh, toàn bộ cư dân vui lòng đóng cổng sau 22h hàng đêm. Nếu về muộn, vui lòng liên hệ số điện thoại bảo vệ.',
                'type'         => 'notice',
                'is_pinned'    => false,
                'published_at' => Carbon::now()->subDays(15),
            ],
            [
                'house_id'     => $house2,
                'title'        => 'Lịch vệ sinh chung tháng ' . Carbon::now()->format('m'),
                'content'      => 'Vệ sinh hành lang, khu vực chung sẽ được thực hiện vào thứ Bảy cuối tháng này. Đề nghị toàn bộ cư dân phối hợp dọn dẹp đồ vật trước cửa phòng.',
                'type'         => 'event',
                'is_pinned'    => false,
                'published_at' => Carbon::now()->subDays(7),
            ],
            [
                'house_id'     => null,
                'title'        => 'Thông báo nâng giá điện từ tháng tới',
                'content'      => 'Do EVN điều chỉnh giá điện bậc thang, giá điện sẽ được cập nhật từ đầu tháng sau. Vui lòng xem chi tiết trong phiếu hóa đơn tháng tới.',
                'type'         => 'notice',
                'is_pinned'    => false,
                'published_at' => Carbon::now()->subDays(1),
            ],
        ];

        foreach ($announcements as $a) {
            DB::table('announcements')->insert([
                ...$a,
                'created_at' => $a['published_at'],
                'updated_at' => $a['published_at'],
            ]);
        }

        $this->command->info('✅ Đã tạo 5 thông báo nội bộ demo.');
    }
}
