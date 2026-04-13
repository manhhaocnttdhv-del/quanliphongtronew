<x-app-layout>
    <x-slot name="header">Lịch sử Hóa Đơn & Thanh toán</x-slot>

    <div class="card card-round">
        <div class="card-header">
            <div class="card-title">Tất cả Hóa Đơn</div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Kỳ thu</th>
                            <th>Phòng</th>
                            <th class="text-end">Tổng cộng</th>
                            <th class="text-end">Đã trả</th>
                            <th class="text-end">Còn nợ</th>
                            <th class="text-center">Trạng thái</th>
                            <th class="text-end">Chi tiết</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($invoices as $invoice)
                        <tr>
                            <td>
                                <span class="fw-bold">Tháng {{ $invoice->month }}/{{ $invoice->year }}</span>
                                <br><small class="text-muted">Hạn: {{ \Carbon\Carbon::parse($invoice->due_date)->format('d/m/Y') }}</small>
                            </td>
                            <td class="text-primary fw-semibold">P.{{ $invoice->contract->room->name }}</td>
                            <td class="text-end fw-bold">{{ number_format($invoice->total,0,',','.')}}đ</td>
                            <td class="text-end text-muted">{{ number_format($invoice->paid_amount,0,',','.')}}đ</td>
                            <td class="text-end fw-bold {{ $invoice->debt > 0 ? 'text-danger' : 'text-success' }}">
                                {{ number_format($invoice->debt,0,',','.')}}đ
                            </td>
                            <td class="text-center">
                                @if($invoice->status=='unpaid')     <span class="badge badge-secondary">Chưa đóng</span>
                                @elseif($invoice->status=='partial') <span class="badge badge-warning">Đóng 1 phần</span>
                                @elseif($invoice->status=='paid')    <span class="badge badge-success">Hoàn tất</span>
                                @elseif($invoice->status=='overdue') <span class="badge badge-danger">Quá hạn</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('tenant.invoices.show', $invoice) }}" class="btn btn-sm btn-primary btn-round">
                                    <i class="fas fa-eye me-1"></i> Xem & QR
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="fas fa-file-invoice fa-2x mb-2 d-block opacity-50"></i>
                                Bạn chưa có hóa đơn nào
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($invoices->hasPages())
            <div class="px-3 py-3 border-top">{{ $invoices->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>
