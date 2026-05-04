{{--
    Submit button with loading state — disables and shows spinner on form submit.

    <x-forms.submit-button>Save Patient</x-forms.submit-button>
    <x-forms.submit-button variant="success" loading-label="Creating…">Create Invoice</x-forms.submit-button>

    Props:
        label         — button text (or use default slot)
        loadingLabel  — text shown while submitting (default: "Saving…")
        variant       — primary | success | danger | secondary  (default: primary)
        icon          — Bootstrap icon class (default: bi-save-fill)
        fullWidth     — bool stretch to container width
--}}
@props([
    'label'        => null,
    'loadingLabel' => 'Saving…',
    'variant'      => 'primary',
    'icon'         => 'bi-save-fill',
    'fullWidth'    => false,
])

@php
/* All colors declared as Tailwind arbitrary values — no inline JS hover hacks */
$variants = [
    'primary'   => 'bg-[#4154f1] hover:bg-[#3347d4] border-[#4154f1] hover:border-[#3347d4] focus-visible:ring-[#4154f1]/30',
    'success'   => 'bg-[#10B981] hover:bg-[#059669] border-[#10B981] hover:border-[#059669] focus-visible:ring-emerald-300',
    'danger'    => 'bg-[#EF4444] hover:bg-[#DC2626] border-[#EF4444] hover:border-[#DC2626] focus-visible:ring-red-300',
    'secondary' => 'bg-[#64748B] hover:bg-[#475569] border-[#64748B] hover:border-[#475569] focus-visible:ring-slate-300',
];

$cls = implode(' ', array_filter([
    'inline-flex items-center gap-2 px-4 py-2.5 rounded-lg text-sm font-semibold text-white border',
    'transition-all duration-150 active:scale-[.98]',
    'focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-1',
    'disabled:opacity-70 disabled:cursor-not-allowed',
    $variants[$variant] ?? $variants['primary'],
    $fullWidth ? 'w-full justify-center' : '',
]));
@endphp

<button type="submit"
        x-data="{ loading: false }"
        @submit.document="loading = true"
        :disabled="loading"
        {{ $attributes->merge(['class' => $cls]) }}>

    {{-- Normal state --}}
    <span x-show="!loading" class="flex items-center gap-2">
        @if($icon)
            <i class="bi {{ $icon }}" aria-hidden="true"></i>
        @endif
        {{ $slot->isNotEmpty() ? $slot : $label }}
    </span>

    {{-- Loading state --}}
    <span x-show="loading" x-cloak class="flex items-center gap-2">
        <span class="w-4 h-4 rounded-full border-2 border-white/30 border-t-white animate-spin inline-block"></span>
        {{ $loadingLabel }}
    </span>

</button>
