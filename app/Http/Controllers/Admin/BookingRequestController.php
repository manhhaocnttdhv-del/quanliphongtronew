<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\RoomNotAvailableException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Booking\ApproveBookingRequest;
use App\Http\Requests\Booking\RejectBookingRequest;
use App\Models\BookingRequest;
use App\Services\Booking\BookingApprovalService;
use Illuminate\Http\Request;

/**
 * Admin controller cho luồng duyệt / từ chối yêu cầu đặt phòng.
 *
 * Tham chiếu: design.md mục 5.3 và requirements.md Requirement 6.
 * - index/show: liệt kê + xem chi tiết yêu cầu.
 * - approve/reject: ủy thác toàn bộ logic transaction cho BookingApprovalService.
 */
class BookingRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = BookingRequest::with(['user', 'room.house'])
            ->orderByDesc('created_at');

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        $requests = $query->paginate(15)->withQueryString();

        return view('admin.booking_requests.index', [
            'requests' => $requests,
            'status'   => $status,
        ]);
    }

    public function show(BookingRequest $bookingRequest)
    {
        $bookingRequest->load([
            'user', 'room.house', 'tenant', 'contract',
            'audits.actor',
        ]);

        return view('admin.booking_requests.show', [
            'booking' => $bookingRequest,
        ]);
    }

    public function approve(ApproveBookingRequest $request, BookingRequest $bookingRequest, BookingApprovalService $service)
    {
        if ($bookingRequest->status !== 'pending') {
            return back()->with('error', 'Yêu cầu này không còn ở trạng thái chờ duyệt.');
        }

        try {
            $service->approve($bookingRequest, $request->user(), $request->validated());
        } catch (RoomNotAvailableException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('admin.booking-requests.show', $bookingRequest)
            ->with('success', 'Đã duyệt yêu cầu đặt phòng.');
    }

    public function reject(RejectBookingRequest $request, BookingRequest $bookingRequest, BookingApprovalService $service)
    {
        if ($bookingRequest->status !== 'pending') {
            return back()->with('error', 'Yêu cầu này không còn ở trạng thái chờ duyệt.');
        }

        $service->reject($bookingRequest, $request->user(), $request->input('rejected_reason'));

        return redirect()->route('admin.booking-requests.show', $bookingRequest)
            ->with('success', 'Đã từ chối yêu cầu đặt phòng.');
    }
}
