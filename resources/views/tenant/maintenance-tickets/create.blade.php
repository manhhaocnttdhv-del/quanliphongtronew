<x-app-layout>
    <x-slot name="header">Gửi Báo Cáo Sự Cố</x-slot>

    <div class="row">
        <div class="col-lg-8 mx-auto">
            {{-- Room Info Banner --}}
            <div class="alert alert-info d-flex align-items-center gap-2 mb-3">
                <i class="fas fa-building text-info"></i>
                <span>Bạn đang báo cáo sự cố tại phòng
                    <strong>{{ $contract->room->name }}</strong> — {{ $contract->room->house->name }}
                </span>
            </div>

            <div class="card card-round">
                <div class="card-header">
                    <div class="card-head-row">
                        <div class="card-title">Chi tiết Sự cố</div>
                        <a href="{{ route('tenant.maintenance-tickets.index') }}" class="btn btn-sm btn-secondary btn-round">
                            <i class="fas fa-arrow-left me-1"></i> Quay lại
                        </a>
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

                    <form action="{{ route('tenant.maintenance-tickets.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="title">Mô tả ngắn sự cố <span class="text-danger">*</span></label>
                                    <input type="text" id="title" name="title" class="form-control @error('title') is-invalid @enderror"
                                           value="{{ old('title') }}" required
                                           placeholder="VD: Bóng đèn phòng vệ sinh bị cháy, Vòi nước bị hỏng..." autofocus>
                                    @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Mức độ cần xử lý <span class="text-danger">*</span></label>
                                    <div class="row g-2 mt-1">
                                        @foreach(['low' => ['label' => '<i class="fas fa-arrow-down me-1"></i> Không gấp', 'desc' => 'Có thể xử lý sau vài ngày', 'color' => 'info'],
                                                  'medium' => ['label' => '<i class="fas fa-minus me-1"></i> Bình thường', 'desc' => 'Cần xử lý trong 1-2 ngày', 'color' => 'warning'],
                                                  'high' => ['label' => '<i class="fas fa-arrow-up me-1"></i> Khẩn cấp', 'desc' => 'Cần xử lý ngay hôm nay', 'color' => 'danger']] as $val => $info)
                                        <div class="col-4">
                                            <input type="radio" class="btn-check" name="priority" id="priority_{{ $val }}"
                                                   value="{{ $val }}" {{ old('priority','medium')==$val ? 'checked':'' }}>
                                            <label class="btn btn-outline-{{ $info['color'] }} w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3"
                                                   for="priority_{{ $val }}">
                                                <span class="fw-semibold">{{ $info['label'] }}</span>
                                                <small class="mt-1 opacity-75">{{ $info['desc'] }}</small>
                                            </label>
                                        </div>
                                        @endforeach
                                    </div>
                                    @error('priority')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="description">Mô tả chi tiết (tuỳ chọn)</label>
                                    <textarea id="description" name="description" rows="4"
                                              class="form-control @error('description') is-invalid @enderror"
                                              placeholder="Mô tả rõ hơn vị trí, tình trạng hư hỏng...">{{ old('description') }}</textarea>
                                    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Đính kèm ảnh chụp (tuỳ chọn)</label>
                                    <div class="mt-2">
                                        <label for="image" class="d-flex flex-column align-items-center justify-content-center gap-2
                                               border-2 border-dashed rounded p-4 cursor-pointer"
                                               style="border: 2px dashed #ddd; cursor: pointer"
                                               id="upload-label">
                                            <i class="fas fa-cloud-upload-alt fa-2x text-muted"></i>
                                            <span class="fw-semibold text-muted">Bấm để chọn ảnh</span>
                                            <small class="text-muted">JPG, PNG, WebP — Tối đa 5MB</small>
                                        </label>
                                        <input id="image" type="file" name="image" class="d-none" accept="image/*"
                                               onchange="document.getElementById('preview-name').textContent = this.files[0]?.name ?? ''">
                                        <p id="preview-name" class="mt-2 text-center text-success small fst-italic"></p>
                                    </div>
                                    @error('image')<div class="text-danger small">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>

                        <div class="card-action d-flex justify-content-end gap-2 mt-3">
                            <a href="{{ route('tenant.maintenance-tickets.index') }}" class="btn btn-secondary btn-round">Hủy</a>
                            <button type="submit" class="btn btn-warning btn-round">
                                <i class="fas fa-paper-plane me-1"></i> Gửi Báo Cáo
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
