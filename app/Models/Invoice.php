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
        return $this->status === 'paid' || (float) $this->debt <= 0;
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
