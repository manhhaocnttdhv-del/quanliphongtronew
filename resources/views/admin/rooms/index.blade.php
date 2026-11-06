<x-app-layout>
    <x-slot name="header">Phòng trọ</x-slot>

    <div class="card card-round">
        <div class="card-header">
            <div class="card-head-row">
                <div class="card-title">{{ $rooms->total() }} phòng</div>
                <div class="card-tools d-flex gap-2 align-items-center flex-wrap">
                    {{-- Filters --}}
                    <form action="{{ route('admin.rooms.index') }}" method="GET" class="admin-filter-form">
                        <select name="house_id" aria-label="Lọc theo chi nhánh" class="form-select form-select-sm" onchange="this.form.requestSubmit()">
                            <option value="">Tất cả chi nhánh</option>
                            @foreach($houses as $house)
                                <option value="{{ $house->id }}" {{ request('house_id')==$house->id?'selected':'' }}>
                                    {{ $house->name }}
                                </option>
                            @endforeach
                        </select>
                        <select name="status" aria-label="Lọc theo trạng thái phòng" class="form-select form-select-sm" onchange="this.form.requestSubmit()">
                            <option value="">Tất cả trạng thái</option>
                            <option value="available"   {{ request('status')=='available'   ?'selected':'' }}>Phòng trống</option>
                            <option value="rented"      {{ request('status')=='rented'      ?'selected':'' }}>Đang thuê</option>
                            <option value="reserved"    {{ request('status')=='reserved'    ?'selected':'' }}>Đang giữ chỗ</option>
                            <option value="maintenance" {{ request('status')=='maintenance' ?'selected':'' }}>Bảo trì</option>
                        </select>
                        @if(request()->hasAny(['house_id','status']))
                            <a href="{{ route('admin.rooms.index') }}" class="btn btn-sm btn-light btn-round">Bỏ lọc</a>
                        @endif
                    </form>
                    <a href="{{ route('admin.rooms.create') }}" class="btn btn-primary btn-sm btn-round">
                        <i class="fas fa-plus me-1" aria-hidden="true"></i> Thêm phòng
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
                            <th style="min-width: 210px;">Phòng</th>
                            <th style="min-width: 160px;">Chi nhánh / Tầng</th>
                            <th style="min-width: 120px;">Giá thuê</th>
                            <th style="min-width: 100px;">Diện tích</th>
                            <th class="text-center" style="min-width: 120px;">Trạng thái</th>
                            <th class="text-end" style="min-width: 100px;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rooms as $room)
                        <tr>
                            <td><div class="room-cell"><span class="room-cell-photo"><x-room-image :room="$room" /></span><div><strong>{{ $room->name }}</strong><small>Tối đa {{ $room->max_occupants }} người</small></div></div></td>
                            <td>
                                <div class="fw-semibold">{{ $room->house->name ?? 'N/A' }}</div>
                                <small class="text-muted">Tầng {{ $room->floor }}</small>
                            </td>
                            <td class="text-nowrap">{{ number_format($room->price,0,',','.')}} đ<br><small class="text-muted">mỗi tháng</small></td>
                            <td class="text-muted">
                                {{ $room->area !== null ? number_format((float) $room->area, 2, ',', '.') . ' m²' : '—' }}
                            </td>
                            <td class="text-center">
                                @if($room->status=='available')
                                    <span class="badge badge-success">Trống</span>
                                @elseif($room->status=='rented')
                                    <span class="badge badge-primary">Đang thuê</span>
                                @elseif($room->status=='reserved')
                                    <span class="badge badge-warning">Đang giữ chỗ</span>
                                @else
                                    <span class="badge badge-danger">Bảo trì</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.rooms.edit', $room) }}" class="btn btn-info btn-action me-1" aria-label="Sửa phòng {{ $room->name }}" title="Sửa phòng {{ $room->name }}">
                                    <i class="fas fa-pen" aria-hidden="true"></i>
                                </a>
                                <form action="{{ route('admin.rooms.destroy', $room) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Xác nhận xóa phòng này?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-action" aria-label="Xóa phòng {{ $room->name }}" title="Xóa phòng {{ $room->name }}">
                                        <i class="fas fa-trash" aria-hidden="true"></i>
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
