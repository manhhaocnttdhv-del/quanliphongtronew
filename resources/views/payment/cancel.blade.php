<x-app-layout>
    <x-slot name="header">Thanh toán thất bại</x-slot>

    <div class="row justify-content-center">
        <div class="col-md-6 text-center mt-5">
            <i class="fas fa-times-circle text-danger" style="font-size: 80px;"></i>
            <h2 class="mt-4 fw-bold">Giao Dịch Đã Bị Hủy!</h2>
            <p class="text-muted fs-5">Mã giao dịch: <strong>{{ $orderCode }}</strong></p>
            <p class="text-muted">Bạn đã hủy quá trình thanh toán hoặc có lỗi xảy ra. Chưa có khoản tiền nào bị trừ.</p>
            
            @if(auth()->check() && auth()->user()->hasRole('admin'))
                <a href="{{ route('admin.invoices.index') }}" class="btn btn-secondary btn-round mt-4 px-4">Quản lý Hóa Đơn</a>
            @elseif(auth()->check() && auth()->user()->hasRole('tenant'))
                <a href="{{ route('tenant.invoices.index') }}" class="btn btn-secondary btn-round mt-4 px-4">Hóa Đơn Của Tôi</a>
            @else
                <a href="{{ route('home') }}" class="btn btn-secondary btn-round mt-4 px-4">Về trang chủ</a>
            @endif
        </div>
    </div>
</x-app-layout>
