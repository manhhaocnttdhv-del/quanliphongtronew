<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    /**
     * Mối quan hệ: Thông báo thuộc về khu trọ nào (null = toàn hệ thống).
     */
    public function house()
    {
        return $this->belongsTo(House::class);
    }

    /**
     * Mối quan hệ: Người đăng bản tin (Admin).
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
