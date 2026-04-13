<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained('contracts')->cascadeOnDelete();
            $table->string('title')->comment('Tiêu đề sự cố, VD: Bóng đèn phòng vệ sinh bị cháy');
            $table->text('description')->nullable()->comment('Mô tả chi tiết');
            $table->string('image_path')->nullable()->comment('Ảnh chụp sự cố');
            $table->enum('priority', ['low', 'medium', 'high'])
                ->default('medium')
                ->comment('Mức độ ưu tiên');
            $table->enum('status', ['pending', 'in_progress', 'done', 'cancelled'])
                ->default('pending')
                ->comment('pending: Chờ xử lý | in_progress: Đang sửa | done: Xong | cancelled: Huỷ');
            $table->text('admin_response')->nullable()->comment('Phản hồi từ Admin/Chủ nhà');
            $table->timestamp('resolved_at')->nullable()->comment('Thời điểm hoàn thành sửa chữa');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_tickets');
    }
};
