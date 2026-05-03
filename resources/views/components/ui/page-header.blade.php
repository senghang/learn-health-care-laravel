{{--
    Standardized page header — replaces the recurring flex div pattern across all pages.

    <x-ui.page-header
        km="អ្នកជំងឺ"
        title="Patients"
        :breadcrumbs="[['label'=>'Dashboard','url'=>route('dashboard')],['label'=>'Patients']]">
        <x-slot:actions>
            <x-ui.button href="{{ route('patients.create') }}" variant="primary">
                <x-slot:icon><i class="bi bi-person-plus-fill"></i></x-slot:icon>
                New Patient
            </x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    Props:
        km          — Khmer page title
        title       — English page title (shown as subtitle if km provided)
        breadcrumbs — array of ['label', 'url'?] items
        compact     — smaller bottom margin (mb-3 vs mb-5)
--}}
@props([
    'km'          => null,
    'title'       => '',
    'breadcrumbs' => [],
    'compact'     => false,
])

<div {{ $attributes->merge(['class' => 'flex items-start justify-between ' . ($compact ? 'mb-3' : 'mb-5')]) }}>
    <div class="min-w-0">
        @if(count($breadcrumbs))
            <x-ui.breadcrumbs :items="$breadcrumbs" class="mb-1"/>
        @endif
        <h1 class="text-xl font-black text-[#1a1f36] leading-tight truncate">
            @if($km)
                {{ $km }}
                @if($title)
                    <span class="text-sm font-normal text-[#6b7280] ml-1">/ {{ $title }}</span>
                @endif
            @else
                {{ $title }}
            @endif
        </h1>
    </div>

    @if($slot->isNotEmpty() || isset($actions))
    <div class="flex items-center gap-2 flex-shrink-0 ml-4">
        @isset($actions){{ $actions }}@endisset
        {{ $slot }}
    </div>
    @endif
</div>
