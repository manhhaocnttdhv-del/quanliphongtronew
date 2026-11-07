<?php

namespace App\Services\Booking;

use App\Models\BookingRequest;
use App\Models\Contract;
use App\Models\Invoice;
use App\Models\Room;
use App\Services\InvoicePaymentService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BookingDepositService
{
    public function createDepositInvoice(BookingRequest $br): Invoice
    {
        return DB::transaction(function () use ($br) {
            Room::lockForUpdate()->findOrFail($br->room_id);
            $contract = Contract::lockForUpdate()->findOrFail($br->contract_id);
            $br = BookingRequest::lockForUpdate()->findOrFail($br->id);
            if ($br->status !== 'approved' || ! $br->isWithinHold() || ! in_array($contract->status, ['draft', 'active'], true)) {
                throw ValidationException::withMessages(['booking' => 'Yêu cầu đặt phòng không còn hiệu lực.']);
            }

            return Invoice::firstOrCreate(['contract_id' => $contract->id, 'type' => 'deposit'], [
                'month' => null,
                'year' => null,
                'room_fee' => 0,
                'electricity_fee' => 0,
                'water_fee' => 0,
                'service_fee' => 0,
                'total' => $br->deposit_amount,
                'paid_amount' => 0,
                'debt' => $br->deposit_amount,
                'due_date' => $br->approved_at->copy()->addHours(48)->toDateString(),
                'status' => (int) $br->deposit_amount === 0 ? 'paid' : 'unpaid',
                'notes' => 'Hóa đơn đặt cọc cho BookingRequest #'.$br->id,
            ]);
        });
    }

    public function markDepositPaid(Invoice $invoice, int $amount, string $referenceCode): void
    {
        if ($invoice->type === 'deposit') {
            app(InvoicePaymentService::class)->record($invoice, $amount, $referenceCode);
        }
    }
}
