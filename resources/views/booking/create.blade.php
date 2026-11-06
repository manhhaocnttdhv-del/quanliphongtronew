@extends('layouts.customer')
@section('title', 'Yêu cầu thuê phòng ' . $room->name)
@section('content')
<nav class="breadcrumb-c" aria-label="Đường dẫn"><a href="{{ route('home') }}">Tìm phòng</a><i class="fas fa-chevron-right" aria-hidden="true"></i><a href="{{ route('room.show', $room) }}">Phòng {{ $room->name }}</a><i class="fas fa-chevron-right" aria-hidden="true"></i><span aria-current="page">Đặt thuê</span></nav>
<div class="booking-heading"><h1>Yêu cầu đặt thuê phòng</h1><p>Phòng {{ $room->name }}, chi nhánh {{ $room->house->name }}.</p></div>
<ol class="booking-steps" aria-label="Quy trình thuê phòng"><li aria-current="step"><span>1</span>Yêu cầu thuê</li><li><span>2</span>Xác nhận & hồ sơ</li><li><span>3</span>Ký hợp đồng</li><li><span>4</span>Đặt cọc</li></ol>
<div class="booking-layout">
    <form class="booking-form" method="POST" action="{{ route('booking.store') }}" id="bookingForm">
        @csrf<input type="hidden" name="room_id" value="{{ $room->id }}">
        <fieldset><legend><i class="far fa-user" aria-hidden="true"></i>Thông tin người thuê</legend>
            <div class="booking-field-grid">
                <div><label class="form-label" for="booking_name">Họ và tên</label><input class="form-control" id="booking_name" value="{{ auth()->user()->name }}" readonly autocomplete="name"></div>
                <div><label class="form-label" for="phone">Số điện thoại <span class="text-danger">*</span></label><input id="phone" type="tel" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', auth()->user()->phone) }}" maxlength="10" required pattern="0\d{9}" inputmode="tel" autocomplete="tel" placeholder="0xxxxxxxxx">@error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                <div><label class="form-label" for="cccd">Số CCCD <span class="text-danger">*</span></label><input id="cccd" type="text" name="cccd" class="form-control @error('cccd') is-invalid @enderror" value="{{ old('cccd') }}" maxlength="12" required pattern="\d{12}" inputmode="numeric" placeholder="12 chữ số">@error('cccd')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                <div><label class="form-label" for="birthday">Ngày sinh <span class="field-optional">Không bắt buộc</span></label><input id="birthday" type="date" name="birthday" class="form-control @error('birthday') is-invalid @enderror" value="{{ old('birthday') }}" max="{{ now()->subDay()->toDateString() }}" autocomplete="bday">@error('birthday')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                <div><label class="form-label" for="gender">Giới tính <span class="field-optional">Không bắt buộc</span></label><select id="gender" name="gender" class="form-select @error('gender') is-invalid @enderror"><option value="">Chưa chọn</option><option value="male" @selected(old('gender') === 'male')>Nam</option><option value="female" @selected(old('gender') === 'female')>Nữ</option><option value="other" @selected(old('gender') === 'other')>Khác</option></select>@error('gender')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                <div><label class="form-label" for="hometown">Quê quán <span class="field-optional">Không bắt buộc</span></label><input id="hometown" name="hometown" class="form-control @error('hometown') is-invalid @enderror" value="{{ old('hometown') }}" maxlength="255" placeholder="VD: Nghệ An">@error('hometown')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                <div class="full"><label class="form-label" for="address">Địa chỉ thường trú <span class="field-optional">Không bắt buộc</span></label><input id="address" name="address" class="form-control @error('address') is-invalid @enderror" value="{{ old('address') }}" maxlength="500" autocomplete="street-address" placeholder="Số nhà, đường, phường/xã, tỉnh/thành">@error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
            </div>
        </fieldset>
        <fieldset><legend><i class="far fa-calendar-alt" aria-hidden="true"></i>Thông tin thuê phòng</legend>
            <div class="booking-field-grid three">
                <div><label class="form-label" for="desired_move_in_date">Ngày chuyển vào <span class="text-danger">*</span></label><input id="desired_move_in_date" type="date" name="desired_move_in_date" class="form-control @error('desired_move_in_date') is-invalid @enderror" value="{{ old('desired_move_in_date') }}" min="{{ now()->toDateString() }}" required>@error('desired_move_in_date')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                <div><label class="form-label" for="desired_occupants">Số người ở <span class="text-danger">*</span></label><input id="desired_occupants" type="number" name="desired_occupants" class="form-control @error('desired_occupants') is-invalid @enderror" value="{{ old('desired_occupants', 1) }}" min="1" max="{{ $room->max_occupants }}" step="1" required><span class="field-help">Tối đa {{ $room->max_occupants }} người</span>@error('desired_occupants')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                <div><label class="form-label" for="desired_lease_months">Thời hạn thuê <span class="text-danger">*</span></label><input id="desired_lease_months" type="number" name="desired_lease_months" class="form-control @error('desired_lease_months') is-invalid @enderror" value="{{ old('desired_lease_months', 12) }}" min="1" max="36" step="1" required><span class="field-help">Tháng, từ 1 đến 36</span>@error('desired_lease_months')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                <div class="full"><label class="form-label" for="customer_note">Lời nhắn cho ban quản lý <span class="field-optional">Không bắt buộc</span></label><textarea id="customer_note" name="customer_note" class="form-control @error('customer_note') is-invalid @enderror" rows="3" maxlength="1000" placeholder="Thời gian muốn xem phòng hoặc yêu cầu khác...">{{ old('customer_note') }}</textarea>@error('customer_note')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
            </div>
        </fieldset>
        <div class="booking-actions"><a href="{{ route('room.show', $room) }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2" aria-hidden="true"></i>Quay lại phòng</a><button type="submit" class="btn btn-primary" id="bookingSubmit"><i class="far fa-paper-plane me-2" aria-hidden="true"></i>Gửi yêu cầu đặt thuê</button></div>
    </form>
    <aside class="reservation-panel booking-summary" aria-label="Phòng bạn chọn">
        <div class="summary-photo"><x-room-image :room="$room" loading="eager" /></div>
        <div class="summary-content"><p class="rental-branch mb-1">{{ $room->house->name }}</p><h2>Phòng {{ $room->name }}</h2><p class="branch-address mb-0">{{ $room->house->address }}</p><div class="reservation-price">{{ number_format($room->price, 0, ',', '.') }} đ <span class="field-optional">/ tháng</span></div>
            <div class="summary-specs"><span><i class="fas fa-vector-square" aria-hidden="true"></i>{{ $room->area !== null ? number_format((float) $room->area, 2, ',', '.') . ' m²' : 'Chưa rõ diện tích' }}</span><span><i class="fas fa-user-friends" aria-hidden="true"></i>{{ $room->max_occupants }} người</span><span>Tầng {{ $room->floor }}</span></div>
            <hr><div class="summary-total"><span>Thanh toán lúc gửi</span><strong>0 đ</strong></div><p class="summary-notice"><i class="fas fa-shield-alt" aria-hidden="true"></i>Tiền cọc được xác nhận sau khi ban quản lý duyệt yêu cầu và hai bên thống nhất hợp đồng.</p>
        </div>
    </aside>
</div>
@endsection
@push('scripts')
<script>
document.getElementById('bookingForm').addEventListener('submit', function () {
    const button = document.getElementById('bookingSubmit');
    button.disabled = true;
    button.setAttribute('aria-busy', 'true');
    button.textContent = 'Đang gửi yêu cầu...';
});
</script>
@endpush
