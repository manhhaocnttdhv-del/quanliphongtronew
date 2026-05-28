<?php

namespace App\Exceptions;

use Exception;

/**
 * Exception ném ra khi cố gắng duyệt một BookingRequest cho phòng không còn ở
 * trạng thái `available` (đã được phòng khác đặt giữ chỗ, đã thuê, hoặc đang
 * bảo trì). Controller phía admin sẽ bắt và trả về thông báo lỗi cho người
 * thao tác — xem `BookingApprovalService::approve()` và design.md mục 4.1.
 */
class RoomNotAvailableException extends Exception
{
}
