<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>@isset($header){{ $header }} | @endisset{{ \App\Models\Setting::get('site_name', 'Trọ Nhà Mình') }}</title>
    <meta content="width=device-width, initial-scale=1.0, shrink-to-fit=no" name="viewport" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#ffffff">
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/brand-home.svg') }}">

    <!-- Fonts and icons -->
    <script src="{{ asset('kaiadmin/js/plugin/webfont/webfont.min.js') }}"></script>
    <script>
        WebFont.load({
            google: { families: ["Be Vietnam Pro:400,500,600,700"] },
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

        /* PayOS Popup Overlay Styling */
        #payos-embed iframe {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            width: 100vw !important;
            height: 100vh !important;
            border: none !important;
            z-index: 99999999 !important;
        }

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
        .toggle-wrap input[type="checkbox"]:checked ~ .toggle-track { background: #b43b32; }
        .toggle-wrap input[type="checkbox"]:focus-visible ~ .toggle-track { outline: 3px solid #3176a9; outline-offset: 3px; }
        .toggle-wrap input[type="checkbox"]:checked ~ .toggle-track::after { transform: translateX(20px); }
        .toggle-text { font-size: .9rem; line-height: 1; }

    </style>
</head>
<body>
    <a class="visually-hidden-focusable skip-content" href="#app-main">Đến nội dung chính</a>
    <div class="wrapper">

        {{-- ══════════════ SIDEBAR ══════════════ --}}
        <div class="sidebar" id="app-sidebar" role="navigation" aria-label="Điều hướng chính" data-background-color="white">
            <div class="sidebar-logo">
                <div class="logo-header" data-background-color="white">
                    <a href="{{ route('home') }}" class="logo">
                        <span class="brand-mark"><i class="fas fa-home" aria-hidden="true"></i></span>
                        <span class="brand-name" translate="no">{{ \App\Models\Setting::get('site_name', 'Trọ Nhà Mình') }}</span>
                    </a>
                    <div class="nav-toggle">
                        <button class="btn btn-toggle toggle-sidebar" aria-label="Thu gọn thanh bên" title="Thu gọn thanh bên"><i class="gg-menu-right" aria-hidden="true"></i></button>
                        <button class="btn btn-toggle sidenav-toggler" aria-label="Mở thanh bên" title="Mở thanh bên"><i class="gg-menu-left" aria-hidden="true"></i></button>
                    </div>
                    <button class="topbar-toggler more" aria-label="Mở menu tài khoản"><i class="gg-more-vertical-alt" aria-hidden="true"></i></button>
                </div>
            </div>

            <div class="sidebar-wrapper scrollbar scrollbar-inner">
                <div class="sidebar-content">
                    <div class="ops-nav-heading"><strong>Menu {{ Auth::user()->hasRole('admin') ? 'quản lý' : 'khách thuê' }}</strong><button type="button" class="btn btn-action" data-close-nav aria-label="Đóng menu" title="Đóng menu"><i class="fas fa-times" aria-hidden="true"></i></button></div>
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
                                <p>Tổng quan</p>
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
                                <p>Chi nhánh</p>
                            </a>
                        </li>
                        <li class="nav-item {{ request()->routeIs('admin.rooms.*') ? 'active' : '' }}">
                            <a href="{{ route('admin.rooms.index') }}">
                                <i class="fas fa-door-open"></i>
                                <p>Phòng trọ</p>
                            </a>
                        </li>
                        <li class="nav-item {{ request()->routeIs('admin.tenants.*') ? 'active' : '' }}">
                            <a href="{{ route('admin.tenants.index') }}">
                                <i class="fas fa-users"></i>
                                <p>Khách thuê</p>
                            </a>
                        </li>
                        <li class="nav-item {{ request()->routeIs('admin.contracts.*', 'admin.settlements.*') ? 'active' : '' }}">
                            <a href="{{ route('admin.contracts.index') }}">
                                <i class="fas fa-file-contract"></i>
                                <p>Hợp đồng thuê</p>
                            </a>
                        </li>
                        <li class="nav-item {{ request()->routeIs('admin.booking-requests.*') ? 'active' : '' }}">
                            <a href="{{ route('admin.booking-requests.index') }}">
                                <i class="fas fa-clipboard-list"></i>
                                <p>Yêu cầu đặt phòng</p>
                            </a>
                        </li>

                        <li class="nav-item ops-nav-group">
                            <details @if(request()->routeIs('admin.invoices.*', 'admin.billing.*', 'admin.payment-reviews.*', 'admin.expenses.*', 'admin.reports.*')) open @endif>
                                <summary aria-label="Tài chính" title="Tài chính"><i class="fas fa-wallet" aria-hidden="true"></i><span class="ops-nav-label">Tài chính</span><i class="fas fa-chevron-down ops-nav-chevron" aria-hidden="true"></i></summary>
                                <ul class="ops-nav-links">
                                    @foreach([
                                        ['admin.invoices.index', 'fas fa-file-invoice-dollar', 'Hóa đơn & thu tiền'],
                                        ['admin.billing.index', 'fas fa-calculator', 'Lập hóa đơn tháng'],
                                        ['admin.payment-reviews.index', 'fas fa-balance-scale', 'Đối soát & hoàn tiền'],
                                        ['admin.expenses.index', 'fas fa-receipt', 'Chi phí'],
                                        ['admin.reports.finance', 'fas fa-chart-bar', 'Báo cáo thu chi'],
                                    ] as [$navRoute, $navIcon, $navLabel])
                                    @php
                                        $navActive = request()->routeIs(substr($navRoute, 0, strrpos($navRoute, '.')).'.*');
                                    @endphp
                                    <li><a href="{{ route($navRoute) }}" @if($navActive) aria-current="page" @endif><i class="{{ $navIcon }}" aria-hidden="true"></i><span>{{ $navLabel }}</span></a></li>
                                    @endforeach
                                </ul>
                            </details>
                        </li>

                        {{-- VẬN HÀNH --}}
                        <li class="nav-section">
                            <span class="sidebar-mini-icon"><i class="fa fa-ellipsis-h"></i></span>
                            <h4 class="text-section">Vận hành</h4>
                        </li>
                        <li class="nav-item {{ request()->routeIs('admin.reminders.*') ? 'active' : '' }}"><a href="{{ route('admin.reminders.index') }}"><i class="fas fa-clock" aria-hidden="true"></i><p>Nhắc hạn</p></a></li>
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
                                <i class="fas fa-home"></i><p>Phòng của tôi</p>
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
        <button type="button" class="ops-nav-backdrop" data-close-nav aria-label="Đóng menu" tabindex="-1" hidden></button>

        <div class="main-panel">
            {{-- ══════════════ HEADER ══════════════ --}}
            <div class="main-header">
                <div class="main-header-logo">
                    <div class="logo-header" data-background-color="white">
                        <a href="{{ route('home') }}" class="logo">
                            <span class="brand-mark"><i class="fas fa-home" aria-hidden="true"></i></span>
                            <span class="brand-name" translate="no">{{ \App\Models\Setting::get('site_name', 'Trọ Nhà Mình') }}</span>
                        </a>
                        <div class="nav-toggle">
                            <button class="btn btn-toggle toggle-sidebar" aria-label="Thu gọn thanh bên"><i class="gg-menu-right" aria-hidden="true"></i></button>
                            <button class="btn btn-toggle sidenav-toggler" aria-label="Mở thanh bên"><i class="gg-menu-left" aria-hidden="true"></i></button>
                        </div>
                        <button class="topbar-toggler more" aria-label="Mở menu tài khoản"><i class="gg-more-vertical-alt" aria-hidden="true"></i></button>
                    </div>
                </div>

                <nav class="navbar navbar-header navbar-header-transparent navbar-expand-lg border-bottom">
                    <div class="container-fluid">
                        {{-- Page title on left --}}
                        <div class="workspace-label"><span class="workspace-dot" aria-hidden="true"></span>{{ Auth::user()->hasRole('admin') ? 'Quản lý nhà trọ' : 'Dành cho khách thuê' }}</div>

                        <ul class="navbar-nav topbar-nav ms-md-auto align-items-center">
                            {{-- Notifications --}}
                            @php
                                $unreadCount = auth()->user()->unreadNotifications()->count();
                            @endphp
                            <li class="nav-item topbar-icon dropdown hidden-caret">
                                <a class="nav-link dropdown-toggle" href="#" id="notifDropdown" role="button"
                                   data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" aria-label="Thông báo" title="Thông báo">
                                    <i class="fa fa-bell" aria-hidden="true"></i>
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
                                <a class="dropdown-toggle profile-pic" data-bs-toggle="dropdown" href="#" aria-expanded="false" aria-label="Tài khoản {{ Auth::user()->name }}">
                                    <div class="avatar-sm">
                                        <span class="avatar-img rounded-circle d-flex align-items-center justify-content-center text-white fw-bold"
                                              style="width:35px;height:35px;background:#f7eae8;color:#94342c!important;font-size:.85rem">
                                            {{ mb_substr(Auth::user()->name, 0, 1) }}
                                        </span>
                                    </div>
                                    <span class="profile-username">
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
            <main class="container app-content-container" id="app-main">
                <div class="page-inner">
                    {{-- Page Header --}}
                    <div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-3">
                        <div>
                            <h1 class="page-title">@isset($header){{ $header }}@else Tổng quan @endisset</h1>
                        </div>
                        @isset($actions)
                            <div class="page-header-actions">{!! $actions !!}</div>
                        @endisset
                    </div>

                    @if(session('error'))
                        <div class="alert alert-danger" role="alert">{{ session('error') }}</div>
                    @endif
                    @if($errors->has('history'))
                        <div class="alert alert-danger" role="alert">{{ $errors->first('history') }}</div>
                    @endif
                    @if(session('success'))
                        <div class="alert alert-success" role="status">{{ session('success') }}</div>
                    @endif
                    {{-- Slot content --}}
                    {{ $slot }}
                </div>
            </main>
            {{-- ══════════════ END CONTENT ══════════════ --}}

            <footer class="footer">
                <div class="container-fluid d-flex justify-content-between">
                    <div class="copyright">
                        &copy; {{ date('Y') }} <strong>{{ \App\Models\Setting::get('site_name', 'Trọ Nhà Mình') }}</strong>
                    </div>
                    <div class="text-muted" style="font-size:.8rem">
                        Vinh, Nghệ An
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

    <div id="payos-embed"></div>

    <script src="https://cdn.payos.vn/payos-checkout/v1/stable/payos-initialize.js"></script>

    @vite(['resources/js/app.js'])

    {{-- Page-level scripts --}}
    @isset($scripts)
        {!! $scripts !!}
    @endisset
</body>
</html>
