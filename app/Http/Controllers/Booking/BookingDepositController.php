<?php

namespace App\Http\Controllers\Booking;

use App\Http\Controllers\Controller;
use App\Models\BookingRequest;
use App\Services\Booking\BookingDepositService;

/**
 * Controller hiển thị màn hình đặt cọc và khởi tạo phiên thanh toán PayOS.
 *
 * Tham chiếu: design.md mục 5.3 và Requirement 10.
 *
 * Hai action chính:
 *  - `show()`  : Render trang `booking.deposit` với Invoice cọc (idempotent
 *                qua `BookingDepositService::createDepositInvoice`).
 *  - `pay()`   : Tạo (hoặc lấy lại) Invoice cọc rồi redirect sang
 *                `payment.checkout` để cổng PayOS xử lý phần còn lại.
 *
 * Cả hai action đều bắt buộc:
 *  1. User đã đăng nhập (middleware `auth`).
 *  2. User là chủ BookingRequest và BookingRequest đã được ký
 *     (kiểm tra qua policy `pay`).
 *  3. Hợp đồng đã ký — nếu chưa, redirect về trang ký và báo lỗi để
 *     tránh trường hợp khách bỏ qua bước ký.
 */
class BookingDepositController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Hiển thị màn hình đặt cọc với thông tin Invoice cọc đã phát hành.
     */
    public function show(BookingRequest $bookingRequest, BookingDepositService $depositService)
    {
        $this->authorize('pay', $bookingRequest);

        if (! $bookingRequest->isSigned()) {
            return redirect()->route('booking.sign.create', $bookingRequest)
                ->with('error', 'Vui lòng ký xác nhận hợp đồng trước khi đặt cọc.');
        }

        $invoice = $depositService->createDepositInvoice($bookingRequest);
        $bookingRequest->load(['room.house', 'contract']);

        return view('booking.deposit', [
            'booking' => $bookingRequest,
            'invoice' => $invoice,
        ]);
    }

    /**
     * Khởi tạo (hoặc reuse) Invoice cọc và redirect sang cổng thanh toán PayOS.
     */
    public function pay(BookingRequest $bookingRequest, BookingDepositService $depositService)
    {
        $this->authorize('pay', $bookingRequest);

        if (! $bookingRequest->isSigned()) {
            return redirect()->route('booking.sign.create', $bookingRequest)
                ->with('error', 'Vui lòng ký xác nhận hợp đồng trước khi đặt cọc.');
        }

        $invoice = $depositService->createDepositInvoice($bookingRequest);

        return redirect()->route('payment.checkout', $invoice->id);
    }
}
