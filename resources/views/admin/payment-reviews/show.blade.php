<x-app-layout>
    <x-slot name="header">Đối soát khoản thu #{{ $payment->id }}</x-slot>
    @include('admin.operations.errors')
    <div class="ops-form">
        <dl class="row border-bottom pb-3 mb-4"><dt class="col-sm-4">Khách thuê / Phòng</dt><dd class="col-sm-8">{{ $payment->invoice->contract->tenant->user->name }} / {{ $payment->invoice->contract->room->name }}</dd><dt class="col-sm-4">Đã thu</dt><dd class="col-sm-8">{{ number_format($payment->amount, 0, ',', '.') }} đ / {{ $payment->reference_code }}</dd><dt class="col-sm-4">Có thể hoàn</dt><dd class="col-sm-8">{{ number_format($refundable, 0, ',', '.') }} đ</dd><dt class="col-sm-4">Hóa đơn</dt><dd class="col-sm-8"><a href="{{ route('admin.invoices.show', $payment->invoice) }}">#{{ $payment->invoice_id }}</a></dd></dl>
        @if($payment->resolution)
        <h2 class="h5">{{ $payment->resolution->outcome === 'refund' ? 'Đã ghi nhận hoàn tiền' : 'Đã đối soát giữ lại khoản thu' }}</h2>
        <p>{{ $payment->resolution->reason }}</p>
        <p>{{ $payment->resolution->resolver->name }} / {{ $payment->resolution->resolved_at->format('d/m/Y H:i') }}</p>
        @if($payment->resolution->refund_amount > 0)<p>Đã hoàn: <strong>{{ number_format($payment->resolution->refund_amount, 0, ',', '.') }} đ</strong> / {{ $payment->resolution->reference }}</p>@endif
        @if($payment->resolution->evidence_path)<a class="btn btn-outline-secondary" href="{{ route('admin.payment-reviews.evidence', $payment) }}"><i class="fas fa-download me-1" aria-hidden="true"></i> Bằng chứng</a>@endif
        @else
        <form method="POST" action="{{ route('admin.payment-reviews.store', $payment) }}" enctype="multipart/form-data">
            @csrf
            <fieldset class="mb-3"><legend class="h6">Kết quả xử lý</legend><div class="d-flex gap-4 flex-wrap"><label class="form-check"><input class="form-check-input" type="radio" name="outcome" value="retain" @checked(old('outcome', 'retain') === 'retain')> Ghi nhận giữ lại khoản thu</label><label class="form-check"><input class="form-check-input" type="radio" name="outcome" value="refund" @checked(old('outcome') === 'refund') @disabled($refundable <= 0)> Đã hoàn tiền cho khách</label></div></fieldset>
            <fieldset id="refund-fields">
            <div class="row g-3 mb-3"><div class="col-md-6"><label class="form-label" for="refund_amount">Số tiền đã hoàn (đ)</label><input class="form-control" id="refund_amount" type="number" min="1" max="{{ max(1, $refundable) }}" name="refund_amount" value="{{ old('refund_amount', $refundable ?: '') }}"></div><div class="col-md-6"><label class="form-label" for="reference">Mã chuyển khoản / Phiếu chi</label><input id="reference" class="form-control" name="reference" maxlength="150" value="{{ old('reference') }}"></div></div>
            <div class="mb-3"><label class="form-label" for="evidence">Bằng chứng hoàn tiền (PNG, JPG, PDF; tối đa 5 MB)</label><input id="evidence" type="file" name="evidence" accept="image/png,image/jpeg,application/pdf" class="form-control"></div>
            </fieldset>
            <div class="mb-3"><label class="form-label" for="reason">Lý do và nội dung đối soát</label><textarea id="reason" name="reason" class="form-control" rows="4" minlength="10" maxlength="3000" required>{{ old('reason') }}</textarea></div>
            <button class="btn btn-primary"><i class="fas fa-check me-1" aria-hidden="true"></i> Lưu kết quả đối soát</button>
        </form>
        @endif
        <a href="{{ route('admin.payment-reviews.index') }}" class="btn btn-link mt-3"><i class="fas fa-arrow-left me-1" aria-hidden="true"></i> Danh sách đối soát</a>
    </div>
    <x-slot name="scripts"><script>
        (() => {
            const fields = document.getElementById('refund-fields');
            if (!fields) return;
            function updateRefundFields() {
                const refund = document.querySelector('[name="outcome"]:checked').value === 'refund';
                fields.hidden = !refund;
                fields.disabled = !refund;
                fields.querySelectorAll('input').forEach(input => input.required = refund);
            }
            document.querySelectorAll('[name="outcome"]').forEach(input => input.addEventListener('change', updateRefundFields));
            updateRefundFields();
        })();
    </script></x-slot>
</x-app-layout>
