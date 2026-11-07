@extends('layouts.customer')
@section('content')

    <div class="row justify-content-center">
        <div class="col-md-6 text-center mt-5">
            <i class="fas {{ $confirmed ? 'fa-check-circle text-success' : 'fa-clock text-warning' }}" style="font-size: 80px;" aria-hidden="true"></i>
            <h2 class="mt-4 fw-bold">{{ $confirmed ? 'Thanh toán đã được xác nhận' : 'Đang xác minh thanh toán' }}</h2>
            <p class="text-muted fs-5">Mã giao dịch: <strong>{{ $orderCode ?? 'Chưa có' }}</strong></p>
            <p class="text-muted">{{ $confirmed ? 'Hệ thống đã ghi nhận khoản thanh toán của bạn.' : 'Vui lòng kiểm tra hóa đơn sau khi giao dịch được xác nhận.' }}</p>
            
            <a href="{{ route('home') }}" class="btn btn-primary btn-round mt-4 px-4">Về trang chủ</a>
            @if(auth()->check() && auth()->user()->hasRole('admin'))
                <a href="{{ route('admin.invoices.index') }}" class="btn btn-secondary btn-round mt-4 px-4">Quản lý Hóa Đơn</a>
            @elseif(auth()->check() && auth()->user()->hasRole('tenant'))
                <a href="{{ route('tenant.invoices.index') }}" class="btn btn-secondary btn-round mt-4 px-4">Hóa Đơn Của Tôi</a>
            @endif
        </div>
    </div>
@endsection
