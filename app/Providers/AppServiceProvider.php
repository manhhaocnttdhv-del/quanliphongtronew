<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Contract;
use App\Models\House;
use App\Models\Invoice;
use App\Models\Room;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $protectHistory = static function (bool $hasHistory): void {
            if ($hasHistory) {
                throw ValidationException::withMessages(['history' => 'Không thể xóa dữ liệu đã có hợp đồng hoặc lịch sử tài chính.']);
            }
        };
        User::deleting(fn (User $user) => $protectHistory($user->tenant()->whereHas('contracts')->exists()
            || \App\Models\Expense::where('created_by', $user->id)->orWhere('voided_by', $user->id)->exists()
            || \App\Models\PaymentResolution::where('resolved_by', $user->id)->exists()
            || \App\Models\ContractSettlement::where('created_by', $user->id)->orWhere('refunded_by', $user->id)->exists()));
        Tenant::deleting(fn (Tenant $tenant) => $protectHistory($tenant->contracts()->exists()));
        Room::deleting(fn (Room $room) => $protectHistory($room->contracts()->exists()));
        House::deleting(fn (House $house) => $protectHistory($house->rooms()->whereHas('contracts')->exists() || \App\Models\Expense::where('house_id', $house->id)->exists()));
        Contract::deleting(fn (Contract $contract) => $protectHistory(in_array($contract->status, ['active', 'draft'], true) || $contract->invoices()->exists() || $contract->settlement()->exists() || $contract->transferred_to_contract_id || Contract::where('transferred_to_contract_id', $contract->id)->exists()));
        Invoice::deleting(fn (Invoice $invoice) => $protectHistory((int) $invoice->total > 0 || $invoice->payments()->exists() || $invoice->paymentOrders()->exists()));
    }
}
