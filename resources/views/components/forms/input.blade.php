{{--
    Bare styled input — 40px height, full visual state coverage, ARIA-ready.
    Use inside <x-forms.field> for labels and error messages.
    For standalone use with labels, see <x-ui.input>.

    <x-forms.input name="surname" :value="old('surname')" required />
    <x-forms.input type="date" name="dob" />
    <x-forms.input name="code" readonly value="PT-0042" />

    Visual states (auto-detected):
        empty    → white bg, #CBD5E1 border
        filled   → #FAFBFF bg (subtle tint), slightly stronger border
        focus    → brand border + ring
        error    → red border + ring  (from $errors bag or hasError prop)
        readonly → #F1F5F9 bg, muted text, default cursor
        disabled → #F8FAFC bg, disabled text, not-allowed cursor

    Note: Error *messages* are rendered by <x-forms.field>, not here.
          This component only applies the visual error state (border/ring).

    Props:
        hasError — bool force error state (auto-detected from $errors if name set)
--}}
@props(['hasError' => false])

@php
$name       = $attributes->get('name', '');
$rawValue   = (string)($attributes->get('value', ''));
$isError    = $hasError || ($name && $errors->has($name));
$isDisabled = $attributes->has('disabled');
$isReadonly = $attributes->has('readonly');
$isFilled   = !$isDisabled && !$isReadonly && $rawValue !== '';

/*
 * Height: py-[9px] + text-sm (14px × 1.5 line-height = 21px) = 9+21+9 = 39 ≈ 40px
 */
$base = 'w-full text-sm border rounded-md transition-colors duration-150 focus:outline-none focus-visible:ring-2 px-3 py-[9px]';

$state = match(true) {
    $isError    => 'border-[#EF4444] focus-visible:ring-[#EF4444]/20 focus-visible:border-[#EF4444]',
    $isDisabled => 'border-[#E2E8F0] focus-visible:ring-0',
    $isReadonly => 'border-[#E2E8F0] focus-visible:ring-0',
    $isFilled   => 'border-[#9BA8C0] focus-visible:ring-[#4154f1]/20 focus-visible:border-[#4154f1]',
    default     => 'border-[#CBD5E1] focus-visible:ring-[#4154f1]/20 focus-visible:border-[#4154f1]',
};

$surface = match(true) {
    $isDisabled => 'bg-[#F8FAFC] text-[#CBD5E1] cursor-not-allowed',
    $isReadonly => 'bg-[#F1F5F9] text-[#64748B] cursor-default',
    $isFilled   => 'bg-[#FAFBFF] text-[#0F172A] placeholder-[#94A3B8]',
    default     => 'bg-white text-[#0F172A] placeholder-[#94A3B8]',
};

$cls = "{$base} {$state} {$surface}";

/* ARIA — aria-describedby must match the <p id="…"> rendered by x-forms.field */
$aria = [];
if ($isError && $name)            $aria['aria-invalid']     = 'true';
if ($attributes->has('required')) $aria['aria-required']    = 'true';
if ($isError && $name)            $aria['aria-describedby'] = $name . '_error';
@endphp

<input {{ $attributes->merge(array_merge(['class' => $cls], $aria)) }}>
