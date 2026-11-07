<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\House;
use App\Models\Invoice;
use App\Models\Room;
use App\Models\Setting;
use App\Notifications\NewInvoiceNotification;
use App\Services\InvoiceCalculationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BulkInvoiceController extends Controller
{
    public function index(Request $request, InvoiceCalculationService $calculator)
    {
        $data = $request->validate(['period' => 'nullable|date_format:Y-m', 'house_id' => 'nullable|exists:houses,id', 'draft' => 'nullable|uuid']);
        $draft = isset($data['draft']) ? $request->session()->get('billing_previews.'.$data['draft']) : null;
        if ($draft && $draft['expires'] < now()->timestamp) {
            $draft = null;
        }
        $formInput = $draft['form_input'] ?? $draft['input'] ?? [];
        $draftRows = collect($formInput['rows'] ?? [])->keyBy('contract_id');
        $period = Carbon::createFromFormat('!Y-m', $formInput['period'] ?? $data['period'] ?? today()->format('Y-m'));
        $houseId = $formInput['house_id'] ?? $data['house_id'] ?? null;
        $contracts = Contract::with(['room.house', 'room.services', 'tenant.user'])
            ->where('status', 'active')->whereDate('start_date', '<=', $period->copy()->endOfMonth())
            ->whereDate('end_date', '>=', $period)->when($houseId, fn ($q) => $q->whereHas('room', fn ($q) => $q->where('house_id', $houseId)))
            ->orderBy('room_id')->get();
        $rows = $contracts->map(function ($contract) use ($calculator, $period) {
            $previous = $calculator->previous($contract, $period);

            return [
                'contract' => $contract, 'electricity_old' => $previous?->electricity_new ?? 0,
                'water_old' => $previous?->water_new ?? 0, 'rates' => $calculator->rates($contract),
                'exists' => $contract->invoices()->monthly()->where('month', $period->month)->where('year', $period->year)->exists(),
            ];
        });
        $houses = House::orderBy('name')->get();
        $dueDate = $period->copy()->addMonth()->addDays((int) Setting::get('invoice_due_days', 5) - 1)->toDateString();
        $dueDate = $draft['input']['due_date'] ?? $dueDate;

        return view('admin.billing.index', compact('rows', 'houses', 'period', 'dueDate', 'draftRows', 'draft', 'houseId'));
    }

    public function preview(Request $request, InvoiceCalculationService $calculator)
    {
        $data = $request->validate([
            'period' => 'required|date_format:Y-m', 'due_date' => 'required|date|after_or_equal:period',
            'house_id' => 'nullable|exists:houses,id',
            'rows' => 'required|array|min:1|max:500', 'rows.*.selected' => 'sometimes|boolean',
            'rows.*.contract_id' => 'required|integer|distinct|exists:contracts,id',
            'rows.*.electricity_old' => 'required|numeric|min:0|max:9999999|decimal:0,2',
            'rows.*.electricity_new' => 'required|numeric|min:0|max:9999999|decimal:0,2',
            'rows.*.water_old' => 'required|numeric|min:0|max:9999999|decimal:0,2',
            'rows.*.water_new' => 'required|numeric|min:0|max:9999999|decimal:0,2',
        ]);
        $formInput = $data;
        $data['rows'] = array_values(array_filter($data['rows'], fn ($row) => ! empty($row['selected'])));
        if (! $data['rows']) {
            throw ValidationException::withMessages(['rows' => 'Vui lòng chọn ít nhất một phòng.']);
        }
        $period = Carbon::createFromFormat('!Y-m', $data['period']);
        $rows = [];
        foreach ($data['rows'] as $row) {
            $contract = Contract::with(['room.services', 'room.house', 'tenant.user'])->findOrFail($row['contract_id']);
            $rows[] = ['contract' => $contract, 'invoice' => $calculator->monthly($contract, $row, $period, $data['due_date'])];
        }
        $token = (string) Str::uuid();
        $request->session()->put('billing_previews.'.$token, ['input' => $data, 'form_input' => $formInput, 'invoices' => array_column($rows, 'invoice'), 'expires' => now()->addMinutes(20)->timestamp]);

        return view('admin.billing.preview', compact('rows', 'period', 'token'));
    }

    public function store(Request $request, InvoiceCalculationService $calculator)
    {
        $data = $request->validate(['token' => 'required|uuid']);
        $preview = $request->session()->get('billing_previews.'.$data['token']);
        if (! $preview || $preview['expires'] < now()->timestamp) {
            throw ValidationException::withMessages(['token' => 'Bản xem trước đã hết hạn hoặc đã được sử dụng.']);
        }
        $count = DB::transaction(function () use ($preview, $calculator) {
            $ids = array_column($preview['input']['rows'], 'contract_id');
            $roomIds = Contract::whereIn('id', $ids)->pluck('room_id');
            Room::whereIn('id', $roomIds)->orderBy('id')->lockForUpdate()->get();
            $contracts = Contract::with('room.services')->whereIn('id', $ids)->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            $period = Carbon::createFromFormat('!Y-m', $preview['input']['period']);
            foreach ($preview['input']['rows'] as $index => $row) {
                $contract = $contracts->get($row['contract_id']);
                abort_unless($contract, 409);
                $attributes = $calculator->monthly($contract, $row, $period, $preview['input']['due_date']);
                if ($attributes != $preview['invoices'][$index]) {
                    throw ValidationException::withMessages(['rows' => 'Biểu phí đã thay đổi. Vui lòng xem trước lại.']);
                }
                $invoice = Invoice::create($attributes);
                if (Setting::get('notify_new_invoice', '1') === '1') {
                    $contract->tenant->user?->notify(new NewInvoiceNotification($invoice));
                }
            }

            return count($ids);
        }, 3);
        $request->session()->forget('billing_previews.'.$data['token']);

        return redirect()->route('admin.invoices.index')->with('success', 'Đã lập '.$count.' hóa đơn.');
    }
}
