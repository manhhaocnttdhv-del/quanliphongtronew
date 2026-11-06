<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\House;
use App\Models\Room;
use App\Models\Tenant;
use App\Models\Invoice;
use App\Models\MaintenanceTicket;
use App\Models\BookingRequest;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $today = now();
        $currentMonth = $today->month;
        $currentYear = $today->year;

        $roomStats = Room::query()
            ->selectRaw("COUNT(*) AS total_rooms, SUM(CASE WHEN status = 'rented' THEN 1 ELSE 0 END) AS rented_rooms")
            ->first();

        $invoiceStats = Invoice::query()
            ->selectRaw(
                'COALESCE(SUM(debt), 0) AS debt_total,
                 COALESCE(SUM(CASE WHEN type = ? AND month = ? AND year = ? THEN paid_amount ELSE 0 END), 0) AS revenue_this_month',
                ['monthly', $currentMonth, $currentYear]
            )
            ->first();

        $monthlyRevenue = Invoice::monthly()
            ->where('year', $currentYear)
            ->selectRaw('month, SUM(paid_amount) AS total')
            ->groupBy('month')
            ->pluck('total', 'month');

        $stats = [
            'total_houses' => House::count(),
            'total_rooms' => (int) ($roomStats->total_rooms ?? 0),
            'rented_rooms' => (int) ($roomStats->rented_rooms ?? 0),
            'total_tenants' => Tenant::count(),
            'revenue_this_month' => (float) ($invoiceStats->revenue_this_month ?? 0),
            'debt_total' => (float) ($invoiceStats->debt_total ?? 0),
            'pending_tickets' => MaintenanceTicket::where('status', 'pending')->count(),
            'overdue_invoices' => Invoice::where('status', 'overdue')->where('debt', '>', 0)->count(),
            'pending_bookings' => BookingRequest::where('status', 'pending')->count(),
        ];
        
        $stats['occupancy_rate'] = $stats['total_rooms'] > 0 
            ? round(($stats['rented_rooms'] / $stats['total_rooms']) * 100) 
            : 0;

        // Dữ liệu biểu đồ doanh thu theo từng tháng của năm hiện tại
        $revenueData = array_map(
            static fn (int $month): float => (float) ($monthlyRevenue->get($month, 0)),
            range(1, 12)
        );
        $stats['revenue_chart_data'] = $revenueData;
        $stats['current_year'] = $currentYear;

        // Hoạt động gần đây (Recent Activities)
        $recent_invoices = Invoice::query()
                                  ->select(['id', 'contract_id', 'month', 'year', 'total', 'status', 'created_at'])
                                  ->with([
                                      'contract:id,room_id,tenant_id',
                                      'contract.room:id,name',
                                      'contract.tenant:id,user_id',
                                      'contract.tenant.user:id,name',
                                  ])
                                  ->latest()
                                  ->limit(5)
                                  ->get();
                                  
        $recent_tickets = MaintenanceTicket::query()
                                           ->select(['id', 'contract_id', 'title', 'status', 'created_at'])
                                           ->with([
                                               'contract:id,room_id',
                                               'contract.room:id,name',
                                           ])
                                           ->latest()
                                           ->limit(5)
                                           ->get();

        $branches = House::query()->withCount([
            'rooms',
            'rooms as rented_rooms_count' => fn ($query) => $query->where('status', 'rented'),
            'rooms as available_rooms_count' => fn ($query) => $query->where('status', 'available'),
        ])->orderBy('name')->get(['id', 'name', 'address']);

        return view('dashboard', compact('stats', 'recent_invoices', 'recent_tickets', 'branches'));
    }
}
