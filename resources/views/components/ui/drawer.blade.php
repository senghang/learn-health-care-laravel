{{--
    Slide-in drawer panel — right side by default. Powered by Alpine.js.

    <!-- Trigger -->
    <x-ui.button @click="$dispatch('open-drawer', {id:'patientDrawer'})">View</x-ui.button>

    <!-- Drawer -->
    <x-ui.drawer id="patientDrawer" title="Patient Details" km="ព័ត៌មានអ្នកជំងឺ" size="md">
        <p>Drawer body content here.</p>
        <x-slot:footer>
            <x-ui.button variant="primary">Save</x-ui.button>
        </x-slot:footer>
    </x-ui.drawer>

    Props:
        id      — unique drawer id (used by open-drawer event)
        title   — English title shown in header
        km      — Khmer title
        size    — sm | md | lg | xl | full  (default: md)
        side    — right | left  (default: right)
        static  — bool: clicking backdrop does NOT close drawer
--}}
@props([
    'id'     => 'drawer',
    'title'  => '',
    'km'     => null,
    'size'   => 'md',
    'side'   => 'right',
    'static' => false,
])

@php
$widthMap = [
    'sm'   => 'max-w-xs',
    'md'   => 'max-w-md',
    'lg'   => 'max-w-lg',
    'xl'   => 'max-w-xl',
    'full' => 'max-w-full',
];
$maxW = $widthMap[$size] ?? $widthMap['md'];
$slideIn  = $side === 'left' ? '-translate-x-full' : 'translate-x-full';
$position = $side === 'left' ? 'left-0' : 'right-0';
@endphp

<div
    x-data="{ open: false }"
    x-on:open-drawer.window="if ($event.detail.id === '{{ $id }}') open = true"
    x-on:close-drawer.window="if ($event.detail.id === '{{ $id }}') open = false"
    x-on:keydown.escape.window="open = false"
    x-cloak>

    {{-- Backdrop --}}
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-40 bg-black/40 backdrop-blur-sm"
        @if(!$static) @click="open = false" @endif
        aria-hidden="true">
    </div>

    {{-- Panel --}}
    <div
        id="{{ $id }}"
        role="dialog"
        aria-modal="true"
        aria-label="{{ $title }}"
        x-show="open"
        x-transition:enter="transition ease-out duration-250"
        x-transition:enter-start="opacity-0 {{ $slideIn }}"
        x-transition:enter-end="opacity-100 translate-x-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-x-0"
        x-transition:leave-end="opacity-0 {{ $slideIn }}"
        class="fixed top-0 {{ $position }} z-50 h-full {{ $maxW }} w-full flex flex-col bg-white shadow-2xl">

        {{-- Header --}}
        <div class="flex items-center justify-between px-5 py-4 flex-shrink-0"
             style="border-bottom:1px solid #e6e9f0">
            <div>
                @if($km)
                    <h2 class="text-sm font-black text-[#1a1f36] leading-tight">{{ $km }}</h2>
                    @if($title)
                        <p class="text-xs text-[#94a3b8] mt-0.5">{{ $title }}</p>
                    @endif
                @else
                    <h2 class="text-sm font-black text-[#1a1f36]">{{ $title }}</h2>
                @endif
            </div>
            <button type="button" @click="open = false"
                    class="w-8 h-8 flex items-center justify-center rounded-lg text-[#94a3b8]
                           hover:bg-[#f3f4f6] hover:text-[#1a1f36] transition-colors"
                    aria-label="Close">
                <i class="bi bi-x-lg text-sm"></i>
            </button>
        </div>

        {{-- Body --}}
        <div class="flex-1 overflow-y-auto p-5">
            {{ $slot }}
        </div>

        {{-- Footer --}}
        @isset($footer)
        <div class="flex items-center justify-end gap-2 px-5 py-4 flex-shrink-0"
             style="border-top:1px solid #e6e9f0;background:#f9fafb">
            {{ $footer }}
        </div>
        @endisset
    </div>
</div>
