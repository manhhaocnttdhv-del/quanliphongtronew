<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model `BookingRequestAudit` — log audit append-only cho từng `BookingRequest`.
 *
 * Một bản ghi audit ứng với một sự kiện trong vòng đời booking (created,
 * approved, rejected, cancelled, expired, documents_uploaded, signed,
 * deposit_paid…). Chỉ có cột `created_at`, không có `updated_at`, vì audit
 * là bất biến — xem design.md mục 2.6 và 3.2.
 *
 * @property int         $id
 * @property int         $booking_request_id
 * @property string      $event
 * @property int|null    $actor_user_id     null khi sự kiện do hệ thống tạo
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property array|null  $metadata
 * @property \Illuminate\Support\Carbon|null $created_at
 */
class BookingRequestAudit extends Model
{
    /**
     * Cho phép mass-assign tất cả ngoại trừ khoá chính.
     * Audit được tạo nội bộ qua service nên không cần whitelist chặt.
     */
    protected $guarded = ['id'];

    /**
     * Bảng audit chỉ có `created_at`, không dùng cặp timestamps mặc định
     * của Eloquent (vốn yêu cầu thêm `updated_at`).
     */
    public $timestamps = false;

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'metadata'   => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Audit này thuộc về một BookingRequest (cascade delete khi xoá booking).
     */
    public function bookingRequest()
    {
        return $this->belongsTo(BookingRequest::class);
    }

    /**
     * Người thực hiện sự kiện. Có thể null nếu sự kiện do hệ thống sinh
     * (ví dụ scheduled command đánh dấu `expired`).
     */
    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }
}
