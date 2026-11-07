<?php

namespace App\Services;

use App\Models\Contract;
use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class InvoiceCalculationService
{
    public function rates(Contract $contract): array
    {
        $rates = ['electricity_price' => 0, 'water_price' => 0, 'service_fee' => 0];
        foreach ($contract->room->services as $service) {
            if (! $service->is_active || ! $service->pivot->is_active) {
                continue;
            }
            $price = (int) ($service->pivot->custom_price ?? $service->price);
            if (in_array($service->type, ['electricity', 'water'], true)) {
                $rates[$service->type.'_price'] += $price;
            } else {
                $rates['service_fee'] += $price;
            }
        }

        return $rates;
    }

    public function previous(Contract $contract, Carbon $period): ?Invoice
    {
        return $contract->invoices()->where('type', 'monthly')
            ->where(fn ($q) => $q->where('year', '<', $period->year)
                ->orWhere(fn ($q) => $q->where('year', $period->year)->where('month', '<', $period->month)))
            ->orderByDesc('year')->orderByDesc('month')->first();
    }

    public function utilities(array $input, array $rates): array
    {
        foreach (['electricity', 'water'] as $type) {
            if ($input[$type.'_new'] < $input[$type.'_old']) {
                throw ValidationException::withMessages([$type.'_new' => 'Chỉ số mới không được nhỏ hơn chỉ số cũ.']);
            }
        }
        $electricityUsage = round($input['electricity_new'] - $input['electricity_old'], 2);
        $waterUsage = round($input['water_new'] - $input['water_old'], 2);

        return [
            'electricity_old' => $input['electricity_old'], 'electricity_new' => $input['electricity_new'],
            'water_old' => $input['water_old'], 'water_new' => $input['water_new'],
            'electricity_usage' => $electricityUsage, 'water_usage' => $waterUsage,
            'electricity_fee' => (int) round($electricityUsage * $rates['electricity_price']),
            'water_fee' => (int) round($waterUsage * $rates['water_price']),
        ];
    }

    public function monthly(Contract $contract, array $input, Carbon $period, string $dueDate): array
    {
        if ($contract->status !== 'active' || $contract->settlement()->exists()
            || $contract->start_date->gt($period->copy()->endOfMonth())
            || $contract->end_date->lt($period)) {
            throw ValidationException::withMessages(['rows' => 'Hợp đồng #'.$contract->id.' không có hiệu lực trong kỳ này.']);
        }
        if ($contract->invoices()->monthly()->where('month', $period->month)->where('year', $period->year)->exists()) {
            throw ValidationException::withMessages(['rows' => 'Hợp đồng #'.$contract->id.' đã có hóa đơn trong kỳ này.']);
        }
        if ($contract->invoices()->monthly()->where(fn ($q) => $q->where('year', '>', $period->year)
            ->orWhere(fn ($q) => $q->where('year', $period->year)->where('month', '>', $period->month)))->exists()) {
            throw ValidationException::withMessages(['rows' => 'Đã có kỳ hóa đơn sau kỳ đang chọn. Vui lòng kiểm tra lại chỉ số.']);
        }
        $previous = $this->previous($contract, $period);
        foreach (['electricity', 'water'] as $type) {
            if ($previous && (float) $input[$type.'_old'] !== (float) $previous->{$type.'_new'}) {
                throw ValidationException::withMessages(['rows' => 'Chỉ số cũ đã thay đổi. Vui lòng tải lại bảng.']);
            }
        }
        $rates = $this->rates($contract);
        $amounts = $this->utilities($input, $rates);
        $amounts['room_fee'] = (int) $contract->monthly_price;
        $amounts['service_fee'] = $rates['service_fee'];
        $total = $amounts['room_fee'] + $amounts['service_fee'] + $amounts['electricity_fee'] + $amounts['water_fee'];
        if ($total > 999999999999) {
            throw ValidationException::withMessages(['rows' => 'Tổng hóa đơn vượt giới hạn cho phép.']);
        }
        $invoice = new Invoice(['due_date' => $dueDate]);

        return $amounts + $invoice->balanceAttributes($total, 0) + [
            'contract_id' => $contract->id, 'type' => 'monthly', 'month' => $period->month,
            'year' => $period->year, 'due_date' => $dueDate,
        ];
    }
}
