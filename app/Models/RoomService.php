<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class RoomService extends Pivot
{
    protected $table = 'room_services';
    protected $guarded = ['id'];

    protected $casts = [
        'custom_price' => 'decimal:0',
        'is_active' => 'boolean',
    ];
}
