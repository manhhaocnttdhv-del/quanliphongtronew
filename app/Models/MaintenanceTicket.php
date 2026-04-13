<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintenanceTicket extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    /**
     * Mối quan hệ: Sự cố thuộc về Hợp đồng nào (có thể lấy Room và Tenant thông qua đây).
     */
    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }

    /**
     * Helper: Lấy Room thông qua Contract.
     */
    public function getRoom()
    {
        return $this->contract?->room;
    }

    /**
     * Helper: Lấy Tenant thông qua Contract.
     */
    public function getTenant()
    {
        return $this->contract?->tenant;
    }
}
