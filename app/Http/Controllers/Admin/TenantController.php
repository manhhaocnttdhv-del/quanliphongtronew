<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class TenantController extends Controller
{
    /**
     * Hiển thị danh sách khách thuê.
     */
    public function index(Request $request)
    {
        $tenants = Tenant::with(['user'])->latest()->paginate(15);
        return view('admin.tenants.index', compact('tenants'));
    }

    /**
     * Show form tạo mới khách thuê.
     */
    public function create()
    {
        return view('admin.tenants.create');
    }

    /**
     * Lưu thông tin khách thuê mới.
     * Logic: Tạo User trước (để làm account đăng nhập), sau đó tạo Tenant.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'cccd' => 'nullable|string|max:20|unique:tenants,cccd',
            'phone' => 'nullable|string|max:15',
            'address' => 'nullable|string',
            'hometown' => 'nullable|string',
            'birthday' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
        ]);

        // 1. Tạo User account
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make('12345678'), // Mật khẩu mặc định
        ]);

        // Có thể gán role Tenant nếu xài Spatie ở đây:
        // $user->assignRole('Tenant');

        // 2. Tạo Tenant info
        Tenant::create([
            'user_id' => $user->id,
            'cccd' => $validated['cccd'],
            'phone' => $validated['phone'],
            'address' => $validated['address'],
            'hometown' => $validated['hometown'],
            'birthday' => $validated['birthday'],
            'gender' => $validated['gender'],
        ]);

        return redirect()->route('admin.tenants.index')->with('success', 'Thêm khách thuê thành công! Mật khẩu mặc định: 12345678');
    }

    /**
     * Hiển thị thông tin chi tiết.
     */
    public function show(Tenant $tenant)
    {
        return view('admin.tenants.show', compact('tenant'));
    }

    /**
     * Form sửa thông tin.
     */
    public function edit(Tenant $tenant)
    {
        return view('admin.tenants.edit', compact('tenant'));
    }

    /**
     * Cập nhật thông tin.
     */
    public function update(Request $request, Tenant $tenant)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $tenant->user_id,
            'cccd' => 'nullable|string|max:20|unique:tenants,cccd,' . $tenant->id,
            'phone' => 'nullable|string|max:15',
            'address' => 'nullable|string',
            'hometown' => 'nullable|string',
            'birthday' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
        ]);

        // Cập nhật User
        $tenant->user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        // Cập nhật Tenant
        $tenant->update([
            'cccd' => $validated['cccd'],
            'phone' => $validated['phone'],
            'address' => $validated['address'],
            'hometown' => $validated['hometown'],
            'birthday' => $validated['birthday'],
            'gender' => $validated['gender'],
        ]);

        return redirect()->route('admin.tenants.index')->with('success', 'Cập nhật thông tin khách thuê thành công!');
    }

    /**
     * Xóa khách thuê.
     */
    public function destroy(Tenant $tenant)
    {
        // Khi xóa tenant, thường ta xóa user luôn nếu cascadeOnDelete đã cấu hình
        $tenant->user->delete();
        return redirect()->route('admin.tenants.index')->with('success', 'Đã xóa khách thuê!');
    }
}
