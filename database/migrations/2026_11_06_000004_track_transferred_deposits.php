<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->foreignId('transferred_to_contract_id')->nullable()->constrained('contracts')->restrictOnDelete();
            $table->decimal('deposit_transferred', 12, 0)->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('transferred_to_contract_id');
            $table->dropColumn('deposit_transferred');
        });
    }
};
