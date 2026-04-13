<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', 'Quản lý phòng trọ') }} – Đăng nhập</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            *, *::before, *::after { box-sizing: border-box; }

            body {
                font-family: 'Inter', sans-serif;
                margin: 0;
                padding: 0;
                min-height: 100vh;
                background: #0f0c29;
                overflow-x: hidden;
            }

            /* ── Animated gradient background ── */
            .auth-bg {
                position: fixed;
                inset: 0;
                background: linear-gradient(135deg, #0f0c29 0%, #302b63 50%, #24243e 100%);
                z-index: 0;
            }
            .auth-bg::before {
                content: '';
                position: absolute;
                inset: 0;
                background:
                    radial-gradient(ellipse 80% 60% at 20% 20%, rgba(99,102,241,.35) 0%, transparent 60%),
                    radial-gradient(ellipse 70% 50% at 80% 80%, rgba(168,85,247,.25) 0%, transparent 60%),
                    radial-gradient(ellipse 50% 40% at 50% 50%, rgba(59,130,246,.15) 0%, transparent 60%);
                animation: bgPulse 8s ease-in-out infinite alternate;
            }
            @keyframes bgPulse {
                0%   { opacity: 1; transform: scale(1); }
                100% { opacity: .7; transform: scale(1.05); }
            }

            /* ── Floating orbs ── */
            .orb {
                position: fixed;
                border-radius: 50%;
                filter: blur(80px);
                opacity: .45;
                animation: floatOrb var(--dur, 12s) ease-in-out infinite alternate;
                pointer-events: none;
                z-index: 0;
            }
            .orb-1 { width: 420px; height: 420px; background: radial-gradient(circle, #6366f1, transparent 70%); top: -80px; left: -80px; --dur: 10s; }
            .orb-2 { width: 360px; height: 360px; background: radial-gradient(circle, #a855f7, transparent 70%); bottom: -60px; right: -60px; --dur: 14s; }
            .orb-3 { width: 280px; height: 280px; background: radial-gradient(circle, #3b82f6, transparent 70%); top: 40%; left: 30%; --dur: 18s; }
            @keyframes floatOrb {
                0%   { transform: translate(0, 0) scale(1); }
                100% { transform: translate(40px, -40px) scale(1.1); }
            }

            /* ── Page wrapper ── */
            .auth-wrapper {
                position: relative;
                z-index: 1;
                min-height: 100vh;
                display: grid;
                grid-template-columns: 1fr 1fr;
            }
            @media (max-width: 900px) {
                .auth-wrapper { grid-template-columns: 1fr; }
                .auth-left { display: none; }
            }

            /* ── Left panel ── */
            .auth-left {
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
                padding: 60px 48px;
                gap: 40px;
                border-right: 1px solid rgba(255,255,255,.06);
            }
            .auth-left-brand {
                display: flex;
                align-items: center;
                gap: 16px;
            }
            .brand-icon {
                width: 64px; height: 64px;
                background: linear-gradient(135deg, #6366f1, #a855f7);
                border-radius: 20px;
                display: flex; align-items: center; justify-content: center;
                box-shadow: 0 20px 60px rgba(99,102,241,.5);
                animation: iconFloat 4s ease-in-out infinite;
            }
            @keyframes iconFloat {
                0%,100% { transform: translateY(0); }
                50%      { transform: translateY(-8px); }
            }
            .brand-name {
                font-size: 2rem; font-weight: 800;
                color: #fff;
                letter-spacing: -0.5px;
            }
            .brand-name span { color: #a78bfa; }

            .auth-left-tagline {
                text-align: center;
                max-width: 340px;
            }
            .auth-left-tagline h2 {
                font-size: 1.75rem; font-weight: 700;
                color: #fff; margin: 0 0 12px;
                line-height: 1.3;
            }
            .auth-left-tagline p {
                color: rgba(255,255,255,.55);
                font-size: .95rem; line-height: 1.7;
                margin: 0;
            }

            /* Feature pills */
            .feature-pills {
                display: flex; flex-direction: column; gap: 14px;
                width: 100%; max-width: 340px;
            }
            .feature-pill {
                display: flex; align-items: center; gap: 14px;
                background: rgba(255,255,255,.06);
                border: 1px solid rgba(255,255,255,.09);
                border-radius: 16px;
                padding: 14px 18px;
                transition: background .25s;
            }
            .feature-pill:hover { background: rgba(255,255,255,.1); }
            .pill-icon {
                width: 40px; height: 40px; border-radius: 12px;
                display: flex; align-items: center; justify-content: center;
                flex-shrink: 0;
            }
            .pill-icon.indigo { background: rgba(99,102,241,.25); }
            .pill-icon.purple { background: rgba(168,85,247,.25); }
            .pill-icon.blue   { background: rgba(59,130,246,.25); }
            .pill-text strong { color: #fff; font-size: .9rem; display: block; }
            .pill-text span   { color: rgba(255,255,255,.45); font-size: .8rem; }

            /* ── Right panel ── */
            .auth-right {
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
                padding: 48px 32px;
            }

            /* Logo for mobile */
            .mobile-brand {
                display: none;
                flex-direction: column; align-items: center; gap: 12px;
                margin-bottom: 32px;
            }
            @media (max-width: 900px) {
                .mobile-brand { display: flex; }
            }

            /* ── Glass card ── */
            .auth-card {
                width: 100%;
                max-width: 440px;
                background: rgba(255, 255, 255, 0.07);
                backdrop-filter: blur(24px);
                -webkit-backdrop-filter: blur(24px);
                border: 1px solid rgba(255,255,255,.13);
                border-radius: 28px;
                padding: 44px 42px;
                box-shadow: 0 32px 80px rgba(0,0,0,.5), 0 0 0 1px rgba(255,255,255,.05) inset;
                animation: cardIn .5s cubic-bezier(.22,1,.36,1) both;
            }
            @keyframes cardIn {
                from { opacity: 0; transform: translateY(24px) scale(.97); }
                to   { opacity: 1; transform: translateY(0) scale(1); }
            }

            .auth-card-title {
                font-size: 1.65rem; font-weight: 700;
                color: #fff; margin: 0 0 6px;
                letter-spacing: -.4px;
            }
            .auth-card-sub {
                color: rgba(255,255,255,.45);
                font-size: .875rem; margin: 0 0 32px;
            }

            /* ── Form elements ── */
            .form-group { margin-bottom: 20px; }
            .form-label {
                display: block;
                color: rgba(255,255,255,.7);
                font-size: .825rem; font-weight: 500;
                margin-bottom: 8px;
                letter-spacing: .3px;
            }
            .input-wrap { position: relative; }
            .input-icon {
                position: absolute; left: 14px; top: 50%;
                transform: translateY(-50%);
                color: rgba(255,255,255,.3);
                pointer-events: none;
                transition: color .2s;
            }
            .input-wrap:focus-within .input-icon { color: #a78bfa; }
            .form-input {
                width: 100%;
                padding: 12px 14px 12px 42px;
                background: rgba(255,255,255,.08);
                border: 1px solid rgba(255,255,255,.12);
                border-radius: 12px;
                color: #fff;
                font-size: .9rem;
                font-family: 'Inter', sans-serif;
                outline: none;
                transition: border-color .2s, background .2s, box-shadow .2s;
            }
            .form-input::placeholder { color: rgba(255,255,255,.25); }
            .form-input:focus {
                border-color: rgba(167,139,250,.6);
                background: rgba(255,255,255,.11);
                box-shadow: 0 0 0 3px rgba(167,139,250,.15);
            }
            .form-input.error-input { border-color: rgba(248,113,113,.6); }

            /* Password toggle */
            .pw-toggle {
                position: absolute; right: 14px; top: 50%;
                transform: translateY(-50%);
                background: none; border: none;
                color: rgba(255,255,255,.3); cursor: pointer;
                padding: 0; transition: color .2s;
            }
            .pw-toggle:hover { color: rgba(255,255,255,.7); }

            /* Error text */
            .form-error { color: #f87171; font-size: .78rem; margin-top: 6px; }

            /* Session status */
            .session-status {
                background: rgba(52,211,153,.1);
                border: 1px solid rgba(52,211,153,.3);
                color: #6ee7b7;
                border-radius: 10px;
                padding: 10px 14px;
                font-size: .82rem;
                margin-bottom: 20px;
            }

            /* Remember + forgot */
            .form-row {
                display: flex; align-items: center; justify-content: space-between;
                margin-bottom: 28px; margin-top: 4px;
            }
            .checkbox-label {
                display: flex; align-items: center; gap: 8px;
                color: rgba(255,255,255,.55); font-size: .83rem; cursor: pointer;
            }
            .checkbox-label input[type=checkbox] {
                width: 16px; height: 16px;
                accent-color: #a78bfa;
                cursor: pointer;
            }
            .forgot-link {
                color: #a78bfa; font-size: .83rem; font-weight: 500;
                text-decoration: none;
                transition: color .2s;
            }
            .forgot-link:hover { color: #c4b5fd; }

            /* Submit button */
            .btn-submit {
                width: 100%;
                padding: 13px;
                background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
                border: none; border-radius: 12px;
                color: #fff; font-size: .95rem; font-weight: 600;
                font-family: 'Inter', sans-serif;
                cursor: pointer;
                position: relative; overflow: hidden;
                box-shadow: 0 8px 30px rgba(99,102,241,.4);
                transition: transform .15s, box-shadow .15s;
            }
            .btn-submit::before {
                content: '';
                position: absolute; inset: 0;
                background: linear-gradient(135deg, rgba(255,255,255,.15), transparent);
                opacity: 0; transition: opacity .2s;
            }
            .btn-submit:hover { transform: translateY(-1px); box-shadow: 0 12px 40px rgba(99,102,241,.55); }
            .btn-submit:hover::before { opacity: 1; }
            .btn-submit:active { transform: translateY(0); }

            /* Footer */
            .auth-footer {
                color: rgba(255,255,255,.3);
                font-size: .78rem; text-align: center;
                margin-top: 32px;
            }

            /* Divider */
            .divider {
                height: 1px;
                background: rgba(255,255,255,.08);
                margin: 28px 0;
            }
        </style>
    </head>
    <body>
        <!-- Background -->
        <div class="auth-bg"></div>
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
        <div class="orb orb-3"></div>

        <div class="auth-wrapper">
            <!-- ── Left Panel ── -->
            <div class="auth-left">
                <div class="auth-left-brand">
                    <div class="brand-icon">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
                            <polyline points="9 22 9 12 15 12 15 22"/>
                        </svg>
                    </div>
                    <div class="brand-name">Boarding<span>Pro</span></div>
                </div>

                <div class="auth-left-tagline">
                    <h2>Quản lý phòng trọ thông minh</h2>
                    <p>Nền tảng toàn diện giúp chủ trọ vận hành hiệu quả và minh bạch hơn bao giờ hết.</p>
                </div>

                <div class="feature-pills">
                    <div class="feature-pill">
                        <div class="pill-icon indigo">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#818cf8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/>
                            </svg>
                        </div>
                        <div class="pill-text">
                            <strong>Quản lý phòng & hợp đồng</strong>
                            <span>Theo dõi toàn bộ chu kỳ thuê phòng</span>
                        </div>
                    </div>
                    <div class="feature-pill">
                        <div class="pill-icon purple">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#c084fc" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/>
                            </svg>
                        </div>
                        <div class="pill-text">
                            <strong>Hoá đơn & thanh toán</strong>
                            <span>Tích hợp VietQR, theo dõi tự động</span>
                        </div>
                    </div>
                    <div class="feature-pill">
                        <div class="pill-icon blue">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#60a5fa" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>
                            </svg>
                        </div>
                        <div class="pill-text">
                            <strong>Báo cáo & thống kê</strong>
                            <span>Dashboard trực quan, realtime</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── Right Panel ── -->
            <div class="auth-right">
                <!-- Mobile logo -->
                <div class="mobile-brand">
                    <div class="brand-icon">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
                            <polyline points="9 22 9 12 15 12 15 22"/>
                        </svg>
                    </div>
                    <div class="brand-name" style="font-size:1.5rem">Boarding<span>Pro</span></div>
                </div>

                <div class="auth-card">
                    {{ $slot }}

                    <div class="divider"></div>
                    <div class="auth-footer">&copy; {{ date('Y') }} BoardingPro – Đồ án Quản lý Phòng trọ</div>
                </div>
            </div>
        </div>
    </body>
</html>
