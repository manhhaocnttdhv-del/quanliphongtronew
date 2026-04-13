<x-app-layout>
    <x-slot name="header">Hợp Đồng Của Tôi</x-slot>

    @if($contracts->isEmpty())
    <div class="card card-round text-center py-5">
        <div class="card-body">
            <i class="fas fa-file-contract fa-3x text-muted mb-3 opacity-50"></i>
            <h5 class="fw-bold">Chưa có hợp đồng nào</h5>
            <p class="text-muted">Hãy liên hệ quản lý nếu có sai sót.</p>
        </div>
    </div>
    @else
    <div class="d-flex flex-column gap-3">
        @foreach($contracts as $contract)
        <div class="card card-round mb-0">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="avatar-sm">
                            <span class="avatar-img rounded-circle d-flex align-items-center justify-content-center
                                {{ $contract->status=='active' ? 'bg-success' : 'bg-secondary' }}">
                                <i class="fas fa-file-contract text-white small"></i>
                            </span>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-1">
                                Phòng {{ $contract->room->name }} — {{ $contract->room->house->name }}
                                @if($contract->status=='active')
                                    <span class="badge badge-success ms-1">Đang hiệu lực</span>
                                @elseif($contract->status=='expired')
                                    <span class="badge badge-secondary ms-1">Hết hạn</span>
                                @else
                                    <span class="badge badge-danger ms-1">Đã thanh lý</span>
                                @endif
                            </h6>
                            <p class="small text-muted mb-0">
                                {{ \Carbon\Carbon::parse($contract->start_date)->format('d/m/Y') }}
                                → {{ \Carbon\Carbon::parse($contract->end_date)->format('d/m/Y') }}
                                &middot;
                                <span class="text-primary fw-semibold">{{ number_format($contract->monthly_price,0,',','.')}}đ/tháng</span>
                            </p>
                        </div>
                    </div>
                    <a href="{{ route('tenant.contracts.show', $contract) }}" class="btn btn-sm btn-primary btn-round">
                        <i class="fas fa-eye me-1"></i> Xem Chi Tiết
                    </a>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</x-app-layout>
