<x-app-layout>
    <x-slot name="header">Ghi Chỉ Số Điện / Nước</x-slot>

    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card card-round">
                <div class="card-header">
                    <div class="card-head-row">
                        <div class="card-title">Thông tin Chỉ số</div>
                        <a href="{{ route('admin.meter-readings.index') }}" class="btn btn-sm btn-secondary btn-round">
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

                    <form action="{{ route('admin.meter-readings.store') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="room_id">Phòng (Đang thuê) <span class="text-danger">*</span></label>
                                    <select id="room_id" name="room_id" class="form-select @error('room_id') is-invalid @enderror" required>
                                        <option value="">-- Chọn phòng --</option>
                                        @foreach($rooms as $room)
                                            @php
                                                $activeContract = $room->contracts->first();
                                                $tenantName = $activeContract && $activeContract->tenant && $activeContract->tenant->user ? ' — ' . $activeContract->tenant->user->name : '';
                                            @endphp
                                            <option value="{{ $room->id }}" {{ old('room_id')==$room->id?'selected':'' }}>
                                                Phòng {{ $room->name }} ({{ $room->house->name }}){{ $tenantName }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('room_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="service_id">Loại dịch vụ (Điện/Nước) <span class="text-danger">*</span></label>
                                    <select id="service_id" name="service_id" class="form-select @error('service_id') is-invalid @enderror" required>
                                        <option value="">-- Chọn loại --</option>
                                        @foreach($services as $service)
                                            <option value="{{ $service->id }}" {{ old('service_id')==$service->id?'selected':'' }}>
                                                {{ $service->name }} ({{ number_format($service->price,0,',','.')}}đ/{{ $service->unit }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('service_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="month">Kỳ tính (Tháng) <span class="text-danger">*</span></label>
                                    <select id="month" name="month" class="form-select @error('month') is-invalid @enderror" required>
                                        @for($i=1;$i<=12;$i++)
                                            <option value="{{ $i }}" {{ old('month',date('n'))==$i?'selected':'' }}>Tháng {{ $i }}</option>
                                        @endfor
                                    </select>
                                    @error('month')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="year">Kỳ tính (Năm) <span class="text-danger">*</span></label>
                                    <input type="number" id="year" name="year" class="form-control @error('year') is-invalid @enderror"
                                           value="{{ old('year', date('Y')) }}" required>
                                    @error('year')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>

                            <div class="col-12"><hr class="my-2"></div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="old_value">Chỉ số cũ <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" id="old_value" name="old_value"
                                           class="form-control @error('old_value') is-invalid @enderror"
                                           value="{{ old('old_value', 0) }}" required>
                                    @error('old_value')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="new_value">Chỉ số mới <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" id="new_value" name="new_value"
                                           class="form-control fw-bold @error('new_value') is-invalid @enderror"
                                           value="{{ old('new_value') }}" required placeholder="Phải >= Chỉ số cũ">
                                    @error('new_value')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>

                            {{-- Preview tiêu thụ --}}
                            <div class="col-12">
                                <div class="alert alert-info py-2" id="consumption-preview" style="display:none">
                                    <i class="fas fa-bolt me-1"></i>
                                    Tiêu thụ: <strong id="consumption-val">0</strong> đơn vị
                                </div>
                            </div>
                        </div>

                        <div class="card-action d-flex justify-content-end gap-2 mt-3">
                            <a href="{{ route('admin.meter-readings.index') }}" class="btn btn-secondary btn-round">Hủy</a>
                            <button type="submit" class="btn btn-primary btn-round">
                                <i class="fas fa-save me-1"></i> Lưu Chỉ Số
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <x-slot name="scripts">
    <script>
        function updatePreview() {
            var oldVal = parseFloat($('#old_value').val()) || 0;
            var newVal = parseFloat($('#new_value').val()) || 0;
            var diff = newVal - oldVal;
            if (newVal > 0) {
                $('#consumption-val').text(diff.toFixed(2));
                $('#consumption-preview').show();
            } else {
                $('#consumption-preview').hide();
            }
        }
        $('#old_value, #new_value').on('input', updatePreview);

        // Tự động load chỉ số mới nhất (old_value)
        function fetchOldValue() {
            var roomId = $('#room_id').val();
            var serviceId = $('#service_id').val();
            
            if(roomId && serviceId) {
                $.ajax({
                    url: '{{ route("admin.meter-readings.get-old-value") }}',
                    type: 'GET',
                    data: { room_id: roomId, service_id: serviceId },
                    success: function(res) {
                        $('#old_value').val(res.old_value);
                        updatePreview();
                    }
                });
            }
        }

        $('#room_id, #service_id').on('change', fetchOldValue);
        
        // Load lúc đầu nếu có sẵn giá trị
        if($('#room_id').val() && $('#service_id').val()) {
            fetchOldValue();
        }
    </script>
    </x-slot>
</x-app-layout>
