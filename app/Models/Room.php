<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    /**
     * Mối quan hệ: Mỗi phòng phải thuộc về một phần khu trọ (House) cụ thể.
     */
    public function house()
    {
        return $this->belongsTo(House::class);
    }

    /**
     * Mối quan hệ: Phân loại phòng (VIP, Thường,...).
     */
    public function roomType()
    {
        return $this->belongsTo(RoomType::class);
    }

    /**
     * Mối quan hệ: Một phòng có thể có nhiều người thuê cùng lúc (Tenants).
     */
    public function tenants()
    {
        return $this->hasMany(Tenant::class);
    }

    /**
     * Mối quan hệ: Lịch sử hợp đồng thuê của phòng này.
     */
    public function contracts()
    {
        return $this->hasMany(Contract::class);
    }

    /**
     * Mối quan hệ: Các dịch vụ đang được gán cho phòng này (Điện, Nước, Rác,...).
     */
    public function services()
    {
        return $this->belongsToMany(Service::class, 'room_services')->withPivot('quantity', 'price')->withTimestamps();
    }
}
