<x-app-layout>
    <x-slot name="header">Chi tiết Hợp Đồng HD-{{ str_pad($contract->id,4,'0',STR_PAD_LEFT) }}</x-slot>

    <div class="row">
        <div class="col-lg-8">
            {{-- Contract Info --}}
            <div class="card card-round">
                <div class="card-header">
                    <div class="card-head-row">
                        <div class="card-title">
                            HD-{{ str_pad($contract->id,4,'0',STR_PAD_LEFT) }}
                            @if($contract->status=='active')
                                <span class="badge badge-success ms-2">Đang hiệu lực</span>
                            @elseif($contract->status=='expired')
                                <span class="badge badge-secondary ms-2">Hết hạn</span>
                            @else
                                <span class="badge badge-danger ms-2">Đã thanh lý</span>
                            @endif
                        </div>
                        <div class="card-tools d-flex gap-2">
                            <a href="{{ route('admin.contracts.pdf', $contract) }}" target="_blank" class="btn btn-sm btn-danger btn-round">
                                <i class="fas fa-file-pdf me-1"></i> Xuất PDF
                            </a>
                            <a href="{{ route('admin.contracts.edit', $contract) }}" class="btn btn-sm btn-primary btn-round">
                                <i class="fas fa-edit me-1"></i> Thanh lý / Cập nhật
                            </a>
                            <a href="{{ route('admin.contracts.index') }}" class="btn btn-sm btn-secondary btn-round">
                                <i class="fas fa-arrow-left me-1"></i> Quay lại
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="fw-bold text-primary"><i class="fas fa-door-open me-1"></i> Phòng thuê</h6>
                            <table class="table table-sm table-borderless mb-3">
                                <tr><td class="text-muted">Phòng</td><td class="fw-bold text-primary fs-5">{{ $contract->room->name }}</td></tr>
                                <tr><td class="text-muted">Khu trọ</td><td>{{ $contract->room->house->name }}</td></tr>
                                <tr><td class="text-muted">Địa chỉ</td><td>{{ $contract->room->house->address }}</td></tr>
                                <tr><td class="text-muted">Số người ở</td><td>{{ $contract->occupants }} người</td></tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6 class="fw-bold text-primary"><i class="fas fa-user me-1"></i> Khách thuê</h6>
                            <table class="table table-sm table-borderless mb-3">
                                <tr><td class="text-muted">Họ tên</td><td class="fw-bold">{{ $contract->tenant->user->name }}</td></tr>
                                <tr><td class="text-muted">Email</td><td>{{ $contract->tenant->user->email }}</td></tr>
                                <tr><td class="text-muted">CCCD</td><td class="font-monospace">{{ $contract->tenant->cccd ?? 'N/A' }}</td></tr>
                                <tr><td class="text-muted">Điện thoại</td><td>{{ $contract->tenant->phone ?? 'N/A' }}</td></tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Financial Info --}}
            <div class="card card-round bg-primary text-white">
                <div class="card-body">
                    <h6 class="fw-bold text-white-50 mb-3"><i class="fas fa-coins me-1"></i> Thông Tin Tài Chính</h6>
                    <div class="row text-center">
                        <div class="col-4">
                            <p class="small opacity-75 mb-1">Giá thuê/tháng</p>
                            <h4 class="fw-bold mb-0">{{ number_format($contract->monthly_price,0,',','.')}}đ</h4>
                        </div>
                        <div class="col-4">
                            <p class="small opacity-75 mb-1">Tiền cọc</p>
                            <h4 class="fw-bold mb-0">{{ number_format($contract->deposit,0,',','.')}}đ</h4>
                        </div>
                        <div class="col-4">
                            <p class="small opacity-75 mb-1">Thời hạn HĐ</p>
                            <p class="fw-bold mb-0 small">{{ \Carbon\Carbon::parse($contract->start_date)->format('d/m/Y') }}</p>
                            <p class="small opacity-75 mb-0">→ {{ \Carbon\Carbon::parse($contract->end_date)->format('d/m/Y') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Invoice History --}}
            @if($contract->invoices->count() > 0)
            <div class="card card-round">
                <div class="card-header">
                    <div class="card-head-row">
                        <div class="card-title">Lịch Sử Hóa Đơn</div>
                        <span class="badge badge-primary">{{ $contract->invoices->count() }} hóa đơn</span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Kỳ Thu</th>
                                    <th class="text-end">Tổng</th>
                                    <th class="text-end">Đã thu</th>
                                    <th class="text-center">Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($contract->invoices->sortByDesc('created_at') as $inv)
                                <tr>
                                    <td class="fw-semibold">Tháng {{ $inv->month }}/{{ $inv->year }}</td>
                                    <td class="text-end fw-bold">{{ number_format($inv->total,0,',','.')}}đ</td>
                                    <td class="text-end text-success">{{ number_format($inv->paid_amount,0,',','.')}}đ</td>
                                    <td class="text-center">
                                        @if($inv->status=='paid')     <span class="badge badge-success">Hoàn tất</span>
                                        @elseif($inv->status=='partial') <span class="badge badge-warning">Thu thiếu</span>
                                        @elseif($inv->status=='overdue') <span class="badge badge-danger">Quá hạn</span>
                                        @else                           <span class="badge badge-secondary">Chưa thu</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif

            @if($contract->notes)
            <div class="alert alert-warning">
                <strong><i class="fas fa-sticky-note me-1"></i> Ghi chú / Điều khoản:</strong>
                <p class="mt-1 mb-0" style="white-space:pre-line">{{ $contract->notes }}</p>
            </div>
            @endif
        </div>

        <div class="col-lg-4">
            <div class="card card-round">
                <div class="card-header"><div class="card-title">Thao tác nhanh</div></div>
                <div class="card-body d-grid gap-2">
                    <a href="{{ route('admin.contracts.pdf', $contract) }}" target="_blank" class="btn btn-danger btn-round">
                        <i class="fas fa-file-pdf me-1"></i> Tải Hợp Đồng PDF
                    </a>
                    <a href="{{ route('admin.invoices.create') }}?contract_id={{ $contract->id }}" class="btn btn-success btn-round">
                        <i class="fas fa-file-invoice-dollar me-1"></i> Lập Hóa Đơn Mới
                    </a>
                    <a href="{{ route('admin.contracts.edit', $contract) }}" class="btn btn-primary btn-round">
                        <i class="fas fa-edit me-1"></i> Cập nhật / Thanh lý
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
