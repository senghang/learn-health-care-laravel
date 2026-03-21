@props([
    'type'    => 'info',   // info | success | warning | danger
    'icon'    => null,     // override icon class; auto-selected if null
    'dismiss' => false,    // show dismiss button
])

@php
    $icons = [
        'info'    => 'bi-info-circle-fill',
        'success' => 'bi-check-circle-fill',
        'warning' => 'bi-exclamation-triangle-fill',
        'danger'  => 'bi-exclamation-triangle-fill',
    ];
    $classes = [
        'info'    => 'note-info',
        'success' => 'note-success',
        'warning' => 'note-warn',
        'danger'  => 'note-danger',
    ];
    $iconClass = $icon ?? ($icons[$type] ?? 'bi-info-circle-fill');
    $noteClass = $classes[$type] ?? 'note-info';
@endphp

<div class="note {{ $noteClass }} mb-3 {{ $attributes->get('class') }}"
     style="{{ $attributes->get('style') }}"
     @if($dismiss) x-data="{ show: true }" x-show="show" @endif>
    <i class="bi {{ $iconClass }}" style="flex-shrink:0;margin-top:1px"></i>
    <div style="flex:1">{{ $slot }}</div>
    @if($dismiss)
        <button @click="show=false"
                style="background:none;border:none;cursor:pointer;padding:0 4px;opacity:.6;line-height:1;flex-shrink:0">
            <i class="bi bi-x-lg" style="font-size:12px"></i>
        </button>
    @endif
</div>
