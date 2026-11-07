<?php

namespace App\Services;

use App\Models\Contract;
use App\Models\Invoice;
use App\Models\ReminderDelivery;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\RentalReminder;
use Illuminate\Support\Facades\DB;

class RentalReminderService
{
    public function send(): int
    {
        if (Setting::get('reminders_enabled', '1') !== '1') {
            return 0;
        }
        $before = max(0, min(30, (int) Setting::get('reminder_before_days', 3)));
        $repeat = max(1, min(30, (int) Setting::get('reminder_repeat_days', 7)));
        $contractDays = max(1, min(90, (int) Setting::get('reminder_contract_days', 30)));
        $count = 0;
        Invoice::where('type', '!=', 'deposit')->whereNull('voided_at')->where('debt', '>', 0)
            ->whereDate('due_date', '<=', today()->addDays($before))->chunkById(100, function ($invoices) use ($repeat, &$count) {
                foreach ($invoices as $candidate) {
                    $count += DB::transaction(function () use ($candidate, $repeat) {
                        $invoice = Invoice::lockForUpdate()->find($candidate->id);
                        if (! $invoice || $invoice->voided_at || $invoice->net_paid_amount >= (int) $invoice->total) {
                            return 0;
                        }
                        $user = $invoice->contract?->tenant?->user;
                        if (! $user) {
                            return 0;
                        }
                        $late = $invoice->due_date->lt(today());
                        $kind = $late ? 'invoice_overdue' : 'invoice_due';
                        $cycle = $late ? (int) floor($invoice->due_date->diffInDays(today()) / $repeat) : 0;

                        return $this->deliver($user, $kind, 'invoice', $invoice->id, $invoice->due_date->toDateString().':'.$cycle, [
                            'title' => $late ? 'Nhắc thanh toán hóa đơn quá hạn' : 'Hóa đơn sắp đến hạn',
                            'message' => 'Phòng '.$invoice->contract->room->name.': còn '.number_format($invoice->debt, 0, ',', '.').' đ. Hạn '.$invoice->due_date->format('d/m/Y').'.',
                            'url' => route('tenant.invoices.show', $invoice),
                        ]);
                    }, 3);
                }
            });
        Contract::where('status', 'active')->whereDate('end_date', '>=', today())
            ->whereDate('end_date', '<=', today()->addDays($contractDays))->chunkById(100, function ($contracts) use (&$count) {
                foreach ($contracts as $candidate) {
                    $count += DB::transaction(function () use ($candidate) {
                        $contract = Contract::lockForUpdate()->find($candidate->id);
                        if (! $contract || $contract->status !== 'active' || $contract->end_date->lt(today())) {
                            return 0;
                        }
                        $recipients = User::role('admin')->get();
                        if ($contract->tenant?->user) {
                            $recipients->push($contract->tenant->user);
                        }
                        $sent = 0;
                        foreach ($recipients->unique('id') as $user) {
                            $sent += $this->deliver($user, 'contract_ending', 'contract', $contract->id, $contract->end_date->toDateString(), [
                                'title' => 'Hợp đồng sắp hết hạn',
                                'message' => 'Phòng '.$contract->room->name.' hết hạn ngày '.$contract->end_date->format('d/m/Y').'.',
                                'url' => route($user->hasRole('admin') ? 'admin.contracts.show' : 'tenant.contracts.show', $contract),
                            ]);
                        }

                        return $sent;
                    }, 3);
                }
            });

        return $count;
    }

    private function deliver(User $user, string $kind, string $subject, int $id, string $cycle, array $message): int
    {
        $key = implode(':', [$user->id, $kind, $id, $cycle]);
        if (ReminderDelivery::where('delivery_key', $key)->exists()) {
            return 0;
        }
        ReminderDelivery::create([
            'delivery_key' => $key, 'user_id' => $user->id, 'kind' => $kind,
            'subject_type' => $subject, 'subject_id' => $id, 'sent_at' => now(),
        ]);
        $user->notify(new RentalReminder($message + [
            'type' => $kind, 'icon' => 'fas fa-clock', 'color' => 'text-warning',
        ]));

        return 1;
    }
}
