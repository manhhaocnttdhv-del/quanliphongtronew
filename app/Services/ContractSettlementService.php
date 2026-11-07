<?php

namespace App\Services;

use App\Models\BookingRequest;
use App\Models\Contract;
use App\Models\ContractSettlement;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Room;
use App\Services\Booking\RoomStatusSyncService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ContractSettlementService
{
    public function availableDeposit(Contract $contract): int
    {
        if ($contract->transferred_to_contract_id) {
            return 0;
        }
        $deposit = $contract->invoices()->deposit()->first();

        return max(0, min((int) $contract->deposit, $deposit ? $deposit->net_paid_amount : (int) $contract->deposit) - (int) $contract->deposit_refund);
    }

    public function calculate(Contract $contract, array $input): array
    {
        if ($contract->status !== 'active' || $contract->settlement()->exists()) {
            throw ValidationException::withMessages(['contract' => 'Chỉ quyết toán một lần cho hợp đồng đang hoạt động.']);
        }
        $date = Carbon::parse($input['checkout_date'])->startOfDay();
        if ($date->lt($contract->start_date) || $date->gt(today())) {
            throw ValidationException::withMessages(['checkout_date' => 'Ngày trả phòng phải từ ngày nhận phòng đến hôm nay.']);
        }
        if ($contract->invoices()->whereHas('payments', fn ($q) => $q->where('requires_review', true))->exists()) {
            throw ValidationException::withMessages(['contract' => 'Cần hoàn tất đối soát các khoản thu trước khi quyết toán.']);
        }
        if ($contract->invoices()->monthly()->where(fn ($q) => $q->where('year', '>', $date->year)
            ->orWhere(fn ($q) => $q->where('year', $date->year)->where('month', '>', $date->month)))->exists()) {
            throw ValidationException::withMessages(['contract' => 'Có hóa đơn kỳ sau ngày trả phòng; cần điều chỉnh trước khi quyết toán.']);
        }
        $latest = $contract->invoices()->monthly()->orderByDesc('year')->orderByDesc('month')->first();
        foreach (['electricity', 'water'] as $type) {
            if ($latest && (float) $input[$type.'_old'] !== (float) $latest->{$type.'_new'}) {
                throw ValidationException::withMessages([$type.'_old' => 'Chỉ số cũ phải khớp hóa đơn gần nhất.']);
            }
        }
        $calculator = app(InvoiceCalculationService::class);
        $rates = $calculator->rates($contract);
        $attributes = $calculator->utilities($input, $rates);
        $billed = $contract->invoices()->monthly()->where('month', $date->month)->where('year', $date->year)->exists();
        $start = $contract->start_date->gt($date->copy()->startOfMonth()) ? $contract->start_date : $date->copy()->startOfMonth();
        $days = $start->diffInDays($date) + 1;
        $attributes['room_fee'] = $billed ? 0 : (int) round((int) $contract->monthly_price * $days / $date->daysInMonth);
        $attributes['service_fee'] = ($billed ? 0 : $rates['service_fee']) + (int) $input['damage_fee'] + (int) $input['other_fee'];
        $total = $attributes['room_fee'] + $attributes['service_fee'] + $attributes['electricity_fee'] + $attributes['water_fee'];
        $held = (int) $input['deposit_held'];
        if ($held > $this->availableDeposit($contract)) {
            throw ValidationException::withMessages(['deposit_held' => 'Cọc đang giữ vượt số cọc thực nhận còn lại.']);
        }
        $debts = $contract->invoices()->where('type', '!=', 'deposit')->whereNull('voided_at')->where('debt', '>', 0)->orderBy('id')->get();
        $oldDebt = (int) $debts->sum('debt');
        if ($total + $oldDebt > 999999999999) {
            throw ValidationException::withMessages(['contract' => 'Tổng quyết toán vượt giới hạn cho phép.']);
        }
        $applied = min($held, $oldDebt + $total);

        return [
            'invoice' => $attributes + ['total' => $total],
            'old_debts' => $debts->map(fn ($invoice) => ['id' => $invoice->id, 'debt' => (int) $invoice->debt])->all(),
            'old_debt' => $oldDebt, 'damage_fee' => (int) $input['damage_fee'], 'other_fee' => (int) $input['other_fee'],
            'already_billed' => $billed, 'rent_days' => $billed ? 0 : $days,
            'deposit_held' => $held, 'deposit_applied' => $applied,
            'refund_due' => $held - $applied, 'cash_due' => $oldDebt + $total - $applied,
        ];
    }

    public function finalize(Contract $contract, array $input, array $expected, int $actor): ContractSettlement
    {
        return DB::transaction(function () use ($contract, $input, $expected, $actor) {
            $room = Room::lockForUpdate()->findOrFail($contract->room_id);
            $contract = Contract::lockForUpdate()->findOrFail($contract->id);
            BookingRequest::where('contract_id', $contract->id)->lockForUpdate()->get();
            $invoices = $contract->invoices()->orderBy('id')->lockForUpdate()->get();
            $breakdown = $this->calculate($contract, $input);
            if ($breakdown != $expected) {
                throw ValidationException::withMessages(['contract' => 'Công nợ hoặc biểu phí đã thay đổi. Vui lòng xem trước lại.']);
            }
            $date = Carbon::parse($input['checkout_date']);
            $invoice = Invoice::create($breakdown['invoice'] + [
                'contract_id' => $contract->id, 'type' => 'settlement', 'month' => $date->month, 'year' => $date->year,
                'paid_amount' => 0, 'debt' => $breakdown['invoice']['total'], 'due_date' => $date->toDateString(),
                'status' => $breakdown['invoice']['total'] === 0 ? 'paid' : 'unpaid',
                'notes' => 'Quyết toán trả phòng. '.$input['handover_notes'],
            ]);
            $remaining = $breakdown['deposit_applied'];
            foreach ($invoices->where('type', '!=', 'deposit')->push($invoice) as $bill) {
                if ($remaining <= 0 || $bill->voided_at || (int) $bill->debt <= 0) {
                    continue;
                }
                $amount = min($remaining, (int) $bill->debt);
                Payment::create([
                    'invoice_id' => $bill->id, 'amount' => $amount, 'method' => 'other', 'kind' => 'deposit_application',
                    'paid_at' => now(), 'received_by' => (string) $actor, 'note' => 'Bù trừ tiền cọc khi trả phòng.',
                ]);
                $bill->update($bill->balanceAttributes((int) $bill->total, (int) $bill->paid_amount + $amount));
                $remaining -= $amount;
            }
            $settlement = ContractSettlement::create([
                'contract_id' => $contract->id, 'invoice_id' => $invoice->id, 'checkout_date' => $date,
                'breakdown' => $breakdown, 'deposit_held' => $breakdown['deposit_held'],
                'deposit_applied' => $breakdown['deposit_applied'], 'refund_due' => $breakdown['refund_due'],
                'cash_due' => $breakdown['cash_due'], 'handover_notes' => $input['handover_notes'], 'created_by' => $actor,
            ]);
            foreach ($invoices->where('type', 'deposit') as $deposit) {
                $deposit->update(['voided_at' => now(), 'debt' => 0]);
            }
            $contract->update(['status' => 'terminated', 'terminated_at' => $date]);
            app(RoomStatusSyncService::class)->recompute($room);
            app(RoomStatusSyncService::class)->syncTenantRole($contract->tenant);

            return $settlement;
        }, 3);
    }

    public function confirmRefund(ContractSettlement $settlement, array $data, int $actor): void
    {
        DB::transaction(function () use ($settlement, $data, $actor) {
            Room::lockForUpdate()->findOrFail($settlement->contract->room_id);
            $contract = Contract::lockForUpdate()->findOrFail($settlement->contract_id);
            $settlement = ContractSettlement::lockForUpdate()->findOrFail($settlement->id);
            if ($settlement->refund_due <= 0 || $settlement->refunded_at) {
                throw ValidationException::withMessages(['refund' => 'Khoản hoàn cọc đã hoàn tất hoặc không có tiền phải hoàn.']);
            }
            $settlement->update([
                'refunded_at' => now(), 'refunded_by' => $actor, 'refund_reference' => $data['reference'],
                'refund_evidence_path' => $data['evidence_path'],
            ]);
            $contract->update(['deposit_refund' => $settlement->refund_due]);
            $deposit = $contract->invoices()->deposit()->lockForUpdate()->first();
            if ($deposit) {
                $deposit->update(['refunded_amount' => (int) $deposit->refunded_amount + $settlement->refund_due]);
            }
        }, 3);
    }
}
