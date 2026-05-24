<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Room;
use App\Models\Setting;

class HomeController extends Controller
{
    public function index()
    {
        // Lấy danh sách các phòng còn trống (available)
        $rooms = Room::with(['house', 'roomType'])
            ->where('status', 'available')
            ->orderBy('id', 'desc')
            ->paginate(12);

        // Lấy số Zalo từ cài đặt
        $zaloNumber = Setting::get('zalo_number', '');

        return view('welcome', compact('rooms', 'zaloNumber'));
    }
    public function show($id)
    {
        $room = Room::with(['house', 'roomType'])->findOrFail($id);
        $zaloNumber = Setting::get('zalo_number', '');
        return view('room_detail', compact('room', 'zaloNumber'));
    }
}
