<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Contract;
use App\Models\Invoice;
use App\Models\Announcement;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Lấy thông tin hợp đồng hiện hành của người thuê
        $contract = Contract::whereHas('tenant', function($q) use ($user) {
            $q->where('user_id', $user->id);
        })->where('status', 'active')->with(['room.house'])->first();

        $invoices = [];
        $unpaidAmount = 0;
        
        if ($contract) {
            $invoices = Invoice::where('contract_id', $contract->id)
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get();
                
            $unpaidAmount = Invoice::where('contract_id', $contract->id)
                ->whereIn('status', ['unpaid', 'partial', 'overdue'])
                ->sum('debt');
        }

        // Lấy thông báo chung
        $announcements = Announcement::whereNull('house_id');
        if ($contract) {
            $announcements = $announcements->orWhere('house_id', $contract->room->house_id);
        }
        $announcements = $announcements->latest()->take(5)->get();

        return view('tenant.dashboard', compact('contract', 'invoices', 'unpaidAmount', 'announcements'));
    }
}
