<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MeterReading;
use App\Models\Room;
use App\Models\Service;
use Illuminate\Http\Request;

class MeterReadingController extends Controller
{
    public function index(Request $request)
    {
        // Mặc định: tháng trước, năm tương ứng
        $prevMonth = now()->subMonth();
        $defaultMonth = (int) $prevMonth->format('n');
        $defaultYear  = (int) $prevMonth->format('Y');

        $filterMonth = $request->filled('month') ? (int) $request->month : $defaultMonth;
        $filterYear  = $request->filled('year')  ? (int) $request->year  : $defaultYear;

        $query = MeterReading::with(['room.house', 'service'])->latest();

        if ($filterMonth) {
            $query->where('month', $filterMonth);
        }

        if ($filterYear) {
            $query->where('year', $filterYear);
        }

        $readings = $query->paginate(15)->withQueryString();

        return view('admin.meter-readings.index', compact('readings', 'filterMonth', 'filterYear'));
    }

    public function create()
    {
        $rooms = Room::where('status', 'rented')
                     ->with(['house', 'contracts' => function($q) {
                         $q->where('status', 'active')->with('tenant.user');
                     }])->get();
        // Chỉ lấy các dịch vụ tính theo đồng hồ (Điện, Nước)
        $services = Service::whereIn('type', ['electricity', 'water'])->get();
        
        return view('admin.meter-readings.create', compact('rooms', 'services'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'service_id' => 'required|exists:services,id',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2000|max:2100',
            'old_value' => 'required|numeric|min:0',
            'new_value' => 'required|numeric|gte:old_value',
        ]);

        $service = Service::find($validated['service_id']);

        // Check trùng
        $exists = MeterReading::where([
            'room_id' => $validated['room_id'],
            'service_id' => $validated['service_id'],
            'month' => $validated['month'],
            'year' => $validated['year'],
        ])->exists();

        if ($exists) {
            return back()->withInput()->withErrors(['Dữ liệu tháng này của phòng đã có!']);
        }

        $consumption = $validated['new_value'] - $validated['old_value'];
        $total_amount = $consumption * $service->price;

        MeterReading::create($validated + [
            'unit_price' => $service->price,
            'total_amount' => $total_amount,
            'recorded_by' => auth()->user()->name
        ]);

        return redirect()->route('admin.meter-readings.index')->with('success', 'Ghi chỉ số thành công!');
    }

    public function destroy(MeterReading $meterReading)
    {
        $meterReading->delete();
        return redirect()->route('admin.meter-readings.index')->with('success', 'Đã xóa chỉ số!');
    }

    public function getOldValue(Request $request)
    {
        $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'service_id' => 'required|exists:services,id'
        ]);

        $lastReading = MeterReading::where('room_id', $request->room_id)
            ->where('service_id', $request->service_id)
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->first();

        return response()->json([
            'old_value' => $lastReading ? $lastReading->new_value : 0
        ]);
    }
}
