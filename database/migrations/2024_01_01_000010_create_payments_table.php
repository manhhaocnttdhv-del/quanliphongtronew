<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->decimal('amount', 12, 0)->comment('Số tiền đóng (VNĐ)');
            $table->enum('method', ['cash', 'transfer', 'other'])
                ->default('cash')
                ->comment('cash: Tiền mặt | transfer: Chuyển khoản | other: Khác');
            $table->string('reference_code')->nullable()->comment('Mã giao dịch ngân hàng');
            $table->timestamp('paid_at')->useCurrent()->comment('Thời điểm thanh toán');
            $table->string('received_by')->nullable()->comment('Người thu tiền');
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
