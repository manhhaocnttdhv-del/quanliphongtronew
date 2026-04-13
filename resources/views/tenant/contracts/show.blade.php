<x-app-layout>
    <x-slot name="header">Thông Tin Hợp Đồng</x-slot>

    <div class="row">
        <!-- Header thông tin -->
        <div class="col-12 mb-4">
            <div class="card card-round" style="background: linear-gradient(135deg,#1d7af3,#6c48d2);">
                <div class="card-body p-4 p-md-5 text-white">
                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                        <div>
                            <p class="text-white-50 text-uppercase fw-semibold mb-1" style="font-size:.8rem;letter-spacing:1px">Hợp Đồng Số</p>
                            <h2 class="text-white fw-bold mb-0">HĐ-{{ str_pad($contract->id, 4, '0', STR_PAD_LEFT) }}</h2>
                        </div>
                        <div class="text-end">
                            @if($contract->status == 'active')
                                <span class="badge bg-success border border-white border-opacity-25 py-2 px-3 fs-6 rounded-pill">
                                    <i class="fas fa-check-circle me-1"></i> Đang Hiệu Lực
                                </span>
                            @elseif($contract->status == 'expired')
                                <span class="badge bg-secondary border border-white border-opacity-25 py-2 px-3 fs-6 rounded-pill">Hết Hạn</span>
                            @else
                                <span class="badge bg-danger border border-white border-opacity-25 py-2 px-3 fs-6 rounded-pill">Đã Thanh Lý</span>
                            @endif
                        </div>
                    </div>
                    
                    <div class="row g-4 mt-4">
                        <div class="col-md-4">
                            <div class="bg-white bg-opacity-10 border border-white border-opacity-25 rounded p-3 text-center h-100">
                                <p class="text-white-50 text-uppercase mb-1" style="font-size:.75rem;letter-spacing:1px">Giá Thuê/Tháng</p>
                                <h4 class="text-white fw-bold mb-0">{{ number_format($contract->monthly_price, 0, ',', '.') }}đ</h4>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="bg-white bg-opacity-10 border border-white border-opacity-25 rounded p-3 text-center h-100">
                                <p class="text-white-50 text-uppercase mb-1" style="font-size:.75rem;letter-spacing:1px">Tiền Cọc</p>
                                <h4 class="text-white fw-bold mb-0">{{ number_format($contract->deposit, 0, ',', '.') }}đ</h4>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="bg-white bg-opacity-10 border border-white border-opacity-25 rounded p-3 text-center h-100">
                                <p class="text-white-50 text-uppercase mb-1" style="font-size:.75rem;letter-spacing:1px">Số Người Ở</p>
                                <h4 class="text-white fw-bold mb-0">{{ $contract->occupants }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Thông tin Phòng -->
        <div class="col-lg-6">
            <div class="card card-round h-100">
                <div class="card-header">
                    <div class="card-title">
                        <i class="fas fa-home text-primary me-2"></i>Thông Tin Phòng
                    </div>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="text-muted">Phòng</span>
                            <span class="fw-bold text-primary fs-5">{{ $contract->room->name }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="text-muted">Khu trọ</span>
                            <span class="fw-semibold">{{ $contract->room->house->name }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-start px-0">
                            <span class="text-muted">Địa chỉ</span>
                            <span class="fw-semibold text-end" style="max-width: 60%">{{ $contract->room->house->address }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="text-muted">Ngày bắt đầu</span>
                            <span class="fw-semibold">{{ \Carbon\Carbon::parse($contract->start_date)->format('d/m/Y') }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="text-muted">Ngày kết thúc</span>
                            <span class="fw-semibold">{{ \Carbon\Carbon::parse($contract->end_date)->format('d/m/Y') }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Thông tin bản thân -->
        <div class="col-lg-6">
            <div class="card card-round h-100">
                <div class="card-header">
                    <div class="card-title">
                        <i class="fas fa-user-circle text-info me-2"></i>Thông Tin Của Bạn
                    </div>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="text-muted">Họ tên</span>
                            <span class="fw-bold">{{ $contract->tenant->user->name }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="text-muted">Email</span>
                            <span class="fw-semibold">{{ $contract->tenant->user->email }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="text-muted">CCCD</span>
                            <span class="fw-semibold">{{ $contract->tenant->cccd ?? 'N/A' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="text-muted">Điện thoại</span>
                            <span class="fw-semibold">{{ $contract->tenant->phone ?? 'N/A' }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        @if($contract->notes)
        <div class="col-12">
            <div class="alert alert-warning border border-warning" role="alert">
                <h6 class="alert-heading fw-bold"><i class="fas fa-info-circle me-2"></i>Ghi chú / Điều khoản bổ sung từ Quản lý:</h6>
                <p class="mb-0 whitespace-pre-line text-dark">{{ $contract->notes }}</p>
            </div>
        </div>
        @endif

        <!-- Lịch sử hóa đơn -->
        @if($contract->invoices->count() > 0)
        <div class="col-12">
            <div class="card card-round">
                <div class="card-header">
                    <div class="card-title">Lịch Sử Hóa Đơn (Gần nhất)</div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-items-center mb-0">
                            <tbody>
                                @foreach($contract->invoices->sortByDesc('created_at')->take(6) as $invoice)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">Tháng {{ $invoice->month }}/{{ $invoice->year }}</div>
                                        <small class="text-muted">Hạn: {{ \Carbon\Carbon::parse($invoice->due_date)->format('d/m/Y') }}</small>
                                    </td>
                                    <td class="text-end">
                                        <div class="fw-bold fs-6 mb-1">{{ number_format($invoice->total, 0, ',', '.') }}đ</div>
                                        @if($invoice->status == 'paid')
                                            <span class="badge badge-success">Hoàn tất</span>
                                        @elseif($invoice->status == 'partial')
                                            <span class="badge badge-warning">Thu thiếu</span>
                                        @elseif($invoice->status == 'overdue')
                                            <span class="badge badge-danger">Quá hạn</span>
                                        @else
                                            <span class="badge badge-secondary">Chưa thu</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($contract->invoices->count() > 6)
                    <div class="card-footer text-center bg-light">
                        <a href="{{ route('tenant.invoices.index') }}" class="btn btn-sm btn-link text-primary fw-semibold">Xem tất cả {{ $contract->invoices->count() }} hóa đơn <i class="fas fa-arrow-right ms-1"></i></a>
                    </div>
                @endif
            </div>
        </div>
        @endif
        
        <div class="col-12 text-center mt-3 mb-5">
            <a href="{{ route('tenant.contracts.index') }}" class="btn btn-light btn-round border">
                <i class="fas fa-arrow-left me-1"></i> Quay lại danh sách
            </a>
        </div>
    </div>
</x-app-layout>
