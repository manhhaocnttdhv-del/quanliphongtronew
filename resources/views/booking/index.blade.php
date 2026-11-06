@extends('layouts.customer')

@section('title', 'Yêu cầu đặt thuê của tôi')

@php
    $statusMap = [
        'pending'   => ['label' => 'Chờ duyệt',    'class' => 'bg-warning text-dark'],
        'approved'  => ['label' => 'Đã duyệt',     'class' => 'bg-primary'],
        'rejected'  => ['label' => 'Đã từ chối',   'class' => 'bg-danger'],
        'cancelled' => ['label' => 'Đã hủy',       'class' => 'bg-secondary'],
        'expired'   => ['label' => 'Hết hạn',      'class' => 'bg-dark'],
    ];
@endphp

@section('content')
<div class="booking-page-title">
    <div><h1>Yêu cầu đặt thuê của tôi</h1><p>Các yêu cầu thuê phòng tại {{ \App\Models\Setting::get('site_name', 'Trọ Nhà Mình') }}.</p></div>
    <a href="{{ route('home') }}" class="btn btn-outline-primary"><i class="fas fa-search me-2" aria-hidden="true"></i>Tìm phòng</a>
</div>

<div class="card card-c">
    <div class="card-body p-0">
        @if($requests->count() > 0)
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Phòng / Chi nhánh</th>
                            <th>Ngày gửi</th>
                            <th>Ngày dự kiến chuyển vào</th>
                            <th>Trạng thái</th>
                            <th class="text-end">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($requests as $req)
                            @php $st = $statusMap[$req->status] ?? ['label' => $req->status, 'class' => 'bg-secondary']; @endphp
                            <tr>
                                <td>#{{ $req->id }}</td>
                                <td>
                                    <strong>{{ optional($req->room)->name ?? '—' }}</strong><br>
                                    <small class="text-muted">{{ optional(optional($req->room)->house)->name }}</small>
                                </td>
                                <td>{{ $req->created_at->format('d/m/Y H:i') }}</td>
                                <td>{{ optional($req->desired_move_in_date)->format('d/m/Y') }}</td>
                                <td><span class="badge badge-status {{ $st['class'] }}">{{ $st['label'] }}</span></td>
                                <td class="text-end">
                                    <a href="{{ route('booking.show', $req) }}" class="btn btn-sm btn-primary"><i class="fas fa-eye me-1"></i>Chi tiết</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-3">{{ $requests->links() }}</div>
        @else
            <div class="request-empty">
                <i class="far fa-folder-open" aria-hidden="true"></i>
                <h2>Chưa có yêu cầu đặt thuê</h2>
                <p class="text-muted">Hãy duyệt danh sách phòng trống để gửi yêu cầu đầu tiên của bạn.</p>
                <a href="{{ route('home') }}" class="btn btn-primary"><i class="fas fa-search me-1"></i>Tìm phòng ngay</a>
            </div>
        @endif
    </div>
</div>
@endsection
