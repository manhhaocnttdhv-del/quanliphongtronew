<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\ContractSettlement;
use App\Services\ContractSettlementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class SettlementController extends Controller
{
    public function create(Request $request, Contract $contract, ContractSettlementService $service)
    {
        if ($contract->settlement) {
            return redirect()->route('admin.settlements.show', $contract->settlement);
        }
        abort_unless($contract->status === 'active', 409, 'Hợp đồng không còn hoạt động.');
        $contract->load(['room.house', 'tenant.user']);
        $latest = $contract->invoices()->monthly()->orderByDesc('year')->orderByDesc('month')->first();
        $deposit = $service->availableDeposit($contract);
        $data = $request->validate(['draft' => 'nullable|uuid']);
        $draft = isset($data['draft']) ? $request->session()->get('settlement_previews.'.$data['draft']) : null;
        $draftInput = $draft && $draft['contract_id'] === $contract->id && $draft['expires'] >= now()->timestamp ? $draft['input'] : [];

        return view('admin.settlements.create', compact('contract', 'latest', 'deposit', 'draftInput'));
    }

    public function preview(Request $request, Contract $contract, ContractSettlementService $service)
    {
        $data = $request->validate([
            'checkout_date' => 'required|date|before_or_equal:today',
            'electricity_old' => 'required|numeric|min:0|max:9999999|decimal:0,2',
            'electricity_new' => 'required|numeric|min:0|max:9999999|decimal:0,2',
            'water_old' => 'required|numeric|min:0|max:9999999|decimal:0,2',
            'water_new' => 'required|numeric|min:0|max:9999999|decimal:0,2',
            'damage_fee' => 'required|integer|min:0|max:99999999999', 'other_fee' => 'required|integer|min:0|max:99999999999',
            'deposit_held' => 'required|integer|min:0|max:99999999999', 'handover_notes' => 'required|string|min:10|max:5000',
        ]);
        $breakdown = $service->calculate($contract, $data);
        $token = (string) Str::uuid();
        $request->session()->put('settlement_previews.'.$token, [
            'contract_id' => $contract->id, 'input' => $data, 'breakdown' => $breakdown, 'expires' => now()->addMinutes(20)->timestamp,
        ]);

        return view('admin.settlements.preview', compact('contract', 'data', 'breakdown', 'token'));
    }

    public function store(Request $request, Contract $contract, ContractSettlementService $service)
    {
        $data = $request->validate(['token' => 'required|uuid']);
        $preview = $request->session()->get('settlement_previews.'.$data['token']);
        if (! $preview || $preview['expires'] < now()->timestamp || $preview['contract_id'] !== $contract->id) {
            throw ValidationException::withMessages(['token' => 'Bản xem trước đã hết hạn hoặc không đúng hợp đồng.']);
        }
        $settlement = $service->finalize($contract, $preview['input'], $preview['breakdown'], $request->user()->id);
        $request->session()->forget('settlement_previews.'.$data['token']);

        return redirect()->route('admin.settlements.show', $settlement)->with('success', 'Đã chốt quyết toán và bàn giao phòng.');
    }

    public function show(ContractSettlement $settlement)
    {
        $settlement->load(['contract.room.house', 'contract.tenant.user', 'creator', 'invoice']);
        $currentDebt = (int) $settlement->contract->invoices()->where('type', '!=', 'deposit')->whereNull('voided_at')->sum('debt');

        return view('admin.settlements.show', compact('settlement', 'currentDebt'));
    }

    public function refund(Request $request, ContractSettlement $settlement, ContractSettlementService $service)
    {
        $data = $request->validate([
            'reference' => 'required|string|min:3|max:150', 'evidence' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'confirmed' => 'accepted',
        ]);
        $path = $request->file('evidence')->store('finance/settlement-refunds/'.$settlement->id, 'private');
        try {
            $service->confirmRefund($settlement, $data + ['evidence_path' => $path], $request->user()->id);
        } catch (Throwable $exception) {
            Storage::disk('private')->delete($path);
            throw $exception;
        }

        return back()->with('success', 'Đã ghi nhận hoàn cọc.');
    }

    public function evidence(ContractSettlement $settlement)
    {
        $path = $settlement->refund_evidence_path;
        abort_unless($path && Storage::disk('private')->exists($path), 404);

        return Storage::disk('private')->download($path, basename($path), ['Cache-Control' => 'private, no-store', 'X-Content-Type-Options' => 'nosniff']);
    }
}
