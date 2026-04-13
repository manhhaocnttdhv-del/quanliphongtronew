<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MaintenanceTicket;
use App\Models\Contract;
use Faker\Factory as Faker;

class MaintenanceTicketSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('vi_VN');

        // Lấy tất cả hợp đồng active
        $contracts = Contract::where('status', 'active')->pluck('id');

        if ($contracts->isEmpty()) {
            $this->command->warn('Không có hợp đồng active nào. Bỏ qua MaintenanceTicketSeeder.');
            return;
        }

        $ticketTemplates = [
            ['title' => 'Bóng đèn phòng vệ sinh bị cháy', 'priority' => 'medium', 'desc' => 'Bóng đèn trong phòng vệ sinh không sáng từ hôm qua, ảnh hưởng đến sinh hoạt ban đêm.'],
            ['title' => 'Vòi nước bị nhỏ giọt, rò rỉ', 'priority' => 'high', 'desc' => 'Vòi nước lạnh trong phòng tắm bị rò rỉ, nước nhỏ giọt liên tục dù đã vặn chặt.'],
            ['title' => 'Cửa phòng khó đóng, không vào khóa được', 'priority' => 'high', 'desc' => 'Cửa phòng bị lệch bản lề, phải dùng lực mạnh mới đóng được, ổ khóa không vào.'],
            ['title' => 'Quạt trần kêu to và rung lắc', 'priority' => 'medium', 'desc' => 'Quạt trần kêu tiếng động to bất thường và có hiện tượng rung lắc khi bật tốc độ cao.'],
            ['title' => 'Tường bị ẩm mốc', 'priority' => 'low', 'desc' => 'Góc tường gần cửa sổ xuất hiện vết ẩm mốc đen, có mùi khó chịu.'],
            ['title' => 'Ổ điện bị chập, không dùng được', 'priority' => 'high', 'desc' => 'Ổ điện đầu giường bị chập khi cắm sạc điện thoại, nghe thấy tiếng nổ nhỏ.'],
            ['title' => 'Bồn cầu bị tắc nghẽn', 'priority' => 'high', 'desc' => 'Bồn cầu bị tắc không xả được, cần xử lý ngay ảnh hưởng vệ sinh.'],
            ['title' => 'Cửa sổ bị hỏng chốt, không đóng được', 'priority' => 'medium', 'desc' => 'Cửa sổ phòng bị bật chốt, không khóa được, mưa vào phòng khi trời xấu.'],
            ['title' => 'Điều hòa không mát, không hoạt động', 'priority' => 'high', 'desc' => 'Điều hòa bật lên nhưng không ra gió lạnh, không hoạt động từ 2 ngày nay.'],
            ['title' => 'Đường nước tắc, nước không thoát', 'priority' => 'medium', 'desc' => 'Nước trong phòng tắm thoát rất chậm, đứng tắm nước dâng cao.'],
        ];

        $statuses = ['pending', 'pending', 'pending', 'in_progress', 'done', 'done', 'cancelled'];

        $count = 0;
        foreach ($contracts as $contractId) {
            // Mỗi hợp đồng random 0-3 tickets
            $numTickets = rand(0, 3);
            for ($i = 0; $i < $numTickets; $i++) {
                $template = $faker->randomElement($ticketTemplates);
                $status = $faker->randomElement($statuses);

                $data = [
                    'contract_id' => $contractId,
                    'title' => $template['title'],
                    'description' => $template['desc'],
                    'priority' => $template['priority'],
                    'status' => $status,
                    'image_path' => null,
                    'admin_response' => null,
                    'resolved_at' => null,
                    'created_at' => $faker->dateTimeBetween('-3 months', 'now'),
                ];

                if ($status == 'in_progress') {
                    $data['admin_response'] = 'Đã ghi nhận, thợ sẽ đến kiểm tra và xử lý trong ngày hôm nay hoặc ngày mai. Cảm ơn bạn đã thông báo!';
                } elseif ($status == 'done') {
                    $data['admin_response'] = 'Đã xử lý xong. Nếu còn vấn đề, vui lòng báo lại nhé!';
                    $data['resolved_at'] = $faker->dateTimeBetween('-1 month', 'now');
                }

                MaintenanceTicket::create($data);
                $count++;
            }

            if ($count >= 25) break; // Giới hạn tối đa
        }

        $this->command->info("✅ Đã tạo {$count} maintenance tickets mẫu.");
    }
}
