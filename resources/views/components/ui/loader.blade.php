{{--
    <x-ui.loader />                       — inline spinner (md)
    <x-ui.loader size="sm" />            — small inline spinner
    <x-ui.loader type="page" />          — full-page overlay loader
    <x-ui.loader type="skeleton" lines="3" /> — skeleton text lines

    Props:
        type  — inline|page|skeleton   (default: inline)
        size  — sm|md|lg               (default: md)
        lines — number of skeleton lines (type=skeleton only)
        label — screen-reader label
--}}
@props([
    'type'  => 'inline',
    'size'  => 'md',
    'lines' => 3,
    'label' => 'Loading…',
])

@php
$spinnerSizes = ['sm' => 'w-4 h-4', 'md' => 'w-5 h-5', 'lg' => 'w-7 h-7'];
$spinCls = $spinnerSizes[$size] ?? $spinnerSizes['md'];
@endphp

@if($type === 'page')
{{-- Full-page overlay --}}
<div class="fixed inset-0 z-50 flex items-center justify-center"
     style="background:rgba(1,41,112,.08);backdrop-filter:blur(2px)"
     role="status" aria-label="{{ $label }}">
    <div class="flex flex-col items-center gap-3 p-8 rounded-2xl bg-white shadow-xl">
        <svg class="animate-spin w-10 h-10 text-[#4154f1]" fill="none" viewBox="0 0 24 24" aria-hidden="true">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
        </svg>
        <span class="text-sm font-semibold text-[#475569]">{{ $label }}</span>
    </div>
</div>

@elseif($type === 'skeleton')
{{-- Skeleton loading lines --}}
<div class="space-y-2 animate-pulse" role="status" aria-label="{{ $label }}">
    @for($i = 0; $i < (int)$lines; $i++)
    <div class="h-3 rounded-full {{ $i === (int)$lines - 1 ? 'w-2/3' : 'w-full' }}"
         style="background:#e2e8f0"></div>
    @endfor
    <span class="sr-only">{{ $label }}</span>
</div>

@else
{{-- Inline spinner --}}
<span class="inline-flex items-center gap-2" role="status" aria-label="{{ $label }}">
    <svg class="animate-spin {{ $spinCls }} text-[#4154f1]" fill="none" viewBox="0 0 24 24" aria-hidden="true">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
    </svg>
    @if($slot->isNotEmpty())
        <span class="text-sm text-[#64748b]">{{ $slot }}</span>
    @else
        <span class="sr-only">{{ $label }}</span>
    @endif
</span>
@endif
