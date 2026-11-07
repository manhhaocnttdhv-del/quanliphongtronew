<x-app-layout>
    <x-slot name="header">Nhắc hạn</x-slot>
    @include('admin.operations.errors')
    <form method="POST" action="{{ route('admin.reminders.update') }}" class="ops-form mb-4">
        @csrf @method('PUT')
        <div class="d-flex align-items-center flex-wrap gap-4 mb-3">
            <label class="form-check"><input class="form-check-input" type="checkbox" name="reminders_enabled" value="1" @checked(old('reminders_enabled', \App\Models\Setting::get('reminders_enabled', '1')) === '1')><span class="form-check-label">Bật nhắc hạn tự động</span></label>
            <span class="text-muted">Thông báo trong hệ thống / Hằng ngày 08:00 ({{ config('rentals.reminder_timezone') }})</span>
        </div>
        <div class="row g-3 mb-3">
            @foreach(['reminder_before_days' => ['Nhắc trước hạn hóa đơn (ngày)', 3, 0, 30], 'reminder_repeat_days' => ['Lặp nhắc nợ sau (ngày)', 7, 1, 30], 'reminder_contract_days' => ['Nhắc trước hết hợp đồng (ngày)', 30, 1, 90]] as $key => [$label, $default, $min, $max])
            <div class="col-md-4"><label class="form-label" for="{{ $key }}">{{ $label }}</label><input class="form-control" id="{{ $key }}" type="number" min="{{ $min }}" max="{{ $max }}" name="{{ $key }}" value="{{ old($key, \App\Models\Setting::get($key, $default)) }}" required></div>
            @endforeach
        </div>
        <button class="btn btn-primary"><i class="fas fa-save me-1" aria-hidden="true"></i> Lưu lịch nhắc</button>
    </form>
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 border-top pt-4 mb-3"><h2 class="h5 mb-0">Lịch sử gửi</h2><form method="POST" action="{{ route('admin.reminders.send') }}">@csrf<button class="btn btn-outline-primary"><i class="fas fa-paper-plane me-1" aria-hidden="true"></i> Gửi nhắc hạn</button></form></div>
    <div class="table-responsive"><table class="table"><thead><tr><th>Thời gian</th><th>Người nhận</th><th>Loại nhắc</th><th>Đối tượng</th></tr></thead><tbody>
    @forelse($deliveries as $delivery)<tr><td class="text-nowrap">{{ $delivery->sent_at->format('d/m/Y H:i') }}</td><td>{{ $delivery->user->name }}</td><td>{{ ['invoice_due' => 'Hóa đơn đến hạn', 'invoice_overdue' => 'Nhắc nợ', 'contract_ending' => 'Hợp đồng sắp hết hạn'][$delivery->kind] }}</td><td>{{ $delivery->subject_type === 'invoice' ? 'Hóa đơn' : 'Hợp đồng' }} #{{ $delivery->subject_id }}</td></tr>
    @empty<tr><td colspan="4" class="text-center py-5 text-muted">Chưa có thông báo nhắc hạn.</td></tr>@endforelse
    </tbody></table></div>
    {{ $deliveries->links() }}
</x-app-layout>
