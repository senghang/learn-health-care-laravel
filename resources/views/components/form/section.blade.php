@props([
    'title',
    'km' => null,
    'color' => '#4154f1'
])

<div class="sec-block" style="border-color:{{ $color }};background:{{ $color }}0d">
    <div style="
        font-size:10.5px;
        font-weight:800;
        text-transform:uppercase;
        letter-spacing:.6px;
        color:{{ $color }};
        margin-bottom:12px">
        {{ $km }} / {{ $title }}
    </div>

    {{ $slot }}
</div>
