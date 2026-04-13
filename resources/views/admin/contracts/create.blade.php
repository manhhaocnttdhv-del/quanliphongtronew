<x-app-layout>
    <x-slot name="header">Lập Hợp Đồng Thuê Mới</x-slot>

    <div class="row">
        <div class="col-lg-10 mx-auto">
            <div class="card card-round">
                <div class="card-header">
                    <div class="card-head-row">
                        <div class="card-title">Thông tin Hợp đồng</div>
                        <a href="{{ route('admin.contracts.index') }}" class="btn btn-sm btn-secondary btn-round">
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

                    <form action="{{ route('admin.contracts.store') }}" method="POST">
                        @csrf

                        <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">
                            <i class="fas fa-door-open me-1"></i> Phòng & Khách thuê
                        </h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="room_id">Phòng thuê <span class="text-danger">*</span></label>
                                    <select id="room_id" name="room_id" class="form-select @error('room_id') is-invalid @enderror" required>
                                        <option value="">-- Chọn phòng --</option>
                                        @foreach($rooms as $room)
                                            <option value="{{ $room->id }}" {{ old('room_id')==$room->id?'selected':'' }}>
                                                Phòng {{ $room->name }} ({{ $room->house->name }}) — {{ number_format($room->price,0,',','.')}}đ/tháng
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('room_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tenant_id">Khách thuê đại diện (Người ký) <span class="text-danger">*</span></label>
                                    <select id="tenant_id" name="tenant_id" class="form-select @error('tenant_id') is-invalid @enderror" required>
                                        <option value="">-- Chọn khách thuê --</option>
                                        @foreach($tenants as $tenant)
                                            <option value="{{ $tenant->id }}" {{ old('tenant_id')==$tenant->id?'selected':'' }}>
                                                {{ $tenant->user->name }} ({{ $tenant->phone ?? 'Không có SĐT' }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('tenant_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>

                        <h6 class="fw-bold text-primary border-bottom pb-2 mb-3 mt-3">
                            <i class="fas fa-file-contract me-1"></i> Điều khoản & Chi phí
                        </h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="monthly_price">Tiền phòng xuất hóa đơn (VNĐ/tháng) <span class="text-danger">*</span></label>
                                    <input type="number" id="monthly_price" name="monthly_price" class="form-control @error('monthly_price') is-invalid @enderror"
                                           value="{{ old('monthly_price') }}" required placeholder="VD: 3000000">
                                    @error('monthly_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="deposit">Tiền cọc (VNĐ) <span class="text-danger">*</span></label>
                                    <input type="number" id="deposit" name="deposit" class="form-control @error('deposit') is-invalid @enderror"
                                           value="{{ old('deposit', 0) }}" required>
                                    @error('deposit')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="start_date">Ngày bắt đầu tính tiền <span class="text-danger">*</span></label>
                                    <input type="date" id="start_date" name="start_date" class="form-control @error('start_date') is-invalid @enderror"
                                           value="{{ old('start_date') }}" required>
                                    @error('start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="end_date">Ngày hết hạn hợp đồng <span class="text-danger">*</span></label>
                                    <input type="date" id="end_date" name="end_date" class="form-control @error('end_date') is-invalid @enderror"
                                           value="{{ old('end_date') }}" required>
                                    @error('end_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="occupants">Số người ở thực tế <span class="text-danger">*</span></label>
                                    <input type="number" id="occupants" name="occupants" class="form-control @error('occupants') is-invalid @enderror"
                                           value="{{ old('occupants', 1) }}" required>
                                    @error('occupants')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="notes">Ghi chú / Điều khoản thêm</label>
                                    <textarea id="notes" name="notes" rows="3" class="form-control @error('notes') is-invalid @enderror"
                                              placeholder="VD: Giá điện 3.500đ/kWh, Nước 80.000đ/người, Wifi 80.000đ/tháng...">{{ old('notes') }}</textarea>
                                    @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>

                        <div class="card-action d-flex justify-content-end gap-2 mt-3">
                            <a href="{{ route('admin.contracts.index') }}" class="btn btn-secondary btn-round">Hủy</a>
                            <button type="submit" class="btn btn-primary btn-round">
                                <i class="fas fa-file-signature me-1"></i> Lưu Hợp Đồng
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
