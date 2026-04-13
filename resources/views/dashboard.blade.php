<x-app-layout>
    <x-slot name="header">Dashboard</x-slot>

    {{-- ══ KPI Cards ══ --}}
    <div class="row">
        {{-- Doanh thu --}}
        <div class="col-sm-6 col-md-3">
            <div class="card card-stats card-round">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-icon">
                            <div class="icon-big text-center icon-primary bubble-shadow-small">
                                <i class="fas fa-dollar-sign"></i>
                            </div>
                        </div>
                        <div class="col col-stats ms-3 ms-sm-0">
                            <div class="numbers">
                                <p class="card-category">Doanh thu tháng {{ date('n') }}</p>
                                <h4 class="card-title">{{ number_format($stats['revenue_this_month'] / 1000000, 1) }}tr đ</h4>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="stats">
                        <i class="fas fa-clock me-1"></i>
                        Nợ chờ thu: <strong>{{ number_format($stats['debt_total'], 0, ',', '.') }}đ</strong>
                    </div>
                </div>
            </div>
        </div>

        {{-- Lấp đầy --}}
        <div class="col-sm-6 col-md-3">
            <div class="card card-stats card-round">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-icon">
                            <div class="icon-big text-center icon-success bubble-shadow-small">
                                <i class="fas fa-home"></i>
                            </div>
                        </div>
                        <div class="col col-stats ms-3 ms-sm-0">
                            <div class="numbers">
                                <p class="card-category">Tỷ lệ lấp đầy</p>
                                <h4 class="card-title">{{ $stats['occupancy_rate'] }}%</h4>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="stats">
                        <i class="fas fa-door-open me-1"></i>
                        {{ $stats['rented_rooms'] }} / {{ $stats['total_rooms'] }} phòng đang thuê
                    </div>
                </div>
            </div>
        </div>

        {{-- Khách thuê --}}
        <div class="col-sm-6 col-md-3">
            <div class="card card-stats card-round">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-icon">
                            <div class="icon-big text-center icon-info bubble-shadow-small">
                                <i class="fas fa-users"></i>
                            </div>
                        </div>
                        <div class="col col-stats ms-3 ms-sm-0">
                            <div class="numbers">
                                <p class="card-category">Tổng Khách Thuê</p>
                                <h4 class="card-title">{{ $stats['total_tenants'] }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="stats">
                        <i class="fas fa-building me-1"></i>
                        {{ $stats['total_houses'] }} khu trọ đang quản lý
                    </div>
                </div>
            </div>
        </div>

        {{-- Sự cố --}}
        <div class="col-sm-6 col-md-3">
            <div class="card card-stats card-round">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-icon">
                            <div class="icon-big text-center icon-danger bubble-shadow-small">
                                <i class="fas fa-tools"></i>
                            </div>
                        </div>
                        <div class="col col-stats ms-3 ms-sm-0">
                            <div class="numbers">
                                <p class="card-category">Sự Cố Chờ Xử Lý</p>
                                <h4 class="card-title">{{ $stats['pending_tickets'] }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="stats">
                        @if($stats['pending_tickets'] > 0)
                            <i class="fas fa-exclamation-triangle text-danger me-1"></i>
                            <span class="text-danger">Cần xử lý sớm</span>
                        @else
                            <i class="fas fa-check-circle text-success me-1"></i>
                            Mọi thứ ổn định
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ══ Tables Row ══ --}}
    <div class="row">
        {{-- Hóa đơn gần đây --}}
        <div class="col-md-12 col-lg-6">
            <div class="card card-round">
                <div class="card-header">
                    <div class="card-head-row">
                        <div class="card-title">Hóa đơn gần đây</div>
                        <div class="card-tools">
                            <a href="{{ route('admin.invoices.index') }}" class="btn btn-label-success btn-round btn-sm">
                                Xem tất cả
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-items-center mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th scope="col">Khách / Phòng</th>
                                    <th scope="col" class="text-end">Số tiền</th>
                                    <th scope="col" class="text-center">Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recent_invoices as $invoice)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $invoice->contract->tenant->user->name ?? 'N/A' }}</div>
                                        <small class="text-muted">P.{{ $invoice->contract->room->name ?? 'N/A' }} · Tháng {{ $invoice->month }}/{{ $invoice->year }}</small>
                                    </td>
                                    <td class="text-end fw-bold text-primary">
                                        {{ number_format($invoice->total, 0, ',', '.') }}đ
                                    </td>
                                    <td class="text-center">
                                        @if($invoice->status == 'paid')
                                            <span class="badge badge-success">Đã đóng</span>
                                        @elseif($invoice->status == 'partial')
                                            <span class="badge badge-warning">Một phần</span>
                                        @else
                                            <span class="badge badge-danger">Chưa đóng</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-4">Chưa có hóa đơn nào</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sự cố gần đây --}}
        <div class="col-md-12 col-lg-6">
            <div class="card card-round">
                <div class="card-header">
                    <div class="card-head-row">
                        <div class="card-title">Báo cáo sự cố mới nhất</div>
                        <div class="card-tools">
                            <a href="{{ route('admin.maintenance-tickets.index') }}" class="btn btn-label-danger btn-round btn-sm">
                                Xử lý ngay
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-items-center mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th scope="col">Sự cố</th>
                                    <th scope="col">Phòng</th>
                                    <th scope="col" class="text-center">Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recent_tickets as $ticket)
                                <tr>
                                    <td>
                                        <div class="fw-semibold" style="max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $ticket->title }}</div>
                                        <small class="text-muted">{{ $ticket->created_at->diffForHumans() }}</small>
                                    </td>
                                    <td>P.{{ $ticket->contract->room->name ?? 'N/A' }}</td>
                                    <td class="text-center">
                                        @if($ticket->status == 'pending')
                                            <span class="badge badge-warning">Chờ xử lý</span>
                                        @elseif($ticket->status == 'in_progress')
                                            <span class="badge badge-primary">Đang sửa</span>
                                        @elseif($ticket->status == 'done')
                                            <span class="badge badge-success">Hoàn tất</span>
                                        @else
                                            <span class="badge badge-secondary">Huỷ</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-4">
                                        <i class="fas fa-check-circle text-success me-1"></i>Không có sự cố nào!
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
