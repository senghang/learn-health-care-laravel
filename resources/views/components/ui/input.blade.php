{{--
    Full-featured form field — label + input + prefix/suffix + helper/error.
    Use x-forms.input for bare inputs without the label wrapper.

    <x-ui.input name="surname" label="Surname" km="នាមត្រកូល" required />
    <x-ui.input name="phone"   label="Phone"   type="tel" prefix="bi-telephone" helper="+855 xxx xxx xxx" />
    <x-ui.input name="amount" label="Amount"  suffix="$" type="number" />
    <x-ui.input name="code"   label="Code"   readonly value="PT-0042" />

    Props:
        name        — input name (required)
        label       — English label
        km          — Khmer label (shown above English, dominant)
        id          — override id (defaults to name)
        type        — input type (default: text)
        value       — current value
        placeholder — placeholder text
        required    — bool
        disabled    — bool
        readonly    — bool
        helper      — helper text below input
        prefix      — left adornment: Bootstrap icon class (bi-*) or plain text
        suffix      — right adornment: Bootstrap icon class (bi-*) or plain text
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
$hasError = $error || ($name && $errors->has($name));
$errMsg   = $error ?? ($name ? $errors->first($name) : null);

/*
 * Height: py-[9px] + text-sm (14px line-height 1.5 ≈ 21px) ≈ 40px
 */
$padX  = ($prefix ? 'pl-9' : 'pl-3') . ' ' . ($suffix ? 'pr-9' : 'pr-3');
$padY  = 'py-[9px]';

$base  = "w-full text-sm border rounded-md transition-colors duration-150 focus:outline-none focus-visible:ring-2 {$padX} {$padY}";

$state = match(true) {
    $hasError  => 'border-[#EF4444] focus-visible:ring-[#EF4444]/20 focus-visible:border-[#EF4444]',
    $disabled || $readonly => 'border-[#E2E8F0] focus-visible:ring-0',
    default    => 'border-[#CBD5E1] focus-visible:ring-[#4154f1]/20 focus-visible:border-[#4154f1]',
};

$surface = match(true) {
    $disabled || $readonly => 'bg-[#F8FAFC] text-[#94A3B8] cursor-not-allowed',
    $hasError => 'bg-white text-[#0F172A] placeholder-[#94A3B8]',
    default   => 'bg-white text-[#0F172A] placeholder-[#94A3B8]',
};

$inputCls = "{$base} {$state} {$surface}";
@endphp

<div class="{{ $attributes->get('class', 'mb-4') }}">

    {{-- Label: Khmer dominant, English qualifier --}}
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

    {{-- Input wrapper --}}
    <div class="relative">
        {{-- Prefix --}}
        @if($prefix)
        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none" aria-hidden="true">
            @if(str_starts_with($prefix, 'bi-'))
                <i class="bi {{ $prefix }} text-sm" style="color:var(--text-muted,#94A3B8)"></i>
            @else
                <span class="text-sm" style="color:var(--text-muted,#94A3B8)">{{ $prefix }}</span>
            @endif
        </div>
        @endif

        <input
            id="{{ $inputId }}"
            name="{{ $name }}"
            type="{{ $type }}"
            value="{{ old($name, $value) }}"
            @if($placeholder) placeholder="{{ $placeholder }}" @endif
            class="{{ $inputCls }}"
            @if($required)  required aria-required="true" @endif
            @if($disabled)  disabled @endif
            @if($readonly)  readonly @endif
            @if($hasError)  aria-invalid="true" aria-describedby="{{ $inputId }}-error" @endif
            @if($helper && !$hasError) aria-describedby="{{ $inputId }}-helper" @endif
        />

        {{-- Suffix --}}
        @if($suffix)
        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none" aria-hidden="true">
            @if(str_starts_with($suffix, 'bi-'))
                <i class="bi {{ $suffix }} text-sm" style="color:var(--text-muted,#94A3B8)"></i>
            @else
                <span class="text-sm" style="color:var(--text-muted,#94A3B8)">{{ $suffix }}</span>
            @endif
        </div>
        @endif
    </div>

    {{-- Error / helper --}}
    @if($hasError && $errMsg)
    <p id="{{ $inputId }}-error" class="flex items-center gap-1 mt-1.5 text-xs font-medium" style="color:#EF4444" role="alert" aria-live="polite">
        <i class="bi bi-exclamation-circle-fill" style="font-size:11px" aria-hidden="true"></i>
        {{ $errMsg }}
    </p>
    @elseif($helper)
    <p id="{{ $inputId }}-helper" class="mt-1.5 text-xs" style="color:var(--text-muted,#94A3B8)">{{ $helper }}</p>
    @endif

</div>
