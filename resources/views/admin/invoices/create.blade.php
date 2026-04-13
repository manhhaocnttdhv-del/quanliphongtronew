<x-app-layout>
    <x-slot name="header">Lập Hóa Đơn Thu Tiền Mới</x-slot>

    <div class="row">
        <div class="col-lg-10 mx-auto">
            <div class="card card-round">
                <div class="card-header">
                    <div class="card-head-row">
                        <div class="card-title">Thông tin Hóa đơn</div>
                        <a href="{{ route('admin.invoices.index') }}" class="btn btn-sm btn-secondary btn-round">
                            <i class="fas fa-arrow-left me-1"></i> Quay lại
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show">
                        <strong><i class="fas fa-exclamation-triangle me-1"></i>Có lỗi:</strong>
                        <ul class="mb-0 mt-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    @endif

                    <form action="{{ route('admin.invoices.store') }}" method="POST">
                        @csrf

                        <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">
                            <i class="fas fa-info-circle me-1"></i> Thông tin chung
                        </h6>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="contract_id">Thu tiền phòng nào <span class="text-danger">*</span></label>
                                    <select id="contract_id" name="contract_id" class="form-select @error('contract_id') is-invalid @enderror" required>
                                        <option value="">-- Chọn phòng / hợp đồng --</option>
                                        @foreach($contracts as $contract)
                                            <option value="{{ $contract->id }}" {{ old('contract_id')==$contract->id?'selected':'' }}>
                                                Phòng {{ $contract->room->name }} — {{ $contract->tenant->user->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('contract_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="month">Tháng <span class="text-danger">*</span></label>
                                    <input type="number" id="month" name="month" class="form-control @error('month') is-invalid @enderror"
                                           value="{{ old('month', date('n')) }}" min="1" max="12" required>
                                    @error('month')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="year">Năm <span class="text-danger">*</span></label>
                                    <input type="number" id="year" name="year" class="form-control @error('year') is-invalid @enderror"
                                           value="{{ old('year', date('Y')) }}" required>
                                    @error('year')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="due_date">Hạn chót thanh toán <span class="text-danger">*</span></label>
                                    <input type="date" id="due_date" name="due_date" class="form-control @error('due_date') is-invalid @enderror"
                                           value="{{ old('due_date', \Carbon\Carbon::now()->addDays(5)->format('Y-m-d')) }}" required>
                                    @error('due_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>

                        <h6 class="fw-bold text-primary border-bottom pb-2 mb-3 mt-3">
                            <i class="fas fa-calculator me-1"></i> Phân rã Biểu phí
                        </h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="room_fee">Tiền phòng <span class="text-danger">*</span></label>
                                    <input type="number" id="room_fee" name="room_fee" class="form-control @error('room_fee') is-invalid @enderror"
                                           value="{{ old('room_fee', 0) }}" required>
                                    @error('room_fee')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="electricity_fee">Tiền Điện <span class="text-danger">*</span></label>
                                    <input type="number" id="electricity_fee" name="electricity_fee" class="form-control @error('electricity_fee') is-invalid @enderror"
                                           value="{{ old('electricity_fee', 0) }}" required>
                                    @error('electricity_fee')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    <small class="text-muted">Lấy từ mục Chỉ số Điện/Nước</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="water_fee">Tiền Nước <span class="text-danger">*</span></label>
                                    <input type="number" id="water_fee" name="water_fee" class="form-control @error('water_fee') is-invalid @enderror"
                                           value="{{ old('water_fee', 0) }}" required>
                                    @error('water_fee')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="service_fee">Thu khác (Rác, Wifi, Xe...) <span class="text-danger">*</span></label>
                                    <input type="number" id="service_fee" name="service_fee" class="form-control @error('service_fee') is-invalid @enderror"
                                           value="{{ old('service_fee', 0) }}" required>
                                    @error('service_fee')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="notes">Ghi chú Hóa đơn</label>
                                    <textarea id="notes" name="notes" rows="2" class="form-control @error('notes') is-invalid @enderror"
                                              placeholder="VD: Đã bao gồm phí sửa ống nước tháng trước">{{ old('notes') }}</textarea>
                                    @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>

                        <div id="auto-calc-warning" class="alert alert-warning mt-3" style="display: none;">
                            <i class="fas fa-exclamation-triangle me-1"></i> <span id="auto-calc-msg"></span>
                        </div>

                        <div class="card-action d-flex justify-content-end gap-2 mt-3">
                            <a href="{{ route('admin.invoices.index') }}" class="btn btn-secondary btn-round">Hủy</a>
                            <button type="submit" class="btn btn-primary btn-round">
                                <i class="fas fa-file-invoice-dollar me-1"></i> Lập Hóa Đơn Mới
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
            const contractSelect = document.getElementById('contract_id');
            const monthInput = document.getElementById('month');
            const yearInput = document.getElementById('year');

            const roomFee = document.getElementById('room_fee');
            const electricityFee = document.getElementById('electricity_fee');
            const waterFee = document.getElementById('water_fee');
            const serviceFee = document.getElementById('service_fee');
            const warningDiv = document.getElementById('auto-calc-warning');
            const warningMsg = document.getElementById('auto-calc-msg');

            function fetchCalculations() {
                const contractId = contractSelect.value;
                const month = monthInput.value;
                const year = yearInput.value;

                if (!contractId || !month || !year) return;

                warningDiv.style.display = 'none';

                fetch(`{{ route('admin.invoices.auto-calculate') }}?contract_id=${contractId}&month=${month}&year=${year}`)
                    .then(response => response.json())
                    .then(data => {
                        roomFee.value = data.room_fee || 0;
                        serviceFee.value = data.service_fee || 0;
                        
                        electricityFee.value = data.electricity_fee !== null ? data.electricity_fee : 0;
                        waterFee.value = data.water_fee !== null ? data.water_fee : 0;

                        if (data.electricity_fee === null || data.water_fee === null) {
                            warningMsg.textContent = 'Phòng này CHƯA được cập nhật đầy đủ chỉ số Điện/Nước cho tháng ' + month + '/' + year + '. Vui lòng kiểm tra lại!';
                            warningDiv.style.display = 'block';
                        }
                    })
                    .catch(error => console.error('Error fetching fees:', error));
            }

            contractSelect.addEventListener('change', fetchCalculations);
            monthInput.addEventListener('change', fetchCalculations);
            yearInput.addEventListener('change', fetchCalculations);
            
            // Trigger once on load if values are already selected (e.g. going back from validation error)
            if (contractSelect.value) {
                fetchCalculations();
            }
        });
    </script>
    </x-slot>
</x-app-layout>
