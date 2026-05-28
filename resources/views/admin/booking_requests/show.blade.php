<x-app-layout>
    <x-slot name="header">Yêu cầu đặt phòng #{{ $booking->id }}</x-slot>

    @php
        $statusMap = [
            'pending'   => ['label' => 'Chờ duyệt',    'class' => 'bg-warning text-dark'],
            'approved'  => ['label' => 'Đã duyệt',     'class' => 'bg-primary text-white'],
            'rejected'  => ['label' => 'Đã từ chối',   'class' => 'bg-danger text-white'],
            'cancelled' => ['label' => 'Đã hủy',       'class' => 'bg-secondary text-white'],
            'expired'   => ['label' => 'Hết hạn',      'class' => 'bg-dark text-white'],
        ];
        $eventMap = [
            'created'            => 'Đã tạo yêu cầu',
            'approved'           => 'Admin duyệt yêu cầu',
            'rejected'           => 'Admin từ chối',
            'cancelled'          => 'Khách hàng hủy',
            'expired'            => 'Hết hạn giữ chỗ',
            'documents_uploaded' => 'Đã upload giấy tờ',
            'signed'             => 'Đã ký hợp đồng',
            'deposit_paid'       => 'Đã thanh toán cọc',
        ];
        $st = $statusMap[$booking->status] ?? ['label' => $booking->status, 'class' => 'bg-secondary text-white'];
    @endphp

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card card-round mb-3">
                <div class="card-header"><div class="card-title">Thông tin khách hàng</div></div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-md-6"><strong>Họ tên:</strong> {{ optional($booking->user)->name }}</div>
                        <div class="col-md-6"><strong>Email:</strong> {{ optional($booking->user)->email }}</div>
                        <div class="col-md-6"><strong>SĐT:</strong> {{ $booking->phone }}</div>
                        <div class="col-md-6"><strong>CCCD:</strong> {{ $booking->cccd }}</div>
                        <div class="col-md-6"><strong>Ngày sinh:</strong> {{ optional($booking->birthday)->format('d/m/Y') ?: '—' }}</div>
                        <div class="col-md-6"><strong>Giới tính:</strong>
                            @switch($booking->gender) @case('male')Nam @break @case('female')Nữ @break @case('other')Khác @break @default — @endswitch
                        </div>
                        <div class="col-md-6"><strong>Quê quán:</strong> {{ $booking->hometown ?: '—' }}</div>
                        <div class="col-md-6"><strong>Địa chỉ:</strong> {{ $booking->address ?: '—' }}</div>
                    </div>

                    @if($booking->tenant && ($booking->tenant->cccd_front_path || $booking->tenant->cccd_back_path))
                        <hr>
                        <h6>Ảnh CCCD đã upload</h6>
                        <div class="row g-2">
                            @if($booking->tenant->cccd_front_path)
                                <div class="col-md-6"><img src="{{ \Illuminate\Support\Facades\Storage::url($booking->tenant->cccd_front_path) }}" class="img-fluid rounded border"><small class="text-muted d-block">Mặt trước</small></div>
                            @endif
                            @if($booking->tenant->cccd_back_path)
                                <div class="col-md-6"><img src="{{ \Illuminate\Support\Facades\Storage::url($booking->tenant->cccd_back_path) }}" class="img-fluid rounded border"><small class="text-muted d-block">Mặt sau</small></div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            <div class="card card-round mb-3">
                <div class="card-header"><div class="card-title">Thông tin phòng</div></div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-md-6"><strong>Phòng:</strong> {{ optional($booking->room)->name }}</div>
                        <div class="col-md-6"><strong>Khu trọ:</strong> {{ optional(optional($booking->room)->house)->name }}</div>
                        <div class="col-12"><strong>Địa chỉ:</strong> {{ optional(optional($booking->room)->house)->address }}</div>
                        <div class="col-md-4"><strong>Giá thuê:</strong> {{ number_format(optional($booking->room)->price, 0, ',', '.') }} đ/tháng</div>
                        <div class="col-md-4"><strong>Sức chứa:</strong> {{ optional($booking->room)->max_occupants }} người</div>
                        <div class="col-md-4"><strong>Diện tích:</strong> {{ optional($booking->room)->area }} m²</div>
                    </div>
                </div>
            </div>

            <div class="card card-round mb-3">
                <div class="card-header"><div class="card-title">Yêu cầu thuê</div></div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-md-4"><strong>Ngày chuyển vào:</strong> {{ optional($booking->desired_move_in_date)->format('d/m/Y') }}</div>
                        <div class="col-md-4"><strong>Số người ở:</strong> {{ $booking->desired_occupants }}</div>
                        <div class="col-md-4"><strong>Thời hạn:</strong> {{ $booking->desired_lease_months }} tháng</div>
                        @if($booking->customer_note)
                            <div class="col-12"><strong>Ghi chú khách:</strong><div class="text-muted">{{ $booking->customer_note }}</div></div>
                        @endif
                    </div>
                </div>
            </div>

            @if($booking->admin_note || $booking->rejected_reason || $booking->deposit_amount)
                <div class="card card-round mb-3">
                    <div class="card-header"><div class="card-title">Ghi chú admin</div></div>
                    <div class="card-body">
                        @if($booking->deposit_amount)
                            <p class="mb-2"><strong>Tiền cọc đã thiết lập:</strong> {{ number_format($booking->deposit_amount, 0, ',', '.') }} đ</p>
                        @endif
                        @if($booking->admin_note)
                            <p class="mb-2"><strong>Ghi chú:</strong> {{ $booking->admin_note }}</p>
                        @endif
                        @if($booking->rejected_reason)
                            <div class="alert alert-danger mb-0"><strong>Lý do từ chối:</strong> {{ $booking->rejected_reason }}</div>
                        @endif
                    </div>
                </div>
            @endif

            <div class="card card-round">
                <div class="card-header"><div class="card-title">Lịch sử (Audit log)</div></div>
                <div class="card-body p-0">
                    @if($booking->audits->isEmpty())
                        <div class="p-3 text-muted">Chưa có sự kiện nào.</div>
                    @else
                        <ul class="list-group list-group-flush">
                            @foreach($booking->audits->sortByDesc('created_at') as $a)
                                <li class="list-group-item d-flex justify-content-between">
                                    <span><i class="fas fa-circle text-primary me-2" style="font-size:8px;vertical-align:middle"></i>{{ $eventMap[$a->event] ?? $a->event }} <small class="text-muted ms-2">— {{ optional($a->actor)->name ?? 'Hệ thống' }}</small></span>
                                    <small class="text-muted">{{ $a->created_at?->format('d/m/Y H:i') }}</small>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card card-round mb-3">
                <div class="card-body text-center">
                    <p class="mb-2 text-muted small">Trạng thái hiện tại</p>
                    <span class="badge {{ $st['class'] }} px-4 py-2 fs-6">{{ $st['label'] }}</span>

                    @if($booking->status !== 'pending')
                        <hr>
                        <div class="text-start small">
                            @if($booking->approved_at)<div><strong>Duyệt lúc:</strong> {{ $booking->approved_at->format('d/m/Y H:i') }}</div>@endif
                            @if($booking->rejected_at)<div><strong>Từ chối lúc:</strong> {{ $booking->rejected_at->format('d/m/Y H:i') }}</div>@endif
                            @if($booking->cancelled_at)<div><strong>Hủy lúc:</strong> {{ $booking->cancelled_at->format('d/m/Y H:i') }}</div>@endif
                            @if($booking->expired_at)<div><strong>Hết hạn lúc:</strong> {{ $booking->expired_at->format('d/m/Y H:i') }}</div>@endif
                            @if($booking->deposit_paid_at)<div><strong>Đặt cọc lúc:</strong> {{ $booking->deposit_paid_at->format('d/m/Y H:i') }}</div>@endif
                        </div>
                    @endif
                </div>
            </div>

            @if($booking->status === 'pending')
                <div class="card card-round mb-3">
                    <div class="card-header"><div class="card-title">Duyệt yêu cầu</div></div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('admin.booking-requests.approve', $booking) }}" onsubmit="return confirm('Xác nhận duyệt yêu cầu này?')">
                            @csrf
                            <div class="mb-2">
                                <label class="form-label small mb-0">Tiền cọc (VNĐ)</label>
                                <input type="number" name="deposit_amount" class="form-control form-control-sm" value="{{ optional($booking->room)->price }}" min="0" required>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small mb-0">Ngày bắt đầu</label>
                                <input type="date" name="start_date" class="form-control form-control-sm" value="{{ optional($booking->desired_move_in_date)->toDateString() ?? now()->toDateString() }}" required>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small mb-0">Ngày kết thúc</label>
                                <input type="date" name="end_date" class="form-control form-control-sm" value="{{ optional($booking->desired_move_in_date)->copy()->addMonths($booking->desired_lease_months)->toDateString() ?? now()->addMonths(12)->toDateString() }}" required>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small mb-0">Ghi chú</label>
                                <textarea name="admin_note" class="form-control form-control-sm" rows="2"></textarea>
                            </div>
                            <button class="btn btn-success btn-sm w-100"><i class="fas fa-check me-1"></i>Duyệt</button>
                        </form>
                    </div>
                </div>

                <div class="card card-round">
                    <div class="card-header"><div class="card-title">Từ chối</div></div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('admin.booking-requests.reject', $booking) }}" onsubmit="return confirm('Xác nhận từ chối yêu cầu này?')">
                            @csrf
                            <div class="mb-2">
                                <label class="form-label small mb-0">Lý do từ chối <span class="text-danger">*</span></label>
                                <textarea name="rejected_reason" class="form-control form-control-sm" rows="3" minlength="10" required placeholder="Tối thiểu 10 ký tự..."></textarea>
                            </div>
                            <button class="btn btn-danger btn-sm w-100"><i class="fas fa-times me-1"></i>Từ chối</button>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
