{{--
    Toggle switch — accessible checkbox replacement.

    <x-forms.toggle name="is_active" :checked="$user->is_active" label="Active Account" />
    <x-forms.toggle name="notifications" label="Email Alerts" km="ការជូនដំណឹង" />

    Props:
        name     — input name
        id       — override id (defaults to name)
        checked  — bool initial state
        label    — English label
        km       — Khmer label (dominant)
        hint     — helper text below label
        disabled — bool
        value    — value when checked (default: 1)
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

{{--
    Alpine drives both track color and knob position.
    Tailwind peer-checked: can't reach a knob nested inside the track span,
    so x-bind:class on both elements is the clean cross-browser solution.
--}}
<label x-data="{ on: @js((bool)$checked) }"
       class="flex items-start gap-3 select-none
              {{ $disabled ? 'opacity-60 pointer-events-none' : 'cursor-pointer' }}
              {{ $attributes->get('class') }}">

    {{-- Hidden 0-value so unchecked state submits correctly --}}
    <input type="hidden" name="{{ $name }}" value="0">

    {{-- Actual checkbox --}}
    <input type="checkbox"
           id="{{ $inputId }}"
           name="{{ $name }}"
           value="{{ $value }}"
           {{ $disabled ? 'disabled' : '' }}
           x-bind:checked="on"
           @change="on = $event.target.checked"
           class="sr-only"
           {{ $attributes->except('class') }}>

    {{-- Track --}}
    <span class="relative flex-shrink-0 w-10 h-6 rounded-full transition-colors duration-200
                 focus-within:ring-2 focus-within:ring-[var(--brand)]/40"
          :class="on ? 'bg-[var(--brand)]' : 'bg-slate-200'">
        {{-- Knob --}}
        <span class="absolute top-0.5 left-0.5 w-5 h-5 rounded-full bg-white shadow
                     transition-transform duration-200"
              :class="on ? 'translate-x-4' : 'translate-x-0'">
        </span>
    </span>

    {{-- Label text --}}
    @if($km || $label || $hint)
    <div class="leading-tight mt-0.5">
        @if($km)
            <div class="text-sm font-semibold text-[var(--text-primary)] font-khmer">{{ $km }}</div>
            @if($label)
                <div class="text-[11px] text-[var(--text-muted)]">{{ $label }}</div>
            @endif
        @elseif($label)
            <div class="text-sm font-semibold text-[var(--text-primary)]">{{ $label }}</div>
        @endif
        @if($hint)
            <div class="text-[11px] text-[var(--text-muted)] mt-0.5">{{ $hint }}</div>
        @endif
    </div>
    @endif

</label>
