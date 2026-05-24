<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Room;
use App\Models\House;

class ScrapeRooms extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scrape:rooms {url?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scrape room data from phongtro123.com';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $url = $this->argument('url') ?? 'https://phongtro123.com/tinh-thanh/nghe-an';
        $this->info("Bắt đầu cào dữ liệu từ: $url");

        try {
            $response = Http::get($url);
            if (!$response->successful()) {
                $this->error("Không thể tải trang web: " . $response->status());
                return;
            }

            $html = $response->body();

            // Tìm khu trọ Nghệ An hoặc tạo mới
            $house = House::firstOrCreate(
                ['name' => 'Khu trọ Nghệ An (Scraped)'],
                ['address' => 'Nghệ An', 'description' => 'Khu trọ cào từ phongtro123']
            );

            // Dùng Regex đơn giản để tìm các bài đăng thay vì DomCrawler
            // Cấu trúc hiện tại: <ul class="post__listing"> <li>...</li> </ul>
            preg_match('/<ul class="post__listing">(.*?)<\/ul>/is', $html, $listMatch);
            $listHtml = $listMatch[1] ?? '';

            preg_match_all('/<li class="d-flex.*?>(.*?)<\/li>/is', $listHtml, $matches);
            $items = $matches[1] ?? [];

            if (empty($items)) {
                $this->error("Không tìm thấy dữ liệu bài đăng nào. Cấu trúc web có thể đã thay đổi.");
                return;
            }

            $count = 0;
            foreach ($items as $itemHtml) {
                try {
                    // Lấy tiêu đề
                    preg_match('/<h3.*?><a.*?>(.*?)<\/a><\/h3>/is', $itemHtml, $titleMatch);
                    $title = isset($titleMatch[1]) ? strip_tags($titleMatch[1]) : 'Phòng trọ mới ' . uniqid();
                    $title = trim(preg_replace('/\s+/', ' ', $title));
                    if (empty($title)) $title = 'Phòng trọ mới ' . uniqid();
                    
                    // Lấy giá
                    preg_match('/class="text-green.*?>(.*?)<\/span>/is', $itemHtml, $priceMatch);
                    $priceText = isset($priceMatch[1]) ? strip_tags($priceMatch[1]) : '';
                    $price = $this->parsePrice($priceText);

                    // Lấy diện tích
                    preg_match('/<\/span><span class="dot mx-2"><\/span><span>(.*?)<\/span><\/div>/is', $itemHtml, $areaMatch);
                    $areaText = isset($areaMatch[1]) ? strip_tags($areaMatch[1]) : '';
                    $area = $this->parseArea($areaText);

                    // Lấy hình ảnh
                    preg_match('/<img.*?src="(.*?)".*?>/is', $itemHtml, $imgMatch);
                    $imagePath = $imgMatch[1] ?? null;

                    // Lấy mô tả
                    preg_match('/<p class="line-clamp-2.*?>(.*?)<\/p>/is', $itemHtml, $descMatch);
                    $descText = isset($descMatch[1]) ? strip_tags($descMatch[1]) : $title;

                    // Lưu vào DB
                    Room::create([
                        'house_id' => $house->id,
                        'name' => mb_substr($title, 0, 50),
                        'price' => $price ?: 2000000,
                        'area' => $area ?: 20,
                        'max_occupants' => 3,
                        'status' => 'available',
                        'description' => mb_substr($descText, 0, 255),
                        'images' => $imagePath ? [$imagePath] : [],
                    ]);
                    $count++;
                    $this->info("Đã cào: $title - $priceText - $areaText");
                } catch (\Exception $e) {
                    $this->warn("Lỗi 1 bài đăng: " . $e->getMessage());
                }
            }

            $this->info("Đã cào xong $count phòng!");

        } catch (\Exception $e) {
            $this->error("Lỗi: " . $e->getMessage());
        }
    }

    private function parsePrice($text)
    {
        // VD: 1.5 triệu/tháng -> 1500000
        $text = mb_strtolower(trim($text));
        if (strpos($text, 'triệu') !== false) {
            preg_match('/([\d\.]+)/', $text, $matches);
            if (isset($matches[1])) {
                return (float)$matches[1] * 1000000;
            }
        }
        if (strpos($text, 'đồng') !== false || strpos($text, 'đ') !== false) {
             preg_match('/([\d\.\,]+)/', $text, $matches);
             if (isset($matches[1])) {
                 $val = str_replace(['.', ','], '', $matches[1]);
                 return (float)$val;
             }
        }
        return 0;
    }

    private function parseArea($text)
    {
        // VD: 20m2, 20m²
        preg_match('/([\d\.]+)/', $text, $matches);
        if (isset($matches[1])) {
            return (float)$matches[1];
        }
        return 0;
    }
}
