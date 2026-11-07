<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->restrictOnDelete();
            $table->unsignedBigInteger('order_code')->unique();
            $table->decimal('amount', 12, 0);
            $table->decimal('paid_amount', 12, 0)->default(0);
            $table->string('status', 20)->default('pending');
            $table->text('checkout_url')->nullable();
            $table->timestamps();
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('payment_order_id')->nullable()->constrained()->restrictOnDelete();
            $table->string('gateway_reference', 160)->nullable()->unique();
            $table->boolean('requires_review')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('payment_order_id');
            $table->dropUnique(['gateway_reference']);
            $table->dropColumn(['gateway_reference', 'requires_review']);
        });
        Schema::dropIfExists('payment_orders');
    }
};
