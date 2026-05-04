{{--
    Card section header — icon bubble + bilingual title + count badge + actions.

    <x-ui.card-header km="អ្នកជំងឺ" label="Patients" icon="bi-people-fill" :count="100">
        <x-slot:actions>
            <x-ui.button size="sm">Add</x-ui.button>
        </x-slot:actions>
    </x-ui.card-header>

    Bilingual rule:
        km    → 14px font-bold, text-primary, font-khmer  (dominant)
        label → 12px font-normal, text-muted, font-latin  (qualifier)

    Props:
        title|label — English label (aliases)
        km          — Khmer label
        icon        — Bootstrap icon class (bi-*)
        iconColor   — accent hex (default: #4154f1)
        count       — numeric record badge
        compact     — smaller padding
--}}
@props([
    'title'     => null,
    'label'     => null,
    'km'        => null,
    'icon'      => null,
    'iconColor' => '#4154f1',
    'count'     => null,
    'compact'   => false,
])
@php $label = $title ?? $label; @endphp

<div class="flex items-center justify-between px-5 {{ $compact ? 'py-3' : 'py-4' }}
            border-b border-[var(--border-subtle)] {{ $attributes->get('class') }}">

    {{-- Left: icon bubble + bilingual title --}}
    <div class="flex items-center gap-2.5 min-w-0">

        @if($icon)
        <div class="flex-shrink-0 flex items-center justify-center w-7 h-7 rounded-md"
             style="background:{{ $iconColor }}18">
            <i class="bi {{ $icon }} text-sm" style="color:{{ $iconColor }}" aria-hidden="true"></i>
        </div>
        @endif

        <div class="min-w-0">
            @if($km)
                <span class="text-sm font-bold block leading-tight truncate
                             text-[var(--text-primary)] font-khmer">
                    {{ $km }}
                </span>
                @if($label)
                <span class="text-xs block leading-tight text-[var(--text-muted)] font-latin">
                    {{ $label }}
                </span>
                @endif
            @elseif($label)
                <span class="text-sm font-bold block leading-tight truncate text-[var(--text-primary)]">
                    {{ $label }}
                </span>
            @endif
        </div>

        @if($count !== null)
        <span class="flex-shrink-0 text-xs font-semibold px-2 py-0.5 rounded-full
                     bg-slate-100 text-slate-500">
            {{ number_format($count) }}
        </span>
        @endif

    </div>

    {{-- Right: actions --}}
    @if($slot->isNotEmpty() || isset($actions))
    <div class="flex items-center gap-2 flex-shrink-0 ml-3">
        @isset($actions){{ $actions }}@endisset
        {{ $slot }}
    </div>
    @endif

</div>
