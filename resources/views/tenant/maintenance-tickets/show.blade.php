<x-app-layout>
    <x-slot name="header">Chi Tiết Sự Cố</x-slot>

    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card card-round">
                <div class="card-header">
                    <div class="card-head-row">
                        <div class="card-title">
                            {{ $maintenanceTicket->title }}
                            @if($maintenanceTicket->priority=='high')     <span class="badge badge-danger ms-1"><i class="fas fa-arrow-up me-1"></i> Khẩn cấp</span>
                            @elseif($maintenanceTicket->priority=='medium') <span class="badge badge-warning ms-1"><i class="fas fa-minus me-1"></i> Bình thường</span>
                            @else                                          <span class="badge badge-info ms-1"><i class="fas fa-arrow-down me-1"></i> Không gấp</span>
                            @endif
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            @if($maintenanceTicket->status=='pending')     <span class="badge badge-secondary">Chờ xử lý</span>
                            @elseif($maintenanceTicket->status=='in_progress') <span class="badge badge-primary">Đang sửa</span>
                            @elseif($maintenanceTicket->status=='done')    <span class="badge badge-success">Đã xong</span>
                            @else                                          <span class="badge badge-danger">Đã huỷ</span>
                            @endif
                            <a href="{{ route('tenant.maintenance-tickets.index') }}" class="btn btn-sm btn-secondary btn-round">
                                <i class="fas fa-arrow-left me-1"></i> Quay lại
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">
                        <i class="fas fa-clock me-1"></i> Gửi lúc {{ $maintenanceTicket->created_at->format('H:i d/m/Y') }}
                        &middot; Phòng <strong>{{ $maintenanceTicket->contract->room->name }}</strong>
                        — {{ $maintenanceTicket->contract->room->house->name }}
                    </p>

                    @if($maintenanceTicket->image_path)
                    <div class="mb-4">
                        <label class="fw-bold small text-muted text-uppercase">Ảnh chụp sự cố</label>
                        <div class="mt-2">
                            <img src="{{ Storage::url($maintenanceTicket->image_path) }}" alt="Ảnh sự cố"
                                 class="img-fluid rounded" style="max-width:400px" onerror="this.style.display='none'">
                        </div>
                    </div>
                    @endif

                    @if($maintenanceTicket->description)
                    <div class="mb-4">
                        <label class="fw-bold small text-muted text-uppercase">Mô tả từ bạn</label>
                        <div class="bg-light rounded p-3 mt-2 small" style="white-space:pre-line">{{ $maintenanceTicket->description }}</div>
                    </div>
                    @endif

                    @if($maintenanceTicket->admin_response)
                    <div class="alert alert-info">
                        <strong><i class="fas fa-reply me-1"></i> Phản Hồi Từ Quản Lý:</strong>
                        <p class="mt-1 mb-0" style="white-space:pre-line">{{ $maintenanceTicket->admin_response }}</p>
                        @if($maintenanceTicket->resolved_at)
                        <p class="small mb-0 mt-2 text-success">
                            <i class="fas fa-check-circle me-1"></i>
                            Hoàn thành lúc {{ \Carbon\Carbon::parse($maintenanceTicket->resolved_at)->format('H:i d/m/Y') }}
                        </p>
                        @endif
                    </div>
                    @elseif($maintenanceTicket->status=='pending')
                    <div class="alert alert-warning">
                        <i class="fas fa-clock me-1"></i>
                        Quản lý đang xem xét yêu cầu của bạn. Vui lòng chờ phản hồi sớm nhất!
                    </div>
                    @endif

                    @if($maintenanceTicket->status=='pending')
                    <div class="card-action d-flex justify-content-end mt-3">
                        <form action="{{ route('tenant.maintenance-tickets.update', $maintenanceTicket) }}" method="POST"
                              onsubmit="return confirm('Xác nhận huỷ báo cáo này?')">
                            @csrf @method('PUT')
                            <button type="submit" class="btn btn-outline-danger btn-sm btn-round">
                                <i class="fas fa-times me-1"></i> Huỷ Báo Cáo Này
                            </button>
                        </form>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
