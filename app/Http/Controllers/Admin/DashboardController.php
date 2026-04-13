<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\House;
use App\Models\Room;
use App\Models\Tenant;
use App\Models\Invoice;
use App\Models\MaintenanceTicket;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_houses' => House::count(),
            'total_rooms' => Room::count(),
            'rented_rooms' => Room::where('status', 'rented')->count(),
            'total_tenants' => Tenant::count(),
            
            // Doanh thu tháng này
            'revenue_this_month' => Invoice::where('month', date('n'))
                                           ->where('year', date('Y'))
                                           ->sum('paid_amount'),
                                           
            // Nợ cần thu
            'debt_total' => Invoice::sum('debt'),
            
            // Sự cố chờ xử lý
            'pending_tickets' => MaintenanceTicket::where('status', 'pending')->count()
        ];
        
        $stats['occupancy_rate'] = $stats['total_rooms'] > 0 
            ? round(($stats['rented_rooms'] / $stats['total_rooms']) * 100) 
            : 0;

        // Hoạt động gần đây (Recent Activities)
        $recent_invoices = Invoice::with(['contract.room.house', 'contract.tenant.user'])
                                  ->latest()->take(5)->get();
                                  
        $recent_tickets = MaintenanceTicket::with(['contract.room', 'contract.tenant.user'])
                                           ->latest()->take(5)->get();

        return view('dashboard', compact('stats', 'recent_invoices', 'recent_tickets'));
    }
}
