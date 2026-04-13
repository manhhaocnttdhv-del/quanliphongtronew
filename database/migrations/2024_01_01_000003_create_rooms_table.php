<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('house_id')->constrained('houses')->cascadeOnDelete();
            $table->foreignId('room_type_id')->nullable()->constrained('room_types')->nullOnDelete();
            $table->string('name')->comment('Tên/Số phòng, VD: P101, P201');
            $table->integer('floor')->default(1)->comment('Tầng');
            $table->decimal('price', 12, 0)->comment('Giá thuê thực tế (VNĐ/tháng)');
            $table->decimal('area', 8, 2)->nullable()->comment('Diện tích (m²)');
            $table->integer('max_occupants')->default(4)->comment('Số người tối đa');
            $table->enum('status', ['available', 'rented', 'maintenance'])
                ->default('available')
                ->comment('available: Trống | rented: Đang thuê | maintenance: Đang sửa');
            $table->text('description')->nullable();
            $table->string('image_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
