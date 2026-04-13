<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Contract;
use App\Models\Room;
use App\Models\MeterReading;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\NewInvoiceNotification;
use App\Notifications\OverdueInvoiceNotification;
use Illuminate\Http\Request;
use Carbon\Carbon;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $query = Invoice::with(['contract.room.house', 'contract.tenant.user'])->latest();

        if ($request->filled('month')) {
            $query->where('month', $request->month);
        }
        if ($request->filled('year')) {
            $query->where('year', $request->year);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $invoices = $query->paginate(15)->withQueryString();
        return view('admin.invoices.index', compact('invoices'));
    }

    public function create(Request $request)
    {
        $contracts = Contract::where('status', 'active')->with(['room.house', 'tenant.user'])->get();
        return view('admin.invoices.create', compact('contracts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'contract_id' => 'required|exists:contracts,id',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2000|max:2100',
            'room_fee' => 'required|numeric|min:0',
            'electricity_fee' => 'required|numeric|min:0',
            'water_fee' => 'required|numeric|min:0',
            'service_fee' => 'required|numeric|min:0',
            'due_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $exists = Invoice::where([
            'contract_id' => $validated['contract_id'],
            'month' => $validated['month'],
            'year' => $validated['year'],
        ])->exists();

        if ($exists) {
            return back()->withInput()->withErrors(['Hóa đơn tháng này của hợp đồng đã được lập!']);
        }

        $total = $validated['room_fee'] + $validated['electricity_fee'] + $validated['water_fee'] + $validated['service_fee'];

        Invoice::create($validated + [
            'total'       => $total,
            'debt'        => $total,
            'paid_amount' => 0,
            'status'      => 'unpaid'
        ]);

        // Gửi thông báo cho tenant nếu setting bật
        if (Setting::get('notify_new_invoice', '1') === '1') {
            $contract = Contract::with('tenant.user')->find($validated['contract_id']);
            if ($contract && $contract->tenant && $contract->tenant->user) {
                $invoice = Invoice::where([
                    'contract_id' => $validated['contract_id'],
                    'month'       => $validated['month'],
                    'year'        => $validated['year'],
                ])->latest()->first();
                $contract->tenant->user->notify(new NewInvoiceNotification($invoice));
            }
        }

        return redirect()->route('admin.invoices.index')->with('success', 'Lập hóa đơn thành công!');
    }

    public function show(Invoice $invoice)
    {
        $invoice->load(['contract.room.house', 'contract.tenant.user']);
        return view('admin.invoices.show', compact('invoice'));
    }

    public function edit(Invoice $invoice)
    {
        return view('admin.invoices.edit', compact('invoice'));
    }

    public function update(Request $request, Invoice $invoice)
    {
        // Thường update hóa đơn là ghi nhận thanh toán
        $validated = $request->validate([
            'paid_amount' => 'required|numeric|min:0',
            'status' => 'required|in:unpaid,partial,paid,overdue',
            'notes' => 'nullable|string',
        ]);

        $debt = $invoice->total - $validated['paid_amount'];
        
        $invoice->update([
            'paid_amount' => $validated['paid_amount'],
            'debt'        => $debt > 0 ? $debt : 0,
            'status'      => $validated['status'],
            'notes'       => $validated['notes'],
        ]);

        // Gửi thông báo quá hạn cho tenant nếu admin đánh dấu overdue
        if ($validated['status'] === 'overdue' && Setting::get('notify_overdue', '1') === '1') {
            $invoice->load('contract.tenant.user');
            if ($invoice->contract && $invoice->contract->tenant && $invoice->contract->tenant->user) {
                // Tránh gửi trùng: chỉ gửi nếu chưa có notif overdue cho invoice này
                $alreadyNotified = $invoice->contract->tenant->user
                    ->notifications()
                    ->where('type', OverdueInvoiceNotification::class)
                    ->whereJsonContains('data->type', 'overdue_invoice')
                    ->where('data->url', 'LIKE', '%/invoices/' . $invoice->id)
                    ->exists();
                if (!$alreadyNotified) {
                    $invoice->contract->tenant->user->notify(new OverdueInvoiceNotification($invoice));
                }
            }
        }

        return redirect()->route('admin.invoices.index')->with('success', 'Đã cập nhật tình trạng hóa đơn!');
    }

    public function destroy(Invoice $invoice)
    {
        $invoice->delete();
        return redirect()->route('admin.invoices.index')->with('success', 'Đã xóa hóa đơn!');
    }

    public function autoCalculate(Request $request)
    {
        $request->validate([
            'contract_id' => 'required|exists:contracts,id',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer'
        ]);

        $contract = Contract::with(['room.services'])->findOrFail($request->contract_id);
        $room = $contract->room;

        // Tiền phòng mặc định
        $roomFee = $contract->monthly_price;

        // Tiền điện nước từ chỉ số
        $meterReadings = MeterReading::where('room_id', $room->id)
            ->where('month', $request->month)
            ->where('year', $request->year)
            ->with('service')
            ->get();

        $electricityFee = null;
        $waterFee = null;

        foreach ($meterReadings as $reading) {
            if ($reading->service->type === 'electricity') {
                $electricityFee = $reading->total_amount;
            } elseif ($reading->service->type === 'water') {
                $waterFee = $reading->total_amount;
            }
        }

        // Tính tiền dịch vụ khác (không phải điện/nước)
        $serviceFee = 0;
        foreach ($room->services as $service) {
            if (!in_array($service->type, ['electricity', 'water'])) {
                $qty = $service->pivot->quantity ?? 1;
                $serviceFee += $service->price * $qty;
            }
        }

        return response()->json([
            'room_fee' => $roomFee,
            'electricity_fee' => $electricityFee,
            'water_fee' => $waterFee,
            'service_fee' => $serviceFee,
        ]);
    }
}
