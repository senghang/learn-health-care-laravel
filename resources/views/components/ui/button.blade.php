{{--
    <x-ui.button variant="primary" size="md">Save Patient</x-ui.button>
    <x-ui.button variant="danger" size="sm" :loading="true">Deleting…</x-ui.button>
    <x-ui.button variant="success" href="{{ route('patients.create') }}">
        <x-slot:icon><i class="bi bi-person-plus-fill"></i></x-slot:icon>
        New Patient
    </x-ui.button>

    Props:
        variant    — primary|secondary|success|warning|danger|ghost   (default: primary)
        size       — sm|md|lg                                          (default: md)
        loading    — bool show spinner                                 (default: false)
        disabled   — bool                                              (default: false)
        fullWidth  — bool w-full                                       (default: false)
        type       — button|submit|reset                               (default: button)
        href       — renders as <a> when present
    Slots:
        icon       — left icon slot
        iconRight  — right icon slot
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
$base = 'inline-flex items-center justify-center gap-2 font-semibold rounded-lg border transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-offset-1 select-none whitespace-nowrap';

$variants = [
    'primary'   => 'bg-[#4154f1] text-white border-[#4154f1] hover:bg-[#3344d0] focus:ring-[#4154f1]/30',
    'secondary' => 'bg-white text-[#374151] border-[#d1d5db] hover:bg-[#f9fafb] hover:border-[#9ca3af] focus:ring-[#6b7280]/20',
    'success'   => 'bg-[#2eca6a] text-white border-[#2eca6a] hover:bg-[#25b55f] focus:ring-[#2eca6a]/30',
    'warning'   => 'bg-[#ff771d] text-white border-[#ff771d] hover:bg-[#e56515] focus:ring-[#ff771d]/30',
    'danger'    => 'bg-[#ef4444] text-white border-[#ef4444] hover:bg-[#dc2626] focus:ring-[#ef4444]/30',
    'ghost'     => 'bg-transparent text-[#4154f1] border-transparent hover:bg-[#f3f4f6] focus:ring-[#4154f1]/20',
];

$sizes = [
    'sm' => 'text-xs px-3 py-1.5',
    'md' => 'text-sm px-4 py-2',
    'lg' => 'text-base px-6 py-2.5',
];

$cls = implode(' ', array_filter([
    $base,
    $variants[$variant] ?? $variants['primary'],
    $sizes[$size] ?? $sizes['md'],
    $fullWidth ? 'w-full' : '',
    ($disabled || $loading) ? 'opacity-60 cursor-not-allowed pointer-events-none' : '',
]));
@endphp

@if($href)
<a href="{{ $href }}" {{ $attributes->merge(['class' => $cls]) }} role="button"
   @if($disabled) aria-disabled="true" tabindex="-1" @endif>
    @if($loading)
        <svg class="animate-spin w-3.5 h-3.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" aria-hidden="true">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
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
        <svg class="animate-spin w-3.5 h-3.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" aria-hidden="true">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
        </svg>
    @elseif(isset($icon))
        <span aria-hidden="true">{{ $icon }}</span>
    @endif
    {{ $slot }}
    @isset($iconRight)<span aria-hidden="true">{{ $iconRight }}</span>@endisset
</button>
@endif
