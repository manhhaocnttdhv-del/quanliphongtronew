<x-app-layout>
    <x-slot name="header">Báo cáo thu chi</x-slot>
    @include('admin.operations.errors')
    <form method="GET" class="d-flex align-items-end gap-3 flex-wrap mb-4">
        <div><label class="form-label" for="period">Tháng</label><input id="period" type="month" name="period" value="{{ $period->format('Y-m') }}" class="form-control" required></div>
        <div><label class="form-label" for="house_id">Chi nhánh</label><select id="house_id" name="house_id" class="form-select"><option value="">Tất cả (gồm chi phí chung)</option>@foreach($houses as $house)<option value="{{ $house->id }}" @selected(request('house_id') == $house->id)>{{ $house->name }}</option>@endforeach</select></div>
        <button class="btn btn-outline-primary"><i class="fas fa-filter me-1" aria-hidden="true"></i> Xem báo cáo</button>
        <a class="btn btn-success" href="{{ route('admin.reports.finance', ['period' => $period->format('Y-m'), 'house_id' => request('house_id'), 'export' => 'xlsx']) }}"><i class="fas fa-file-excel me-1" aria-hidden="true"></i> Xuất Excel</a>
    </form>
    <div class="row g-3 border-top border-bottom py-3 mb-4">
        @foreach(['receipts' => 'Thực thu tiền thuê', 'expenses' => 'Chi phí vận hành', 'deposit_applied' => 'Cọc bù công nợ', 'profit' => 'Lợi nhuận vận hành'] as $key => $label)
        <div class="col-sm-6 col-xl-3"><p class="text-muted small mb-1">{{ $label }}</p><strong class="fs-5 text-break {{ $key === 'profit' ? ($summary[$key] < 0 ? 'text-danger' : 'text-success') : '' }}">{{ number_format($summary[$key], 0, ',', '.') }} đ</strong></div>
        @endforeach
    </div>
    <dl class="row mb-4"><dt class="col-md-3">Cọc nhận / Cọc hoàn</dt><dd class="col-md-9">{{ number_format($summary['deposit_received'], 0, ',', '.') }} đ / {{ number_format($summary['deposit_refund'], 0, ',', '.') }} đ</dd><dt class="col-md-3">Hoàn tiền thuê</dt><dd class="col-md-9">{{ number_format($summary['income_refunds'], 0, ',', '.') }} đ</dd><dt class="col-md-3">Biến động tiền thực tế</dt><dd class="col-md-9">{{ number_format($summary['cash_change'], 0, ',', '.') }} đ</dd></dl>
    <p class="text-muted small">Lợi nhuận vận hành = tiền thuê thực thu + cọc đã bù công nợ − hoàn tiền thuê − chi phí. Cọc nhận và hoàn cọc được theo dõi riêng.</p>
    <h2 class="h5">Giao dịch trong tháng</h2>
    <div class="table-responsive"><table class="table align-middle"><thead><tr><th>Ngày</th><th>Chi nhánh / Phòng</th><th>Nội dung</th><th class="text-end">Tiền vào</th><th class="text-end">Tiền ra</th></tr></thead><tbody>
    @forelse($transactions as $entry)<tr><td class="text-nowrap">{{ \Carbon\Carbon::parse($entry['date'])->format('d/m/Y') }}</td><td>{{ $entry['house'] }}<br><small>{{ $entry['room'] }}</small></td><td class="text-break">{{ \App\Http\Controllers\Admin\FinanceReportController::LABELS[$entry['kind']] }}<br><small>{{ $entry['description'] }}</small></td><td class="text-end text-nowrap">{{ number_format($entry['in'], 0, ',', '.') }} đ</td><td class="text-end text-nowrap">{{ number_format($entry['out'], 0, ',', '.') }} đ</td></tr>@empty<tr><td colspan="5" class="text-center text-muted py-5">Chưa có giao dịch trong tháng này.</td></tr>@endforelse
    </tbody></table></div>
    {{ $transactions->links() }}
</x-app-layout>
