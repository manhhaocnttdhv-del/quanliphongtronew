<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('VD: Tiền điện, Tiền nước, Rác, Wifi, Xe máy');
            $table->decimal('price', 12, 0)->comment('Đơn giá');
            $table->string('unit')->default('tháng')->comment('VD: kWh, m³, tháng, xe');
            $table->enum('type', ['electricity', 'water', 'fixed', 'other'])
                ->default('fixed')
                ->comment('electricity: Điện | water: Nước | fixed: Phí cố định | other: Khác');
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
