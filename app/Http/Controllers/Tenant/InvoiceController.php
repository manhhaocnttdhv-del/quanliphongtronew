<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Invoice;
use App\Models\Contract;

class InvoiceController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Tìm hợp đồng của tenant (có thể tenant có nhiều hợp đồng lịch sử, nhưng ta lấy toàn bộ Invoice thuộc tenant này)
        $invoices = Invoice::whereHas('contract.tenant', function($q) use ($user) {
            $q->where('user_id', $user->id);
        })->with('contract.room')
          ->orderBy('created_at', 'desc')
          ->paginate(12);
        
        return view('tenant.invoices.index', compact('invoices'));
    }

    public function show(Invoice $invoice)
    {
        // Phải đảm bảo invoice này thuộc về user đang đăng nhập
        if ($invoice->contract->tenant->user_id !== Auth::id()) {
            abort(403, 'Bạn không có quyền xem hóa đơn này.');
        }

        $invoice->load(['contract.room.house', 'contract.tenant.user']);
        
        // Tồn tại hóa đơn nghĩa là có khoản phí, có thể thêm tính vietqr vào đây sau
        
        return view('tenant.invoices.show', compact('invoice'));
    }
}
