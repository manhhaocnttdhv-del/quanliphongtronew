<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingRequest extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'desired_move_in_date' => 'date',
        'birthday' => 'date',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'expired_at' => 'datetime',
        'deposit_paid_at' => 'datetime',
        'last_status_changed_at' => 'datetime',
    ];

    /**
     * Mối quan hệ: Yêu cầu đặt thuê này thuộc về user (khách hàng) nào.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Mối quan hệ: Phòng được yêu cầu thuê.
     */
    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Mối quan hệ: Tenant được tạo sau khi admin duyệt yêu cầu (nullable trước khi duyệt).
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Mối quan hệ: Hợp đồng (draft) được tạo cùng lúc với việc duyệt yêu cầu (nullable trước khi duyệt).
     */
    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }

    /**
     * Mối quan hệ: Lịch sử audit của yêu cầu đặt thuê này.
     */
    public function audits()
    {
        return $this->hasMany(BookingRequestAudit::class);
    }

    /**
     * Helper: Yêu cầu đang chờ admin xử lý.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Helper: Yêu cầu đã được duyệt nhưng chưa hoàn tất thanh toán đặt cọc.
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Helper: Yêu cầu đã ở trạng thái kết thúc (không thể tiếp tục flow).
     */
    public function isFinalized(): bool
    {
        return in_array($this->status, ['cancelled', 'rejected', 'expired']);
    }

    /**
     * Helper: Khách hàng đã upload đầy đủ ảnh CCCD (mặt trước + mặt sau) hay chưa.
     */
    public function hasUploadedDocuments(): bool
    {
        return $this->tenant
            && $this->tenant->cccd_front_path
            && $this->tenant->cccd_back_path;
    }

    /**
     * Helper: Hợp đồng đã được khách hàng ký điện tử hay chưa.
     */
    public function isSigned(): bool
    {
        return $this->contract && $this->contract->signed_at;
    }
}
