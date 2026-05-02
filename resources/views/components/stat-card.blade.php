{{--
    Reusable KPI stat card.
    Usage:
      <div class="col-6 col-xl-3">
          <x-stat-card :value="$total" icon="bi-people-fill"
              color="#4154f1" bg="#eef0fd"
              km="បុគ្គលិករួម" en="Total Employees" />
      </div>

    Props:
      value   — number or string to display
      icon    — Bootstrap icon class
      color   — icon / number color
      bg      — icon background color
      km      — Khmer label
      en      — English label (shown below km)
      trend   — optional trend text e.g. '+12% this month'
      trend-up — bool, green if true, red if false (default null = neutral)
--}}
@props([
    'value'   => 0,
    'icon'    => 'bi-circle',
    'color'   => '#4154f1',
    'bg'      => '#eef0fd',
    'km'      => '',
    'en'      => '',
    'trend'   => null,
    'trendUp' => null,
])

@php
    $trendColor = $trendUp === null ? '#aaa' : ($trendUp ? '#2eca6a' : '#e74c3c');
    $trendIcon  = $trendUp === null ? '' : ($trendUp ? 'bi-arrow-up-short' : 'bi-arrow-down-short');
@endphp

<div class="stat-card">
    <div class="stat-icon" style="background:{{ $bg }};color:{{ $color }}">
        <i class="bi {{ $icon }}"></i>
    </div>
    <div>
        <div class="stat-num" style="color:{{ $color }}">{{ $value }}</div>
        <div class="stat-lbl">
            @if($km){{ $km }}@endif
            @if($km && $en)<br>@endif
            @if($en)<small>{{ $en }}</small>@endif
        </div>
        @if($trend)
            <div class="stat-trend" style="color:{{ $trendColor }}">
                @if($trendIcon)<i class="bi {{ $trendIcon }}"></i>@endif
                {{ $trend }}
            </div>
        @endif
    </div>
</div>
