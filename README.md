<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com/)**
- **[Tighten Co.](https://tighten.co)**
- **[WebReinvent](https://webreinvent.com/)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel/)**
- **[Cyber-Duck](https://cyber-duck.co.uk)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Jump24](https://jump24.co.uk)**
- **[Redberry](https://redberry.international/laravel/)**
- **[Active Logic](https://activelogic.com)**
- **[byte5](https://byte5.de)**
- **[OP.GG](https://op.gg)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## Online Booking Storage

Ảnh phòng và ảnh sự cố dùng vùng lưu trữ công khai. Tạo liên kết cho các ảnh này:

```bash
php artisan storage:link
```

CCCD và chữ ký được lưu trên disk `private`, không đi qua liên kết `public/storage`.

Khi cập nhật từ phiên bản cũ, chạy trước khi mở web trở lại:

```bash
php artisan migrate --force
php artisan documents:privatize
php artisan optimize:clear
```

Lệnh chuyển hồ sơ giữ nguyên đường dẫn tương đối, kiểm tra nội dung rồi mới xóa bản công khai. Nếu có file riêng khác nội dung, lệnh dừng để tránh ghi đè.

### Cấu trúc thư mục lưu trữ

Các file upload trong luồng booking được lưu tại:

- `storage/app/private/tenants/cccd/{tenant_id}/...` — ảnh CCCD mặt trước/sau của khách thuê.
- `storage/app/private/contracts/signatures/{contract_id}/signature.png` — chữ ký PNG của khách thuê.

Laravel tạo thư mục riêng khi upload; nội dung `storage/app/private` được Git bỏ qua.

### Yêu cầu bảo mật

- File CCCD và chữ ký chỉ được trả qua route `rental-documents.*` cho chính khách thuê hoặc admin, với header không lưu cache.
- Không commit file upload thực vào git — `.gitignore` trong các thư mục con đã loại trừ tất cả file ngoại trừ `.gitkeep`/`.gitignore`.


---

## Online Booking — Đặt thuê phòng trọ Online

### Luồng nghiệp vụ

1. Khách truy cập `/` → xem phòng trống → bấm **Đăng ký** trên navbar.
2. Đăng ký tài khoản (`/register`) — role mặc định là `customer`.
3. Đăng nhập sẽ tự redirect tới `/booking/my-requests`.
4. Vào trang chi tiết phòng `/phong/{id}` → bấm **Yêu cầu đặt thuê** → điền form CCCD + ngày chuyển vào + số người → submit.
5. Admin vào `/admin/booking-requests` → duyệt hoặc từ chối. Khi duyệt: hệ thống tự tạo Tenant + Contract draft, đổi role User thành `tenant`, đổi Room sang `reserved`.
6. Customer hoàn tất 3 bước: Upload CCCD → Ký điện tử → Thanh toán cọc qua PayOS.
7. Sau khi PayOS xác nhận thanh toán → Contract `active`, Room `rented`.

### Tài khoản demo

| Role     | Email                | Mật khẩu  |
|----------|----------------------|-----------|
| Admin    | admin@demo.com       | password  |
| Tenant   | tenant@demo.com      | password  |
| Customer | customer@demo.com    | password  |

### Lệnh tiện ích

```bash
# Migrate + seed
php artisan migrate
php artisan db:seed --class=RolePermissionSeeder

# Tạo symlink storage (nếu chưa có)
php artisan storage:link

# Quét yêu cầu hết hạn giữ chỗ (tự động chạy mỗi giờ qua scheduler)
php artisan bookings:expire-overdue
```

### Cấu hình PayOS

Trong `.env`:

```
PAYOS_CLIENT_ID=...
PAYOS_API_KEY=...
PAYOS_CHECKSUM_KEY=...
```

Checkout gửi đúng số dư hóa đơn và lưu mã đơn duy nhất trong `payment_orders`. Chỉ webhook có chữ ký hợp lệ mới ghi nhận tiền; URL quay về không thay đổi công nợ. Cấu hình webhook PayOS về `POST /payment/payos_transfer_handler`.

Các link PayOS cũ chỉ lưu mã trong `notes` cần được tạo lại sau khi nâng cấp. Giao dịch đến muộn hoặc trả thừa vẫn có phiếu thu và được đánh dấu cần đối soát, không tự kích hoạt hợp đồng đã hủy.

## Quản lý vận hành và tài chính

Chạy `php artisan migrate --force` khi nâng cấp để tạo các bảng đối soát, quyết toán, chi phí và lịch sử nhắc hạn.

- **Lập hóa đơn tháng** (`/admin/billing/bulk`): lọc chi nhánh và tháng, nhập chỉ số điện nước, chọn phòng rồi xem trước. Máy chủ tính tiền theo giá hợp đồng và dịch vụ đang bật, kiểm tra chỉ số kỳ trước, chống lập trùng. Bản xem trước có hiệu lực 20 phút; nếu biểu phí hoặc dữ liệu thay đổi phải xem trước lại. Tiền phòng trong luồng hàng tháng dùng giá trọn tháng.
- **Nhắc hạn** (`/admin/reminders`): cấu hình nhắc trước hạn, khoảng lặp nhắc nợ và hợp đồng sắp hết hạn. Thông báo được gửi trong hệ thống cho người thuê; nhắc hợp đồng cũng gửi admin. Có lịch sử và chống gửi trùng. Có thể chạy thủ công `php artisan rentals:send-reminders`.
- **Đối soát & hoàn tiền** (`/admin/payment-reviews`): xem khoản thu cần kiểm tra, giữ lại có lý do hoặc ghi nhận số đã hoàn. Hoàn tiền yêu cầu mã tham chiếu và chứng từ riêng tư, không tự chuyển tiền ngân hàng. Số hoàn không vượt tiền thừa hoặc tiền cọc của hợp đồng đã hủy. Không xóa phiếu thu gốc.
- **Quyết toán trả phòng**: mở từ chi tiết hợp đồng đang hoạt động. Chốt chỉ số, hư hỏng, phụ phí, cọc thực tế đang giữ và biên bản. Tiền phòng chưa lập của tháng cuối tính theo số ngày ở / số ngày trong tháng; nếu đã có hóa đơn tháng thì không lập lại tiền phòng và phí cố định. Cọc bù các khoản nợ trước, phần còn lại tạo nghĩa vụ hoàn; chỉ ghi đã hoàn khi có chứng từ. Chốt quyết toán trả phòng về trống, khóa số phí đã chốt và không cho kích hoạt lại hợp đồng. Người thuê vẫn xem và trả công nợ còn lại, đồng thời xem biên bản trong hợp đồng.
- **Chi phí** (`/admin/expenses`): ghi khoản chi theo chi nhánh hoặc chi phí chung, ngày, danh mục, mã phiếu và chứng từ. Điều chỉnh bằng hủy có lý do, giữ nguyên lịch sử.
- **Báo cáo thu chi** (`/admin/reports/finance`): tổng hợp theo ngày thực thu/chi trong tháng, lọc chi nhánh và xuất `.xlsx`. Lợi nhuận vận hành trên cơ sở tiền thu gồm tiền thuê thực thu và cọc đã bù công nợ, trừ hoàn tiền thuê và chi phí; tiền nhận cọc và hoàn cọc theo dõi riêng. Lọc một chi nhánh không phân bổ chi phí chung. Đây không phải báo cáo thuế hay sổ kế toán dồn tích.

Với hợp đồng cũ không có hóa đơn cọc, quản lý phải xác nhận lại cọc thực tế đang giữ khi quyết toán; không mặc định tiền cọc danh nghĩa là chứng từ đã thu. Các hóa đơn kỳ sau ngày trả phòng và khoản thu chưa đối soát phải xử lý trước khi chốt. Các khoản thu lịch sử chưa có phiếu thu không thể tự suy ra ngày thu trong báo cáo tiền thực tế.

Chuyển phòng tạo hóa đơn cọc riêng và hóa đơn phụ phí riêng; cọc thực nhận chuyển nội bộ không tính thêm tiền vào hay doanh thu. Số cọc chưa thu ở hợp đồng cũ không được coi là đã chuyển. Hợp đồng nguồn bị khóa kích hoạt lại và hoàn cọc thủ công để tránh dùng cùng tiền cọc hai lần. Nếu muốn giảm cọc xuống dưới số đang giữ, cần quyết toán và hoàn có chứng từ rồi lập hợp đồng mới. Với hợp đồng cũ không có hóa đơn cọc, cần xác minh cọc danh nghĩa trước khi dùng luồng chuyển phòng.

Scheduler cần được hệ điều hành gọi mỗi phút. Trên Windows, cấu hình Task Scheduler chạy `php artisan schedule:run` tại thư mục dự án mỗi phút. Nhắc hạn chạy lúc 08:00 theo `RENTAL_REMINDER_TIMEZONE` (mặc định `Asia/Ho_Chi_Minh`). Nếu scheduler chưa được cấu hình, nút gửi nhắc và lệnh thủ công vẫn hoạt động; website không tự tạo tác vụ của hệ điều hành.

Chứng từ tài chính nằm trong `storage/app/private/finance`, chỉ admin tải được qua route có xác thực. Bảo đảm vùng lưu trữ riêng có trong sao lưu.
