<x-app-layout>
    <x-slot name="header">Xem trước quyết toán #{{ $contract->id }}</x-slot>
    <x-operations-steps :steps="['Thông tin bàn giao', 'Kiểm tra quyết toán']" :current="2" />
    @include('admin.operations.errors')
    <div class="ops-form">
        <p>{{ $contract->tenant->user->name }} / Phòng {{ $contract->room->name }} / Ngày {{ \Carbon\Carbon::parse($data['checkout_date'])->format('d/m/Y') }}</p>
        <x-settlement-summary :cash-due="$breakdown['cash_due']" :refund-due="$breakdown['refund_due']" />
        @include('admin.settlements.breakdown')
        <h2 class="h6 mt-4">Nội dung bàn giao</h2><p class="text-break ops-preline">{{ $data['handover_notes'] }}</p>
        <div class="ops-actionbar"><a class="btn btn-outline-secondary" href="{{ route('admin.settlements.create', ['contract' => $contract, 'draft' => $token]) }}"><i class="fas fa-arrow-left" aria-hidden="true"></i> Sửa quyết toán</a><form method="POST" action="{{ route('admin.settlements.store', $contract) }}" data-submit-state>@csrf<input type="hidden" name="token" value="{{ $token }}"><button class="btn btn-success"><i class="fas fa-check me-1" aria-hidden="true"></i> <span data-submit-label>Chốt & bàn giao phòng</span></button></form></div>
    </div>
</x-app-layout>
