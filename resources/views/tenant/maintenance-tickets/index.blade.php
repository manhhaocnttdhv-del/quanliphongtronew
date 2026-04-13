<x-app-layout>
    <x-slot name="header">Báo Cáo Sự Cố</x-slot>

    <div class="card card-round">
        <div class="card-header">
            <div class="card-head-row">
                <div class="card-title">Danh sách Báo cáo</div>
                @if($contract)
                <a href="{{ route('tenant.maintenance-tickets.create') }}" class="btn btn-warning btn-sm btn-round">
                    <i class="fas fa-plus me-1"></i> Gửi Báo Cáo Mới
                </a>
                @endif
            </div>
        </div>

        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mx-3 mt-3">
            <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <div class="card-body">
            @if($tickets->isEmpty())
            <div class="text-center py-5">
                <i class="fas fa-tools fa-3x text-muted mb-3 opacity-50"></i>
                <h6 class="fw-bold">Chưa có báo cáo sự cố nào</h6>
                <p class="text-muted small">Nếu phát hiện hư hỏng trong phòng, hãy gửi báo cáo để quản lý xử lý kịp thời.</p>
                @if($contract)
                <a href="{{ route('tenant.maintenance-tickets.create') }}" class="btn btn-warning btn-round">
                    <i class="fas fa-plus me-1"></i> Gửi Báo Cáo Đầu Tiên
                </a>
                @endif
            </div>
            @else
            <div class="d-flex flex-column gap-3">
                @foreach($tickets as $ticket)
                <div class="border rounded p-3 d-flex align-items-start gap-3">
                    {{-- Priority icon --}}
                    <div class="flex-shrink-0 mt-1">
                        @if($ticket->priority=='high')
                            <span class="badge badge-danger p-2"><i class="fas fa-exclamation-triangle"></i></span>
                        @elseif($ticket->priority=='medium')
                            <span class="badge badge-warning p-2"><i class="fas fa-info-circle"></i></span>
                        @else
                            <span class="badge badge-info p-2"><i class="fas fa-info"></i></span>
                        @endif
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                            <div>
                                <h6 class="fw-bold mb-1">{{ $ticket->title }}</h6>
                                <p class="small text-muted mb-0">
                                    P{{ $ticket->contract->room->name ?? 'N/A' }} &middot;
                                    {{ \Carbon\Carbon::parse($ticket->created_at)->diffForHumans() }}
                                </p>
                            </div>
                            @if($ticket->status=='pending')     <span class="badge badge-secondary">Chờ Xử Lý</span>
                            @elseif($ticket->status=='in_progress') <span class="badge badge-primary">Đang Sửa</span>
                            @elseif($ticket->status=='done')    <span class="badge badge-success">Đã Xong</span>
                            @else                               <span class="badge badge-danger">Đã Huỷ</span>
                            @endif
                        </div>
                        @if($ticket->description)
                        <p class="small text-muted mt-2 mb-0" style="overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical">
                            {{ $ticket->description }}
                        </p>
                        @endif
                        @if($ticket->admin_response)
                        <div class="alert alert-info py-2 px-3 mt-2 mb-0 small">
                            <i class="fas fa-reply me-1"></i><strong>Phản hồi QLC:</strong> {{ $ticket->admin_response }}
                        </div>
                        @endif
                    </div>
                    <a href="{{ route('tenant.maintenance-tickets.show', $ticket) }}" class="btn btn-sm btn-outline-primary btn-round flex-shrink-0">
                        Xem
                    </a>
                </div>
                @endforeach
            </div>

            @if($tickets->hasPages())
            <div class="mt-3">{{ $tickets->links() }}</div>
            @endif
            @endif
        </div>
    </div>
</x-app-layout>
