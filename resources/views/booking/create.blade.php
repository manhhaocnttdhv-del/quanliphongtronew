@extends('layouts.customer')

@section('title', 'Gửi yêu cầu đặt thuê')

@section('content')
<div class="row g-4">
    <div class="col-lg-8 order-lg-1 order-2">
        <div class="card card-c">
            <div class="card-body p-4">
                <h3 class="mb-4"><i class="fas fa-file-signature me-2 text-primary"></i>Gửi yêu cầu đặt thuê</h3>

                <form method="POST" action="{{ route('booking.store') }}">
                    @csrf
                    <input type="hidden" name="room_id" value="{{ $room->id }}">

                    <h6 class="text-uppercase text-muted small mb-2">Thông tin cá nhân</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Số CCCD <span class="text-danger">*</span></label>
                            <input type="text" name="cccd" class="form-control @error('cccd') is-invalid @enderror" value="{{ old('cccd') }}" maxlength="12" required pattern="\d{12}" placeholder="12 chữ số">
                            @error('cccd')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Số điện thoại <span class="text-danger">*</span></label>
                            <input type="tel" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', auth()->user()->phone) }}" maxlength="10" required pattern="0\d{9}" placeholder="0xxxxxxxxx">
                            @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Ngày sinh</label>
                            <input type="date" name="birthday" class="form-control @error('birthday') is-invalid @enderror" value="{{ old('birthday') }}">
                            @error('birthday')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Giới tính</label>
                            <select name="gender" class="form-select">
                                <option value="">-- Chọn --</option>
                                <option value="male" @selected(old('gender')==='male')>Nam</option>
                                <option value="female" @selected(old('gender')==='female')>Nữ</option>
                                <option value="other" @selected(old('gender')==='other')>Khác</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Quê quán</label>
                            <input type="text" name="hometown" class="form-control" value="{{ old('hometown') }}" placeholder="VD: Nghệ An">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Địa chỉ thường trú</label>
                            <input type="text" name="address" class="form-control" value="{{ old('address') }}" placeholder="Số nhà, đường, phường, quận, tỉnh">
                        </div>
                    </div>

                    <h6 class="text-uppercase text-muted small mb-2">Mong muốn thuê</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label">Ngày dự kiến chuyển vào <span class="text-danger">*</span></label>
                            <input type="date" name="desired_move_in_date" class="form-control @error('desired_move_in_date') is-invalid @enderror" value="{{ old('desired_move_in_date') }}" min="{{ now()->toDateString() }}" required>
                            @error('desired_move_in_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Số người ở dự kiến <span class="text-danger">*</span></label>
                            <input type="number" name="desired_occupants" class="form-control @error('desired_occupants') is-invalid @enderror" value="{{ old('desired_occupants', 1) }}" min="1" max="{{ $room->max_occupants }}" required>
                            <small class="text-muted">Tối đa {{ $room->max_occupants }} người</small>
                            @error('desired_occupants')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Thời hạn thuê (tháng) <span class="text-danger">*</span></label>
                            <input type="number" name="desired_lease_months" class="form-control @error('desired_lease_months') is-invalid @enderror" value="{{ old('desired_lease_months', 12) }}" min="1" max="36" required>
                            @error('desired_lease_months')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">Ghi chú thêm</label>
                            <textarea name="customer_note" class="form-control" rows="3" placeholder="Câu hỏi, mong muốn thêm cho chủ trọ...">{{ old('customer_note') }}</textarea>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary px-4"><i class="fas fa-paper-plane me-1"></i>Gửi yêu cầu</button>
                        <a href="{{ route('home') }}" class="btn btn-outline-secondary">Hủy</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4 order-lg-2 order-1">
        <div class="card card-c">
            <div class="card-body">
                <h5 class="mb-3"><i class="fas fa-home me-2 text-primary"></i>Thông tin phòng</h5>
                <h4 class="text-primary fw-bold">Phòng {{ $room->name }}</h4>
                @if($room->house)
                    <p class="text-muted small mb-2"><i class="fas fa-map-marker-alt text-danger me-1"></i>{{ $room->house->name }} — {{ $room->house->address }}</p>
                @endif
                <hr>
                <ul class="list-unstyled mb-0">
                    <li class="mb-2"><i class="fas fa-tag text-warning me-2"></i><strong>{{ number_format($room->price, 0, ',', '.') }} đ</strong>/tháng</li>
                    <li class="mb-2"><i class="fas fa-vector-square text-primary me-2"></i>Diện tích: {{ $room->area }} m²</li>
                    <li class="mb-2"><i class="fas fa-users text-primary me-2"></i>Sức chứa: {{ $room->max_occupants }} người</li>
                    <li class="mb-2"><i class="fas fa-layer-group text-primary me-2"></i>Tầng: {{ $room->floor }}</li>
                    @if($room->roomType)
                        <li class="mb-2"><i class="fas fa-bed text-primary me-2"></i>Loại: {{ $room->roomType->name }}</li>
                    @endif
                </ul>
            </div>
        </div>
        <div class="alert alert-info small">
            <i class="fas fa-info-circle me-1"></i>Sau khi gửi, admin sẽ duyệt yêu cầu trong 24h. Bạn sẽ được thông báo qua email và trên hệ thống.
        </div>
    </div>
</div>
@endsection
