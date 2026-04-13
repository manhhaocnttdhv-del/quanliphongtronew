<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\Room;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class ContractController extends Controller
{
    public function index()
    {
        $contracts = Contract::with(['room.house', 'tenant.user'])->latest()->paginate(15);
        return view('admin.contracts.index', compact('contracts'));
    }

    public function create()
    {
        $rooms = Room::where('status', 'available')->with('house')->get();
        $tenants = Tenant::with('user')->get();
        return view('admin.contracts.create', compact('rooms', 'tenants'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'tenant_id' => 'required|exists:tenants,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'deposit' => 'required|numeric|min:0',
            'monthly_price' => 'required|numeric|min:0',
            'occupants' => 'required|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        $contract = Contract::create($validated + ['status' => 'active']);

        // Đổi trạng thái phòng sang Đang Thuê
        Room::find($validated['room_id'])->update(['status' => 'rented']);

        return redirect()->route('admin.contracts.index')->with('success', 'Tạo hợp đồng mới thành công!');
    }

    public function show(Contract $contract)
    {
        $contract->load(['room.house', 'tenant.user', 'invoices']);
        return view('admin.contracts.show', compact('contract'));
    }

    public function edit(Contract $contract)
    {
        $rooms = Room::with('house')->get();
        $tenants = Tenant::with('user')->get();
        return view('admin.contracts.edit', compact('contract', 'rooms', 'tenants'));
    }

    public function update(Request $request, Contract $contract)
    {
        $validated = $request->validate([
            'status' => 'required|in:active,expired,terminated',
            'terminated_at' => 'nullable|date',
            'deposit_refund' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $contract->update($validated);

        // Nếu thanh lý, đổi trạng thái phòng về Available
        if (in_array($validated['status'], ['expired', 'terminated'])) {
            $contract->room->update(['status' => 'available']);
        }

        return redirect()->route('admin.contracts.index')->with('success', 'Cập nhật hợp đồng thành công!');
    }

    public function destroy(Contract $contract)
    {
        $contract->delete();
        return redirect()->route('admin.contracts.index')->with('success', 'Đã xóa hợp đồng!');
    }

    /**
     * Xuất PDF Hợp đồng thuê nhà
     */
    public function downloadPDF(Contract $contract)
    {
        $contract->load(['room.house', 'tenant.user']);
        
        $pdf = Pdf::loadView('admin.contracts.pdf', compact('contract'))
                  ->setPaper('a4', 'portrait');

        return $pdf->download('hop-dong-' . $contract->id . '.pdf');
    }
}
