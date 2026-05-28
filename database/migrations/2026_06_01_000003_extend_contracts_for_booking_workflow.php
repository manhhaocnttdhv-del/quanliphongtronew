<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Mở rộng bảng `contracts` để hỗ trợ luồng đặt phòng online:
     *  - Thêm các cột phục vụ ký xác nhận điện tử (signature_path, signed_at,
     *    signed_ip, signed_user_agent).
     *  - Mở rộng enum `status` thêm hai trạng thái `draft` (hợp đồng vừa được
     *    Admin duyệt, chưa thanh toán cọc) và `cancelled` (hợp đồng bị huỷ do
     *    yêu cầu đặt phòng hết hạn / bị huỷ).
     */
    public function up(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->string('signature_path')->nullable()->after('notes')
                ->comment('Đường dẫn ảnh chữ ký điện tử của khách thuê');
            $table->timestamp('signed_at')->nullable()->after('signature_path')
                ->comment('Thời điểm khách ký xác nhận hợp đồng');
            $table->string('signed_ip', 45)->nullable()->after('signed_at')
                ->comment('IP của khách khi ký');
            $table->string('signed_user_agent', 255)->nullable()->after('signed_ip')
                ->comment('User agent của khách khi ký');
        });

        DB::statement("ALTER TABLE contracts MODIFY COLUMN status ENUM('draft','active','expired','terminated','cancelled') NOT NULL DEFAULT 'active' COMMENT 'draft: Hợp đồng nháp chờ đặt cọc | active: Đang hiệu lực | expired: Hết hạn | terminated: Đã thanh lý | cancelled: Đã huỷ'");
    }

    /**
     * Reverse the migrations.
     *
     * Trước khi thu hẹp enum về tập giá trị cũ, đưa các bản ghi đang ở
     * trạng thái `draft` hoặc `cancelled` về `terminated` để tránh lỗi
     * truncate của MySQL khi ALTER ENUM. Sau đó mới drop các cột phụ trợ
     * cho luồng ký điện tử.
     */
    public function down(): void
    {
        DB::table('contracts')
            ->whereIn('status', ['draft', 'cancelled'])
            ->update(['status' => 'terminated']);

        DB::statement("ALTER TABLE contracts MODIFY COLUMN status ENUM('active','expired','terminated') NOT NULL DEFAULT 'active' COMMENT 'active: Đang hiệu lực | expired: Hết hạn | terminated: Đã thanh lý'");

        Schema::table('contracts', function (Blueprint $table) {
            $table->dropColumn([
                'signature_path',
                'signed_at',
                'signed_ip',
                'signed_user_agent',
            ]);
        });
    }
};
