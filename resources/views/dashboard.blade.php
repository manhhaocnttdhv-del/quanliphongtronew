<x-app-layout>
    <x-slot name="header">Tổng quan</x-slot>
    <x-slot name="actions"><div class="d-flex align-items-center gap-2"><a href="{{ route('admin.invoices.create') }}" class="btn btn-outline-primary"><i class="fas fa-file-invoice-dollar me-1" aria-hidden="true"></i> Tạo hóa đơn</a><a href="{{ route('admin.rooms.create') }}" class="btn btn-primary"><i class="fas fa-plus me-1" aria-hidden="true"></i> Thêm phòng</a></div></x-slot>

    <div class="admin-subtitle"><p>{{ $stats['total_houses'] }} chi nhánh, {{ $stats['total_rooms'] }} phòng đang quản lý</p><time datetime="{{ now()->toDateString() }}">{{ now()->locale('vi')->translatedFormat('l, d/m/Y') }}</time></div>

    <div class="metric-strip" aria-label="Số liệu tổng quan">
        <div class="metric-item"><div class="metric-label">Đã thu tháng {{ now()->month }}</div><div class="metric-value">{{ number_format($stats['revenue_this_month'], 0, ',', '.') }} <small>đ</small></div><div class="metric-note">Còn phải thu: <strong>{{ number_format($stats['debt_total'], 0, ',', '.') }} đ</strong></div></div>
        <div class="metric-item"><div class="metric-label">Tỷ lệ lấp đầy</div><div class="metric-value">{{ $stats['occupancy_rate'] }}<small>%</small></div><div class="metric-note">{{ $stats['rented_rooms'] }} / {{ $stats['total_rooms'] }} phòng đang thuê</div></div>
        <div class="metric-item"><div class="metric-label">Khách đang quản lý</div><div class="metric-value">{{ $stats['total_tenants'] }} <small>khách</small></div><div class="metric-note">Tại {{ $stats['total_houses'] }} chi nhánh</div></div>
        <div class="metric-item"><div class="metric-label">Sự cố chờ xử lý</div><div class="metric-value">{{ $stats['pending_tickets'] }} <small>sự cố</small></div><div class="metric-note"><a href="{{ route('admin.maintenance-tickets.index', ['status' => 'pending']) }}">Xem báo cáo sự cố</a></div></div>
    </div>

    <div class="dashboard-tasks" aria-label="Việc cần xử lý">
        <a class="task-link" href="{{ route('admin.booking-requests.index', ['status' => 'pending']) }}"><i class="far fa-calendar-check" aria-hidden="true"></i><div><strong>{{ $stats['pending_bookings'] }} yêu cầu chờ duyệt</strong><small>Yêu cầu thuê phòng từ khách</small></div><i class="fas fa-chevron-right" aria-hidden="true"></i></a>
        <a class="task-link" href="{{ route('admin.invoices.index', ['status' => 'overdue']) }}"><i class="far fa-clock" aria-hidden="true"></i><div><strong>{{ $stats['overdue_invoices'] }} hóa đơn quá hạn</strong><small>Hóa đơn còn dư nợ</small></div><i class="fas fa-chevron-right" aria-hidden="true"></i></a>
        <a class="task-link" href="{{ route('admin.rooms.index', ['status' => 'available']) }}"><i class="fas fa-door-open" aria-hidden="true"></i><div><strong>{{ $branches->sum('available_rooms_count') }} phòng còn trống</strong><small>Đang nhận yêu cầu thuê</small></div><i class="fas fa-chevron-right" aria-hidden="true"></i></a>
    </div>

    <div class="dashboard-grid">
        <section class="dashboard-section" aria-labelledby="revenue-heading">
            <div class="section-heading"><h2 id="revenue-heading">Doanh thu năm {{ $stats['current_year'] }}</h2><span>Tiền đã thu, không gồm tiền cọc</span></div>
            <div class="revenue-chart"><canvas id="revenueChart" role="img" aria-label="Biểu đồ doanh thu đã thu theo tháng năm {{ $stats['current_year'] }}"></canvas></div>
        </section>
        <section class="dashboard-section" aria-labelledby="branches-heading">
            <div class="section-heading"><h2 id="branches-heading">Tình hình chi nhánh</h2><a href="{{ route('admin.houses.index') }}">Tất cả chi nhánh</a></div>
            @forelse($branches as $branch)
                @php $occupancy = $branch->rooms_count ? round($branch->rented_rooms_count / $branch->rooms_count * 100) : 0; @endphp
                <div class="branch-overview"><h3>{{ $branch->name }}</h3><p>{{ $branch->address }}</p><div class="occupancy-track" aria-hidden="true"><span style="width: {{ $occupancy }}%"></span></div><a href="{{ route('admin.rooms.index', ['house_id' => $branch->id]) }}"><span>{{ $branch->rented_rooms_count }}/{{ $branch->rooms_count }} phòng đang thuê</span>{{ $branch->available_rooms_count }} phòng trống</a></div>
            @empty<p class="text-muted py-4">Chưa có chi nhánh. <a href="{{ route('admin.houses.create') }}">Thêm chi nhánh</a></p>@endforelse
        </section>
    </div>

    <div class="dashboard-tables">
        <section class="dashboard-section" aria-labelledby="invoices-heading">
            <div class="section-heading"><h2 id="invoices-heading">Hóa đơn gần đây</h2><a href="{{ route('admin.invoices.index') }}">Xem tất cả <i class="fas fa-angle-right ms-1" aria-hidden="true"></i></a></div>
            <div class="table-responsive"><table class="table mb-0"><thead><tr><th scope="col">Khách thuê / Phòng</th><th scope="col" class="text-end">Số tiền</th><th scope="col" class="text-end">Trạng thái</th></tr></thead><tbody>
                @forelse($recent_invoices as $invoice)
                    <tr><td><a href="{{ route('admin.invoices.show', $invoice) }}">{{ $invoice->contract->tenant->user->name ?? 'Chưa có khách thuê' }}</a><br><small class="text-muted">{{ $invoice->contract->room->name ?? 'Chưa có phòng' }} / Tháng {{ $invoice->month }}/{{ $invoice->year }}</small></td><td class="text-end text-nowrap">{{ number_format($invoice->total, 0, ',', '.') }} đ</td><td class="text-end">@if($invoice->status === 'paid')<span class="badge badge-success">Đã thanh toán</span>@elseif($invoice->status === 'partial')<span class="badge badge-warning">Một phần</span>@elseif($invoice->status === 'overdue')<span class="badge badge-danger">Quá hạn</span>@else<span class="badge badge-secondary">Chưa thanh toán</span>@endif</td></tr>
                @empty<tr><td colspan="3" class="text-muted text-center py-4">Chưa có hóa đơn.</td></tr>@endforelse
            </tbody></table></div>
        </section>
        <section class="dashboard-section" aria-labelledby="tickets-heading">
            <div class="section-heading"><h2 id="tickets-heading">Báo cáo sự cố gần đây</h2><a href="{{ route('admin.maintenance-tickets.index') }}">Xem tất cả <i class="fas fa-angle-right ms-1" aria-hidden="true"></i></a></div>
            <div class="table-responsive"><table class="table mb-0"><thead><tr><th scope="col">Nội dung</th><th scope="col">Phòng</th><th scope="col" class="text-end">Trạng thái</th></tr></thead><tbody>
                @forelse($recent_tickets as $ticket)
                    <tr><td><a href="{{ route('admin.maintenance-tickets.show', $ticket) }}">{{ $ticket->title }}</a><br><small class="text-muted">{{ $ticket->created_at->format('d/m/Y') }}</small></td><td>{{ $ticket->contract->room->name ?? 'Chưa có phòng' }}</td><td class="text-end">@if($ticket->status === 'done')<span class="badge badge-success">Đã xử lý</span>@elseif($ticket->status === 'in_progress')<span class="badge badge-info">Đang xử lý</span>@elseif($ticket->status === 'cancelled')<span class="badge badge-secondary">Đã hủy</span>@else<span class="badge badge-warning">Chờ xử lý</span>@endif</td></tr>
                @empty<tr><td colspan="3" class="text-muted text-center py-4">Chưa có báo cáo sự cố.</td></tr>@endforelse
            </tbody></table></div>
        </section>
    </div>

    <x-slot name="scripts">
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.8/dist/chart.umd.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const formatCurrency = value => new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value);
                Chart.defaults.font.family = 'Be Vietnam Pro, sans-serif';
                Chart.defaults.font.size = 10;
                Chart.defaults.color = '#808991';
                new Chart(document.getElementById('revenueChart'), {
                    type: 'bar',
                    data: {
                        labels: Array.from({length: 12}, (_, index) => 'T' + (index + 1)),
                        datasets: [{label: 'Đã thu', data: @json($stats['revenue_chart_data']), backgroundColor: '#7c9b8a', hoverBackgroundColor: '#527a64', borderRadius: 3, maxBarThickness: 24}]
                    },
                    options: {
                        responsive: true, maintainAspectRatio: false,
                        animation: {duration: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 0 : 250},
                        plugins: {legend: {display: false}, tooltip: {callbacks: {label: context => formatCurrency(context.raw)}}},
                        scales: {
                            x: {grid: {display: false}, border: {display: false}},
                            y: {beginAtZero: true, grid: {color: '#e9edf0'}, border: {display: false}, ticks: {maxTicksLimit: 5, callback: value => new Intl.NumberFormat('vi-VN', {notation: 'compact', compactDisplay: 'short'}).format(value)}}
                        }
                    }
                });
            });
        </script>
    </x-slot>
</x-app-layout>
