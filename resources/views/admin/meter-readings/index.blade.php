<x-app-layout>
    <x-slot name="header">Chỉ Số Điện / Nước</x-slot>

    <div class="card card-round">
        <div class="card-header">
            <div class="card-head-row flex-wrap gap-2">
                <div class="card-title">Danh sách chỉ số tiêu thụ</div>
                <div class="card-tools d-flex align-items-center flex-wrap gap-2">
                    <a href="{{ route('admin.meter-readings.create') }}" class="btn btn-primary btn-round btn-sm">
                        <i class="fas fa-plus me-1"></i> Ghi chỉ số mới
                    </a>
                    <form action="{{ route('admin.meter-readings.index') }}" method="GET" class="d-flex gap-2 flex-wrap">
                        <select name="month" class="form-select form-select-sm" style="width:130px" onchange="this.form.submit()">
                            <option value="">-- Tất cả tháng</option>
                            @for($i=1;$i<=12;$i++)
                                <option value="{{ $i }}" {{ $filterMonth==$i?'selected':'' }}>Tháng {{ $i }}</option>
                            @endfor
                        </select>
                        <select name="year" class="form-select form-select-sm" style="width:110px" onchange="this.form.submit()">
                            <option value="">-- Năm</option>
                            <option value="{{ date('Y') }}"   {{ $filterYear==date('Y')   ?'selected':'' }}>{{ date('Y') }}</option>
                            <option value="{{ date('Y')-1 }}" {{ $filterYear==date('Y')-1 ?'selected':'' }}>{{ date('Y')-1 }}</option>
                        </select>
                        @if(request()->hasAny(['month','year']))
                            <a href="{{ route('admin.meter-readings.index') }}" class="btn btn-sm btn-light">Bỏ lọc</a>
                        @endif
                    </form>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-items-center mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Kỳ</th>
                            <th>Phòng</th>
                            <th>Dịch vụ</th>
                            <th class="text-center">Chỉ số cũ → mới</th>
                            <th class="text-center">Tiêu thụ</th>
                            <th class="text-end">Thành tiền</th>
                            <th class="text-end">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($readings as $reading)
                        <tr>
                            <td class="fw-bold">{{ str_pad($reading->month,2,'0',STR_PAD_LEFT) }}/{{ $reading->year }}</td>
                            <td>
                                <div class="fw-semibold">Phòng {{ $reading->room->name ?? 'N/A' }}</div>
                                <small class="text-muted">{{ $reading->room->house->name ?? '' }}</small>
                            </td>
                            <td><span class="badge badge-info">{{ $reading->service->name ?? 'N/A' }}</span></td>
                            <td class="text-center">
                                <span class="fw-bold">{{ $reading->old_value }}</span>
                                <i class="fas fa-arrow-right text-muted mx-1" style="font-size:.7rem"></i>
                                <span class="fw-bold">{{ $reading->new_value }}</span>
                            </td>
                            <td class="text-center fw-bold text-danger">{{ $reading->consumption }} {{ $reading->service->unit ?? '' }}</td>
                            <td class="text-end fw-bold">{{ number_format($reading->total_amount,0,',','.') }}đ</td>
                            <td class="text-end">
                                <form action="{{ route('admin.meter-readings.destroy',$reading) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Xóa bản ghi này? Số liệu hóa đơn có thể sai lệch!')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger btn-round" title="Xóa">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="text-center py-5 text-muted">Chưa có dữ liệu ghi điện/nước.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($readings->hasPages())
        <div class="card-footer">{{ $readings->links() }}</div>
        @endif
    </div>
</x-app-layout>
