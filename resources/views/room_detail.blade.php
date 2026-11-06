@extends('layouts.customer')
@section('title', 'Phòng ' . $room->name . ' - ' . $room->house->name)
@php
    $imageCount = count(array_unique(array_filter($room->images ?? [])));
    $statusLabel = ['available' => 'Còn trống', 'reserved' => 'Đang giữ chỗ', 'rented' => 'Đã có người thuê', 'maintenance' => 'Đang bảo trì'][$room->status] ?? 'Chưa nhận đặt thuê';
    $activeServices = $room->services->filter(fn ($service) => $service->pivot->is_active);
@endphp
@section('content')
<nav class="breadcrumb-c" aria-label="Đường dẫn"><a href="{{ route('home') }}">Tìm phòng</a><i class="fas fa-chevron-right" aria-hidden="true"></i><a href="{{ route('home', ['house_id' => $room->house_id]) }}">{{ $room->house->name }}</a><i class="fas fa-chevron-right" aria-hidden="true"></i><span aria-current="page">Phòng {{ $room->name }}</span></nav>
<div class="room-detail-heading"><div><h1>Phòng {{ $room->name }}</h1><p><i class="fas fa-map-marker-alt me-1" aria-hidden="true"></i>{{ $room->house->address }}</p></div><a class="btn btn-outline-secondary" href="{{ route('home', ['house_id' => $room->house_id]) }}"><i class="fas fa-arrow-left me-2" aria-hidden="true"></i><span class="d-none d-sm-inline">Phòng cùng chi nhánh</span><span class="d-sm-none">Quay lại</span></a></div>
<div class="room-detail-layout">
    <div class="room-gallery">
        <div class="main-photo"><x-room-image :room="$room" id="mainImage" loading="eager" fetchpriority="high" />@if($imageCount > 0 || $room->image_path)<button class="photo-expand" type="button" id="openGallery"><i class="fas fa-expand" aria-hidden="true"></i>Xem ảnh lớn</button>@endif</div>
        @if($imageCount > 1)
            <div class="gallery-thumbnails" role="group" aria-label="Ảnh phòng">
                @foreach(range(0, $imageCount - 1) as $imageIndex)
                    <button class="gallery-thumb" type="button" aria-label="Xem ảnh {{ $imageIndex + 1 }}" aria-pressed="{{ $imageIndex === 0 ? 'true' : 'false' }}"><x-room-image :room="$room" :image-index="$imageIndex" /></button>
                @endforeach
            </div>
        @endif
        <div class="detail-specs">
            <div><i class="fas fa-vector-square" aria-hidden="true"></i><small>Diện tích</small><strong>{{ $room->area !== null ? number_format((float) $room->area, 2, ',', '.') . ' m²' : 'Chưa cập nhật' }}</strong></div>
            <div><i class="fas fa-user-friends" aria-hidden="true"></i><small>Số người ở</small><strong>Tối đa {{ $room->max_occupants }} người</strong></div>
            <div><i class="fas fa-layer-group" aria-hidden="true"></i><small>Vị trí</small><strong>Tầng {{ $room->floor }}</strong></div>
            <div><i class="fas fa-bed" aria-hidden="true"></i><small>Loại phòng</small><strong>{{ $room->roomType->name ?? 'Phòng cho thuê' }}</strong></div>
        </div>
        <section class="detail-section"><h2>Về phòng này</h2><p>{{ $room->description ?: 'Ban quản lý đang cập nhật mô tả chi tiết. Vui lòng liên hệ chi nhánh để xác nhận hiện trạng và tiện ích của phòng.' }}</p></section>
        @if($activeServices->isNotEmpty())
            <section class="detail-section"><h2>Giá dịch vụ</h2><dl class="service-list mb-0">@foreach($activeServices as $service)<div><dt>{{ $service->name }}</dt><dd>{{ number_format((float) ($service->pivot->custom_price ?? $service->price), 0, ',', '.') }} đ / {{ $service->unit }}</dd></div>@endforeach</dl></section>
        @endif
        <section class="detail-section"><h2>Chi nhánh {{ $room->house->name }}</h2><p>{{ $room->house->address }}</p>@if($room->house->description)<p class="mt-2">{{ $room->house->description }}</p>@endif<a class="d-inline-block mt-3 small" href="{{ route('home', ['house_id' => $room->house_id]) }}">Xem phòng còn trống tại chi nhánh <i class="fas fa-arrow-right ms-1" aria-hidden="true"></i></a></section>
    </div>
    <aside class="reservation-panel" aria-label="Giá và yêu cầu đặt thuê">
        <span class="status-pill {{ $room->isBookable() ? '' : 'unavailable' }}"><i class="fas {{ $room->isBookable() ? 'fa-check-circle' : 'fa-clock' }}" aria-hidden="true"></i>{{ $statusLabel }}</span>
        <div class="reservation-price">{{ number_format($room->price, 0, ',', '.') }} đ</div><p class="reservation-unit">mỗi tháng, chưa gồm dịch vụ</p>
        <hr><p class="branch-name mb-0">{{ \App\Models\Setting::get('site_name', 'Trọ Nhà Mình') }}</p><p class="branch-address">Chi nhánh {{ $room->house->name }}</p>
        @if(auth()->check() && auth()->user()->hasRole('admin'))
            <a href="{{ route('admin.rooms.edit', $room) }}" class="btn btn-primary w-100 mt-2"><i class="fas fa-pen me-2" aria-hidden="true"></i>Quản lý phòng</a>
        @elseif($room->isBookable())
            <a href="{{ route('booking.create', ['room_id' => $room->id]) }}" class="btn btn-primary w-100 mt-2"><i class="fas fa-file-signature me-2" aria-hidden="true"></i>Yêu cầu đặt thuê</a><p class="booking-note">Chưa thu tiền khi gửi yêu cầu.</p>
        @else
            <button type="button" class="btn btn-secondary w-100" disabled>{{ $statusLabel }}</button><a class="btn btn-outline-primary w-100 mt-2" href="{{ route('home', ['house_id' => $room->house_id]) }}">Xem phòng khác</a>
        @endif
        @if($room->house->phone)<a class="btn btn-outline-secondary w-100 mt-3" href="tel:{{ preg_replace('/[^0-9+]/', '', $room->house->phone) }}"><i class="fas fa-phone me-2" aria-hidden="true"></i>{{ $room->house->phone }}</a>@endif
        @if($zaloNumber)<a class="d-block text-center small mt-3" href="https://zalo.me/{{ preg_replace('/[^0-9]/', '', $zaloNumber) }}" target="_blank" rel="noopener">Nhắn ban quản lý qua Zalo <i class="fas fa-external-link-alt ms-1" aria-hidden="true"></i></a>@endif
    </aside>
</div>
<dialog class="gallery-dialog" id="galleryDialog" aria-labelledby="galleryTitle">
    <div class="gallery-dialog-header"><h2 id="galleryTitle">Ảnh phòng {{ $room->name }}</h2><button type="button" id="closeGallery" aria-label="Đóng ảnh" title="Đóng ảnh"><i class="fas fa-times" aria-hidden="true"></i></button></div>
    <img id="galleryImage" alt="Phòng {{ $room->name }}" width="1200" height="800">
    <div class="gallery-dialog-controls"><button type="button" id="previousPhoto" aria-label="Ảnh trước" title="Ảnh trước"><i class="fas fa-chevron-left" aria-hidden="true"></i></button><span id="galleryCount" aria-live="polite"></span><button type="button" id="nextPhoto" aria-label="Ảnh tiếp theo" title="Ảnh tiếp theo"><i class="fas fa-chevron-right" aria-hidden="true"></i></button></div>
</dialog>
@endsection
@push('scripts')
<script>
const thumbs = [...document.querySelectorAll('.gallery-thumb')];
const main = document.getElementById('mainImage');
const dialog = document.getElementById('galleryDialog');
const galleryImage = document.getElementById('galleryImage');
const photos = thumbs.length ? thumbs.map(button => button.querySelector('img')?.src).filter(Boolean) : (main ? [main.src] : []);
let photoIndex = 0;
function showPhoto(index) {
    if (!photos.length) return;
    photoIndex = (index + photos.length) % photos.length;
    if (main) {
        main.hidden = false;
        main.parentElement.classList.remove('photo-missing');
        main.src = photos[photoIndex];
    }
    galleryImage.src = photos[photoIndex];
    document.getElementById('galleryCount').textContent = `${photoIndex + 1} / ${photos.length}`;
    thumbs.forEach((item, i) => item.setAttribute('aria-pressed', String(i === photoIndex)));
}
thumbs.forEach((button, index) => {
    button.addEventListener('click', () => {
        const source = button.querySelector('img');
        if (!source || !main || source.hidden) return;
        showPhoto(index);
    });
});
document.getElementById('openGallery')?.addEventListener('click', () => { showPhoto(photoIndex); dialog.showModal(); });
document.getElementById('closeGallery').addEventListener('click', () => dialog.close());
document.getElementById('previousPhoto').addEventListener('click', () => showPhoto(photoIndex - 1));
document.getElementById('nextPhoto').addEventListener('click', () => showPhoto(photoIndex + 1));
dialog.addEventListener('keydown', event => {
    if (event.key === 'ArrowLeft' || event.key === 'ArrowRight') {
        event.preventDefault();
        showPhoto(photoIndex + (event.key === 'ArrowRight' ? 1 : -1));
    }
});
dialog.addEventListener('click', event => { if (event.target === dialog) dialog.close(); });
document.getElementById('previousPhoto').hidden = photos.length < 2;
document.getElementById('nextPhoto').hidden = photos.length < 2;
</script>
@endpush
