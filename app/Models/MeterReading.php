<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MeterReading extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    /**
     * Mối quan hệ: Chỉ số thuộc về phòng nào đó.
     */
    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Mối quan hệ: Chỉ số thuộc về dịch vụ nào (Điện, Nước).
     */
    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Mối quan hệ: Liên kết với bản ghi hợp đồng khi chốt điện nước.
     */
    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }
}
