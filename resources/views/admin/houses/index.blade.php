<x-app-layout>
    <x-slot name="header">Khu Trọ & Phòng</x-slot>

    <div class="card card-round">
        <div class="card-header">
            <div class="card-head-row">
                <div class="card-title">Danh sách Khu Trọ</div>
                <div class="card-tools d-flex align-items-center gap-2">
                    <a href="{{ route('admin.houses.create') }}" class="btn btn-primary btn-round btn-sm">
                        <i class="fas fa-plus me-1"></i> Thêm Khu Trọ
                    </a>
                    <div class="input-group input-group-sm" style="width:220px">
                        <input type="text" id="house-search" class="form-control" placeholder="Tìm kiếm...">
                        <span class="input-group-text"><i class="fa fa-search"></i></span>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-items-center mb-0" id="house-table">
                    <thead class="thead-light">
                        <tr>
                            <th>Tên Khu trọ</th>
                            <th>Địa chỉ</th>
                            <th class="text-center">Số phòng</th>
                            <th class="text-center">Trạng thái</th>
                            <th class="text-end">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($houses as $house)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar-sm bg-primary rounded-circle d-flex align-items-center justify-content-center text-white fw-bold" style="width:38px;height:38px;flex-shrink:0">
                                        {{ substr($house->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="fw-semibold">{{ $house->name }}</div>
                                        <small class="text-muted">{{ $house->phone ?? 'Chưa có SĐT' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>{{ Str::limit($house->address, 50) }}</td>
                            <td class="text-center">
                                <span class="badge badge-info rounded-pill">{{ $house->rooms_count ?? 0 }} phòng</span>
                            </td>
                            <td class="text-center">
                                @if($house->is_active)
                                    <span class="badge badge-success">Hoạt động</span>
                                @else
                                    <span class="badge badge-danger">Tạm ngưng</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="d-flex justify-content-end gap-1">
                                    <a href="{{ route('admin.houses.edit', $house) }}"
                                       class="btn btn-sm btn-info btn-round" title="Sửa">
                                        <i class="fas fa-pen"></i>
                                    </a>
                                    <form action="{{ route('admin.houses.destroy', $house) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('Xóa khu trọ này? Tất cả phòng bên trong sẽ bị ảnh hưởng!')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger btn-round" title="Xóa">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="fas fa-building fa-2x mb-2 d-block"></i>
                                Chưa có khu trọ nào. <a href="{{ route('admin.houses.create') }}">Thêm ngay</a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($houses->hasPages())
        <div class="card-footer">
            {{ $houses->links() }}
        </div>
        @endif
    </div>

    <x-slot name="scripts">
    <script>
        $('#house-search').on('keyup', function() {
            var val = $(this).val().toLowerCase();
            $('#house-table tbody tr').filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(val) > -1);
            });
        });
    </script>
    </x-slot>
</x-app-layout>
