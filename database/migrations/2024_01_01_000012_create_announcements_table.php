<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('house_id')->nullable()->constrained('houses')->nullOnDelete()
                ->comment('NULL = Thông báo toàn hệ thống, có house_id = Thông báo riêng cho dãy trọ đó');
            $table->string('title');
            $table->text('content');
            $table->enum('type', ['notice', 'warning', 'event'])
                ->default('notice')
                ->comment('notice: Thông báo | warning: Cảnh báo | event: Sự kiện (VD: vệ sinh chung)');
            $table->boolean('is_pinned')->default(false)->comment('Ghim lên đầu danh sách');
            $table->timestamp('published_at')->nullable()->comment('NULL = Lưu nháp, có giá trị = Đã đăng');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};
