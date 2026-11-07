<x-app-layout>
    <x-slot name="header">Lập hóa đơn tháng</x-slot>
    <x-operations-steps :steps="['Nhập chỉ số', 'Kiểm tra hóa đơn']" />
    @include('admin.operations.errors')
    <form method="GET" class="ops-filterbar mb-4">
        <div><label for="period" class="form-label">Kỳ hóa đơn</label><input id="period" type="month" name="period" value="{{ $period->format('Y-m') }}" class="form-control" required></div>
        <div><label for="house_id" class="form-label">Chi nhánh</label><select id="house_id" name="house_id" class="form-select"><option value="">Tất cả</option>@foreach($houses as $house)<option value="{{ $house->id }}" @selected($houseId == $house->id)>{{ $house->name }}</option>@endforeach</select></div>
        <button class="btn btn-outline-primary"><i class="fas fa-filter me-1" aria-hidden="true"></i> Lọc</button>
    </form>
    @php
        $selectedCount = 0;
        $estimatedTotal = 0;
        $availableCount = $rows->where('exists', false)->count();
    @endphp
    <form method="POST" action="{{ route('admin.billing.preview') }}" data-bulk-billing data-submit-state>
        @csrf
        <input type="hidden" name="period" value="{{ $period->format('Y-m') }}">
        <input type="hidden" name="house_id" value="{{ $houseId }}">
        <div class="billing-toolbar">
            <div><label for="due_date" class="form-label">Hạn thanh toán</label><input id="due_date" type="date" name="due_date" min="{{ $period->toDateString() }}" value="{{ old('due_date', $dueDate) }}" class="form-control" required></div>
            <label class="ops-check"><input type="checkbox" id="select-all" @checked($availableCount > 0) @disabled($availableCount === 0)><span>Chọn tất cả <span class="text-muted">({{ $availableCount }} phòng)</span></span></label>
            <button class="btn btn-primary billing-top-preview" data-billing-preview @disabled($availableCount === 0)><i class="fas fa-eye me-1" aria-hidden="true"></i> <span data-submit-label>Xem trước hóa đơn</span></button>
        </div>
        <div class="billing-column-head" aria-hidden="true"><span>Phòng / Khách thuê</span><span>Chỉ số điện</span><span>Chỉ số nước</span><span>Tiền phòng & dịch vụ</span><span class="text-end">Tạm tính</span></div>
        <div class="billing-room-list">
            @forelse($rows as $i => $row)
            @php
                $contract = $row['contract'];
                $selected = old('rows') ? old('rows.'.$i.'.selected') : (! $draft || ! empty($draftRows->get($contract->id)['selected']));
                $readings = [];
                foreach (['electricity', 'water'] as $type) {
                    foreach (['old', 'new'] as $suffix) {
                        $readings[$type.'_'.$suffix] = old('rows.'.$i.'.'.$type.'_'.$suffix, $draftRows->get($contract->id)[$type.'_'.$suffix] ?? $row[$type.'_old']);
                    }
                }
                $estimate = (int) $contract->monthly_price + $row['rates']['service_fee']
                    + (int) round(max(0, (float) $readings['electricity_new'] - (float) $readings['electricity_old']) * $row['rates']['electricity_price'])
                    + (int) round(max(0, (float) $readings['water_new'] - (float) $readings['water_old']) * $row['rates']['water_price']);
                if (! $row['exists'] && $selected) { $selectedCount++; $estimatedTotal += $estimate; }
            @endphp
            <article class="billing-room {{ $row['exists'] ? 'is-billed' : '' }}" aria-labelledby="room-{{ $contract->id }}" data-billing-room data-base="{{ (int) $contract->monthly_price + $row['rates']['service_fee'] }}">
                <div class="billing-room-identity">
                    @if(! $row['exists'])
                    <label class="ops-check"><input class="row-selection" type="checkbox" name="rows[{{ $i }}][selected]" value="1" @checked($selected) aria-label="Chọn phòng {{ $contract->room->name }}"><strong id="room-{{ $contract->id }}">Phòng {{ $contract->room->name }}</strong></label>
                    <input type="hidden" name="rows[{{ $i }}][contract_id]" value="{{ $contract->id }}">
                    @else<strong id="room-{{ $contract->id }}">Phòng {{ $contract->room->name }}</strong>@endif
                    <span>{{ $contract->tenant->user->name }}</span>
                    <small class="text-muted">{{ $contract->room->house->name }}</small>
                </div>
                @if(! $row['exists'])
                @foreach(['electricity' => 'Điện', 'water' => 'Nước'] as $type => $label)
                <fieldset class="billing-meter" data-meter-pair data-meter-label="{{ $label }}" data-rate="{{ $row['rates'][$type.'_price'] }}">
                    <legend>{{ $label }} <small>{{ number_format($row['rates'][$type.'_price'], 0, ',', '.') }} đ/{{ $type === 'electricity' ? 'kWh' : 'm³' }}</small></legend>
                    <div class="ops-meter-fields">
                        @foreach(['old' => 'Chỉ số cũ', 'new' => 'Chỉ số mới'] as $suffix => $fieldLabel)
                        <div><label for="{{ $type }}-{{ $suffix }}-{{ $contract->id }}">{{ $fieldLabel }}</label><input id="{{ $type }}-{{ $suffix }}-{{ $contract->id }}" type="number" inputmode="decimal" step="0.01" min="{{ $suffix === 'new' ? $readings[$type.'_old'] : 0 }}" max="9999999" name="rows[{{ $i }}][{{ $type }}_{{ $suffix }}]" value="{{ $readings[$type.'_'.$suffix] }}" class="form-control" data-meter-{{ $suffix }} aria-describedby="{{ $type }}-error-{{ $contract->id }}" required></div>
                        @endforeach
                    </div>
                    <small id="{{ $type }}-error-{{ $contract->id }}" class="ops-field-error" data-meter-error hidden></small>
                </fieldset>
                @endforeach
                <dl class="billing-fees"><div><dt>Tiền phòng</dt><dd>{{ number_format($contract->monthly_price, 0, ',', '.') }} đ</dd></div><div><dt>Dịch vụ</dt><dd>{{ number_format($row['rates']['service_fee'], 0, ',', '.') }} đ</dd></div></dl>
                <div class="billing-estimate"><span>Tạm tính</span><output data-room-total>{{ number_format($estimate, 0, ',', '.') }} đ</output></div>
                @else<div class="billing-billed-status"><i class="fas fa-check-circle text-success" aria-hidden="true"></i> Đã lập hóa đơn kỳ {{ $period->format('m/Y') }}</div>@endif
            </article>
            @empty<div class="ops-empty"><i class="fas fa-door-open" aria-hidden="true"></i><p>Không có hợp đồng đang hoạt động trong kỳ {{ $period->format('m/Y') }}.</p><a href="{{ route('admin.contracts.index') }}" class="btn btn-outline-secondary">Hợp đồng thuê</a></div>@endforelse
        </div>
        <div class="ops-actionbar" data-billing-summary>
            <div class="billing-selection-summary" aria-live="polite" aria-atomic="true"><span data-selected-count>{{ $selectedCount }} phòng đã chọn</span><strong data-billing-total>{{ number_format($estimatedTotal, 0, ',', '.') }} đ</strong></div>
            <button class="btn btn-primary" data-billing-preview @disabled($selectedCount === 0)><i class="fas fa-eye me-1" aria-hidden="true"></i> <span data-submit-label>Xem trước hóa đơn</span></button>
        </div>
    </form>
</x-app-layout>
