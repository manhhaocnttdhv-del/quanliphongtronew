# Implementation Plan — Online Room Booking & Registration

## Overview

Triển khai luồng đăng ký tài khoản và đặt thuê phòng trọ online cho hệ thống Laravel hiện tại. Plan này chia thành các nhóm: migration → models → services → controllers/views → tích hợp PayOS → notifications → tests. Các nhóm có quan hệ phụ thuộc rõ ràng để cho phép thực thi song song trong cùng tầng.

## Task Dependency Graph

```json
{
  "waves": [
    {
      "wave": 1,
      "tasks": ["1.1", "1.2", "1.3", "1.4", "1.5", "1.6", "15.1"]
    },
    {
      "wave": 2,
      "tasks": ["2.1", "2.2", "2.3", "2.4", "2.5", "3.1"]
    },
    {
      "wave": 3,
      "tasks": ["4.1", "5.1", "6.1", "9.1", "10.1", "7.1", "7.2", "7.3", "7.4", "7.5"]
    },
    {
      "wave": 4,
      "tasks": ["5.2", "6.2", "6.3", "6.4"]
    },
    {
      "wave": 5,
      "tasks": ["8.1", "8.2", "8.3", "8.4", "11.1", "12.1", "9.2"]
    },
    {
      "wave": 6,
      "tasks": ["13.1"]
    },
    {
      "wave": 7,
      "tasks": ["14.1", "14.2", "14.3"]
    },
    {
      "wave": 8,
      "tasks": ["16.1"]
    },
    {
      "wave": 9,
      "tasks": ["17.1"]
    }
  ]
}
```

## Tasks

- [x] 1. Database migrations & schema changes
  - Tạo các migration mới (timestamp `2026_06_*`) để mở rộng schema mà không sửa migration cũ.
  - Đảm bảo `down()` rollback đúng cho mỗi migration.
  - _Requirements: 1, 4, 6, 7, 8, 9, 10, 11, 14_

- [x] 1.1 Migration thêm `phone` vào `users`
  - File: `database/migrations/2026_06_01_000001_add_phone_to_users_table.php`.
  - `up()`: `Schema::table('users', fn($t) => $t->string('phone',15)->nullable()->after('email'))`.
  - `down()`: drop cột.
  - _Requirements: 1.4_

- [x] 1.2 Migration mở rộng enum `rooms.status` thêm `reserved`
  - File: `database/migrations/2026_06_01_000002_add_reserved_status_to_rooms_table.php`.
  - Dùng `DB::statement('ALTER TABLE rooms MODIFY COLUMN status ENUM("available","reserved","rented","maintenance") NOT NULL DEFAULT "available"')`.
  - `down()`: revert về enum cũ.
  - _Requirements: 6.4, 11.2_

- [x] 1.3 Migration mở rộng `contracts` cho luồng booking
  - File: `database/migrations/2026_06_01_000003_extend_contracts_for_booking_workflow.php`.
  - Thêm cột: `signature_path` (string nullable), `signed_at` (timestamp nullable), `signed_ip` (string 45 nullable), `signed_user_agent` (string 255 nullable).
  - ALTER ENUM `status` thành `enum('draft','active','expired','terminated','cancelled')` default `active`.
  - _Requirements: 6.4, 9.3, 9.4, 10.4_

- [x] 1.4 Migration mở rộng `invoices` thêm `type` (deposit/monthly)
  - File: `database/migrations/2026_06_01_000004_extend_invoices_for_deposit_type.php`.
  - Thêm `type` enum default `monthly`. Đặt `month`, `year` thành nullable. Drop unique cũ `(contract_id, month, year)`, tạo unique mới `(contract_id, type, month, year)`.
  - _Requirements: 10.2_

- [x] 1.5 Migration tạo bảng `booking_requests`
  - File: `database/migrations/2026_06_01_000005_create_booking_requests_table.php`.
  - Theo schema mục 2.5 của `design.md` (đầy đủ cột, status enum, các timestamp lifecycle, FK với nullOnDelete cho tenant/contract, FK cascadeOnDelete cho user/room).
  - Index: `(status, created_at)`, `(user_id, status)`, `(room_id, status)`.
  - _Requirements: 4, 5, 6, 7, 14_

- [x] 1.6 Migration tạo bảng `booking_request_audits`
  - File: `database/migrations/2026_06_01_000006_create_booking_request_audits_table.php`.
  - Theo schema mục 2.6 của `design.md`. `metadata` json nullable. Chỉ có `created_at`.
  - _Requirements: 13.5, 14.3_

- [ ] 2. Models & relationships
  - _Requirements: 1, 4, 6, 9, 10, 11_
  - _Phụ thuộc: 1.1, 1.2, 1.3, 1.4, 1.5, 1.6_

- [x] 2.1 Tạo model `App\Models\BookingRequest`
  - Theo design 3.1: `$guarded`, `$casts`, relationships (`user`, `room`, `tenant`, `contract`, `audits`), helpers (`isPending`, `isApproved`, `isFinalized`, `hasUploadedDocuments`, `isSigned`).
  - _Requirements: 4, 5, 6, 14_
  - _Phụ thuộc: 1.5_

- [x] 2.2 Tạo model `App\Models\BookingRequestAudit`
  - Theo design 3.2. `$timestamps = false`, `metadata` cast array.
  - _Requirements: 13.5, 14_
  - _Phụ thuộc: 1.6_

- [x] 2.3 Mở rộng `App\Models\User`
  - Thêm `phone` vào `$fillable`. Thêm `tenant()` (hasOne), `bookingRequests()` (hasMany).
  - _Requirements: 1.1, 5.1_
  - _Phụ thuộc: 1.1_

- [x] 2.4 Mở rộng `App\Models\Room`
  - Thêm `scopeAvailable()`, helper `isBookable()`.
  - _Requirements: 3.1, 3.3, 11_
  - _Phụ thuộc: 1.2_

- [x] 2.5 Mở rộng `App\Models\Contract` & `App\Models\Invoice`
  - Contract: cast `signed_at` datetime.
  - Invoice: thêm scope `deposit()` và `monthly()`.
  - _Requirements: 9, 10_
  - _Phụ thuộc: 1.3, 1.4_

- [x] 3. Roles & seeder
  - _Phụ thuộc: 1.1_

- [x] 3.1 Cập nhật `RolePermissionSeeder` thêm role `customer`
  - Thêm `Role::firstOrCreate(['name' => 'customer', 'guard_name' => 'web'])`.
  - Thêm user demo `customer@demo.com / password` gán role customer.
  - _Requirements: 1.2, 2.3_
  - _Phụ thuộc: 1.1_

- [x] 4. Mở rộng đăng ký với role mặc định customer
  - _Phụ thuộc: 2.3, 3.1_

- [x] 4.1 Sửa `RegisteredUserController` và view register
  - Validate: name, email, phone (regex `/^0\d{9}$/`), password (min 8, confirmed).
  - Sau `User::create`: gán role `customer` (`$user->assignRole('customer')`).
  - Redirect sau khi tạo: `route('booking.index')`.
  - View `resources/views/auth/register.blade.php`: thêm input `phone`.
  - _Requirements: 1.1, 1.2, 1.4, 1.5, 1.6, 1.7, 1.8_
  - _Phụ thuộc: 2.3, 3.1_

- [x] 5. Phân luồng redirect theo role sau đăng nhập
  - _Phụ thuộc: 3.1_

- [x] 5.1 Cập nhật `RedirectBasedOnRole` middleware
  - Logic: admin → `admin.dashboard`, tenant → `tenant.dashboard`, customer → `booking.index`, fallback → `home`.
  - _Requirements: 2.1, 2.2, 2.3, 2.4_
  - _Phụ thuộc: 3.1_

- [x] 5.2 Cập nhật `AuthenticatedSessionController::store()` (login)
  - Sau `Auth::login`, redirect dùng cùng quy tắc trên (gọi middleware hoặc dùng helper).
  - _Requirements: 2.1, 2.2, 2.3, 2.4_
  - _Phụ thuộc: 5.1_

- [x] 6. Service layer cho booking
  - _Phụ thuộc: 2.1, 2.2, 2.3, 2.4, 2.5_

- [x] 6.1 `App\Services\Booking\RoomStatusSyncService`
  - Method `recompute(Room $room)`: priority active → rented, draft → reserved, else → available (giữ nguyên nếu maintenance).
  - _Requirements: 11.1, 11.2, 11.3_
  - _Phụ thuộc: 2.4, 2.5_

- [x] 6.2 `App\Services\Booking\BookingApprovalService`
  - Method `approve(BookingRequest, User $admin, array $data)`: theo design 4.1 (DB::transaction + lockForUpdate, throw `RoomNotAvailableException` nếu không available).
  - Method `reject(BookingRequest, User $admin, string $reason)`.
  - Ghi audit ở mỗi method.
  - _Requirements: 6.3, 6.4, 6.5, 6.6, 6.7, 11.5_
  - _Phụ thuộc: 2.1, 2.2, 6.1_

- [x] 6.3 `App\Services\Booking\BookingDepositService`
  - `createDepositInvoice(BookingRequest)`: tạo Invoice type=deposit gắn contract; total=deposit_amount, due_date = today + 2 days.
  - `markDepositPaid(Invoice, int $amount, string $referenceCode)`: theo design 6 (transaction kích hoạt Contract, đổi Room=rented, set deposit_paid_at, audit, notify).
  - Hỗ trợ partial payment: nếu amount < total → status=partial, không kích hoạt Contract.
  - _Requirements: 10.2, 10.3, 10.4, 10.5, 10.7_
  - _Phụ thuộc: 2.1, 2.5, 6.1_

- [x] 6.4 `App\Services\Booking\BookingExpiryService` + Console command
  - Service method `expireOverdue()`: tìm BookingRequest status=approved, approved_at < now()-48h, deposit_paid_at null. Trong transaction set expired, Contract draft → cancelled, Room → available, audit, notify.
  - Tạo command `app/Console/Commands/ExpireOverdueBookings.php` signature `bookings:expire-overdue`.
  - Đăng ký schedule `->hourly()` trong `App\Console\Kernel`.
  - _Requirements: 7.1, 7.2, 7.3, 7.4_
  - _Phụ thuộc: 2.1, 2.5, 6.1_

- [x] 7. Form Requests
  - _Phụ thuộc: 2.1, 2.4_

- [x] 7.1 `App\Http\Requests\Booking\StoreBookingRequest`
  - Rules: cccd `regex:/^\d{12}$/`, phone `regex:/^0\d{9}$/`, birthday nullable date, gender in male/female/other, address nullable string, hometown nullable string, desired_move_in_date `date|after_or_equal:today`, desired_occupants `int|min:1`, desired_lease_months `int|between:1,36`, customer_note nullable.
  - `withValidator`: kiểm tra `desired_occupants <= room.max_occupants`. Inject `room_id` từ query.
  - Custom messages tiếng Việt theo Requirement 4.
  - _Requirements: 4.1, 4.6, 4.7, 4.8, 4.9_
  - _Phụ thuộc: 2.1_

- [x] 7.2 `App\Http\Requests\Booking\ApproveBookingRequest`
  - Rules: deposit_amount numeric min:0, start_date date, end_date `date|after:start_date`, admin_note nullable.
  - _Requirements: 6.3_
  - _Phụ thuộc: 2.1_

- [x] 7.3 `App\Http\Requests\Booking\RejectBookingRequest`
  - Rules: rejected_reason `string|min:10`.
  - _Requirements: 6.7, 6.8_
  - _Phụ thuộc: 2.1_

- [x] 7.4 `App\Http\Requests\Booking\UpdateBookingDocumentsRequest`
  - Rules: cccd_front, cccd_back `image|mimes:jpg,jpeg,png,webp|max:5120`.
  - _Requirements: 8.3, 8.4, 8.5_
  - _Phụ thuộc: 2.1_

- [x] 7.5 `App\Http\Requests\Booking\StoreSignatureRequest`
  - Rules: signature_data `required|string`, agreed `accepted`.
  - _Requirements: 9.2_
  - _Phụ thuộc: 2.1_

- [x] 8. Controllers (Customer side)
  - _Phụ thuộc: 6.1, 6.2, 6.3, 7.1, 7.4, 7.5, 10.1_

- [x] 8.1 `App\Http\Controllers\Booking\BookingRequestController`
  - `create(Request)`: nhận `room_id`, render form.
  - `store(StoreBookingRequest)`: dùng `Room::lockForUpdate()`. Check room.status='available'. Check no pending duplicate. Tạo BookingRequest. Ghi audit. Notify admins. Redirect index flash success.
  - `index()`: list booking requests của user hiện tại.
  - `show(BookingRequest)`: authorize policy 'view'. Render chi tiết + timeline.
  - `cancel(BookingRequest)`: authorize policy 'cancel'. Update status=cancelled. Audit. Redirect.
  - _Requirements: 4, 5_
  - _Phụ thuộc: 7.1, 10.1_

- [x] 8.2 `App\Http\Controllers\Booking\BookingDocumentController`
  - `edit(BookingRequest)`: authorize 'uploadDocuments'. Render form.
  - `update(UpdateBookingDocumentsRequest, BookingRequest)`: lưu file `storage/app/public/tenants/cccd/{tenant_id}/...`. Cập nhật Tenant.cccd_front_path, cccd_back_path. Audit. Redirect tới sign.
  - _Requirements: 8_
  - _Phụ thuộc: 7.4, 10.1_

- [x] 8.3 `App\Http\Controllers\Booking\BookingSignController`
  - `create(BookingRequest)`: authorize 'sign'. Nếu chưa upload đủ giấy tờ → redirect documents.
  - `store(StoreSignatureRequest, BookingRequest)`: decode base64 PNG → save `storage/app/public/contracts/signatures/{contract_id}/signature.png`. Update Contract: signature_path, signed_at=now, signed_ip, signed_user_agent. Audit. Redirect deposit.show.
  - _Requirements: 9_
  - _Phụ thuộc: 7.5, 10.1_

- [x] 8.4 `App\Http\Controllers\Booking\BookingDepositController`
  - `show(BookingRequest)`: authorize 'pay'. Yêu cầu `isSigned()`. Tạo Invoice deposit nếu chưa có (gọi service). Render trang tóm tắt + nút PayOS.
  - `pay(BookingRequest)`: redirect tới `route('payment.checkout', $invoice)`.
  - _Requirements: 10.1, 10.2_
  - _Phụ thuộc: 6.3, 10.1_

- [x] 9. Notifications
  - _Phụ thuộc: 2.1_

- [x] 9.1 Tạo các notification class trong `App\Notifications\Booking\*`
  - `BookingRequestSubmitted`, `BookingRequestApproved`, `BookingRequestRejected`, `BookingRequestExpired`, `BookingDepositCompleted`.
  - Channel `database` mặc định. Mỗi class có `via()`, `toDatabase()` trả mảng `{title, message, action_url, booking_request_id}`.
  - _Requirements: 12_
  - _Phụ thuộc: 2.1_

- [x] 9.2 Wire notifications vào services
  - `BookingApprovalService::approve` → notify owner `BookingRequestApproved`.
  - `BookingApprovalService::reject` → notify owner `BookingRequestRejected`.
  - `BookingExpiryService` → notify owner `BookingRequestExpired`.
  - `BookingDepositService::markDepositPaid` → notify owner + admins `BookingDepositCompleted`.
  - `BookingRequestController::store` → notify all admins `BookingRequestSubmitted`.
  - _Requirements: 4.10, 6.9, 7.4, 10.5, 12_
  - _Phụ thuộc: 6.2, 6.3, 6.4, 8.1, 9.1_

- [x] 10. Authorization Policy
  - _Phụ thuộc: 2.1_

- [x] 10.1 `App\Policies\BookingRequestPolicy`
  - Methods: `view`, `cancel`, `uploadDocuments`, `sign`, `pay` theo design 5.4.
  - Đăng ký trong `AuthServiceProvider::$policies`.
  - _Requirements: 5.5, 13.3_
  - _Phụ thuộc: 2.1_

- [x] 11. Admin controllers
  - _Phụ thuộc: 6.2, 7.2, 7.3_

- [x] 11.1 `App\Http\Controllers\Admin\BookingRequestController`
  - `index(Request)`: list with filter status, paginate, sort created_at desc.
  - `show(BookingRequest)`: render chi tiết + audit timeline + form approve/reject.
  - `approve(ApproveBookingRequest, BookingRequest)`: gọi `BookingApprovalService::approve`. Catch `RoomNotAvailableException` → redirect back với lỗi.
  - `reject(RejectBookingRequest, BookingRequest)`: gọi service.
  - _Requirements: 6_
  - _Phụ thuộc: 6.2, 7.2, 7.3_

- [x] 12. Tích hợp PayOS với deposit invoice
  - _Phụ thuộc: 6.3_

- [x] 12.1 Sửa `PaymentController` xử lý invoice deposit
  - Trong `paymentSuccess` và `handleWebhook`: nếu `$invoice->type === 'deposit'` → gọi `BookingDepositService::markDepositPaid($invoice, $amount, $referenceCode)`. Tránh double-trigger nếu đã `paid`.
  - _Requirements: 10.3, 10.4, 10.5, 10.7_
  - _Phụ thuộc: 6.3_

- [x] 13. Routes
  - _Phụ thuộc: 4.1, 5.2, 8.1, 8.2, 8.3, 8.4, 11.1_

- [x] 13.1 Đăng ký routes trong `routes/web.php`
  - Thêm group `booking` (auth) và group admin `booking-requests` (auth+role:admin) theo design 5.1.
  - Đảm bảo Route Model Binding hoạt động cho `bookingRequest`.
  - _Requirements: 4, 5, 6, 8, 9, 10, 13.1, 13.2_
  - _Phụ thuộc: 4.1, 8.1, 8.2, 8.3, 8.4, 11.1_

- [ ] 14. Views
  - _Phụ thuộc: 13.1_

- [ ] 14.1 Sửa `welcome.blade.php` và `room_detail.blade.php`
  - Hiện badge trạng thái phòng (Đã thuê / Đã giữ chỗ / Đang sửa) khi không available.
  - Nút "Yêu cầu đặt thuê": auth → `route('booking.create', ['room_id' => $room->id])`; guest → `route('login', ['redirect' => url()->current()])`.
  - _Requirements: 3_
  - _Phụ thuộc: 13.1_

- [ ] 14.2 Tạo views customer trong `resources/views/booking/`
  - `create.blade.php`: form gửi yêu cầu (Bootstrap/Tailwind theo theme hiện tại). Hiển thị thông tin phòng phía bên cạnh.
  - `index.blade.php`: bảng danh sách yêu cầu của user hiện tại.
  - `show.blade.php`: chi tiết + timeline trạng thái + nút action theo trạng thái (Hủy, Hoàn tất hồ sơ, Ký, Thanh toán).
  - `documents.blade.php`: form upload 2 ảnh CCCD.
  - `sign.blade.php`: canvas signature_pad qua CDN, checkbox đồng ý điều khoản, hidden input `signature_data`.
  - `deposit.blade.php`: tóm tắt phòng + số tiền cọc + nút "Thanh toán qua PayOS".
  - _Requirements: 4, 5, 8, 9, 10_
  - _Phụ thuộc: 13.1_

- [ ] 14.3 Tạo views admin trong `resources/views/admin/booking_requests/`
  - `index.blade.php`: bảng có filter status + phân trang.
  - `show.blade.php`: chi tiết + timeline audit + form approve (modal/inline) + form reject với textarea lý do.
  - _Requirements: 6_
  - _Phụ thuộc: 13.1_

- [ ] 15. Storage links & file system
  - _Phụ thuộc: 1.1_

- [ ] 15.1 Đảm bảo `storage:link` đã chạy
  - Kiểm tra `public/storage` symlink. Document trong README hướng dẫn `php artisan storage:link` nếu chưa có.
  - Tạo các thư mục: `storage/app/public/tenants/cccd/`, `storage/app/public/contracts/signatures/`.
  - _Requirements: 8, 9_
  - _Phụ thuộc: 1.1_

- [ ] 16. Tests
  - _Phụ thuộc: 8.1, 8.2, 8.3, 8.4, 11.1, 12.1, 13.1_

- [ ] 16.1 Feature tests cốt lõi
  - File: `tests/Feature/Booking/RegistrationCustomerRoleTest.php`: đăng ký gán role customer + redirect đúng.
  - File: `tests/Feature/Booking/BookingRequestSubmissionTest.php`: validate, duplicate pending, room không available.
  - File: `tests/Feature/Booking/BookingApprovalTest.php`: happy path, rollback khi room không available, transaction tạo Tenant + Contract + sync role.
  - File: `tests/Feature/Booking/BookingCancellationTest.php`: chỉ owner + status=pending.
  - File: `tests/Feature/Booking/BookingExpiryCommandTest.php`: command set expired.
  - File: `tests/Feature/Booking/RedirectBasedOnRoleTest.php`: 3 role redirect.
  - Sử dụng `RefreshDatabase`. Seed roles ở `setUp`.
  - _Requirements: 1, 2, 4, 5, 6, 7_
  - _Phụ thuộc: 13.1, 12.1_

- [ ] 17. Tài liệu
  - _Phụ thuộc: 16.1_

- [ ] 17.1 Cập nhật README hướng dẫn chạy
  - Thêm mục "Online Booking" mô tả luồng, command `bookings:expire-overdue`, cấu hình PayOS test, các tài khoản demo (admin/tenant/customer).
  - _Requirements: tất cả_
  - _Phụ thuộc: 16.1_

## Notes

- Các migration đặt timestamp `2026_06_*` để không đụng migrations cũ.
- Mỗi service đều bọc trong `DB::transaction` và sử dụng `lockForUpdate()` khi đụng tới Room để tránh race condition.
- PayOS hiện đang dùng `amount=2000` cho test demo trong `PaymentController` — giữ nguyên ở v1, `markDepositPaid` so sánh với `Invoice.total` để xác định partial vs full.
- File chữ ký lưu PNG trong `storage/app/public/contracts/signatures/{contract_id}` — yêu cầu `php artisan storage:link`.
- Tất cả notification dùng channel `database`; có thể mở rộng `mail` ở task tương lai.
