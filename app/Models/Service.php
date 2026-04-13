<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    /**
     * Mối quan hệ: Một dịch vụ (vd: Tiền rác) có thể được gán cho nhiều phòng.
     */
    public function rooms()
    {
        return $this->belongsToMany(Room::class, 'room_services')->withPivot('quantity', 'price')->withTimestamps();
    }
}
