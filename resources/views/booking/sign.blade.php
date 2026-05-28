@extends('layouts.customer')

@section('title', 'Ký hợp đồng')

@push('styles')
<style>
    #signature-pad {
        border: 2px dashed #cbd5e1;
        border-radius: 12px;
        background: #f8fafc;
        cursor: crosshair;
        width: 100%;
        max-width: 700px;
        height: 220px;
    }
</style>
@endpush

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="card card-c">
            <div class="card-body p-4">
                <h3 class="mb-2"><i class="fas fa-pen-fancy me-2 text-primary"></i>Bước 2: Ký xác nhận hợp đồng</h3>
                <p class="text-muted">Vui lòng đọc kỹ điều khoản và vẽ chữ ký để xác nhận đồng ý.</p>

                <div class="card shadow-sm border mb-4">
                    <div class="card-header bg-light py-2">
                        <h6 class="mb-0 fw-bold"><i class="fas fa-file-alt text-primary me-2"></i>Dự thảo Hợp đồng thuê phòng</h6>
                    </div>
                    <div class="card-body bg-white text-dark scrollable-contract-body" style="max-height: 350px; overflow-y: auto; font-family: 'Times New Roman', Times, serif; font-size: 0.95rem; line-height: 1.6; padding: 25px; border-bottom: 1px solid #dee2e6;">
                        <div class="text-center mb-3">
                            <h5 class="fw-bold mb-0">CỘNG HÒA XÃ HỘI CHỦ NGHĨA VIỆT NAM</h5>
                            <div class="fw-semibold mb-1" style="text-decoration: underline; font-size: 0.85rem;">Độc lập - Tự do - Hạnh phúc</div>
                            <small class="text-muted italic">Ngày {{ now()->format('d/m/Y') }}</small>
                        </div>
                        
                        <div class="text-center mb-4">
                            <h4 class="fw-bold text-uppercase">HỢP ĐỒNG THUÊ PHÒNG TRỌ</h4>
                            <div class="small">Số: HĐ-{{ str_pad(optional($booking->contract)->id ?? 0, 4, '0', STR_PAD_LEFT) }}/{{ now()->format('Y') }}</div>
                        </div>

                        <p class="mb-2"><em>Căn cứ Bộ luật Dân sự nước Cộng hòa Xã hội Chủ nghĩa Việt Nam năm 2015 và các văn bản liên quan.</em></p>
                        
                        <div class="fw-bold text-uppercase mt-3 mb-2" style="border-bottom: 1px solid #dee2e6; font-size: 0.85rem;">Bên Cho Thuê (Bên A)</div>
                        <p class="mb-1"><strong>Chủ nhà / Đại diện quản lý:</strong> {{ \App\Models\Setting::get('site_name', 'BoardingPro') }}</p>
                        <p class="mb-1"><strong>Địa chỉ khu trọ:</strong> {{ optional(optional($booking->room)->house)->address ?? 'Chưa cập nhật' }}</p>
                        <p class="mb-3"><strong>Số điện thoại:</strong> {{ optional(optional($booking->room)->house)->phone ?? 'Chưa cập nhật' }}</p>

                        <div class="fw-bold text-uppercase mt-3 mb-2" style="border-bottom: 1px solid #dee2e6; font-size: 0.85rem;">Bên Thuê (Bên B)</div>
                        <p class="mb-1"><strong>Họ và tên:</strong> {{ auth()->user()->name }}</p>
                        <p class="mb-1"><strong>Số điện thoại:</strong> {{ $booking->phone }}</p>
                        <p class="mb-1"><strong>Số CCCD:</strong> {{ $booking->cccd }}</p>
                        <p class="mb-1"><strong>Ngày sinh:</strong> {{ optional($booking->birthday)->format('d/m/Y') ?? 'Chưa cập nhật' }}</p>
                        <p class="mb-3"><strong>Địa chỉ thường trú:</strong> {{ $booking->address ?? 'Chưa cập nhật' }}</p>

                        <p class="mb-3">Hai bên thống nhất thỏa thuận các điều khoản sau đây:</p>

                        <p class="mb-1"><strong>Điều 1: Đối tượng thuê</strong></p>
                        <p class="mb-3">Bên A đồng ý cho Bên B thuê phòng số <strong>{{ optional($booking->room)->name }}</strong> thuộc khu trọ <strong>{{ optional(optional($booking->room)->house)->name }}</strong>. Diện tích sử dụng: {{ optional($booking->room)->area }} m².</p>

                        <p class="mb-1"><strong>Điều 2: Thời hạn thuê</strong></p>
                        <p class="mb-3">Thời hạn thuê bắt đầu từ ngày <strong>{{ optional(optional($booking->contract)->start_date ?? $booking->desired_move_in_date)->format('d/m/Y') }}</strong> đến ngày <strong>{{ optional(optional($booking->contract)->end_date ?? $booking->desired_move_in_date->addMonths($booking->desired_lease_months))->format('d/m/Y') }}</strong>.</p>

                        <p class="mb-1"><strong>Điều 3: Giá thuê và tiền đặt cọc</strong></p>
                        <p class="mb-1">- Giá thuê phòng hàng tháng: <strong class="text-primary">{{ number_format(optional($booking->contract)->monthly_price ?? optional($booking->room)->price, 0, ',', '.') }} đ</strong>/tháng.</p>
                        <p class="mb-3">- Tiền đặt cọc giữ phòng: <strong class="text-primary">{{ number_format(optional($booking->contract)->deposit ?? $booking->deposit_amount, 0, ',', '.') }} đ</strong>. Tiền đặt cọc sẽ được hoàn trả đầy đủ khi thanh lý hợp đồng đúng hạn.</p>

                        <p class="mb-1"><strong>Điều 4: Trách nhiệm mỗi bên</strong></p>
                        <p class="mb-1">- Bên A: Đảm bảo cơ sở hạ tầng, bàn giao phòng sạch sẽ, hỗ trợ sửa chữa các lỗi tự nhiên của phòng trọ.</p>
                        <p class="mb-3">- Bên B: Thanh toán tiền phòng đúng hạn (ngày 05 hàng tháng), chấp hành nội quy an ninh, trật tự và quy định phòng cháy chữa cháy.</p>

                        <p class="mb-1"><strong>Điều 5: Ghi chú bổ sung</strong></p>
                        <p class="mb-0">{{ optional($booking->contract)->notes ?? 'Không có ghi chú thêm.' }}</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('booking.sign.store', $booking) }}" id="sign-form">
                    @csrf
                    <input type="hidden" name="signature_data" id="signature_data">

                    <label class="form-label fw-bold">Chữ ký của bạn:</label>
                    <div class="mb-2"><canvas id="signature-pad"></canvas></div>
                    <div class="mb-3">
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="clearBtn"><i class="fas fa-eraser me-1"></i>Xóa</button>
                    </div>

                    <div class="form-check mb-3">
                        <input type="checkbox" name="agreed" id="agreed" class="form-check-input" required>
                        <label for="agreed" class="form-check-label">Tôi đã đọc và đồng ý với các điều khoản hợp đồng thuê phòng.</label>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-pen-fancy me-1"></i>Xác nhận ký</button>
                        <a href="{{ route('booking.show', $booking) }}" class="btn btn-outline-secondary">Hủy</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4/dist/signature_pad.umd.min.js"></script>
<script>
    const canvas = document.getElementById('signature-pad');
    function resize() {
        const ratio = Math.max(window.devicePixelRatio || 1, 1);
        canvas.width = canvas.offsetWidth * ratio;
        canvas.height = canvas.offsetHeight * ratio;
        canvas.getContext('2d').scale(ratio, ratio);
        if (typeof signaturePad !== 'undefined') signaturePad.clear();
    }
    const signaturePad = new SignaturePad(canvas, {
        backgroundColor: 'rgba(255,255,255,0)',
        penColor: '#1f2937'
    });
    window.addEventListener('resize', resize);
    resize();

    document.getElementById('clearBtn').addEventListener('click', () => signaturePad.clear());

    document.getElementById('sign-form').addEventListener('submit', (e) => {
        if (signaturePad.isEmpty()) {
            e.preventDefault();
            alert('Vui lòng vẽ chữ ký trước khi xác nhận.');
            return false;
        }
        document.getElementById('signature_data').value = signaturePad.toDataURL('image/png');
    });
</script>
@endpush
