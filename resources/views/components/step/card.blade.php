{{--
    <x-step.card>
        Props:
          :step-id    — e.g. 'triage'
          :visit      — VisitModel
          :step-idx   — 0-based index
          :steps      — array of all steps
          icon        — Bootstrap icon class, e.g. 'bi-heart-pulse-fill'
          :icon-color — hex, e.g. '#2eca6a'
          km          — Khmer title
          en          — English subtitle
          :badge      — optional saved-badge text (null = hide)
          :badge-color — hex for badge accent (default #2eca6a)

        Slot: form body content
--}}
@props([
    'stepId',
    'visit',
    'stepIdx',
    'steps',
    'icon'       => 'bi-clipboard-fill',
    'iconColor'  => '#4154f1',
    'km'         => '',
    'en'         => '',
    'badge'      => null,
    'badgeColor' => '#2eca6a',
])

@php
    $saveUrl     = url('/workflow/' . $visit->code . '/' . $stepId . '/save');
    $total       = count($steps);
    $pct         = $total > 0 ? round($stepIdx / $total * 100) : 0;
    $badgeBg     = $badgeColor . '22';
    $badgeBorder = $badgeColor . '66';
@endphp

{{-- ── Card Header ────────────────────────────────────────────────── --}}
<div class="card-hd" style="flex-wrap:wrap;gap:8px;padding:14px 18px 10px">
    <div style="flex:1;min-width:0">
        <div class="card-hd-title">
            <i class="bi {{ $icon }}" style="color:{{ $iconColor }}"></i>
            {{ $km }}
            <small style="font-size:11px;color:#bbb;font-weight:400">/ {{ $en }}</small>
        </div>
        <div style="font-size:10.5px;color:#aaa;margin-top:3px">
            ជំហាន {{ $stepIdx + 1 }} នៃ {{ $total }} / Step {{ $stepIdx + 1 }} of {{ $total }}
        </div>
    </div>
    @if($badge !== null)
    <span style="font-size:11px;background:{{ $badgeBg }};color:{{ $badgeColor }};padding:3px 10px;border-radius:20px;border:1px solid {{ $badgeBorder }};font-weight:700;flex-shrink:0">
        <i class="bi bi-check-circle-fill"></i> {{ $badge }}
    </span>
    @endif
    {{ $header ?? '' }}
</div>

{{-- ── Progress Bar ────────────────────────────────────────────────── --}}
<div style="height:3px;background:#f0f2ff">
    <div style="height:100%;width:{{ $pct }}%;background:linear-gradient(90deg,#4154f1,#717ff5)"></div>
</div>

{{-- ── Body ────────────────────────────────────────────────────────── --}}
<div class="card-bd">
    <form id="stepForm" method="POST" action="{{ $saveUrl }}">
        @csrf
        @method('PATCH')
        {{ $slot }}
    </form>
</div>
