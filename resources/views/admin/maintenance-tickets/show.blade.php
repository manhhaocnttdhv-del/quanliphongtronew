<x-app-layout>
    <x-slot name="header">Chi Tiết Sự Cố #{{ $maintenanceTicket->id }}</x-slot>

    <div class="row">
        <div class="col-lg-8">
            <div class="card card-round">
                <div class="card-header">
                    <div class="card-head-row">
                        <div class="card-title">
                            <i class="fas fa-tools text-primary me-2"></i> {{ $maintenanceTicket->title }}
                        </div>
                        <div class="card-tools">
                            @if($maintenanceTicket->status == 'pending')
                                <span class="badge badge-warning">Đang chờ</span>
                            @elseif($maintenanceTicket->status == 'in_progress')
                                <span class="badge badge-primary">Đang xử lý</span>
                            @elseif($maintenanceTicket->status == 'done')
                                <span class="badge badge-success">Hoàn thành</span>
                            @else
                                <span class="badge badge-danger">Đã hủy</span>
                            @endif

                            @if($maintenanceTicket->priority == 'high')
                                <span class="badge badge-danger"><i class="fas fa-arrow-up"></i> Cao</span>
                            @elseif($maintenanceTicket->priority == 'medium')
                                <span class="badge badge-warning"><i class="fas fa-minus"></i> Trung bình</span>
                            @else
                                <span class="badge badge-info"><i class="fas fa-arrow-down"></i> Thấp</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <p class="text-muted small mb-1">NGƯỜI BÁO CÁO</p>
                            <p class="fw-bold mb-0 text-primary">{{ $maintenanceTicket->contract->tenant->user->name ?? 'N/A' }}</p>
                            <p class="small text-muted mb-0"><i class="fas fa-phone me-1"></i> {{ $maintenanceTicket->contract->tenant->phone ?? 'Không có SĐT' }}</p>
                        </div>
                        <div class="col-md-6 text-md-end mt-3 mt-md-0">
                            <p class="text-muted small mb-1">PHÒNG</p>
                            <p class="fw-bold mb-0 fs-5"><i class="fas fa-home text-secondary me-1"></i> Phòng {{ $maintenanceTicket->contract->room->name ?? 'N/A' }}</p>
                            <p class="small text-muted mb-0">{{ $maintenanceTicket->contract->room->house->name ?? 'N/A' }}</p>
                        </div>
                    </div>

                    <div class="mb-4">
                        <p class="text-muted small mb-2">MÔ TẢ SỰ CỐ</p>
                        <div class="bg-light rounded p-4 border text-dark" style="white-space: pre-line;">
                            {{ $maintenanceTicket->description }}
                        </div>
                    </div>

                    @if($maintenanceTicket->image_path)
                    <div class="mb-4">
                        <p class="text-muted small mb-2">HÌNH ẢNH ĐÍNH KÈM</p>
                        <a href="{{ Storage::url($maintenanceTicket->image_path) }}" target="_blank">
                            <img src="{{ Storage::url($maintenanceTicket->image_path) }}" alt="Hình ảnh sự cố" class="img-fluid rounded border shadow-sm" style="max-height: 400px; object-fit: contain;">
                        </a>
                    </div>
                    @endif

                    <div class="text-muted small mt-4 pt-3 border-top d-flex justify-content-between">
                        <span><i class="far fa-clock me-1"></i> Báo cáo lúc: {{ $maintenanceTicket->created_at->format('H:i d/m/Y') }} ({{ $maintenanceTicket->created_at->diffForHumans() }})</span>
                        <span><i class="fas fa-history me-1"></i> Cập nhật: {{ $maintenanceTicket->updated_at->format('H:i d/m/Y') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card card-round">
                <div class="card-header bg-info-subtle">
                    <div class="card-title text-info"><i class="fas fa-reply me-2"></i>Phản hồi & Xử lý</div>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.maintenance-tickets.update', $maintenanceTicket) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="form-group px-0">
                            <label class="form-label fw-semibold">Trạng thái xử lý <span class="text-danger">*</span></label>
                            <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                <option value="pending" {{ $maintenanceTicket->status == 'pending' ? 'selected' : '' }}>Đang chờ (Mới)</option>
                                <option value="in_progress" {{ $maintenanceTicket->status == 'in_progress' ? 'selected' : '' }}>Đang xử lý</option>
                                <option value="done" {{ $maintenanceTicket->status == 'done' ? 'selected' : '' }}>Hoàn thành</option>
                                <option value="cancelled" {{ $maintenanceTicket->status == 'cancelled' ? 'selected' : '' }}>Đã hủy</option>
                            </select>
                            @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="form-group px-0 mt-3">
                            <label class="form-label fw-semibold">Ghi chú / Phản hồi cho Khách thuê</label>
                            <textarea name="admin_response" class="form-control @error('admin_response') is-invalid @enderror" rows="4" placeholder="VD: Thợ sẽ qua sửa vào chiều nay lúc 15h...">{{ old('admin_response', $maintenanceTicket->admin_response) }}</textarea>
                            @error('admin_response')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <small class="form-text text-muted">Khách thuê sẽ nhìn thấy phản hồi này của bạn.</small>
                        </div>

                        <div class="mt-4 text-center">
                            <button type="submit" class="btn btn-primary btn-round w-100">
                                <i class="fas fa-save me-1"></i> Cập nhật trạng thái
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="text-center mt-3">
                 <a href="{{ route('admin.maintenance-tickets.index') }}" class="btn btn-black btn-border btn-round w-100">
                    <i class="fas fa-arrow-left me-1"></i> Quay lại danh sách
                 </a>
            </div>
        </div>
    </div>
</x-app-layout>
