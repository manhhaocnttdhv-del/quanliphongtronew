<x-app-layout>
    <x-slot name="header">Quyết toán trả phòng {{ $contract->room->name }}</x-slot>
    <x-operations-steps :steps="['Thông tin bàn giao', 'Kiểm tra quyết toán']" />
    @include('admin.operations.errors')
    @php
        $checkoutDate = old('checkout_date', $draftInput['checkout_date'] ?? today()->toDateString());
        $depositValue = old('deposit_held', $draftInput['deposit_held'] ?? $deposit);
        $hasFees = old('damage_fee', $draftInput['damage_fee'] ?? 0) > 0 || old('other_fee', $draftInput['other_fee'] ?? 0) > 0 || $errors->hasAny(['damage_fee', 'other_fee']);
    @endphp
    <div class="ops-form">
        <p class="ops-context"><strong>{{ $contract->tenant->user->name }}</strong><span>{{ $contract->room->house->name }} / Hợp đồng #{{ $contract->id }}</span></p>
        <form method="POST" action="{{ route('admin.settlements.preview', $contract) }}" data-submit-state>
            @csrf
            <fieldset class="ops-form-section">
                <legend>Bàn giao & tiền cọc</legend>
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label" for="checkout_date">Ngày bàn giao</label><input id="checkout_date" type="date" name="checkout_date" value="{{ $checkoutDate }}" min="{{ $contract->start_date->toDateString() }}" max="{{ today()->toDateString() }}" class="form-control" required></div>
                    <div class="col-md-6"><label class="form-label" for="deposit_held">Cọc thực tế đang giữ (đ)</label><input id="deposit_held" type="number" inputmode="numeric" min="0" max="{{ $deposit }}" name="deposit_held" value="{{ $depositValue }}" class="form-control" required></div>
                </div>
            </fieldset>
            <fieldset class="ops-form-section">
                <legend>Chốt điện nước</legend>
                <div class="ops-meter-grid">
                    @foreach(['electricity' => 'Điện', 'water' => 'Nước'] as $type => $label)
                    @php
                        $oldReading = old($type.'_old', $draftInput[$type.'_old'] ?? $latest?->{$type.'_new'} ?? 0);
                    @endphp
                    <fieldset class="billing-meter" data-meter-pair data-meter-label="{{ $label }}">
                        <legend>{{ $label }} <small>{{ $type === 'electricity' ? 'kWh' : 'm³' }}</small></legend>
                        <div class="ops-meter-fields">
                            <div><label for="{{ $type }}_old">Chỉ số cũ</label><input id="{{ $type }}_old" type="number" inputmode="decimal" min="0" max="9999999" step="0.01" name="{{ $type }}_old" value="{{ $oldReading }}" class="form-control" @readonly($latest !== null) data-meter-old required></div>
                            <div><label for="{{ $type }}_new">Chỉ số chốt</label><input id="{{ $type }}_new" type="number" inputmode="decimal" min="{{ $oldReading }}" max="9999999" step="0.01" name="{{ $type }}_new" value="{{ old($type.'_new', $draftInput[$type.'_new'] ?? $latest?->{$type.'_new'} ?? 0) }}" class="form-control" aria-describedby="{{ $type }}-error" data-meter-new required></div>
                        </div>
                        <small id="{{ $type }}-error" class="ops-field-error" data-meter-error hidden></small>
                    </fieldset>
                    @endforeach
                </div>
            </fieldset>
            <details class="ops-fee-section" @if($hasFees) open @endif>
                <summary><span>Phí phát sinh</span><span class="text-muted">Hư hỏng / Phụ phí <i class="fas fa-chevron-down ms-1" aria-hidden="true"></i></span></summary>
                <div class="row g-3 pt-3">
                    <div class="col-md-6"><label class="form-label" for="damage_fee">Bồi thường hư hỏng (đ)</label><input id="damage_fee" type="number" inputmode="numeric" min="0" max="99999999999" name="damage_fee" value="{{ old('damage_fee', $draftInput['damage_fee'] ?? 0) }}" class="form-control" required></div>
                    <div class="col-md-6"><label class="form-label" for="other_fee">Phụ phí khác (đ)</label><input id="other_fee" type="number" inputmode="numeric" min="0" max="99999999999" name="other_fee" value="{{ old('other_fee', $draftInput['other_fee'] ?? 0) }}" class="form-control" required></div>
                </div>
            </details>
            <div class="ops-form-section"><label class="form-label" for="handover_notes">Biên bản bàn giao / Nội dung phí phát sinh</label><textarea id="handover_notes" name="handover_notes" minlength="10" maxlength="5000" rows="4" class="form-control" required>{{ old('handover_notes', $draftInput['handover_notes'] ?? '') }}</textarea></div>
            <div class="ops-actionbar"><a class="btn btn-outline-secondary" href="{{ route('admin.contracts.show', $contract) }}"><i class="fas fa-arrow-left" aria-hidden="true"></i> Quay lại</a><button class="btn btn-primary"><i class="fas fa-eye me-1" aria-hidden="true"></i> <span data-submit-label>Xem trước quyết toán</span></button></div>
        </form>
    </div>
</x-app-layout>
