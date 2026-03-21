@props([
    'done'    => 0,    // number of completed steps
    'total'   => 10,   // total steps (dynamic from registry)
    'skipped' => 0,    // number of skipped steps
    'width'   => null, // optional fixed width e.g. '90px'
    'showText'=> false, // show "N/total" text above bar
])

@php
    $pct = $total > 0 ? round($done / $total * 100) : 0;
    $style = $width ? "width:{$width}" : '';
@endphp

<div>
    @if($showText)
    <div style="font-size:10px;color:#aaa;margin-bottom:3px;font-weight:600">
        {{ $done }}/{{ $total }}
        @if($skipped > 0)
            <span style="color:#c97700"> · {{ $skipped }}⏭</span>
        @endif
    </div>
    @endif

    <div class="prog-bar" style="{{ $style }}">
        <div class="prog-fill" style="width:{{ $pct }}%"></div>
    </div>
</div>
