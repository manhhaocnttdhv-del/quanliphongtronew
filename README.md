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

Tính năng đặt thuê phòng online cần truy cập file upload (CCCD, chữ ký) qua URL công khai. Trước khi sử dụng các tính năng upload, hãy chạy lệnh sau **một lần** từ thư mục gốc dự án:

```bash
php artisan storage:link
```

Lệnh này tạo symlink `public/storage` → `storage/app/public`, cho phép Laravel phục vụ file qua helper `Storage::url()` và `asset('storage/...')`.

### Cấu trúc thư mục lưu trữ

Các file upload trong luồng booking được lưu tại:

- `storage/app/public/tenants/cccd/{tenant_id}/...` — ảnh CCCD mặt trước/sau của khách thuê (`cccd_front.jpg`, `cccd_back.jpg`).
- `storage/app/public/contracts/signatures/{contract_id}/signature.png` — file chữ ký số dạng PNG do khách ký bằng signature pad.

Các thư mục `tenants/cccd/` và `contracts/signatures/` đã được tạo sẵn (kèm `.gitkeep`) và git được cấu hình để bỏ qua nội dung file thực, chỉ giữ cấu trúc thư mục.

### Yêu cầu bảo mật

- File CCCD và chữ ký chỉ phục vụ qua route đã có authorization (Policy `BookingRequestPolicy`).
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

Hiện tại `PaymentController::createPaymentLink()` hard-code `amount = 2000` cho demo. Để dùng số tiền thực, sửa thành `$amountToPay`.
