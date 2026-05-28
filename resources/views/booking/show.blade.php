@extends('layouts.customer')

@section('title', 'Chi tiết yêu cầu #' . $booking->id)

@php
    $statusMap = [
        'pending'   => ['label' => 'Chờ duyệt',    'class' => 'bg-warning text-dark'],
        'approved'  => ['label' => 'Đã duyệt',     'class' => 'bg-primary'],
        'rejected'  => ['label' => 'Đã từ chối',   'class' => 'bg-danger'],
        'cancelled' => ['label' => 'Đã hủy',       'class' => 'bg-secondary'],
        'expired'   => ['label' => 'Hết hạn',      'class' => 'bg-dark'],
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
    $st = $statusMap[$booking->status] ?? ['label' => $booking->status, 'class' => 'bg-secondary'];
@endphp

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="mb-0">Yêu cầu #{{ $booking->id }} <span class="badge badge-status {{ $st['class'] }} ms-2">{{ $st['label'] }}</span></h2>
    <a href="{{ route('booking.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Quay lại</a>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card card-c">
            <div class="card-body">
                <h5 class="mb-3"><i class="fas fa-home me-2 text-primary"></i>Thông tin phòng</h5>
                <p class="mb-1"><strong>{{ optional($booking->room)->name }}</strong></p>
                @if($booking->room && $booking->room->house)
                    <p class="text-muted mb-1">{{ $booking->room->house->name }} — {{ $booking->room->house->address }}</p>
                @endif
                <p class="mb-0"><i class="fas fa-tag text-warning me-1"></i>{{ number_format(optional($booking->room)->price, 0, ',', '.') }} đ/tháng</p>
            </div>
        </div>

        <div class="card card-c">
            <div class="card-body">
                <h5 class="mb-3"><i class="fas fa-id-card me-2 text-primary"></i>Thông tin khai báo</h5>
                <div class="row g-3">
                    <div class="col-md-6"><strong>CCCD:</strong> {{ $booking->cccd }}</div>
                    <div class="col-md-6"><strong>Điện thoại:</strong> {{ $booking->phone }}</div>
                    <div class="col-md-6"><strong>Ngày sinh:</strong> {{ optional($booking->birthday)->format('d/m/Y') ?: '—' }}</div>
                    <div class="col-md-6"><strong>Giới tính:</strong>
                        @switch($booking->gender) @case('male')Nam @break @case('female')Nữ @break @case('other')Khác @break @default — @endswitch
                    </div>
                    <div class="col-md-6"><strong>Quê quán:</strong> {{ $booking->hometown ?: '—' }}</div>
                    <div class="col-md-6"><strong>Địa chỉ:</strong> {{ $booking->address ?: '—' }}</div>
                    <div class="col-md-4"><strong>Ngày chuyển vào:</strong> {{ optional($booking->desired_move_in_date)->format('d/m/Y') }}</div>
                    <div class="col-md-4"><strong>Số người ở:</strong> {{ $booking->desired_occupants }}</div>
                    <div class="col-md-4"><strong>Thời hạn:</strong> {{ $booking->desired_lease_months }} tháng</div>
                    @if($booking->customer_note)
                        <div class="col-12"><strong>Ghi chú của bạn:</strong><div class="text-muted">{{ $booking->customer_note }}</div></div>
                    @endif
                </div>
            </div>
        </div>

        @if($booking->admin_note || $booking->rejected_reason || $booking->deposit_amount)
            <div class="card card-c">
                <div class="card-body">
                    <h5 class="mb-3"><i class="fas fa-user-shield me-2 text-primary"></i>Thông tin từ Admin</h5>
                    @if($booking->deposit_amount)
                        <p class="mb-2"><strong>Tiền cọc:</strong> <span class="text-danger fw-bold">{{ number_format($booking->deposit_amount, 0, ',', '.') }} đ</span></p>
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

        <div class="card card-c">
            <div class="card-body">
                <h5 class="mb-3"><i class="fas fa-history me-2 text-primary"></i>Lịch sử</h5>
                @if($booking->audits->isEmpty())
                    <p class="text-muted mb-0">Chưa có sự kiện nào.</p>
                @else
                    <ul class="list-group list-group-flush">
                        @foreach($booking->audits->sortByDesc('created_at') as $a)
                            <li class="list-group-item d-flex justify-content-between">
                                <span>
                                    <i class="fas fa-circle text-primary me-2" style="font-size:8px;vertical-align:middle"></i>
                                    {{ $eventMap[$a->event] ?? $a->event }}
                                    <small class="text-muted ms-2">— {{ optional($a->actor)->name ?? 'Hệ thống' }}</small>
                                </span>
                                <small class="text-muted">{{ $a->created_at?->format('d/m/Y H:i') }}</small>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card card-c">
            <div class="card-body">
                <h5 class="mb-3"><i class="fas fa-tasks me-2 text-primary"></i>Hành động</h5>

                @if($booking->status === 'pending')
                    <p class="text-muted small">Yêu cầu đang chờ admin duyệt. Bạn có thể hủy nếu cần.</p>
                    <form method="POST" action="{{ route('booking.cancel', $booking) }}" onsubmit="return confirm('Bạn chắc chắn muốn hủy yêu cầu này?')">
                        @csrf
                        <button class="btn btn-outline-danger w-100"><i class="fas fa-times me-1"></i>Hủy yêu cầu</button>
                    </form>
                @elseif($booking->status === 'approved')
                    @php
                        $hasDocs = $booking->hasUploadedDocuments();
                        $signed  = $booking->isSigned();
                        $paid    = (bool) $booking->deposit_paid_at;
                    @endphp

                    <ol class="ps-3 small">
                        <li class="{{ $hasDocs ? 'text-success' : 'fw-bold' }}">
                            {{ $hasDocs ? '✔' : '①' }} Upload CCCD
                        </li>
                        <li class="{{ $signed ? 'text-success' : ($hasDocs ? 'fw-bold' : 'text-muted') }}">
                            {{ $signed ? '✔' : '②' }} Ký hợp đồng
                        </li>
                        <li class="{{ $paid ? 'text-success' : ($signed ? 'fw-bold' : 'text-muted') }}">
                            {{ $paid ? '✔' : '③' }} Đặt cọc qua PayOS
                        </li>
                    </ol>

                    @if(!$hasDocs)
                        <a href="{{ route('booking.documents.edit', $booking) }}" class="btn btn-primary w-100"><i class="fas fa-upload me-1"></i>Upload CCCD</a>
                    @elseif(!$signed)
                        <a href="{{ route('booking.sign.create', $booking) }}" class="btn btn-primary w-100"><i class="fas fa-pen-fancy me-1"></i>Ký hợp đồng</a>
                    @elseif(!$paid)
                        <a href="{{ route('booking.deposit.show', $booking) }}" class="btn btn-success w-100"><i class="fas fa-credit-card me-1"></i>Thanh toán cọc</a>
                    @else
                        <div class="alert alert-success mb-0">
                            <i class="fas fa-check-circle me-1"></i>Đã hoàn tất! Hợp đồng đã được kích hoạt.
                        </div>
                    @endif
                @elseif($booking->status === 'rejected')
                    <div class="alert alert-danger mb-0"><i class="fas fa-times-circle me-1"></i>Yêu cầu đã bị từ chối.</div>
                @elseif($booking->status === 'cancelled')
                    <div class="alert alert-secondary mb-0"><i class="fas fa-ban me-1"></i>Yêu cầu đã được hủy.</div>
                @elseif($booking->status === 'expired')
                    <div class="alert alert-dark mb-0"><i class="fas fa-hourglass-end me-1"></i>Yêu cầu đã hết hạn giữ chỗ.</div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
