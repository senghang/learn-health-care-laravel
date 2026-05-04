{{--
    Full-featured textarea — label + textarea + helper/error. Matches x-ui.input visually.

    <x-ui.textarea name="notes"     label="Notes"     km="កំណត់ចំណាំ" :rows="4" />
    <x-ui.textarea name="diagnosis" label="Diagnosis" km="រោគវិនិច្ឆ័យ" required :rows="3" />

    Props:
        name, label, km, id, rows, value, placeholder, required, disabled, readonly, helper, error
--}}
@props([
    'name'        => '',
    'label'       => null,
    'km'          => null,
    'id'          => null,
    'rows'        => 4,
    'value'       => null,
    'placeholder' => null,
    'required'    => false,
    'disabled'    => false,
    'readonly'    => false,
    'helper'      => null,
    'error'       => null,
])

@php
$inputId  = $id ?? $name;
$hasError = $error || ($name && $errors->has($name));
$errMsg   = $error ?? ($name ? $errors->first($name) : null);

$base  = 'w-full text-sm border rounded-md px-3 py-2.5 transition-colors duration-150 focus:outline-none focus-visible:ring-2 resize-y';

$state = match(true) {
    $hasError  => 'border-[#EF4444] focus-visible:ring-[#EF4444]/20 focus-visible:border-[#EF4444]',
    $disabled || $readonly => 'border-[#E2E8F0] focus-visible:ring-0',
    default    => 'border-[#CBD5E1] focus-visible:ring-[#4154f1]/20 focus-visible:border-[#4154f1]',
};

$surface = ($disabled || $readonly)
    ? 'bg-[#F8FAFC] text-[#94A3B8] cursor-not-allowed'
    : 'bg-white text-[#0F172A] placeholder-[#94A3B8]';

$cls = "{$base} {$state} {$surface}";
@endphp

<div class="{{ $attributes->get('class', 'mb-4') }}">

    {{-- Label --}}
    @if($label || $km)
    <label for="{{ $inputId }}"
           class="block text-xs font-medium mb-1.5"
           style="color:{{ $hasError ? '#EF4444' : 'var(--text-secondary,#475569)' }}">
        @if($km)
            <span style="font-family:var(--font-khmer,'Noto Sans Khmer',sans-serif)">{{ $km }}</span>
            @if($label)
                <span class="ml-1 font-normal" style="color:var(--text-muted,#94A3B8)">/ {{ $label }}</span>
            @endif
        @else
            {{ $label }}
        @endif
        @if($required)
            <span style="color:#EF4444" aria-hidden="true"> *</span>
        @endif
    </label>
    @endif

    <textarea
        id="{{ $inputId }}"
        name="{{ $name }}"
        rows="{{ $rows }}"
        @if($placeholder) placeholder="{{ $placeholder }}" @endif
        class="{{ $cls }}"
        style="min-height:calc({{ $rows }} * 1.5rem + 1.25rem)"
        @if($required)  required aria-required="true" @endif
        @if($disabled)  disabled @endif
        @if($readonly)  readonly @endif
        @if($hasError)  aria-invalid="true" aria-describedby="{{ $inputId }}-error" @endif
        @if($helper && !$hasError) aria-describedby="{{ $inputId }}-helper" @endif
    >{{ old($name, $value) }}</textarea>

    @if($hasError && $errMsg)
    <p id="{{ $inputId }}-error" class="flex items-center gap-1 mt-1.5 text-xs font-medium" style="color:#EF4444" role="alert" aria-live="polite">
        <i class="bi bi-exclamation-circle-fill" style="font-size:11px" aria-hidden="true"></i>
        {{ $errMsg }}
    </p>
    @elseif($helper)
    <p id="{{ $inputId }}-helper" class="mt-1.5 text-xs" style="color:var(--text-muted,#94A3B8)">{{ $helper }}</p>
    @endif

</div>
