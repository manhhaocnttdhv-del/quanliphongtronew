<?php

use App\Http\Controllers\Booking\BookingDepositController;
use App\Http\Controllers\Booking\BookingDocumentController;
use App\Http\Controllers\Booking\BookingRequestController as CustomerBookingRequestController;
use App\Http\Controllers\Booking\BookingSignController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', [\App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/phong/{id}', [\App\Http\Controllers\HomeController::class, 'show'])->name('room.show');

// PayOS Routes
Route::get('/payment/checkout/{invoice}', [\App\Http\Controllers\PaymentController::class, 'createPaymentLink'])->middleware('auth')->name('payment.checkout');
Route::get('/payment/success', [\App\Http\Controllers\PaymentController::class, 'paymentSuccess'])->name('payment.success');
Route::get('/payment/cancel', [\App\Http\Controllers\PaymentController::class, 'paymentCancel'])->name('payment.cancel');
Route::post('/payment/payos_transfer_handler', [\App\Http\Controllers\PaymentController::class, 'handleWebhook']);




// Admin Dashboard & CRUD - Chỉ Admin mới được vào
Route::middleware(['auth', 'verified', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    // /admin → /admin/dashboard
    Route::get('/', fn() => redirect()->route('admin.dashboard'));
    Route::get('/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');
    
    Route::resource('houses', \App\Http\Controllers\Admin\HouseController::class);
    Route::resource('rooms', \App\Http\Controllers\Admin\RoomController::class);
    Route::resource('tenants', \App\Http\Controllers\Admin\TenantController::class);
    Route::resource('contracts', \App\Http\Controllers\Admin\ContractController::class);
    Route::get('contracts/{contract}/pdf', [\App\Http\Controllers\Admin\ContractController::class, 'downloadPDF'])->name('contracts.pdf');
    Route::get('contracts/{contract}/transfer', [\App\Http\Controllers\Admin\ContractController::class, 'transferForm'])->name('contracts.transfer.form');
    Route::post('contracts/{contract}/transfer', [\App\Http\Controllers\Admin\ContractController::class, 'transfer'])->name('contracts.transfer');

    Route::get('invoices/auto-calculate', [\App\Http\Controllers\Admin\InvoiceController::class, 'autoCalculate'])->name('invoices.auto-calculate');
    Route::get('billing/bulk', [\App\Http\Controllers\Admin\BulkInvoiceController::class, 'index'])->name('billing.index');
    Route::post('billing/bulk/preview', [\App\Http\Controllers\Admin\BulkInvoiceController::class, 'preview'])->name('billing.preview');
    Route::post('billing/bulk', [\App\Http\Controllers\Admin\BulkInvoiceController::class, 'store'])->name('billing.store');
    Route::get('payment-reviews', [\App\Http\Controllers\Admin\PaymentReviewController::class, 'index'])->name('payment-reviews.index');
    Route::get('payment-reviews/{payment}', [\App\Http\Controllers\Admin\PaymentReviewController::class, 'show'])->name('payment-reviews.show');
    Route::post('payment-reviews/{payment}', [\App\Http\Controllers\Admin\PaymentReviewController::class, 'store'])->name('payment-reviews.store');
    Route::get('payment-reviews/{payment}/evidence', [\App\Http\Controllers\Admin\PaymentReviewController::class, 'evidence'])->name('payment-reviews.evidence');
    Route::get('contracts/{contract}/checkout', [\App\Http\Controllers\Admin\SettlementController::class, 'create'])->name('settlements.create');
    Route::post('contracts/{contract}/checkout/preview', [\App\Http\Controllers\Admin\SettlementController::class, 'preview'])->name('settlements.preview');
    Route::post('contracts/{contract}/checkout', [\App\Http\Controllers\Admin\SettlementController::class, 'store'])->name('settlements.store');
    Route::get('settlements/{settlement}', [\App\Http\Controllers\Admin\SettlementController::class, 'show'])->name('settlements.show');
    Route::post('settlements/{settlement}/refund', [\App\Http\Controllers\Admin\SettlementController::class, 'refund'])->name('settlements.refund');
    Route::get('settlements/{settlement}/evidence', [\App\Http\Controllers\Admin\SettlementController::class, 'evidence'])->name('settlements.evidence');
    Route::get('reminders', [\App\Http\Controllers\Admin\ReminderController::class, 'index'])->name('reminders.index');
    Route::put('reminders', [\App\Http\Controllers\Admin\ReminderController::class, 'update'])->name('reminders.update');
    Route::post('reminders/send', [\App\Http\Controllers\Admin\ReminderController::class, 'send'])->name('reminders.send');
    Route::resource('expenses', \App\Http\Controllers\Admin\ExpenseController::class)->only(['index', 'create', 'store']);
    Route::post('expenses/{expense}/void', [\App\Http\Controllers\Admin\ExpenseController::class, 'void'])->name('expenses.void');
    Route::get('expenses/{expense}/evidence', [\App\Http\Controllers\Admin\ExpenseController::class, 'evidence'])->name('expenses.evidence');
    Route::get('reports/finance', [\App\Http\Controllers\Admin\FinanceReportController::class, 'index'])->name('reports.finance');
    Route::resource('invoices', \App\Http\Controllers\Admin\InvoiceController::class);
    Route::resource('maintenance-tickets', \App\Http\Controllers\Admin\MaintenanceTicketController::class);
    Route::resource('announcements', \App\Http\Controllers\Admin\AnnouncementController::class);

    // Booking Requests Management
    Route::get('booking-requests', [\App\Http\Controllers\Admin\BookingRequestController::class, 'index'])->name('booking-requests.index');
    Route::get('booking-requests/{bookingRequest}', [\App\Http\Controllers\Admin\BookingRequestController::class, 'show'])->name('booking-requests.show');
    Route::post('booking-requests/{bookingRequest}/approve', [\App\Http\Controllers\Admin\BookingRequestController::class, 'approve'])->name('booking-requests.approve');
    Route::post('booking-requests/{bookingRequest}/reject', [\App\Http\Controllers\Admin\BookingRequestController::class, 'reject'])->name('booking-requests.reject');

    // Cài đặt hệ thống
    Route::get('settings', [\App\Http\Controllers\Admin\SettingController::class, 'index'])->name('settings.index');
    Route::put('settings', [\App\Http\Controllers\Admin\SettingController::class, 'update'])->name('settings.update');
});

// Tenant Portal - Giao diện dành riêng cho Người thuê
Route::middleware(['auth', 'verified', 'role:tenant'])->prefix('tenant')->name('tenant.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Tenant\DashboardController::class, 'index'])->name('dashboard');
    Route::resource('invoices', \App\Http\Controllers\Tenant\InvoiceController::class)->only(['index', 'show']);
    Route::resource('contracts', \App\Http\Controllers\Tenant\ContractController::class)->only(['index', 'show']);
    Route::resource('maintenance-tickets', \App\Http\Controllers\Tenant\MaintenanceTicketController::class)->except(['edit', 'destroy']);
});

Route::middleware('auth')->group(function () {
    Route::get('/rental-documents/tenants/{tenant}/{side}', [\App\Http\Controllers\PrivateDocumentController::class, 'document'])->where('side', 'front|back')->name('rental-documents.show');
    Route::get('/rental-documents/contracts/{contract}/signature', [\App\Http\Controllers\PrivateDocumentController::class, 'signature'])->name('rental-documents.signature');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Notifications
    Route::get('/notifications/{id}/read', [\App\Http\Controllers\NotificationController::class, 'read'])->name('notifications.read');
    Route::post('/notifications/read-all', [\App\Http\Controllers\NotificationController::class, 'readAll'])->name('notifications.readAll');
});

// Customer Booking Routes - Cho người dùng đã đăng nhập đặt phòng online
Route::middleware(['auth'])->prefix('booking')->name('booking.')->group(function () {
    Route::get('create', [CustomerBookingRequestController::class, 'create'])->name('create');
    Route::post('/', [CustomerBookingRequestController::class, 'store'])->name('store');
    Route::get('my-requests', [CustomerBookingRequestController::class, 'index'])->name('index');
    Route::get('my-requests/{bookingRequest}', [CustomerBookingRequestController::class, 'show'])->name('show');
    Route::post('my-requests/{bookingRequest}/cancel', [CustomerBookingRequestController::class, 'cancel'])->name('cancel');

    Route::get('my-requests/{bookingRequest}/documents', [BookingDocumentController::class, 'edit'])->name('documents.edit');
    Route::post('my-requests/{bookingRequest}/documents', [BookingDocumentController::class, 'update'])->name('documents.update');

    Route::get('my-requests/{bookingRequest}/sign', [BookingSignController::class, 'create'])->name('sign.create');
    Route::post('my-requests/{bookingRequest}/sign', [BookingSignController::class, 'store'])->name('sign.store');

    Route::get('my-requests/{bookingRequest}/deposit', [BookingDepositController::class, 'show'])->name('deposit.show');
    Route::post('my-requests/{bookingRequest}/deposit', [BookingDepositController::class, 'pay'])->name('deposit.pay');
});

require __DIR__.'/auth.php';
