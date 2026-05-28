<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\Room;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

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

            // Chuyển role của user gắn với tenant về customer nếu không còn hợp đồng active nào khác
            $tenant = $contract->tenant;
            if ($tenant && $tenant->user) {
                $hasOtherActive = Contract::where('tenant_id', $tenant->id)
                    ->where('id', '!=', $contract->id)
                    ->where('status', 'active')
                    ->exists();
                if (!$hasOtherActive) {
                    $tenant->user->syncRoles(['customer']);
                }
            }
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

    public function transferForm(Contract $contract)
    {
        if ($contract->status !== 'active') {
            return redirect()->route('admin.contracts.index')->withErrors(['Chỉ có thể chuyển phòng cho hợp đồng đang hoạt động!']);
        }
        $contract->load(['room.house', 'tenant.user']);
        $availableRooms = Room::where('status', 'available')->with('house')->get();
        return view('admin.contracts.transfer', compact('contract', 'availableRooms'));
    }

    public function transfer(Request $request, Contract $contract)
    {
        $validated = $request->validate([
            'new_room_id' => 'required|exists:rooms,id',
            'transfer_date' => 'required|date',
            'new_monthly_price' => 'required|numeric|min:0',
            'deposit_transfer' => 'required|numeric|min:0',
            'extra_fee' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        if ($contract->status !== 'active') {
            return back()->withErrors(['Hợp đồng hiện tại không hoạt động.']);
        }

        $newRoom = Room::where('id', $validated['new_room_id'])->where('status', 'available')->first();
        if (!$newRoom) {
            return back()->withErrors(['Phòng mới không khả dụng!']);
        }

        DB::transaction(function () use ($contract, $validated, $newRoom) {
            // 1. Kết thúc hợp đồng cũ
            $contract->update([
                'status' => 'terminated',
                'terminated_at' => $validated['transfer_date'],
                'notes' => $contract->notes . "\n[Chuyển sang P." . $newRoom->name . " ngày " . \Carbon\Carbon::parse($validated['transfer_date'])->format('d/m/Y') . "]"
            ]);
            $contract->room->update(['status' => 'available']);

            // 2. Tạo hợp đồng mới
            $newContract = Contract::create([
                'room_id' => $newRoom->id,
                'tenant_id' => $contract->tenant_id,
                'start_date' => $validated['transfer_date'],
                'end_date' => \Carbon\Carbon::parse($validated['transfer_date'])->addMonths(6)->format('Y-m-d'), // Mặc định +6 tháng
                'deposit' => $validated['deposit_transfer'],
                'monthly_price' => $validated['new_monthly_price'],
                'occupants' => $contract->occupants,
                'status' => 'active',
                'notes' => "[Chuyển từ P." . $contract->room->name . " sang]\n" . $validated['notes'],
            ]);

            // Đổi trạng thái phòng mới
            $newRoom->update(['status' => 'rented']);

            // 3. Tự động tạo hóa đơn phụ phí chênh lệch (nếu có)
            $oldDeposit = $contract->deposit;
            $newDeposit = $validated['deposit_transfer'];
            $depositDiff = $newDeposit > $oldDeposit ? ($newDeposit - $oldDeposit) : 0;
            $extraFee = isset($validated['extra_fee']) ? $validated['extra_fee'] : 0;
            
            $totalDifference = $depositDiff + $extraFee;

            if ($totalDifference > 0) {
                $notes = [];
                if ($depositDiff > 0) $notes[] = "Thu thêm chênh lệch cọc: " . number_format($depositDiff) . "đ";
                if ($extraFee > 0) $notes[] = "Phụ phí chuyển phòng: " . number_format($extraFee) . "đ";

                \App\Models\Invoice::create([
                    'contract_id' => $newContract->id,
                    'month' => (int)\Carbon\Carbon::parse($validated['transfer_date'])->format('m'),
                    'year' => (int)\Carbon\Carbon::parse($validated['transfer_date'])->format('Y'),
                    'room_fee' => 0,
                    'electricity_fee' => 0,
                    'water_fee' => 0,
                    'service_fee' => $totalDifference,
                    'total' => $totalDifference,
                    'paid_amount' => 0,
                    'debt' => $totalDifference,
                    'due_date' => \Carbon\Carbon::parse($validated['transfer_date'])->addDays(3)->format('Y-m-d'),
                    'status' => 'unpaid',
                    'notes' => "Hóa đơn chênh lệch khi chuyển từ P." . $contract->room->name . " sang P." . $newRoom->name . "\n" . implode("\n", $notes),
                ]);
            }
        });

        return redirect()->route('admin.contracts.index')->with('success', 'Chuyển phòng thành công!');
    }
}
