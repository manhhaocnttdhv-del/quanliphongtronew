<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentOrder extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'order_code' => 'integer',
        'amount' => 'decimal:0',
        'paid_amount' => 'decimal:0',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
