<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\MaintenanceTicket;
use App\Models\Contract;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\NewMaintenanceTicketNotification;

class MaintenanceTicketController extends Controller
{
    /**
     * Lấy hợp đồng đang hoạt động của Tenant hiện tại.
     */
    private function getActiveContract()
    {
        return Contract::whereHas('tenant', function ($q) {
            $q->where('user_id', Auth::id());
        })->where('status', 'active')->first();
    }

    /**
     * Danh sách tất cả ticket sự cố của tenant.
     */
    public function index()
    {
        $user = Auth::user();
        
        $tickets = MaintenanceTicket::whereHas('contract.tenant', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })->with('contract.room')->latest()->paginate(10);

        $contract = $this->getActiveContract();

        return view('tenant.maintenance-tickets.index', compact('tickets', 'contract'));
    }

    /**
     * Form gửi báo cáo sự cố mới.
     */
    public function create()
    {
        $contract = $this->getActiveContract();
        if (!$contract) {
            return redirect()->route('tenant.dashboard')->with('error', 'Bạn không có hợp đồng đang hoạt động để gửi báo cáo sự cố.');
        }
        return view('tenant.maintenance-tickets.create', compact('contract'));
    }

    /**
     * Lưu báo cáo sự cố mới.
     */
    public function store(Request $request)
    {
        $contract = $this->getActiveContract();
        if (!$contract) {
            return redirect()->route('tenant.dashboard')->with('error', 'Không tìm thấy hợp đồng đang hoạt động.');
        }

        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'priority'    => 'required|in:low,medium,high',
            'image'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('maintenance', 'public');
        }

        MaintenanceTicket::create([
            'contract_id' => $contract->id,
            'title'       => $validated['title'],
            'description' => $validated['description'],
            'priority'    => $validated['priority'],
            'image_path'  => $imagePath,
            'status'      => 'pending',
        ]);

        // Gửi thông báo cho tất cả admin nếu setting bật
        if (Setting::get('notify_maintenance', '1') === '1') {
            $ticket = MaintenanceTicket::with(['contract.room', 'contract.tenant.user'])
                ->where('contract_id', $contract->id)
                ->latest()->first();
            $admins = User::role('admin')->get();
            foreach ($admins as $admin) {
                $admin->notify(new NewMaintenanceTicketNotification($ticket));
            }
        }

        return redirect()->route('tenant.maintenance-tickets.index')
                         ->with('success', 'Báo cáo sự cố của bạn đã được gửi! Quản lý sẽ xem xét và phản hồi sớm nhất.');
    }

    /**
     * Xem chi tiết một phiếu sự cố.
     */
    public function show(MaintenanceTicket $maintenanceTicket)
    {
        // Bảo vệ: chỉ xem ticket của chính mình
        if ($maintenanceTicket->contract->tenant->user_id !== Auth::id()) {
            abort(403, 'Bạn không có quyền xem phiếu sự cố này.');
        }

        $maintenanceTicket->load('contract.room.house');
        return view('tenant.maintenance-tickets.show', compact('maintenanceTicket'));
    }

    /**
     * Cập nhật (tenant chỉ có thể huỷ ticket khi còn đang pending).
     */
    public function update(Request $request, MaintenanceTicket $maintenanceTicket)
    {
        if ($maintenanceTicket->contract->tenant->user_id !== Auth::id()) {
            abort(403);
        }

        if ($maintenanceTicket->status !== 'pending') {
            return back()->with('error', 'Chỉ có thể huỷ phiếu khi đang chờ xử lý.');
        }

        $maintenanceTicket->update(['status' => 'cancelled']);
        return redirect()->route('tenant.maintenance-tickets.index')->with('success', 'Đã huỷ phiếu báo cáo sự cố.');
    }
}
