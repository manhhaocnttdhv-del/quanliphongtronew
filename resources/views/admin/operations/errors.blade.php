@if($errors->any())
<div id="ops-errors" class="alert alert-danger" role="alert" tabindex="-1"><strong>Kiểm tra lại thông tin</strong><ul class="mb-0 mt-2">@foreach($errors->all() as $message)<li>{{ $message }}</li>@endforeach</ul></div>
@endif
