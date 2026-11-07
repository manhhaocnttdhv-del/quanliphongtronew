<x-app-layout>
    <x-slot name="header">Chi tiết Hóa Đơn #{{ $invoice->id }}</x-slot>

    <div class="row">
        <div class="col-lg-8">
            {{-- Invoice Detail Card --}}
            <div class="card card-round">
                <div class="card-header">
                    <div class="card-head-row">
                        <div class="card-title">
                            <span class="font-monospace">INV-{{ $invoice->year }}{{ str_pad($invoice->month,2,'0',STR_PAD_LEFT) }}-{{ str_pad($invoice->id,3,'0',STR_PAD_LEFT) }}</span>
                            &nbsp;
                            @if($invoice->status=='paid')
                                <span class="badge badge-success">Hoàn tất</span>
                            @elseif($invoice->status=='partial')
                                <span class="badge badge-warning">Thu thiếu</span>
                            @elseif($invoice->status=='overdue')
                                <span class="badge badge-danger">Quá hạn</span>
                            @else
                                <span class="badge badge-secondary">Chưa thu</span>
                            @endif
                        </div>
                        <div class="card-tools d-flex gap-2">
                            @if($invoice->debt > 0 && $invoice->type !== 'deposit' && ! $invoice->voided_at)
                            <a href="{{ route('payment.checkout', $invoice) }}" class="btn btn-sm btn-info btn-round" id="payos-btn">
                                <i class="fas fa-qrcode me-1"></i> PayOS
                            </a>
                            @endif
                            @if($invoice->type !== 'deposit' && ! $invoice->voided_at)
                            <a href="{{ route('admin.invoices.edit', $invoice) }}" class="btn btn-sm btn-primary btn-round">
                                <i class="fas fa-money-bill-wave me-1"></i> Ghi nhận Thu
                            </a>
                            @endif
                            <a href="{{ route('admin.invoices.index') }}" class="btn btn-sm btn-secondary btn-round">
                                <i class="fas fa-arrow-left me-1"></i> Quay lại
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    {{-- Header info --}}
                    @if($invoice->voided_at)
                    <div class="alert alert-secondary">Hóa đơn đã hủy hoặc tất toán, không tiếp tục thu tiền.</div>
                    @endif
                    <div class="row mb-4">
                        <div class="col-6">
                            <p class="text-muted small mb-1">KHÁCH THUÊ</p>
                            <p class="fw-bold mb-0">{{ $invoice->contract->tenant->user->name ?? 'N/A' }}</p>
                            <p class="small text-muted">{{ $invoice->contract->tenant->user->email ?? '' }}</p>
                            <p class="small text-muted">CCCD: {{ $invoice->contract->tenant->cccd ?? 'N/A' }}</p>
                        </div>
                        <div class="col-6 text-end">
                            <p class="text-muted small mb-1">PHÒNG</p>
                            <p class="fw-bold text-primary fs-5 mb-0">P.{{ $invoice->contract->room->name ?? 'N/A' }}</p>
                            <p class="small text-muted">{{ $invoice->contract->room->house->name ?? '' }}</p>
                            <p class="text-danger small fw-semibold">
                                <i class="fas fa-exclamation-circle me-1"></i>
                                Hạn nộp: {{ \Carbon\Carbon::parse($invoice->due_date)->format('d/m/Y') }}
                            </p>
                        </div>
                    </div>

                    <hr>

                    {{-- Chi phí breakdown --}}
                    <table class="table table-borderless">
                        <tbody>
                            <tr>
                                <td>{{ $invoice->type === 'deposit' ? 'Tiền đặt cọc' : 'Tiền phòng tháng '.$invoice->month.'/'.$invoice->year }}</td>
                                <td class="text-end fw-semibold">{{ number_format($invoice->type === 'deposit' ? $invoice->total : $invoice->room_fee,0,',','.')}}đ</td>
                            </tr>
                            @if($invoice->electricity_fee > 0 || $invoice->electricity_usage > 0)
                            <tr>
                                <td>
                                    <i class="fas fa-bolt text-warning me-1"></i> Tiền Điện
                                    <div class="small text-muted ms-3 mt-1">
                                        Chỉ số: {{ $invoice->electricity_old }} ➔ {{ $invoice->electricity_new }}
                                        (Tiêu thụ: {{ $invoice->electricity_usage }} kWh)
                                    </div>
                                </td>
                                <td class="text-end fw-semibold text-danger align-middle">{{ number_format($invoice->electricity_fee,0,',','.')}}đ</td>
                            </tr>
                            @endif
                            @if($invoice->water_fee > 0 || $invoice->water_usage > 0)
                            <tr>
                                <td>
                                    <i class="fas fa-tint text-info me-1"></i> Tiền Nước
                                    <div class="small text-muted ms-3 mt-1">
                                        Chỉ số: {{ $invoice->water_old }} ➔ {{ $invoice->water_new }}
                                        (Tiêu thụ: {{ $invoice->water_usage }} m³)
                                    </div>
                                </td>
                                <td class="text-end fw-semibold text-primary align-middle">{{ number_format($invoice->water_fee,0,',','.')}}đ</td>
                            </tr>
                            @endif
                            @if($invoice->service_fee > 0)
                            <tr>
                                <td>Phí Dịch Vụ (Rác, Wifi, Xe...)</td>
                                <td class="text-end fw-semibold">{{ number_format($invoice->service_fee,0,',','.')}}đ</td>
                            </tr>
                            @endif
                        </tbody>
                        <tfoot class="border-top">
                            <tr>
                                <th>Tổng cộng</th>
                                <th class="text-end fs-5">{{ number_format($invoice->total,0,',','.')}}đ</th>
                            </tr>
                            <tr>
                                <td class="text-muted">Đã thu được</td>
                                <td class="text-end text-success fw-bold">- {{ number_format($invoice->paid_amount,0,',','.')}}đ</td>
                            </tr>
                            <tr class="border-top">
                                <td>Đã hoàn tiền</td><td class="text-end">{{ number_format($invoice->refunded_amount, 0, ',', '.') }}đ</td>
                            </tr>
                            <tr class="border-top">
                                <th class="fs-5">Còn phải thu</th>
                                <th class="text-end fs-4 {{ $invoice->debt > 0 ? 'text-danger' : 'text-success' }}">
                                    {{ number_format($invoice->debt,0,',','.')}}đ
                                </th>
                            </tr>
                        </tfoot>
                    </table>

                    @php
                        $displayNotes = trim(preg_replace('/^\[PayOS\].*$/m', '', $invoice->notes));
                        // Clean up consecutive empty newlines
                        $displayNotes = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $displayNotes);
                    @endphp
                    @if($displayNotes)
                    <div class="alert alert-warning mt-3">
                        <strong><i class="fas fa-sticky-note me-1"></i>Ghi chú:</strong> {!! nl2br(e($displayNotes)) !!}
                    </div>
                    @endif
                    @if($invoice->payments->contains('requires_review', true))
                    <div class="alert alert-danger" role="alert">Có khoản thanh toán cần đối soát hoặc hoàn tiền.</div>
                    @endif
                    @if($invoice->payments->isNotEmpty())
                    <h3 class="h6 mt-4">Lịch sử thu tiền</h3>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead><tr><th>Ngày thu</th><th>Số tiền</th><th>Hình thức / Mã giao dịch</th><th>Đối soát</th></tr></thead>
                            <tbody>
                            @foreach($invoice->payments->sortByDesc('paid_at') as $payment)
                            <tr>
                                <td>{{ $payment->paid_at->format('d/m/Y H:i') }}</td>
                                <td class="text-nowrap">{{ number_format($payment->amount, 0, ',', '.') }} đ</td>
                                <td class="text-break">{{ $payment->kind === 'deposit_application' ? 'Bù trừ cọc' : ($payment->method === 'cash' ? 'Tiền mặt' : 'Chuyển khoản') }}<br>{{ $payment->reference_code }}</td>
                                <td>@if($payment->requires_review || $payment->resolution)<a href="{{ route('admin.payment-reviews.show', $payment) }}">{{ $payment->requires_review ? 'Cần đối soát' : 'Đã đối soát' }}</a>@else Đã ghi nhận @endif</td>
                            </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            {{-- Progress Card --}}
            <div class="card card-round">
                <div class="card-header"><div class="card-title">Tiến Độ Thu Tiền</div></div>
                <div class="card-body text-center">
                    @php $percent = $invoice->total > 0 ? min(100, round(($invoice->paid_amount/$invoice->total)*100)) : 0; @endphp
                    <h2 class="fw-bold {{ $percent==100 ? 'text-success' : 'text-primary' }}">{{ $percent }}%</h2>
                    <p class="text-muted small">đã thanh toán</p>
                    <div class="progress mb-3" style="height:10px">
                        <div class="progress-bar {{ $percent==100 ? 'bg-success' : 'bg-primary' }}"
                             style="width:{{ $percent }}%" role="progressbar"></div>
                    </div>
                    <div class="d-flex justify-content-between text-sm">
                        <span class="text-muted">Tổng HĐ</span>
                        <span class="fw-bold">{{ number_format($invoice->total,0,',','.')}}đ</span>
                    </div>
                    <div class="d-flex justify-content-between text-sm mt-1">
                        <span class="text-muted">Đã thu</span>
                        <span class="fw-bold text-success">{{ number_format($invoice->paid_amount,0,',','.')}}đ</span>
                    </div>
                    <div class="d-flex justify-content-between text-sm mt-1">
                        <span class="text-muted">Còn nợ</span>
                        <span class="fw-bold text-danger">{{ number_format($invoice->debt,0,',','.')}}đ</span>
                    </div>
                </div>
            </div>

            @if((int) $invoice->total === 0 && $invoice->payments->isEmpty() && $invoice->paymentOrders->isEmpty())
            <div class="card card-round border border-danger">
                <div class="card-body text-center py-3">
                    <form action="{{ route('admin.invoices.destroy', $invoice) }}" method="POST"
                          onsubmit="return confirm('Xác nhận xóa hóa đơn này? Không thể hoàn tác!')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-round btn-sm w-100">
                            <i class="fas fa-trash me-1"></i> Xóa Hóa Đơn Này
                        </button>
                    </form>
                </div>
            </div>
            @endif
        </div>
    </div>
    <x-slot name="scripts">
        <script>
            (function() {
                const payBtn = document.getElementById('payos-btn');
                
                function openPayOSPopup(checkoutUrl, orderCode) {
                    const payOSConfig = {
                        RETURN_URL: "{{ route('payment.success') }}",
                        ELEMENT_ID: "payos-embed",
                        CHECKOUT_URL: checkoutUrl,
                        embedded: false,
                        onSuccess: (event) => {
                            window.location.replace("{{ route('payment.success') }}?orderCode=" + orderCode);
                        },
                        onCancel: (event) => {
                            window.location.replace("{{ route('payment.cancel') }}?orderCode=" + orderCode);
                        },
                        onExit: (event) => {
                            console.log("PayOS checkout closed");
                        }
                    };
                    
                    const payosInstance = (typeof usePayOS !== 'undefined') ? usePayOS(payOSConfig) : PayOSCheckout.usePayOS(payOSConfig);
                    const { open } = payosInstance;
                    open();
                }

                if (payBtn) {
                    payBtn.addEventListener('click', function(e) {
                        e.preventDefault();
                        
                        // Show loading state
                        const originalHTML = payBtn.innerHTML;
                        payBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Đang tạo...';
                        payBtn.style.pointerEvents = 'none';
                        payBtn.style.opacity = '0.7';
                        
                        fetch(payBtn.getAttribute('href'), {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            }
                        })
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Không thể kết nối với máy chủ.');
                            }
                            return response.json();
                        })
                        .then(data => {
                            // Restore button state
                            payBtn.innerHTML = originalHTML;
                            payBtn.style.pointerEvents = 'auto';
                            payBtn.style.opacity = '1';
                            
                            if (data.success && data.checkoutUrl) {
                                openPayOSPopup(data.checkoutUrl, data.orderCode);
                            } else {
                                alert('Lỗi tạo link thanh toán: ' + (data.message || 'Không rõ lỗi.'));
                            }
                        })
                        .catch(error => {
                            payBtn.innerHTML = originalHTML;
                            payBtn.style.pointerEvents = 'auto';
                            payBtn.style.opacity = '1';
                            alert('Lỗi: ' + error.message);
                        });
                    });
                }
            })();
        </script>
    </x-slot>
</x-app-layout>
