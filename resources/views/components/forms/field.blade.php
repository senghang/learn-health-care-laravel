{{--
    Form field wrapper — owns label, control slot, validation error, hint.
    Every field in every form should use this. Never write bare label+input pairs.

    Basic:
    <x-forms.field name="surname" km="នាមត្រកូល" label="Surname" required>
        <x-forms.input name="surname" :value="old('surname')" />
    </x-forms.field>

    With AUTO badge:
    <x-forms.field name="" km="លេខអ្នកជំងឺ" label="Patient Code" badge="auto">
        <div class="...readonly display...">{{ $nextCode }}</div>
    </x-forms.field>

    With hint:
    <x-forms.field name="spid" km="លេខ SPID" hint="HEF / NSSF card number">
        <x-forms.input name="spid" />
    </x-forms.field>

    Props:
        name     — for @error lookup and <label for="…"> (pass '' to suppress lookup)
        label    — English label (muted qualifier after km)
        km       — Khmer label (dominant)
        badge    — inline label pill: 'auto' | 'optional' | null
        required — bool: show red * marker
        hint     — helper text below (hidden when error shown)
        for      — explicit <label for="…"> id override
--}}
@props([
    'name'     => '',
    'label'    => null,
    'km'       => null,
    'badge'    => null,
    'required' => false,
    'hint'     => null,
    'for'      => null,
])
@php
$inputId  = $for ?? $name;
$hasError = $name && $errors->has($name);

$badgeCfg = match($badge) {
    'auto'     => ['text' => 'AUTO',     'cls' => 'bg-emerald-100 text-emerald-800'],
    'optional' => ['text' => 'Optional', 'cls' => 'bg-slate-100 text-slate-500'],
    default    => null,
};
@endphp

<div {{ $attributes->merge(['class' => 'space-y-1']) }}>

    {{-- Label row --}}
    @if($label || $km || $badgeCfg)
    <label for="{{ $inputId }}"
           class="flex items-center gap-1.5 leading-none cursor-default select-none">

        @if($km)
            <span class="text-[13px] font-semibold text-[var(--text-primary)] font-khmer">{{ $km }}</span>
            @if($label)
                <span class="text-[11px] font-normal text-[var(--text-muted)]">/ {{ $label }}</span>
            @endif
        @elseif($label)
            <span class="text-[13px] font-semibold text-[var(--text-primary)]">{{ $label }}</span>
        @endif

        @if($badgeCfg)
            <span class="inline-flex items-center text-[10px] font-bold px-1.5 py-[3px] rounded-full leading-none flex-shrink-0 {{ $badgeCfg['cls'] }}">
                {{ $badgeCfg['text'] }}
            </span>
        @endif

        @if($required)
            <span class="text-sm font-black leading-none flex-shrink-0 text-red-500" aria-hidden="true">*</span>
        @endif

    </label>
    @endif

    {{-- Control --}}
    {{ $slot }}

    {{-- Error or hint --}}
    @if($hasError)
        @error($name)
            <p id="{{ $name }}_error" role="alert" aria-live="polite"
               class="flex items-center gap-1 text-[11px] leading-tight text-red-500 mt-0.5">
                <i class="bi bi-exclamation-circle-fill flex-shrink-0 text-[10px]" aria-hidden="true"></i>
                {{ $message }}
            </p>
        @enderror
    @elseif($hint)
        <p class="text-[11px] leading-tight text-[var(--text-muted)] mt-0.5">
            {{ $hint }}
        </p>
    @endif

</div>
