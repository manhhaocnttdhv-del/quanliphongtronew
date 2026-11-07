<x-app-layout>
    <x-slot name="header">Biên bản quyết toán #{{ $settlement->id }}</x-slot>
    @include('admin.operations.errors')
    <div class="ops-form">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-3"><div><p class="mb-1">{{ $settlement->contract->tenant->user->name }} / Phòng {{ $settlement->contract->room->name }}</p><p class="text-muted">Bàn giao {{ $settlement->checkout_date->format('d/m/Y') }} / {{ $settlement->creator->name }}</p></div><button type="button" onclick="window.print()" class="btn btn-outline-secondary d-print-none"><i class="fas fa-print" aria-hidden="true"></i> In biên bản</button></div>
        <x-settlement-summary :cash-due="$currentDebt" :refund-due="$settlement->refund_due" cash-label="Công nợ hiện tại" :refund-label="$settlement->refunded_at ? 'Đã hoàn cọc' : 'Cần hoàn cọc'" />
        @include('admin.settlements.breakdown', ['breakdown' => $settlement->breakdown])
        <p>Công nợ hiện tại: <strong>{{ number_format($currentDebt, 0, ',', '.') }} đ</strong> / <a href="{{ route('admin.invoices.show', $settlement->invoice) }}">Hóa đơn quyết toán #{{ $settlement->invoice_id }}</a></p>
        <h2 class="h6 mt-4">Biên bản bàn giao</h2><p class="ops-preline text-break">{{ $settlement->handover_notes }}</p>
        @if($settlement->refund_due > 0)
        <section class="border-top mt-4 pt-4 d-print-none">
            <h2 class="h5">{{ $settlement->refunded_at ? 'Đã hoàn cọc' : 'Chờ hoàn cọc' }}</h2>
            @if($settlement->refunded_at)<p>{{ number_format($settlement->refund_due, 0, ',', '.') }} đ / {{ $settlement->refund_reference }} / {{ $settlement->refunded_at->format('d/m/Y H:i') }}</p><a href="{{ route('admin.settlements.evidence', $settlement) }}" class="btn btn-outline-secondary"><i class="fas fa-download me-1" aria-hidden="true"></i> Bằng chứng hoàn cọc</a>
            @else
            <form method="POST" action="{{ route('admin.settlements.refund', $settlement) }}" enctype="multipart/form-data" data-submit-state>
                @csrf
                <div class="mb-3"><label class="form-label" for="reference">Mã chuyển khoản / Phiếu chi</label><input id="reference" name="reference" value="{{ old('reference') }}" maxlength="150" minlength="3" class="form-control" required></div>
                <div class="mb-3"><label class="form-label" for="evidence">Bằng chứng đã hoàn cọc (PNG, JPG, PDF; tối đa 5 MB)</label><input id="evidence" type="file" name="evidence" class="form-control" accept="image/png,image/jpeg,application/pdf" required></div>
                <label class="form-check mb-3"><input class="form-check-input" type="checkbox" name="confirmed" value="1" required> Tôi xác nhận đã hoàn {{ number_format($settlement->refund_due, 0, ',', '.') }} đ cho khách.</label>
                <button class="btn btn-primary"><i class="fas fa-check me-1" aria-hidden="true"></i> <span data-submit-label>Ghi nhận đã hoàn cọc</span></button>
            </form>
            @endif
        </section>
        @endif
        <a href="{{ route('admin.contracts.show', $settlement->contract) }}" class="btn btn-link mt-3 d-print-none"><i class="fas fa-arrow-left" aria-hidden="true"></i> Hợp đồng</a>
    </div>
</x-app-layout>
