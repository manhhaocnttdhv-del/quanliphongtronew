<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class House extends Model
{
    use HasFactory;

    /**
     * Thuộc tính được phép gán hàng loạt.
     * Sử dụng guarded rỗng để fillable tất cả các cột trừ id.
     */
    protected $guarded = ['id'];

    /**
     * Mối quan hệ: Một khu trọ (House) sẽ có nhiều phòng (Rooms).
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function rooms()
    {
        return $this->hasMany(Room::class);
    }

    /**
     * Mối quan hệ: Lấy danh sách tất cả những người thuê (Tenants) 
     * đang ở tại khu trọ này thông qua các phòng.
     */
    public function tenants()
    {
        return $this->hasManyThrough(Tenant::class, Room::class);
    }
}
