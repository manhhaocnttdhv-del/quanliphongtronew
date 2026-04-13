<x-app-layout>
    <x-slot name="header">Cập Nhật Khu Trọ: {{ $house->name }}</x-slot>

    <div class="row">
        <div class="col-md-10 col-lg-8 mx-auto">
            <div class="card card-round">
                <div class="card-header">
                    <div class="card-head-row">
                        <div class="card-title">Thông tin Khu Trọ</div>
                        <div class="card-tools">
                            <a href="{{ route('admin.houses.index') }}" class="btn btn-sm btn-secondary btn-round">
                                <i class="fas fa-arrow-left me-1"></i> Quay lại
                            </a>
                        </div>
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

                    <form action="{{ route('admin.houses.update', $house) }}" method="POST">
                        @csrf @method('PUT')
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Tên Khu trọ / Tòa nhà <span class="text-danger">*</span></label>
                                    <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror"
                                           value="{{ old('name', $house->name) }}" required autofocus>
                                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="phone">Số điện thoại quản lý</label>
                                    <input type="text" id="phone" name="phone" class="form-control @error('phone') is-invalid @enderror"
                                           value="{{ old('phone', $house->phone) }}">
                                    @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="address">Địa chỉ đầy đủ <span class="text-danger">*</span></label>
                                    <input type="text" id="address" name="address" class="form-control @error('address') is-invalid @enderror"
                                           value="{{ old('address', $house->address) }}" required>
                                    @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="description">Mô tả thêm</label>
                                    <textarea id="description" name="description" rows="4" class="form-control @error('description') is-invalid @enderror">{{ old('description', $house->description) }}</textarea>
                                    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-12">
                                <label class="toggle-wrap">
                                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $house->is_active) ? 'checked' : '' }}>
                                    <span class="toggle-track"></span>
                                    <span class="toggle-text"><strong>Hoạt động</strong> — Cho phép thuê / hiển thị</span>
                                </label>
                            </div>
                        </div>
                        <div class="card-action d-flex justify-content-end gap-2 mt-3">
                            <a href="{{ route('admin.houses.index') }}" class="btn btn-secondary btn-round">Hủy</a>
                            <button type="submit" class="btn btn-primary btn-round">
                                <i class="fas fa-save me-1"></i> Lưu Thay Đổi
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
