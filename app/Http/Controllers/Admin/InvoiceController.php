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
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

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
            'electricity_old' => 'required|numeric|min:0',
            'electricity_new' => 'required|numeric|gte:electricity_old',
            'water_old' => 'required|numeric|min:0',
            'water_new' => 'required|numeric|gte:water_old',
            'electricity_fee' => 'required|numeric|min:0',
            'water_fee' => 'required|numeric|min:0',
            'service_fee' => 'required|numeric|min:0',
            'due_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $invoice = DB::transaction(function () use ($validated) {
            $candidate = Contract::findOrFail($validated['contract_id']);
            Room::lockForUpdate()->findOrFail($candidate->room_id);
            $contract = Contract::lockForUpdate()->findOrFail($candidate->id);
            $period = Carbon::create($validated['year'], $validated['month'], 1)->startOfDay();
            if ($contract->status !== 'active' || $contract->settlement()->exists()
                || $contract->start_date->gt($period->copy()->endOfMonth()) || $contract->end_date->lt($period)) {
                throw ValidationException::withMessages(['contract_id' => 'Hợp đồng không có hiệu lực trong kỳ hóa đơn.']);
            }
            if ($contract->invoices()->monthly()->where('month', $validated['month'])->where('year', $validated['year'])->exists()) {
                throw ValidationException::withMessages(['month' => 'Hóa đơn tháng này của hợp đồng đã được lập.']);
            }
            $total = (int) ($validated['room_fee'] + $validated['electricity_fee'] + $validated['water_fee'] + $validated['service_fee']);
            if ($total > 999999999999) {
                throw ValidationException::withMessages(['room_fee' => 'Tổng hóa đơn vượt giới hạn cho phép.']);
            }
            $invoice = new Invoice(['due_date' => $validated['due_date']]);
            return Invoice::create($validated + [
                'type' => 'monthly', 'electricity_usage' => $validated['electricity_new'] - $validated['electricity_old'],
                'water_usage' => $validated['water_new'] - $validated['water_old'],
            ] + $invoice->balanceAttributes($total, 0));
        }, 3);

        // Gửi thông báo cho tenant nếu setting bật
        if (Setting::get('notify_new_invoice', '1') === '1') {
            $contract = Contract::with('tenant.user')->find($validated['contract_id']);
            if ($contract && $contract->tenant && $contract->tenant->user) {
                $contract->tenant->user->notify(new NewInvoiceNotification($invoice));
            }
        }

        return redirect()->route('admin.invoices.index')->with('success', 'Lập hóa đơn thành công!');
    }

    public function show(Invoice $invoice)
    {
        $invoice->load(['contract.room.house', 'contract.tenant.user', 'payments.resolution', 'paymentOrders']);
        return view('admin.invoices.show', compact('invoice'));
    }

    public function edit(Invoice $invoice)
    {
        if ($invoice->type === 'deposit' || $invoice->voided_at) {
            return redirect()->route('admin.invoices.show', $invoice)->with('error', 'Hóa đơn cọc được xử lý trong luồng đặt phòng.');
        }
        return view('admin.invoices.edit', compact('invoice'));
    }

    public function update(Request $request, Invoice $invoice)
    {
        if ($invoice->type === 'deposit' || $invoice->voided_at) {
            throw ValidationException::withMessages(['invoice' => 'Không sửa tiền cọc bằng biểu mẫu hóa đơn tháng.']);
        }
        // Thường update hóa đơn là ghi nhận thanh toán
        $validated = $request->validate([
            'electricity_old' => 'required|numeric|min:0',
            'electricity_new' => 'required|numeric|gte:electricity_old',
            'water_old' => 'required|numeric|min:0',
            'water_new' => 'required|numeric|gte:water_old',
            'electricity_fee' => 'required|numeric|min:0',
            'water_fee' => 'required|numeric|min:0',
            'room_fee' => 'required|numeric|min:0',
            'service_fee' => 'required|numeric|min:0',
            'paid_amount' => 'required|numeric|min:0',
            'status' => 'sometimes|in:unpaid,partial,paid,overdue',
            'notes' => 'nullable|string',
        ]);

        $invoice = DB::transaction(function () use ($invoice, $validated) {
            Room::lockForUpdate()->findOrFail($invoice->contract->room_id);
            $invoice = Invoice::lockForUpdate()->findOrFail($invoice->id);
            if ($invoice->voided_at) {
                throw ValidationException::withMessages(['invoice' => 'Hóa đơn đã hủy hoặc tất toán.']);
            }
            $total = (int) ($validated['room_fee'] + $validated['electricity_fee'] + $validated['water_fee'] + $validated['service_fee']);
            if ($invoice->contract->settlement()->exists()) {
                foreach (['room_fee', 'service_fee', 'electricity_fee', 'water_fee', 'electricity_old', 'electricity_new', 'water_old', 'water_new'] as $field) {
                    if ((float) $validated[$field] !== (float) $invoice->$field) {
                        throw ValidationException::withMessages(['invoice' => 'Không sửa biểu phí đã chốt trong biên bản quyết toán.']);
                    }
                }
            }
            $paid = (int) $validated['paid_amount'];
            if ($paid < (int) $invoice->paid_amount || $paid - (int) $invoice->refunded_amount > $total) {
                throw ValidationException::withMessages(['paid_amount' => 'Số tiền đã thu không được giảm hoặc vượt tổng hóa đơn.']);
            }
            $difference = $paid - (int) $invoice->paid_amount;
            $invoice->update([
                'electricity_old' => $validated['electricity_old'],
                'electricity_new' => $validated['electricity_new'],
                'electricity_usage' => $validated['electricity_new'] - $validated['electricity_old'],
                'water_old' => $validated['water_old'],
                'water_new' => $validated['water_new'],
                'water_usage' => $validated['water_new'] - $validated['water_old'],
                'electricity_fee' => $validated['electricity_fee'],
                'water_fee' => $validated['water_fee'],
                'room_fee' => $validated['room_fee'],
                'service_fee' => $validated['service_fee'],
                'notes'       => $validated['notes'] ?? null,
            ] + $invoice->balanceAttributes($total, $paid));
            if ($difference > 0) {
                Payment::create([
                    'invoice_id' => $invoice->id,
                    'amount' => $difference,
                    'method' => 'cash',
                    'paid_at' => now(),
                    'received_by' => (string) auth()->id(),
                    'note' => 'Quản lý ghi nhận thu tiền.',
                ]);
            }
            return $invoice;
        }, 3);

        // Gửi thông báo quá hạn cho tenant nếu admin đánh dấu overdue
        if ($invoice->status === 'overdue' && Setting::get('notify_overdue', '1') === '1') {
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

        // Lấy chỉ số cũ từ hóa đơn gần nhất
        $lastInvoice = Invoice::monthly()->where('contract_id', $contract->id)
            ->where(function($q) use ($request) {
                $q->where('year', '<', $request->year)
                  ->orWhere(function($q2) use ($request) {
                      $q2->where('year', $request->year)
                         ->where('month', '<', $request->month);
                  });
            })
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->first();

        $electricityOld = $lastInvoice ? $lastInvoice->electricity_new : 0;
        $waterOld = $lastInvoice ? $lastInvoice->water_new : 0;

        $electricityPrice = 0;
        $waterPrice = 0;
        $serviceFee = 0;

        foreach ($room->services as $service) {
            if (! $service->pivot->is_active) {
                continue;
            }
            $price = $service->pivot->custom_price !== null ? $service->pivot->custom_price : $service->price;
            if ($service->type === 'electricity') {
                $electricityPrice = $price;
            } elseif ($service->type === 'water') {
                $waterPrice = $price;
            } else {
                if ($service->pivot->is_active) {
                    $serviceFee += $price;
                }
            }
        }

        return response()->json([
            'room_fee' => $roomFee,
            'electricity_old' => $electricityOld,
            'water_old' => $waterOld,
            'electricity_price' => $electricityPrice,
            'water_price' => $waterPrice,
            'service_fee' => $serviceFee,
        ]);
    }
}
