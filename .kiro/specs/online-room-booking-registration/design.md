# Design Document — Online Room Booking & Registration

## 1. Tổng quan kiến trúc

Tính năng được cài đặt như một **mô-đun Laravel mới** chồng lên hệ thống quản lý phòng trọ hiện tại. Mô-đun này:

- Mở rộng `users`, `rooms`, `tenants`, `contracts`, `invoices` thay vì tạo bảng song song.
- Thêm bảng mới `booking_requests` làm trung tâm vòng đời yêu cầu thuê online.
- Tận dụng hạ tầng có sẵn: `spatie/laravel-permission`, PayOS qua `PaymentController`, Database Notifications qua `NotificationController`.
- Tách logic phức tạp ra các Service class (`BookingApprovalService`, `RoomStatusSyncService`, `BookingDepositService`) để controller giữ vai trò mỏng.

### Sơ đồ luồng tổng

```
Khách (guest)
  └─► /register ──► User(role=customer) ──► /login
                                              │
                                              ▼
                                     /booking/my-requests
                                              │
                          /  /phong/{id}  ─►  /booking/create?room_id=
                          │                       │
                          │                       ▼
                          │                BookingRequest(status=pending)
                          │                       │
Admin: /admin/booking-requests/{id}  ◄────────────┘
   │
   ├─ Reject ──► status=rejected
   └─ Approve (transaction)
        ├─ Tenant tạo (gắn user_id)
        ├─ Contract(status=draft)
        ├─ User: customer→tenant
        ├─ Room.status=reserved
        └─ BookingRequest.status=approved

Customer:
  └─► /booking/my-requests/{id}/documents  (upload CCCD)
        └─► /sign  (e-sign chữ ký + checkbox)
              └─► /deposit  (PayOS)
                    └─ Webhook/Return URL
                         ├─ Invoice(deposit).paid
                         ├─ Contract.status=active
                         ├─ Room.status=rented
                         └─ Notify customer + admins

Scheduled (hourly): ExpireApprovedBookings
  └─► BookingRequest.approved quá 48h chưa thanh toán
        ├─ status=expired
        ├─ Contract(draft)→cancelled
        └─ Room→available
```

## 2. Thay đổi schema (migrations mới)

Tất cả migration mới đặt timestamp `2026_06_*` để chạy sau migration hiện có và **không sửa file migration cũ**.

### 2.1 `add_phone_to_users_table`
```php
Schema::table('users', function (Blueprint $t) {
    $t->string('phone', 15)->nullable()->after('email');
});
```

### 2.2 `add_reserved_status_and_extras_to_rooms_table`
- `status` chuyển từ `enum('available','rented','maintenance')` thành `enum('available','reserved','rented','maintenance')`.
- Vì MySQL không cho ALTER ENUM trực tiếp một cách an toàn, dùng `DB::statement('ALTER TABLE rooms MODIFY COLUMN status ENUM(...) NOT NULL DEFAULT "available"')` trong `up()`/`down()`.

### 2.3 `extend_contracts_for_booking_workflow`
```php
Schema::table('contracts', function (Blueprint $t) {
    $t->string('signature_path')->nullable();
    $t->timestamp('signed_at')->nullable();
    $t->string('signed_ip', 45)->nullable();
    $t->string('signed_user_agent', 255)->nullable();
});
// ALTER ENUM status thêm 'draft','cancelled':
// enum('draft','active','expired','terminated','cancelled') DEFAULT 'active'
```

### 2.4 `extend_invoices_for_deposit_type`
```php
Schema::table('invoices', function (Blueprint $t) {
    $t->enum('type', ['monthly','deposit'])->default('monthly')->after('contract_id');
    // Cho phép month/year nullable cho hóa đơn deposit:
    $t->unsignedTinyInteger('month')->nullable()->change();
    $t->unsignedSmallInteger('year')->nullable()->change();
});
// Drop unique cũ (contract_id, month, year), tạo unique mới (contract_id, type, month, year).
```

### 2.5 `create_booking_requests_table` — bảng trung tâm

| Cột | Kiểu | Ghi chú |
|---|---|---|
| id | bigint PK | |
| user_id | FK users, cascadeOnDelete | Người gửi |
| room_id | FK rooms, cascadeOnDelete | Phòng yêu cầu |
| tenant_id | FK tenants, nullable | Set khi duyệt |
| contract_id | FK contracts, nullable | Set khi duyệt |
| cccd | string(20) | Mặt nhập tay khi gửi yêu cầu |
| phone | string(15) | |
| birthday | date, nullable | |
| gender | enum male/female/other, nullable | |
| address | text, nullable | Địa chỉ thường trú |
| hometown | string, nullable | |
| desired_move_in_date | date | |
| desired_occupants | int | Số người ở dự kiến |
| desired_lease_months | int | 1..36 |
| customer_note | text, nullable | |
| admin_note | text, nullable | |
| rejected_reason | text, nullable | |
| deposit_amount | decimal(12,0), nullable | Set khi duyệt |
| status | enum('pending','approved','rejected','cancelled','expired') default pending | |
| approved_at, rejected_at, cancelled_at, expired_at, deposit_paid_at | timestamp nullable | |
| last_status_changed_by | FK users nullable | |
| last_status_changed_at | timestamp nullable | |
| timestamps | | |

Index: `(status, created_at)`, `(user_id, status)`, `(room_id, status)`.

### 2.6 `create_booking_request_audits_table` — log audit

| Cột | Kiểu |
|---|---|
| id | bigint PK |
| booking_request_id | FK |
| event | string (`created`, `approved`, `rejected`, `cancelled`, `expired`, `documents_uploaded`, `signed`, `deposit_paid`) |
| actor_user_id | FK users nullable |
| ip_address | string(45) nullable |
| user_agent | string(255) nullable |
| metadata | json nullable |
| created_at | timestamp |

## 3. Models mới và mở rộng

### 3.1 `App\Models\BookingRequest`
```php
class BookingRequest extends Model {
    protected $guarded = ['id'];
    protected $casts = [
        'desired_move_in_date' => 'date',
        'birthday' => 'date',
        'approved_at' => 'datetime', 'rejected_at' => 'datetime',
        'cancelled_at' => 'datetime', 'expired_at' => 'datetime',
        'deposit_paid_at' => 'datetime',
    ];

    public function user()     { return $this->belongsTo(User::class); }
    public function room()     { return $this->belongsTo(Room::class); }
    public function tenant()   { return $this->belongsTo(Tenant::class); }
    public function contract() { return $this->belongsTo(Contract::class); }
    public function audits()   { return $this->hasMany(BookingRequestAudit::class); }

    // Accessors
    public function isPending(): bool   { return $this->status === 'pending'; }
    public function isApproved(): bool  { return $this->status === 'approved'; }
    public function isFinalized(): bool { return in_array($this->status, ['cancelled','rejected','expired']); }

    // Helpers
    public function hasUploadedDocuments(): bool {
        return $this->tenant && $this->tenant->cccd_front_path && $this->tenant->cccd_back_path;
    }
    public function isSigned(): bool { return $this->contract && $this->contract->signed_at; }
}
```

### 3.2 `App\Models\BookingRequestAudit`
```php
class BookingRequestAudit extends Model {
    protected $guarded = ['id'];
    public $timestamps = false;
    protected $casts = ['metadata' => 'array', 'created_at' => 'datetime'];
    public function bookingRequest() { return $this->belongsTo(BookingRequest::class); }
    public function actor()          { return $this->belongsTo(User::class, 'actor_user_id'); }
}
```

### 3.3 Mở rộng models hiện có
- `User`: thêm `phone` vào `$fillable`. Thêm relationship `tenant()` (hasOne) và `bookingRequests()` (hasMany).
- `Room`: thêm scope `scopeAvailable($q)` filter status=available; helper `isBookable()` trả `true` khi status='available'.
- `Contract`: cập nhật cast `signed_at` thành datetime.
- `Invoice`: cập nhật `$casts['type']` (no-op nhưng cho rõ); scope `deposit()`/`monthly()`.

## 4. Service layer

### 4.1 `App\Services\Booking\BookingApprovalService`
Chịu trách nhiệm transaction Approve. API:
```php
public function approve(BookingRequest $br, User $admin, array $data): BookingRequest;
public function reject(BookingRequest $br, User $admin, string $reason): BookingRequest;
```

`approve()` thực hiện trong `DB::transaction()`:
1. `$room = Room::lockForUpdate()->find($br->room_id);` (pessimistic lock)
2. Nếu `$room->status !== 'available'` → throw `RoomNotAvailableException`.
3. Tạo Tenant nếu user chưa có (`Tenant::firstOrCreate(['user_id' => $br->user_id], [...])`).
4. Tạo Contract status=draft, gán signature_path/signed_at = null.
5. `$user->syncRoles(['tenant'])` — chuyển từ customer sang tenant.
6. `$room->update(['status' => 'reserved'])`.
7. `$br->update(['status' => 'approved', 'tenant_id' => ..., 'contract_id' => ..., 'deposit_amount' => $data['deposit_amount'], 'approved_at' => now(), ...])`.
8. Ghi audit `event=approved`.
9. Notify user (`BookingApprovedNotification`).

`reject()`: cập nhật status, rejected_reason, audit, notify.

### 4.2 `App\Services\Booking\RoomStatusSyncService`
Tập trung quy tắc đồng bộ trạng thái Room (Requirement 11):
```php
public function recompute(Room $room): void;
```
- Nếu Room có Contract active → status=rented.
- Else nếu có Contract draft → reserved.
- Else (và status hiện không phải maintenance) → available.

Gọi sau mỗi lần đổi trạng thái Contract.

### 4.3 `App\Services\Booking\BookingDepositService`
- `createDepositInvoice(BookingRequest $br): Invoice` — tạo Invoice type=deposit gắn contract_id.
- `markDepositPaid(Invoice $invoice, int $amount, string $referenceCode): void` — cập nhật Invoice, tạo Payment, kích hoạt Contract, đổi Room sang rented, notify, ghi audit.

### 4.4 `App\Services\Booking\BookingExpiryService`
- `expireOverdue(): int` — quét các BookingRequest approved quá 48h chưa paid_at, set expired, hủy Contract draft, trả Room về available, notify.

## 5. HTTP layer

### 5.1 Routes mới (`routes/web.php`)

```php
// Booking — Customer
Route::middleware(['auth'])->prefix('booking')->name('booking.')->group(function () {
    Route::get('create', [BookingRequestController::class, 'create'])->name('create');
    Route::post('/', [BookingRequestController::class, 'store'])->name('store');
    Route::get('my-requests', [BookingRequestController::class, 'index'])->name('index');
    Route::get('my-requests/{bookingRequest}', [BookingRequestController::class, 'show'])
        ->name('show');
    Route::post('my-requests/{bookingRequest}/cancel', [BookingRequestController::class, 'cancel'])
        ->name('cancel');

    Route::get('my-requests/{bookingRequest}/documents', [BookingDocumentController::class, 'edit'])
        ->name('documents.edit');
    Route::post('my-requests/{bookingRequest}/documents', [BookingDocumentController::class, 'update'])
        ->name('documents.update');

    Route::get('my-requests/{bookingRequest}/sign', [BookingSignController::class, 'create'])
        ->name('sign.create');
    Route::post('my-requests/{bookingRequest}/sign', [BookingSignController::class, 'store'])
        ->name('sign.store');

    Route::get('my-requests/{bookingRequest}/deposit', [BookingDepositController::class, 'show'])
        ->name('deposit.show');
    Route::post('my-requests/{bookingRequest}/deposit', [BookingDepositController::class, 'pay'])
        ->name('deposit.pay');
});

// Admin — Booking approval
Route::middleware(['auth','verified','role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('booking-requests', [Admin\BookingRequestController::class, 'index'])
        ->name('booking-requests.index');
    Route::get('booking-requests/{bookingRequest}', [Admin\BookingRequestController::class, 'show'])
        ->name('booking-requests.show');
    Route::post('booking-requests/{bookingRequest}/approve', [Admin\BookingRequestController::class, 'approve'])
        ->name('booking-requests.approve');
    Route::post('booking-requests/{bookingRequest}/reject', [Admin\BookingRequestController::class, 'reject'])
        ->name('booking-requests.reject');
});
```

Đăng ký Route Model Binding implicit (`bookingRequest` → `BookingRequest`).

### 5.2 Form Requests
- `RegisterRequest` (mở rộng RegisteredUserController): name, email, phone (regex `/^0\d{9}$/`), password (min:8, confirmed).
- `StoreBookingRequest`: cccd (regex `/^\d{12}$/`), phone, birthday (nullable date < hôm nay), gender, address, hometown, desired_move_in_date (after_or_equal:today), desired_occupants (int min:1, max: room.max_occupants — kiểm tra trong `withValidator`), desired_lease_months (int 1..36), customer_note nullable.
- `ApproveBookingRequest`: deposit_amount (>=0), start_date (date), end_date (after start_date), admin_note nullable.
- `RejectBookingRequest`: rejected_reason (min:10).
- `UpdateBookingDocumentsRequest`: cccd_front, cccd_back (image, mimes:jpg,jpeg,png,webp, max:5120).
- `StoreSignatureRequest`: signature_data (string base64 PNG dataURI), agreed (accepted).

### 5.3 Controllers (mỏng, gọi service)

- `App\Http\Controllers\Auth\RegisteredUserController` (sửa): validate phone, gán role 'customer' sau khi tạo, đổi redirect sang `route('booking.index')`.
- `App\Http\Controllers\Booking\BookingRequestController`: index/show/create/store/cancel.
  - `store()` dùng `Room::lockForUpdate()` + check không có pending duplicate.
- `App\Http\Controllers\Booking\BookingDocumentController`: edit/update.
- `App\Http\Controllers\Booking\BookingSignController`: create/store. `store()` decode base64 PNG → save.
- `App\Http\Controllers\Booking\BookingDepositController`: show/pay. `pay()` gọi `BookingDepositService::createDepositInvoice` rồi redirect sang `route('payment.checkout', $invoice)`.
- `App\Http\Controllers\Admin\BookingRequestController`: index (filter status), show, approve, reject. Gọi `BookingApprovalService`.

### 5.4 Authorization (Policies)
- `App\Policies\BookingRequestPolicy`:
  - `view(User $user, BookingRequest $br)`: `user->id === br.user_id || user->hasRole('admin')`.
  - `cancel(User $user, BookingRequest $br)`: `user->id === br.user_id && br.status === 'pending'`.
  - `uploadDocuments`/`sign`/`pay`: owner + status approved + step ordering.
- Đăng ký trong `AuthServiceProvider`.

### 5.5 Middleware update — `RedirectBasedOnRole`
Thay luồng để hỗ trợ 3 role:
```php
if ($u->hasRole('admin'))   return redirect()->route('admin.dashboard');
if ($u->hasRole('tenant'))  return redirect()->route('tenant.dashboard');
if ($u->hasRole('customer'))return redirect()->route('booking.index');
return redirect()->route('home');
```

`AuthenticatedSessionController::store()` cũng dùng cùng logic (hoặc gọi middleware này).

## 6. Tích hợp PayOS cho deposit

Giữ nguyên `PaymentController::createPaymentLink`. Thay đổi:
- `paymentSuccess` và `handleWebhook` cần gọi vào `BookingDepositService::markDepositPaid()` khi Invoice là `type=deposit`. Logic:
  ```php
  if ($invoice->type === 'deposit') {
      app(BookingDepositService::class)->markDepositPaid($invoice, $amount, $referenceCode);
  }
  ```
- `markDepositPaid` (transaction):
  1. Cập nhật Invoice `status=paid`, `paid_amount=total`, `debt=0`.
  2. Tạo Payment(`method=transfer`, `reference_code=$orderCode`).
  3. Cập nhật Contract: `status=active`.
  4. Cập nhật Room: `status=rented`.
  5. Cập nhật BookingRequest: `deposit_paid_at = now()`.
  6. Notify user + admins.
  7. Ghi audit `event=deposit_paid`.

Lưu ý: PaymentController hiện hardcode `amount=2000` cho test — giữ nguyên để không phá flow demo, nhưng `markDepositPaid` so sánh với `$invoice->total` để xác định partial vs full.

## 7. Notifications

Tất cả là `Illuminate\Notifications\Notification` với channel `database` (mặc định). Optional `mail` channel khi user có cờ `email_notifications=1` (cột tương lai — design này dùng config `notifications.email_enabled` đơn giản).

| Class | Recipient | Trigger |
|---|---|---|
| `BookingRequestSubmitted` | All admins | BookingRequest created |
| `BookingRequestApproved` | Owner | After approve |
| `BookingRequestRejected` | Owner | After reject |
| `BookingRequestExpired` | Owner | After expiry |
| `BookingDepositCompleted` | Owner + admins | After deposit paid |

Mỗi notification chứa `bookingRequestId`, `roomName`, `actionUrl`.

## 8. Console command — `bookings:expire-overdue`

```php
class ExpireOverdueBookings extends Command {
    protected $signature = 'bookings:expire-overdue';
    public function handle(BookingExpiryService $svc) {
        $n = $svc->expireOverdue();
        $this->info("Expired {$n} booking requests.");
    }
}
```

Đăng ký trong `App\Console\Kernel::schedule()`:
```php
$schedule->command('bookings:expire-overdue')->hourly();
```

## 9. Frontend / Views

Layouts: tận dụng `resources/views/layouts/app.blade.php` hiện có.

### 9.1 Sửa view có sẵn
- `resources/views/auth/register.blade.php`: thêm input `phone`.
- `resources/views/welcome.blade.php`: nút "Yêu cầu đặt thuê" link tới `/booking/create?room_id=` nếu auth, ngược lại sang `/login?redirect=/phong/{id}`. Hiện badge trạng thái.
- `resources/views/room_detail.blade.php`: tương tự — ẩn nút khi room.status != available.

### 9.2 View mới
```
resources/views/booking/
  create.blade.php           — form gửi yêu cầu
  index.blade.php            — danh sách yêu cầu của customer
  show.blade.php             — chi tiết, timeline, action buttons
  documents.blade.php        — upload CCCD
  sign.blade.php             — canvas chữ ký (signature_pad lib qua CDN)
  deposit.blade.php          — tóm tắt + nút PayOS
resources/views/admin/booking_requests/
  index.blade.php            — bảng + filter status
  show.blade.php             — chi tiết, form approve/reject
```

`sign.blade.php` dùng [signature_pad](https://github.com/szimek/signature_pad) qua CDN, submit data URL trong hidden input.

## 10. Seeder & permissions

`RolePermissionSeeder` thêm:
```php
$customerRole = Role::firstOrCreate(['name' => 'customer', 'guard_name' => 'web']);
```
Và một user demo `customer@demo.com / password` gán role customer (tùy chọn để test).

## 11. Bảo mật & audit

- Mọi route booking yêu cầu `auth`.
- Mọi form POST có `@csrf`.
- `BookingRequestPolicy` chặn truy cập chéo.
- Webhook PayOS đã verify chữ ký bằng `verifyPaymentWebhookData` — giữ nguyên.
- File upload: `storage/app/public/tenants/cccd/{tenant_id}/...` và `storage/app/public/contracts/signatures/{contract_id}/...`. Đảm bảo `php artisan storage:link` đã chạy.
- Audit log ghi vào bảng `booking_request_audits`. Trang admin show timeline đọc bảng này.

## 12. Kiểm thử

### 12.1 Feature tests (`tests/Feature/Booking/`)
- `RegistrationCustomerRoleTest` — đăng ký gán role customer + redirect.
- `BookingRequestSubmissionTest` — tạo, validate, duplicate pending, room not available.
- `BookingApprovalTest` — happy path transaction; rollback khi room đổi trạng thái; concurrency với 2 admin (sử dụng DB::transaction + lock).
- `BookingCancellationTest` — chỉ owner + status=pending.
- `BookingDocumentsUploadTest` — file validation.
- `BookingSignTest` — canvas required, lưu signature, bắt buộc upload trước.
- `BookingDepositPayOSTest` — mock PayOS, kích hoạt contract.
- `BookingExpiryCommandTest` — quét và set expired.
- `RoomStatusSyncServiceTest` — recompute đúng theo các tổ hợp contract.
- `RedirectBasedOnRoleTest` — 3 role redirect đúng.

### 12.2 Property-based test (1 file đại diện)
`tests/Feature/Booking/BookingApprovalConcurrencyTest.php` — sinh N admin cố duyệt cùng 1 room đồng thời, assert chỉ 1 thành công.

## 13. Các quyết định thiết kế cần highlight

| Vấn đề | Quyết định | Lý do |
|---|---|---|
| Tách `customer` khỏi `tenant` role | Có | Theo Requirement 1, 2; tenant chỉ tồn tại sau khi có Tenant record + Contract |
| Tạo Tenant ngay lúc duyệt thay vì lúc đặt cọc | Có | Để Contract draft tham chiếu được tenant_id (FK NOT NULL trong contracts hiện tại) |
| Reservation hold = 48h cứng | Có (Requirement 7) | Có thể đưa vào Setting sau, nhưng v1 hardcode trong service |
| ALTER ENUM thay vì migrate sang string | Giữ ENUM | Tương thích phần còn lại của code đang query theo string literal |
| Chữ ký lưu PNG trong storage public | Có | Đơn giản, không cần lib chữ ký số chuẩn cho v1 |
| Sử dụng PayOS hiện tại với fixed amount=2000 | Tạm chấp nhận (debt) | Tuân theo code hiện tại của project; markdown trong notes |
| Audit dùng bảng riêng thay vì laravel-auditing | Bảng riêng | Tránh thêm dependency, đủ dùng cho 6 sự kiện |

## 14. Out of scope (v1)

- 2FA / OTP đăng ký.
- Chữ ký số chuẩn (PKI/CA).
- Refund deposit khi rejected sau khi đã thanh toán.
- Chat realtime giữa customer và admin.
- Gói thông báo mobile push.
- Đa ngôn ngữ (chỉ tiếng Việt).
