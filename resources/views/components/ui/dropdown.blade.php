{{--
    <x-ui.dropdown>
        <x-slot:trigger>
            <x-ui.button variant="secondary" size="sm">
                Actions <i class="bi bi-chevron-down text-xs ml-1"></i>
            </x-ui.button>
        </x-slot:trigger>
        <x-ui.dropdown-item href="{{ route('patients.show', $p->code) }}" icon="bi-eye">View</x-ui.dropdown-item>
        <x-ui.dropdown-item href="{{ route('patients.edit', $p->code) }}" icon="bi-pencil">Edit</x-ui.dropdown-item>
        <x-ui.dropdown-divider />
        <x-ui.dropdown-item icon="bi-trash" variant="danger" @click="...">Delete</x-ui.dropdown-item>
    </x-ui.dropdown>

    Props:
        align   — left|right (default: right)
        width   — Tailwind width class (default: w-48)
--}}
@props([
    'align' => 'right',
    'width' => 'w-48',
])

@php
$alignCls = $align === 'left' ? 'left-0' : 'right-0';
@endphp

<div class="relative inline-block {{ $attributes->get('class') }}"
     x-data="{ open: false }"
     @click.outside="open = false"
     @keydown.escape.window="open = false">

    {{-- Trigger --}}
    <div @click="open = !open" class="cursor-pointer">
        {{ $trigger }}
    </div>

    {{-- Menu --}}
    <div
        x-show="open"
        x-transition:enter="transition duration-150 ease-out"
        x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition duration-100 ease-in"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute z-40 mt-1 {{ $width }} {{ $alignCls }} bg-white rounded-xl py-1 overflow-hidden"
        style="box-shadow:0 4px 20px rgba(17,24,39,.12),0 1px 3px rgba(17,24,39,.06);border:1px solid #e6e9f0"
        role="menu"
        x-cloak
    >
        {{ $slot }}
    </div>
</div>


{{-- ── Sub-components inline ──────────────────────────────── --}}
