<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MaintenanceTicket;
use Illuminate\Http\Request;
use Carbon\Carbon;

class MaintenanceTicketController extends Controller
{
    public function index(Request $request)
    {
        $query = MaintenanceTicket::with(['contract.room.house', 'contract.tenant.user'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        $tickets = $query->paginate(15)->withQueryString();
        return view('admin.maintenance-tickets.index', compact('tickets'));
    }

    public function create()
    {
        // Thường admin không tạo ticket mà sửa ticket do tenant gửi.
        // Nhưng nếu cần, ta vẫn có thể làm màn hình tạo thay cho tenant.
        return redirect()->route('admin.maintenance-tickets.index')->with('info', 'Chức năng báo sự cố dành cho Khách thuê trên Portal.');
    }

    public function store(Request $request)
    {
        // 
    }

    public function show(MaintenanceTicket $maintenanceTicket)
    {
        $maintenanceTicket->load(['contract.room.house', 'contract.tenant.user']);
        return view('admin.maintenance-tickets.show', compact('maintenanceTicket'));
    }

    public function edit(MaintenanceTicket $maintenanceTicket)
    {
        $maintenanceTicket->load(['contract.room.house', 'contract.tenant.user']);
        return view('admin.maintenance-tickets.edit', compact('maintenanceTicket'));
    }

    public function update(Request $request, MaintenanceTicket $maintenanceTicket)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,in_progress,done,cancelled',
            'admin_response' => 'nullable|string',
        ]);

        if ($validated['status'] == 'done' && $maintenanceTicket->status != 'done') {
            $validated['resolved_at'] = Carbon::now();
        }

        $maintenanceTicket->update($validated);

        return redirect()->route('admin.maintenance-tickets.index')->with('success', 'Đã cập nhật trạng thái xử lý sự cố!');
    }

    public function destroy(MaintenanceTicket $maintenanceTicket)
    {
        $maintenanceTicket->delete();
        return redirect()->route('admin.maintenance-tickets.index')->with('success', 'Đã xóa phiếu báo sự cố!');
    }
}
