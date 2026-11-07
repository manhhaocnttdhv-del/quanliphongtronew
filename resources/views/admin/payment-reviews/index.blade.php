<x-app-layout>
    <x-slot name="header">Đối soát & hoàn tiền</x-slot>
    @include('admin.operations.errors')
    <ul class="nav nav-tabs mb-4"><li class="nav-item"><a class="nav-link {{ ! $resolved ? 'active' : '' }}" href="{{ route('admin.payment-reviews.index') }}">Cần đối soát</a></li><li class="nav-item"><a class="nav-link {{ $resolved ? 'active' : '' }}" href="{{ route('admin.payment-reviews.index', ['status' => 'resolved']) }}">Đã xử lý</a></li></ul>
    <div class="table-responsive"><table class="table align-middle"><thead><tr><th>Khoản thu</th><th>Khách / Phòng</th><th class="text-end">Số tiền</th><th>Trạng thái</th><th></th></tr></thead><tbody>
    @forelse($payments as $payment)<tr><td>#{{ $payment->id }}<br><small>{{ $payment->paid_at->format('d/m/Y H:i') }} / {{ $payment->reference_code }}</small></td><td>{{ $payment->invoice->contract->tenant->user->name }}<br><small>{{ $payment->invoice->contract->room->house->name }} / {{ $payment->invoice->contract->room->name }}</small></td><td class="text-end text-nowrap">{{ number_format($payment->amount, 0, ',', '.') }} đ</td><td>{{ $payment->resolution ? ($payment->resolution->outcome === 'refund' ? 'Đã hoàn tiền' : 'Đã đối soát') : 'Cần xử lý' }}</td><td><a class="btn btn-sm btn-outline-primary" href="{{ route('admin.payment-reviews.show', $payment) }}" title="Xem đối soát" aria-label="Xem đối soát khoản thu {{ $payment->id }}"><i class="fas fa-eye" aria-hidden="true"></i></a></td></tr>
    @empty<tr><td colspan="5" class="text-center text-muted py-5">Không có khoản thu trong danh sách này.</td></tr>@endforelse
    </tbody></table></div>
    {{ $payments->links() }}
</x-app-layout>
