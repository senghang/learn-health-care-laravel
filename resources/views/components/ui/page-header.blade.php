{{--
    Standardized page header — top of every content view.

    <x-ui.page-header
        km="អ្នកជំងឺ"
        title="Patients"
        :breadcrumbs="[
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Patients'],
        ]">
        <x-slot:actions>
            <x-ui.button href="{{ route('patients.create') }}" variant="primary">
                <x-slot:icon><i class="bi bi-person-plus-fill"></i></x-slot:icon>
                New Patient
            </x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    Bilingual hierarchy:
        km    → 24px font-black, text-primary, font-khmer  (dominant)
        title → 14px font-normal, text-muted,  font-latin  (qualifier)

    Props:
        km          — Khmer page title
        title       — English subtitle
        breadcrumbs — array of ['label', 'url'?]
        compact     — smaller bottom margin
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
            <x-ui.breadcrumbs :items="$breadcrumbs" class="mb-1.5" />
        @endif

        <div class="flex items-baseline gap-2 flex-wrap">
            @if($km)
                <h1 class="text-2xl font-black leading-tight text-[var(--text-primary)] font-khmer">
                    {{ $km }}
                </h1>
                @if($title)
                    <span class="text-sm font-normal text-[var(--text-muted)] font-latin">
                        / {{ $title }}
                    </span>
                @endif
            @else
                <h1 class="text-2xl font-black leading-tight text-[var(--text-primary)]">
                    {{ $title }}
                </h1>
            @endif
        </div>

    </div>

    @if($slot->isNotEmpty() || isset($actions))
    <div class="flex items-center gap-2 flex-shrink-0 ml-4 mt-0.5">
        @isset($actions){{ $actions }}@endisset
        {{ $slot }}
    </div>
    @endif

</div>
