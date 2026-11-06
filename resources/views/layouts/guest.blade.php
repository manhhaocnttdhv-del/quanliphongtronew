@php
    $siteName = \App\Models\Setting::get('site_name', 'Trọ Nhà Mình');
    $authRoom = \App\Models\Room::available()->inActiveBranch()->with('house:id,name')->first(['id', 'house_id', 'name', 'images', 'image_path']);
@endphp
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#b43b32">
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/brand-home.svg') }}">
    <title>{{ request()->routeIs('register') ? 'Đăng ký thuê phòng' : 'Tài khoản' }} | {{ $siteName }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('kaiadmin/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('kaiadmin/css/fonts.min.css') }}">
    @vite(['resources/css/customer.css', 'resources/js/app.js'])
</head>
<body class="auth-page">
    <header class="container auth-nav">
        <a class="customer-brand" href="{{ route('home') }}"><span class="brand-symbol"><i class="fas fa-home" aria-hidden="true"></i></span><span class="brand-copy"><strong translate="no">{{ $siteName }}</strong><small>Phòng trọ ở Vinh, Nghệ An</small></span></a>
        <a class="small" href="{{ route('home') }}"><i class="fas fa-arrow-left me-2" aria-hidden="true"></i>Xem phòng trống</a>
    </header>
    <main class="container auth-layout">
        <div class="auth-visual">
            @if($authRoom)<x-room-image :room="$authRoom" loading="eager" />@endif
            <div class="auth-visual-caption"><h2>Phòng trọ tại Nghệ An</h2>@if($authRoom)<p>{{ $siteName }}, chi nhánh {{ $authRoom->house->name }}</p>@endif</div>
        </div>
        <div class="auth-form-area"><div class="auth-card">{{ $slot }}</div><p class="auth-footer">&copy; {{ date('Y') }} {{ $siteName }}. Nghệ An, Việt Nam.</p></div>
    </main>
</body>
</html>
