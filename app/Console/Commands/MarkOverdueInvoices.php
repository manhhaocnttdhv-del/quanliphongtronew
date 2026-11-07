<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Models\Setting;
use App\Notifications\OverdueInvoiceNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MarkOverdueInvoices extends Command
{
    protected $signature = 'invoices:mark-overdue';

    protected $description = 'Tự động đánh dấu hóa đơn quá hạn và nhắc khách thuê.';

    public function handle(): int
    {
        $invoices = Invoice::query()
            ->whereNull('voided_at')
            ->whereIn('status', ['unpaid', 'partial'])
            ->where('debt', '>', 0)
            ->whereDate('due_date', '<', today())
            ->with(['contract.tenant.user'])
            ->get();

        $count = 0;
        foreach ($invoices as $invoice) {
            DB::transaction(function () use ($invoice, &$count): void {
                $invoice->update(['status' => 'overdue']);

                $user = $invoice->contract?->tenant?->user;
                if ($user && Setting::get('notify_overdue', '1') === '1') {
                    $alreadyNotified = $user->notifications()
                        ->where('type', OverdueInvoiceNotification::class)
                        ->whereJsonContains('data->type', 'overdue_invoice')
                        ->where('data->url', 'LIKE', '%/invoices/' . $invoice->id)
                        ->exists();

                    if (! $alreadyNotified) {
                        $user->notify(new OverdueInvoiceNotification($invoice));
                    }
                }

                $count++;
            });
        }

        $this->info("Đã cập nhật {$count} hóa đơn quá hạn.");

        return self::SUCCESS;
    }
}
