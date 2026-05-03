{{--
    <x-ui.alert type="success">Patient saved successfully.</x-ui.alert>
    <x-ui.alert type="warning" :dismissible="true">Low stock detected for 3 items.</x-ui.alert>
    <x-ui.alert type="error">
        <strong>Validation error:</strong> Please fill in all required fields.
    </x-ui.alert>

    Props:
        type        — success|error|warning|info   (default: info)
        dismissible — bool show close button        (default: false)
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
    'success' => ['bg'=>'#e8f8ef','border'=>'#86efac','text'=>'#15803d','icon'=>'bi-check-circle-fill','icolor'=>'#2eca6a'],
    'error'   => ['bg'=>'#fef2f2','border'=>'#fca5a5','text'=>'#b91c1c','icon'=>'bi-exclamation-triangle-fill','icolor'=>'#ef4444'],
    'warning' => ['bg'=>'#fff7ed','border'=>'#fed7aa','text'=>'#c2410c','icon'=>'bi-exclamation-triangle-fill','icolor'=>'#ff771d'],
    'info'    => ['bg'=>'#eff6ff','border'=>'#bfdbfe','text'=>'#1e40af','icon'=>'bi-info-circle-fill','icolor'=>'#3b82f6'],
];
$c = $cfg[$type] ?? $cfg['info'];
$iconCls = $icon ?? $c['icon'];
@endphp

<div x-data="{ show: true }" x-show="show" x-transition
     role="alert"
     style="background:{{ $c['bg'] }};border:1px solid {{ $c['border'] }};color:{{ $c['text'] }};border-radius:10px;padding:12px 16px"
     class="flex items-start gap-3 mb-3 {{ $attributes->get('class') }}">

    <i class="bi {{ $iconCls }} flex-shrink-0 mt-0.5" style="font-size:15px;color:{{ $c['icolor'] }}" aria-hidden="true"></i>

    <div class="flex-1 text-sm leading-relaxed">
        @if($title)
            <div class="font-bold mb-0.5">{{ $title }}</div>
        @endif
        {{ $slot }}
    </div>

    @if($dismissible)
    <button @click="show = false" type="button"
            class="flex-shrink-0 flex items-center justify-center w-5 h-5 rounded opacity-60 hover:opacity-100 transition-opacity"
            style="color:{{ $c['text'] }};background:none;border:none;cursor:pointer"
            aria-label="Dismiss">
        <i class="bi bi-x-lg" style="font-size:11px" aria-hidden="true"></i>
    </button>
    @endif
</div>
