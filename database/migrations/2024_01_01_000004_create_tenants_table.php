<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('cccd', 20)->nullable()->unique()->comment('Số CCCD/CMND');
            $table->string('phone', 15)->nullable();
            $table->text('address')->nullable()->comment('Địa chỉ thường trú');
            $table->string('hometown')->nullable()->comment('Quê quán');
            $table->date('birthday')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->string('avatar_path')->nullable()->comment('Ảnh đại diện');
            $table->string('cccd_front_path')->nullable()->comment('Ảnh CCCD mặt trước');
            $table->string('cccd_back_path')->nullable()->comment('Ảnh CCCD mặt sau');
            $table->string('emergency_contact_name')->nullable()->comment('Người liên hệ khẩn cấp');
            $table->string('emergency_contact_phone', 15)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
