<x-app-layout>
    <x-slot name="header">Yêu cầu đặt phòng</x-slot>

    @php
        $statusMap = [
            'pending'   => ['label' => 'Chờ duyệt',    'class' => 'bg-warning text-dark'],
            'approved'  => ['label' => 'Đã duyệt',     'class' => 'bg-primary text-white'],
            'rejected'  => ['label' => 'Đã từ chối',   'class' => 'bg-danger text-white'],
            'cancelled' => ['label' => 'Đã hủy',       'class' => 'bg-secondary text-white'],
            'expired'   => ['label' => 'Hết hạn',      'class' => 'bg-dark text-white'],
        ];
    @endphp

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    <div class="card card-round">
        <div class="card-header">
            <div class="card-head-row">
                <div class="card-title">Danh sách yêu cầu đặt thuê</div>
                <form method="GET" class="d-flex align-items-center gap-2">
                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()" style="width:180px">
                        <option value="">Tất cả trạng thái</option>
                        @foreach($statusMap as $key => $info)
                            <option value="{{ $key }}" @selected($status === $key)>{{ $info['label'] }}</option>
                        @endforeach
                    </select>
                </form>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-items-center mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>#</th>
                            <th>Khách hàng</th>
                            <th>Phòng</th>
                            <th>Ngày gửi</th>
                            <th>Ngày chuyển vào</th>
                            <th class="text-center">Trạng thái</th>
                            <th class="text-end">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($requests as $req)
                            @php $st = $statusMap[$req->status] ?? ['label' => $req->status, 'class' => 'bg-secondary text-white']; @endphp
                            <tr>
                                <td>#{{ $req->id }}</td>
                                <td>
                                    <strong>{{ optional($req->user)->name }}</strong><br>
                                    <small class="text-muted">{{ optional($req->user)->email }}</small>
                                </td>
                                <td>
                                    {{ optional($req->room)->name }}<br>
                                    <small class="text-muted">{{ optional(optional($req->room)->house)->name }}</small>
                                </td>
                                <td>{{ $req->created_at->format('d/m/Y H:i') }}</td>
                                <td>{{ optional($req->desired_move_in_date)->format('d/m/Y') }}</td>
                                <td class="text-center"><span class="badge {{ $st['class'] }} px-3 py-2">{{ $st['label'] }}</span></td>
                                <td class="text-end">
                                    <a href="{{ route('admin.booking-requests.show', $req) }}" class="btn btn-sm btn-primary"><i class="fas fa-eye"></i> Chi tiết</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center py-5 text-muted"><i class="fas fa-inbox fa-2x mb-2 d-block"></i>Không có yêu cầu nào.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($requests->hasPages())
                <div class="p-3">{{ $requests->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>
