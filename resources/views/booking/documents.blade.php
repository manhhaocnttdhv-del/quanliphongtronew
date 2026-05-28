@extends('layouts.customer')

@section('title', 'Upload giấy tờ')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card card-c">
            <div class="card-body p-4">
                <h3 class="mb-2"><i class="fas fa-id-card me-2 text-primary"></i>Bước 1: Upload CCCD</h3>
                <p class="text-muted">Vui lòng upload ảnh CCCD mặt trước và mặt sau (định dạng JPG/PNG/WEBP, tối đa 5MB mỗi ảnh).</p>

                <form method="POST" action="{{ route('booking.documents.update', $booking) }}" enctype="multipart/form-data">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">CCCD mặt trước <span class="text-danger">*</span></label>
                            @if($booking->tenant && $booking->tenant->cccd_front_path)
                                <div class="mb-2">
                                    <img src="{{ \Illuminate\Support\Facades\Storage::url($booking->tenant->cccd_front_path) }}" class="img-fluid rounded border" style="max-height:200px">
                                    <p class="small text-success mt-1"><i class="fas fa-check"></i> Đã upload — bạn có thể tải lại để thay thế.</p>
                                </div>
                            @endif
                            <input type="file" name="cccd_front" class="form-control @error('cccd_front') is-invalid @enderror" accept="image/jpeg,image/png,image/webp" required>
                            @error('cccd_front')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">CCCD mặt sau <span class="text-danger">*</span></label>
                            @if($booking->tenant && $booking->tenant->cccd_back_path)
                                <div class="mb-2">
                                    <img src="{{ \Illuminate\Support\Facades\Storage::url($booking->tenant->cccd_back_path) }}" class="img-fluid rounded border" style="max-height:200px">
                                    <p class="small text-success mt-1"><i class="fas fa-check"></i> Đã upload — bạn có thể tải lại để thay thế.</p>
                                </div>
                            @endif
                            <input type="file" name="cccd_back" class="form-control @error('cccd_back') is-invalid @enderror" accept="image/jpeg,image/png,image/webp" required>
                            @error('cccd_back')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="mt-4 d-flex gap-2">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-upload me-1"></i>Lưu giấy tờ</button>
                        <a href="{{ route('booking.show', $booking) }}" class="btn btn-outline-secondary">Hủy</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
