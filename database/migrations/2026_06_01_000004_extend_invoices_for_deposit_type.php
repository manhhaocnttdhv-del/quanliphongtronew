<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Mở rộng `invoices` để hỗ trợ hóa đơn cọc (`type=deposit`) song song
     * với hóa đơn tháng (`type=monthly`). Cho phép `month`/`year` NULL với
     * hóa đơn cọc, đồng thời cập nhật ràng buộc unique.
     *
     * Lưu ý quan trọng: KHÔNG drop unique cũ `(contract_id, month, year)`
     * trước, vì cột `contract_id` là foreign key và unique đó là index duy
     * nhất phủ leftmost `contract_id` → MySQL sẽ throw 1553 "Cannot drop
     * index needed in a foreign key constraint". Do đó:
     *   1. Tạo unique mới `(contract_id, type, month, year)` trước
     *      (cũng phủ leftmost `contract_id`, đủ cho FK).
     *   2. Sau đó mới drop unique cũ.
     *
     * Migration cũng idempotent để retry sau khi chạy fail giữa chừng.
     */
    public function up(): void
    {
        // 1. Thêm cột `type` nếu chưa có (idempotent cho retry sau fail).
        if (! Schema::hasColumn('invoices', 'type')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->enum('type', ['monthly', 'deposit'])
                    ->default('monthly')
                    ->after('contract_id')
                    ->comment('monthly: Hóa đơn tháng | deposit: Hóa đơn đặt cọc');
            });
        }

        // 2. Đổi `month`, `year` sang NULL để hóa đơn deposit không cần kỳ.
        DB::statement("ALTER TABLE invoices MODIFY COLUMN month TINYINT UNSIGNED NULL COMMENT 'Tháng hóa đơn (1-12)'");
        DB::statement("ALTER TABLE invoices MODIFY COLUMN year SMALLINT UNSIGNED NULL COMMENT 'Năm hóa đơn'");

        // 3. Tạo unique mới TRƯỚC khi drop cái cũ — đảm bảo luôn có index
        //    phủ leftmost `contract_id` cho ràng buộc FK.
        if (! $this->indexExists('invoices', 'invoices_contract_type_month_year_unique')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->unique(
                    ['contract_id', 'type', 'month', 'year'],
                    'invoices_contract_type_month_year_unique'
                );
            });
        }

        // 4. Drop unique cũ — giờ FK đã có index thay thế.
        if ($this->indexExists('invoices', 'invoices_contract_id_month_year_unique')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->dropUnique('invoices_contract_id_month_year_unique');
            });
        }
    }

    public function down(): void
    {
        // Xoá hóa đơn deposit (không hợp lệ với schema cũ vì month/year NOT NULL).
        if (Schema::hasColumn('invoices', 'type')) {
            DB::table('invoices')->where('type', 'deposit')->delete();
        }

        // Tạo lại unique cũ TRƯỚC khi drop cái mới (cùng lý do FK ở trên).
        if (! $this->indexExists('invoices', 'invoices_contract_id_month_year_unique')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->unique(['contract_id', 'month', 'year']);
            });
        }

        if ($this->indexExists('invoices', 'invoices_contract_type_month_year_unique')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->dropUnique('invoices_contract_type_month_year_unique');
            });
        }

        DB::statement("ALTER TABLE invoices MODIFY COLUMN month TINYINT UNSIGNED NOT NULL COMMENT 'Tháng hóa đơn (1-12)'");
        DB::statement("ALTER TABLE invoices MODIFY COLUMN year SMALLINT UNSIGNED NOT NULL COMMENT 'Năm hóa đơn'");

        if (Schema::hasColumn('invoices', 'type')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->dropColumn('type');
            });
        }
    }

    /**
     * Kiểm tra một index theo tên có tồn tại trên table hay không.
     */
    private function indexExists(string $table, string $indexName): bool
    {
        $database = DB::getDatabaseName();
        $row = DB::selectOne(
            'SELECT COUNT(*) AS c FROM information_schema.statistics
             WHERE table_schema = ? AND table_name = ? AND index_name = ?',
            [$database, $table, $indexName]
        );
        return (int) ($row->c ?? 0) > 0;
    }
};
