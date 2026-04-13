<x-app-layout>
    <x-slot name="header">Trang Chủ Khách Thuê</x-slot>

    {{-- Welcome banner --}}
    <div class="card bg-primary text-white card-round mb-4">
        <div class="card-body py-3">
            <h5 class="fw-bold mb-0"><i class="fas fa-hand-wave me-2"></i>Xin chào, {{ Auth::user()->name }}!</h5>
            <p class="mb-0 opacity-75 small mt-1">Chào mừng bạn trở lại hệ thống quản lý phòng trọ</p>
        </div>
    </div>

    @if(!$contract)
        <div class="alert alert-warning text-center py-5">
            <i class="fas fa-info-circle fa-3x mb-3 d-block text-warning opacity-75"></i>
            <h5 class="fw-bold">Bạn chưa có phòng</h5>
            <p class="mb-0 text-muted">Hệ thống không tìm thấy hợp đồng thuê phòng nào đang có hiệu lực. Hãy liên hệ Quản lý nếu có sai sót.</p>
        </div>
    @else

    {{-- Debt alert --}}
    @if($unpaidAmount > 0)
    <div class="alert alert-danger d-flex justify-content-between align-items-center">
        <div>
            <strong><i class="fas fa-exclamation-triangle me-1"></i> Cần Thanh Toán!</strong>
            <p class="mb-0 small">Bạn đang có <strong>{{ number_format($unpaidAmount,0,',','.')}}đ</strong> chưa thanh toán.</p>
        </div>
        <a href="{{ route('tenant.invoices.index') }}" class="btn btn-danger btn-sm btn-round">Xem ngay</a>
    </div>
    @else
    <div class="alert alert-success">
        <i class="fas fa-check-circle me-1"></i> <strong>Tuyệt vời!</strong> Bạn đã thanh toán đầy đủ tất cả các khoản phí.
    </div>
    @endif

    <div class="row">
        {{-- Left: Room Info --}}
        <div class="col-lg-4">
            <div class="card card-round">
                <div class="card-header"><div class="card-title"><i class="fas fa-door-open text-primary me-2"></i>Phòng Đang Ở</div></div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <h1 class="text-primary fw-bold mb-0">P.{{ $contract->room->name }}</h1>
                        <p class="text-muted mb-0">{{ $contract->room->house->name }}</p>
                        <small class="text-muted">{{ $contract->room->house->address }}</small>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between py-1">
                        <span class="text-muted">Giá thuê</span>
                        <span class="fw-bold text-primary">{{ number_format($contract->monthly_price,0,',','.')}}đ/tháng</span>
                    </div>
                    <div class="d-flex justify-content-between py-1">
                        <span class="text-muted">Tiền cọc</span>
                        <span class="fw-semibold">{{ number_format($contract->deposit,0,',','.')}}đ</span>
                    </div>
                    <div class="d-flex justify-content-between py-1">
                        <span class="text-muted">Thời hạn HĐ</span>
                        <span class="fw-semibold small">{{ \Carbon\Carbon::parse($contract->start_date)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($contract->end_date)->format('d/m/Y') }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right: Announcements + Invoices --}}
        <div class="col-lg-8">
            {{-- Announcements --}}
            <div class="card card-round mb-3">
                <div class="card-header"><div class="card-title"><i class="fas fa-bullhorn text-warning me-2"></i>Bảng Tin & Thông Báo</div></div>
                <div class="card-body p-0">
                    @forelse($announcements as $announcement)
                    <div class="d-flex gap-3 p-3 border-bottom {{ $announcement->is_pinned ? 'bg-warning bg-opacity-10' : '' }}">
                        <div class="flex-shrink-0">
                            @if($announcement->type=='notice')
                                <span class="badge badge-info"><i class="fas fa-info"></i></span>
                            @elseif($announcement->type=='warning')
                                <span class="badge badge-danger"><i class="fas fa-exclamation"></i></span>
                            @else
                                <span class="badge badge-purple"><i class="fas fa-calendar"></i></span>
                            @endif
                        </div>
                        <div>
                            <p class="fw-bold mb-1">
                                {{ $announcement->title }}
                                @if($announcement->is_pinned)
                                    <i class="fas fa-thumbtack text-warning ms-1 small"></i>
                                @endif
                            </p>
                            <p class="small text-muted mb-1">{{ \Carbon\Carbon::parse($announcement->published_at)->diffForHumans() }}</p>
                            <p class="small mb-0" style="white-space:pre-line">{{ $announcement->content }}</p>
                        </div>
                    </div>
                    @empty
                    <div class="py-4 text-center text-muted">Không có thông báo mới</div>
                    @endforelse
                </div>
            </div>

            {{-- Recent Invoices --}}
            <div class="card card-round">
                <div class="card-header">
                    <div class="card-head-row">
                        <div class="card-title"><i class="fas fa-file-invoice-dollar text-success me-2"></i>Hóa Đơn Gần Đây</div>
                        <a href="{{ route('tenant.invoices.index') }}" class="btn btn-sm btn-link">Xem tất cả</a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Kỳ Thu</th>
                                    <th class="text-end">Tổng</th>
                                    <th class="text-end">Còn nợ</th>
                                    <th class="text-center">Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($invoices as $invoice)
                                <tr>
                                    <td>
                                        <span class="fw-semibold">Tháng {{ $invoice->month }}/{{ $invoice->year }}</span>
                                        <br><small class="text-muted">Hạn: {{ \Carbon\Carbon::parse($invoice->due_date)->format('d/m/Y') }}</small>
                                    </td>
                                    <td class="text-end fw-bold">{{ number_format($invoice->total,0,',','.')}}đ</td>
                                    <td class="text-end fw-bold {{ $invoice->debt > 0 ? 'text-danger' : 'text-success' }}">
                                        {{ number_format($invoice->debt,0,',','.')}}đ
                                    </td>
                                    <td class="text-center">
                                        @if($invoice->status=='unpaid')     <span class="badge badge-secondary">Chưa đóng</span>
                                        @elseif($invoice->status=='partial') <span class="badge badge-warning">Đóng thiếu</span>
                                        @elseif($invoice->status=='paid')    <span class="badge badge-success">Hoàn tất</span>
                                        @elseif($invoice->status=='overdue') <span class="badge badge-danger">Quá hạn</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="4" class="text-center text-muted py-4">Bạn chưa có hóa đơn nào</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</x-app-layout>
