<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReminderDelivery extends Model
{
    public $timestamps = false;

    protected $guarded = ['id'];

    protected $casts = ['sent_at' => 'datetime'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
