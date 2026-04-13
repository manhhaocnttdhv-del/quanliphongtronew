<x-app-layout>
    <x-slot name="header">Cập Nhật Khách Thuê: {{ $tenant->user->name }}</x-slot>

    <div class="row">
        <div class="col-lg-10 mx-auto">
            <div class="card card-round">
                <div class="card-header">
                    <div class="card-head-row">
                        <div class="card-title">Thông tin Khách Thuê</div>
                        <a href="{{ route('admin.tenants.index') }}" class="btn btn-sm btn-secondary btn-round">
                            <i class="fas fa-arrow-left me-1"></i> Quay lại
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show">
                        <strong><i class="fas fa-exclamation-triangle me-1"></i>Có lỗi:</strong>
                        <ul class="mb-0 mt-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    @endif

                    <form action="{{ route('admin.tenants.update', $tenant) }}" method="POST">
                        @csrf @method('PUT')

                        <h6 class="fw-bold text-primary mb-3 border-bottom pb-2">
                            <i class="fas fa-lock me-1"></i> Thông tin Đăng nhập
                        </h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Họ và tên <span class="text-danger">*</span></label>
                                    <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror"
                                           value="{{ old('name', $tenant->user->name) }}" required autofocus>
                                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email">Email Đăng nhập <span class="text-danger">*</span></label>
                                    <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                           value="{{ old('email', $tenant->user->email) }}" required>
                                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>

                        <h6 class="fw-bold text-primary mb-3 border-bottom pb-2 mt-3">
                            <i class="fas fa-id-card me-1"></i> Thông tin Cá nhân & Liên hệ
                        </h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="phone">Số điện thoại</label>
                                    <input type="text" id="phone" name="phone" class="form-control @error('phone') is-invalid @enderror"
                                           value="{{ old('phone', $tenant->phone) }}">
                                    @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="cccd">Số CCCD / CMND</label>
                                    <input type="text" id="cccd" name="cccd" class="form-control @error('cccd') is-invalid @enderror"
                                           value="{{ old('cccd', $tenant->cccd) }}">
                                    @error('cccd')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="gender">Giới tính</label>
                                    <select id="gender" name="gender" class="form-select @error('gender') is-invalid @enderror">
                                        <option value="male"   {{ old('gender',$tenant->gender)=='male'   ?'selected':'' }}>Nam</option>
                                        <option value="female" {{ old('gender',$tenant->gender)=='female' ?'selected':'' }}>Nữ</option>
                                        <option value="other"  {{ old('gender',$tenant->gender)=='other'  ?'selected':'' }}>Khác</option>
                                    </select>
                                    @error('gender')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="birthday">Ngày sinh</label>
                                    <input type="date" id="birthday" name="birthday" class="form-control @error('birthday') is-invalid @enderror"
                                           value="{{ old('birthday', $tenant->birthday) }}">
                                    @error('birthday')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="hometown">Quê quán</label>
                                    <input type="text" id="hometown" name="hometown" class="form-control @error('hometown') is-invalid @enderror"
                                           value="{{ old('hometown', $tenant->hometown) }}">
                                    @error('hometown')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="address">Địa chỉ thường trú</label>
                                    <textarea id="address" name="address" rows="2" class="form-control @error('address') is-invalid @enderror">{{ old('address', $tenant->address) }}</textarea>
                                    @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>

                        <div class="card-action d-flex justify-content-end gap-2 mt-3">
                            <a href="{{ route('admin.tenants.index') }}" class="btn btn-secondary btn-round">Hủy</a>
                            <button type="submit" class="btn btn-primary btn-round">
                                <i class="fas fa-save me-1"></i> Lưu Thay Đổi
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
