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
     * Mối quan hệ: Người thuê hiện tại của phòng (thông qua hợp đồng đang active).
     * Tenants không có room_id trực tiếp — kết nối qua bảng contracts.
     */
    public function tenants()
    {
        return $this->hasManyThrough(
            Tenant::class,
            Contract::class,
            'room_id',    // FK trên contracts → rooms
            'id',         // FK trên tenants (PK)
            'id',         // PK của rooms
            'tenant_id'   // FK trên contracts → tenants
        );
    }

    /**
     * Mối quan hệ: Người thuê đang có hợp đồng active tại phòng này.
     */
    public function activeTenants()
    {
        return $this->hasManyThrough(
            Tenant::class,
            Contract::class,
            'room_id',
            'id',
            'id',
            'tenant_id'
        )->where('contracts.status', 'active');
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
        return $this->belongsToMany(Service::class, 'room_services')->withPivot('custom_price', 'is_active')->withTimestamps();
    }
}
