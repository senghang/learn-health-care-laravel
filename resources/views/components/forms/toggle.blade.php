{{--
    Toggle switch — styled checkbox replacement.

    <x-forms.toggle name="is_active" :checked="$user->is_active" label="Active Account" />
    <x-forms.toggle name="notifications" label="Email Alerts" km="ការជូនដំណឹង" />

    Props:
        name    — input name attribute
        id      — input id (defaults to name)
        checked — boolean initial state
        label   — English label text
        km      — Khmer label text
        hint    — small description below label
        disabled — bool
        value   — value when checked (default: 1)
--}}
@props([
    'name'     => '',
    'id'       => null,
    'checked'  => false,
    'label'    => null,
    'km'       => null,
    'hint'     => null,
    'disabled' => false,
    'value'    => '1',
])
@php $inputId = $id ?? $name; @endphp

<label class="flex items-start gap-3 cursor-pointer {{ $disabled ? 'opacity-60 pointer-events-none' : '' }} {{ $attributes->get('class') }}">
    {{-- Hidden 0-value so unchecked still submits --}}
    <input type="hidden" name="{{ $name }}" value="0">

    {{-- The actual checkbox, visually hidden --}}
    <input type="checkbox"
           name="{{ $name }}"
           id="{{ $inputId }}"
           value="{{ $value }}"
           {{ $checked ? 'checked' : '' }}
           {{ $disabled ? 'disabled' : '' }}
           class="sr-only peer"
           {{ $attributes->except('class') }}>

    {{-- Visual track + knob --}}
    <span class="relative flex-shrink-0 w-10 h-6 rounded-full transition-colors duration-200
                 bg-[#e2e8f0] peer-checked:bg-[#4154f1] peer-focus-visible:ring-2 peer-focus-visible:ring-[#4154f1]/40">
        <span class="absolute top-0.5 left-0.5 w-5 h-5 rounded-full bg-white shadow
                     transition-transform duration-200 peer-checked:translate-x-4"
              style="transition:transform .2s"></span>
    </span>

    {{-- Label text --}}
    @if($km || $label || $hint)
    <div class="leading-tight mt-0.5">
        @if($km)
            <div class="text-sm font-semibold text-[#012970]">{{ $km }}</div>
            @if($label)<div class="text-[11px] text-[#94a3b8]">{{ $label }}</div>@endif
        @elseif($label)
            <div class="text-sm font-semibold text-[#012970]">{{ $label }}</div>
        @endif
        @if($hint)
            <div class="text-[11px] text-[#94a3b8] mt-0.5">{{ $hint }}</div>
        @endif
    </div>
    @endif
</label>

<style>
/* Ensure the knob transforms correctly relative to the track */
.peer:checked ~ span > span {
    transform: translateX(1rem);
}
</style>
