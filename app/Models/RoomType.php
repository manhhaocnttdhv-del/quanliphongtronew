<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomType extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'default_price' => 'decimal:0',
    ];

    /**
     * Mối quan hệ: Một loại phòng (vd: Phòng VIP, Phòng thường) 
     * áp dụng cho nhiều phòng khác nhau.
     */
    public function rooms()
    {
        return $this->hasMany(Room::class);
    }
}
