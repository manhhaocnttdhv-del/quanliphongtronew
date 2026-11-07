<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    public const CATEGORIES = [
        'repair' => 'Sửa chữa', 'utilities' => 'Điện nước chung', 'cleaning' => 'Vệ sinh',
        'salary' => 'Nhân công', 'other' => 'Khác',
    ];

    protected $guarded = ['id'];

    protected $casts = ['amount' => 'integer', 'spent_on' => 'date', 'voided_at' => 'datetime'];

    public function house()
    {
        return $this->belongsTo(House::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
