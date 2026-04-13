<x-app-layout>
    <x-slot name="header">Báo Cáo Sự Cố</x-slot>

    <div class="card card-round">
        <div class="card-header">
            <div class="card-head-row flex-wrap gap-2">
                <div class="card-title">Danh sách yêu cầu sửa chữa</div>
                <form action="{{ route('admin.maintenance-tickets.index') }}" method="GET" class="d-flex gap-2 flex-wrap">
                    <select name="status" class="form-select form-select-sm" style="width:155px" onchange="this.form.submit()">
                        <option value="">-- Tất cả trạng thái</option>
                        <option value="pending"     {{ request('status')=='pending'     ?'selected':'' }}>Chờ xử lý</option>
                        <option value="in_progress" {{ request('status')=='in_progress' ?'selected':'' }}>Đang sửa</option>
                        <option value="done"        {{ request('status')=='done'        ?'selected':'' }}>Đã xong</option>
                        <option value="cancelled"   {{ request('status')=='cancelled'   ?'selected':'' }}>Đã huỷ</option>
                    </select>
                    <select name="priority" class="form-select form-select-sm" style="width:175px" onchange="this.form.submit()">
                        <option value="">-- Mức độ ưu tiên</option>
                        <option value="high"   {{ request('priority')=='high'  ?'selected':'' }}>Nghiêm trọng</option>
                        <option value="medium" {{ request('priority')=='medium'?'selected':'' }}>Bình thường</option>
                        <option value="low"    {{ request('priority')=='low'   ?'selected':'' }}>Thấp</option>
                    </select>
                    @if(request()->hasAny(['priority','status']))
                        <a href="{{ route('admin.maintenance-tickets.index') }}" class="btn btn-sm btn-light">Bỏ lọc</a>
                    @endif
                </form>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-items-center mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>ID</th>
                            <th>Sự cố</th>
                            <th>Phòng & Người báo</th>
                            <th class="text-center">Trạng thái</th>
                            <th>Ngày tạo</th>
                            <th class="text-end">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tickets as $ticket)
                        <tr>
                            <td class="fw-bold text-muted">#{{ $ticket->id }}</td>
                            <td>
                                <div class="fw-semibold" style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $ticket->title }}</div>
                                @if($ticket->priority=='high')
                                    <span class="badge badge-danger">Gấp</span>
                                @elseif($ticket->priority=='medium')
                                    <span class="badge badge-warning">Bình thường</span>
                                @endif
                            </td>
                            <td>
                                <div class="fw-semibold text-primary">P.{{ $ticket->contract->room->name ?? 'N/A' }}</div>
                                <small class="text-muted">{{ $ticket->contract->tenant->user->name ?? '' }}</small>
                            </td>
                            <td class="text-center">
                                @if($ticket->status=='pending')       <span class="badge badge-warning">Chờ xử lý</span>
                                @elseif($ticket->status=='in_progress') <span class="badge badge-primary">Đang sửa</span>
                                @elseif($ticket->status=='done')      <span class="badge badge-success">Hoàn tất</span>
                                @else                                 <span class="badge badge-secondary">Đã huỷ</span>
                                @endif
                            </td>
                            <td>{{ $ticket->created_at->format('d/m/Y H:i') }}</td>
                            <td class="text-end">
                                <a href="{{ route('admin.maintenance-tickets.edit', $ticket) }}"
                                   class="btn btn-sm btn-info btn-round" title="Xử lý">
                                    <i class="fas fa-pen"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="fas fa-check-circle text-success fa-2x mb-2 d-block"></i>
                                Chưa có sự cố nào được báo cáo.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($tickets->hasPages())
        <div class="card-footer">{{ $tickets->links() }}</div>
        @endif
    </div>
</x-app-layout>
