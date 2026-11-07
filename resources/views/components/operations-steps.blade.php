@props(['steps', 'current' => 1])
<ol class="ops-steps d-print-none" aria-label="Tiến trình">
    @foreach($steps as $step)
    <li class="{{ $loop->iteration === (int) $current ? 'is-current' : '' }} {{ $loop->iteration < (int) $current ? 'is-complete' : '' }}" @if($loop->iteration === (int) $current) aria-current="step" @endif>
        <span class="ops-step-index" aria-hidden="true">@if($loop->iteration < (int) $current)<i class="fas fa-check"></i>@else{{ $loop->iteration }}@endif</span>
        <span>{{ $step }}</span>
    </li>
    @endforeach
</ol>
