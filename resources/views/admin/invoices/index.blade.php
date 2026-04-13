<x-app-layout>
    <x-slot name="header">Hóa Đơn & Thu Tiền</x-slot>

    <div class="card card-round">
        <div class="card-header">
            <div class="card-head-row flex-wrap gap-2">
                <div class="card-title">Danh sách Hóa đơn</div>
                <div class="card-tools d-flex align-items-center flex-wrap gap-2">
                    <a href="{{ route('admin.invoices.create') }}" class="btn btn-primary btn-round btn-sm">
                        <i class="fas fa-plus me-1"></i> Lập Hóa Đơn
                    </a>
                    <form action="{{ route('admin.invoices.index') }}" method="GET" class="d-flex gap-2 flex-wrap">
                        <select name="month" class="form-select form-select-sm" style="width:130px" onchange="this.form.submit()">
                            <option value="">-- Tất cả tháng</option>
                            @for($i=1;$i<=12;$i++)
                                <option value="{{ $i }}" {{ request('month')==$i?'selected':'' }}>Tháng {{ $i }}</option>
                            @endfor
                        </select>
                        <select name="status" class="form-select form-select-sm" style="width:155px" onchange="this.form.submit()">
                            <option value="">-- Tất cả trạng thái</option>
                            <option value="unpaid"   {{ request('status')=='unpaid'  ?'selected':'' }}>Chưa thanh toán</option>
                            <option value="partial"  {{ request('status')=='partial' ?'selected':'' }}>Đóng 1 phần</option>
                            <option value="paid"     {{ request('status')=='paid'    ?'selected':'' }}>Đã hoàn tất</option>
                            <option value="overdue"  {{ request('status')=='overdue' ?'selected':'' }}>Quá hạn</option>
                        </select>
                        @if(request()->hasAny(['month','status']))
                            <a href="{{ route('admin.invoices.index') }}" class="btn btn-sm btn-light">Bỏ lọc</a>
                        @endif
                    </form>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-items-center mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Mã HĐ</th>
                            <th>Khách & Phòng</th>
                            <th class="text-end">Tổng cộng</th>
                            <th class="text-end">Đã thu / Còn nợ</th>
                            <th class="text-center">Trạng thái</th>
                            <th class="text-end">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($invoices as $invoice)
                        <tr>
                            <td class="fw-bold text-muted" style="font-size:.8rem">
                                INV{{ $invoice->year }}{{ str_pad($invoice->month,2,'0',STR_PAD_LEFT) }}-{{ sprintf('%03d',$invoice->id) }}
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $invoice->contract->tenant->user->name ?? 'N/A' }}</div>
                                <small class="text-muted">Phòng {{ $invoice->contract->room->name ?? 'N/A' }}</small>
                            </td>
                            <td class="text-end fw-bold text-primary">{{ number_format($invoice->total,0,',','.') }}đ</td>
                            <td class="text-end">
                                <span class="text-success">{{ number_format($invoice->paid_amount,0,',','.') }}đ</span><br>
                                @if($invoice->debt > 0)
                                    <span class="badge badge-danger">Nợ: {{ number_format($invoice->debt,0,',','.') }}đ</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($invoice->status=='unpaid')     <span class="badge badge-secondary">Chưa thu</span>
                                @elseif($invoice->status=='partial') <span class="badge badge-warning">Thu thiếu</span>
                                @elseif($invoice->status=='paid')    <span class="badge badge-success">Hoàn tất</span>
                                @elseif($invoice->status=='overdue') <span class="badge badge-danger">Quá hạn</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="d-flex justify-content-end gap-1">
                                    <a href="{{ route('admin.invoices.show', $invoice) }}" class="btn btn-sm btn-secondary btn-round" title="Xem"><i class="fas fa-eye"></i></a>
                                    <a href="{{ route('admin.invoices.edit', $invoice) }}" class="btn btn-sm btn-info btn-round" title="Cập nhật"><i class="fas fa-money-bill-wave"></i></a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center text-muted py-5">Không tìm thấy hóa đơn nào.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($invoices->hasPages())
        <div class="card-footer">{{ $invoices->links() }}</div>
        @endif
    </div>
</x-app-layout>
