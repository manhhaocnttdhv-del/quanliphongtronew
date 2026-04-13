<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Pivot: phòng đăng ký những dịch vụ nào (giá có thể ghi đè)
        Schema::create('room_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained('rooms')->cascadeOnDelete();
            $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
            $table->decimal('custom_price', 12, 0)->nullable()
                ->comment('Giá ghi đè riêng cho phòng, NULL = dùng giá mặc định service');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['room_id', 'service_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('room_services');
    }
};
