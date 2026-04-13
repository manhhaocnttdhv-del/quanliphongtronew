<x-app-layout>
    <x-slot name="header">Cài đặt hệ thống</x-slot>

    @php $tab = request('tab', 'general'); @endphp

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    {{-- ── Tab Navigation ── --}}
    <div class="nav-tabs-wrapper mb-4">
        <ul class="nav nav-pills nav-settings" id="settingsTabs">
            <li class="nav-item">
                <a href="?tab=general"
                   class="nav-link {{ $tab === 'general' ? 'active' : '' }}">
                    <i class="fas fa-globe me-2"></i>Thông tin trang
                </a>
            </li>
            <li class="nav-item">
                <a href="?tab=finance"
                   class="nav-link {{ $tab === 'finance' ? 'active' : '' }}">
                    <i class="fas fa-coins me-2"></i>Tài chính
                </a>
            </li>
            <li class="nav-item">
                <a href="?tab=notification"
                   class="nav-link {{ $tab === 'notification' ? 'active' : '' }}">
                    <i class="fas fa-bell me-2"></i>Thông báo
                </a>
            </li>
            <li class="nav-item">
                <a href="?tab=account"
                   class="nav-link {{ $tab === 'account' ? 'active' : '' }}">
                    <i class="fas fa-user-shield me-2"></i>Tài khoản
                </a>
            </li>
        </ul>
    </div>

    {{-- ══════════════════════════════════════
         TAB 1: THÔNG TIN TRANG
    ══════════════════════════════════════ --}}
    @if($tab === 'general')
    <form action="{{ route('admin.settings.update') }}" method="POST">
        @csrf @method('PUT')
        <input type="hidden" name="tab" value="general">

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card card-round">
                    <div class="card-header">
                        <div class="card-title">
                            <i class="fas fa-globe text-primary me-2"></i>Thông tin website
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Tên hệ thống <span class="text-danger">*</span></label>
                                <input type="text" name="site_name" class="form-control @error('site_name') is-invalid @enderror"
                                    value="{{ old('site_name', $general['site_name'] ?? 'BoardingPro') }}" required>
                                @error('site_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Slogan / Mô tả ngắn</label>
                                <input type="text" name="site_tagline" class="form-control"
                                    value="{{ old('site_tagline', $general['site_tagline'] ?? '') }}"
                                    placeholder="VD: Hệ thống quản lý phòng trọ thông minh">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card card-round mt-4">
                    <div class="card-header">
                        <div class="card-title">
                            <i class="fas fa-address-card text-info me-2"></i>Thông tin liên hệ
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Email liên hệ</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-envelope text-muted"></i></span>
                                    <input type="email" name="contact_email" class="form-control @error('contact_email') is-invalid @enderror"
                                        value="{{ old('contact_email', $general['contact_email'] ?? '') }}"
                                        placeholder="admin@example.com">
                                    @error('contact_email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Số điện thoại</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-phone text-muted"></i></span>
                                    <input type="text" name="contact_phone" class="form-control"
                                        value="{{ old('contact_phone', $general['contact_phone'] ?? '') }}"
                                        placeholder="0901 234 567">
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Địa chỉ</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-map-marker-alt text-muted"></i></span>
                                    <input type="text" name="contact_address" class="form-control"
                                        value="{{ old('contact_address', $general['contact_address'] ?? '') }}"
                                        placeholder="123 Đường ABC, Quận 1, TP.HCM">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card card-round h-auto">
                    <div class="card-header">
                        <div class="card-title"><i class="fas fa-info-circle text-warning me-2"></i>Hướng dẫn</div>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-2">Thông tin này hiển thị trên toàn bộ giao diện và các tài liệu xuất ra (hóa đơn, hợp đồng PDF…).</p>
                        <ul class="text-muted small ps-3">
                            <li class="mb-1"><strong>Tên hệ thống</strong>: hiển thị ở góc trên sidebar và tiêu đề trang.</li>
                            <li class="mb-1"><strong>Slogan</strong>: dòng phụ bên dưới tên hệ thống.</li>
                            <li class="mb-1"><strong>Liên hệ</strong>: in trên hóa đơn và hợp đồng.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-3">
            <button type="submit" class="btn btn-primary btn-round">
                <i class="fas fa-save me-1"></i>Lưu thông tin trang
            </button>
        </div>
    </form>
    @endif

    {{-- ══════════════════════════════════════
         TAB 2: TÀI CHÍNH
    ══════════════════════════════════════ --}}
    @if($tab === 'finance')
    <form action="{{ route('admin.settings.update') }}" method="POST">
        @csrf @method('PUT')
        <input type="hidden" name="tab" value="finance">

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card card-round">
                    <div class="card-header">
                        <div class="card-title">
                            <i class="fas fa-coins text-warning me-2"></i>Cài đặt tài chính
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Đơn vị tiền tệ</label>
                                <select name="currency" class="form-select">
                                    <option value="VND" {{ ($finance['currency'] ?? 'VND') === 'VND' ? 'selected' : '' }}>VND — Đồng Việt Nam</option>
                                    <option value="USD" {{ ($finance['currency'] ?? '') === 'USD' ? 'selected' : '' }}>USD — Đô la Mỹ</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Số ngày hạn thanh toán</label>
                                <div class="input-group">
                                    <input type="number" name="invoice_due_days" class="form-control @error('invoice_due_days') is-invalid @enderror"
                                        value="{{ old('invoice_due_days', $finance['invoice_due_days'] ?? 10) }}" min="1" max="60">
                                    <span class="input-group-text">ngày</span>
                                    @error('invoice_due_days')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="form-text">Tính từ ngày xuất hóa đơn.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Thuế VAT (%)</label>
                                <div class="input-group">
                                    <input type="number" name="vat_rate" class="form-control @error('vat_rate') is-invalid @enderror"
                                        value="{{ old('vat_rate', $finance['vat_rate'] ?? 0) }}" min="0" max="100" step="0.5">
                                    <span class="input-group-text">%</span>
                                    @error('vat_rate')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="form-text">Đặt 0 nếu không áp dụng VAT.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Phí trả trễ / tháng (%)</label>
                                <div class="input-group">
                                    <input type="number" name="late_fee_rate" class="form-control @error('late_fee_rate') is-invalid @enderror"
                                        value="{{ old('late_fee_rate', $finance['late_fee_rate'] ?? 2) }}" min="0" max="100" step="0.5">
                                    <span class="input-group-text">%</span>
                                    @error('late_fee_rate')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="form-text">Phần trăm tính trên tổng hóa đơn khi quá hạn.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card card-round" style="border-left: 4px solid #f5a623;">
                    <div class="card-body">
                        <h6 class="fw-bold text-warning mb-3"><i class="fas fa-calculator me-2"></i>Công thức tính</h6>
                        <div class="p-3 rounded" style="background:#fffbf0;font-size:.85rem">
                            <p class="mb-1"><strong>Tổng hóa đơn</strong> = Tiền phòng + Điện + Nước + DV khác</p>
                            <p class="mb-1"><strong>VAT</strong> = Tổng × {{ $finance['vat_rate'] ?? 0 }}%</p>
                            <p class="mb-0"><strong>Phí trễ</strong> = Tổng × {{ $finance['late_fee_rate'] ?? 2 }}% / tháng</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-3">
            <button type="submit" class="btn btn-warning btn-round text-white">
                <i class="fas fa-save me-1"></i>Lưu cài đặt tài chính
            </button>
        </div>
    </form>
    @endif

    {{-- ══════════════════════════════════════
         TAB 3: THÔNG BÁO
    ══════════════════════════════════════ --}}
    @if($tab === 'notification')
    <form action="{{ route('admin.settings.update') }}" method="POST">
        @csrf @method('PUT')
        <input type="hidden" name="tab" value="notification">

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card card-round">
                    <div class="card-header">
                        <div class="card-title">
                            <i class="fas fa-bell text-danger me-2"></i>Cài đặt thông báo hệ thống
                        </div>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-4">Bật/tắt các loại thông báo tự động trong hệ thống.</p>

                        @php
                            $notifItems = [
                                'notify_new_invoice'  => ['icon' => 'fa-file-invoice-dollar', 'color' => 'text-primary',  'title' => 'Hóa đơn mới',         'desc' => 'Gửi thông báo khi hóa đơn tháng được tạo.'],
                                'notify_overdue'      => ['icon' => 'fa-exclamation-triangle','color' => 'text-danger',   'title' => 'Quá hạn thanh toán',   'desc' => 'Nhắc nhở khi hóa đơn quá ngày đến hạn.'],
                                'notify_maintenance'  => ['icon' => 'fa-tools',               'color' => 'text-warning',  'title' => 'Báo cáo sự cố mới',    'desc' => 'Thông báo ngay khi khách thuê gửi yêu cầu sửa chữa.'],
                            ];
                        @endphp

                        <div class="d-flex flex-column gap-3">
                            @foreach($notifItems as $key => $item)
                            <div class="d-flex align-items-center justify-content-between p-3 rounded"
                                 style="border: 1px solid #e8ecef; background: #f8f9fa;">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center"
                                         style="width:44px;height:44px;background:#fff;box-shadow:0 2px 8px rgba(0,0,0,.08)">
                                        <i class="fas {{ $item['icon'] }} {{ $item['color'] }}"></i>
                                    </div>
                                    <div>
                                        <div class="fw-semibold">{{ $item['title'] }}</div>
                                        <div class="text-muted" style="font-size:.82rem">{{ $item['desc'] }}</div>
                                    </div>
                                </div>
                                <label class="toggle-wrap ms-3">
                                    <input type="checkbox" name="{{ $key }}"
                                        {{ ($notification[$key] ?? '1') === '1' ? 'checked' : '' }}>
                                    <span class="toggle-track"></span>
                                </label>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card card-round" style="border-left:4px solid #ea4335;">
                    <div class="card-body">
                        <h6 class="fw-bold mb-2"><i class="fas fa-info-circle text-danger me-2"></i>Lưu ý</h6>
                        <p class="text-muted small mb-0">
                            Thông báo hiện tại hiển thị trong hệ thống (in-app). Tích hợp email/SMS có thể bổ sung trong phiên bản nâng cao.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-3">
            <button type="submit" class="btn btn-danger btn-round">
                <i class="fas fa-save me-1"></i>Lưu cài đặt thông báo
            </button>
        </div>
    </form>
    @endif

    {{-- ══════════════════════════════════════
         TAB 4: TÀI KHOẢN ADMIN
    ══════════════════════════════════════ --}}
    @if($tab === 'account')
    <form action="{{ route('admin.settings.update') }}" method="POST">
        @csrf @method('PUT')
        <input type="hidden" name="tab" value="account">

        <div class="row g-4">
            {{-- Avatar & info --}}
            <div class="col-lg-4">
                <div class="card card-round text-center">
                    <div class="card-body py-4">
                        <div class="mx-auto mb-3 rounded-circle d-flex align-items-center justify-content-center text-white fw-bold"
                             style="width:80px;height:80px;font-size:2rem;background:linear-gradient(135deg,#1d7af3,#6c48d2)">
                            {{ substr(auth()->user()->name, 0, 1) }}
                        </div>
                        <h5 class="mb-0 fw-bold">{{ auth()->user()->name }}</h5>
                        <p class="text-muted small mb-2">{{ auth()->user()->email }}</p>
                        <span class="badge" style="background:rgba(29,122,243,.15);color:#1d7af3">Quản trị viên</span>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card card-round">
                    <div class="card-header">
                        <div class="card-title">
                            <i class="fas fa-user-edit text-primary me-2"></i>Thông tin tài khoản
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Họ và tên <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                    value="{{ old('name', auth()->user()->name) }}" required>
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                    value="{{ old('email', auth()->user()->email) }}" required>
                                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card card-round mt-4">
                    <div class="card-header">
                        <div class="card-title">
                            <i class="fas fa-lock text-warning me-2"></i>Đổi mật khẩu
                            <span class="text-muted fw-normal" style="font-size:.82rem">(để trống nếu không đổi)</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Mật khẩu mới</label>
                                <div class="input-group">
                                    <input type="password" name="password" id="newPassword"
                                        class="form-control @error('password') is-invalid @enderror"
                                        placeholder="Tối thiểu 8 ký tự">
                                    <button type="button" class="btn btn-outline-secondary" onclick="togglePwd('newPassword', this)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Xác nhận mật khẩu</label>
                                <div class="input-group">
                                    <input type="password" name="password_confirmation" id="confirmPassword"
                                        class="form-control @error('password_confirmation') is-invalid @enderror"
                                        placeholder="Nhập lại mật khẩu mới">
                                    <button type="button" class="btn btn-outline-secondary" onclick="togglePwd('confirmPassword', this)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    @error('password_confirmation')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-3">
                    <button type="submit" class="btn btn-primary btn-round">
                        <i class="fas fa-save me-1"></i>Lưu tài khoản
                    </button>
                </div>
            </div>
        </div>
    </form>
    @endif

    <x-slot name="scripts">
    <style>
        .nav-settings {
            background: #fff;
            border-radius: 12px;
            padding: 6px;
            box-shadow: 0 2px 12px rgba(0,0,0,.06);
            gap: 4px;
            flex-wrap: wrap;
        }
        .nav-settings .nav-link {
            color: #6c757d;
            border-radius: 8px;
            padding: 8px 18px;
            font-size: .88rem;
            font-weight: 500;
            transition: all .2s;
        }
        .nav-settings .nav-link:hover {
            background: #f0f4ff;
            color: #1d7af3;
        }
        .nav-settings .nav-link.active {
            background: #1d7af3;
            color: #fff !important;
            box-shadow: 0 4px 12px rgba(29,122,243,.3);
        }
    </style>
    <script>
        function togglePwd(id, btn) {
            const inp = document.getElementById(id);
            const isText = inp.type === 'text';
            inp.type = isText ? 'password' : 'text';
            btn.querySelector('i').className = isText ? 'fas fa-eye' : 'fas fa-eye-slash';
        }
    </script>
    </x-slot>
</x-app-layout>
