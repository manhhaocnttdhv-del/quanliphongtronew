<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentResolution extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['refund_amount' => 'integer', 'resolved_at' => 'datetime'];

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    public function resolver()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }
}
