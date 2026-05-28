<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang Chủ - {{ \App\Models\Setting::get('site_name', 'Hệ Thống Quản Lý Phòng Trọ') }}</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- AOS Animation CSS -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <style>
        :root {
            --primary: #4f46e5;
            --primary-light: #818cf8;
            --primary-dark: #3730a3;
            --secondary: #f59e0b;
            --dark: #0f172a;
            --dark-light: #1e293b;
            --light: #f8fafc;
            --gray: #64748b;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--light);
            color: #334155;
            overflow-x: hidden;
        }

        /* ----- NAVBAR ----- */
        .navbar {
            background-color: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(15px);
            border-bottom: 1px solid rgba(0,0,0,0.05);
            transition: all 0.4s ease;
            padding: 15px 0;
        }
        .navbar.scrolled {
            padding: 10px 0;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            background-color: rgba(255, 255, 255, 0.98);
        }
        .navbar-brand {
            font-weight: 800;
            color: var(--primary) !important;
            font-size: 1.8rem;
            letter-spacing: -0.5px;
        }
        .navbar-brand span { color: var(--secondary); }
        .nav-link {
            font-weight: 600;
            color: var(--dark) !important;
            margin: 0 10px;
            position: relative;
        }
        .nav-link::after {
            content: '';
            position: absolute;
            width: 0; height: 3px;
            background: var(--primary);
            bottom: -5px; left: 0;
            transition: 0.3s;
            border-radius: 5px;
        }
        .nav-link:hover::after { width: 100%; }
        
        .btn-login {
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            border: none;
            color: white;
            padding: 10px 25px;
            border-radius: 50px;
            font-weight: 600;
            box-shadow: 0 5px 15px rgba(79, 70, 229, 0.3);
            transition: 0.3s;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(79, 70, 229, 0.4);
            color: white;
        }

        /* ----- HERO SECTION ----- */
        .hero {
            position: relative;
            min-height: 100vh;
            display: flex;
            align-items: center;
            background: linear-gradient(to right, rgba(15, 23, 42, 0.9), rgba(15, 23, 42, 0.6)), url('https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?q=80&w=2000') center/cover no-repeat;
            padding-top: 80px;
        }
        .hero-content {
            color: white;
            max-width: 700px;
        }
        .hero-badge {
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
            padding: 8px 20px;
            border-radius: 50px;
            font-size: 0.9rem;
            font-weight: 600;
            display: inline-block;
            margin-bottom: 20px;
            border: 1px solid rgba(255,255,255,0.2);
            color: var(--secondary);
        }
        .hero-title {
            font-size: 4.5rem;
            font-weight: 900;
            line-height: 1.1;
            margin-bottom: 20px;
        }
        .hero-title span {
            background: linear-gradient(120deg, #a78bfa, #818cf8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .hero-desc {
            font-size: 1.2rem;
            color: #cbd5e1;
            margin-bottom: 40px;
            font-weight: 300;
        }
        .hero-btns .btn {
            padding: 15px 35px;
            border-radius: 50px;
            font-size: 1.1rem;
            font-weight: 600;
            transition: 0.3s;
        }
        .btn-explore {
            background: var(--primary);
            color: white;
            border: none;
            box-shadow: 0 10px 20px rgba(79, 70, 229, 0.4);
        }
        .btn-explore:hover {
            background: var(--primary-dark);
            transform: translateY(-3px);
            color: white;
        }
        .btn-contact-hero {
            background: transparent;
            color: white;
            border: 2px solid white;
        }
        .btn-contact-hero:hover {
            background: white;
            color: var(--dark);
        }

        /* ----- HOW IT WORKS (PROCESS) ----- */
        .process-section {
            padding: 100px 0;
            background: white;
        }
        .section-header {
            text-align: center;
            margin-bottom: 60px;
        }
        .section-header h2 {
            font-size: 2.8rem;
            font-weight: 800;
            color: var(--dark);
            margin-bottom: 15px;
        }
        .section-header p {
            color: var(--gray);
            font-size: 1.1rem;
            max-width: 600px;
            margin: 0 auto;
        }
        .process-card {
            text-align: center;
            padding: 30px;
            position: relative;
        }
        .process-icon {
            width: 90px;
            height: 90px;
            background: var(--light);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            color: var(--primary);
            margin: 0 auto 25px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
            position: relative;
            z-index: 2;
        }
        .process-step {
            position: absolute;
            top: 20px;
            right: 50%;
            transform: translateX(60px);
            width: 35px;
            height: 35px;
            background: var(--secondary);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            border: 3px solid white;
            z-index: 3;
        }
        .process-card h4 {
            font-weight: 700;
            font-size: 1.4rem;
        }
        .process-arrow {
            position: absolute;
            top: 60px;
            right: -20px;
            font-size: 2rem;
            color: #cbd5e1;
            z-index: 1;
        }
        @media (max-width: 991px) {
            .process-arrow { display: none; }
        }

        /* ----- ROOMS SECTION ----- */
        .rooms-section {
            padding: 100px 0;
            background: var(--light);
        }
        .room-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            border: none;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            transition: 0.4s ease;
            height: 100%;
            position: relative;
        }
        .room-card:hover {
            transform: translateY(-15px);
            box-shadow: 0 20px 40px rgba(79, 70, 229, 0.15);
        }
        .room-img-container {
            position: relative;
            overflow: hidden;
            height: 260px;
        }
        .room-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: 0.6s ease;
        }
        .room-card:hover .room-img {
            transform: scale(1.1) rotate(1deg);
        }
        .room-overlay {
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: linear-gradient(to bottom, rgba(0,0,0,0.1), rgba(0,0,0,0.5));
        }
        .room-badge-status {
            position: absolute;
            top: 20px; right: 20px;
            background: #10b981;
            color: white;
            padding: 6px 15px;
            border-radius: 30px;
            font-size: 0.85rem;
            font-weight: 600;
            box-shadow: 0 4px 10px rgba(16, 185, 129, 0.3);
        }
        .room-price-tag {
            position: absolute;
            bottom: 20px; left: 20px;
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(5px);
            padding: 8px 15px;
            border-radius: 12px;
            font-weight: 800;
            font-size: 1.3rem;
            color: var(--primary);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .room-price-tag span {
            font-size: 0.8rem;
            color: var(--gray);
            font-weight: 500;
        }
        .room-body {
            padding: 30px 25px;
        }
        .room-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 15px;
        }
        .room-title a {
            color: var(--dark);
            text-decoration: none;
            transition: 0.2s;
        }
        .room-title a:hover {
            color: var(--primary);
        }
        .room-features {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 1px solid #f1f5f9;
        }
        .r-feature {
            display: flex;
            align-items: center;
            color: var(--gray);
            font-size: 0.95rem;
            font-weight: 500;
        }
        .r-feature i {
            color: var(--primary-light);
            margin-right: 8px;
            font-size: 1.1rem;
            width: 20px;
        }
        .btn-view-room {
            width: 100%;
            background: var(--light);
            color: var(--primary);
            border: none;
            padding: 12px;
            border-radius: 12px;
            font-weight: 600;
            transition: 0.3s;
        }
        .btn-view-room:hover {
            background: var(--primary);
            color: white;
        }

        /* ----- FEATURES / AMENITIES ----- */
        .amenities-section {
            padding: 100px 0;
            background: white;
        }
        .amenity-card {
            display: flex;
            align-items: flex-start;
            padding: 30px;
            border-radius: 20px;
            background: var(--light);
            transition: 0.3s;
            height: 100%;
        }
        .amenity-card:hover {
            background: var(--primary);
            color: white;
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(79, 70, 229, 0.2);
        }
        .amenity-icon {
            width: 60px; height: 60px;
            background: white;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            color: var(--primary);
            margin-right: 20px;
            flex-shrink: 0;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: 0.3s;
        }
        .amenity-card:hover .amenity-icon {
            background: rgba(255,255,255,0.2);
            color: white;
        }
        .amenity-info h4 {
            font-weight: 700;
            font-size: 1.2rem;
            margin-bottom: 10px;
        }
        .amenity-card:hover .amenity-info p {
            color: rgba(255,255,255,0.8) !important;
        }

        /* ----- TESTIMONIALS ----- */
        .testimonials {
            padding: 100px 0;
            background: var(--dark);
            color: white;
            position: relative;
            overflow: hidden;
        }
        .testimonials::before {
            content: '';
            position: absolute;
            top: -100px; right: -100px;
            width: 300px; height: 300px;
            background: var(--primary);
            filter: blur(150px);
            opacity: 0.5;
        }
        .testimonials .section-header h2 {
            color: white;
        }
        .testimonials .section-header p {
            color: #94a3b8;
        }
        .review-card {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 40px 30px;
            margin: 15px;
        }
        .stars {
            color: var(--secondary);
            margin-bottom: 20px;
            font-size: 1.2rem;
        }
        .review-text {
            font-size: 1.1rem;
            font-style: italic;
            color: #cbd5e1;
            margin-bottom: 30px;
            line-height: 1.7;
        }
        .reviewer {
            display: flex;
            align-items: center;
        }
        .reviewer img {
            width: 60px; height: 60px;
            border-radius: 50%;
            margin-right: 15px;
            object-fit: cover;
            border: 2px solid var(--primary);
        }
        .reviewer-info h5 {
            margin: 0;
            font-weight: 700;
            font-size: 1.1rem;
        }
        .reviewer-info span {
            color: #94a3b8;
            font-size: 0.9rem;
        }

        /* ----- CTA SECTION ----- */
        .cta-section {
            padding: 80px 0;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            position: relative;
            overflow: hidden;
        }
        .cta-bg-shape {
            position: absolute;
            width: 100%; height: 100%;
            top: 0; left: 0;
            background: url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxMDAlIiBoZWlnaHQ9IjEwMCUiPjxkZWZzPjxwYXR0ZXJuIGlkPSJwIiB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgcGF0dGVyblVuaXRzPSJ1c2VyU3BhY2VPblVzZSI+PGNpcmNsZSBjeD0iNTAiIGN5PSI1MCIgcj0iMjAiIGZpbGw9InJnYmEoMjU1LDI1NSwyNTUsMC4wNSkiLz48L3BhdHRlcm4+PC9kZWZzPjxyZWN0IHdpZHRoPSIxMDAlIiBoZWlnaHQ9IjEwMCUiIGZpbGw9InVybCgjcCkiLz48L3N2Zz4=');
        }
        .cta-content {
            position: relative;
            z-index: 2;
            text-align: center;
            color: white;
        }
        .cta-content h2 {
            font-size: 3rem;
            font-weight: 800;
            margin-bottom: 20px;
        }
        .cta-content p {
            font-size: 1.2rem;
            opacity: 0.9;
            margin-bottom: 40px;
        }
        .btn-cta {
            background: white;
            color: var(--primary);
            padding: 15px 40px;
            border-radius: 50px;
            font-weight: 700;
            font-size: 1.2rem;
            text-decoration: none;
            display: inline-block;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
            transition: 0.3s;
        }
        .btn-cta:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.2);
            color: var(--primary-dark);
        }

        /* ----- FOOTER ----- */
        footer {
            background: #020617;
            color: #94a3b8;
            padding: 80px 0 30px;
        }
        .footer-brand {
            font-size: 2rem;
            font-weight: 800;
            color: white;
            margin-bottom: 20px;
        }
        .footer-brand span { color: var(--primary); }
        .footer-desc { line-height: 1.8; margin-bottom: 30px; }
        .social-links a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px; height: 40px;
            background: rgba(255,255,255,0.05);
            color: white;
            border-radius: 50%;
            margin-right: 10px;
            transition: 0.3s;
        }
        .social-links a:hover {
            background: var(--primary);
            transform: translateY(-3px);
        }
        .footer-title {
            color: white;
            font-weight: 700;
            font-size: 1.3rem;
            margin-bottom: 25px;
            position: relative;
            padding-bottom: 10px;
        }
        .footer-title::after {
            content: '';
            position: absolute;
            left: 0; bottom: 0;
            width: 40px; height: 3px;
            background: var(--primary);
            border-radius: 3px;
        }
        .footer-links li { margin-bottom: 15px; }
        .footer-links a {
            color: #94a3b8;
            text-decoration: none;
            transition: 0.3s;
            display: flex;
            align-items: center;
        }
        .footer-links a::before {
            content: '\f105';
            font-family: 'Font Awesome 5 Free';
            font-weight: 900;
            margin-right: 10px;
            color: var(--primary);
            font-size: 0.8rem;
        }
        .footer-links a:hover { color: white; transform: translateX(5px); }
        
        .contact-list li {
            display: flex;
            margin-bottom: 20px;
        }
        .contact-list i {
            color: var(--primary);
            font-size: 1.2rem;
            margin-top: 5px;
            margin-right: 15px;
            width: 20px;
        }
        
        .copyright {
            border-top: 1px solid rgba(255,255,255,0.05);
            padding-top: 30px;
            margin-top: 50px;
            text-align: center;
        }

        /* Zalo Widget */
        .zalo-widget {
            position: fixed;
            bottom: 30px; right: 30px;
            width: 65px; height: 65px;
            background-color: #0068ff;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 4px 20px rgba(0, 104, 255, 0.4);
            cursor: pointer;
            z-index: 1000;
            animation: pulse 2s infinite;
            transition: 0.3s;
        }
        .zalo-widget:hover { transform: scale(1.1) rotate(10deg); }
        .zalo-widget img { width: 40px; height: 40px; filter: brightness(0) invert(1); }

    </style>
</head>
<body>

    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand" href="/">
                <i class="fas fa-building me-2"></i>Boarding<span>Pro</span>
            </a>
            <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <i class="fas fa-bars fs-2"></i>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav align-items-center gap-3">
                    <li class="nav-item"><a class="nav-link" href="#quy-trinh">Quy Trình</a></li>
                    <li class="nav-item"><a class="nav-link" href="#danh-sach-phong">Phòng Trống</a></li>
                    <li class="nav-item"><a class="nav-link" href="#tien-ich">Tiện Ích</a></li>
                    <li class="nav-item"><a class="nav-link" href="#cam-nhan">Đánh Giá</a></li>
                    
                    @if (Route::has('login'))
                        @auth
                            @if(auth()->user()->hasRole('admin'))
                                <li class="nav-item ms-lg-2"><a href="{{ route('admin.dashboard') }}" class="btn btn-login">Quản Trị</a></li>
                            @elseif(auth()->user()->hasRole('tenant'))
                                <li class="nav-item ms-lg-2"><a href="{{ route('tenant.dashboard') }}" class="btn btn-login">Cổng Khách</a></li>
                            @elseif(auth()->user()->hasRole('customer'))
                                <li class="nav-item ms-lg-2"><a href="{{ route('booking.index') }}" class="btn btn-login"><i class="fas fa-clipboard-list me-2"></i>Yêu cầu của tôi</a></li>
                            @else
                                <li class="nav-item ms-lg-2"><a href="{{ url('/dashboard') }}" class="btn btn-login">Dashboard</a></li>
                            @endif
                        @else
                            <li class="nav-item ms-lg-2"><a href="{{ route('login') }}" class="btn btn-login"><i class="fas fa-user-circle me-2"></i>Đăng Nhập</a></li>
                            @if (Route::has('register'))
                                <li class="nav-item ms-lg-2"><a href="{{ route('register') }}" class="btn btn-login" style="background: linear-gradient(135deg, var(--secondary), #fbbf24);"><i class="fas fa-user-plus me-2"></i>Đăng Ký</a></li>
                            @endif
                        @endauth
                    @endif
                </ul>
            </div>
        </div>
    </nav>

    <!-- HERO -->
    <section class="hero">
        <div class="container">
            <div class="hero-content" data-aos="fade-up" data-aos-duration="1000">
                <div class="hero-badge"><i class="fas fa-star text-warning me-2"></i>Hệ thống phòng trọ #1 TP.HCM</div>
                <h1 class="hero-title">Tìm Tổ Ấm Mới,<br><span>Bắt Đầu Cuộc Sống Mới</span></h1>
                <p class="hero-desc">Trải nghiệm không gian sống hiện đại, an ninh và đầy đủ tiện nghi. Hệ thống quản lý chuyên nghiệp giúp bạn an tâm tận hưởng cuộc sống.</p>
                <div class="hero-btns d-flex gap-3 flex-wrap">
                    <a href="#danh-sach-phong" class="btn btn-explore"><i class="fas fa-search me-2"></i>Xem Phòng Trống</a>
                    <a href="#quy-trinh" class="btn btn-contact-hero"><i class="fas fa-info-circle me-2"></i>Tìm hiểu thêm</a>
                </div>
            </div>
        </div>
    </section>

    <!-- PROCESS (HOW IT WORKS) -->
    <section id="quy-trinh" class="process-section">
        <div class="container">
            <div class="section-header" data-aos="fade-up">
                <h2>Quy Trình 3 Bước Đơn Giản</h2>
                <p>Thuê phòng chưa bao giờ dễ dàng và nhanh chóng đến thế với quy trình tối ưu hóa của chúng tôi.</p>
            </div>
            
            <div class="row position-relative">
                <div class="col-lg-4 col-md-6 mb-5 mb-lg-0" data-aos="fade-up" data-aos-delay="100">
                    <div class="process-card">
                        <div class="process-step">1</div>
                        <div class="process-icon"><i class="fas fa-search-location"></i></div>
                        <h4>Tìm & Chọn Phòng</h4>
                        <p class="text-muted mt-3">Duyệt qua danh sách các phòng đang trống, xem chi tiết hình ảnh, giá cả và các tiện ích đi kèm.</p>
                        <i class="fas fa-chevron-right process-arrow"></i>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-5 mb-lg-0" data-aos="fade-up" data-aos-delay="200">
                    <div class="process-card">
                        <div class="process-step">2</div>
                        <div class="process-icon"><i class="fas fa-calendar-check"></i></div>
                        <h4>Hẹn Xem Phòng</h4>
                        <p class="text-muted mt-3">Liên hệ với chúng tôi qua Zalo hoặc Hotline để đặt lịch đến xem trực tiếp căn phòng bạn ưng ý.</p>
                        <i class="fas fa-chevron-right process-arrow"></i>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mx-auto" data-aos="fade-up" data-aos-delay="300">
                    <div class="process-card">
                        <div class="process-step">3</div>
                        <div class="process-icon"><i class="fas fa-file-signature"></i></div>
                        <h4>Ký Hợp Đồng & Chuyển Vào</h4>
                        <p class="text-muted mt-3">Ký hợp đồng rõ ràng, minh bạch pháp lý. Nhận chìa khóa và bắt đầu cuộc sống tuyệt vời tại nơi ở mới.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ROOMS -->
    <section id="danh-sach-phong" class="rooms-section">
        <div class="container">
            <div class="section-header" data-aos="fade-up">
                <h2>Phòng Đang Trống Dành Cho Bạn</h2>
                <p>Khám phá bộ sưu tập các phòng tốt nhất hiện nay. Đừng bỏ lỡ cơ hội sở hữu không gian sống lý tưởng.</p>
            </div>

            @if($rooms->count() > 0)
                <div class="row g-4">
                    @foreach($rooms as $index => $room)
                        <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="{{ ($index % 3) * 100 }}">
                            <div class="room-card">
                                <div class="room-img-container">
                                    @php
                                        $imageUrl = 'https://via.placeholder.com/600x400?text=Phong+Tro';
                                        if(!empty($room->images) && is_array($room->images) && count($room->images) > 0) {
                                            $img = $room->images[0];
                                            $imageUrl = Str::startsWith($img, ['http://', 'https://']) ? $img : Storage::url($img);
                                        } elseif(!empty($room->image_path)) {
                                            $imageUrl = Str::startsWith($room->image_path, ['http://', 'https://']) ? $room->image_path : Storage::url($room->image_path);
                                        }
                                    @endphp
                                    <a href="{{ route('room.show', $room->id) }}">
                                        <img src="{{ $imageUrl }}" class="room-img" alt="Phòng {{ $room->name }}">
                                        <div class="room-overlay"></div>
                                    </a>
                                    <div class="room-badge-status"><i class="fas fa-circle text-white me-1" style="font-size: 8px; vertical-align: middle;"></i> Còn trống</div>
                                    <div class="room-price-tag">
                                        {{ number_format($room->price, 0, ',', '.') }} đ <span>/ tháng</span>
                                    </div>
                                </div>
                                
                                <div class="room-body">
                                    <h3 class="room-title">
                                        <a href="{{ route('room.show', $room->id) }}">Phòng {{ $room->name }}</a>
                                    </h3>
                                    
                                    <div class="room-features">
                                        <div class="r-feature"><i class="fas fa-vector-square"></i> {{ $room->area }} m²</div>
                                        <div class="r-feature"><i class="fas fa-users"></i> Tối đa {{ $room->max_occupants }}</div>
                                        <div class="r-feature"><i class="fas fa-layer-group"></i> Tầng {{ $room->floor }}</div>
                                        @if($room->roomType)
                                            <div class="r-feature text-truncate"><i class="fas fa-bed"></i> {{ $room->roomType->name }}</div>
                                        @endif
                                    </div>
                                    
                                    @if($room->house)
                                        <p class="text-muted small mb-4 text-truncate"><i class="fas fa-map-marker-alt text-danger me-2"></i>{{ $room->house->name }} - {{ $room->house->address }}</p>
                                    @endif

                                    <a href="{{ route('room.show', $room->id) }}" class="btn btn-view-room d-block text-center text-decoration-none">
                                        Xem Chi Tiết Phòng <i class="fas fa-arrow-right ms-2"></i>
                                    </a>

                                    @if($room->status === 'available')
                                        @auth
                                            <a href="{{ route('booking.create', ['room_id' => $room->id]) }}" class="btn btn-explore d-block text-center text-decoration-none mt-2 text-white">
                                                <i class="fas fa-file-signature me-2"></i>Yêu cầu đặt thuê
                                            </a>
                                        @else
                                            <a href="{{ route('login') . '?redirect=' . urlencode(route('room.show', $room->id)) }}" class="btn btn-explore d-block text-center text-decoration-none mt-2 text-white">
                                                <i class="fas fa-file-signature me-2"></i>Yêu cầu đặt thuê
                                            </a>
                                        @endauth
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                
                <div class="mt-5 d-flex justify-content-center" data-aos="fade-up">
                    {{ $rooms->links() }}
                </div>
            @else
                <div class="text-center py-5" data-aos="zoom-in">
                    <img src="https://cdn-icons-png.flaticon.com/512/7486/7486744.png" width="120" class="mb-4 opacity-50" alt="Empty">
                    <h3 class="fw-bold text-dark mb-3">Cháy Phòng!</h3>
                    <p class="text-muted fs-5">Hiện tại tất cả các phòng đã được lấp đầy. Rất cảm ơn sự ủng hộ của quý khách.<br>Vui lòng liên hệ Hotline để xếp hàng chờ đợt sau.</p>
                </div>
            @endif
        </div>
    </section>

    <!-- AMENITIES -->
    <section id="tien-ich" class="amenities-section">
        <div class="container">
            <div class="section-header" data-aos="fade-up">
                <h2>Tiện Ích Nổi Bật</h2>
                <p>Chúng tôi hiểu bạn cần gì. Một không gian sống chuẩn mực với đầy đủ tiện ích đáp ứng mọi nhu cầu sinh hoạt hàng ngày.</p>
            </div>
            
            <div class="row g-4">
                <div class="col-md-6" data-aos="fade-right">
                    <div class="amenity-card">
                        <div class="amenity-icon"><i class="fas fa-fingerprint"></i></div>
                        <div class="amenity-info">
                            <h4>Khóa Vân Tay Thông Minh</h4>
                            <p class="text-muted mb-0">Ra vào tự do 24/7 không cần chìa khóa rườm rà. Hệ thống cửa cuốn an ninh kép chống trộm tuyệt đối.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6" data-aos="fade-left">
                    <div class="amenity-card">
                        <div class="amenity-icon"><i class="fas fa-video"></i></div>
                        <div class="amenity-info">
                            <h4>Camera Giám Sát 24/24</h4>
                            <p class="text-muted mb-0">Camera HD bao quát khu vực nhà xe, hành lang và cổng ra vào. Đảm bảo an toàn tài sản cho mọi cư dân.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6" data-aos="fade-right" data-aos-delay="100">
                    <div class="amenity-card">
                        <div class="amenity-icon"><i class="fas fa-parking"></i></div>
                        <div class="amenity-info">
                            <h4>Bãi Xe Rộng Rãi</h4>
                            <p class="text-muted mb-0">Khu vực để xe tầng trệt miễn phí, rộng rãi, dễ dàng dắt xe ra vào không sợ trầy xước.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6" data-aos="fade-left" data-aos-delay="100">
                    <div class="amenity-card">
                        <div class="amenity-icon"><i class="fas fa-tshirt"></i></div>
                        <div class="amenity-info">
                            <h4>Khu Vực Máy Giặt & Sân Phơi</h4>
                            <p class="text-muted mb-0">Sân thượng rộng rãi ngập nắng, trang bị máy giặt chung miễn phí giúp bạn tiết kiệm thời gian.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- TESTIMONIALS -->
    <section id="cam-nhan" class="testimonials">
        <div class="container position-relative z-2">
            <div class="section-header" data-aos="fade-up">
                <h2>Khách Hàng Nói Gì Về Chúng Tôi</h2>
                <p>Hơn 100+ khách hàng đã và đang sinh sống tại hệ thống đánh giá mức độ hài lòng tuyệt đối.</p>
            </div>
            
            <div class="row">
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="review-card">
                        <div class="stars"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
                        <p class="review-text">"Phòng ở đây rất mát mẻ và sạch sẽ. Điểm tôi thích nhất là dùng khóa vân tay nên đi làm về khuya rất tiện, không sợ phiền ai."</p>
                        <div class="reviewer">
                            <img src="https://randomuser.me/api/portraits/women/44.jpg" alt="User">
                            <div class="reviewer-info">
                                <h5>Nguyễn Thu Trang</h5>
                                <span>Nhân viên văn phòng</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="review-card">
                        <div class="stars"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
                        <p class="review-text">"Quản lý rất nhiệt tình. Hôm trước vòi nước bị rỉ, báo app cái là buổi chiều có chú thợ qua sửa ngay lập tức. Rất an tâm."</p>
                        <div class="reviewer">
                            <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="User">
                            <div class="reviewer-info">
                                <h5>Trần Hải Đăng</h5>
                                <span>Sinh viên ĐH KHTN</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="review-card">
                        <div class="stars"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star-half-alt"></i></div>
                        <p class="review-text">"Hệ thống app quản lý quá xịn xò. Đóng tiền nhà chuyển khoản xong là tự động gạch nợ trên app, hóa đơn tiền điện nước cũng rõ ràng minh bạch."</p>
                        <div class="reviewer">
                            <img src="https://randomuser.me/api/portraits/women/68.jpg" alt="User">
                            <div class="reviewer-info">
                                <h5>Lê Ngọc Mai</h5>
                                <span>Thiết kế tự do</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="cta-section">
        <div class="cta-bg-shape"></div>
        <div class="container cta-content" data-aos="zoom-in">
            <h2>Bạn Đã Sẵn Sàng Trở Thành Cư Dân Mới?</h2>
            <p>Đừng chần chừ, hãy liên hệ ngay hôm nay để nhận ưu đãi giảm 50% tiền cọc tháng đầu tiên.</p>
            @if($zaloNumber)
                <a href="https://zalo.me/{{ preg_replace('/[^0-9]/', '', $zaloNumber) }}" target="_blank" class="btn-cta">
                    <i class="fas fa-paper-plane me-2"></i> Liên Hệ Giữ Chỗ Ngay
                </a>
            @endif
        </div>
    </section>

    <!-- Zalo Widget -->
    @if($zaloNumber)
        <a href="https://zalo.me/{{ preg_replace('/[^0-9]/', '', $zaloNumber) }}" target="_blank" class="zalo-widget" title="Chat qua Zalo">
            <img src="https://upload.wikimedia.org/wikipedia/commons/9/91/Icon_of_Zalo.svg" alt="Zalo">
        </a>
    @endif

    <!-- FOOTER -->
    <footer>
        <div class="container">
            <div class="row mb-5">
                <div class="col-lg-4 mb-4 mb-lg-0">
                    <div class="footer-brand"><i class="fas fa-building me-2"></i>Boarding<span>Pro</span></div>
                    <p class="footer-desc">Hệ thống quản lý phòng trọ tiên phong áp dụng công nghệ số, mang đến không gian sống tiện nghi, hiện đại và quy trình vận hành minh bạch nhất cho người Việt.</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-tiktok"></i></a>
                        <a href="#"><i class="fab fa-youtube"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4 mb-md-0 mx-auto">
                    <h5 class="footer-title">Khám Phá</h5>
                    <ul class="list-unstyled footer-links">
                        <li><a href="#quy-trinh">Quy trình thuê phòng</a></li>
                        <li><a href="#danh-sach-phong">Danh sách phòng trống</a></li>
                        <li><a href="#tien-ich">Hệ thống tiện ích</a></li>
                        <li><a href="#cam-nhan">Khách hàng đánh giá</a></li>
                        <li><a href="{{ route('login') }}">Đăng nhập cư dân</a></li>
                    </ul>
                </div>
                <div class="col-lg-4 col-md-6">
                    <h5 class="footer-title">Liên Hệ Trực Tiếp</h5>
                    <ul class="list-unstyled contact-list text-muted">
                        <li>
                            <i class="fas fa-map-marker-alt"></i>
                            <div><strong>Văn phòng:</strong> Tòa nhà Landmark 81, Vinhomes Central Park, Q.Bình Thạnh, TP.HCM</div>
                        </li>
                        @if($zaloNumber)
                        <li>
                            <i class="fas fa-phone-alt"></i>
                            <div><strong>Hotline / Zalo:</strong> <br> <span class="text-white fs-5 fw-bold">{{ $zaloNumber }}</span></div>
                        </li>
                        @endif
                        @if(\App\Models\Setting::get('contact_email'))
                        <li>
                            <i class="fas fa-envelope"></i>
                            <div><strong>Email:</strong> <br> {{ \App\Models\Setting::get('contact_email') }}</div>
                        </li>
                        @endif
                    </ul>
                </div>
            </div>
            <div class="copyright">
                <p class="mb-0">&copy; {{ date('Y') }} Bản quyền thuộc về <strong>{{ \App\Models\Setting::get('site_name', 'BoardingPro') }}</strong>. Nền tảng quản lý lưu trú số 1 Việt Nam.</p>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        // Initialize AOS animations
        AOS.init({
            once: true,
            offset: 50,
            duration: 800,
            easing: 'ease-out-cubic',
        });

        // Navbar scrolled effect
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });
    </script>
</body>
</html>
