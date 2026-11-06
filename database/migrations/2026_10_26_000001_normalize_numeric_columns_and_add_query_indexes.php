<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Meter readings can be fractional. Keep the invoice snapshot in the
        // same numeric shape so arithmetic never depends on string coercion.
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE rooms MODIFY floor SMALLINT UNSIGNED NOT NULL DEFAULT 1');
            DB::statement('ALTER TABLE rooms MODIFY max_occupants TINYINT UNSIGNED NOT NULL DEFAULT 4');
            DB::statement('ALTER TABLE contracts MODIFY occupants TINYINT UNSIGNED NOT NULL DEFAULT 1');
            DB::statement('ALTER TABLE booking_requests MODIFY desired_occupants TINYINT UNSIGNED NOT NULL');
            DB::statement('ALTER TABLE booking_requests MODIFY desired_lease_months SMALLINT UNSIGNED NOT NULL');
            DB::statement('ALTER TABLE invoices
                MODIFY electricity_old DECIMAL(10,2) NOT NULL DEFAULT 0,
                MODIFY electricity_new DECIMAL(10,2) NOT NULL DEFAULT 0,
                MODIFY electricity_usage DECIMAL(10,2) NOT NULL DEFAULT 0,
                MODIFY water_old DECIMAL(10,2) NOT NULL DEFAULT 0,
                MODIFY water_new DECIMAL(10,2) NOT NULL DEFAULT 0,
                MODIFY water_usage DECIMAL(10,2) NOT NULL DEFAULT 0');
        }

        Schema::table('rooms', function (Blueprint $table) {
            $table->index(['house_id', 'status'], 'rooms_house_status_index');
        });

        Schema::table('contracts', function (Blueprint $table) {
            $table->index(['tenant_id', 'status'], 'contracts_tenant_status_index');
            $table->index(['room_id', 'status'], 'contracts_room_status_index');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->index(['year', 'month', 'status'], 'invoices_period_status_index');
            $table->index(['status', 'due_date'], 'invoices_status_due_date_index');
        });

        Schema::table('maintenance_tickets', function (Blueprint $table) {
            $table->index(['status', 'created_at'], 'maintenance_status_created_index');
        });

        Schema::table('announcements', function (Blueprint $table) {
            $table->index(['house_id', 'published_at'], 'announcements_house_published_index');
        });
    }

    public function down(): void
    {
        Schema::table('announcements', fn (Blueprint $table) => $table->dropIndex('announcements_house_published_index'));
        Schema::table('maintenance_tickets', fn (Blueprint $table) => $table->dropIndex('maintenance_status_created_index'));
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropIndex('invoices_period_status_index');
            $table->dropIndex('invoices_status_due_date_index');
        });
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropIndex('contracts_tenant_status_index');
            $table->dropIndex('contracts_room_status_index');
        });
        Schema::table('rooms', fn (Blueprint $table) => $table->dropIndex('rooms_house_status_index'));

        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE rooms MODIFY floor INT NOT NULL DEFAULT 1');
            DB::statement('ALTER TABLE rooms MODIFY max_occupants INT NOT NULL DEFAULT 4');
            DB::statement('ALTER TABLE contracts MODIFY occupants INT NOT NULL DEFAULT 1');
            DB::statement('ALTER TABLE booking_requests MODIFY desired_occupants INT UNSIGNED NOT NULL');
            DB::statement('ALTER TABLE booking_requests MODIFY desired_lease_months INT UNSIGNED NOT NULL');
            DB::statement('ALTER TABLE invoices
                MODIFY electricity_old INT NOT NULL DEFAULT 0,
                MODIFY electricity_new INT NOT NULL DEFAULT 0,
                MODIFY electricity_usage INT NOT NULL DEFAULT 0,
                MODIFY water_old INT NOT NULL DEFAULT 0,
                MODIFY water_new INT NOT NULL DEFAULT 0,
                MODIFY water_usage INT NOT NULL DEFAULT 0');
        }
    }
};
