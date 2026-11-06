<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('settings')->updateOrInsert(
            ['key' => 'site_name'],
            ['value' => 'Trọ Nhà Mình', 'group' => 'general', 'updated_at' => now()]
        );
        DB::table('settings')->where('key', 'site_tagline')
            ->update(['value' => 'Phòng trọ tại Vinh, Nghệ An', 'updated_at' => now()]);
        DB::table('settings')->where('key', 'contact_email')->where('value', 'admin@boardingpro.vn')
            ->update(['value' => null, 'updated_at' => now()]);
    }

    public function down(): void
    {
        DB::table('settings')->where('key', 'site_name')->where('value', 'Trọ Nhà Mình')
            ->update(['value' => 'BoardingPro', 'updated_at' => now()]);
    }
};
