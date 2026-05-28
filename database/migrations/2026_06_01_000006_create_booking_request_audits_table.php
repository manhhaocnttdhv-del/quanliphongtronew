<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Tạo bảng `booking_request_audits` — log audit ghi lại các sự kiện
     * trên một `booking_request` xuyên suốt vòng đời (xem design.md mục 2.6).
     *
     * Quan hệ FK:
     *  - booking_request_id → booking_requests (cascadeOnDelete): xoá yêu cầu
     *    thì cũng xoá toàn bộ audit liên quan.
     *  - actor_user_id → users (nullOnDelete): nullable vì có sự kiện do
     *    hệ thống tạo (ví dụ `expired` từ scheduled command).
     *
     * Lưu ý: bảng này chỉ có cột `created_at`, KHÔNG dùng `timestamps()`
     * chuẩn vì audit là bản ghi append-only, không có khái niệm "updated_at".
     */
    public function up(): void
    {
        Schema::create('booking_request_audits', function (Blueprint $table) {
            $table->id();

            $table->foreignId('booking_request_id')
                ->constrained('booking_requests')
                ->cascadeOnDelete();

            // Tên sự kiện: created, approved, rejected, cancelled, expired,
            // documents_uploaded, signed, deposit_paid (xem design 2.6).
            $table->string('event');

            $table->foreignId('actor_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 255)->nullable();

            $table->json('metadata')->nullable();

            // Chỉ lưu thời điểm tạo — audit là append-only.
            $table->timestamp('created_at')->nullable();

            // Indexes phục vụ truy vấn timeline theo booking và lọc theo event.
            $table->index(['booking_request_id', 'event']);
            $table->index(['booking_request_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_request_audits');
    }
};
