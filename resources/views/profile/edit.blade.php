<x-app-layout>
    <x-slot name="header">Hồ sơ cá nhân</x-slot>

    <div class="row g-4">
        {{-- THÔNG TIN CƠ BẢN --}}
        <div class="col-lg-6">
            <div class="card card-round h-100">
                <div class="card-header">
                    <div class="card-title">
                        <i class="fas fa-user-edit text-primary me-2"></i>Thông tin hồ sơ
                    </div>
                    <div class="card-category">Cập nhật tên tài khoản và địa chỉ email của bạn.</div>
                </div>
                <div class="card-body">
                    <form method="post" action="{{ route('profile.update') }}">
                        @csrf
                        @method('patch')

                        <div class="form-group px-0">
                            <label class="form-label fw-semibold">Họ và tên <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name">
                            <x-input-error class="text-danger small mt-1" :messages="$errors->get('name')" />
                        </div>

                        <div class="form-group px-0">
                            <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required autocomplete="username">
                            <x-input-error class="text-danger small mt-1" :messages="$errors->get('email')" />

                            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                                <div class="mt-2 text-warning small">
                                    {{ __('Địa chỉ email của bạn chưa được xác minh.') }}
                                    <button form="send-verification" class="btn btn-link btn-sm p-0 m-0 align-baseline">
                                        {{ __('Bấm vào đây để gửi lại email xác minh.') }}
                                    </button>
                                </div>
                                @if (session('status') === 'verification-link-sent')
                                    <div class="mt-2 text-success small">
                                        {{ __('Một liên kết xác minh mới đã được gửi đến địa chỉ email của bạn.') }}
                                    </div>
                                @endif
                            @endif
                        </div>

                        <div class="mt-4 pt-2 border-top d-flex align-items-center gap-3">
                            <button type="submit" class="btn btn-primary btn-round">
                                <i class="fas fa-save me-1"></i>Lưu thông tin
                            </button>
                            @if (session('status') === 'profile-updated')
                                <span class="text-success small fw-semibold transition show" id="profile-status">
                                    <i class="fas fa-check-circle me-1"></i>Đã lưu thành công.
                                </span>
                                <script>setTimeout(() => document.getElementById('profile-status').style.display='none', 3000)</script>
                            @endif
                        </div>
                    </form>

                    <form id="send-verification" method="post" action="{{ route('verification.send') }}" class="d-none">
                        @csrf
                    </form>
                </div>
            </div>
        </div>

        {{-- ĐỔI MẬT KHẨU --}}
        <div class="col-lg-6">
            <div class="card card-round h-100">
                <div class="card-header">
                    <div class="card-title">
                        <i class="fas fa-lock text-warning me-2"></i>Đổi Mật Khẩu
                    </div>
                    <div class="card-category">Đảm bảo tài khoản của bạn đang sử dụng mật khẩu dài, ngẫu nhiên để an toàn.</div>
                </div>
                <div class="card-body">
                    <form method="post" action="{{ route('password.update') }}">
                        @csrf
                        @method('put')

                        <div class="form-group px-0">
                            <label class="form-label fw-semibold">Mật khẩu hiện tại</label>
                            <input type="password" name="current_password" class="form-control" autocomplete="current-password">
                            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="text-danger small mt-1" />
                        </div>

                        <div class="form-group px-0">
                            <label class="form-label fw-semibold">Mật khẩu mới</label>
                            <input type="password" name="password" class="form-control" autocomplete="new-password">
                            <x-input-error :messages="$errors->updatePassword->get('password')" class="text-danger small mt-1" />
                        </div>

                        <div class="form-group px-0">
                            <label class="form-label fw-semibold">Xác nhận mật khẩu mới</label>
                            <input type="password" name="password_confirmation" class="form-control" autocomplete="new-password">
                            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="text-danger small mt-1" />
                        </div>

                        <div class="mt-4 pt-2 border-top d-flex align-items-center gap-3">
                            <button type="submit" class="btn btn-warning btn-round text-white">
                                <i class="fas fa-key me-1"></i>Đổi mật khẩu
                            </button>
                            @if (session('status') === 'password-updated')
                                <span class="text-success small fw-semibold transition show" id="pwd-status">
                                    <i class="fas fa-check-circle me-1"></i>Đã cập nhật mật khẩu.
                                </span>
                                <script>setTimeout(() => document.getElementById('pwd-status').style.display='none', 3000)</script>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- XÓA TÀI KHOẢN --}}
        <div class="col-lg-12">
            <div class="card card-round" style="border: 1px solid #ffadad;">
                <div class="card-header bg-danger-subtle">
                    <div class="card-title text-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>Xóa tài khoản
                    </div>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3 text-sm">
                        Khi tài khoản bị xóa, tất cả tài nguyên và dữ liệu sẽ bị xóa vĩnh viễn và không thể khôi phục.
                    </p>
                    
                    <button type="button" class="btn btn-danger btn-round" data-bs-toggle="modal" data-bs-target="#deleteAccountModal">
                        Xóa tài khoản của tôi
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL XÓA TÀI KHOẢN --}}
    <div class="modal fade" id="deleteAccountModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="post" action="{{ route('profile.destroy') }}">
                    @csrf
                    @method('delete')
                    
                    <div class="modal-header">
                        <h5 class="modal-title text-danger fw-bold"><i class="fas fa-exclamation-circle me-2"></i>Bạn có chắc chắn?</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-3">Vui lòng nhập mật khẩu để xác nhận bạn muốn xóa vĩnh viễn tài khoản của mình. <strong>Thao tác này KHÔNG thể hoàn tác!</strong></p>
                        <div class="form-group px-0">
                            <label class="form-label fw-semibold">Mật khẩu của bạn</label>
                            <input type="password" name="password" class="form-control" placeholder="Mật khẩu..." required autofocus>
                            <x-input-error :messages="$errors->userDeletion->get('password')" class="text-danger mt-1 small" />
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-light btn-round" data-bs-dismiss="modal">Hủy bỏ</button>
                        <button type="submit" class="btn btn-danger btn-round">Xác nhận xóa tài khoản</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @if($errors->userDeletion->isNotEmpty())
        <script>
            // Nếu có lỗi xóa (sai pass), tự động bật lại Modal
            document.addEventListener('DOMContentLoaded', function() {
                var delModal = new bootstrap.Modal(document.getElementById('deleteAccountModal'));
                delModal.show();
            });
        </script>
    @endif
</x-app-layout>
