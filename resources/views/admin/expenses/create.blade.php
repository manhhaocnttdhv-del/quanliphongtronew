<x-app-layout>
    <x-slot name="header">Ghi chi phí</x-slot>
    @include('admin.operations.errors')
    <form method="POST" action="{{ route('admin.expenses.store') }}" enctype="multipart/form-data" class="ops-form">
        @csrf<input type="hidden" name="submission_token" value="{{ $token }}">
        <div class="row g-3 mb-4">
            <div class="col-md-6"><label class="form-label" for="house_id">Chi nhánh</label><select id="house_id" name="house_id" class="form-select"><option value="">Chi phí chung</option>@foreach($houses as $house)<option value="{{ $house->id }}" @selected(old('house_id') == $house->id)>{{ $house->name }}</option>@endforeach</select></div>
            <div class="col-md-6"><label class="form-label" for="category">Danh mục</label><select id="category" name="category" class="form-select" required>@foreach(\App\Models\Expense::CATEGORIES as $key => $label)<option value="{{ $key }}" @selected(old('category') === $key)>{{ $label }}</option>@endforeach</select></div>
            <div class="col-md-6"><label class="form-label" for="amount">Số tiền (đ)</label><input id="amount" name="amount" type="number" min="1" max="999999999999" value="{{ old('amount') }}" class="form-control" required></div>
            <div class="col-md-6"><label class="form-label" for="spent_on">Ngày chi</label><input id="spent_on" name="spent_on" type="date" max="{{ today()->toDateString() }}" value="{{ old('spent_on', today()->toDateString()) }}" class="form-control" required></div>
            <div class="col-12"><label class="form-label" for="description">Nội dung chi</label><input id="description" name="description" maxlength="255" value="{{ old('description') }}" class="form-control" required></div>
            <div class="col-12"><label class="form-label" for="reference">Mã phiếu chi / Hóa đơn</label><input id="reference" name="reference" maxlength="150" value="{{ old('reference') }}" class="form-control"></div>
            <div class="col-12"><label class="form-label" for="evidence">Chứng từ (PNG, JPG, PDF; tối đa 5 MB)</label><input id="evidence" name="evidence" type="file" accept="image/png,image/jpeg,application/pdf" class="form-control"></div>
        </div>
        <div class="d-flex gap-2"><a href="{{ route('admin.expenses.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left" aria-hidden="true"></i> Quay lại</a><button class="btn btn-primary"><i class="fas fa-save me-1" aria-hidden="true"></i> Ghi sổ</button></div>
    </form>
</x-app-layout>
