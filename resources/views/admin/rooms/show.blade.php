<x-app-layout>
    <x-slot name="header">Phòng {{ $room->name }}</x-slot>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="h5 mb-0">{{ $room->house->name }} / {{ $room->name }}</h2>
        <a href="{{ route('admin.rooms.edit', $room) }}" class="btn btn-primary"><i class="fas fa-pen me-1"></i>Chỉnh sửa</a>
    </div>
    <div class="row g-4">
        <div class="col-md-4"><x-room-image :room="$room" class="img-fluid w-100" /></div>
        <div class="col-md-8">
            <dl class="row">
                <dt class="col-5">Trạng thái</dt><dd class="col-7">{{ ['available' => 'Trống', 'reserved' => 'Giữ chỗ', 'rented' => 'Đang thuê', 'maintenance' => 'Bảo trì'][$room->status] ?? $room->status }}</dd>
                <dt class="col-5">Giá thuê</dt><dd class="col-7">{{ number_format($room->price, 0, ',', '.') }} đ/tháng</dd>
                <dt class="col-5">Diện tích</dt><dd class="col-7">{{ $room->area }} m²</dd>
                <dt class="col-5">Sức chứa</dt><dd class="col-7">{{ $room->max_occupants }} người</dd>
            </dl>
            <p>{{ $room->description }}</p>
        </div>
    </div>
    <h3 class="h6 mt-4">Lịch sử hợp đồng</h3>
    <div class="table-responsive">
        <table class="table">
            <thead><tr><th>Hợp đồng</th><th>Khách thuê</th><th>Thời hạn</th><th>Trạng thái</th></tr></thead>
            <tbody>
            @forelse($room->contracts as $contract)
                <tr><td><a href="{{ route('admin.contracts.show', $contract) }}">#{{ $contract->id }}</a></td><td>{{ $contract->tenant->user->name }}</td><td>{{ $contract->start_date->format('d/m/Y') }} - {{ $contract->end_date->format('d/m/Y') }}</td><td>{{ $contract->status }}</td></tr>
            @empty
                <tr><td colspan="4">Chưa có hợp đồng.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</x-app-layout>
