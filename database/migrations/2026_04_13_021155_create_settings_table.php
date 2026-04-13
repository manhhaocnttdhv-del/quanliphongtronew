<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('group')->default('general')->comment('general|finance|notification');
            $table->timestamps();
        });

        // Seed mặc định
        $defaults = [
            // Thông tin trang
            ['key' => 'site_name',       'value' => 'BoardingPro',                        'group' => 'general'],
            ['key' => 'site_tagline',    'value' => 'Hệ thống quản lý phòng trọ',         'group' => 'general'],
            ['key' => 'contact_email',   'value' => 'admin@boardingpro.vn',               'group' => 'general'],
            ['key' => 'contact_phone',   'value' => '0901 234 567',                       'group' => 'general'],
            ['key' => 'contact_address', 'value' => '123 Đường ABC, Quận 1, TP.HCM',      'group' => 'general'],
            // Tài chính
            ['key' => 'currency',        'value' => 'VND',                                'group' => 'finance'],
            ['key' => 'vat_rate',        'value' => '0',                                  'group' => 'finance'],
            ['key' => 'late_fee_rate',   'value' => '2',                                  'group' => 'finance'],
            ['key' => 'invoice_due_days','value' => '10',                                 'group' => 'finance'],
            // Thông báo
            ['key' => 'notify_new_invoice',  'value' => '1',                             'group' => 'notification'],
            ['key' => 'notify_overdue',      'value' => '1',                             'group' => 'notification'],
            ['key' => 'notify_maintenance',  'value' => '1',                             'group' => 'notification'],
        ];

        foreach ($defaults as $row) {
            DB::table('settings')->insert(array_merge($row, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
