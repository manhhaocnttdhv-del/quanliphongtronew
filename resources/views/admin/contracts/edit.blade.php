<x-app-layout>
    <x-slot name="header">Cập Nhật Hợp Đồng: HD-{{ sprintf('%04d', $contract->id) }}</x-slot>

    <div class="row">
        <div class="col-lg-10 mx-auto">

            {{-- Thông tin readonly --}}
            <div class="card card-round mb-3 border-start border-4 border-primary">
                <div class="card-body py-3">
                    <div class="row text-center text-md-start">
                        <div class="col-md-4">
                            <small class="text-muted">Phòng thuê</small>
                            <p class="fw-bold mb-0">Phòng {{ $contract->room->name }} — {{ $contract->room->house->name }}</p>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted">Đại diện thuê</small>
                            <p class="fw-bold mb-0">{{ $contract->tenant->user->name }} · {{ $contract->tenant->phone }}</p>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted">Giá thuê / Tiền cọc</small>
                            <p class="fw-bold mb-0">
                                {{ number_format($contract->monthly_price,0,',','.')}}đ/tháng
                                · Cọc: {{ number_format($contract->deposit,0,',','.')}}đ
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card card-round">
                <div class="card-header">
                    <div class="card-head-row">
                        <div class="card-title">Cập nhật Trạng thái Hợp đồng</div>
                        <a href="{{ route('admin.contracts.index') }}" class="btn btn-sm btn-secondary btn-round">
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

                    <form action="{{ route('admin.contracts.update', $contract) }}" method="POST">
                        @csrf @method('PUT')
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="status">Tình trạng hợp đồng <span class="text-danger">*</span></label>
                                    <select id="status" name="status" class="form-select @error('status') is-invalid @enderror" required>
                                        <option value="active"     {{ old('status',$contract->status)=='active'     ?'selected':'' }}>Đang hiệu lực (Chưa trả phòng)</option>
                                        <option value="expired"    {{ old('status',$contract->status)=='expired'    ?'selected':'' }}>Hết hạn hợp đồng (Đã trả)</option>
                                        <option value="terminated" {{ old('status',$contract->status)=='terminated' ?'selected':'' }}>Thanh lý sớm (Đã trả)</option>
                                    </select>
                                    @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    <small class="text-muted">Đổi sang "Hết hạn" / "Thanh lý" sẽ tự trả phòng về trạng thái Trống.</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="terminated_at">Ngày thanh lý (nếu bỏ dở)</label>
                                    <input type="date" id="terminated_at" name="terminated_at"
                                           class="form-control @error('terminated_at') is-invalid @enderror"
                                           value="{{ old('terminated_at', $contract->terminated_at?->format('Y-m-d')) }}">
                                    @error('terminated_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="deposit_refund">Tiền cọc thực trả lại khách (VNĐ)</label>
                                    <input type="number" id="deposit_refund" name="deposit_refund"
                                           class="form-control @error('deposit_refund') is-invalid @enderror"
                                           value="{{ old('deposit_refund', (int)$contract->deposit_refund) }}">
                                    @error('deposit_refund')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="notes">Ghi chú / Lý do thanh lý</label>
                                    <textarea id="notes" name="notes" rows="3" class="form-control @error('notes') is-invalid @enderror">{{ old('notes', $contract->notes) }}</textarea>
                                    @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>

                        <div class="card-action d-flex justify-content-end gap-2 mt-3">
                            <a href="{{ route('admin.contracts.index') }}" class="btn btn-secondary btn-round">Hủy</a>
                            <button type="submit" class="btn btn-primary btn-round">
                                <i class="fas fa-save me-1"></i> Lưu Cập Nhật
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
