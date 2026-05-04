{{--
    Alert banner.

    <x-ui.alert type="success">Patient saved successfully.</x-ui.alert>
    <x-ui.alert type="warning" :dismissible="true">Low stock detected.</x-ui.alert>
    <x-ui.alert type="error" title="Validation error">
        Please fill in all required fields.
    </x-ui.alert>

    Props:
        type        — success | error | warning | info   (default: info)
        dismissible — bool show × close button           (default: false)
        icon        — override icon class (bi-*)
        title       — optional bold title line
--}}
@props([
    'type'        => 'info',
    'dismissible' => false,
    'icon'        => null,
    'title'       => null,
])

@php
$cfg = [
    'success' => ['cls' => 'bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl',  'icon' => 'bi-check-circle-fill',        'iCls' => 'text-emerald-500'],
    'error'   => ['cls' => 'bg-red-50    border border-red-200    text-red-800    rounded-xl',     'icon' => 'bi-exclamation-triangle-fill', 'iCls' => 'text-red-500'],
    'warning' => ['cls' => 'bg-amber-50  border border-amber-200  text-amber-800  rounded-xl',     'icon' => 'bi-exclamation-triangle-fill', 'iCls' => 'text-amber-500'],
    'info'    => ['cls' => 'bg-blue-50   border border-blue-200   text-blue-800   rounded-xl',     'icon' => 'bi-info-circle-fill',          'iCls' => 'text-blue-500'],
];
$c       = $cfg[$type] ?? $cfg['info'];
$iconCls = $icon ?? $c['icon'];
@endphp

<div x-data="{ show: true }" x-show="show" x-transition
     role="alert"
     class="flex items-start gap-3 px-4 py-3 mb-3 {{ $c['cls'] }} {{ $attributes->get('class') }}">

    <i class="bi {{ $iconCls }} flex-shrink-0 mt-0.5 text-base {{ $c['iCls'] }}" aria-hidden="true"></i>

    <div class="flex-1 text-sm leading-relaxed">
        @if($title)
            <div class="font-bold mb-0.5">{{ $title }}</div>
        @endif
        {{ $slot }}
    </div>

    @if($dismissible)
    <button @click="show = false" type="button"
            class="flex-shrink-0 flex items-center justify-center w-5 h-5 rounded
                   opacity-60 hover:opacity-100 transition-opacity bg-transparent border-0 cursor-pointer"
            aria-label="Dismiss">
        <i class="bi bi-x-lg text-[11px]" aria-hidden="true"></i>
    </button>
    @endif

</div>
