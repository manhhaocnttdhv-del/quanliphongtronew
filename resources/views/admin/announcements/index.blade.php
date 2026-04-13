<x-app-layout>
    <x-slot name="header">Bản Tin & Thông Báo</x-slot>

    <div class="card card-round">
        <div class="card-header">
            <div class="card-head-row">
                <div class="card-title">Danh sách thông báo</div>
                <div class="card-tools">
                    <a href="{{ route('admin.announcements.create') }}" class="btn btn-primary btn-round btn-sm">
                        <i class="fas fa-plus me-1"></i> Tạo Thông báo
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-items-center mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>ID</th>
                            <th>Tiêu đề</th>
                            <th>Phạm vi</th>
                            <th class="text-center">Trạng thái</th>
                            <th class="text-end">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($announcements as $announcement)
                        <tr>
                            <td class="fw-bold text-muted">#{{ $announcement->id }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    @if($announcement->is_pinned)
                                        <i class="fas fa-thumbtack text-warning" title="Ghim"></i>
                                    @endif
                                    @if($announcement->type=='warning')
                                        <span class="badge badge-danger me-1">Cảnh báo</span>
                                    @elseif($announcement->type=='event')
                                        <span class="badge badge-purple me-1">Sự kiện</span>
                                    @endif
                                    <span class="fw-semibold">{{ $announcement->title }}</span>
                                </div>
                                <small class="text-muted">{{ Str::limit($announcement->content, 60) }}</small>
                            </td>
                            <td>
                                @if($announcement->house_id)
                                    <span class="text-primary fw-semibold">{{ $announcement->house->name }}</span>
                                @else
                                    <span class="text-muted">Tất cả khu trọ</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($announcement->published_at)
                                    <span class="badge badge-success">Đã đăng</span>
                                    <small class="d-block text-muted mt-1">{{ \Carbon\Carbon::parse($announcement->published_at)->diffForHumans() }}</small>
                                @else
                                    <span class="badge badge-secondary">Nháp</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="d-flex justify-content-end gap-1">
                                    <a href="{{ route('admin.announcements.edit', $announcement) }}"
                                       class="btn btn-sm btn-info btn-round" title="Sửa">
                                        <i class="fas fa-pen"></i>
                                    </a>
                                    <form action="{{ route('admin.announcements.destroy', $announcement) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('Xóa thông báo này vĩnh viễn?')">
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
                            <td colspan="5" class="text-center py-5 text-muted">Chưa có thông báo nào.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($announcements->hasPages())
        <div class="card-footer">{{ $announcements->links() }}</div>
        @endif
    </div>
</x-app-layout>
