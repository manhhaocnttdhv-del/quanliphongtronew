<?php

namespace App\Http\Controllers\Booking;

use App\Http\Controllers\Controller;
use App\Http\Requests\Booking\StoreSignatureRequest;
use App\Models\BookingRequest;
use App\Models\BookingRequestAudit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Xử lý bước ký hợp đồng điện tử trong luồng đặt thuê phòng online.
 *
 * Khách hàng vẽ chữ ký trên canvas (signature_pad), client export PNG dưới
 * dạng data-URI base64 và submit lên `store()`. Controller decode, lưu file
 * vào `storage/app/public/contracts/signatures/{contract_id}/signature.png`,
 * cập nhật contract (signature_path, signed_at, signed_ip, signed_user_agent)
 * và ghi audit log. Sau khi ký xong, redirect khách sang trang đặt cọc.
 *
 * Tham chiếu: design.md mục 5.3 + Requirement 9.
 */
class BookingSignController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Hiển thị form ký hợp đồng. Yêu cầu khách đã upload đủ ảnh CCCD.
     */
    public function create(BookingRequest $bookingRequest)
    {
        $this->authorize('sign', $bookingRequest);

        if (! $bookingRequest->hasUploadedDocuments()) {
            return redirect()
                ->route('booking.documents.edit', $bookingRequest)
                ->with('error', 'Vui lòng hoàn tất upload giấy tờ trước khi ký.');
        }

        $bookingRequest->load(['room', 'tenant', 'contract']);

        return view('booking.sign', ['booking' => $bookingRequest]);
    }

    /**
     * Lưu chữ ký vào storage và đánh dấu hợp đồng đã ký.
     */
    public function store(StoreSignatureRequest $request, BookingRequest $bookingRequest)
    {
        $this->authorize('sign', $bookingRequest);

        $contract = $bookingRequest->contract;
        if (! $contract) {
            abort(409, 'Booking chưa có contract.');
        }

        // Decode base64 PNG (định dạng data-URI: "data:image/png;base64,...").
        $dataUri = $request->input('signature_data');
        $b64 = preg_replace('#^data:image/png;base64,#', '', $dataUri);
        $binary = base64_decode($b64, true);
        if ($binary === false) {
            return back()
                ->withErrors(['signature_data' => 'Chữ ký không hợp lệ.'])
                ->withInput();
        }

        $relPath = 'contracts/signatures/'.$contract->id.'/signature.png';
        Storage::disk('public')->put($relPath, $binary);

        DB::transaction(function () use ($contract, $relPath, $bookingRequest, $request) {
            $contract->update([
                'signature_path' => $relPath,
                'signed_at' => now(),
                'signed_ip' => $request->ip(),
                'signed_user_agent' => substr((string) $request->userAgent(), 0, 255),
            ]);

            BookingRequestAudit::create([
                'booking_request_id' => $bookingRequest->id,
                'event' => 'signed',
                'actor_user_id' => auth()->id(),
                'ip_address' => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 255),
                'metadata' => ['signature_path' => $relPath],
                'created_at' => now(),
            ]);
        });

        return redirect()
            ->route('booking.deposit.show', $bookingRequest)
            ->with('success', 'Đã ký xác nhận. Vui lòng tiến hành đặt cọc.');
    }
}
