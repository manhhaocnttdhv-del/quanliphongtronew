<x-app-layout>
    <x-slot name="header">Chi Tiết Hóa Đơn Kỳ {{ $invoice->month }}/{{ $invoice->year }}</x-slot>

    <div class="row">
        <div class="col-lg-8">
            <div class="card card-round">
                <div class="card-header">
                    <div class="card-head-row">
                        <div class="card-title">
                            INV-{{ $invoice->year }}{{ str_pad($invoice->month,2,'0',STR_PAD_LEFT) }}-{{ str_pad($invoice->id,3,'0',STR_PAD_LEFT) }}
                            @if($invoice->status=='paid')     <span class="badge badge-success ms-2">Đã thanh toán</span>
                            @elseif($invoice->status=='partial') <span class="badge badge-warning ms-2">Thu thiếu</span>
                            @elseif($invoice->status=='overdue') <span class="badge badge-danger ms-2">Quá hạn</span>
                            @else                              <span class="badge badge-secondary ms-2">Chưa đóng</span>
                            @endif
                        </div>
                        <a href="{{ route('tenant.invoices.index') }}" class="btn btn-sm btn-secondary btn-round">
                            <i class="fas fa-arrow-left me-1"></i> Quay lại
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-6">
                            <p class="text-muted small mb-1">KHÁCH THUÊ</p>
                            <p class="fw-bold mb-0">{{ Auth::user()->name }}</p>
                            <p class="small text-muted mb-0">{{ Auth::user()->email }}</p>
                        </div>
                        <div class="col-6 text-end">
                            <p class="text-muted small mb-1">PHÒNG</p>
                            <p class="fw-bold text-primary fs-5 mb-0">P.{{ $invoice->contract->room->name }}</p>
                            <p class="small text-muted mb-0">{{ $invoice->contract->room->house->name }}</p>
                            <p class="text-danger small mb-0">Hạn nộp: {{ \Carbon\Carbon::parse($invoice->due_date)->format('d/m/Y') }}</p>
                        </div>
                    </div>
                    <hr>

                    <table class="table table-borderless">
                        <tbody>
                            <tr>
                                <td>Tiền phòng tháng {{ $invoice->month }}/{{ $invoice->year }}</td>
                                <td class="text-end fw-semibold">{{ number_format($invoice->room_fee,0,',','.')}}đ</td>
                            </tr>
                            @if(($invoice->electricity_fee??0) > 0)
                            <tr>
                                <td><i class="fas fa-bolt text-warning me-1"></i> Tiền Điện</td>
                                <td class="text-end text-danger fw-semibold">{{ number_format($invoice->electricity_fee,0,',','.')}}đ</td>
                            </tr>
                            @endif
                            @if(($invoice->water_fee??0) > 0)
                            <tr>
                                <td><i class="fas fa-tint text-info me-1"></i> Tiền Nước</td>
                                <td class="text-end text-primary fw-semibold">{{ number_format($invoice->water_fee,0,',','.')}}đ</td>
                            </tr>
                            @endif
                            @if(($invoice->service_fee??0) > 0)
                            <tr>
                                <td>Phí Dịch Vụ (Rác, Wifi, Xe...)</td>
                                <td class="text-end fw-semibold">{{ number_format($invoice->service_fee,0,',','.')}}đ</td>
                            </tr>
                            @endif
                        </tbody>
                        <tfoot class="border-top">
                            <tr><th>Tổng cộng</th><th class="text-end fs-5">{{ number_format($invoice->total,0,',','.')}}đ</th></tr>
                            <tr><td class="text-muted">Đã thanh toán</td><td class="text-end text-success fw-bold">- {{ number_format($invoice->paid_amount,0,',','.')}}đ</td></tr>
                            <tr class="border-top">
                                <th class="fs-5">Còn phải nộp</th>
                                <th class="text-end fs-4 {{ $invoice->debt > 0 ? 'text-danger' : 'text-success' }}">{{ number_format($invoice->debt,0,',','.')}}đ</th>
                            </tr>
                        </tfoot>
                    </table>

                    @if($invoice->notes)
                    <div class="alert alert-light border mt-2">
                        <strong><i class="fas fa-sticky-note text-warning me-1"></i> Ghi chú:</strong> {{ $invoice->notes }}
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            @if($invoice->debt > 0)
            {{-- VietQR Payment --}}
            <div class="card card-round bg-primary text-white">
                <div class="card-header border-0 text-white">
                    <div class="card-title text-white"><i class="fas fa-qrcode me-1"></i> Thanh toán Quét Mã QR</div>
                </div>
                <div class="card-body text-center">
                    <p class="small opacity-75 mb-3">Mở App ngân hàng bất kỳ để quét. Tiền vào thẳng tài khoản Chủ nhà.</p>
                    @php
                        $bank_bin = '970422';
                        $account_no = '1234567890';
                        $account_name = 'CHU NHA TRO BOARDING PRO';
                        $amount = $invoice->debt;
                        $desc = 'THANH TOAN '.$invoice->month.'/'.$invoice->year.' P'.$invoice->contract->room->name;
                        $qr_url = "https://img.vietqr.io/image/{$bank_bin}-{$account_no}-print.png?amount={$amount}&addInfo=".urlencode($desc)."&accountName=".urlencode($account_name);
                    @endphp
                    <div class="bg-white rounded p-2 mb-3 d-inline-block">
                        <img src="{{ $qr_url }}" alt="VietQR" style="max-width:180px" class="rounded">
                    </div>
                    <div class="text-start small bg-white bg-opacity-10 rounded p-3 border border-white border-opacity-25">
                        <div class="mb-2"><span class="opacity-75">Ngân hàng</span><br><strong>MB Bank</strong></div>
                        <div class="mb-2"><span class="opacity-75">Số TK</span><br><strong class="font-monospace">{{ $account_no }}</strong></div>
                        <div class="mb-2"><span class="opacity-75">Số tiền</span><br><strong>{{ number_format($amount,0,',','.')}}đ</strong></div>
                        <div><span class="opacity-75">Nội dung CK</span><br><strong>{{ $desc }}</strong></div>
                    </div>
                </div>
            </div>
            @else
            <div class="card card-round border border-success text-center py-5">
                <div class="card-body">
                    <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                    <h5 class="fw-bold text-success">Đã thanh toán đầy đủ!</h5>
                    <p class="text-muted small">Cảm ơn bạn đã thanh toán kịp thời.</p>
                </div>
            </div>
            @endif
        </div>
    </div>
</x-app-layout>
