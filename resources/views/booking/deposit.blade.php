@extends('layouts.customer')

@section('title', 'Đặt cọc')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card card-c">
            <div class="card-body p-4">
                <h3 class="mb-3"><i class="fas fa-credit-card me-2 text-primary"></i>Bước 3: Đặt cọc qua PayOS</h3>

                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-1"></i>Bạn sẽ được chuyển sang cổng thanh toán PayOS để hoàn tất giao dịch. Sau khi thanh toán thành công, hợp đồng của bạn sẽ tự động được kích hoạt.
                </div>

                <table class="table table-bordered">
                    <tbody>
                        <tr><th width="35%">Phòng</th><td>{{ optional($booking->room)->name }} — {{ optional(optional($booking->room)->house)->name }}</td></tr>
                        <tr><th>Mã hóa đơn</th><td>HD-{{ $invoice->id }}</td></tr>
                        <tr><th>Loại</th><td><span class="badge bg-info">Đặt cọc</span></td></tr>
                        <tr><th>Số tiền cọc</th><td><strong class="text-danger fs-4">{{ number_format($invoice->total, 0, ',', '.') }} đ</strong></td></tr>
                        <tr><th>Đã thanh toán</th><td>{{ number_format($invoice->paid_amount, 0, ',', '.') }} đ</td></tr>
                        <tr><th>Còn phải đóng</th><td><strong>{{ number_format($invoice->debt, 0, ',', '.') }} đ</strong></td></tr>
                        <tr><th>Hạn cuối</th><td>{{ optional($invoice->due_date)->format('d/m/Y') }}</td></tr>
                        <tr><th>Trạng thái</th>
                            <td>
                                @if($invoice->status === 'paid')
                                    <span class="badge bg-success">Đã thanh toán</span>
                                @elseif($invoice->status === 'partial')
                                    <span class="badge bg-warning text-dark">Đã cọc một phần</span>
                                @else
                                    <span class="badge bg-danger">Chưa thanh toán</span>
                                @endif
                            </td>
                        </tr>
                    </tbody>
                </table>

                @if($invoice->status !== 'paid')
                    <form method="POST" action="{{ route('booking.deposit.pay', $booking) }}">
                        @csrf
                        <button class="btn btn-success btn-lg w-100"><i class="fas fa-credit-card me-2"></i>Thanh toán {{ number_format($invoice->debt, 0, ',', '.') }} đ qua PayOS</button>
                    </form>
                @else
                    <div class="alert alert-success mb-0"><i class="fas fa-check-circle me-1"></i>Hóa đơn đã được thanh toán đầy đủ.</div>
                @endif

                <div class="mt-3">
                    <a href="{{ route('booking.show', $booking) }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Quay lại</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
