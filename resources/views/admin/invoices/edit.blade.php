<x-app-layout>
    <x-slot name="header">Ghi Nhận Thu Tiền — Hóa Đơn #{{ $invoice->id }}</x-slot>

    <div class="row">
        <div class="col-lg-10 mx-auto">
            {{-- Thông tin tổng quan hóa đơn --}}
            <div class="card card-round bg-primary text-white mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div>
                            <p class="mb-1 opacity-75 fw-semibold">
                                <i class="fas fa-user me-1"></i> {{ $invoice->contract->tenant->user->name ?? 'N/A' }}
                                &nbsp;·&nbsp; Phòng {{ $invoice->contract->room->name ?? 'N/A' }}
                            </p>
                            <p class="mb-0 opacity-75">
                                Kỳ Tháng {{ $invoice->month }}/{{ $invoice->year }}
                                &nbsp;·&nbsp; Hạn: <strong>{{ \Carbon\Carbon::parse($invoice->due_date)->format('d/m/Y') }}</strong>
                            </p>
                        </div>
                        <div class="text-end">
                            <p class="small opacity-75 mb-0">Tổng phải thu</p>
                            <h3 class="mb-0 fw-bold">{{ number_format($invoice->total,0,',','.')}}đ</h3>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card card-round">
                <div class="card-header">
                    <div class="card-head-row">
                        <div class="card-title">Cập nhật Trạng thái Thu</div>
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

                    <form action="{{ route('admin.invoices.update', $invoice) }}" method="POST">
                        @csrf @method('PUT')
                        <div class="row">
                            <div class="col-md-12 mt-2 mb-2">
                                <span class="fw-bold"><i class="fas fa-calculator me-1"></i> Biểu phí & Chỉ số</span>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="room_fee">Tiền phòng <span class="text-danger">*</span></label>
                                    <input type="number" id="room_fee" name="room_fee" class="form-control @error('room_fee') is-invalid @enderror"
                                           value="{{ old('room_fee', (int)$invoice->room_fee) }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="service_fee">Thu khác (Rác, Wifi, Xe...) <span class="text-danger">*</span></label>
                                    <input type="number" id="service_fee" name="service_fee" class="form-control @error('service_fee') is-invalid @enderror"
                                           value="{{ old('service_fee', (int)$invoice->service_fee) }}" required>
                                </div>
                            </div>

                            <div class="col-md-12 mt-2 mb-2">
                                <span class="fw-bold"><i class="fas fa-bolt text-warning me-1"></i> Điện</span>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="electricity_old">Chỉ số cũ <span class="text-danger">*</span></label>
                                    <input type="number" id="electricity_old" name="electricity_old" class="form-control @error('electricity_old') is-invalid @enderror"
                                           value="{{ old('electricity_old', (int)$invoice->electricity_old) }}" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="electricity_new">Chỉ số mới <span class="text-danger">*</span></label>
                                    <input type="number" id="electricity_new" name="electricity_new" class="form-control @error('electricity_new') is-invalid @enderror"
                                           value="{{ old('electricity_new', (int)$invoice->electricity_new) }}" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Đơn giá</label>
                                    <input type="text" id="electricity_price" class="form-control" 
                                        value="{{ $invoice->electricity_usage > 0 ? round($invoice->electricity_fee / $invoice->electricity_usage) : 0 }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="electricity_fee">Thành tiền (Điện) <span class="text-danger">*</span></label>
                                    <input type="number" id="electricity_fee" name="electricity_fee" class="form-control fw-bold @error('electricity_fee') is-invalid @enderror"
                                           value="{{ old('electricity_fee', (int)$invoice->electricity_fee) }}" readonly required>
                                </div>
                            </div>

                            <div class="col-md-12 mt-2 mb-2 border-top pt-3">
                                <span class="fw-bold"><i class="fas fa-tint text-info me-1"></i> Nước</span>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="water_old">Chỉ số cũ <span class="text-danger">*</span></label>
                                    <input type="number" id="water_old" name="water_old" class="form-control @error('water_old') is-invalid @enderror"
                                           value="{{ old('water_old', (int)$invoice->water_old) }}" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="water_new">Chỉ số mới <span class="text-danger">*</span></label>
                                    <input type="number" id="water_new" name="water_new" class="form-control @error('water_new') is-invalid @enderror"
                                           value="{{ old('water_new', (int)$invoice->water_new) }}" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Đơn giá</label>
                                    <input type="text" id="water_price" class="form-control" 
                                        value="{{ $invoice->water_usage > 0 ? round($invoice->water_fee / $invoice->water_usage) : 0 }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="water_fee">Thành tiền (Nước) <span class="text-danger">*</span></label>
                                    <input type="number" id="water_fee" name="water_fee" class="form-control fw-bold @error('water_fee') is-invalid @enderror"
                                           value="{{ old('water_fee', (int)$invoice->water_fee) }}" readonly required>
                                </div>
                            </div>
                            
                            <div class="col-md-12 mt-2 mb-2 border-top pt-3">
                                <span class="fw-bold"><i class="fas fa-money-check-alt me-1"></i> Thanh toán</span>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="paid_amount">Số tiền đã thu thực tế (VNĐ) <span class="text-danger">*</span></label>
                                    <input type="number" id="paid_amount" name="paid_amount"
                                           class="form-control form-control-lg fw-bold @error('paid_amount') is-invalid @enderror"
                                           value="{{ old('paid_amount', (int)$invoice->paid_amount) }}" required>
                                    @error('paid_amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    <small class="text-muted">Phần mềm tự tính ra số còn nợ.</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="status">Trạng thái hóa đơn <span class="text-danger">*</span></label>
                                    <select id="status" name="status" class="form-select @error('status') is-invalid @enderror" required>
                                        <option value="unpaid"  {{ old('status',$invoice->status)=='unpaid'  ?'selected':'' }}>Chưa thu đồng nào</option>
                                        <option value="partial" {{ old('status',$invoice->status)=='partial' ?'selected':'' }}>Đóng 1 phần (Khách nợ lại)</option>
                                        <option value="paid"    {{ old('status',$invoice->status)=='paid'    ?'selected':'' }}>Hoàn tất (Đã đóng đủ)</option>
                                        <option value="overdue" {{ old('status',$invoice->status)=='overdue' ?'selected':'' }}>Quá hạn chưa đóng</option>
                                    </select>
                                    @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="notes">Ghi chú</label>
                                    <textarea id="notes" name="notes" rows="3" class="form-control @error('notes') is-invalid @enderror"
                                              placeholder="VD: Khách hẹn gửi sau 500k...">{{ old('notes', $invoice->notes) }}</textarea>
                                    @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>
                        <div class="card-action d-flex justify-content-end gap-2 mt-3">
                            <a href="{{ route('admin.invoices.index') }}" class="btn btn-secondary btn-round">Hủy</a>
                            <button type="submit" class="btn btn-success btn-round">
                                <i class="fas fa-check me-1"></i> Cập nhật Số liệu Thu
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
    <x-slot name="scripts">
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const electricityOld = document.getElementById('electricity_old');
            const electricityNew = document.getElementById('electricity_new');
            const electricityPrice = document.getElementById('electricity_price');
            const electricityFee = document.getElementById('electricity_fee');

            const waterOld = document.getElementById('water_old');
            const waterNew = document.getElementById('water_new');
            const waterPrice = document.getElementById('water_price');
            const waterFee = document.getElementById('water_fee');

            function calculateFees() {
                const eOld = parseFloat(electricityOld.value) || 0;
                const eNew = parseFloat(electricityNew.value) || 0;
                const ePrice = parseFloat(electricityPrice.value) || 0;
                let eUsage = eNew - eOld;
                if (eUsage < 0) eUsage = 0;
                electricityFee.value = eUsage * ePrice;

                const wOld = parseFloat(waterOld.value) || 0;
                const wNew = parseFloat(waterNew.value) || 0;
                const wPrice = parseFloat(waterPrice.value) || 0;
                let wUsage = wNew - wOld;
                if (wUsage < 0) wUsage = 0;
                waterFee.value = wUsage * wPrice;
            }

            electricityOld.addEventListener('input', calculateFees);
            electricityNew.addEventListener('input', calculateFees);
            electricityPrice.addEventListener('input', calculateFees);
            waterOld.addEventListener('input', calculateFees);
            waterNew.addEventListener('input', calculateFees);
            waterPrice.addEventListener('input', calculateFees);
        });
    </script>
    </x-slot>
