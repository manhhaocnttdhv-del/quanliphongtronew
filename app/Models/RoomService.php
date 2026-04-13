<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class RoomService extends Pivot
{
    protected $table = 'room_services';
    protected $guarded = ['id'];
}
