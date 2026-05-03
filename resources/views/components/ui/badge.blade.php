{{--
    <x-ui.badge variant="success">Completed</x-ui.badge>
    <x-ui.badge variant="warning" dot>Pending</x-ui.badge>
    <x-ui.badge variant="critical" size="lg">Critical</x-ui.badge>

    Props:
        variant — primary|secondary|success|warning|danger|info|critical|admitted|discharged|paid|unpaid
        size    — sm|md|lg   (default: md)
        dot     — show pulsing dot indicator
--}}
@props([
    'variant' => 'secondary',
    'size'    => 'md',
    'dot'     => false,
])

@php
$variants = [
    'primary'    => 'bg-[#eef0fd] text-[#4154f1] border border-[#c7cdfb]',
    'secondary'  => 'bg-[#f1f5f9] text-[#64748b] border border-[#e2e8f0]',
    'success'    => 'bg-[#e8f8ef] text-[#16a34a] border border-[#bbf7d0]',
    'warning'    => 'bg-[#fff3e8] text-[#ea580c] border border-[#fed7aa]',
    'danger'     => 'bg-[#fef2f2] text-[#dc2626] border border-[#fecaca]',
    'info'       => 'bg-[#e0f2fe] text-[#0284c7] border border-[#bae6fd]',
    'critical'   => 'bg-[#fef2f2] text-[#dc2626] border border-[#fca5a5] animate-pulse',
    'admitted'   => 'bg-[#eff6ff] text-[#1d4ed8] border border-[#bfdbfe]',
    'discharged' => 'bg-[#f0fdf4] text-[#15803d] border border-[#bbf7d0]',
    'paid'       => 'bg-[#e8f8ef] text-[#16a34a] border border-[#86efac]',
    'unpaid'     => 'bg-[#fff7ed] text-[#c2410c] border border-[#fed7aa]',
];

$sizes = [
    'sm' => 'text-[10px] px-1.5 py-0.5 rounded gap-1',
    'md' => 'text-xs px-2 py-0.5 rounded-md gap-1.5',
    'lg' => 'text-sm px-3 py-1 rounded-lg gap-2',
];

$dotColors = [
    'primary'    => 'bg-[#4154f1]',
    'secondary'  => 'bg-[#64748b]',
    'success'    => 'bg-[#2eca6a]',
    'warning'    => 'bg-[#ff771d]',
    'danger'     => 'bg-[#ef4444]',
    'info'       => 'bg-[#0284c7]',
    'critical'   => 'bg-[#dc2626]',
    'admitted'   => 'bg-[#1d4ed8]',
    'discharged' => 'bg-[#15803d]',
    'paid'       => 'bg-[#16a34a]',
    'unpaid'     => 'bg-[#c2410c]',
];

$cls = 'inline-flex items-center font-semibold ' . ($variants[$variant] ?? $variants['secondary']) . ' ' . ($sizes[$size] ?? $sizes['md']);
$dotCls = $dotColors[$variant] ?? 'bg-gray-400';
@endphp

<span {{ $attributes->merge(['class' => $cls]) }}>
    @if($dot)
        <span class="inline-block w-1.5 h-1.5 rounded-full flex-shrink-0 {{ $dotCls }}" aria-hidden="true"></span>
    @endif
    {{ $slot }}
</span>
