<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Contract;

class ContractController extends Controller
{
    /**
     * Danh sách hợp đồng của tenant (hiện tại + lịch sử).
     */
    public function index()
    {
        $user = Auth::user();

        $contracts = Contract::whereHas('tenant', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })->with(['room.house'])->latest()->get();

        return view('tenant.contracts.index', compact('contracts'));
    }

    /**
     * Chi tiết một hợp đồng cụ thể.
     */
    public function show(Contract $contract)
    {
        // Bảo vệ: chỉ xem hợp đồng của mình
        if ($contract->tenant->user_id !== Auth::id()) {
            abort(403, 'Bạn không có quyền xem hợp đồng này.');
        }

        $contract->load(['room.house', 'tenant.user', 'invoices']);

        return view('tenant.contracts.show', compact('contract'));
    }
}
