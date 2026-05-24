<x-app-layout>
    <x-slot name="header">
        <h4 class="fw-bold mb-0 text-dark" style="letter-spacing: -0.5px;">Trang Chủ Khách Thuê</h4>
    </x-slot>

    <style>
        /* Modern Premium CSS Enhancements */
        .premium-card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.03);
            transition: all 0.3s ease;
            overflow: hidden;
            background: #fff;
        }
        .premium-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.08);
        }

        /* Animated Welcome Banner */
        .welcome-banner {
            background: linear-gradient(-45deg, #4f46e5, #7c3aed, #3b82f6, #ec4899);
            background-size: 400% 400%;
            animation: gradientBG 10s ease infinite;
            border-radius: 24px;
            color: #fff;
            position: relative;
            overflow: hidden;
            border: none;
        }
        .welcome-banner::before {
            content: '';
            position: absolute;
            top: 0; right: 0; bottom: 0; left: 0;
            background: radial-gradient(circle at top right, rgba(255,255,255,0.2) 0%, transparent 60%);
        }
        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* Room Detail Card */
        .room-card-bg {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            color: #f8fafc;
        }
        .room-card-bg .text-muted {
            color: #94a3b8 !important;
        }
        .room-card-bg .card-header {
            border-bottom: 1px solid rgba(255,255,255,0.1);
            background: transparent;
        }
        .room-stat-item {
            padding: 12px 16px;
            background: rgba(255,255,255,0.05);
            border-radius: 12px;
            margin-bottom: 12px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.05);
            transition: background 0.3s;
        }
        .room-stat-item:hover {
            background: rgba(255,255,255,0.1);
        }

        /* Alerts */
        .alert-premium-danger {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            border: 1px solid #f87171;
            border-radius: 16px;
            color: #991b1b;
            box-shadow: 0 4px 15px rgba(239, 68, 68, 0.2);
        }
        .alert-premium-success {
            background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
            border: 1px solid #4ade80;
            border-radius: 16px;
            color: #166534;
            box-shadow: 0 4px 15px rgba(34, 197, 94, 0.2);
        }

        /* Announcement List */
        .announcement-item {
            transition: all 0.2s;
            border-left: 4px solid transparent;
        }
        .announcement-item:hover {
            background: #f8fafc;
            border-left-color: #3b82f6;
        }
        .announcement-item.pinned {
            background: #fffbeb;
            border-left-color: #f59e0b;
        }
        
        .icon-circle {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }

        /* Badges */
        .badge-modern {
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.75rem;
        }
    </style>

    {{-- Welcome banner --}}
    <div class="card welcome-banner mb-4 shadow-lg">
        <div class="card-body p-4 p-md-5 d-flex align-items-center">
            <div class="me-4 d-none d-md-block">
                <div class="bg-white rounded-circle p-3 shadow-sm d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                    <i class="fas fa-user-astronaut text-primary fs-1"></i>
                </div>
            </div>
            <div>
                <h3 class="fw-bold mb-2 text-white" style="text-shadow: 0 2px 4px rgba(0,0,0,0.2);">Xin chào, {{ Auth::user()->name }}! 👋</h3>
                <p class="mb-0 fs-6 text-white-50">Chào mừng bạn trở lại nền tảng quản lý phòng trọ thông minh.</p>
            </div>
        </div>
    </div>

    @if(!$contract)
        <div class="premium-card text-center py-5">
            <div class="card-body">
                <div class="mb-4">
                    <div class="icon-circle bg-warning bg-opacity-10 text-warning mx-auto" style="width: 80px; height: 80px; font-size: 2.5rem;">
                        <i class="fas fa-house-user"></i>
                    </div>
                </div>
                <h4 class="fw-bold text-dark">Bạn chưa có phòng</h4>
                <p class="text-muted mb-4 max-w-md mx-auto">Hệ thống không tìm thấy hợp đồng thuê phòng nào đang có hiệu lực đối với tài khoản của bạn. Vui lòng liên hệ ban quản lý nếu có sai sót.</p>
                <a href="https://zalo.me" target="_blank" class="btn btn-primary btn-round px-4 py-2">
                    <i class="fas fa-headset me-2"></i> Liên hệ Quản lý
                </a>
            </div>
        </div>
    @else

    {{-- Debt alert --}}
    @if($unpaidAmount > 0)
    <div class="alert alert-premium-danger d-flex flex-column flex-md-row justify-content-between align-items-md-center p-4 mb-4">
        <div class="d-flex align-items-center mb-3 mb-md-0">
            <div class="icon-circle bg-white text-danger me-3 shadow-sm">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div>
                <h5 class="fw-bold mb-1">Cần Thanh Toán!</h5>
                <p class="mb-0 text-danger opacity-75">Bạn đang có hóa đơn chưa thanh toán với tổng dư nợ: <strong>{{ number_format($unpaidAmount,0,',','.')}} VNĐ</strong>.</p>
            </div>
        </div>
        <a href="{{ route('tenant.invoices.index') }}" class="btn btn-danger btn-round px-4 py-2 shadow-sm" style="white-space: nowrap;">
            <i class="fas fa-credit-card me-2"></i>Thanh toán ngay
        </a>
    </div>
    @else
    <div class="alert alert-premium-success d-flex align-items-center p-4 mb-4">
        <div class="icon-circle bg-white text-success me-3 shadow-sm">
            <i class="fas fa-check-double"></i>
        </div>
        <div>
            <h5 class="fw-bold mb-1">Tuyệt vời!</h5>
            <p class="mb-0 text-success opacity-75">Bạn đã thanh toán đầy đủ tất cả các khoản phí. Cảm ơn bạn!</p>
        </div>
    </div>
    @endif

    <div class="row g-4">
        {{-- Left: Room Info --}}
        <div class="col-lg-4">
            <div class="premium-card room-card-bg h-100">
                <div class="card-header border-0 pt-4 pb-0">
                    <h5 class="card-title fw-bold text-white"><i class="fas fa-door-open text-info me-2"></i> Phòng Đang Ở</h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4 mt-2">
                        <div class="d-inline-block bg-white bg-opacity-10 rounded-circle p-3 mb-3">
                            <i class="fas fa-home text-white fs-1"></i>
                        </div>
                        <h2 class="text-white fw-bold mb-1" style="letter-spacing: 1px;">P.{{ $contract->room->name }}</h2>
                        <h6 class="text-info mb-1">{{ $contract->room->house->name }}</h6>
                        <small class="text-muted"><i class="fas fa-map-marker-alt me-1"></i> {{ $contract->room->house->address }}</small>
                    </div>
                    
                    <div class="room-stat-item d-flex justify-content-between align-items-center">
                        <span class="text-muted"><i class="fas fa-money-bill-wave me-2"></i> Giá thuê</span>
                        <span class="fw-bold text-white fs-5">{{ number_format($contract->monthly_price,0,',','.')}}<small class="fw-normal fs-6">đ</small></span>
                    </div>
                    
                    <div class="room-stat-item d-flex justify-content-between align-items-center">
                        <span class="text-muted"><i class="fas fa-shield-alt me-2"></i> Tiền cọc</span>
                        <span class="fw-semibold text-white">{{ number_format($contract->deposit,0,',','.')}}đ</span>
                    </div>
                    
                    <div class="room-stat-item d-flex justify-content-between align-items-center">
                        <span class="text-muted"><i class="fas fa-calendar-alt me-2"></i> Hợp đồng</span>
                        <span class="fw-semibold text-white small text-end">
                            {{ \Carbon\Carbon::parse($contract->start_date)->format('d/m/Y') }} <br>
                            <span class="text-muted">đến</span> {{ \Carbon\Carbon::parse($contract->end_date)->format('d/m/Y') }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right: Announcements + Invoices --}}
        <div class="col-lg-8">
            <div class="d-flex flex-column gap-4 h-100">
                
                {{-- Announcements --}}
                <div class="premium-card flex-grow-1">
                    <div class="card-header bg-white border-bottom-0 pt-4 pb-2">
                        <h5 class="card-title fw-bold text-dark"><i class="fas fa-bullhorn text-warning me-2"></i> Bảng Tin & Thông Báo</h5>
                    </div>
                    <div class="card-body p-0">
                        @forelse($announcements as $announcement)
                        <div class="announcement-item p-4 border-bottom {{ $announcement->is_pinned ? 'pinned' : '' }}">
                            <div class="d-flex gap-3">
                                <div class="flex-shrink-0">
                                    @if($announcement->type=='notice')
                                        <div class="icon-circle bg-info bg-opacity-10 text-info"><i class="fas fa-info"></i></div>
                                    @elseif($announcement->type=='warning')
                                        <div class="icon-circle bg-danger bg-opacity-10 text-danger"><i class="fas fa-exclamation"></i></div>
                                    @else
                                        <div class="icon-circle bg-primary bg-opacity-10 text-primary"><i class="fas fa-calendar"></i></div>
                                    @endif
                                </div>
                                <div>
                                    <h6 class="fw-bold text-dark mb-1 d-flex align-items-center">
                                        {{ $announcement->title }}
                                        @if($announcement->is_pinned)
                                            <span class="badge bg-warning text-dark ms-2" style="font-size: 0.65rem;">Ghim</span>
                                        @endif
                                    </h6>
                                    <p class="small text-muted mb-2"><i class="far fa-clock me-1"></i> {{ \Carbon\Carbon::parse($announcement->published_at)->diffForHumans() }}</p>
                                    <p class="mb-0 text-dark opacity-75" style="white-space:pre-line; font-size: 0.9rem;">{{ $announcement->content }}</p>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="py-5 text-center">
                            <img src="https://cdn-icons-png.flaticon.com/512/4076/4076432.png" alt="No Announcements" style="width: 80px; opacity: 0.2; margin-bottom: 15px;">
                            <p class="text-muted mb-0">Không có thông báo mới nào từ ban quản lý.</p>
                        </div>
                        @endforelse
                    </div>
                </div>

                {{-- Recent Invoices --}}
                <div class="premium-card">
                    <div class="card-header bg-white border-bottom-0 pt-4 pb-2 d-flex justify-content-between align-items-center">
                        <h5 class="card-title fw-bold text-dark mb-0"><i class="fas fa-file-invoice-dollar text-success me-2"></i> Hóa Đơn Gần Đây</h5>
                        <a href="{{ route('tenant.invoices.index') }}" class="btn btn-sm btn-outline-primary btn-round">Xem tất cả</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-borderless align-middle mb-0">
                                <thead class="table-light text-muted small text-uppercase">
                                    <tr>
                                        <th class="ps-4 py-3">Kỳ Thu</th>
                                        <th class="text-end py-3">Tổng Tiền</th>
                                        <th class="text-end py-3">Còn Nợ</th>
                                        <th class="text-center pe-4 py-3">Trạng thái</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($invoices as $invoice)
                                    <tr class="border-bottom">
                                        <td class="ps-4 py-3">
                                            <div class="fw-bold text-dark">Tháng {{ $invoice->month }}/{{ $invoice->year }}</div>
                                            <div class="small text-muted"><i class="far fa-calendar-alt me-1"></i> Hạn: {{ \Carbon\Carbon::parse($invoice->due_date)->format('d/m/Y') }}</div>
                                        </td>
                                        <td class="text-end fw-bold text-dark py-3">{{ number_format($invoice->total,0,',','.')}}đ</td>
                                        <td class="text-end fw-bold {{ $invoice->debt > 0 ? 'text-danger' : 'text-success' }} py-3">
                                            {{ number_format($invoice->debt,0,',','.')}}đ
                                        </td>
                                        <td class="text-center pe-4 py-3">
                                            @if($invoice->status=='unpaid')     
                                                <span class="badge-modern bg-secondary text-white">Chưa đóng</span>
                                            @elseif($invoice->status=='partial') 
                                                <span class="badge-modern bg-warning text-dark">Đóng thiếu</span>
                                            @elseif($invoice->status=='paid')    
                                                <span class="badge-modern bg-success text-white">Hoàn tất</span>
                                            @elseif($invoice->status=='overdue') 
                                                <span class="badge-modern bg-danger text-white">Quá hạn</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-5">
                                            <i class="fas fa-file-invoice fa-2x opacity-25 mb-3 d-block"></i>
                                            Bạn chưa có hóa đơn nào
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
    @endif
</x-app-layout>
