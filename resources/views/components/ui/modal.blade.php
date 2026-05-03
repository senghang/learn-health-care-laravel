{{--
    Usage (trigger from any button):
    <x-ui.modal id="addPatient" title="Register Patient" km="ចុះឈ្មោះអ្នកជំងឺ" size="lg">
        <x-slot:trigger>
            <x-ui.button>Open Modal</x-ui.button>
        </x-slot:trigger>

        <p>Modal body content here</p>

        <x-slot:footer>
            <x-ui.button variant="secondary" @click="open = false">Cancel</x-ui.button>
            <x-ui.button variant="primary" type="submit">Save</x-ui.button>
        </x-slot:footer>
    </x-ui.modal>

    Or open programmatically:
    <button onclick="document.getElementById('addPatient').dispatchEvent(new Event('open'))">Open</button>

    Props:
        id       — unique modal id (required for programmatic open)
        title    — modal title
        km       — Khmer title
        size     — sm|md|lg|xl|full (default: md)
        static   — bool disable click-outside close (default: false)
--}}
@props([
    'id'     => null,
    'title'  => null,
    'km'     => null,
    'size'   => 'md',
    'static' => false,
])

@php
$sizes = [
    'sm'   => 'max-w-sm',
    'md'   => 'max-w-lg',
    'lg'   => 'max-w-2xl',
    'xl'   => 'max-w-4xl',
    'full' => 'max-w-full mx-4',
];
$panelCls = 'relative bg-white rounded-2xl shadow-2xl w-full flex flex-col max-h-[90vh] ' . ($sizes[$size] ?? $sizes['md']);
$modalId = $id ?? 'modal_' . uniqid();
@endphp

<div
    x-data="{ open: false }"
    @if($id) id="{{ $id }}" @endif
    @open.window="open = true"
    x-on:open="open = true"
>
    {{-- Trigger slot --}}
    @isset($trigger)
    <div @click="open = true">{{ $trigger }}</div>
    @endisset

    {{-- Overlay --}}
    <div
        x-show="open"
        x-transition:enter="transition duration-200 ease-out"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition duration-150 ease-in"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        style="background:rgba(17,24,39,.45);backdrop-filter:blur(4px)"
        @if(!$static) @click.self="open = false" @endif
        @keydown.escape.window="open = false"
        role="dialog"
        aria-modal="true"
        @if($title || $km) aria-labelledby="{{ $modalId }}-title" @endif
        x-cloak
    >
        {{-- Panel --}}
        <div
            class="{{ $panelCls }}"
            x-transition:enter="transition duration-200 ease-out"
            x-transition:enter-start="opacity-0 scale-95 translate-y-2"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="transition duration-150 ease-in"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            @click.stop
        >
            {{-- Header --}}
            @if($title || $km || isset($header))
            <div class="flex items-center justify-between px-6 py-4 flex-shrink-0"
                 style="border-bottom:1px solid #e6e9f0">
                @isset($header)
                {{ $header }}
                @else
                <div id="{{ $modalId }}-title">
                    @if($km)<span class="text-base font-bold text-[#1a1f36]">{{ $km }}</span>@endif
                    @if($km && $title)<span class="text-xs ml-1.5 text-[#6b7280]">/ {{ $title }}</span>
                    @elseif($title)<span class="text-base font-bold text-[#1a1f36]">{{ $title }}</span>@endif
                </div>
                @endisset
                <button @click="open = false" type="button"
                        class="flex items-center justify-center w-8 h-8 rounded-lg text-[#6b7280] hover:text-[#374151] hover:bg-[#f3f4f6] transition-colors ml-4"
                        aria-label="Close modal">
                    <i class="bi bi-x-lg" style="font-size:13px" aria-hidden="true"></i>
                </button>
            </div>
            @endif

            {{-- Body --}}
            <div class="flex-1 overflow-y-auto p-6">
                {{ $slot }}
            </div>

            {{-- Footer --}}
            @isset($footer)
            <div class="flex items-center justify-end gap-3 px-6 py-4 flex-shrink-0"
                 style="border-top:1px solid #e6e9f0;background:#f9fafb">
                {{ $footer }}
            </div>
            @endisset
        </div>
    </div>
</div>
