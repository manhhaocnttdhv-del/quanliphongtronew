# Requirements Document

## Introduction

Tính năng **Đăng ký tài khoản và Đặt thuê phòng trọ Online** mở rộng hệ thống quản lý phòng trọ hiện tại (Laravel) để cho phép khách hàng tiềm năng tự đăng ký tài khoản, duyệt phòng trống, gửi yêu cầu đặt thuê, ký xác nhận điện tử và đặt cọc trực tuyến qua PayOS.

Hiện tại hệ thống chỉ cho phép Admin tạo Tenant và Contract thủ công. Tính năng này thêm luồng tự phục vụ (self-service) cho người dùng cuối, đồng thời thêm luồng kiểm duyệt (approval) cho Admin để biến yêu cầu đặt phòng (Booking Request) thành Hợp đồng (Contract) chính thức.

Phạm vi bao gồm:
- Đăng ký tài khoản với role mặc định `customer`.
- Trang danh sách phòng trống và trang chi tiết phòng cho phép gửi yêu cầu đặt thuê.
- Form yêu cầu đặt thuê với thông tin cá nhân (CCCD, SĐT, ngày dự kiến chuyển vào, số người ở).
- Quy trình Admin duyệt/từ chối yêu cầu.
- Khi duyệt: tạo Tenant, tạo Contract draft, nâng role User từ `customer` lên `tenant`, đổi trạng thái Room sang `reserved`.
- Upload giấy tờ (CCCD mặt trước/sau) và ký xác nhận điện tử (e-sign).
- Đặt cọc online qua PayOS.
- Thông báo trạng thái cho cả khách hàng và Admin xuyên suốt luồng.

## Glossary

- **System**: Hệ thống quản lý phòng trọ Online (toàn bộ ứng dụng Laravel).
- **Registration_Module**: Mô-đun đăng ký tài khoản (mở rộng `RegisteredUserController`).
- **Booking_Module**: Mô-đun xử lý yêu cầu đặt thuê phòng online.
- **Approval_Module**: Mô-đun duyệt yêu cầu đặt thuê dành cho Admin.
- **Document_Module**: Mô-đun upload và lưu trữ giấy tờ tùy thân và hợp đồng điện tử.
- **E_Sign_Module**: Mô-đun ký xác nhận điện tử (đánh dấu đồng ý điều khoản và lưu chữ ký số đơn giản).
- **Deposit_Payment_Module**: Mô-đun xử lý đặt cọc trực tuyến qua tích hợp PayOS hiện có.
- **Notification_Module**: Mô-đun gửi thông báo (database notifications) cho khách hàng và Admin.
- **Customer**: Người dùng đã đăng ký tài khoản nhưng chưa có hợp đồng thuê (role `customer`).
- **Tenant**: Khách thuê đã có ít nhất một Contract (role `tenant`, gắn với bản ghi `tenants`).
- **Admin**: Quản trị viên hệ thống (role `admin`).
- **Booking_Request**: Bản ghi yêu cầu đặt thuê do Customer gửi, gồm các trạng thái: `pending`, `approved`, `rejected`, `cancelled`, `expired`.
- **Booking_Status**: Trường trạng thái của Booking_Request.
- **Room**: Phòng trọ trong hệ thống, có trạng thái `available`, `reserved`, `rented`, `maintenance`.
- **Reserved_Room**: Phòng đã được giữ chỗ cho một Booking_Request đã duyệt nhưng chưa hoàn tất đặt cọc.
- **Contract_Draft**: Hợp đồng ở trạng thái `draft`, được tạo khi Admin duyệt Booking_Request, chuyển sang `active` sau khi đặt cọc thành công.
- **Deposit_Amount**: Số tiền đặt cọc do Admin cấu hình khi duyệt (mặc định bằng giá thuê 1 tháng).
- **PayOS**: Cổng thanh toán hiện đã tích hợp trong `PaymentController` để xử lý giao dịch online.
- **CCCD**: Căn cước công dân (số CMND/CCCD).
- **Reservation_Hold_Period**: Khoảng thời gian giữ phòng sau khi duyệt nhưng chưa đặt cọc, mặc định 48 giờ.

## Requirements

### Requirement 1: Đăng ký tài khoản với role mặc định Customer

**User Story:** Là một khách hàng tiềm năng, tôi muốn tự đăng ký tài khoản trên website, để có thể duyệt phòng và gửi yêu cầu đặt thuê online.

#### Acceptance Criteria

1. WHEN khách hàng truy cập trang `/register`, THE Registration_Module SHALL hiển thị form đăng ký với các trường: họ tên, email, số điện thoại, mật khẩu, xác nhận mật khẩu.
2. WHEN khách hàng gửi form đăng ký với dữ liệu hợp lệ, THE Registration_Module SHALL tạo một bản ghi User mới với role mặc định là `customer`.
3. IF email gửi đăng ký đã tồn tại trong bảng `users`, THEN THE Registration_Module SHALL trả về lỗi xác thực với thông báo "Email đã được sử dụng".
4. IF số điện thoại gửi đăng ký không khớp định dạng số điện thoại Việt Nam (10 chữ số bắt đầu bằng `0`), THEN THE Registration_Module SHALL trả về lỗi xác thực với thông báo "Số điện thoại không hợp lệ".
5. IF mật khẩu gửi đăng ký có độ dài nhỏ hơn 8 ký tự, THEN THE Registration_Module SHALL trả về lỗi xác thực với thông báo "Mật khẩu phải có ít nhất 8 ký tự".
6. WHEN bản ghi User được tạo thành công, THE Registration_Module SHALL kích hoạt sự kiện `Registered` để gửi email xác minh tài khoản.
7. WHEN bản ghi User được tạo thành công, THE Registration_Module SHALL đăng nhập tự động cho User và chuyển hướng đến trang `/booking/my-requests` (trang quản lý yêu cầu đặt thuê của khách hàng).
8. THE Registration_Module SHALL lưu trữ mật khẩu dưới dạng băm (hashed) bằng Bcrypt.

### Requirement 2: Phân luồng người dùng theo Role sau đăng nhập

**User Story:** Là một người dùng, tôi muốn được đưa đến giao diện phù hợp với vai trò của mình sau khi đăng nhập, để truy cập đúng chức năng.

#### Acceptance Criteria

1. WHEN một User có role `admin` đăng nhập thành công, THE System SHALL chuyển hướng đến trang `/admin/dashboard`.
2. WHEN một User có role `tenant` đăng nhập thành công, THE System SHALL chuyển hướng đến trang `/tenant/dashboard`.
3. WHEN một User có role `customer` đăng nhập thành công, THE System SHALL chuyển hướng đến trang `/booking/my-requests`.
4. WHERE một User có nhiều role, THE System SHALL ưu tiên theo thứ tự: `admin` > `tenant` > `customer` khi quyết định trang chuyển hướng.
5. IF một User có role `customer` cố truy cập tuyến đường có prefix `/admin`, THEN THE System SHALL trả về HTTP 403 Forbidden.
6. IF một User có role `customer` cố truy cập tuyến đường có prefix `/tenant`, THEN THE System SHALL trả về HTTP 403 Forbidden.

### Requirement 3: Duyệt danh sách phòng trống dành cho Customer

**User Story:** Là một Customer đã đăng nhập, tôi muốn xem danh sách các phòng còn trống, để chọn phòng phù hợp với nhu cầu của mình.

#### Acceptance Criteria

1. WHEN một Customer truy cập trang chủ `/`, THE Booking_Module SHALL hiển thị danh sách Room có `status = 'available'` kèm thông tin: tên phòng, khu trọ, loại phòng, giá thuê, diện tích, số người tối đa, ảnh đại diện.
2. WHEN một Customer truy cập trang chi tiết phòng `/phong/{id}`, THE Booking_Module SHALL hiển thị thông tin chi tiết của Room kèm danh sách dịch vụ đi kèm và nút "Yêu cầu đặt thuê".
3. WHILE Room có `status` khác `available`, THE Booking_Module SHALL ẩn nút "Yêu cầu đặt thuê" trên trang chi tiết phòng và hiển thị nhãn trạng thái tương ứng (Đã thuê / Đã giữ chỗ / Đang sửa).
4. WHEN một khách chưa đăng nhập bấm nút "Yêu cầu đặt thuê", THE Booking_Module SHALL chuyển hướng đến trang `/login` kèm tham số `redirect` về trang chi tiết phòng.
5. WHERE Customer đã đăng nhập, THE Booking_Module SHALL hiển thị nút "Yêu cầu đặt thuê" liên kết đến form `/booking/create?room_id={id}`.

### Requirement 4: Gửi yêu cầu đặt thuê phòng online

**User Story:** Là một Customer, tôi muốn gửi yêu cầu đặt thuê một phòng cụ thể với thông tin cá nhân và mong muốn của tôi, để Admin có thể xem xét và duyệt.

#### Acceptance Criteria

1. WHEN một Customer truy cập trang `/booking/create?room_id={id}` cho một Room có `status = 'available'`, THE Booking_Module SHALL hiển thị form yêu cầu đặt thuê với các trường: số CCCD, số điện thoại, ngày sinh, giới tính, địa chỉ thường trú, quê quán, ngày dự kiến chuyển vào, số người ở dự kiến, thời hạn thuê dự kiến (số tháng), ghi chú.
2. WHEN Customer gửi form yêu cầu đặt thuê hợp lệ, THE Booking_Module SHALL tạo một bản ghi Booking_Request mới với `booking_status = 'pending'`, gắn với `user_id` của Customer và `room_id` đã chọn.
3. IF Room đã chọn không còn `status = 'available'` tại thời điểm submit, THEN THE Booking_Module SHALL từ chối yêu cầu, không tạo Booking_Request, và hiển thị thông báo "Phòng này đã được đặt hoặc không còn trống".
4. IF Customer đã có Booking_Request với `booking_status = 'pending'` cho cùng `room_id`, THEN THE Booking_Module SHALL từ chối tạo yêu cầu mới và hiển thị thông báo "Bạn đã có yêu cầu đang chờ duyệt cho phòng này".
5. IF số CCCD đã tồn tại trong bảng `tenants` và liên kết với một `user_id` khác, THEN THE Booking_Module SHALL từ chối yêu cầu và hiển thị thông báo "Số CCCD này đã được sử dụng bởi tài khoản khác".
6. IF số CCCD không khớp định dạng (12 chữ số), THEN THE Booking_Module SHALL trả về lỗi xác thực với thông báo "Số CCCD phải gồm 12 chữ số".
7. IF ngày dự kiến chuyển vào nhỏ hơn ngày hiện tại, THEN THE Booking_Module SHALL trả về lỗi xác thực với thông báo "Ngày chuyển vào phải từ hôm nay trở đi".
8. IF số người ở dự kiến lớn hơn `max_occupants` của Room, THEN THE Booking_Module SHALL trả về lỗi xác thực với thông báo "Số người ở vượt quá sức chứa của phòng".
9. IF thời hạn thuê dự kiến nhỏ hơn 1 tháng hoặc lớn hơn 36 tháng, THEN THE Booking_Module SHALL trả về lỗi xác thực với thông báo "Thời hạn thuê phải từ 1 đến 36 tháng".
10. WHEN một Booking_Request được tạo thành công, THE Notification_Module SHALL tạo thông báo dạng database cho tất cả User có role `admin` với nội dung "Có yêu cầu đặt phòng mới từ {tên Customer} cho phòng {tên Room}".
11. WHEN một Booking_Request được tạo thành công, THE Booking_Module SHALL chuyển hướng Customer đến trang `/booking/my-requests` với thông báo flash "Yêu cầu đặt thuê của bạn đã được gửi và đang chờ duyệt".

### Requirement 5: Quản lý yêu cầu đặt thuê dành cho Customer

**User Story:** Là một Customer, tôi muốn xem và quản lý các yêu cầu đặt thuê đã gửi, để theo dõi trạng thái và hủy yêu cầu khi cần.

#### Acceptance Criteria

1. WHEN một Customer truy cập trang `/booking/my-requests`, THE Booking_Module SHALL hiển thị danh sách Booking_Request thuộc về Customer đó, kèm các cột: tên phòng, ngày gửi yêu cầu, trạng thái, ngày dự kiến chuyển vào.
2. WHEN một Customer truy cập trang `/booking/my-requests/{id}`, THE Booking_Module SHALL hiển thị chi tiết Booking_Request bao gồm thông tin phòng, thông tin cá nhân đã khai, ghi chú từ Admin (nếu có), và các hành động phù hợp với trạng thái hiện tại.
3. WHILE Booking_Request có `booking_status = 'pending'`, THE Booking_Module SHALL hiển thị nút "Hủy yêu cầu" trên trang chi tiết.
4. WHEN một Customer bấm "Hủy yêu cầu" cho một Booking_Request có `booking_status = 'pending'`, THE Booking_Module SHALL cập nhật `booking_status = 'cancelled'` và ghi nhận `cancelled_at`.
5. IF một Customer cố truy cập Booking_Request không thuộc về mình, THEN THE Booking_Module SHALL trả về HTTP 403 Forbidden.
6. WHILE Booking_Request có `booking_status = 'approved'`, THE Booking_Module SHALL hiển thị nút "Hoàn tất hồ sơ" liên kết đến luồng upload giấy tờ và đặt cọc.

### Requirement 6: Duyệt yêu cầu đặt thuê dành cho Admin

**User Story:** Là một Admin, tôi muốn xem danh sách yêu cầu đặt thuê và duyệt hoặc từ chối, để biến yêu cầu thành hợp đồng chính thức.

#### Acceptance Criteria

1. WHEN một Admin truy cập trang `/admin/booking-requests`, THE Approval_Module SHALL hiển thị danh sách Booking_Request có thể lọc theo `booking_status`, sắp xếp mặc định theo ngày tạo giảm dần.
2. WHEN một Admin truy cập trang `/admin/booking-requests/{id}`, THE Approval_Module SHALL hiển thị chi tiết Booking_Request, thông tin Customer, thông tin Room và các nút hành động: "Duyệt", "Từ chối".
3. WHEN một Admin bấm "Duyệt" cho một Booking_Request có `booking_status = 'pending'`, THE Approval_Module SHALL hiển thị form xác nhận yêu cầu nhập: số tiền đặt cọc, ngày bắt đầu hợp đồng, ngày kết thúc hợp đồng, ghi chú.
4. WHEN một Admin xác nhận duyệt với dữ liệu hợp lệ, THE Approval_Module SHALL thực hiện các bước trong cùng một transaction: (a) tạo bản ghi Tenant gắn `user_id` của Customer, (b) tạo Contract_Draft với `status = 'draft'`, (c) nâng role User từ `customer` lên `tenant`, (d) cập nhật Room sang `status = 'reserved'`, (e) cập nhật Booking_Request sang `booking_status = 'approved'`.
5. IF bất kỳ bước nào trong quy trình duyệt thất bại, THEN THE Approval_Module SHALL rollback toàn bộ transaction và giữ nguyên trạng thái cũ của Booking_Request, Room và User.
6. IF Room đã chuyển sang `status` khác `available` tại thời điểm Admin bấm duyệt, THEN THE Approval_Module SHALL từ chối thao tác và hiển thị thông báo "Phòng này không còn trống để duyệt".
7. WHEN một Admin bấm "Từ chối" cho một Booking_Request, THE Approval_Module SHALL yêu cầu nhập lý do từ chối và cập nhật `booking_status = 'rejected'`, `rejected_reason = {lý do}`, `rejected_at = {thời điểm hiện tại}`.
8. IF lý do từ chối có độ dài nhỏ hơn 10 ký tự, THEN THE Approval_Module SHALL trả về lỗi xác thực với thông báo "Lý do từ chối phải có ít nhất 10 ký tự".
9. WHEN trạng thái Booking_Request thay đổi sang `approved` hoặc `rejected`, THE Notification_Module SHALL tạo thông báo dạng database cho User chủ yêu cầu với nội dung tương ứng.
10. WHILE Booking_Request có `booking_status` khác `pending`, THE Approval_Module SHALL ẩn các nút "Duyệt" và "Từ chối" trên trang chi tiết.

### Requirement 7: Hết hạn giữ phòng tự động

**User Story:** Là một Admin, tôi muốn các phòng đã giữ chỗ nhưng không được khách hàng hoàn tất đặt cọc đúng hạn được giải phóng tự động, để tránh chiếm dụng phòng trống.

#### Acceptance Criteria

1. THE System SHALL chạy một lệnh đã lên lịch (scheduled command) mỗi giờ một lần để rà soát Booking_Request có `booking_status = 'approved'` chưa hoàn tất đặt cọc.
2. WHEN một Booking_Request có `booking_status = 'approved'` đã quá Reservation_Hold_Period (48 giờ kể từ `approved_at`) và chưa có Payment thành công cho khoản đặt cọc, THE System SHALL chuyển `booking_status` sang `expired`.
3. WHEN một Booking_Request chuyển sang `booking_status = 'expired'`, THE System SHALL chuyển Contract_Draft liên quan sang `status = 'cancelled'` và Room liên quan trở về `status = 'available'`.
4. WHEN một Booking_Request chuyển sang `booking_status = 'expired'`, THE Notification_Module SHALL tạo thông báo dạng database cho User chủ yêu cầu với nội dung "Yêu cầu đặt phòng {tên Room} đã hết hạn giữ chỗ do chưa hoàn tất đặt cọc".

### Requirement 8: Upload giấy tờ tùy thân

**User Story:** Là một Customer có yêu cầu đã được duyệt, tôi muốn upload ảnh CCCD mặt trước và mặt sau, để hoàn tất hồ sơ thuê phòng.

#### Acceptance Criteria

1. WHEN một Customer truy cập trang `/booking/my-requests/{id}/documents` cho một Booking_Request có `booking_status = 'approved'`, THE Document_Module SHALL hiển thị form upload ảnh CCCD mặt trước và mặt sau.
2. WHEN Customer upload file ảnh hợp lệ, THE Document_Module SHALL lưu file vào ổ đĩa `public` dưới thư mục `tenants/cccd/{tenant_id}` và cập nhật trường `cccd_front_path`, `cccd_back_path` của bản ghi Tenant tương ứng.
3. IF file upload có định dạng khác `jpg`, `jpeg`, `png`, `webp`, THEN THE Document_Module SHALL trả về lỗi xác thực với thông báo "Chỉ chấp nhận ảnh định dạng JPG, PNG hoặc WEBP".
4. IF file upload có dung lượng lớn hơn 5 MB, THEN THE Document_Module SHALL trả về lỗi xác thực với thông báo "Dung lượng ảnh tối đa là 5 MB".
5. THE Document_Module SHALL yêu cầu cả hai ảnh CCCD mặt trước và mặt sau đều được upload trước khi cho phép sang bước ký xác nhận điện tử.

### Requirement 9: Ký xác nhận điện tử (E-Sign)

**User Story:** Là một Customer, tôi muốn ký xác nhận điện tử trên hợp đồng dự thảo, để xác nhận đồng ý các điều khoản trước khi đặt cọc.

#### Acceptance Criteria

1. WHEN một Customer truy cập trang `/booking/my-requests/{id}/sign` cho một Booking_Request có `booking_status = 'approved'` đã upload đủ giấy tờ, THE E_Sign_Module SHALL hiển thị nội dung Contract_Draft và canvas ký tay (signature pad).
2. THE E_Sign_Module SHALL chỉ cho phép submit khi Customer đã tích chọn ô "Tôi đồng ý với các điều khoản" và đã vẽ chữ ký trên canvas.
3. WHEN một Customer submit ký xác nhận, THE E_Sign_Module SHALL lưu ảnh chữ ký dưới dạng PNG vào ổ đĩa `public` dưới thư mục `contracts/signatures/{contract_id}` và lưu đường dẫn vào trường `signature_path` của Contract.
4. WHEN một Customer submit ký xác nhận, THE E_Sign_Module SHALL ghi nhận `signed_at`, `signed_ip` (IP request) và `signed_user_agent` vào Contract.
5. WHEN ký xác nhận thành công, THE E_Sign_Module SHALL chuyển hướng Customer đến trang đặt cọc `/booking/my-requests/{id}/deposit`.
6. IF Customer cố truy cập trang ký xác nhận trước khi upload đủ giấy tờ CCCD, THEN THE E_Sign_Module SHALL chuyển hướng về trang upload giấy tờ kèm thông báo "Vui lòng hoàn tất upload giấy tờ trước khi ký".

### Requirement 10: Đặt cọc trực tuyến qua PayOS

**User Story:** Là một Customer đã ký xác nhận, tôi muốn đặt cọc online qua PayOS, để hoàn tất việc đặt phòng và kích hoạt hợp đồng.

#### Acceptance Criteria

1. WHEN một Customer truy cập trang `/booking/my-requests/{id}/deposit` cho một Booking_Request có `booking_status = 'approved'` đã ký xác nhận, THE Deposit_Payment_Module SHALL hiển thị tóm tắt: tên phòng, số tiền cọc, nút "Thanh toán qua PayOS".
2. WHEN một Customer bấm "Thanh toán qua PayOS", THE Deposit_Payment_Module SHALL tạo Invoice loại `deposit` gắn với Contract_Draft, sau đó tạo PayOS payment link sử dụng `PaymentController` hiện có.
3. WHEN PayOS xác nhận thanh toán thành công (qua webhook hoặc return URL), THE Deposit_Payment_Module SHALL cập nhật Invoice sang `status = 'paid'`, tạo Payment record với `method = 'transfer'` và `reference_code = orderCode`.
4. WHEN khoản cọc được ghi nhận thanh toán đầy đủ, THE Deposit_Payment_Module SHALL chuyển Contract sang `status = 'active'` và Room sang `status = 'rented'`.
5. WHEN khoản cọc được ghi nhận thanh toán đầy đủ, THE Notification_Module SHALL tạo thông báo dạng database cho User chủ Booking_Request và toàn bộ User role `admin` với nội dung "Khách hàng {tên} đã hoàn tất đặt cọc cho phòng {tên Room}".
6. IF Customer hủy giao dịch trên cổng PayOS, THEN THE Deposit_Payment_Module SHALL giữ nguyên Booking_Request ở `booking_status = 'approved'` và cho phép Customer thử lại.
7. IF số tiền PayOS xác nhận thanh toán nhỏ hơn `Deposit_Amount`, THEN THE Deposit_Payment_Module SHALL ghi nhận Payment một phần, giữ Invoice ở `status = 'partial'` và không kích hoạt Contract.

### Requirement 11: Quản lý phiên giữ phòng và đồng bộ trạng thái Room

**User Story:** Là một Admin, tôi muốn trạng thái Room luôn đồng bộ với các Booking_Request và Contract liên quan, để tránh tình trạng một phòng được nhiều khách đặt cùng lúc.

#### Acceptance Criteria

1. WHILE một Room có ít nhất một Contract với `status = 'active'`, THE System SHALL giữ Room ở `status = 'rented'`.
2. WHILE một Room có ít nhất một Booking_Request với `booking_status = 'approved'` và Contract liên quan có `status = 'draft'`, THE System SHALL giữ Room ở `status = 'reserved'`.
3. WHEN tất cả Contract của một Room chuyển sang `status` khác `active` và `draft`, THE System SHALL chuyển Room sang `status = 'available'` (trừ khi Room đang ở `status = 'maintenance'` do Admin đặt thủ công).
4. WHILE một Room có `status = 'reserved'` hoặc `status = 'rented'` hoặc `status = 'maintenance'`, THE Booking_Module SHALL từ chối tạo Booking_Request mới cho Room đó.
5. THE System SHALL áp dụng khóa bi quan (pessimistic lock) trên bản ghi Room khi xử lý duyệt Booking_Request, để ngăn hai Admin duyệt đồng thời cho cùng một Room.

### Requirement 12: Thông báo cho Customer và Admin

**User Story:** Là một người dùng (Customer hoặc Admin), tôi muốn nhận thông báo về các thay đổi trạng thái yêu cầu đặt thuê, để cập nhật kịp thời.

#### Acceptance Criteria

1. WHEN một Booking_Request được tạo, THE Notification_Module SHALL gửi thông báo database cho tất cả User role `admin`.
2. WHEN một Booking_Request chuyển sang `approved` hoặc `rejected`, THE Notification_Module SHALL gửi thông báo database cho User chủ yêu cầu.
3. WHEN một Booking_Request chuyển sang `expired`, THE Notification_Module SHALL gửi thông báo database cho User chủ yêu cầu.
4. WHEN khoản đặt cọc được ghi nhận thanh toán đầy đủ, THE Notification_Module SHALL gửi thông báo database cho cả User chủ yêu cầu và toàn bộ User role `admin`.
5. WHERE người dùng đã bật tùy chọn nhận email, THE Notification_Module SHALL đồng thời gửi email với cùng nội dung qua kênh `mail`.
6. THE Notification_Module SHALL hiển thị số thông báo chưa đọc trên thanh điều hướng cho mọi User đã đăng nhập.

### Requirement 13: Phân quyền và bảo mật

**User Story:** Là một chủ hệ thống, tôi muốn các tuyến đường và hành động liên quan đến đặt phòng được phân quyền chặt chẽ, để bảo vệ dữ liệu khách hàng.

#### Acceptance Criteria

1. THE System SHALL yêu cầu xác thực (middleware `auth`) cho mọi tuyến đường có prefix `/booking`.
2. THE System SHALL yêu cầu role `admin` (middleware `role:admin`) cho mọi tuyến đường có prefix `/admin/booking-requests`.
3. IF một User cố truy cập trang chi tiết Booking_Request không thuộc về mình và không có role `admin`, THEN THE System SHALL trả về HTTP 403 Forbidden.
4. THE System SHALL bảo vệ mọi form POST của Booking_Module, Approval_Module, Document_Module, E_Sign_Module, Deposit_Payment_Module bằng CSRF token.
5. THE System SHALL ghi log audit cho các sự kiện: tạo Booking_Request, duyệt, từ chối, hủy, hoàn tất đặt cọc, kèm `user_id`, `booking_request_id`, IP và thời điểm.
6. THE System SHALL xác thực chữ ký webhook PayOS bằng `verifyPaymentWebhookData` trước khi cập nhật trạng thái Invoice.

### Requirement 14: Theo dõi vòng đời Booking_Request

**User Story:** Là một Admin, tôi muốn xem lịch sử thay đổi trạng thái của một Booking_Request, để truy vết các quyết định đã thực hiện.

#### Acceptance Criteria

1. THE System SHALL ghi nhận các trường thời điểm trên Booking_Request: `created_at`, `approved_at`, `rejected_at`, `cancelled_at`, `expired_at`, `signed_at`, `deposit_paid_at`.
2. WHEN trạng thái Booking_Request thay đổi, THE System SHALL ghi nhận `last_status_changed_by` (user_id của người thực hiện) và `last_status_changed_at`.
3. WHEN một Admin truy cập trang chi tiết Booking_Request, THE Approval_Module SHALL hiển thị timeline các sự kiện trạng thái theo thứ tự thời gian.
4. THE System SHALL không cho phép xóa cứng (hard delete) Booking_Request đã ở trạng thái `approved`, `expired`, hoặc đã liên kết với Contract; thay vào đó chỉ cho phép cập nhật trạng thái.
