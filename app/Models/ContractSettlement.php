<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContractSettlement extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'checkout_date' => 'date', 'breakdown' => 'array', 'deposit_held' => 'integer',
        'deposit_applied' => 'integer', 'refund_due' => 'integer', 'cash_due' => 'integer',
        'refunded_at' => 'datetime',
    ];

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
