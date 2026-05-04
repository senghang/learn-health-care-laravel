{{--
    Statistics KPI card — icon, value, label, optional trend and live badge.

    <x-ui.stats-card
        km="អ្នកជំងឺថ្ងៃនេះ"
        title="Today's Patients"
        value="24"
        icon="bi-people-fill"
        color="#4154f1"
        trend="up"
        trend-value="+12%"
        href="{{ route('visits.index') }}"
        :live="true"
    />

    Props:
        title|label — English label
        km          — Khmer label
        value       — display value
        icon        — Bootstrap icon class
        color       — accent hex (default: #4154f1)  — drives icon bg + value color
        bg          — explicit icon background (auto-computed from color if not set)
        trend       — up | down | neutral | null
        trendValue  — trend label e.g. '+12%'
        href        — makes card a clickable <a>
        live        — bool show live pulse badge
--}}
@props([
    'title'      => '',
    'label'      => null,
    'km'         => null,
    'value'      => '—',
    'icon'       => 'bi-bar-chart-fill',
    'color'      => '#4154f1',
    'bg'         => null,
    'trend'      => null,
    'trendValue' => null,
    'href'       => null,
    'live'       => false,
])
@php
$title = $title ?: ($label ?? '');

$trendCfg = [
    'up'      => ['icon' => 'bi-arrow-up-right',   'cls' => 'bg-emerald-50 text-emerald-600'],
    'down'    => ['icon' => 'bi-arrow-down-right',  'cls' => 'bg-red-50 text-red-600'],
    'neutral' => ['icon' => 'bi-dash',              'cls' => 'bg-slate-100 text-slate-500'],
];
$td  = $trendCfg[$trend] ?? null;
$tag = $href ? 'a' : 'div';

$cardCls = 'block rounded-xl p-5 bg-white border border-[var(--border-subtle)] shadow-sm transition-all duration-150 group '
         . ($href ? 'cursor-pointer hover:-translate-y-0.5 hover:shadow-md' : '');
@endphp

<{{ $tag }}
    @if($href) href="{{ $href }}" @endif
    {{ $attributes->merge(['class' => $cardCls]) }}>

    {{-- Icon + badges row --}}
    <div class="flex items-start justify-between mb-4">

        {{-- Icon bubble — color is a runtime prop so inline style is correct here --}}
        <div class="w-11 h-11 rounded-xl flex items-center justify-center text-lg flex-shrink-0"
             style="{{ $bg
                 ? 'background:'.$bg.';color:'.$color
                 : 'background:linear-gradient(135deg,'.$color.','.$color.'cc);color:#fff' }}">
            <i class="bi {{ $icon }}" aria-hidden="true"></i>
        </div>

        <div class="flex items-center gap-2">
            @if($live)
            <span class="flex items-center gap-1 text-xs font-bold px-2 py-0.5 rounded-full
                         bg-emerald-50 text-emerald-600">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 inline-block"
                      style="animation:livePulse 2s ease-in-out infinite" aria-hidden="true"></span>
                Live
            </span>
            @endif

            @if($td && $trendValue)
            <span class="flex items-center gap-1 text-xs font-bold px-2 py-0.5 rounded-full {{ $td['cls'] }}">
                <i class="bi {{ $td['icon'] }}" aria-hidden="true"></i>
                {{ $trendValue }}
            </span>
            @endif
        </div>
    </div>

    {{-- Value — color is a runtime prop --}}
    <div class="text-[2.25rem] font-black leading-none tracking-tight mb-1"
         style="color:{{ $color }}">
        {{ $value }}
    </div>

    {{-- Labels --}}
    <div class="text-xs font-semibold text-slate-500">{{ $title }}</div>
    @if($km)
    <div class="text-xs mt-0.5 text-slate-400 font-khmer">{{ $km }}</div>
    @endif

</{{ $tag }}>
