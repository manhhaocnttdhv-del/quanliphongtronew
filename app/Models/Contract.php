<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contract extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    /**
     * Các trường ngày tháng để Laravel tự động cast về đối tượng Carbon.
     */
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * Mối quan hệ: Hợp đồng này ký cho phòng nào.
     */
    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Mối quan hệ: Hợp đồng này là của khách thuê nào.
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Mối quan hệ: Dữ liệu ghi điện/nước liên quan đến hợp đồng này.
     */
    public function meterReadings()
    {
        return $this->hasMany(MeterReading::class);
    }

    /**
     * Mối quan hệ: Hóa đơn được tạo dựa trên hợp đồng này.
     */
    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
}
