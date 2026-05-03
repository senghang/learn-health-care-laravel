{{--
    <x-ui.card-header title="Patients" km="អ្នកជំងឺ" icon="bi-people-fill" :count="100">
        <x-slot:actions>
            <x-ui.button size="sm" variant="primary">Add</x-ui.button>
        </x-slot:actions>
    </x-ui.card-header>

    Props:
        title     — English title
        km        — Khmer title
        icon      — Bootstrap icon class
        iconColor — icon hex color (default: #4154f1)
        count     — badge count
        compact   — smaller padding
--}}
@props([
    'title'     => null,
    'label'     => null,   // alias for title
    'km'        => null,
    'icon'      => null,
    'iconColor' => '#4154f1',
    'count'     => null,
    'compact'   => false,
])
@php $title = $title ?? $label; @endphp

<div class="flex items-center justify-between px-5 {{ $compact ? 'py-3' : 'py-4' }} {{ $attributes->get('class') }}"
     style="border-bottom:1px solid #e6e9f0">
    <div class="flex items-center gap-2">
        @if($icon)
        <i class="bi {{ $icon }}" style="color:{{ $iconColor }};font-size:15px" aria-hidden="true"></i>
        @endif
        <div>
            @if($km)
            <span class="text-sm font-bold" style="color:#1a1f36">{{ $km }}</span>
            @if($title)<span class="text-xs ml-1.5" style="color:#6b7280">/ {{ $title }}</span>@endif
            @else
            <span class="text-sm font-bold" style="color:#1a1f36">{{ $title }}</span>
            @endif
        </div>
        @if($count !== null)
        <span class="text-xs font-semibold px-2 py-0.5 rounded-full" style="background:#f1f5f9;color:#64748b">
            {{ number_format($count) }}
        </span>
        @endif
    </div>

    @if($slot->isNotEmpty() || isset($actions))
    <div class="flex items-center gap-2">
        @isset($actions){{ $actions }}@endisset
        {{ $slot }}
    </div>
    @endif
</div>
