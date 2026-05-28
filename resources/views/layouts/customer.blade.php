<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Đặt thuê phòng') - {{ \App\Models\Setting::get('site_name', 'BoardingPro') }}</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        :root {
            --primary: #4f46e5;
            --primary-dark: #3730a3;
            --secondary: #f59e0b;
            --light: #f8fafc;
        }
        body {
            font-family: 'Inter', sans-serif;
            background: var(--light);
            color: #334155;
        }
        .navbar-customer {
            background: white;
            border-bottom: 1px solid #e5e7eb;
            box-shadow: 0 2px 10px rgba(0,0,0,0.04);
            padding: 12px 0;
        }
        .navbar-customer .navbar-brand {
            font-weight: 800;
            color: var(--primary) !important;
            font-size: 1.4rem;
        }
        .navbar-customer .navbar-brand span { color: var(--secondary); }
        .navbar-customer .nav-link {
            font-weight: 500;
            color: #1f2937 !important;
        }
        .btn-primary-c {
            background: linear-gradient(135deg, var(--primary), #6366f1);
            color: white;
            border: none;
            padding: 8px 18px;
            border-radius: 50px;
            font-weight: 600;
        }
        .btn-primary-c:hover { color: white; transform: translateY(-1px); }
        .page-header {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            padding: 30px 0;
            margin-bottom: 30px;
        }
        .page-header h1 { font-weight: 700; margin: 0; }
        .card-c {
            background: white;
            border: none;
            border-radius: 14px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }
        .badge-status {
            padding: 6px 14px;
            border-radius: 30px;
            font-weight: 600;
            font-size: 0.85rem;
        }
        footer.customer-footer {
            background: #1f2937;
            color: #9ca3af;
            text-align: center;
            padding: 25px 0;
            margin-top: 60px;
        }
    </style>

    @stack('styles')
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-customer">
    <div class="container">
        <a class="navbar-brand" href="/"><i class="fas fa-building me-2"></i>Boarding<span>Pro</span></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMain"><span class="navbar-toggler-icon"></span></button>
        <div class="collapse navbar-collapse justify-content-end" id="navMain">
            <ul class="navbar-nav align-items-lg-center gap-2">
                <li class="nav-item"><a class="nav-link" href="{{ route('home') }}">Trang chủ</a></li>
                @auth
                    <li class="nav-item"><a class="nav-link" href="{{ route('booking.index') }}">Yêu cầu của tôi</a></li>
                    
                    {{-- Notifications Dropdown for Customer --}}
                    @php
                        $unreadNotifs = auth()->user()->unreadNotifications;
                        $unreadCount  = $unreadNotifs->count();
                    @endphp
                    <li class="nav-item dropdown">
                        <a class="nav-link position-relative px-2 me-2" href="#" id="notifDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fa-regular fa-bell fs-5 text-secondary"></i>
                            @if($unreadCount > 0)
                                <span class="position-absolute top-1 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem; padding: 0.25em 0.5em;">
                                    {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                                </span>
                            @endif
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end py-0 shadow border-0 rounded-4 overflow-hidden" aria-labelledby="notifDropdown" style="width: 320px; font-size: 0.9rem;">
                            <li>
                                <div class="d-flex justify-content-between align-items-center px-3 py-2 bg-light border-bottom">
                                    <span class="fw-bold">Thông báo {{ $unreadCount > 0 ? "($unreadCount)" : '' }}</span>
                                    @if($unreadCount > 0)
                                        <form action="{{ route('notifications.readAll') }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-link p-0 text-decoration-none fw-semibold" style="font-size: 0.8rem; color: var(--primary);">Đánh dấu đã đọc</button>
                                        </form>
                                    @endif
                                </div>
                            </li>
                            <li class="overflow-auto" style="max-height: 280px;">
                                @forelse(auth()->user()->notifications()->latest()->take(10)->get() as $notif)
                                    @php $data = $notif->data; @endphp
                                    <a class="dropdown-item d-flex align-items-start gap-2 py-2 px-3 border-bottom {{ $notif->read_at ? '' : 'bg-light' }}" href="{{ route('notifications.read', $notif->id) }}">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center bg-white border flex-shrink-0" style="width: 32px; height: 32px;">
                                            <i class="{{ $data['icon'] ?? 'fas fa-bell' }} {{ $data['color'] ?? 'text-primary' }}" style="font-size: 0.8rem;"></i>
                                        </div>
                                        <div style="flex: 1; min-width: 0;">
                                            <div class="fw-semibold text-wrap" style="font-size: 0.8rem; line-height: 1.2;">{{ $data['title'] ?? '' }}</div>
                                            <div class="text-muted text-wrap text-truncate" style="font-size: 0.75rem; line-height: 1.2;">{{ $data['message'] ?? '' }}</div>
                                            <div class="text-muted" style="font-size: 0.7rem; margin-top: 2px;">{{ $notif->created_at->diffForHumans() }}</div>
                                        </div>
                                        @if(!$notif->read_at)
                                            <span class="rounded-circle flex-shrink-0 bg-primary mt-1" style="width: 6px; height: 6px;"></span>
                                        @endif
                                    </a>
                                @empty
                                    <div class="text-center text-muted py-4">
                                        <i class="fa-regular fa-bell-slash mb-2 fs-4 d-block"></i>
                                        Không có thông báo nào
                                    </div>
                                @endforelse
                            </li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle me-1"></i>{{ auth()->user()->name }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="{{ route('profile.edit') }}">Hồ sơ</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">@csrf
                                    <button class="dropdown-item" type="submit">Đăng xuất</button>
                                </form>
                            </li>
                        </ul>
                    </li>
                @else
                    <li class="nav-item"><a class="nav-link" href="{{ route('login') }}">Đăng nhập</a></li>
                    @if (Route::has('register'))
                        <li class="nav-item"><a class="btn btn-primary-c" href="{{ route('register') }}"><i class="fas fa-user-plus me-1"></i>Đăng ký</a></li>
                    @endif
                @endauth
            </ul>
        </div>
    </div>
</nav>

<main class="container py-4">
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Vui lòng kiểm tra lại:</strong>
            <ul class="mb-0">
                @foreach ($errors->all() as $err)<li>{{ $err }}</li>@endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @yield('content')
</main>

<footer class="customer-footer">
    <div class="container">
        <p class="mb-0 small">&copy; {{ date('Y') }} {{ \App\Models\Setting::get('site_name', 'BoardingPro') }}. All rights reserved.</p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
