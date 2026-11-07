<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE invoices MODIFY COLUMN type ENUM('monthly', 'deposit', 'settlement', 'adjustment') NOT NULL DEFAULT 'monthly'");
        Schema::table('invoices', function (Blueprint $table) {
            $table->decimal('refunded_amount', 12, 0)->default(0);
            $table->timestamp('voided_at')->nullable();
        });
        Schema::table('payments', function (Blueprint $table) {
            $table->string('kind', 30)->default('receipt')->index();
        });
        Schema::create('payment_resolutions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->unique()->constrained()->restrictOnDelete();
            $table->string('outcome', 20);
            $table->decimal('refund_amount', 12, 0)->default(0);
            $table->string('reference')->nullable();
            $table->string('evidence_path')->nullable();
            $table->text('reason');
            $table->foreignId('resolved_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('resolved_at')->index();
            $table->timestamps();
        });
        Schema::create('contract_settlements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->unique()->constrained()->restrictOnDelete();
            $table->foreignId('invoice_id')->nullable()->constrained()->restrictOnDelete();
            $table->date('checkout_date');
            $table->json('breakdown');
            $table->decimal('deposit_held', 12, 0);
            $table->decimal('deposit_applied', 12, 0);
            $table->decimal('refund_due', 12, 0);
            $table->decimal('cash_due', 12, 0);
            $table->string('refund_reference')->nullable();
            $table->string('refund_evidence_path')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->foreignId('refunded_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->text('handover_notes');
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->uuid('submission_key')->unique();
            $table->foreignId('house_id')->nullable()->constrained()->restrictOnDelete();
            $table->string('category', 30);
            $table->string('description');
            $table->decimal('amount', 12, 0);
            $table->date('spent_on')->index();
            $table->string('reference')->nullable();
            $table->string('evidence_path')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('voided_at')->nullable();
            $table->foreignId('voided_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->text('void_reason')->nullable();
            $table->timestamps();
        });
        Schema::create('reminder_deliveries', function (Blueprint $table) {
            $table->id();
            $table->string('delivery_key')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('kind', 30);
            $table->string('subject_type', 20);
            $table->unsignedBigInteger('subject_id');
            $table->timestamp('sent_at')->index();
        });
    }

    public function down(): void
    {
        // Keep extended invoice types: narrowing an enum would discard financial history.
        Schema::dropIfExists('reminder_deliveries');
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('contract_settlements');
        Schema::dropIfExists('payment_resolutions');
        Schema::table('payments', fn (Blueprint $table) => $table->dropColumn('kind'));
        Schema::table('invoices', fn (Blueprint $table) => $table->dropColumn(['refunded_amount', 'voided_at']));
    }
};
