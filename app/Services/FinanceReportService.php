<?php

namespace App\Services;

use App\Models\ContractSettlement;
use App\Models\Expense;
use App\Models\Payment;
use App\Models\PaymentResolution;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class FinanceReportService
{
    public function entries(Carbon $start, Carbon $end, ?int $houseId): Collection
    {
        $payments = Payment::with('invoice.contract.room.house')->where('kind', '!=', 'deposit_transfer')->whereBetween('paid_at', [$start, $end])
            ->when($houseId, fn ($q) => $q->whereHas('invoice.contract.room', fn ($q) => $q->where('house_id', $houseId)))->get();
        $entries = $payments->map(function ($payment) {
            $deposit = $payment->invoice->type === 'deposit';
            $applied = $payment->kind === 'deposit_application';

            return $this->entry($payment->paid_at, $payment->invoice->contract->room->house->name, $payment->invoice->contract->room->name,
                $applied ? 'deposit_application' : ($deposit ? 'deposit_received' : 'receipt'),
                'Phiếu thu #'.$payment->id.' / Hóa đơn #'.$payment->invoice_id,
                $applied ? 0 : (int) $payment->amount, 0, $deposit ? 0 : (int) $payment->amount);
        });
        $expenses = Expense::with('house')->whereNull('voided_at')->whereBetween('spent_on', [$start->toDateString(), $end->toDateString()])
            ->when($houseId, fn ($q) => $q->where('house_id', $houseId))->get();
        foreach ($expenses as $expense) {
            $entries->push($this->entry(Carbon::parse($expense->spent_on), $expense->house?->name ?? 'Chi phí chung', '', 'expense',
                Expense::CATEGORIES[$expense->category].': '.$expense->description, 0, (int) $expense->amount, -(int) $expense->amount));
        }
        $refunds = PaymentResolution::with('payment.invoice.contract.room.house')->where('refund_amount', '>', 0)->whereBetween('resolved_at', [$start, $end])
            ->when($houseId, fn ($q) => $q->whereHas('payment.invoice.contract.room', fn ($q) => $q->where('house_id', $houseId)))->get();
        foreach ($refunds as $refund) {
            $room = $refund->payment->invoice->contract->room;
            $deposit = $refund->payment->invoice->type === 'deposit';
            $entries->push($this->entry($refund->resolved_at, $room->house->name, $room->name, $deposit ? 'deposit_refund' : 'income_refund',
                'Hoàn tiền phiếu thu #'.$refund->payment_id.' / '.$refund->reference, 0, (int) $refund->refund_amount, $deposit ? 0 : -(int) $refund->refund_amount));
        }
        $settlements = ContractSettlement::with('contract.room.house')->whereNotNull('refunded_at')->whereBetween('refunded_at', [$start, $end])
            ->when($houseId, fn ($q) => $q->whereHas('contract.room', fn ($q) => $q->where('house_id', $houseId)))->get();
        foreach ($settlements as $settlement) {
            $room = $settlement->contract->room;
            $entries->push($this->entry($settlement->refunded_at, $room->house->name, $room->name, 'deposit_refund',
                'Hoàn cọc quyết toán #'.$settlement->id.' / '.$settlement->refund_reference, 0, (int) $settlement->refund_due, 0));
        }

        return $entries->sortByDesc('date')->values();
    }

    private function entry(Carbon $date, string $house, string $room, string $kind, string $description, int $in, int $out, int $profit): array
    {
        return ['date' => $date->format('Y-m-d H:i:s'), 'house' => $house, 'room' => $room,
            'kind' => $kind, 'description' => $description, 'in' => $in, 'out' => $out, 'profit' => $profit];
    }

    public function summary(Collection $entries): array
    {
        return [
            'receipts' => (int) $entries->where('kind', 'receipt')->sum('in'),
            'expenses' => (int) $entries->where('kind', 'expense')->sum('out'),
            'income_refunds' => (int) $entries->where('kind', 'income_refund')->sum('out'),
            'deposit_received' => (int) $entries->where('kind', 'deposit_received')->sum('in'),
            'deposit_refund' => (int) $entries->where('kind', 'deposit_refund')->sum('out'),
            'deposit_applied' => (int) $entries->where('kind', 'deposit_application')->sum('profit'),
            'profit' => (int) $entries->sum('profit'),
            'cash_change' => (int) $entries->sum('in') - (int) $entries->sum('out'),
        ];
    }
}
