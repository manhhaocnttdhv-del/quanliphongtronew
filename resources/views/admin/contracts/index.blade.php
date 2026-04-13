<x-app-layout>
    <x-slot name="header">Hợp Đồng Thuê</x-slot>

    <div class="card card-round">
        <div class="card-header">
            <div class="card-head-row">
                <div class="card-title">Danh sách Hợp đồng</div>
                <div class="card-tools">
                    <a href="{{ route('admin.contracts.create') }}" class="btn btn-primary btn-round btn-sm">
                        <i class="fas fa-plus me-1"></i> Tạo Hợp đồng
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-items-center mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Mã HĐ</th>
                            <th>Phòng / Khu trọ</th>
                            <th>Khách thuê</th>
                            <th>Thời gian</th>
                            <th class="text-center">Trạng thái</th>
                            <th class="text-end">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($contracts as $contract)
                        <tr>
                            <td class="fw-bold text-primary">HD-{{ sprintf('%04d', $contract->id) }}</td>
                            <td>
                                <div class="fw-semibold">Phòng {{ $contract->room->name ?? 'N/A' }}</div>
                                <small class="text-muted">{{ $contract->room->house->name ?? 'N/A' }}</small>
                            </td>
                            <td class="fw-semibold">{{ $contract->tenant->user->name ?? 'N/A' }}</td>
                            <td>
                                <small>Từ: <strong>{{ $contract->start_date->format('d/m/Y') }}</strong></small><br>
                                <small>Đến: <strong>{{ $contract->end_date->format('d/m/Y') }}</strong></small>
                            </td>
                            <td class="text-center">
                                @if($contract->status == 'active')
                                    <span class="badge badge-success">Hiệu lực</span>
                                @elseif($contract->status == 'expired')
                                    <span class="badge badge-warning">Hết hạn</span>
                                @else
                                    <span class="badge badge-danger">Đã thanh lý</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="d-flex justify-content-end gap-1">
                                    <a href="{{ route('admin.contracts.show', $contract) }}" class="btn btn-sm btn-secondary btn-round" title="Xem"><i class="fas fa-eye"></i></a>
                                    <a href="{{ route('admin.contracts.pdf', $contract) }}" target="_blank" class="btn btn-sm btn-danger btn-round" title="PDF"><i class="fas fa-file-pdf"></i></a>
                                    <a href="{{ route('admin.contracts.edit', $contract) }}" class="btn btn-sm btn-info btn-round" title="Sửa"><i class="fas fa-pen"></i></a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">Chưa có hợp đồng nào.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($contracts->hasPages())
        <div class="card-footer">{{ $contracts->links() }}</div>
        @endif
    </div>
</x-app-layout>
