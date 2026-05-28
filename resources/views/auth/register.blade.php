<x-guest-layout>
    <h1 class="auth-card-title">Tạo tài khoản mới ✨</h1>
    <p class="auth-card-sub">Đăng ký để gửi yêu cầu đặt thuê phòng online</p>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        {{-- Name --}}
        <div class="form-group">
            <label class="form-label" for="name">Họ và tên</label>
            <div class="input-wrap">
                <span class="input-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/>
                        <circle cx="12" cy="7" r="4"/>
                    </svg>
                </span>
                <input
                    id="name"
                    type="text"
                    name="name"
                    value="{{ old('name') }}"
                    required autofocus autocomplete="name"
                    placeholder="Nguyễn Văn A"
                    class="form-input {{ $errors->get('name') ? 'error-input' : '' }}"
                >
            </div>
            @if ($errors->get('name'))
                @foreach ($errors->get('name') as $msg)
                    <div class="form-error">{{ $msg }}</div>
                @endforeach
            @endif
        </div>

        {{-- Email --}}
        <div class="form-group">
            <label class="form-label" for="email">Địa chỉ Email</label>
            <div class="input-wrap">
                <span class="input-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                        <polyline points="22,6 12,13 2,6"/>
                    </svg>
                </span>
                <input
                    id="email"
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    required autocomplete="username"
                    placeholder="example@gmail.com"
                    class="form-input {{ $errors->get('email') ? 'error-input' : '' }}"
                >
            </div>
            @if ($errors->get('email'))
                @foreach ($errors->get('email') as $msg)
                    <div class="form-error">{{ $msg }}</div>
                @endforeach
            @endif
        </div>

        {{-- Phone --}}
        <div class="form-group">
            <label class="form-label" for="phone">Số điện thoại</label>
            <div class="input-wrap">
                <span class="input-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0122 16.92z"/>
                    </svg>
                </span>
                <input
                    id="phone"
                    type="tel"
                    name="phone"
                    value="{{ old('phone') }}"
                    required autocomplete="tel"
                    placeholder="0xxxxxxxxx"
                    pattern="0\d{9}"
                    maxlength="10"
                    class="form-input {{ $errors->get('phone') ? 'error-input' : '' }}"
                >
            </div>
            @if ($errors->get('phone'))
                @foreach ($errors->get('phone') as $msg)
                    <div class="form-error">{{ $msg }}</div>
                @endforeach
            @endif
        </div>

        {{-- Password --}}
        <div class="form-group">
            <label class="form-label" for="password">Mật khẩu</label>
            <div class="input-wrap">
                <span class="input-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                        <path d="M7 11V7a5 5 0 0110 0v4"/>
                    </svg>
                </span>
                <input
                    id="password"
                    type="password"
                    name="password"
                    required autocomplete="new-password"
                    placeholder="Tối thiểu 8 ký tự"
                    minlength="8"
                    class="form-input {{ $errors->get('password') ? 'error-input' : '' }}"
                >
                <button type="button" class="pw-toggle" onclick="togglePw('password','eye-1')" title="Hiện/ẩn mật khẩu">
                    <svg id="eye-1" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                        <circle cx="12" cy="12" r="3"/>
                    </svg>
                </button>
            </div>
            @if ($errors->get('password'))
                @foreach ($errors->get('password') as $msg)
                    <div class="form-error">{{ $msg }}</div>
                @endforeach
            @endif
        </div>

        {{-- Confirm Password --}}
        <div class="form-group">
            <label class="form-label" for="password_confirmation">Xác nhận mật khẩu</label>
            <div class="input-wrap">
                <span class="input-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 12l2 2 4-4"/>
                        <path d="M21 12c0 4.97-4.03 9-9 9s-9-4.03-9-9 4.03-9 9-9 9 4.03 9 9z"/>
                    </svg>
                </span>
                <input
                    id="password_confirmation"
                    type="password"
                    name="password_confirmation"
                    required autocomplete="new-password"
                    placeholder="Nhập lại mật khẩu"
                    class="form-input {{ $errors->get('password_confirmation') ? 'error-input' : '' }}"
                >
                <button type="button" class="pw-toggle" onclick="togglePw('password_confirmation','eye-2')" title="Hiện/ẩn mật khẩu">
                    <svg id="eye-2" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                        <circle cx="12" cy="12" r="3"/>
                    </svg>
                </button>
            </div>
            @if ($errors->get('password_confirmation'))
                @foreach ($errors->get('password_confirmation') as $msg)
                    <div class="form-error">{{ $msg }}</div>
                @endforeach
            @endif
        </div>

        <button type="submit" class="btn-submit" id="register-btn">
            Đăng ký ngay
        </button>

        <div class="form-row" style="justify-content:center; margin-top:24px; margin-bottom:0;">
            <span style="color: rgba(255,255,255,.45); font-size:.85rem;">Đã có tài khoản?</span>
            <a href="{{ route('login') }}" class="forgot-link" style="margin-left:8px;">Đăng nhập ngay</a>
        </div>
    </form>
</x-guest-layout>

<script>
    function togglePw(inputId, iconId) {
        const pw = document.getElementById(inputId);
        const icon = document.getElementById(iconId);
        const isHidden = pw.type === 'password';
        pw.type = isHidden ? 'text' : 'password';
        icon.innerHTML = isHidden
            ? `<path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>`
            : `<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>`;
    }

    document.querySelector('form').addEventListener('submit', function() {
        const btn = document.getElementById('register-btn');
        btn.textContent = 'Đang tạo tài khoản...';
        btn.disabled = true;
        btn.style.opacity = '.75';
    });
</script>
