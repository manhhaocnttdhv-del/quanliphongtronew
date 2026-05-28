<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Mở rộng enum `rooms.status` để thêm trạng thái `reserved` phục vụ luồng
     * giữ chỗ sau khi Admin duyệt yêu cầu đặt thuê online.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE rooms MODIFY COLUMN status ENUM('available','reserved','rented','maintenance') NOT NULL DEFAULT 'available'");
    }

    /**
     * Reverse the migrations.
     *
     * Trước khi thu hẹp enum về tập giá trị cũ, đưa các bản ghi đang ở trạng thái
     * `reserved` về `available` để tránh lỗi truncate của MySQL khi ALTER ENUM.
     */
    public function down(): void
    {
        DB::table('rooms')->where('status', 'reserved')->update(['status' => 'available']);

        DB::statement("ALTER TABLE rooms MODIFY COLUMN status ENUM('available','rented','maintenance') NOT NULL DEFAULT 'available'");
    }
};
