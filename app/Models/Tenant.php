<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    /**
     * Mối quan hệ: Một người thuê sẽ gắn với một phòng cụ thể hiện tại (nếu có).
     */
    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Mối quan hệ: Nối với tài khoản User (để khách thuê có thể đăng nhập).
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Mối quan hệ: Hợp đồng của khách thuê.
     */
    public function contracts()
    {
        return $this->hasMany(Contract::class);
    }

    /**
     * Mối quan hệ: Lịch sử hóa đơn của khách thuê này.
     */
    public function invoices()
    {
        return $this->hasManyThrough(Invoice::class, Contract::class);
    }
}
