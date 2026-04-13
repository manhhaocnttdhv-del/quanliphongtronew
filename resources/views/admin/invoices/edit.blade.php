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
