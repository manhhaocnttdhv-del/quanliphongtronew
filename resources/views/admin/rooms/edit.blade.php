<x-app-layout>
    <x-slot name="header">Cập Nhật Phòng: {{ $room->name }}</x-slot>

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

                    <form action="{{ route('admin.rooms.update', $room) }}" method="POST" enctype="multipart/form-data">
                        @csrf @method('PUT')
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="house_id">Thuộc Khu trọ <span class="text-danger">*</span></label>
                                    <select id="house_id" name="house_id" class="form-select @error('house_id') is-invalid @enderror" required>
                                        <option value="">-- Chọn khu trọ --</option>
                                        @foreach($houses as $house)
                                            <option value="{{ $house->id }}" {{ old('house_id',$room->house_id)==$house->id?'selected':'' }}>{{ $house->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('house_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Tên/Số phòng <span class="text-danger">*</span></label>
                                    <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror"
                                           value="{{ old('name', $room->name) }}" required>
                                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="price">Giá thuê (VNĐ/tháng) <span class="text-danger">*</span></label>
                                    <input type="number" id="price" name="price" class="form-control @error('price') is-invalid @enderror"
                                           value="{{ old('price', (int)$room->price) }}" required>
                                    @error('price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="floor">Tầng <span class="text-danger">*</span></label>
                                    <input type="number" id="floor" name="floor" class="form-control @error('floor') is-invalid @enderror"
                                           value="{{ old('floor', $room->floor) }}" required>
                                    @error('floor')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="area">Diện tích (m²)</label>
                                    <input type="number" step="0.1" id="area" name="area" class="form-control @error('area') is-invalid @enderror"
                                           value="{{ old('area', $room->area) }}">
                                    @error('area')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="max_occupants">Số người ở tối đa <span class="text-danger">*</span></label>
                                    <input type="number" id="max_occupants" name="max_occupants" class="form-control @error('max_occupants') is-invalid @enderror"
                                           value="{{ old('max_occupants', $room->max_occupants) }}" required>
                                    @error('max_occupants')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="status">Trạng thái <span class="text-danger">*</span></label>
                                    <select id="status" name="status" class="form-select @error('status') is-invalid @enderror" required>
                                        <option value="available"   {{ old('status',$room->status)=='available'   ?'selected':'' }}>Trống (Sẵn sàng cho thuê)</option>
                                        <option value="rented"      {{ old('status',$room->status)=='rented'      ?'selected':'' }}>Đang thuê</option>
                                        <option value="maintenance" {{ old('status',$room->status)=='maintenance' ?'selected':'' }}>Đang bảo trì</option>
                                    </select>
                                    @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="images">Hình ảnh phòng (chọn nhiều ảnh)</label>
                                    <input type="file" id="images" name="images[]" class="form-control @error('images') is-invalid @enderror" accept="image/*" multiple>
                                    <div id="image-preview-container" class="mt-2 d-flex gap-2 flex-wrap">
                                        @if($room->images)
                                            @foreach($room->images as $img)
                                                <img src="{{ Str::startsWith($img, ['http://', 'https://']) ? $img : Storage::url($img) }}" alt="Ảnh phòng" style="max-height: 80px; border-radius: 4px; object-fit: cover; border: 1px solid #ddd;">
                                            @endforeach
                                        @elseif($room->image_path)
                                            <img src="{{ Str::startsWith($room->image_path, ['http://', 'https://']) ? $room->image_path : Storage::url($room->image_path) }}" alt="Ảnh phòng" style="max-height: 80px; border-radius: 4px; object-fit: cover; border: 1px solid #ddd;">
                                        @endif
                                    </div>
                                    @error('images')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="description">Mô tả thêm về phòng</label>
                                    <textarea id="description" name="description" rows="3" class="form-control @error('description') is-invalid @enderror">{{ old('description', $room->description) }}</textarea>
                                    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>
                        <div class="card-action d-flex justify-content-end gap-2 mt-3">
                            <a href="{{ route('admin.rooms.index') }}" class="btn btn-secondary btn-round">Hủy</a>
                            <button type="submit" class="btn btn-primary btn-round">
                                <i class="fas fa-save me-1"></i> Lưu Thay Đổi
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

<x-slot name="scripts">
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const imageInput = document.getElementById('images');
            const previewContainer = document.getElementById('image-preview-container');

            imageInput.addEventListener('change', function() {
                previewContainer.innerHTML = ''; // Xóa preview cũ
                
                if (this.files) {
                    Array.from(this.files).forEach(file => {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            const img = document.createElement('img');
                            img.src = e.target.result;
                            img.style.maxHeight = '80px';
                            img.style.borderRadius = '4px';
                            img.style.objectFit = 'cover';
                            img.style.border = '1px solid #ddd';
                            previewContainer.appendChild(img);
                        }
                        reader.readAsDataURL(file);
                    });
                }
            });
        });
    </script>
</x-slot>
</x-app-layout>
