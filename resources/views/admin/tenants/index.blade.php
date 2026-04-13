<x-app-layout>
    <x-slot name="header">Khách Thuê</x-slot>

    <div class="card card-round">
        <div class="card-header">
            <div class="card-head-row">
                <div class="card-title">Danh sách Người thuê trọ</div>
                <div class="card-tools d-flex align-items-center gap-2">
                    <a href="{{ route('admin.tenants.create') }}" class="btn btn-primary btn-round btn-sm">
                        <i class="fas fa-plus me-1"></i> Thêm Khách Thuê
                    </a>
                    <div class="input-group input-group-sm" style="width:220px">
                        <input type="text" id="tenant-search" class="form-control" placeholder="Tìm tên, SĐT...">
                        <span class="input-group-text"><i class="fa fa-search"></i></span>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-items-center mb-0" id="tenant-table">
                    <thead class="thead-light">
                        <tr>
                            <th>ID</th>
                            <th>Khách thuê</th>
                            <th>SĐT / CCCD</th>
                            <th class="text-center">Giới tính</th>
                            <th class="text-end">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tenants as $tenant)
                        <tr>
                            <td class="fw-bold text-muted">#{{ $tenant->id }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar-sm bg-info rounded-circle d-flex align-items-center justify-content-center text-white fw-bold" style="width:38px;height:38px;flex-shrink:0">
                                        {{ substr($tenant->user->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="fw-semibold">{{ $tenant->user->name }}</div>
                                        <small class="text-muted">{{ $tenant->user->email }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div>{{ $tenant->phone ?? 'N/A' }}</div>
                                <small class="text-muted">CCCD: {{ $tenant->cccd ?? 'N/A' }}</small>
                            </td>
                            <td class="text-center">
                                @if($tenant->gender == 'male')
                                    <span class="badge badge-primary">Nam</span>
                                @elseif($tenant->gender == 'female')
                                    <span class="badge badge-danger">Nữ</span>
                                @else
                                    <span class="badge badge-secondary">Khác</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="d-flex justify-content-end gap-1">
                                    <a href="{{ route('admin.tenants.edit', $tenant) }}"
                                       class="btn btn-sm btn-info btn-round" title="Sửa">
                                        <i class="fas fa-pen"></i>
                                    </a>
                                    <form action="{{ route('admin.tenants.destroy', $tenant) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('Xóa khách thuê này? Tài khoản đăng nhập cũng bị xóa!')">
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
                                <i class="fas fa-users fa-2x mb-2 d-block"></i>
                                Chưa có khách thuê nào.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($tenants->hasPages())
        <div class="card-footer">{{ $tenants->links() }}</div>
        @endif
    </div>

    <x-slot name="scripts">
    <script>
        $('#tenant-search').on('keyup', function() {
            var val = $(this).val().toLowerCase();
            $('#tenant-table tbody tr').filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(val) > -1);
            });
        });
    </script>
    </x-slot>
</x-app-layout>
