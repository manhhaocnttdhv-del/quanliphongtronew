<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->changeDeleteRule('restrict');
    }

    public function down(): void
    {
        $this->changeDeleteRule('cascade');
    }

    private function changeDeleteRule(string $rule): void
    {
        foreach (['contracts' => ['room_id' => 'rooms', 'tenant_id' => 'tenants'], 'invoices' => ['contract_id' => 'contracts'], 'payments' => ['invoice_id' => 'invoices']] as $tableName => $relations) {
            Schema::table($tableName, function (Blueprint $table) use ($relations, $rule) {
                foreach ($relations as $column => $parent) {
                    $table->dropForeign([$column]);
                    $table->foreign($column)->references('id')->on($parent)->onDelete($rule);
                }
            });
        }
    }
};
