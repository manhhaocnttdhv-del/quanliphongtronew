<x-app-layout>
    <x-slot name="header">Chuyển Phòng — {{ $contract->tenant->user->name }}</x-slot>

    <div class="row">
        <div class="col-lg-10 mx-auto">
            <div class="card card-round">
                <div class="card-header bg-warning text-white">
                    <div class="card-head-row">
                        <div class="card-title fw-bold">
                            <i class="fas fa-exchange-alt me-2"></i> Làm thủ tục Chuyển Phòng
                        </div>
                        <a href="{{ route('admin.contracts.show', $contract) }}" class="btn btn-sm btn-light btn-round text-dark">
                            <i class="fas fa-arrow-left me-1"></i> Quay lại
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <strong>Lưu ý:</strong> Quá trình này sẽ thực hiện 2 việc cùng lúc:
                        <ul class="mb-0 mt-1">
                            <li>Thanh lý hợp đồng phòng hiện tại (Phòng {{ $contract->room->name }}).</li>
                            <li>Tạo hợp đồng mới với phòng được chọn, giữ nguyên khách thuê.</li>
                        </ul>
                    </div>

                    @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                    </div>
                    @endif

                    <form action="{{ route('admin.contracts.transfer', $contract) }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Khách thuê</label>
                                <input type="text" class="form-control bg-light" value="{{ $contract->tenant->user->name }} - CCCD: {{ $contract->tenant->cccd }}" readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Phòng hiện tại (Đang ở)</label>
                                <input type="text" class="form-control bg-light" value="P.{{ $contract->room->name }} - {{ $contract->room->house->name }}" readonly>
                            </div>

                            <div class="col-12"><hr></div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Chọn Phòng Mới (Chuyển đến) <span class="text-danger">*</span></label>
                                <select name="new_room_id" id="new_room_id" class="form-select" required>
                                    <option value="">-- Chọn phòng đang trống --</option>
                                    @foreach($availableRooms as $room)
                                        <option value="{{ $room->id }}" data-price="{{ $room->price }}" {{ old('new_room_id') == $room->id ? 'selected' : '' }}>
                                            P.{{ $room->name }} ({{ $room->house->name }}) - Giá: {{ number_format($room->price, 0, ',', '.') }} đ
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Ngày chuyển phòng (Bắt đầu tính) <span class="text-danger">*</span></label>
                                <input type="date" name="transfer_date" class="form-control" value="{{ old('transfer_date', date('Y-m-d')) }}" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Tiền cọc chuyển sang (VNĐ) <span class="text-danger">*</span></label>
                                <input type="number" name="deposit_transfer" class="form-control" value="{{ old('deposit_transfer', (int)$contract->deposit) }}" required>
                                <small class="text-muted">Mặc định giữ nguyên tiền cọc từ phòng cũ.</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Giá thuê phòng mới (VNĐ/Tháng) <span class="text-danger">*</span></label>
                                <input type="number" name="new_monthly_price" id="new_monthly_price" class="form-control" value="{{ old('new_monthly_price') }}" required>
                                <small class="text-muted">Sẽ tự động lấy theo giá phòng chọn, có thể sửa.</small>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label fw-bold">Các khoản phí phát sinh khác (nếu có)</label>
                                <input type="number" name="extra_fee" class="form-control" value="{{ old('extra_fee', 0) }}" min="0">
                                <small class="text-danger fw-bold"><i class="fas fa-exclamation-triangle"></i> KHÔNG nhập tiền bù cọc vào đây!</small><br>
                                <small class="text-muted">Chỉ nhập vào đây các loại tiền như: phí dọn dẹp vệ sinh phòng cũ, tiền phạt làm hư hỏng đồ, đền bù mất chìa khóa... Nếu không có thì để là 0.</small>
                            </div>

                            <div class="col-12 mb-3">
                                <label class="form-label fw-bold">Ghi chú thủ tục</label>
                                <textarea name="notes" rows="3" class="form-control" placeholder="Lý do chuyển, thỏa thuận đền bù hư hỏng phòng cũ (nếu có)...">{{ old('notes') }}</textarea>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="{{ route('admin.contracts.show', $contract) }}" class="btn btn-secondary btn-round">Hủy</a>
                            <button type="submit" class="btn btn-warning btn-round text-white fw-bold" onclick="return confirm('Xác nhận tiến hành chuyển phòng? Thao tác này sẽ đóng hợp đồng cũ ngay lập tức.')">
                                <i class="fas fa-check-circle me-1"></i> Hoàn Tắt Chuyển Phòng
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <x-slot name="scripts">
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const roomSelect = document.getElementById('new_room_id');
            const priceInput = document.getElementById('new_monthly_price');

            roomSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                if (selectedOption.value) {
                    priceInput.value = selectedOption.getAttribute('data-price');
                } else {
                    priceInput.value = '';
                }
            });
        });
    </script>
    </x-slot>
</x-app-layout>
