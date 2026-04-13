<x-app-layout>
    <x-slot name="header">Soạn Thông Báo / Bản Tin</x-slot>

    <div class="row">
        <div class="col-lg-10 mx-auto">
            <div class="card card-round">
                <div class="card-header">
                    <div class="card-head-row">
                        <div class="card-title">Nội dung Thông báo</div>
                        <a href="{{ route('admin.announcements.index') }}" class="btn btn-sm btn-secondary btn-round">
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

                    <form action="{{ route('admin.announcements.store') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="title">Tiêu đề thông báo <span class="text-danger">*</span></label>
                                    <input type="text" id="title" name="title" class="form-control form-control-lg @error('title') is-invalid @enderror"
                                           value="{{ old('title') }}" required placeholder="VD: Thông báo nghỉ lễ 30/4 - 1/5" autofocus>
                                    @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="type">Phân loại <span class="text-danger">*</span></label>
                                    <select id="type" name="type" class="form-select @error('type') is-invalid @enderror" required>
                                        <option value="notice"  {{ old('type')=='notice'  ?'selected':'' }}>📢 Thông báo chung</option>
                                        <option value="warning" {{ old('type')=='warning' ?'selected':'' }}>⚠️ Cảnh báo</option>
                                        <option value="event"   {{ old('type')=='event'   ?'selected':'' }}>🎉 Sự kiện</option>
                                    </select>
                                    @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="house_id">Gửi đến</label>
                                    <select id="house_id" name="house_id" class="form-select @error('house_id') is-invalid @enderror">
                                        <option value="">-- Tất cả các khu trọ --</option>
                                        @foreach($houses as $house)
                                            <option value="{{ $house->id }}" {{ old('house_id')==$house->id?'selected':'' }}>
                                                Chỉ khu: {{ $house->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('house_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="content">Nội dung chi tiết <span class="text-danger">*</span></label>
                                    <textarea id="content" name="content" rows="6" class="form-control @error('content') is-invalid @enderror"
                                              required placeholder="Nhập nội dung thông báo...">{{ old('content') }}</textarea>
                                    @error('content')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="d-flex gap-4 p-3 bg-light rounded border">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="is_pinned" name="is_pinned" value="1"
                                               {{ old('is_pinned') ? 'checked' : '' }}>
                                        <label class="form-check-label fw-semibold" for="is_pinned">
                                            <i class="fas fa-thumbtack text-warning me-1"></i> Ghim lên đầu trang
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="publish_now" name="publish_now" value="1" checked>
                                        <label class="form-check-label fw-semibold text-success" for="publish_now">
                                            <i class="fas fa-paper-plane me-1"></i> Đăng ngay (Bỏ tick để lưu nháp)
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-action d-flex justify-content-end gap-2 mt-3">
                            <a href="{{ route('admin.announcements.index') }}" class="btn btn-secondary btn-round">Hủy</a>
                            <button type="submit" class="btn btn-primary btn-round">
                                <i class="fas fa-bell me-1"></i> Lưu Thông Báo
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
