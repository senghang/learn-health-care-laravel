{{--
    Search input with clear button and optional icon.

    <x-forms.search-input name="search" placeholder="Search patients..." />
    <x-forms.search-input name="q" :value="request('q')" placeholder="Filter by name or ID" />

    Props:
        name        — input name / id
        value       — initial value (default: request(name))
        placeholder — hint text
        autofocus   — bool
        clearable   — bool show × clear button (default: true)
        width       — tailwind max-w class (default: none, full width of container)
--}}
@props([
    'name'        => 'search',
    'value'       => null,
    'placeholder' => 'Search…',
    'autofocus'   => false,
    'clearable'   => true,
    'width'       => null,
])
@php
$val = $value ?? request($name, '');
$widthClass = $width ? "max-w-{$width}" : '';
@endphp

<div class="relative {{ $widthClass }}" x-data="{ val: @js($val) }" {{ $attributes }}>
    {{-- Search icon --}}
    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[#94a3b8] pointer-events-none"
          style="font-size:13px">
        <i class="bi bi-search" aria-hidden="true"></i>
    </span>

    <input
        type="search"
        name="{{ $name }}"
        id="{{ $name }}"
        x-model="val"
        value="{{ $val }}"
        placeholder="{{ $placeholder }}"
        {{ $autofocus ? 'autofocus' : '' }}
        autocomplete="off"
        class="w-full py-[9px] pl-9 {{ $clearable ? 'pr-9' : 'pr-3' }} text-sm rounded-lg border
               transition-colors duration-150
               focus:outline-none focus:ring-2 focus:ring-[#4154f1]/25 focus:border-[#4154f1]"
        style="font-size:13px;color:var(--text-primary,#0F172A);border-color:var(--border-default,#CBD5E1);background:white">

    {{-- Clear button --}}
    @if($clearable)
    <button type="button"
            x-show="val.length > 0"
            x-cloak
            @click="val = ''; $el.closest('form')?.submit()"
            class="absolute right-3 top-1/2 -translate-y-1/2 text-[#94a3b8] hover:text-[#64748b]
                   w-5 h-5 flex items-center justify-center rounded transition-colors"
            aria-label="Clear search">
        <i class="bi bi-x-lg" style="font-size:11px"></i>
    </button>
    @endif
</div>
