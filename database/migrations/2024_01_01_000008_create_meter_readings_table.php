<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meter_readings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained('rooms')->cascadeOnDelete();
            $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
            $table->unsignedTinyInteger('month')->comment('Tháng (1-12)');
            $table->unsignedSmallInteger('year')->comment('Năm');
            $table->decimal('old_value', 10, 2)->default(0)->comment('Chỉ số đầu kỳ');
            $table->decimal('new_value', 10, 2)->default(0)->comment('Chỉ số cuối kỳ');
            $table->decimal('consumption', 10, 2)->storedAs('new_value - old_value')
                ->comment('Tiêu thụ = Chỉ số mới - Chỉ số cũ (tính tự động)');
            $table->decimal('unit_price', 12, 0)->default(0)->comment('Đơn giá tại thời điểm ghi');
            $table->decimal('total_amount', 12, 0)->default(0)->comment('Thành tiền = tiêu thụ × đơn giá');
            $table->string('recorded_by')->nullable()->comment('Người ghi chỉ số');
            $table->timestamps();

            // Mỗi phòng chỉ có 1 chỉ số điện/nước mỗi tháng
            $table->unique(['room_id', 'service_id', 'month', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meter_readings');
    }
};
