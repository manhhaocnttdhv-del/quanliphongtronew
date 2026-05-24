<x-app-layout>
    <x-slot name="header">Thanh toán thành công</x-slot>

    <div class="row justify-content-center">
        <div class="col-md-6 text-center mt-5">
            <i class="fas fa-check-circle text-success" style="font-size: 80px;"></i>
            <h2 class="mt-4 fw-bold">Thanh Toán Thành Công!</h2>
            <p class="text-muted fs-5">Cảm ơn bạn. Giao dịch mã <strong>{{ $orderCode }}</strong> đã được ghi nhận.</p>
            <p class="text-muted">Hệ thống sẽ tự động gạch nợ hóa đơn trong ít phút (thông qua Webhook).</p>
            
            <a href="{{ route('home') }}" class="btn btn-primary btn-round mt-4 px-4">Về trang chủ</a>
            @if(auth()->check() && auth()->user()->hasRole('admin'))
                <a href="{{ route('admin.invoices.index') }}" class="btn btn-secondary btn-round mt-4 px-4">Quản lý Hóa Đơn</a>
            @elseif(auth()->check() && auth()->user()->hasRole('tenant'))
                <a href="{{ route('tenant.invoices.index') }}" class="btn btn-secondary btn-round mt-4 px-4">Hóa Đơn Của Tôi</a>
            @endif
        </div>
    </div>
</x-app-layout>
