<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Only replace the repository's known demo values; real operator data
        // entered later remains untouched.
        DB::table('settings')
            ->where('key', 'contact_address')
            ->where('value', '123 Đường ABC, Quận 1, TP.HCM')
            ->update(['value' => 'Số 12, đường Nguyễn Sỹ Sách, TP. Vinh, Nghệ An', 'updated_at' => now()]);

        DB::table('houses')
            ->where('name', 'Dãy trọ Bình Minh')
            ->where('address', '12 Nguyễn Trãi, Phường 2, Quận 5, TP.HCM')
            ->update(['address' => '12 đường Lê Mao, TP. Vinh, Nghệ An', 'updated_at' => now()]);

        DB::table('houses')
            ->where('name', 'Chung cư mini Ánh Dương')
            ->where('address', '88 Lê Văn Sỹ, Phường 11, Quận 3, TP.HCM')
            ->update(['address' => '88 đường Nguyễn Thị Minh Khai, TP. Vinh, Nghệ An', 'updated_at' => now()]);
    }

    public function down(): void
    {
        DB::table('settings')
            ->where('key', 'contact_address')
            ->where('value', 'Số 12, đường Nguyễn Sỹ Sách, TP. Vinh, Nghệ An')
            ->update(['value' => '123 Đường ABC, Quận 1, TP.HCM', 'updated_at' => now()]);

        DB::table('houses')
            ->where('name', 'Dãy trọ Bình Minh')
            ->where('address', '12 đường Lê Mao, TP. Vinh, Nghệ An')
            ->update(['address' => '12 Nguyễn Trãi, Phường 2, Quận 5, TP.HCM', 'updated_at' => now()]);

        DB::table('houses')
            ->where('name', 'Chung cư mini Ánh Dương')
            ->where('address', '88 đường Nguyễn Thị Minh Khai, TP. Vinh, Nghệ An')
            ->update(['address' => '88 Lê Văn Sỹ, Phường 11, Quận 3, TP.HCM', 'updated_at' => now()]);
    }
};
