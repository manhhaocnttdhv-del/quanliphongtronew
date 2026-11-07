<x-app-layout>
    <x-slot name="header">Chi phí vận hành</x-slot>
    @include('admin.operations.errors')
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3"><p class="mb-0">Tổng theo bộ lọc: <strong>{{ number_format($total, 0, ',', '.') }} đ</strong></p><a href="{{ route('admin.expenses.create') }}" class="btn btn-primary"><i class="fas fa-plus me-1" aria-hidden="true"></i> Ghi chi phí</a></div>
    <form method="GET" class="d-flex gap-2 flex-wrap mb-4">
        <input type="month" name="period" value="{{ request('period') }}" class="form-control ops-filter" aria-label="Tháng chi phí">
        <select name="house_id" class="form-select ops-filter" aria-label="Chi nhánh"><option value="">Tất cả chi nhánh</option>@foreach($houses as $house)<option value="{{ $house->id }}" @selected(request('house_id') == $house->id)>{{ $house->name }}</option>@endforeach</select>
        <select name="status" class="form-select ops-filter" aria-label="Trạng thái"><option value="active">Đã ghi sổ</option><option value="voided" @selected(request('status') === 'voided')>Đã hủy</option></select>
        <button class="btn btn-outline-primary" title="Lọc chi phí" aria-label="Lọc chi phí"><i class="fas fa-filter" aria-hidden="true"></i></button>
    </form>
    <div class="table-responsive"><table class="table align-middle"><thead><tr><th>Ngày / Chi nhánh</th><th>Nội dung</th><th class="text-end">Số tiền</th><th>Người ghi</th><th>Chứng từ / Điều chỉnh</th></tr></thead><tbody>
    @forelse($expenses as $expense)<tr><td class="text-nowrap">{{ $expense->spent_on->format('d/m/Y') }}<br><small>{{ $expense->house?->name ?? 'Chi phí chung' }}</small></td><td class="text-break">{{ $expense->description }}<br><small>{{ \App\Models\Expense::CATEGORIES[$expense->category] }} / {{ $expense->reference }}</small></td><td class="text-end text-nowrap">{{ number_format($expense->amount, 0, ',', '.') }} đ</td><td>{{ $expense->creator->name }}</td><td>
        @if($expense->evidence_path)<a href="{{ route('admin.expenses.evidence', $expense) }}" class="btn btn-sm btn-outline-secondary mb-2" title="Tải chứng từ" aria-label="Tải chứng từ khoản chi {{ $expense->id }}"><i class="fas fa-download" aria-hidden="true"></i></a>@endif
        @if($expense->voided_at)<span class="badge bg-secondary">Đã hủy</span><p class="small mb-0 text-break">{{ $expense->void_reason }}</p>@else
        <details><summary class="text-danger">Hủy khoản chi</summary><form method="POST" action="{{ route('admin.expenses.void', $expense) }}" class="mt-2">@csrf<label class="form-label small" for="void-{{ $expense->id }}">Lý do hủy</label><textarea id="void-{{ $expense->id }}" class="form-control form-control-sm mb-2" name="void_reason" minlength="10" maxlength="2000" rows="2" required></textarea><button class="btn btn-sm btn-outline-danger"><i class="fas fa-ban me-1" aria-hidden="true"></i> Xác nhận hủy</button></form></details>
        @endif
    </td></tr>@empty<tr><td colspan="5" class="text-center text-muted py-5">Chưa có khoản chi phù hợp.</td></tr>@endforelse
    </tbody></table></div>
    {{ $expenses->links() }}
</x-app-layout>
