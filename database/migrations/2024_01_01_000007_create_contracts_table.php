<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained('rooms')->cascadeOnDelete();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->date('start_date')->comment('Ngày bắt đầu hợp đồng');
            $table->date('end_date')->comment('Ngày kết thúc hợp đồng');
            $table->decimal('deposit', 12, 0)->default(0)->comment('Tiền cọc (VNĐ)');
            $table->decimal('monthly_price', 12, 0)->comment('Tiền phòng theo hợp đồng (VNĐ/tháng)');
            $table->integer('occupants')->default(1)->comment('Số người ở thực tế');
            $table->string('pdf_path')->nullable()->comment('Đường dẫn file PDF hợp đồng');
            $table->enum('status', ['active', 'expired', 'terminated'])
                ->default('active')
                ->comment('active: Đang hiệu lực | expired: Hết hạn | terminated: Đã thanh lý');
            $table->date('terminated_at')->nullable()->comment('Ngày thanh lý sớm');
            $table->decimal('deposit_refund', 12, 0)->default(0)->comment('Tiền cọc hoàn lại khi thanh lý');
            $table->text('notes')->nullable()->comment('Ghi chú điều khoản thêm');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};
