<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    
    protected $casts = [
        'amount' => 'decimal:0',
        'paid_at' => 'datetime',
    ];

    /**
     * Mối quan hệ: Bản ghi thanh toán này trả cho hóa đơn (Invoice) nào.
     */
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
