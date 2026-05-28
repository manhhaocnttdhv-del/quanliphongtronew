<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Phòng {{ $room->name }} - {{ \App\Models\Setting::get('site_name', 'Hệ Thống Quản Lý Phòng Trọ') }}</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
            color: #333;
        }

        .navbar-brand {
            font-weight: 700;
            color: #4f46e5 !important;
            font-size: 1.5rem;
        }

        .breadcrumb {
            background-color: transparent;
            padding: 0;
            margin-bottom: 2rem;
        }

        .breadcrumb-item a {
            color: #4f46e5;
            text-decoration: none;
        }

        .room-gallery {
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            background: white;
            padding: 20px;
        }

        .main-image {
            width: 100%;
            height: 400px;
            object-fit: cover;
            border-radius: 12px;
            margin-bottom: 15px;
        }

        .thumbnail-container {
            display: flex;
            gap: 10px;
            overflow-x: auto;
            padding-bottom: 10px;
        }

        .thumbnail {
            width: 100px;
            height: 70px;
            object-fit: cover;
            border-radius: 8px;
            cursor: pointer;
            opacity: 0.6;
            transition: opacity 0.2s;
        }

        .thumbnail:hover, .thumbnail.active {
            opacity: 1;
            border: 2px solid #4f46e5;
        }

        .room-details-card {
            background: white;
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            height: 100%;
        }

        .room-title {
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: 10px;
            color: #1f2937;
        }

        .room-price {
            font-size: 2rem;
            font-weight: 700;
            color: #ef4444;
            margin-bottom: 20px;
        }

        .feature-item {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            font-size: 1.1rem;
            color: #4b5563;
        }

        .feature-item i {
            width: 30px;
            color: #4f46e5;
            font-size: 1.2rem;
        }

        .description-box {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
        }

        .btn-contact {
            background-color: #4f46e5;
            color: white;
            border: none;
            border-radius: 12px;
            padding: 15px;
            font-weight: 600;
            font-size: 1.1rem;
            transition: background 0.2s;
            width: 100%;
            display: block;
            text-align: center;
            text-decoration: none;
            margin-top: 20px;
        }

        .btn-contact:hover {
            background-color: #4338ca;
            color: white;
        }

        footer {
            background-color: #1f2937;
            color: #9ca3af;
            padding: 30px 0;
            text-align: center;
            margin-top: 60px;
        }
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white py-3 shadow-sm mb-5">
        <div class="container">
            <a class="navbar-brand" href="/">
                <i class="fas fa-home me-2"></i> {{ \App\Models\Setting::get('site_name', 'BoardingPro') }}
            </a>
            <div class="d-flex">
                <a href="/" class="btn btn-outline-primary rounded-pill px-4">
                    <i class="fas fa-arrow-left me-2"></i> Quay lại
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container pb-5">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/">Trang chủ</a></li>
                <li class="breadcrumb-item"><a href="/#danh-sach-phong">Danh sách phòng</a></li>
                <li class="breadcrumb-item active" aria-current="page">Phòng {{ $room->name }}</li>
            </ol>
        </nav>

        <div class="row g-4">
            <!-- Left Column: Gallery -->
            <div class="col-lg-7">
                <div class="room-gallery">
                    @php
                        $images = [];
                        if(!empty($room->images) && is_array($room->images) && count($room->images) > 0) {
                            foreach($room->images as $img) {
                                $images[] = Str::startsWith($img, ['http://', 'https://']) ? $img : Storage::url($img);
                            }
                        } elseif(!empty($room->image_path)) {
                            $images[] = Str::startsWith($room->image_path, ['http://', 'https://']) ? $room->image_path : Storage::url($room->image_path);
                        } else {
                            $images[] = 'https://via.placeholder.com/800x600?text=Phong+Tro';
                        }
                    @endphp

                    <img id="mainImage" src="{{ $images[0] }}" class="main-image" alt="Hình ảnh phòng {{ $room->name }}">
                    
                    @if(count($images) > 1)
                        <div class="thumbnail-container">
                            @foreach($images as $index => $img)
                                <img src="{{ $img }}" class="thumbnail {{ $index === 0 ? 'active' : '' }}" onclick="changeImage(this, '{{ $img }}')" alt="Thumbnail {{ $index + 1 }}">
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <!-- Right Column: Details -->
            <div class="col-lg-5">
                <div class="room-details-card">
                    @if($room->status == 'available')
                        <span class="badge bg-success mb-3 px-3 py-2 fs-6 rounded-pill">Còn trống</span>
                    @elseif($room->status == 'reserved')
                        <span class="badge bg-warning text-dark mb-3 px-3 py-2 fs-6 rounded-pill">Đã giữ chỗ</span>
                    @elseif($room->status == 'rented')
                        <span class="badge bg-danger mb-3 px-3 py-2 fs-6 rounded-pill">Đã thuê</span>
                    @else
                        <span class="badge bg-secondary mb-3 px-3 py-2 fs-6 rounded-pill">Đang sửa</span>
                    @endif

                    <h1 class="room-title">Phòng {{ $room->name }}</h1>
                    <div class="room-price">{{ number_format($room->price, 0, ',', '.') }} đ <span class="fs-5 text-muted fw-normal">/ tháng</span></div>

                    <div class="mt-4">
                        <div class="feature-item">
                            <i class="fas fa-layer-group"></i> 
                            <span><strong>Diện tích:</strong> {{ $room->area }} m²</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-building"></i> 
                            <span><strong>Tầng:</strong> Tầng {{ $room->floor }}</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-users"></i> 
                            <span><strong>Tối đa:</strong> {{ $room->max_occupants }} người</span>
                        </div>
                        
                        @if($room->roomType)
                        <div class="feature-item">
                            <i class="fas fa-bed"></i> 
                            <span><strong>Loại phòng:</strong> {{ $room->roomType->name }}</span>
                        </div>
                        @endif

                        @if($room->house)
                        <div class="feature-item align-items-start">
                            <i class="fas fa-map-marker-alt mt-1"></i> 
                            <div>
                                <strong>Khu trọ:</strong> {{ $room->house->name }}<br>
                                <span class="text-muted fs-6">{{ $room->house->address }}</span>
                            </div>
                        </div>
                        @endif
                    </div>

                    <div class="description-box">
                        <h4 class="fw-bold mb-3">Mô tả thêm</h4>
                        <p class="text-muted lh-lg">
                            {{ $room->description ?: 'Chưa có mô tả chi tiết cho phòng này. Tuy nhiên đây là một phòng sạch sẽ, thoáng mát, khu vực an ninh tốt, giờ giấc tự do, có chỗ để xe rộng rãi.' }}
                        </p>
                    </div>

                    @if($room->status == 'available')
                        @auth
                            <a href="{{ route('booking.create', ['room_id' => $room->id]) }}" class="btn-contact">
                                <i class="fas fa-file-signature me-2"></i> Yêu cầu đặt thuê
                            </a>
                        @else
                            <a href="{{ route('login') . '?redirect=' . urlencode(url()->current()) }}" class="btn-contact">
                                <i class="fas fa-file-signature me-2"></i> Yêu cầu đặt thuê
                            </a>
                        @endauth

                        @if($zaloNumber)
                            <a href="https://zalo.me/{{ preg_replace('/[^0-9]/', '', $zaloNumber) }}?text=Chào bạn, tôi muốn hỏi thuê Phòng {{ $room->name }} - {{ $room->house ? $room->house->name : '' }}" target="_blank" class="btn-contact" style="background-color:#0068ff;">
                                <i class="fas fa-comment-dots me-2"></i> Liên hệ qua Zalo
                            </a>
                        @endif
                    @else
                        @php
                            $statusLabels = [
                                'reserved' => 'Đã giữ chỗ',
                                'rented' => 'Đã thuê',
                                'maintenance' => 'Đang sửa',
                            ];
                            $statusLabel = $statusLabels[$room->status] ?? 'Không có sẵn';
                        @endphp
                        <button class="btn btn-secondary w-100 py-3 rounded-3 mt-4" disabled>
                            <i class="fas fa-ban me-2"></i> {{ $statusLabel }} - không thể đặt thuê
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <div class="container">
            <h5 class="text-white mb-3">{{ \App\Models\Setting::get('site_name', 'BoardingPro') }}</h5>
            <p class="mb-0 small">&copy; {{ date('Y') }} Bản quyền thuộc về {{ \App\Models\Setting::get('site_name', 'BoardingPro') }}.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function changeImage(element, src) {
            document.getElementById('mainImage').src = src;
            
            // Update active thumbnail
            const thumbnails = document.querySelectorAll('.thumbnail');
            thumbnails.forEach(thumb => thumb.classList.remove('active'));
            element.classList.add('active');
        }
    </script>
</body>
</html>
