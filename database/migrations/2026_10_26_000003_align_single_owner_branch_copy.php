<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('settings')
            ->where('key', 'site_tagline')
            ->where('value', 'Hệ thống quản lý phòng trọ')
            ->update([
                'value' => 'Nền tảng quản lý nhiều chi nhánh phòng trọ',
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        DB::table('settings')
            ->where('key', 'site_tagline')
            ->where('value', 'Nền tảng quản lý nhiều chi nhánh phòng trọ')
            ->update([
                'value' => 'Hệ thống quản lý phòng trọ',
                'updated_at' => now(),
            ]);
    }
};
