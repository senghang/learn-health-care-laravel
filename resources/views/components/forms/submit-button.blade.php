{{--
    Submit button with loading state. Disables and shows spinner on form submit.

    <x-forms.submit-button>Save Patient</x-forms.submit-button>
    <x-forms.submit-button label="Create Invoice" loading-label="Saving…" variant="success" />

    Props:
        label         — button text (or use default slot)
        loadingLabel  — text while submitting (default: "Saving…")
        variant       — primary | success | danger | secondary  (default: primary)
        icon          — Bootstrap icon class (optional)
        fullWidth     — bool stretch to container width
--}}
@props([
    'label'        => null,
    'loadingLabel' => 'Saving…',
    'variant'      => 'primary',
    'icon'         => 'bi-save-fill',
    'fullWidth'    => false,
])

@php
$colorMap = [
    'primary'   => ['bg' => '#4154f1', 'hover' => '#3347d4'],
    'success'   => ['bg' => '#2eca6a', 'hover' => '#25b35e'],
    'danger'    => ['bg' => '#e74c3c', 'hover' => '#c0392b'],
    'secondary' => ['bg' => '#64748b', 'hover' => '#475569'],
];
$c = $colorMap[$variant] ?? $colorMap['primary'];
$widthClass = $fullWidth ? 'w-full justify-center' : '';
@endphp

<button type="submit"
        x-data="{ loading: false }"
        @submit.document="loading = true"
        :disabled="loading"
        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold text-white
               transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-offset-1
               disabled:opacity-70 disabled:cursor-not-allowed {{ $widthClass }}"
        style="background:{{ $c['bg'] }};--hover-bg:{{ $c['hover'] }};focus-ring-color:{{ $c['bg'] }}"
        onmouseover="this.style.background='{{ $c['hover'] }}'"
        onmouseout="this.style.background='{{ $c['bg'] }}'"
        {{ $attributes->except(['class']) }}>

    {{-- Normal state --}}
    <span x-show="!loading" class="flex items-center gap-2">
        @if($icon)
            <i class="bi {{ $icon }}" aria-hidden="true"></i>
        @endif
        {{ $slot->isNotEmpty() ? $slot : $label }}
    </span>

    {{-- Loading state --}}
    <span x-show="loading" x-cloak class="flex items-center gap-2">
        <span class="w-4 h-4 rounded-full border-2 border-white/30 border-t-white animate-spin inline-block"></span>
        {{ $loadingLabel }}
    </span>

</button>
