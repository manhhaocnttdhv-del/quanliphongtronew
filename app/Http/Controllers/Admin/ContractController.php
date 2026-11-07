<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\Room;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use App\Models\BookingRequest;
use App\Services\Booking\RoomStatusSyncService;
use Illuminate\Validation\ValidationException;

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

        DB::transaction(function () use ($validated) {
            $room = Room::lockForUpdate()->findOrFail($validated['room_id']);
            $tenant = Tenant::lockForUpdate()->findOrFail($validated['tenant_id']);
            $sync = app(RoomStatusSyncService::class);
            $sync->ensureAvailable($room);
            if ($validated['occupants'] > $room->max_occupants) {
                throw ValidationException::withMessages(['occupants' => 'Số người ở vượt quá sức chứa của phòng.']);
            }
            Contract::create($validated + ['status' => 'active']);
            $sync->recompute($room);
            $sync->syncTenantRole($tenant);
        }, 3);

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
            'status' => 'required|in:draft,active,expired,terminated,cancelled',
            'terminated_at' => 'nullable|date',
            'deposit_refund' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        DB::transaction(function () use ($contract, $validated) {
            $room = Room::lockForUpdate()->findOrFail($contract->room_id);
            $contract = Contract::lockForUpdate()->findOrFail($contract->id);
            $booking = BookingRequest::where('contract_id', $contract->id)->lockForUpdate()->first();
            $sync = app(RoomStatusSyncService::class);
            if (! in_array($validated['status'], $contract->allowedAdminStatuses(), true)) {
                throw ValidationException::withMessages(['status' => 'Không thể chuyển hợp đồng sang trạng thái này.']);
            }
            if (($contract->transferred_to_contract_id || $contract->settlement()->exists()) && array_key_exists('deposit_refund', $validated)
                && (int) $validated['deposit_refund'] !== (int) $contract->deposit_refund) {
                throw ValidationException::withMessages(['deposit_refund' => 'Cọc đã chuyển hoặc quyết toán; ghi nhận hoàn tại hợp đồng đang giữ cọc.']);
            }
            if (in_array($validated['status'], ['draft', 'active'], true)) {
                $sync->ensureAvailable($room, $contract->id);
                if ($validated['status'] === 'active'
                    && ((int) $contract->deposit_refund > 0 || (int) ($validated['deposit_refund'] ?? 0) > 0)) {
                    throw ValidationException::withMessages(['status' => 'Hợp đồng đã hoàn cọc cần lập lại thủ tục thuê.']);
                }
            }
            if (($validated['deposit_refund'] ?? 0) > (int) $contract->deposit) {
                throw ValidationException::withMessages(['deposit_refund' => 'Tiền hoàn không được vượt quá tiền cọc.']);
            }
            $contract->update($validated);
            if ($booking && $validated['status'] === 'cancelled' && ! $booking->deposit_paid_at) {
                $booking->update(['status' => 'cancelled', 'cancelled_at' => now(), 'last_status_changed_at' => now()]);
            }
            $sync->recompute($room);
            $sync->syncTenantRole($contract->tenant);
        }, 3);

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
            'transfer_date' => 'required|date|after_or_equal:'.$contract->start_date->toDateString().'|before_or_equal:today',
            'new_monthly_price' => 'required|integer|min:0|max:99999999999',
            'deposit_transfer' => 'required|integer|min:0|max:99999999999',
            'extra_fee' => 'nullable|integer|min:0|max:99999999999',
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
            $rooms = Room::whereIn('id', [$contract->room_id, $newRoom->id])->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            $contract = Contract::lockForUpdate()->findOrFail($contract->id);
            if ($contract->status !== 'active') {
                throw ValidationException::withMessages(['contract' => 'Hợp đồng hiện tại không hoạt động.']);
            }
            $newRoom = $rooms->get($newRoom->id);
            app(RoomStatusSyncService::class)->ensureAvailable($newRoom);
            if ($contract->occupants > $newRoom->max_occupants) {
                throw ValidationException::withMessages(['new_room_id' => 'Phòng mới không đủ sức chứa.']);
            }
            $depositInvoices = $contract->invoices()->deposit()->lockForUpdate()->get();
            if ($contract->invoices()->whereHas('payments', fn ($q) => $q->where('requires_review', true))->exists()) {
                throw ValidationException::withMessages(['contract' => 'Hoàn tất đối soát khoản thu trước khi chuyển phòng.']);
            }
            $carried = app(\App\Services\ContractSettlementService::class)->availableDeposit($contract);
            if ((int) $validated['deposit_transfer'] < $carried) {
                throw ValidationException::withMessages(['deposit_transfer' => 'Cọc mới thấp hơn cọc đang giữ. Hãy quyết toán và hoàn phần chênh lệch có chứng từ trước khi lập hợp đồng mới.']);
            }
            // 1. Kết thúc hợp đồng cũ
            $contract->update([
                'status' => 'terminated',
                'terminated_at' => $validated['transfer_date'],
                'notes' => $contract->notes . "\n[Chuyển sang P." . $newRoom->name . " ngày " . \Carbon\Carbon::parse($validated['transfer_date'])->format('d/m/Y') . "]"
            ]);
            app(RoomStatusSyncService::class)->recompute($rooms->get($contract->room_id));

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
                'notes' => "[Chuyển từ P." . $contract->room->name . " sang]\n" . ($validated['notes'] ?? ''),
            ]);

            // Đổi trạng thái phòng mới
            $newRoom->update(['status' => 'rented']);

            $contract->update(['transferred_to_contract_id' => $newContract->id, 'deposit_transferred' => $carried]);
            foreach ($depositInvoices as $oldInvoice) {
                $oldInvoice->update(['voided_at' => now(), 'debt' => 0]);
            }
            $newDeposit = (int) $validated['deposit_transfer'];
            if ($newDeposit > 0) {
                $depositInvoice = \App\Models\Invoice::create([
                    'contract_id' => $newContract->id, 'type' => 'deposit', 'month' => null, 'year' => null,
                    'room_fee' => 0,
                    'total' => $newDeposit, 'paid_amount' => $carried, 'debt' => $newDeposit - $carried,
                    'due_date' => \Carbon\Carbon::parse($validated['transfer_date'])->addDays(3)->toDateString(),
                    'status' => $carried === $newDeposit ? 'paid' : ($carried > 0 ? 'partial' : 'unpaid'),
                    'notes' => 'Cọc chuyển từ hợp đồng #'.$contract->id.'. Cọc đã chuyển: '.number_format($carried).'đ.',
                ]);
                if ($carried > 0) {
                    \App\Models\Payment::create([
                        'invoice_id' => $depositInvoice->id, 'amount' => $carried, 'method' => 'other', 'kind' => 'deposit_transfer',
                        'paid_at' => now(), 'received_by' => (string) auth()->id(), 'note' => 'Cọc chuyển nội bộ từ hợp đồng #'.$contract->id.'.',
                    ]);
                }
            }
            $extraFee = (int) ($validated['extra_fee'] ?? 0);
            if ($extraFee > 0) {
                \App\Models\Invoice::create([
                    'contract_id' => $newContract->id,
                    'type' => 'adjustment',
                    'month' => (int)\Carbon\Carbon::parse($validated['transfer_date'])->format('m'),
                    'year' => (int)\Carbon\Carbon::parse($validated['transfer_date'])->format('Y'),
                    'room_fee' => 0,
                    'electricity_fee' => 0,
                    'water_fee' => 0,
                    'service_fee' => $extraFee,
                    'total' => $extraFee,
                    'paid_amount' => 0,
                    'debt' => $extraFee,
                    'due_date' => \Carbon\Carbon::parse($validated['transfer_date'])->addDays(3)->format('Y-m-d'),
                    'status' => 'unpaid',
                    'notes' => 'Phụ phí chuyển từ P.'.$contract->room->name.' sang P.'.$newRoom->name.'.',
                ]);
            }
        });

        return redirect()->route('admin.contracts.index')->with('success', 'Chuyển phòng thành công!');
    }
}
