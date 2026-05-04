{{--
    Styled select — 40px height, matches input states, Alpine-powered placeholder tint.
    Use inside <x-forms.field> for labels and error messages.

    <x-forms.select name="sex" required>
        <option value="" disabled>ជ្រើសភេទ / Choose…</option>
        <option value="M" @selected(old('sex') === 'M')>♂ ប្រុស / Male</option>
        <option value="F" @selected(old('sex') === 'F')>♀ ស្រី / Female</option>
    </x-forms.select>

    Visual states: default / filled (non-empty) / error (auto) / disabled
    Placeholder tint: when the selected option value is "", the select text is rendered
    in --text-muted color via Alpine, making empty vs filled immediately distinguishable.

    Note: Error *messages* are rendered by <x-forms.field>, not here.

    Props:
        hasError — bool force error state
--}}
@props(['hasError' => false])

@php
$name       = $attributes->get('name', '');
$rawValue   = (string)($attributes->get('value', old($name, '')));
$isError    = $hasError || ($name && $errors->has($name));
$isDisabled = $attributes->has('disabled');

$base = 'w-full text-sm border rounded-md transition-colors duration-150 focus:outline-none focus-visible:ring-2 appearance-none px-3 py-[9px]';

$state = match(true) {
    $isError    => 'border-[#EF4444] focus-visible:ring-[#EF4444]/20 focus-visible:border-[#EF4444]',
    $isDisabled => 'border-[#E2E8F0] focus-visible:ring-0',
    default     => 'border-[#CBD5E1] focus-visible:ring-[#4154f1]/20 focus-visible:border-[#4154f1]',
};

$surface = $isDisabled ? 'bg-[#F8FAFC] cursor-not-allowed' : 'bg-white';

$cls = "{$base} {$state} {$surface}";

/* ARIA */
$aria = [];
if ($isError && $name)            $aria['aria-invalid']     = 'true';
if ($attributes->has('required')) $aria['aria-required']    = 'true';
if ($isError && $name)            $aria['aria-describedby'] = $name . '_error';

/* Escaped initial value for Alpine */
$initVal = e($rawValue);
@endphp

{{--
    Alpine x-data wraps the select to drive the placeholder-vs-filled text color.
    x-init reads the actual DOM-selected value after @selected directives have run,
    which is more reliable than the PHP-computed $rawValue.
--}}
<div class="relative"
     x-data="{ val: '{{ $initVal }}' }"
     x-init="val = $refs.sel.value">

    <select {{ $attributes->merge(array_merge(['class' => $cls], $aria)) }}
            x-ref="sel"
            x-model="val"
            :style="val === ''
                ? 'padding-right:2.5rem;color:var(--text-muted,#94A3B8)'
                : 'padding-right:2.5rem;color:var(--text-primary,#0F172A)'">
        {{ $slot }}
    </select>

    {{-- Custom chevron replaces native browser arrow --}}
    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none" aria-hidden="true">
        <i class="bi bi-chevron-down" style="font-size:11px;color:{{ $isDisabled ? '#CBD5E1' : '#64748B' }}"></i>
    </div>
</div>
