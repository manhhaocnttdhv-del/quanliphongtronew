<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cache phân quyền
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Tạo 3 Role
        $adminRole    = Role::firstOrCreate(['name' => 'admin',    'guard_name' => 'web']);
        $tenantRole   = Role::firstOrCreate(['name' => 'tenant',   'guard_name' => 'web']);
        $customerRole = Role::firstOrCreate(['name' => 'customer', 'guard_name' => 'web']);

        // ── Tài khoản Admin ──────────────────────────────────
        $admin = User::firstOrCreate(
            ['email' => 'admin@demo.com'],
            [
                'name'     => 'Quản Trị Viên',
                'password' => Hash::make('password'),
            ]
        );
        $admin->assignRole($adminRole);

        // ── Tài khoản Demo Tenant (sẽ tạo thêm trong TenantContractSeeder) ──
        $tenant = User::firstOrCreate(
            ['email' => 'tenant@demo.com'],
            [
                'name'     => 'Nguyễn Văn An',
                'password' => Hash::make('password'),
            ]
        );
        $tenant->assignRole($tenantRole);

        // ── Tài khoản Demo Customer (đăng ký online qua luồng booking) ──
        $customer = User::firstOrCreate(
            ['email' => 'customer@demo.com'],
            [
                'name'     => 'Khách Đăng Ký',
                'password' => Hash::make('password'),
            ]
        );
        $customer->assignRole($customerRole);

        $this->command->info('✅ Roles & Users demo đã được tạo.');
        $this->command->info('   Admin    → admin@demo.com / password');
        $this->command->info('   Tenant   → tenant@demo.com / password');
        $this->command->info('   Customer → customer@demo.com / password');
    }
}
