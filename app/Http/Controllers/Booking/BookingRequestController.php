<?php

namespace App\Http\Controllers\Booking;

use App\Http\Controllers\Controller;
use App\Http\Requests\Booking\StoreBookingRequest;
use App\Models\BookingRequest;
use App\Models\BookingRequestAudit;
use App\Models\Room;
use App\Models\Tenant;
use App\Models\User;
use App\Notifications\Booking\BookingRequestSubmitted;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;

/**
 * Controller phía Customer cho luồng đặt thuê phòng online.
 *
 * Phụ trách: hiển thị form gửi yêu cầu, tạo BookingRequest mới, liệt kê
 * yêu cầu của user hiện tại, xem chi tiết và hủy yêu cầu khi còn pending.
 *
 * Các bước "hoàn tất hồ sơ" (upload CCCD, ký, thanh toán cọc) được tách
 * ra các controller riêng (BookingDocumentController, BookingSignController,
 * BookingDepositController) — xem design.md mục 5.3.
 */
class BookingRequestController extends Controller
{
    /**
     * Mọi action ở đây đều yêu cầu user đã đăng nhập.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Trang quản lý yêu cầu đặt thuê của user hiện tại — Requirement 5.1.
     */
    public function index()
    {
        $requests = BookingRequest::with('room.house')
            ->where('user_id', auth()->id())
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('booking.index', compact('requests'));
    }

    /**
     * Form gửi yêu cầu đặt thuê cho một phòng cụ thể — Requirement 4.1.
     *
     * Phòng phải đang ở trạng thái `available`; nếu không sẽ redirect về
     * trang chủ kèm thông báo lỗi (Requirement 4.3 + 11.4).
     */
    public function create(Request $request)
    {
        $roomId = $request->query('room_id');
        $room = $roomId
            ? Room::with(['house', 'roomType'])->find($roomId)
            : null;

        if (! $room || $room->status !== 'available') {
            return redirect()->route('home')
                ->with('error', 'Phòng này đã được đặt hoặc không còn trống');
        }

        return view('booking.create', compact('room'));
    }

    /**
     * Xử lý submit form gửi yêu cầu đặt thuê — Requirement 4.2 → 4.11.
     *
     * Bọc trong DB transaction + lockForUpdate trên Room để tránh race
     * condition khi nhiều khách cùng gửi yêu cầu cho một phòng. Sau khi
     * tạo thành công sẽ ghi audit `created` và bắn notification database
     * cho toàn bộ admin.
     */
    public function store(StoreBookingRequest $request)
    {
        $userId = auth()->id();
        $data = $request->validated();

        $br = DB::transaction(function () use ($data, $userId) {
            // Khóa pessimistic trên row Room để chặn race với admin/khách khác.
            $room = Room::lockForUpdate()->findOrFail($data['room_id']);

            // Requirement 4.3: phòng phải còn available tại thời điểm submit.
            if ($room->status !== 'available') {
                throw ValidationException::withMessages([
                    'room_id' => 'Phòng này đã được đặt hoặc không còn trống',
                ]);
            }

            // Requirement 4.4: không cho gửi thêm khi đã có pending cho cùng phòng.
            $duplicate = BookingRequest::where('user_id', $userId)
                ->where('room_id', $room->id)
                ->where('status', 'pending')
                ->exists();
            if ($duplicate) {
                throw ValidationException::withMessages([
                    'room_id' => 'Bạn đã có yêu cầu đang chờ duyệt cho phòng này',
                ]);
            }

            // Requirement 4.5: CCCD đã được tài khoản khác sử dụng.
            $cccdConflict = Tenant::where('cccd', $data['cccd'])
                ->where('user_id', '!=', $userId)
                ->exists();
            if ($cccdConflict) {
                throw ValidationException::withMessages([
                    'cccd' => 'Số CCCD này đã được sử dụng bởi tài khoản khác',
                ]);
            }

            $br = BookingRequest::create(array_merge($data, [
                'user_id' => $userId,
                'status' => 'pending',
                'last_status_changed_by' => $userId,
                'last_status_changed_at' => now(),
            ]));

            BookingRequestAudit::create([
                'booking_request_id' => $br->id,
                'event' => 'created',
                'actor_user_id' => $userId,
                'ip_address' => request()->ip(),
                'user_agent' => substr((string) request()->userAgent(), 0, 255),
                'metadata' => null,
                'created_at' => now(),
            ]);

            return $br;
        });

        // Requirement 4.10: notify toàn bộ admin về yêu cầu mới.
        $admins = User::role('admin')->get();
        if ($admins->isNotEmpty()) {
            Notification::send(
                $admins,
                new BookingRequestSubmitted($br->load(['user', 'room']))
            );
        }

        return redirect()->route('booking.index')
            ->with('success', 'Yêu cầu đặt thuê của bạn đã được gửi và đang chờ duyệt');
    }

    /**
     * Trang chi tiết một yêu cầu đặt thuê — Requirement 5.2 + 5.5.
     *
     * Policy `view` đảm bảo chỉ chủ yêu cầu hoặc admin xem được.
     */
    public function show(BookingRequest $bookingRequest)
    {
        $this->authorize('view', $bookingRequest);

        $bookingRequest->load([
            'room.house',
            'room.roomType',
            'tenant',
            'contract',
            'audits.actor',
        ]);

        return view('booking.show', ['booking' => $bookingRequest]);
    }

    /**
     * Hủy yêu cầu đặt thuê khi còn pending — Requirement 5.3 + 5.4.
     *
     * Policy `cancel` đã ràng buộc owner + status=pending; tại đây chỉ cần
     * cập nhật trạng thái và ghi audit.
     */
    public function cancel(BookingRequest $bookingRequest)
    {
        $this->authorize('cancel', $bookingRequest);

        DB::transaction(function () use ($bookingRequest) {
            $bookingRequest->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'last_status_changed_by' => auth()->id(),
                'last_status_changed_at' => now(),
            ]);

            BookingRequestAudit::create([
                'booking_request_id' => $bookingRequest->id,
                'event' => 'cancelled',
                'actor_user_id' => auth()->id(),
                'ip_address' => request()->ip(),
                'user_agent' => substr((string) request()->userAgent(), 0, 255),
                'metadata' => null,
                'created_at' => now(),
            ]);
        });

        return redirect()->route('booking.index')
            ->with('success', 'Đã hủy yêu cầu đặt phòng.');
    }
}
