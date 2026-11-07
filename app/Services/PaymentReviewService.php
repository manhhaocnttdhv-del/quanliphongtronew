<?php

namespace App\Services;

use App\Models\BookingRequest;
use App\Models\Contract;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentResolution;
use App\Models\Room;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaymentReviewService
{
    public function refundable(Payment $payment): int
    {
        $invoice = $payment->invoice;
        $contract = $invoice->contract;
        if ($invoice->type === 'deposit' && $contract->transferred_to_contract_id) {
            return min((int) $payment->amount, max(0, $invoice->net_paid_amount - (int) $contract->deposit_transferred));
        }
        $settlement = $invoice->type === 'deposit' ? $contract->settlement : null;
        if ($settlement) {
            $reserved = $settlement->deposit_applied + ($settlement->refunded_at ? 0 : $settlement->refund_due);

            return min((int) $payment->amount, max(0, $invoice->net_paid_amount - $reserved));
        }
        $booking = BookingRequest::where('contract_id', $contract->id)->first();
        $cancelledDeposit = $invoice->type === 'deposit' && $contract->status !== 'active'
            && ($contract->status === 'cancelled' || $booking?->isFinalized() || ($booking && ! $booking->isWithinHold()));
        $limit = $cancelledDeposit ? $invoice->net_paid_amount : max(0, $invoice->net_paid_amount - (int) $invoice->total);

        return min((int) $payment->amount, $limit);
    }

    public function resolve(Payment $payment, array $data, int $actor): PaymentResolution
    {
        return DB::transaction(function () use ($payment, $data, $actor) {
            $contractId = $payment->invoice->contract_id;
            Room::lockForUpdate()->findOrFail(Contract::findOrFail($contractId)->room_id);
            $contract = Contract::lockForUpdate()->findOrFail($contractId);
            $booking = BookingRequest::where('contract_id', $contractId)->lockForUpdate()->first();
            $invoice = Invoice::lockForUpdate()->findOrFail($payment->invoice_id);
            $payment = Payment::lockForUpdate()->findOrFail($payment->id);
            if (! $payment->requires_review || $payment->resolution()->exists()) {
                throw ValidationException::withMessages(['payment' => 'Khoản thu đã được đối soát.']);
            }
            $payment->setRelation('invoice', $invoice->setRelation('contract', $contract));
            $refund = $data['outcome'] === 'refund' ? (int) $data['refund_amount'] : 0;
            if ($refund > $this->refundable($payment) || ($data['outcome'] === 'refund' && $refund < 1)) {
                throw ValidationException::withMessages(['refund_amount' => 'Số hoàn vượt khoản tiền có thể hoàn.']);
            }
            if ($data['outcome'] === 'refund' && (empty($data['reference']) || empty($data['evidence_path']))) {
                throw ValidationException::withMessages(['evidence' => 'Cần mã tham chiếu và bằng chứng đã hoàn tiền.']);
            }
            $resolution = PaymentResolution::create([
                'payment_id' => $payment->id, 'outcome' => $data['outcome'], 'refund_amount' => $refund,
                'reference' => $data['reference'] ?? null, 'evidence_path' => $data['evidence_path'] ?? null,
                'reason' => $data['reason'], 'resolved_by' => $actor, 'resolved_at' => now(),
            ]);
            $payment->update(['requires_review' => false]);
            $invoice->refunded_amount = (int) $invoice->refunded_amount + $refund;
            if ($invoice->type === 'deposit' && $contract->status !== 'active'
                && ($contract->status === 'cancelled' || $booking?->isFinalized() || ($booking && ! $booking->isWithinHold()))) {
                $invoice->voided_at = now();
            }
            $invoice->fill($invoice->balanceAttributes((int) $invoice->total, (int) $invoice->paid_amount))->save();
            if ($payment->payment_order_id) {
                \App\Models\PaymentOrder::whereKey($payment->payment_order_id)->update([
                    'status' => $invoice->voided_at ? 'refunded' : ($invoice->net_paid_amount >= (int) $invoice->total ? 'paid' : 'pending'),
                ]);
            }

            return $resolution;
        }, 3);
    }
}
