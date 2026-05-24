<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->integer('electricity_old')->default(0)->after('electricity_fee');
            $table->integer('electricity_new')->default(0)->after('electricity_old');
            $table->integer('electricity_usage')->default(0)->after('electricity_new');
            
            $table->integer('water_old')->default(0)->after('water_fee');
            $table->integer('water_new')->default(0)->after('water_old');
            $table->integer('water_usage')->default(0)->after('water_new');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['electricity_old', 'electricity_new', 'electricity_usage', 'water_old', 'water_new', 'water_usage']);
        });
    }
};
