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
                            <a href="{{ route('admin.invoices.edit', $invoice) }}" class="btn btn-sm btn-primary btn-round">
                                <i class="fas fa-money-bill-wave me-1"></i> Ghi nhận Thu
                            </a>
                            <a href="{{ route('admin.invoices.index') }}" class="btn btn-sm btn-secondary btn-round">
                                <i class="fas fa-arrow-left me-1"></i> Quay lại
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    {{-- Header info --}}
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
                                <td>Tiền phòng tháng {{ $invoice->month }}/{{ $invoice->year }}</td>
                                <td class="text-end fw-semibold">{{ number_format($invoice->room_fee,0,',','.')}}đ</td>
                            </tr>
                            @if($invoice->electricity_fee > 0)
                            <tr>
                                <td><i class="fas fa-bolt text-warning me-1"></i> Tiền Điện</td>
                                <td class="text-end fw-semibold text-danger">{{ number_format($invoice->electricity_fee,0,',','.')}}đ</td>
                            </tr>
                            @endif
                            @if($invoice->water_fee > 0)
                            <tr>
                                <td><i class="fas fa-tint text-info me-1"></i> Tiền Nước</td>
                                <td class="text-end fw-semibold text-primary">{{ number_format($invoice->water_fee,0,',','.')}}đ</td>
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
                                <th class="fs-5">Còn phải thu</th>
                                <th class="text-end fs-4 {{ $invoice->debt > 0 ? 'text-danger' : 'text-success' }}">
                                    {{ number_format($invoice->debt,0,',','.')}}đ
                                </th>
                            </tr>
                        </tfoot>
                    </table>

                    @if($invoice->notes)
                    <div class="alert alert-warning mt-3">
                        <strong><i class="fas fa-sticky-note me-1"></i>Ghi chú:</strong> {{ $invoice->notes }}
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

            {{-- Delete --}}
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
        </div>
    </div>
</x-app-layout>
