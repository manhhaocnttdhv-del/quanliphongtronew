<x-guest-layout>
    {{-- Session Status --}}
    @if (session('status'))
        <div class="session-status">{{ session('status') }}</div>
    @endif

    <h1 class="auth-card-title">Chào mừng trở lại 👋</h1>
    <p class="auth-card-sub">Đăng nhập để truy cập hệ thống quản lý</p>

    <form method="POST" action="{{ route('login') }}">
        @csrf

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
                    required autofocus autocomplete="username"
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
                    required autocomplete="current-password"
                    placeholder="••••••••"
                    class="form-input {{ $errors->get('password') ? 'error-input' : '' }}"
                >
                <button type="button" class="pw-toggle" onclick="togglePassword()" title="Hiện/ẩn mật khẩu">
                    <svg id="eye-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
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

        {{-- Remember + Forgot --}}
        <div class="form-row">
            <label class="checkbox-label">
                <input type="checkbox" name="remember" id="remember_me">
                Ghi nhớ đăng nhập
            </label>
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="forgot-link">Quên mật khẩu?</a>
            @endif
        </div>

        <button type="submit" class="btn-submit" id="login-btn">
            Đăng nhập ngay
        </button>
    </form>
</x-guest-layout>

<script>
function togglePassword() {
    const pw = document.getElementById('password');
    const icon = document.getElementById('eye-icon');
    const isHidden = pw.type === 'password';
    pw.type = isHidden ? 'text' : 'password';
    icon.innerHTML = isHidden
        ? `<path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>`
        : `<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>`;
}

// Button loading state
document.querySelector('form').addEventListener('submit', function() {
    const btn = document.getElementById('login-btn');
    btn.textContent = 'Đang đăng nhập...';
    btn.disabled = true;
    btn.style.opacity = '.75';
});
</script>
