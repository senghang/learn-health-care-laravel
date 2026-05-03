{{--
    Hover tooltip — wraps any element and shows a text tip on hover.
    Powered by Alpine.js (no extra lib needed).

    <x-ui.tooltip text="Patient has allergies">
        <i class="bi bi-exclamation-triangle-fill text-[#ff771d]"></i>
    </x-ui.tooltip>

    <x-ui.tooltip text="Inactive account" position="left">
        <x-ui.badge variant="danger">Inactive</x-ui.badge>
    </x-ui.tooltip>

    Props:
        text      — tooltip label (plain text only)
        position  — top | bottom | left | right  (default: top)
        delay     — ms before showing (default: 120)
--}}
@props([
    'text'     => '',
    'position' => 'top',
    'delay'    => 120,
])

@php
// Positioning classes for the tooltip box
$posMap = [
    'top'    => 'bottom-full left-1/2 -translate-x-1/2 mb-1.5',
    'bottom' => 'top-full left-1/2 -translate-x-1/2 mt-1.5',
    'left'   => 'right-full top-1/2 -translate-y-1/2 mr-1.5',
    'right'  => 'left-full top-1/2 -translate-y-1/2 ml-1.5',
];
$posClass = $posMap[$position] ?? $posMap['top'];

// Arrow classes
$arrowMap = [
    'top'    => 'top-full left-1/2 -translate-x-1/2 border-l-transparent border-r-transparent border-b-transparent border-t-[#1e293b]',
    'bottom' => 'bottom-full left-1/2 -translate-x-1/2 border-l-transparent border-r-transparent border-t-transparent border-b-[#1e293b]',
    'left'   => 'left-full top-1/2 -translate-y-1/2 border-t-transparent border-b-transparent border-r-transparent border-l-[#1e293b]',
    'right'  => 'right-full top-1/2 -translate-y-1/2 border-t-transparent border-b-transparent border-l-transparent border-r-[#1e293b]',
];
$arrowClass = $arrowMap[$position] ?? $arrowMap['top'];
@endphp

<span class="relative inline-flex items-center"
      x-data="{ show: false, timer: null }"
      @mouseenter="timer = setTimeout(() => show = true, {{ $delay }})"
      @mouseleave="clearTimeout(timer); show = false"
      @focusin="show = true"
      @focusout="show = false"
      {{ $attributes }}>

    {{-- Wrapped content --}}
    {{ $slot }}

    {{-- Tooltip box --}}
    <span
        x-show="show"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        role="tooltip"
        class="absolute {{ $posClass }} z-50 pointer-events-none whitespace-nowrap
               px-2.5 py-1.5 rounded-lg text-[11px] font-semibold text-white leading-tight
               shadow-lg"
        style="background:#1e293b;max-width:220px;white-space:normal">
        {{ $text }}
    </span>

</span>
