{{-- ══════════════════════════════════════════════════════════════════════════════
     FILE: resources/views/components/tw/stat.blade.php
     
     Tailwind replacement for .stat-card
     
     Usage:
       <x-tw.stat label-km="អ្នកជំងឺថ្មី" label-en="New Patients" :value="42" icon="bi-people-fill" color="blue" />
     ══════════════════════════════════════════════════════════════════════════════ --}}
@props([
    'labelKm' => '',
    'labelEn' => '',
    'value' => '0',
    'icon' => 'bi-bar-chart-fill',
    'color' => 'blue',
])

@php
    $colors = [
        'blue'   => ['bg' => 'bg-blue-50',   'text' => 'text-blue-600',   'ring' => 'ring-blue-200'],
        'green'  => ['bg' => 'bg-green-50',  'text' => 'text-green-600',  'ring' => 'ring-green-200'],
        'red'    => ['bg' => 'bg-red-50',    'text' => 'text-red-500',    'ring' => 'ring-red-200'],
        'orange' => ['bg' => 'bg-orange-50', 'text' => 'text-orange-500', 'ring' => 'ring-orange-200'],
        'purple' => ['bg' => 'bg-purple-50', 'text' => 'text-purple-600', 'ring' => 'ring-purple-200'],
        'cyan'   => ['bg' => 'bg-cyan-50',   'text' => 'text-cyan-600',   'ring' => 'ring-cyan-200'],
    ];
    $c = $colors[$color] ?? $colors['blue'];
@endphp

<div class="bg-white rounded-xl shadow-sm border border-blue-50 p-4 flex items-center gap-3">
    <div class="w-11 h-11 rounded-xl {{ $c['bg'] }} {{ $c['text'] }} flex items-center justify-center text-lg flex-shrink-0">
        <i class="bi {{ $icon }}"></i>
    </div>
    <div>
        <div class="font-extrabold text-xl {{ $c['text'] }} leading-tight">{{ $value }}</div>
        <div class="text-xs text-slate-500 leading-tight">
            {{ $labelKm }}
            @if($labelEn)<br><span class="text-slate-400">{{ $labelEn }}</span>@endif
        </div>
    </div>
</div>


{{-- ══════════════════════════════════════════════════════════════════════════════
     FILE: resources/views/components/tw/badge.blade.php
     
     Usage:
       <x-tw.badge color="green">Active</x-tw.badge>
       <x-tw.badge color="red" dot>Overdue</x-tw.badge>
     ══════════════════════════════════════════════════════════════════════════════ --}}
{{--
@props(['color' => 'blue', 'dot' => false])

@php
    $styles = [
        'blue'   => 'bg-blue-100 text-blue-700',
        'green'  => 'bg-green-100 text-green-700',
        'red'    => 'bg-red-100 text-red-700',
        'orange' => 'bg-orange-100 text-orange-700',
        'purple' => 'bg-purple-100 text-purple-700',
        'gray'   => 'bg-slate-100 text-slate-500',
        'cyan'   => 'bg-cyan-100 text-cyan-700',
        'amber'  => 'bg-amber-100 text-amber-700',
    ];
    $dotColors = [
        'blue' => 'bg-blue-500', 'green' => 'bg-green-500', 'red' => 'bg-red-500',
        'orange' => 'bg-orange-500', 'purple' => 'bg-purple-500', 'gray' => 'bg-slate-400',
    ];
@endphp

<span class="inline-flex items-center gap-1 text-xs font-semibold px-2 py-0.5 rounded-full {{ $styles[$color] ?? $styles['blue'] }}">
    @if($dot)
        <span class="w-1.5 h-1.5 rounded-full {{ $dotColors[$color] ?? 'bg-blue-500' }}"></span>
    @endif
    {{ $slot }}
</span>
--}}


{{-- ══════════════════════════════════════════════════════════════════════════════
     FILE: resources/views/components/tw/note.blade.php
     
     Tailwind replacement for .note .note-info / .note-danger / .note-success
     
     Usage:
       <x-tw.note type="info">Some helpful info</x-tw.note>
       <x-tw.note type="danger">Error occurred!</x-tw.note>
       <x-tw.note type="success">Saved successfully</x-tw.note>
     ══════════════════════════════════════════════════════════════════════════════ --}}
{{--
@props(['type' => 'info'])

@php
    $styles = [
        'info'    => ['bg' => 'bg-blue-50 border-blue-200 text-blue-700',    'icon' => 'bi-info-circle-fill'],
        'success' => ['bg' => 'bg-green-50 border-green-200 text-green-700', 'icon' => 'bi-check-circle-fill'],
        'danger'  => ['bg' => 'bg-red-50 border-red-200 text-red-700',       'icon' => 'bi-exclamation-triangle-fill'],
        'warning' => ['bg' => 'bg-amber-50 border-amber-200 text-amber-700', 'icon' => 'bi-exclamation-circle-fill'],
    ];
    $s = $styles[$type] ?? $styles['info'];
@endphp

<div class="flex items-start gap-2 border px-4 py-3 rounded-lg text-sm {{ $s['bg'] }}">
    <i class="bi {{ $s['icon'] }} flex-shrink-0 mt-0.5"></i>
    <div>{{ $slot }}</div>
</div>
--}}
