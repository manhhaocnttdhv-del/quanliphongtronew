<x-app-layout>
    <x-slot name="header">Kiểm tra hóa đơn {{ $period->format('m/Y') }}</x-slot>
    <x-operations-steps :steps="['Nhập chỉ số', 'Kiểm tra hóa đơn']" :current="2" />
    @include('admin.operations.errors')
    <div class="ops-review-total"><div><span>{{ count($rows) }} hóa đơn</span><strong>{{ number_format(array_sum(array_column(array_column($rows, 'invoice'), 'total')), 0, ',', '.') }} <small>đ</small></strong></div><div><span>Hạn thanh toán</span><strong class="ops-review-date">{{ \Carbon\Carbon::parse($rows[0]['invoice']['due_date'])->format('d/m/Y') }}</strong></div></div>
    <div class="billing-preview-list">
        @foreach($rows as $row)
        <article class="billing-preview-room">
            <div class="billing-preview-identity"><h2>Phòng {{ $row['contract']->room->name }}</h2><span>{{ $row['contract']->tenant->user->name }}</span><small class="text-muted">{{ $row['contract']->room->house->name }}</small></div>
            <dl>@foreach(['room_fee' => 'Tiền phòng', 'electricity_fee' => 'Tiền điện', 'water_fee' => 'Tiền nước', 'service_fee' => 'Dịch vụ', 'total' => 'Tổng hóa đơn'] as $field => $label)<div class="{{ $field === 'total' ? 'billing-preview-total' : '' }}"><dt>{{ $label }}</dt><dd>{{ number_format($row['invoice'][$field], 0, ',', '.') }} đ</dd></div>@endforeach</dl>
        </article>
        @endforeach
    </div>
    <div class="ops-actionbar">
        <a class="btn btn-outline-secondary" href="{{ route('admin.billing.index', ['period' => $period->format('Y-m'), 'draft' => $token]) }}"><i class="fas fa-arrow-left me-1" aria-hidden="true"></i> Sửa chỉ số</a>
        <form method="POST" action="{{ route('admin.billing.store') }}" data-submit-state>@csrf<input type="hidden" name="token" value="{{ $token }}"><button class="btn btn-success"><i class="fas fa-check me-1" aria-hidden="true"></i> <span data-submit-label>Lập {{ count($rows) }} hóa đơn</span></button></form>
    </div>
</x-app-layout>
