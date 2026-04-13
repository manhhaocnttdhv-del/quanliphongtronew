<x-app-layout>
    <x-slot name="header">Thêm Phòng Mới</x-slot>

    <div class="row">
        <div class="col-lg-10 mx-auto">
            <div class="card card-round">
                <div class="card-header">
                    <div class="card-head-row">
                        <div class="card-title">Thông tin Phòng</div>
                        <a href="{{ route('admin.rooms.index') }}" class="btn btn-sm btn-secondary btn-round">
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

                    <form action="{{ route('admin.rooms.store') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="house_id">Thuộc Khu trọ <span class="text-danger">*</span></label>
                                    <select id="house_id" name="house_id" class="form-select @error('house_id') is-invalid @enderror" required>
                                        <option value="">-- Chọn khu trọ --</option>
                                        @foreach($houses as $house)
                                            <option value="{{ $house->id }}" {{ old('house_id')==$house->id?'selected':'' }}>{{ $house->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('house_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Tên/Số phòng <span class="text-danger">*</span></label>
                                    <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror"
                                           value="{{ old('name') }}" required placeholder="VD: P101" autofocus>
                                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="price">Giá thuê (VNĐ/tháng) <span class="text-danger">*</span></label>
                                    <input type="number" id="price" name="price" class="form-control @error('price') is-invalid @enderror"
                                           value="{{ old('price') }}" required placeholder="VD: 3000000">
                                    @error('price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="floor">Tầng <span class="text-danger">*</span></label>
                                    <input type="number" id="floor" name="floor" class="form-control @error('floor') is-invalid @enderror"
                                           value="{{ old('floor', 1) }}" required>
                                    @error('floor')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="area">Diện tích (m²)</label>
                                    <input type="number" step="0.1" id="area" name="area" class="form-control @error('area') is-invalid @enderror"
                                           value="{{ old('area') }}" placeholder="VD: 25.5">
                                    @error('area')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="max_occupants">Số người ở tối đa <span class="text-danger">*</span></label>
                                    <input type="number" id="max_occupants" name="max_occupants" class="form-control @error('max_occupants') is-invalid @enderror"
                                           value="{{ old('max_occupants', 4) }}" required>
                                    @error('max_occupants')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="status">Trạng thái <span class="text-danger">*</span></label>
                                    <select id="status" name="status" class="form-select @error('status') is-invalid @enderror" required>
                                        <option value="available"   {{ old('status')=='available'   ?'selected':'' }}>Trống (Sẵn sàng cho thuê)</option>
                                        <option value="rented"      {{ old('status')=='rented'      ?'selected':'' }}>Đang thuê</option>
                                        <option value="maintenance" {{ old('status')=='maintenance' ?'selected':'' }}>Đang bảo trì</option>
                                    </select>
                                    @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="description">Mô tả thêm về phòng</label>
                                    <textarea id="description" name="description" rows="3" class="form-control @error('description') is-invalid @enderror"
                                              placeholder="VD: Phòng góc, có cửa sổ thoáng mát...">{{ old('description') }}</textarea>
                                    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>

                        <div class="card-action d-flex justify-content-end gap-2 mt-3">
                            <a href="{{ route('admin.rooms.index') }}" class="btn btn-secondary btn-round">Hủy</a>
                            <button type="submit" class="btn btn-primary btn-round">
                                <i class="fas fa-save me-1"></i> Lưu Phòng
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
