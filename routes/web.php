<?php

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

Route::get('/', fn() => null)->middleware('redirect.role')->name('home');


// Admin Dashboard & CRUD - Chỉ Admin mới được vào
Route::middleware(['auth', 'verified', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');
    
    Route::resource('houses', \App\Http\Controllers\Admin\HouseController::class);
    Route::resource('rooms', \App\Http\Controllers\Admin\RoomController::class);
    Route::resource('tenants', \App\Http\Controllers\Admin\TenantController::class);
    Route::resource('contracts', \App\Http\Controllers\Admin\ContractController::class);
    Route::get('contracts/{contract}/pdf', [\App\Http\Controllers\Admin\ContractController::class, 'downloadPDF'])->name('contracts.pdf');
    Route::get('meter-readings/get-old-value', [\App\Http\Controllers\Admin\MeterReadingController::class, 'getOldValue'])->name('meter-readings.get-old-value');
    Route::resource('meter-readings', \App\Http\Controllers\Admin\MeterReadingController::class);
    Route::get('invoices/auto-calculate', [\App\Http\Controllers\Admin\InvoiceController::class, 'autoCalculate'])->name('invoices.auto-calculate');
    Route::resource('invoices', \App\Http\Controllers\Admin\InvoiceController::class);
    Route::resource('maintenance-tickets', \App\Http\Controllers\Admin\MaintenanceTicketController::class);
    Route::resource('announcements', \App\Http\Controllers\Admin\AnnouncementController::class);

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
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Notifications
    Route::get('/notifications/{id}/read', [\App\Http\Controllers\NotificationController::class, 'read'])->name('notifications.read');
    Route::post('/notifications/read-all', [\App\Http\Controllers\NotificationController::class, 'readAll'])->name('notifications.readAll');
});

require __DIR__.'/auth.php';
