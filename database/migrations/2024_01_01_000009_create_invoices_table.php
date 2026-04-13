<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained('contracts')->cascadeOnDelete();
            $table->unsignedTinyInteger('month')->comment('Tháng hóa đơn (1-12)');
            $table->unsignedSmallInteger('year')->comment('Năm hóa đơn');
            $table->decimal('room_fee', 12, 0)->comment('Tiền phòng tháng này');
            $table->decimal('electricity_fee', 12, 0)->default(0)->comment('Tiền điện');
            $table->decimal('water_fee', 12, 0)->default(0)->comment('Tiền nước');
            $table->decimal('service_fee', 12, 0)->default(0)->comment('Các phí dịch vụ cố định khác');
            $table->decimal('total', 12, 0)->comment('Tổng cộng phải đóng');
            $table->decimal('paid_amount', 12, 0)->default(0)->comment('Đã thanh toán');
            $table->decimal('debt', 12, 0)->default(0)->comment('Còn nợ = total - paid_amount');
            $table->date('due_date')->comment('Hạn chót đóng tiền');
            $table->enum('status', ['unpaid', 'partial', 'paid', 'overdue'])
                ->default('unpaid')
                ->comment('unpaid: Chưa thanh toán | partial: Đã cọc 1 phần | paid: Hoàn tất | overdue: Quá hạn');
            $table->text('notes')->nullable();
            $table->timestamps();

            // 1 phòng chỉ có 1 hóa đơn mỗi tháng
            $table->unique(['contract_id', 'month', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
