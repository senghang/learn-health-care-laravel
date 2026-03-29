{{-- ══════════════════════════════════════════════════════════════════════════════
     FILE: resources/views/components/tw/card.blade.php
     
     Tailwind replacement for .card-emr, .card-hd, .card-bd
     
     Usage:
       <x-tw.card title="Patient Info" icon="bi-person-fill" icon-color="blue">
           Content here
       </x-tw.card>
     ══════════════════════════════════════════════════════════════════════════════ --}}
@props([
    'title' => '',
    'subtitle' => '',
    'icon' => '',
    'iconColor' => 'blue',
    'headerBg' => '',
    'sticky' => false,
])

@php
    $colorMap = [
        'blue' => 'text-blue-600', 'green' => 'text-green-600', 'red' => 'text-red-500',
        'orange' => 'text-orange-500', 'purple' => 'text-purple-600', 'cyan' => 'text-cyan-600',
    ];
    $iconClass = $colorMap[$iconColor] ?? 'text-blue-600';
@endphp

<div class="bg-white rounded-xl shadow-sm border border-blue-50 {{ $sticky ? 'sticky top-[76px]' : '' }}">
    @if($title)
    <div class="px-5 py-3 border-b border-blue-50 {{ $headerBg ?: 'bg-white' }} rounded-t-xl">
        <div class="font-bold text-sm text-blue-900 flex items-center gap-2">
            @if($icon)<i class="bi {{ $icon }} {{ $iconClass }}"></i>@endif
            {{ $title }}
            @if($subtitle)<small class="font-normal text-slate-400"> / {{ $subtitle }}</small>@endif
        </div>
    </div>
    @endif
    <div class="p-5">
        {{ $slot }}
    </div>
</div>
