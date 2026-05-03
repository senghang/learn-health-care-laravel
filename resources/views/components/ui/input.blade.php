{{--
    <x-ui.input name="patient_name" label="Patient Name" km="ឈ្មោះអ្នកជំងឺ" required />
    <x-ui.input name="phone" label="Phone" type="tel" prefix="🇰🇭" helper="+855 xxx xxx xxx" />
    <x-ui.input name="amount" label="Amount" suffix="$" type="number" />

    Props:
        name        — input name (required)
        label       — English label
        km          — Khmer label (shown before English)
        id          — override id (defaults to name)
        type        — input type (default: text)
        value       — current value
        placeholder — placeholder text
        required    — bool
        disabled    — bool
        readonly    — bool
        helper      — helper text below input
        prefix      — left adornment (icon class bi-* or text)
        suffix      — right adornment (icon class bi-* or text)
        error       — force error message (overrides $errors bag)
--}}
@props([
    'name'        => '',
    'label'       => null,
    'km'          => null,
    'id'          => null,
    'type'        => 'text',
    'value'       => null,
    'placeholder' => null,
    'required'    => false,
    'disabled'    => false,
    'readonly'    => false,
    'helper'      => null,
    'prefix'      => null,
    'suffix'      => null,
    'error'       => null,
])

@php
$inputId  = $id ?? $name;
$hasError = $error || ($errors->has($name));
$errMsg   = $error ?? $errors->first($name);

$inputBase = 'w-full text-sm rounded-lg border bg-white transition-colors duration-150 focus:outline-none focus:ring-2 focus:ring-offset-0';
$inputNormal = 'border-[#e2e8f0] text-[#374151] placeholder-[#94a3b8] focus:border-[#4154f1] focus:ring-[#4154f1]/20';
$inputError  = 'border-[#ef4444] text-[#374151] placeholder-[#94a3b8] focus:border-[#ef4444] focus:ring-[#ef4444]/20 bg-[#fef2f2]';
$inputDisabled = 'bg-[#f8faff] text-[#94a3b8] cursor-not-allowed';

$padding = ($prefix ? 'pl-9 pr-4' : 'px-4') . ' ' . ($suffix ? 'pr-9' : '') . ' py-2.5';
$inputCls = implode(' ', array_filter([$inputBase, $hasError ? $inputError : $inputNormal, $disabled ? $inputDisabled : '', $padding]));
@endphp

<div class="mb-4 {{ $attributes->get('class') }}">

    {{-- Label --}}
    @if($label || $km)
    <label for="{{ $inputId }}"
           class="block text-xs font-semibold mb-1.5 {{ $hasError ? 'text-[#dc2626]' : 'text-[#374151]' }}">
        @if($km)<span>{{ $km }}</span>@endif
        @if($km && $label)<span class="ml-1 font-normal text-[#94a3b8]">/ {{ $label }}</span>
        @elseif($label)<span>{{ $label }}</span>@endif
        @if($required)<span class="text-[#ef4444] ml-0.5" aria-hidden="true">*</span>@endif
    </label>
    @endif

    {{-- Input wrapper --}}
    <div class="relative">
        @if($prefix)
        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none" aria-hidden="true">
            @if(str_starts_with($prefix, 'bi-'))
                <i class="bi {{ $prefix }} text-sm text-[#94a3b8]"></i>
            @else
                <span class="text-sm text-[#94a3b8]">{{ $prefix }}</span>
            @endif
        </div>
        @endif

        <input
            id="{{ $inputId }}"
            name="{{ $name }}"
            type="{{ $type }}"
            value="{{ old($name, $value) }}"
            placeholder="{{ $placeholder }}"
            class="{{ $inputCls }}"
            @if($required) required aria-required="true" @endif
            @if($disabled) disabled @endif
            @if($readonly) readonly @endif
            @if($hasError) aria-invalid="true" aria-describedby="{{ $inputId }}-error" @endif
            @if($helper && !$hasError) aria-describedby="{{ $inputId }}-helper" @endif
        />

        @if($suffix)
        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none" aria-hidden="true">
            @if(str_starts_with($suffix, 'bi-'))
                <i class="bi {{ $suffix }} text-sm text-[#94a3b8]"></i>
            @else
                <span class="text-sm text-[#94a3b8]">{{ $suffix }}</span>
            @endif
        </div>
        @endif
    </div>

    {{-- Error --}}
    @if($hasError)
    <p id="{{ $inputId }}-error" class="flex items-center gap-1 mt-1.5 text-xs font-medium text-[#dc2626]" role="alert">
        <i class="bi bi-exclamation-circle-fill" aria-hidden="true"></i>
        {{ $errMsg }}
    </p>
    @elseif($helper)
    <p id="{{ $inputId }}-helper" class="mt-1.5 text-xs text-[#94a3b8]">{{ $helper }}</p>
    @endif
</div>
