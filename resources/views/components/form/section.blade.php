@props([
    'title',
    'km'    => null,
    'color' => '#4154f1',
])

<div class="sec-block mb-3"
     style="border-left-color:{{ $color }};background:{{ $color }}0d;border-radius:10px;padding:14px 16px;border-left:4px solid {{ $color }}">
    <div style="font-size:10.5px;font-weight:700;text-transform:uppercase;letter-spacing:.6px;color:{{ $color }};margin-bottom:12px">
        @if($km){{ $km }} / @endif{{ $title }}
    </div>
    {{ $slot }}
</div>
