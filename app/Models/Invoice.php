<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    
    protected $casts = [
        'due_date' => 'date',
        'month' => 'integer',
        'year' => 'integer',
        'room_fee' => 'decimal:0',
        'electricity_fee' => 'decimal:0',
        'electricity_old' => 'decimal:2',
        'electricity_new' => 'decimal:2',
        'electricity_usage' => 'decimal:2',
        'water_fee' => 'decimal:0',
        'water_old' => 'decimal:2',
        'water_new' => 'decimal:2',
        'water_usage' => 'decimal:2',
        'service_fee' => 'decimal:0',
        'total' => 'decimal:0',
        'paid_amount' => 'decimal:0',
        'debt' => 'decimal:0',
        'refunded_amount' => 'integer',
        'voided_at' => 'datetime',
    ];

    /**
     * Mối quan hệ: Hóa đơn của hợp đồng nào.
     */
    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }

    /**
     * Mối quan hệ: Các khoản thanh toán (Payments) đã giao dịch cho hóa đơn này.
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
    
    /**
     * Mutator/Accessor mẫu: Trả về trạng thái đã thanh toán dựa vào Số dư.
     */
    public function getIsPaidAttribute(): bool
    {
        return ! $this->voided_at && $this->net_paid_amount >= (int) $this->total;
    }

    public function getNetPaidAmountAttribute(): int
    {
        return max(0, (int) $this->paid_amount - (int) $this->refunded_amount);
    }

    public function balanceAttributes(int $total, int $paid): array
    {
        $debt = $this->voided_at ? 0 : max(0, $total - $paid + (int) $this->refunded_amount);
        $status = $debt === 0 ? 'paid' : ($paid > 0 ? 'partial' : 'unpaid');
        if ($debt > 0 && $this->due_date?->lt(today())) {
            $status = 'overdue';
        }

        return ['total' => $total, 'paid_amount' => $paid, 'debt' => $debt, 'status' => $status];
    }

    public function paymentOrders()
    {
        return $this->hasMany(PaymentOrder::class);
    }

    /**
     * Scope: Chỉ lấy hóa đơn đặt cọc (deposit).
     */
    public function scopeDeposit($query)
    {
        return $query->where('type', 'deposit');
    }

    /**
     * Scope: Chỉ lấy hóa đơn hàng tháng (monthly).
     */
    public function scopeMonthly($query)
    {
        return $query->where('type', 'monthly');
    }
}
