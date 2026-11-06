@props(['room', 'imageIndex' => 0])
@php
    $photos = array_values(array_unique(array_filter($room->images ?? [])));
    $photo = $photos[$imageIndex] ?? $photos[0] ?? $room->image_path;
    $photoUrl = $photo ? (\Illuminate\Support\Str::startsWith($photo, ['https://', 'http://']) ? $photo : \Illuminate\Support\Facades\Storage::url($photo)) : null;
    if ($photo && \Illuminate\Support\Str::startsWith($photo, ['https://', 'http://'])) {
        $localPhoto = 'images/rooms/' . basename(parse_url($photo, PHP_URL_PATH));
        if (is_file(public_path($localPhoto))) $photoUrl = asset($localPhoto);
    }
@endphp
@if($photoUrl)
    <img src="{{ $photoUrl }}" {{ $attributes->merge(['alt' => 'Phòng ' . $room->name, 'loading' => 'lazy', 'width' => 900, 'height' => 600, 'decoding' => 'async']) }}
         onerror="this.hidden=true;this.parentElement.classList.add('photo-missing')">
@else
    <span class="room-photo-empty"><i class="fas fa-image" aria-hidden="true"></i> Chưa có ảnh phòng</span>
@endif
