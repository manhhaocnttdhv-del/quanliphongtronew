<?php

namespace App\Http\Controllers\Booking;

use App\Http\Controllers\Controller;
use App\Http\Requests\Booking\UpdateBookingDocumentsRequest;
use App\Models\BookingRequest;
use App\Models\BookingRequestAudit;
use Illuminate\Support\Facades\DB;

/**
 * Controller xử lý bước upload giấy tờ (CCCD mặt trước/sau) trong luồng booking.
 *
 * Theo design.md mục 5.3 — sau khi admin duyệt yêu cầu, khách hàng cần upload
 * 2 ảnh CCCD trước khi tiếp tục bước ký hợp đồng điện tử.
 */
class BookingDocumentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Hiển thị form upload CCCD cho yêu cầu đặt thuê đã được duyệt.
     */
    public function edit(BookingRequest $bookingRequest)
    {
        $this->authorize('uploadDocuments', $bookingRequest);

        $bookingRequest->load(['room', 'tenant']);

        return view('booking.documents', ['booking' => $bookingRequest]);
    }

    /**
     * Lưu ảnh CCCD vào storage và cập nhật Tenant tương ứng,
     * sau đó chuyển hướng tới trang ký hợp đồng.
     */
    public function update(UpdateBookingDocumentsRequest $request, BookingRequest $bookingRequest)
    {
        $this->authorize('uploadDocuments', $bookingRequest);

        $tenant = $bookingRequest->tenant;
        if (! $tenant) {
            abort(409, 'Booking chưa có tenant; cần admin duyệt trước.');
        }

        $front = $request->file('cccd_front')->store('tenants/cccd/' . $tenant->id, 'public');
        $back  = $request->file('cccd_back')->store('tenants/cccd/' . $tenant->id, 'public');

        DB::transaction(function () use ($tenant, $front, $back, $bookingRequest) {
            $tenant->update([
                'cccd_front_path' => $front,
                'cccd_back_path'  => $back,
            ]);

            BookingRequestAudit::create([
                'booking_request_id' => $bookingRequest->id,
                'event'              => 'documents_uploaded',
                'actor_user_id'      => auth()->id(),
                'ip_address'         => request()->ip(),
                'user_agent'         => substr((string) request()->userAgent(), 0, 255),
                'metadata'           => [
                    'cccd_front_path' => $front,
                    'cccd_back_path'  => $back,
                ],
                'created_at'         => now(),
            ]);
        });

        return redirect()->route('booking.sign.create', $bookingRequest)
            ->with('success', 'Đã upload giấy tờ. Vui lòng tiến hành ký xác nhận điện tử.');
    }
}
