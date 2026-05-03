{{--
    <x-ui.select name="doctor_id" label="Doctor" km="វេជ្ជបណ្ឌិត" placeholder="— Select doctor —" required>
        @foreach($doctors as $d)
            <option value="{{ $d->id }}" @selected(old('doctor_id') == $d->id)>{{ $d->name }}</option>
        @endforeach
    </x-ui.select>

    Props:
        name        — select name
        label       — English label
        km          — Khmer label
        id          — override id
        placeholder — disabled first option text
        required    — bool
        disabled    — bool
        helper      — helper text
        error       — force error (overrides $errors bag)
--}}
@props([
    'name'        => '',
    'label'       => null,
    'km'          => null,
    'id'          => null,
    'placeholder' => null,
    'required'    => false,
    'disabled'    => false,
    'helper'      => null,
    'error'       => null,
])

@php
$inputId  = $id ?? $name;
$hasError = $error || $errors->has($name);
$errMsg   = $error ?? $errors->first($name);

$base = 'w-full text-sm rounded-lg border px-4 py-2.5 bg-white transition-colors duration-150 focus:outline-none focus:ring-2 focus:ring-offset-0 appearance-none cursor-pointer';
$normal = 'border-[#e2e8f0] text-[#374151] focus:border-[#4154f1] focus:ring-[#4154f1]/20';
$errCls = 'border-[#ef4444] text-[#374151] focus:border-[#ef4444] focus:ring-[#ef4444]/20 bg-[#fef2f2]';
$disCls = 'bg-[#f8faff] text-[#94a3b8] cursor-not-allowed';
$cls = implode(' ', array_filter([$base, $hasError ? $errCls : $normal, $disabled ? $disCls : '']));
@endphp

<div class="mb-4 {{ $attributes->get('class') }}">
    @if($label || $km)
    <label for="{{ $inputId }}" class="block text-xs font-semibold mb-1.5 {{ $hasError ? 'text-[#dc2626]' : 'text-[#374151]' }}">
        @if($km)<span>{{ $km }}</span>@endif
        @if($km && $label)<span class="ml-1 font-normal text-[#94a3b8]">/ {{ $label }}</span>
        @elseif($label)<span>{{ $label }}</span>@endif
        @if($required)<span class="text-[#ef4444] ml-0.5" aria-hidden="true">*</span>@endif
    </label>
    @endif

    <div class="relative">
        <select
            id="{{ $inputId }}"
            name="{{ $name }}"
            class="{{ $cls }}"
            style="padding-right:2.5rem"
            @if($required) required aria-required="true" @endif
            @if($disabled) disabled @endif
            @if($hasError) aria-invalid="true" aria-describedby="{{ $inputId }}-error" @endif
        >
            @if($placeholder)
            <option value="" disabled {{ old($name) ? '' : 'selected' }}>{{ $placeholder }}</option>
            @endif
            {{ $slot }}
        </select>
        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none" aria-hidden="true">
            <i class="bi bi-chevron-down text-xs text-[#94a3b8]"></i>
        </div>
    </div>

    @if($hasError)
    <p id="{{ $inputId }}-error" class="flex items-center gap-1 mt-1.5 text-xs font-medium text-[#dc2626]" role="alert">
        <i class="bi bi-exclamation-circle-fill" aria-hidden="true"></i>{{ $errMsg }}
    </p>
    @elseif($helper)
    <p id="{{ $inputId }}-helper" class="mt-1.5 text-xs text-[#94a3b8]">{{ $helper }}</p>
    @endif
</div>
