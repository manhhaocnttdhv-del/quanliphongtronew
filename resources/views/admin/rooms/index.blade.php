<x-app-layout>
    <x-slot name="header">Quản lý Phòng</x-slot>

    <div class="card card-round">
        <div class="card-header">
            <div class="card-head-row">
                <div class="card-title">Danh sách Phòng</div>
                <div class="card-tools d-flex gap-2 align-items-center flex-wrap">
                    {{-- Filters --}}
                    <form action="{{ route('admin.rooms.index') }}" method="GET" class="d-flex gap-2 align-items-center">
                        <select name="house_id" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">-- Tất cả khu trọ --</option>
                            @foreach($houses as $house)
                                <option value="{{ $house->id }}" {{ request('house_id')==$house->id?'selected':'' }}>
                                    {{ $house->name }}
                                </option>
                            @endforeach
                        </select>
                        <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">-- Tất cả trạng thái --</option>
                            <option value="available"   {{ request('status')=='available'   ?'selected':'' }}>Phòng trống</option>
                            <option value="rented"      {{ request('status')=='rented'      ?'selected':'' }}>Đang thuê</option>
                            <option value="maintenance" {{ request('status')=='maintenance' ?'selected':'' }}>Bảo trì</option>
                        </select>
                        @if(request()->hasAny(['house_id','status']))
                            <a href="{{ route('admin.rooms.index') }}" class="btn btn-sm btn-light btn-round">Bỏ lọc</a>
                        @endif
                    </form>
                    <a href="{{ route('admin.rooms.create') }}" class="btn btn-primary btn-sm btn-round">
                        <i class="fas fa-plus me-1"></i> Thêm Phòng
                    </a>
                </div>
            </div>
        </div>

        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mx-3 mt-3">
            <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width:80px">Phòng</th>
                            <th>Khu trọ / Tầng</th>
                            <th>Giá thuê</th>
                            <th>Diện tích</th>
                            <th class="text-center">Trạng thái</th>
                            <th class="text-end">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rooms as $room)
                        <tr>
                            <td class="text-center fw-bold fs-5 text-dark">{{ $room->name }}</td>
                            <td>
                                <div class="fw-semibold">{{ $room->house->name ?? 'N/A' }}</div>
                                <small class="text-muted">Tầng {{ $room->floor }}</small>
                            </td>
                            <td class="fw-bold text-primary">{{ number_format($room->price,0,',','.')}}đ<small class="text-muted fw-normal">/tháng</small></td>
                            <td class="text-muted">{{ $room->area ? $room->area.' m²' : '—' }}</td>
                            <td class="text-center">
                                @if($room->status=='available')
                                    <span class="badge badge-success">Trống</span>
                                @elseif($room->status=='rented')
                                    <span class="badge badge-primary">Đang thuê</span>
                                @else
                                    <span class="badge badge-danger">Bảo trì</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.rooms.edit', $room) }}" class="btn btn-sm btn-info btn-round me-1">
                                    <i class="fas fa-pen"></i>
                                </a>
                                <form action="{{ route('admin.rooms.destroy', $room) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Xác nhận xóa phòng này?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger btn-round">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="fas fa-door-open fa-2x mb-2 d-block opacity-50"></i>
                                Không tìm thấy phòng nào
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($rooms->hasPages())
            <div class="px-3 py-3 border-top">
                {{ $rooms->links() }}
            </div>
            @endif
        </div>
    </div>
</x-app-layout>
