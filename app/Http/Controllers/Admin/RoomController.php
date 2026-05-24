<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\House;
use App\Models\RoomType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RoomController extends Controller
{
    /**
     * Hiển thị danh sách phòng.
     */
    public function index(Request $request)
    {
        $query = Room::with(['house', 'roomType', 'tenants'])->latest();
        
        // Filter theo nhà (House)
        if ($request->filled('house_id')) {
            $query->where('house_id', $request->house_id);
        }

        // Filter theo trạng thái (status)
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $rooms = $query->paginate(15)->withQueryString();
        $houses = House::all();
        
        return view('admin.rooms.index', compact('rooms', 'houses'));
    }

    /**
     * Hiển thị form tạo mới phòng.
     */
    public function create()
    {
        $houses = House::all();
        $roomTypes = RoomType::all();
        return view('admin.rooms.create', compact('houses', 'roomTypes'));
    }

    /**
     * Lưu dữ liệu phòng mới.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'house_id' => 'required|exists:houses,id',
            'room_type_id' => 'nullable|exists:room_types,id',
            'name' => 'required|string|max:255',
            'floor' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'area' => 'nullable|numeric|min:0',
            'max_occupants' => 'required|integer|min:1',
            'description' => 'nullable|string',
            'images.*' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('images')) {
            $imagePaths = [];
            foreach ($request->file('images') as $file) {
                $imagePaths[] = $file->store('rooms', 'public');
            }
            $validated['images'] = $imagePaths;
            $validated['image_path'] = $imagePaths[0] ?? null; // Keep the first image as main image
        }

        Room::create($validated);

        return redirect()->route('admin.rooms.index')->with('success', 'Thêm phòng thành công!');
    }

    /**
     * Hiển thị chi tiết 1 phòng.
     */
    public function show(Room $room)
    {
        $room->load(['house', 'roomType', 'tenants', 'contracts', 'services']);
        return view('admin.rooms.show', compact('room'));
    }

    /**
     * Hiển thị form sửa phòng.
     */
    public function edit(Room $room)
    {
        $houses = House::all();
        $roomTypes = RoomType::all();
        return view('admin.rooms.edit', compact('room', 'houses', 'roomTypes'));
    }

    /**
     * Cập nhật dữ liệu phòng.
     */
    public function update(Request $request, Room $room)
    {
        $validated = $request->validate([
            'house_id' => 'required|exists:houses,id',
            'room_type_id' => 'nullable|exists:room_types,id',
            'name' => 'required|string|max:255',
            'floor' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'area' => 'nullable|numeric|min:0',
            'max_occupants' => 'required|integer|min:1',
            'status' => 'required|in:available,rented,maintenance',
            'description' => 'nullable|string',
            'images.*' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('images')) {
            // Xóa ảnh cũ (tùy chọn, ở đây tạm giữ hoặc có thể xóa)
            if ($room->images) {
                foreach ($room->images as $oldImage) {
                    Storage::disk('public')->delete($oldImage);
                }
            } elseif ($room->image_path) {
                Storage::disk('public')->delete($room->image_path);
            }
            
            $imagePaths = [];
            foreach ($request->file('images') as $file) {
                $imagePaths[] = $file->store('rooms', 'public');
            }
            $validated['images'] = $imagePaths;
            $validated['image_path'] = $imagePaths[0] ?? null; // Keep first image as main
        }

        $room->update($validated);

        return redirect()->route('admin.rooms.index')->with('success', 'Cập nhật phòng thành công!');
    }

    /**
     * Xóa phòng.
     */
    public function destroy(Room $room)
    {
        $room->delete();
        return redirect()->route('admin.rooms.index')->with('success', 'Đã xóa phòng thành công!');
    }
}
