@props(['cashDue', 'refundDue', 'cashLabel' => 'Cần thu thêm', 'refundLabel' => 'Cần hoàn cọc'])
<section class="ops-outcome" aria-label="Kết quả quyết toán">
    <div>
        <span class="ops-outcome-label"><i class="fas fa-arrow-down" aria-hidden="true"></i> {{ $cashLabel }}</span>
        <strong class="{{ (int) $cashDue > 0 ? 'text-danger' : '' }}" data-cash-due>{{ number_format($cashDue, 0, ',', '.') }} <small>đ</small></strong>
    </div>
    <div>
        <span class="ops-outcome-label"><i class="fas fa-arrow-up" aria-hidden="true"></i> {{ $refundLabel }}</span>
        <strong class="{{ (int) $refundDue > 0 ? 'text-success' : '' }}" data-refund-due>{{ number_format($refundDue, 0, ',', '.') }} <small>đ</small></strong>
    </div>
</section>
