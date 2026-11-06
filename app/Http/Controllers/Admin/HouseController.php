<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\House;
use Illuminate\Http\Request;

class HouseController extends Controller
{
    /**
     * Hiển thị danh sách các chi nhánh thuộc đơn vị vận hành.
     */
    public function index()
    {
        // Lấy danh sách kèm số lượng phòng bám theo relationship
        $houses = House::withCount('rooms')->latest()->paginate(10);
        return view('admin.houses.index', compact('houses'));
    }

    /**
     * Hiển thị form tạo mới chi nhánh.
     */
    public function create()
    {
        return view('admin.houses.create');
    }

    /**
     * Lưu dữ liệu chi nhánh mới.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);
        
        $validated['is_active'] = $request->has('is_active');

        House::create($validated);

        return redirect()->route('admin.houses.index')->with('success', 'Thêm chi nhánh thành công!');
    }

    /**
     * Hiển thị chi tiết 1 chi nhánh.
     */
    public function show(House $house)
    {
        return view('admin.houses.show', compact('house'));
    }

    /**
     * Hiển thị form sửa chi nhánh.
     */
    public function edit(House $house)
    {
        return view('admin.houses.edit', compact('house'));
    }

    /**
     * Cập nhật dữ liệu chi nhánh.
     */
    public function update(Request $request, House $house)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);
        
        $validated['is_active'] = $request->has('is_active');

        $house->update($validated);

        return redirect()->route('admin.houses.index')->with('success', 'Cập nhật chi nhánh thành công!');
    }

    /**
     * Xóa chi nhánh.
     */
    public function destroy(House $house)
    {
        // Có thể cần kiểm tra nếu có phòng đang thuê thì không cho xóa
        // Tạm thời cho phép xóa
        $house->delete();
        
        return redirect()->route('admin.houses.index')->with('success', 'Đã xóa chi nhánh thành công!');
    }
}
