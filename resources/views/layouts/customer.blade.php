@php
    $siteName = \App\Models\Setting::get('site_name', 'Trọ Nhà Mình');
    $contactPhone = \App\Models\Setting::get('contact_phone');
    $contactEmail = \App\Models\Setting::get('contact_email');
@endphp
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#b43b32">
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/brand-home.svg') }}">
    <title>@yield('title', 'Phòng trọ Nghệ An') | {{ $siteName }}</title>
    <meta name="description" content="Phòng trọ tại các chi nhánh {{ $siteName }} ở Nghệ An. Thông tin phòng, giá thuê và yêu cầu đặt thuê trực tiếp với ban quản lý.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('kaiadmin/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('kaiadmin/css/fonts.min.css') }}">
    @vite('resources/css/customer.css')
    @stack('styles')
</head>
<body>
<a class="visually-hidden-focusable" href="#main-content">Đến nội dung chính</a>
<header class="customer-nav">
    <nav class="navbar navbar-expand-lg p-0" aria-label="Điều hướng chính">
        <div class="container">
            <a class="customer-brand" href="{{ route('home') }}">
                <span class="brand-symbol"><i class="fas fa-home" aria-hidden="true"></i></span>
                <span class="brand-copy"><strong translate="no">{{ $siteName }}</strong><small>Phòng trọ ở Vinh, Nghệ An</small></span>
            </a>
            @guest
                <a class="customer-mobile-register" href="{{ route('register') }}"><i class="fas fa-user-plus" aria-hidden="true"></i>Đăng ký</a>
            @endguest
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMain" aria-controls="navMain" aria-expanded="false" aria-label="Mở menu">
                <i class="fas fa-bars" aria-hidden="true"></i>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navMain">
                <ul class="navbar-nav align-items-lg-center gap-1">
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('home', 'room.show') ? 'active' : '' }}" href="{{ route('home') }}">Tìm phòng</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('home') }}#chi-nhanh">Các chi nhánh</a></li>
                    <li class="nav-item"><a class="nav-link" href="#lien-he">Liên hệ</a></li>
                    @auth
                        @if(auth()->user()->hasRole('admin'))
                            <li class="nav-item"><a class="nav-link" href="{{ route('admin.dashboard') }}">Quản trị</a></li>
                        @else
                            <li class="nav-item"><a class="nav-link {{ request()->routeIs('booking.*') ? 'active' : '' }}" href="{{ route('booking.index') }}">Yêu cầu của tôi</a></li>
                        @endif
                        @if(auth()->user()->hasRole('tenant'))
                            <li class="nav-item"><a class="nav-link" href="{{ route('tenant.dashboard') }}">Phòng của tôi</a></li>
                        @endif
                        @php
                            $unreadCount = auth()->user()->unreadNotifications()->count();
                            $notifications = auth()->user()->notifications()->latest()->limit(8)->get();
                        @endphp
                        <li class="nav-item dropdown">
                            <button class="nav-link position-relative border-0 bg-transparent" id="customerNotifications" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Thông báo{{ $unreadCount ? ', ' . $unreadCount . ' chưa đọc' : '' }}" title="Thông báo">
                                <i class="far fa-bell" aria-hidden="true"></i>
                                @if($unreadCount)<span class="notification-count">{{ $unreadCount > 9 ? '9+' : $unreadCount }}</span>@endif
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end notification-menu" aria-labelledby="customerNotifications">
                                <li class="px-3 py-2 border-bottom d-flex align-items-center justify-content-between">
                                    <strong>Thông báo</strong>
                                    @if($unreadCount)
                                        <form method="POST" action="{{ route('notifications.readAll') }}">@csrf
                                            <button class="btn btn-link p-0 small" type="submit">Đọc tất cả</button>
                                        </form>
                                    @endif
                                </li>
                                @forelse($notifications as $notification)
                                    <li><a class="dropdown-item text-wrap py-3 {{ $notification->read_at ? '' : 'bg-light' }}" href="{{ route('notifications.read', $notification->id) }}">
                                        <strong class="d-block small">{{ $notification->data['title'] ?? 'Thông báo mới' }}</strong>
                                        <span class="d-block small text-muted">{{ $notification->data['message'] ?? '' }}</span>
                                    </a></li>
                                @empty
                                    <li class="text-muted text-center py-4 small">Chưa có thông báo</li>
                                @endforelse
                            </ul>
                        </li>
                        <li class="nav-item dropdown">
                            <button class="nav-link dropdown-toggle account-name border-0 bg-transparent" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="far fa-user me-1" aria-hidden="true"></i>{{ auth()->user()->name }}
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="{{ route('profile.edit') }}">Tài khoản</a></li>
                                <li><form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="dropdown-item">Đăng xuất</button></form></li>
                            </ul>
                        </li>
                    @else
                        <li class="nav-item"><a class="nav-link" href="{{ route('login') }}">Đăng nhập</a></li>
                        <li class="nav-item ms-lg-2"><a class="btn btn-primary customer-register-link" href="{{ route('register') }}"><i class="fas fa-user-plus" aria-hidden="true"></i>Đăng ký</a></li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>
</header>
<main id="main-content" class="@yield('main-class', 'container customer-main')">
    @if(session('success') || session('error') || $errors->any())
        <div class="container px-0 py-3">
            @if(session('success'))<div class="alert alert-success" role="status">{{ session('success') }}</div>@endif
            @if(session('error'))<div class="alert alert-danger" role="alert">{{ session('error') }}</div>@endif
            @if($errors->any())
                <div class="alert alert-danger" role="alert"><strong>Vui lòng kiểm tra lại thông tin.</strong><ul class="mb-0 mt-2">@foreach($errors->all() as $message)<li>{{ $message }}</li>@endforeach</ul></div>
            @endif
        </div>
    @endif
    @yield('content')
</main>
<footer class="customer-footer" id="lien-he">
    <div class="container">
        <div class="footer-columns">
            <div>
                <a class="customer-brand" href="{{ route('home') }}"><span class="brand-symbol"><i class="fas fa-home" aria-hidden="true"></i></span><span class="brand-copy"><strong translate="no">{{ $siteName }}</strong><small>Phòng trọ ở Vinh, Nghệ An</small></span></a>
                <p>Một chủ nhà, nhiều chi nhánh. Liên hệ trực tiếp với ban quản lý để xem phòng và làm thủ tục thuê.</p>
            </div>
            <div><h2>Chi nhánh & phòng ở</h2><a href="{{ route('home') }}">Phòng còn trống</a><a href="{{ route('home') }}#chi-nhanh">Địa chỉ các chi nhánh</a><a href="{{ route('booking.index') }}">Yêu cầu đặt thuê</a></div>
            <div>
                <h2>Liên hệ ban quản lý</h2>
                @if($contactPhone)<a href="tel:{{ preg_replace('/[^0-9+]/', '', $contactPhone) }}"><i class="fas fa-phone me-2" aria-hidden="true"></i>{{ $contactPhone }}</a>@endif
                @if($contactEmail)<a href="mailto:{{ $contactEmail }}">{{ $contactEmail }}</a>@endif
                <p>{{ \App\Models\Setting::get('contact_address', 'Nghệ An') }}</p>
            </div>
        </div>
        <div class="footer-bottom"><span>&copy; {{ date('Y') }} {{ $siteName }}</span><span>Nghệ An, Việt Nam</span></div>
    </div>
</footer>
<script src="{{ asset('kaiadmin/js/core/popper.min.js') }}"></script>
<script src="{{ asset('kaiadmin/js/core/bootstrap.min.js') }}"></script>
@stack('scripts')
</body>
</html>
