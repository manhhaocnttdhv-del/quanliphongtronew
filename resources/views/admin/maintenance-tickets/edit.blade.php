<x-app-layout>
    <x-slot name="header">Xử Lý Sự Cố #{{ $maintenanceTicket->id }}</x-slot>

    <div class="row">
        <div class="col-lg-10 mx-auto">

            {{-- Chi tiết báo cáo từ khách --}}
            <div class="card card-round mb-3">
                <div class="card-header">
                    <div class="card-head-row">
                        <div class="card-title">
                            {{ $maintenanceTicket->title }}
                            @if($maintenanceTicket->priority=='high')
                                <span class="badge badge-danger ms-2">Gấp</span>
                            @elseif($maintenanceTicket->priority=='medium')
                                <span class="badge badge-warning ms-2">Bình thường</span>
                            @else
                                <span class="badge badge-secondary ms-2">Thấp</span>
                            @endif
                        </div>
                        <a href="{{ route('admin.maintenance-tickets.index') }}" class="btn btn-sm btn-secondary btn-round">
                            <i class="fas fa-arrow-left me-1"></i> Quay lại
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-1"><i class="fas fa-user text-primary me-2"></i>
                                <strong>Người báo:</strong> {{ $maintenanceTicket->contract->tenant->user->name ?? 'N/A' }}
                            </p>
                            <p class="mb-1"><i class="fas fa-door-open text-info me-2"></i>
                                <strong>Phòng:</strong> {{ $maintenanceTicket->contract->room->name ?? 'N/A' }}
                                ({{ $maintenanceTicket->contract->room->house->name ?? '' }})
                            </p>
                            <p class="mb-0"><i class="fas fa-clock text-muted me-2"></i>
                                <strong>Báo lúc:</strong> {{ $maintenanceTicket->created_at->format('H:i d/m/Y') }}
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted fw-bold text-uppercase small">Mô tả sự cố:</h6>
                            <p class="mb-0" style="white-space:pre-line">{{ $maintenanceTicket->description ?: 'Không có mô tả chi tiết.' }}</p>
                        </div>
                    </div>

                    @if($maintenanceTicket->image_path)
                    <div class="mt-3">
                        <h6 class="text-muted fw-bold text-uppercase small">Hình ảnh đính kèm:</h6>
                        <img src="{{ Storage::url($maintenanceTicket->image_path) }}" alt="Hình ảnh sự cố"
                             class="img-fluid rounded" style="max-width:350px;cursor:pointer">
                    </div>
                    @endif
                </div>
            </div>

            {{-- Form xử lý --}}
            <div class="card card-round">
                <div class="card-header">
                    <div class="card-title text-primary">
                        <i class="fas fa-tools me-1"></i> Admin Xử Lý
                    </div>
                </div>
                <div class="card-body">
                    @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show">
                        <strong><i class="fas fa-exclamation-triangle me-1"></i>Có lỗi:</strong>
                        <ul class="mb-0 mt-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    @endif

                    <form action="{{ route('admin.maintenance-tickets.update', $maintenanceTicket) }}" method="POST">
                        @csrf @method('PUT')
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="status">Cập nhật trạng thái <span class="text-danger">*</span></label>
                                    <select id="status" name="status" class="form-select @error('status') is-invalid @enderror" required>
                                        <option value="pending"     {{ old('status',$maintenanceTicket->status)=='pending'     ?'selected':'' }}>Chờ xử lý</option>
                                        <option value="in_progress" {{ old('status',$maintenanceTicket->status)=='in_progress' ?'selected':'' }}>Đang gọi thợ / Đang sửa chữa</option>
                                        <option value="done"        {{ old('status',$maintenanceTicket->status)=='done'        ?'selected':'' }}>Đã sửa xong</option>
                                        <option value="cancelled"   {{ old('status',$maintenanceTicket->status)=='cancelled'   ?'selected':'' }}>Huỷ bỏ (Không hợp lệ)</option>
                                    </select>
                                    @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="admin_response">Phản hồi cho người thuê</label>
                                    <textarea id="admin_response" name="admin_response" rows="4"
                                              class="form-control @error('admin_response') is-invalid @enderror"
                                              placeholder="VD: Chủ nhà đã gọi thợ điện, dự kiến chiều nay sẽ vào thay.">{{ old('admin_response', $maintenanceTicket->admin_response) }}</textarea>
                                    @error('admin_response')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>

                        <div class="card-action d-flex justify-content-between align-items-center mt-3">
                            <div>
                                @if($maintenanceTicket->status=='done' && $maintenanceTicket->resolved_at)
                                    <span class="text-success fw-semibold">
                                        <i class="fas fa-check-circle me-1"></i>
                                        Hoàn thành lúc {{ \Carbon\Carbon::parse($maintenanceTicket->resolved_at)->format('H:i d/m/Y') }}
                                    </span>
                                @endif
                            </div>
                            <div class="d-flex gap-2">
                                <a href="{{ route('admin.maintenance-tickets.index') }}" class="btn btn-secondary btn-round">Quay lại</a>
                                <button type="submit" class="btn btn-primary btn-round">
                                    <i class="fas fa-save me-1"></i> Lưu Cập Nhật
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
