<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>@isset($header){{ $header }} — @endisset{{ \App\Models\Setting::get('site_name', config('app.name', 'BoardingPro')) }}</title>
    <meta content="width=device-width, initial-scale=1.0, shrink-to-fit=no" name="viewport" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Fonts and icons -->
    <script src="{{ asset('kaiadmin/js/plugin/webfont/webfont.min.js') }}"></script>
    <script>
        WebFont.load({
            google: { families: ["Public Sans:300,400,500,600,700"] },
            custom: {
                families: ["Font Awesome 5 Solid", "Font Awesome 5 Regular", "Font Awesome 5 Brands", "simple-line-icons"],
                urls: ["{{ asset('kaiadmin/css/fonts.min.css') }}"],
            },
            active: function () { sessionStorage.fonts = true; },
        });
    </script>

    <!-- CSS Files -->
    <link rel="stylesheet" href="{{ asset('kaiadmin/css/bootstrap.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('kaiadmin/css/plugins.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('kaiadmin/css/kaiadmin.min.css') }}" />

    @vite(['resources/css/app.css'])

    <style>
        [x-cloak] { display: none !important; }
        /* Breadcrumb active highlight */
        .nav-item.active > a { color: #1d7af3 !important; }
        /* Custom badge cho role */
        .sidebar-role-badge { font-size: .6rem; background: rgba(29,122,243,.2); color: #1d7af3; border: 1px solid rgba(29,122,243,.3); border-radius: 4px; padding: 1px 6px; margin-left: 4px; vertical-align: middle; }

        /* ── Custom Toggle Switch (độc lập, không dùng Bootstrap form-switch) ── */
        .toggle-wrap { display: inline-flex; align-items: center; gap: .55rem; cursor: pointer; user-select: none; }
        .toggle-wrap input[type="checkbox"] { position: absolute; opacity: 0; width: 0; height: 0; }
        .toggle-track {
            position: relative;
            width: 44px; height: 24px;
            background: #d1d5db;
            border-radius: 24px;
            flex-shrink: 0;
            transition: background .25s;
        }
        .toggle-track::after {
            content: '';
            position: absolute;
            top: 3px; left: 3px;
            width: 18px; height: 18px;
            background: #fff;
            border-radius: 50%;
            transition: transform .25s;
            box-shadow: 0 1px 3px rgba(0,0,0,.25);
        }
        .toggle-wrap input[type="checkbox"]:checked ~ .toggle-track { background: #1d7af3; }
        .toggle-wrap input[type="checkbox"]:checked ~ .toggle-track::after { transform: translateX(20px); }
        .toggle-text { font-size: .9rem; line-height: 1; }
    </style>
</head>
<body>
    <div class="wrapper">

        {{-- ══════════════ SIDEBAR ══════════════ --}}
        <div class="sidebar" data-background-color="dark">
            <div class="sidebar-logo">
                <div class="logo-header" data-background-color="dark">
                    <a href="{{ route('home') }}" class="logo">
                        <span style="font-size:1.15rem;font-weight:700;color:#fff;letter-spacing:-.3px">
                            {{ \App\Models\Setting::get('site_name', 'BoardingPro') }}
                        </span>
                    </a>
                    <div class="nav-toggle">
                        <button class="btn btn-toggle toggle-sidebar"><i class="gg-menu-right"></i></button>
                        <button class="btn btn-toggle sidenav-toggler"><i class="gg-menu-left"></i></button>
                    </div>
                    <button class="topbar-toggler more"><i class="gg-more-vertical-alt"></i></button>
                </div>
            </div>

            <div class="sidebar-wrapper scrollbar scrollbar-inner">
                <div class="sidebar-content">
                    @if(Auth::user()->hasRole('admin'))
                    <ul class="nav nav-secondary">
                        {{-- TỔNG QUAN --}}
                        <li class="nav-section">
                            <span class="sidebar-mini-icon"><i class="fa fa-ellipsis-h"></i></span>
                            <h4 class="text-section">Tổng quan</h4>
                        </li>
                        <li class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                            <a href="{{ route('admin.dashboard') }}">
                                <i class="fas fa-home"></i>
                                <p>Dashboard</p>
                            </a>
                        </li>

                        {{-- QUẢN LÝ --}}
                        <li class="nav-section">
                            <span class="sidebar-mini-icon"><i class="fa fa-ellipsis-h"></i></span>
                            <h4 class="text-section">Quản lý</h4>
                        </li>
                        <li class="nav-item {{ request()->routeIs('admin.houses.*') ? 'active' : '' }}">
                            <a href="{{ route('admin.houses.index') }}">
                                <i class="fas fa-building"></i>
                                <p>Khu trọ & Phòng</p>
                            </a>
                        </li>
                        <li class="nav-item {{ request()->routeIs('admin.tenants.*') ? 'active' : '' }}">
                            <a href="{{ route('admin.tenants.index') }}">
                                <i class="fas fa-users"></i>
                                <p>Khách thuê</p>
                            </a>
                        </li>
                        <li class="nav-item {{ request()->routeIs('admin.contracts.*') ? 'active' : '' }}">
                            <a href="{{ route('admin.contracts.index') }}">
                                <i class="fas fa-file-contract"></i>
                                <p>Hợp đồng thuê</p>
                            </a>
                        </li>

                        {{-- TÀI CHÍNH --}}
                        <li class="nav-section">
                            <span class="sidebar-mini-icon"><i class="fa fa-ellipsis-h"></i></span>
                            <h4 class="text-section">Tài chính</h4>
                        </li>
                        <li class="nav-item {{ request()->routeIs('admin.meter-readings.*') ? 'active' : '' }}">
                            <a href="{{ route('admin.meter-readings.index') }}">
                                <i class="fas fa-bolt"></i>
                                <p>Chỉ số Điện/Nước</p>
                            </a>
                        </li>
                        <li class="nav-item {{ request()->routeIs('admin.invoices.*') ? 'active' : '' }}">
                            <a href="{{ route('admin.invoices.index') }}">
                                <i class="fas fa-file-invoice-dollar"></i>
                                <p>Hóa đơn & Thu tiền</p>
                            </a>
                        </li>

                        {{-- VẬN HÀNH --}}
                        <li class="nav-section">
                            <span class="sidebar-mini-icon"><i class="fa fa-ellipsis-h"></i></span>
                            <h4 class="text-section">Vận hành</h4>
                        </li>
                        <li class="nav-item {{ request()->routeIs('admin.maintenance-tickets.*') ? 'active' : '' }}">
                            <a href="{{ route('admin.maintenance-tickets.index') }}">
                                <i class="fas fa-tools"></i>
                                <p>Báo cáo sự cố</p>
                            </a>
                        </li>
                        <li class="nav-item {{ request()->routeIs('admin.announcements.*') ? 'active' : '' }}">
                            <a href="{{ route('admin.announcements.index') }}">
                                <i class="fas fa-bell"></i>
                                <p>Thông báo</p>
                            </a>
                        </li>

                        {{-- HỆ THỐNG --}}
                        <li class="nav-section">
                            <span class="sidebar-mini-icon"><i class="fa fa-ellipsis-h"></i></span>
                            <h4 class="text-section">Hệ thống</h4>
                        </li>
                        <li class="nav-item {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                            <a href="{{ route('admin.settings.index') }}">
                                <i class="fas fa-cog"></i>
                                <p>Cài đặt</p>
                            </a>
                        </li>
                    </ul>
                    @else
                    {{-- TENANT NAV --}}
                    <ul class="nav nav-secondary">
                        <li class="nav-section">
                            <h4 class="text-section">Cổng thông tin</h4>
                        </li>
                        <li class="nav-item {{ request()->routeIs('tenant.dashboard') ? 'active' : '' }}">
                            <a href="{{ route('tenant.dashboard') }}">
                                <i class="fas fa-home"></i><p>Dashboard</p>
                            </a>
                        </li>
                        <li class="nav-item {{ request()->routeIs('tenant.invoices.*') ? 'active' : '' }}">
                            <a href="{{ route('tenant.invoices.index') }}">
                                <i class="fas fa-file-invoice-dollar"></i><p>Hóa đơn</p>
                            </a>
                        </li>
                        <li class="nav-item {{ request()->routeIs('tenant.contracts.*') ? 'active' : '' }}">
                            <a href="{{ route('tenant.contracts.index') }}">
                                <i class="fas fa-file-contract"></i><p>Hợp đồng</p>
                            </a>
                        </li>
                        <li class="nav-item {{ request()->routeIs('tenant.maintenance-tickets.*') ? 'active' : '' }}">
                            <a href="{{ route('tenant.maintenance-tickets.index') }}">
                                <i class="fas fa-tools"></i><p>Báo cáo sự cố</p>
                            </a>
                        </li>
                    </ul>
                    @endif
                </div>
            </div>
        </div>
        {{-- ══════════════ END SIDEBAR ══════════════ --}}

        <div class="main-panel">
            {{-- ══════════════ HEADER ══════════════ --}}
            <div class="main-header">
                <div class="main-header-logo">
                    <div class="logo-header" data-background-color="dark">
                        <a href="{{ route('home') }}" class="logo">
                            <span style="font-size:1.1rem;font-weight:700;color:#fff">{{ \App\Models\Setting::get('site_name', 'BoardingPro') }}</span>
                        </a>
                        <div class="nav-toggle">
                            <button class="btn btn-toggle toggle-sidebar"><i class="gg-menu-right"></i></button>
                            <button class="btn btn-toggle sidenav-toggler"><i class="gg-menu-left"></i></button>
                        </div>
                        <button class="topbar-toggler more"><i class="gg-more-vertical-alt"></i></button>
                    </div>
                </div>

                <nav class="navbar navbar-header navbar-header-transparent navbar-expand-lg border-bottom">
                    <div class="container-fluid">
                        {{-- Page title on left --}}
                        <div class="d-flex align-items-center gap-3">
                            @isset($header)
                                <span class="fw-semibold text-dark" style="font-size:.95rem">{{ $header }}</span>
                            @endisset
                            @isset($actions)
                                {!! $actions !!}
                            @endisset
                        </div>

                        <ul class="navbar-nav topbar-nav ms-md-auto align-items-center">
                            {{-- Notifications --}}
                            @php
                                $unreadNotifs = auth()->user()->unreadNotifications;
                                $unreadCount  = $unreadNotifs->count();
                            @endphp
                            <li class="nav-item topbar-icon dropdown hidden-caret">
                                <a class="nav-link dropdown-toggle" href="#" id="notifDropdown" role="button"
                                   data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fa fa-bell"></i>
                                    @if($unreadCount > 0)
                                        <span class="notification">{{ $unreadCount > 9 ? '9+' : $unreadCount }}</span>
                                    @endif
                                </a>
                                <ul class="dropdown-menu notif-box animated fadeIn" aria-labelledby="notifDropdown" style="min-width:320px;max-width:380px">
                                    <li>
                                        <div class="dropdown-title d-flex justify-content-between align-items-center px-3 py-2">
                                            <span>Thông báo {{ $unreadCount > 0 ? "($unreadCount chưa đọc)" : '' }}</span>
                                            @if($unreadCount > 0)
                                                <form action="{{ route('notifications.readAll') }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-xs btn-link p-0 text-primary" style="font-size:.78rem">Đánh dấu tất cả đã đọc</button>
                                                </form>
                                            @endif
                                        </div>
                                    </li>
                                    <li>
                                        <div class="notif-scroll scrollbar-outer" style="max-height:300px;overflow-y:auto">
                                            @forelse(auth()->user()->notifications()->latest()->take(10)->get() as $notif)
                                                @php $data = $notif->data; @endphp
                                                <a class="dropdown-item notif-item d-flex align-items-start gap-2 py-2 {{ $notif->read_at ? '' : 'bg-light' }}"
                                                   href="{{ route('notifications.read', $notif->id) }}">
                                                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                                                         style="width:36px;height:36px;background:#f0f4ff;margin-top:2px">
                                                        <i class="{{ $data['icon'] ?? 'fas fa-bell' }} {{ $data['color'] ?? 'text-primary' }}" style="font-size:.85rem"></i>
                                                    </div>
                                                    <div style="flex:1;min-width:0">
                                                        <div class="fw-semibold" style="font-size:.82rem;line-height:1.3;white-space:normal">{{ $data['title'] ?? '' }}</div>
                                                        <div class="text-muted" style="font-size:.75rem;white-space:normal;line-height:1.3">{{ $data['message'] ?? '' }}</div>
                                                        <div class="text-muted" style="font-size:.7rem;margin-top:2px">{{ $notif->created_at->diffForHumans() }}</div>
                                                    </div>
                                                    @if(!$notif->read_at)
                                                        <span class="rounded-circle flex-shrink-0" style="width:8px;height:8px;background:#1d7af3;margin-top:6px"></span>
                                                    @endif
                                                </a>
                                            @empty
                                                <div class="text-center text-muted py-4" style="font-size:.85rem">
                                                    <i class="fas fa-bell-slash mb-2 d-block" style="font-size:1.5rem"></i>
                                                    Không có thông báo nào
                                                </div>
                                            @endforelse
                                        </div>
                                    </li>
                                </ul>
                            </li>

                            {{-- User Dropdown --}}
                            <li class="nav-item topbar-user dropdown hidden-caret">
                                <a class="dropdown-toggle profile-pic" data-bs-toggle="dropdown" href="#" aria-expanded="false">
                                    <div class="avatar-sm">
                                        <span class="avatar-img rounded-circle d-flex align-items-center justify-content-center text-white fw-bold"
                                              style="width:35px;height:35px;background:linear-gradient(135deg,#1d7af3,#6c48d2);font-size:.85rem">
                                            {{ substr(Auth::user()->name, 0, 1) }}
                                        </span>
                                    </div>
                                    <span class="profile-username">
                                        <span class="op-7">Hi,</span>
                                        <span class="fw-bold">{{ Auth::user()->name }}</span>
                                    </span>
                                </a>
                                <ul class="dropdown-menu dropdown-user animated fadeIn">
                                    <div class="dropdown-user-scroll scrollbar-outer">
                                        <li>
                                            <div class="user-box">
                                                <div class="u-text">
                                                    <h4>{{ Auth::user()->name }}</h4>
                                                    <p class="text-muted">{{ Auth::user()->email }}</p>
                                                    <a href="{{ route('profile.edit') }}" class="btn btn-xs btn-secondary btn-sm">Hồ sơ</a>
                                                </div>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="dropdown-divider"></div>
                                            <a class="dropdown-item" href="{{ route('profile.edit') }}">
                                                <i class="fas fa-user me-2"></i>Hồ sơ cá nhân
                                            </a>
                                            <div class="dropdown-divider"></div>
                                            <form method="POST" action="{{ route('logout') }}">
                                                @csrf
                                                <button type="submit" class="dropdown-item text-danger">
                                                    <i class="fas fa-sign-out-alt me-2"></i>Đăng xuất
                                                </button>
                                            </form>
                                        </li>
                                    </div>
                                </ul>
                            </li>
                        </ul>
                    </div>
                </nav>
            </div>
            {{-- ══════════════ END HEADER ══════════════ --}}

            {{-- ══════════════ CONTENT ══════════════ --}}
            <div class="container">
                <div class="page-inner">
                    {{-- Page Header --}}
                    <div class="page-header">
                        <h4 class="page-title">@isset($header){{ $header }}@else Dashboard @endisset</h4>
                        <ul class="breadcrumbs">
                            <li class="nav-home">
                                <a href="{{ route('home') }}"><i class="icon-home"></i></a>
                            </li>
                            <li class="separator"><i class="icon-arrow-right"></i></li>
                            <li class="nav-item">
                                <a href="#">@isset($header){{ $header }}@else Dashboard @endisset</a>
                            </li>
                        </ul>
                    </div>

                    {{-- Slot content --}}
                    {{ $slot }}
                </div>
            </div>
            {{-- ══════════════ END CONTENT ══════════════ --}}

            <footer class="footer">
                <div class="container-fluid d-flex justify-content-between">
                    <div class="copyright">
                        &copy; {{ date('Y') }} <strong>{{ \App\Models\Setting::get('site_name', 'BoardingPro') }}</strong> — {{ \App\Models\Setting::get('site_tagline', 'Hệ thống Quản lý Phòng trọ') }}
                    </div>
                    <div class="text-muted" style="font-size:.8rem">
                        Đồ án tốt nghiệp
                    </div>
                </div>
            </footer>
        </div>
    </div>

    {{-- ══ Core JS ══ --}}
    <script src="{{ asset('kaiadmin/js/core/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ asset('kaiadmin/js/core/popper.min.js') }}"></script>
    <script src="{{ asset('kaiadmin/js/core/bootstrap.min.js') }}"></script>
    <script src="{{ asset('kaiadmin/js/plugin/jquery-scrollbar/jquery.scrollbar.min.js') }}"></script>
    <script src="{{ asset('kaiadmin/js/plugin/chart.js/chart.min.js') }}"></script>
    <script src="{{ asset('kaiadmin/js/plugin/jquery.sparkline/jquery.sparkline.min.js') }}"></script>
    <script src="{{ asset('kaiadmin/js/plugin/chart-circle/circles.min.js') }}"></script>
    <script src="{{ asset('kaiadmin/js/plugin/datatables/datatables.min.js') }}"></script>
    <script src="{{ asset('kaiadmin/js/plugin/bootstrap-notify/bootstrap-notify.min.js') }}"></script>
    <script src="{{ asset('kaiadmin/js/kaiadmin.min.js') }}"></script>

    @vite(['resources/js/app.js'])

    {{-- Page-level scripts --}}
    @isset($scripts)
        {!! $scripts !!}
    @endisset
</body>
</html>
