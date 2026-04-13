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
    public function getIsPaidAttribute()
    {
        return strtolower($this->status) === 'paid' || $this->amount_due <= 0;
    }
}
