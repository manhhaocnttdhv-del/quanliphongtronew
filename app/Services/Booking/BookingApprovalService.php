<?php

namespace App\Services\Booking;

use App\Exceptions\RoomNotAvailableException;
use App\Models\BookingRequest;
use App\Models\BookingRequestAudit;
use App\Models\Contract;
use App\Models\Room;
use App\Models\Tenant;
use App\Models\User;
use App\Notifications\Booking\BookingRequestApproved;
use App\Notifications\Booking\BookingRequestRejected;
use Illuminate\Support\Facades\DB;

/**
 * Service xử lý transaction Approve / Reject cho `BookingRequest`.
 *
 * Tham chiếu: design.md mục 4.1 — toàn bộ thao tác Approve được bọc trong
 * `DB::transaction()` với `lockForUpdate()` trên Room nhằm tránh race khi
 * hai admin cùng duyệt hai yêu cầu cho cùng một phòng.
 */
class BookingApprovalService
{
    /**
     * Duyệt yêu cầu đặt thuê: tạo Tenant, Contract draft, đổi role User
     * từ customer→tenant, đổi Room sang `reserved`, cập nhật BookingRequest,
     * ghi audit và gửi notification cho khách hàng.
     *
     * Toàn bộ thao tác trong một transaction + `lockForUpdate` trên Room.
     *
     * @param  BookingRequest  $br     Yêu cầu cần duyệt.
     * @param  User            $admin  Admin thực hiện duyệt (dùng cho audit + last_status_changed_by).
     * @param  array           $data   Payload từ ApproveBookingRequest:
     *                                 keys: start_date, end_date, deposit_amount, admin_note (nullable).
     *
     * @throws RoomNotAvailableException khi Room đã không còn ở trạng thái `available`.
     */
    public function approve(BookingRequest $br, User $admin, array $data): BookingRequest
    {
        return DB::transaction(function () use ($br, $admin, $data) {
            // Pessimistic lock để tránh duyệt song song trên cùng một phòng.
            $room = Room::lockForUpdate()->findOrFail($br->room_id);

            if ($room->status !== 'available') {
                throw new RoomNotAvailableException('Phòng này không còn trống để duyệt.');
            }

            // 1. Đảm bảo Tenant tồn tại cho user (firstOrCreate dựa trên user_id).
            //    Thông tin nhân thân lấy từ snapshot trong BookingRequest.
            $tenant = Tenant::firstOrCreate(
                ['user_id' => $br->user_id],
                [
                    'cccd'     => $br->cccd,
                    'phone'    => $br->phone,
                    'address'  => $br->address,
                    'hometown' => $br->hometown,
                    'birthday' => $br->birthday,
                    'gender'   => $br->gender,
                ]
            );

            // 2. Tạo Contract status=draft, chờ ký + thanh toán cọc để kích hoạt.
            $contract = Contract::create([
                'room_id'       => $room->id,
                'tenant_id'     => $tenant->id,
                'start_date'    => $data['start_date'],
                'end_date'      => $data['end_date'],
                'deposit'       => $data['deposit_amount'],
                'monthly_price' => $room->price,
                'occupants'     => $br->desired_occupants,
                'status'        => 'draft',
                'notes'         => $data['admin_note'] ?? null,
            ]);

            // 3. Đổi role User: customer → tenant (sync để xoá role customer).
            $user = User::findOrFail($br->user_id);
            $user->syncRoles(['tenant']);

            // 4. Đổi Room sang reserved (đã có người giữ chỗ, chưa active).
            $room->update(['status' => 'reserved']);

            // 5. Cập nhật BookingRequest sang trạng thái approved + liên kết tenant/contract.
            $br->update([
                'tenant_id'              => $tenant->id,
                'contract_id'            => $contract->id,
                'deposit_amount'         => $data['deposit_amount'],
                'admin_note'             => $data['admin_note'] ?? null,
                'status'                 => 'approved',
                'approved_at'            => now(),
                'last_status_changed_by' => $admin->id,
                'last_status_changed_at' => now(),
            ]);

            // 6. Audit log — append-only, ghi nhận hành vi của admin.
            BookingRequestAudit::create([
                'booking_request_id' => $br->id,
                'event'              => 'approved',
                'actor_user_id'      => $admin->id,
                'ip_address'         => request()->ip(),
                'user_agent'         => substr((string) request()->userAgent(), 0, 255),
                'metadata'           => [
                    'tenant_id'      => $tenant->id,
                    'contract_id'    => $contract->id,
                    'deposit_amount' => $data['deposit_amount'],
                ],
                'created_at'         => now(),
            ]);

            // 7. Notify owner — kèm bản fresh có sẵn quan hệ user/room cho payload.
            $user->notify(new BookingRequestApproved($br->fresh(['user', 'room'])));

            return $br->fresh();
        });
    }

    /**
     * Từ chối yêu cầu đặt thuê: cập nhật trạng thái + ghi audit + notify khách.
     *
     * Không đụng tới Room/Contract/Tenant vì khi reject yêu cầu vẫn chưa có
     * các thực thể liên quan (chưa từng được approve).
     *
     * @param  BookingRequest  $br      Yêu cầu cần từ chối.
     * @param  User            $admin   Admin thực hiện thao tác.
     * @param  string          $reason  Lý do từ chối (đã được validate min:10 ở Request).
     */
    public function reject(BookingRequest $br, User $admin, string $reason): BookingRequest
    {
        return DB::transaction(function () use ($br, $admin, $reason) {
            $br->update([
                'status'                 => 'rejected',
                'rejected_reason'        => $reason,
                'rejected_at'            => now(),
                'last_status_changed_by' => $admin->id,
                'last_status_changed_at' => now(),
            ]);

            BookingRequestAudit::create([
                'booking_request_id' => $br->id,
                'event'              => 'rejected',
                'actor_user_id'      => $admin->id,
                'ip_address'         => request()->ip(),
                'user_agent'         => substr((string) request()->userAgent(), 0, 255),
                'metadata'           => ['reason' => $reason],
                'created_at'         => now(),
            ]);

            $br->user->notify(new BookingRequestRejected($br->fresh(['user', 'room'])));

            return $br->fresh();
        });
    }
}
