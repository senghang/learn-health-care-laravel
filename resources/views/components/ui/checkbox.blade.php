{{--
    <x-ui.checkbox name="agree" label="I agree to terms" />
    <x-ui.checkbox name="active" label="Active" description="This patient record is active." :checked="true" />

    Props:
        name        — input name
        label       — label text
        description — optional helper description below label
        value       — checkbox value (default: 1)
        checked     — bool default checked state
        disabled    — bool
        error       — force error (overrides $errors bag)
--}}
@props([
    'name'        => '',
    'label'       => null,
    'description' => null,
    'value'       => '1',
    'checked'     => false,
    'disabled'    => false,
    'error'       => null,
])

@php
$inputId  = $name . '_' . uniqid();
$hasError = $error || $errors->has($name);
$errMsg   = $error ?? $errors->first($name);
$isChecked = old($name, $checked) ? true : false;
@endphp

<div class="mb-3 {{ $attributes->get('class') }}">
    <label class="flex items-start gap-3 cursor-{{ $disabled ? 'not-allowed' : 'pointer' }} group">
        <div class="flex-shrink-0 mt-0.5">
            <input
                type="checkbox"
                id="{{ $inputId }}"
                name="{{ $name }}"
                value="{{ $value }}"
                @if($isChecked) checked @endif
                @if($disabled) disabled @endif
                class="sr-only peer"
            />
            <div class="w-4 h-4 rounded border-2 flex items-center justify-center transition-all duration-150
                        {{ $hasError ? 'border-[#ef4444]' : 'border-[#cbd5e1] group-hover:border-[#4154f1]' }}
                        peer-checked:bg-[#4154f1] peer-checked:border-[#4154f1]
                        peer-disabled:bg-[#f8faff] peer-disabled:border-[#e2e8f0]
                        peer-focus:ring-2 peer-focus:ring-[#4154f1]/30 peer-focus:ring-offset-1">
                <svg class="w-2.5 h-2.5 text-white opacity-0 peer-checked:opacity-100 transition-opacity" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true">
                    <path d="M13.854 3.646a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L6.5 10.293l6.646-6.647a.5.5 0 0 1 .708 0z"/>
                </svg>
            </div>
        </div>
        <div class="flex-1 min-w-0">
            @if($label)
            <span class="block text-sm font-medium {{ $disabled ? 'text-[#94a3b8]' : ($hasError ? 'text-[#dc2626]' : 'text-[#374151]') }}">
                {{ $label }}
            </span>
            @endif
            @if($description)
            <span class="block text-xs mt-0.5 text-[#94a3b8]">{{ $description }}</span>
            @endif
        </div>
    </label>

    @if($hasError)
    <p class="flex items-center gap-1 mt-1 text-xs font-medium text-[#dc2626] ml-7" role="alert">
        <i class="bi bi-exclamation-circle-fill" aria-hidden="true"></i>{{ $errMsg }}
    </p>
    @endif
</div>
