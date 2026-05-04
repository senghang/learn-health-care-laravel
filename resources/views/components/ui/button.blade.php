{{--
    Button component — all interactive trigger states.

    Usage:
        <x-ui.button variant="primary">Save</x-ui.button>
        <x-ui.button variant="danger" size="sm" :loading="true">Deleting…</x-ui.button>
        <x-ui.button href="{{ route('patients.create') }}" variant="success">
            <x-slot:icon><i class="bi bi-person-plus-fill"></i></x-slot:icon>
            New Patient
        </x-ui.button>

    Props:
        variant    — primary|secondary|success|warning|danger|ghost   (default: primary)
        size       — sm|md|lg                                          (default: md)
        loading    — bool show spinner, disables interaction           (default: false)
        disabled   — bool                                              (default: false)
        fullWidth  — bool w-full                                       (default: false)
        type       — button|submit|reset                               (default: button)
        href       — renders as <a> when present
    Slots:
        icon       — left icon (wrapped in aria-hidden span)
        iconRight  — right icon
        default    — label text
--}}
@props([
    'variant'   => 'primary',
    'size'      => 'md',
    'loading'   => false,
    'disabled'  => false,
    'fullWidth' => false,
    'type'      => 'button',
    'href'      => null,
])

@php
/* Base: shared structure for all variants */
$base = 'inline-flex items-center justify-center gap-2 font-semibold border transition-all select-none whitespace-nowrap '
      . 'focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-1 cursor-pointer';

/*
 * Height targets (min-height so text wraps don't collapse):
 *   sm → 32px  (py-1.5  + text-xs  line ≈ 32px)
 *   md → 40px  (py-2.5  + text-sm  line ≈ 40px)  ← spec target
 *   lg → 48px  (py-3    + text-base line ≈ 48px)
 */
$variants = [
    'primary'   => 'bg-[#4154f1] text-white border-[#4154f1] '
                 . 'hover:bg-[#3344d0] hover:border-[#3344d0] '
                 . 'focus-visible:ring-[#4154f1]/30 '
                 . 'active:scale-[.98]',

    'secondary' => 'bg-white text-[#475569] border-[#CBD5E1] '
                 . 'hover:bg-[#F8FAFC] hover:border-[#94A3B8] '
                 . 'focus-visible:ring-[#94A3B8]/25',

    'success'   => 'bg-[#10B981] text-white border-[#10B981] '
                 . 'hover:bg-[#059669] hover:border-[#059669] '
                 . 'focus-visible:ring-[#10B981]/30 '
                 . 'active:scale-[.98]',

    'warning'   => 'bg-[#F59E0B] text-white border-[#F59E0B] '
                 . 'hover:bg-[#D97706] hover:border-[#D97706] '
                 . 'focus-visible:ring-[#F59E0B]/30 '
                 . 'active:scale-[.98]',

    'danger'    => 'bg-[#EF4444] text-white border-[#EF4444] '
                 . 'hover:bg-[#DC2626] hover:border-[#DC2626] '
                 . 'focus-visible:ring-[#EF4444]/30 '
                 . 'active:scale-[.98]',

    'ghost'     => 'bg-transparent text-[#4154f1] border-transparent '
                 . 'hover:bg-[#EEF0FD] '
                 . 'focus-visible:ring-[#4154f1]/20',
];

$sizes = [
    'sm' => 'text-xs  px-3   py-1.5  rounded-md',  /* ≈ 32px */
    'md' => 'text-sm  px-4   py-2.5  rounded-lg',  /* ≈ 40px */
    'lg' => 'text-base px-6  py-3    rounded-lg',  /* ≈ 48px */
];

$cls = implode(' ', array_filter([
    $base,
    $variants[$variant] ?? $variants['primary'],
    $sizes[$size]       ?? $sizes['md'],
    $fullWidth   ? 'w-full'                                : '',
    ($disabled || $loading) ? 'opacity-50 cursor-not-allowed pointer-events-none' : '',
    'duration-150',
]));
@endphp

@if($href)
<a href="{{ $href }}" {{ $attributes->merge(['class' => $cls]) }} role="button"
   @if($disabled) aria-disabled="true" tabindex="-1" @endif>
    @if($loading)
        <svg class="animate-spin flex-shrink-0 {{ $size === 'sm' ? 'w-3 h-3' : 'w-3.5 h-3.5' }}"
             fill="none" viewBox="0 0 24 24" aria-hidden="true">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path  class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
        </svg>
    @elseif(isset($icon))
        <span aria-hidden="true">{{ $icon }}</span>
    @endif
    {{ $slot }}
    @isset($iconRight)<span aria-hidden="true">{{ $iconRight }}</span>@endisset
</a>
@else
<button type="{{ $type }}"
        {{ $attributes->merge(['class' => $cls]) }}
        @if($disabled || $loading) disabled aria-disabled="true" @endif>
    @if($loading)
        <svg class="animate-spin flex-shrink-0 {{ $size === 'sm' ? 'w-3 h-3' : 'w-3.5 h-3.5' }}"
             fill="none" viewBox="0 0 24 24" aria-hidden="true">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path  class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
        </svg>
    @elseif(isset($icon))
        <span aria-hidden="true">{{ $icon }}</span>
    @endif
    {{ $slot }}
    @isset($iconRight)<span aria-hidden="true">{{ $iconRight }}</span>@endisset
</button>
@endif
