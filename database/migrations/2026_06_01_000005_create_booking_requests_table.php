<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Tạo bảng `booking_requests` — bảng trung tâm điều phối vòng đời
     * yêu cầu đặt thuê phòng online (xem design.md mục 2.5).
     *
     * Quan hệ FK:
     *  - user_id → users (cascadeOnDelete): chủ yêu cầu, xoá user thì xoá yêu cầu.
     *  - room_id → rooms (cascadeOnDelete): phòng được yêu cầu.
     *  - tenant_id → tenants (nullOnDelete): chỉ set sau khi admin duyệt.
     *  - contract_id → contracts (nullOnDelete): chỉ set sau khi admin duyệt.
     *  - last_status_changed_by → users (nullOnDelete): admin/customer thay đổi trạng thái gần nhất.
     */
    public function up(): void
    {
        Schema::create('booking_requests', function (Blueprint $table) {
            $table->id();

            // Quan hệ chính
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->foreignId('room_id')
                ->constrained('rooms')
                ->cascadeOnDelete();
            $table->foreignId('tenant_id')
                ->nullable()
                ->constrained('tenants')
                ->nullOnDelete();
            $table->foreignId('contract_id')
                ->nullable()
                ->constrained('contracts')
                ->nullOnDelete();

            // Thông tin cá nhân do customer kê khai khi gửi yêu cầu
            $table->string('cccd', 20)->comment('Số CCCD/CMND');
            $table->string('phone', 15)->comment('Số điện thoại liên hệ');
            $table->date('birthday')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->text('address')->nullable()->comment('Địa chỉ thường trú');
            $table->string('hometown')->nullable()->comment('Quê quán');

            // Mong muốn thuê
            $table->date('desired_move_in_date');
            $table->unsignedInteger('desired_occupants')->comment('Số người dự kiến ở');
            $table->unsignedInteger('desired_lease_months')->comment('Số tháng dự kiến thuê (1-36)');

            // Ghi chú & lý do
            $table->text('customer_note')->nullable();
            $table->text('admin_note')->nullable();
            $table->text('rejected_reason')->nullable();

            // Tài chính (set khi duyệt)
            $table->decimal('deposit_amount', 12, 0)->nullable()->comment('Số tiền cọc do admin xác định khi duyệt');

            // Trạng thái vòng đời
            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled', 'expired'])
                ->default('pending');

            // Timestamps lifecycle
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->timestamp('deposit_paid_at')->nullable();

            // Audit cuối cùng (denormalized cho query nhanh)
            $table->foreignId('last_status_changed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('last_status_changed_at')->nullable();

            $table->timestamps();

            // Indexes phục vụ truy vấn dashboard admin/customer
            $table->index(['status', 'created_at']);
            $table->index(['user_id', 'status']);
            $table->index(['room_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_requests');
    }
};
