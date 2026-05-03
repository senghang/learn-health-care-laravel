{{--
    <x-ui.stats-card
        title="Today's Patients"
        km="អ្នកជំងឺថ្ងៃនេះ"
        value="24"
        icon="bi-people-fill"
        color="#4154f1"
        trend="up"
        trend-value="+12%"
        href="{{ route('visits.index') }}"
    />

    Props:
        title      — English label
        km         — Khmer label
        value      — display value (number or string)
        icon       — Bootstrap icon class
        color      — accent hex color (default: #4154f1)
        trend      — up|down|neutral|null   (default: null, hides trend)
        trendValue — trend label e.g. '+12%' or '3 more'
        href       — make card clickable
        live       — bool show live pulse indicator
--}}
@props([
    'title'      => '',
    'label'      => null,    // alias for title
    'km'         => null,
    'value'      => '—',
    'icon'       => 'bi-bar-chart-fill',
    'color'      => '#4154f1',
    'bg'         => null,    // icon background color (auto-computed if not set)
    'trend'      => null,
    'trendValue' => null,
    'href'       => null,
    'live'       => false,
])
@php $title = $title ?: ($label ?? ''); @endphp

@php
$trendCfg = [
    'up'      => ['icon' => 'bi-arrow-up-right', 'cls' => 'text-[#16a34a]', 'bg' => '#e8f8ef'],
    'down'    => ['icon' => 'bi-arrow-down-right','cls' => 'text-[#dc2626]', 'bg' => '#fef2f2'],
    'neutral' => ['icon' => 'bi-dash',            'cls' => 'text-[#64748b]', 'bg' => '#f1f5f9'],
];
$td = $trendCfg[$trend] ?? null;
$lightBg = $color . '18';
$tag = $href ? 'a' : 'div';
$baseClass = 'block rounded-xl p-5 bg-white border border-[#e6e9f0] transition-all duration-150 group ' .
             ($href ? 'cursor-pointer hover:-translate-y-0.5 hover:shadow-md' : '');
@endphp

<{{ $tag }}
    @if($href) href="{{ $href }}" @endif
    {{ $attributes->merge(['class' => $baseClass]) }}
    style="box-shadow:0 1px 2px rgba(17,24,39,.05),0 4px 12px rgba(17,24,39,.04)">

    {{-- Icon + live badge --}}
    <div class="flex items-start justify-between mb-4">
        <div class="w-11 h-11 rounded-xl flex items-center justify-center text-lg flex-shrink-0"
             style="{{ $bg ? 'background:'.$bg.';color:'.$color : 'background:linear-gradient(135deg,'.$color.','.$color.'cc);color:#fff' }}">
            <i class="bi {{ $icon }}" aria-hidden="true"></i>
        </div>
        <div class="flex items-center gap-2">
            @if($live)
            <span class="flex items-center gap-1 text-xs font-bold px-2 py-0.5 rounded-full"
                  style="background:#e8f8ef;color:#2eca6a">
                <span class="w-1.5 h-1.5 rounded-full bg-[#2eca6a] inline-block"
                      style="animation:livePulse 2s ease-in-out infinite" aria-hidden="true"></span>
                Live
            </span>
            @endif
            @if($td && $trendValue)
            <span class="flex items-center gap-1 text-xs font-bold px-2 py-0.5 rounded-full {{ $td['cls'] }}"
                  style="background:{{ $td['bg'] }}">
                <i class="bi {{ $td['icon'] }}" aria-hidden="true"></i>
                {{ $trendValue }}
            </span>
            @endif
        </div>
    </div>

    {{-- Value --}}
    <div class="text-[2.25rem] font-black leading-none tracking-tight mb-1"
         style="color:{{ $color }}">
        {{ $value }}
    </div>

    {{-- Label --}}
    <div class="text-xs font-semibold" style="color:#6b7280">{{ $title }}</div>
    @if($km)
    <div class="text-xs mt-0.5" style="color:#9ca3af">{{ $km }}</div>
    @endif

</{{ $tag }}>
