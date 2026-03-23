@props(['visit', 'steps', 'currentStep'])

@php
    $done      = $visit->done_steps    ?? [];
    $skipped   = $visit->skipped_steps ?? [];
    $total     = count($steps);
    $doneCount = count($done);
    $skipCount = count($skipped);
    $pct       = $total > 0 ? round($doneCount / $total * 100) : 0;
@endphp

{{-- ── Progress summary bar (all screen sizes) ──────────────────────── --}}
<div class="wf-summary-bar mb-2">

    {{-- Circular progress ring (SVG) --}}
    <svg id="wfRing" class="wf-progress-ring" width="44" height="44" viewBox="0 0 44 44"
         data-ring-pct="{{ $pct }}" style="flex-shrink:0">
        <circle cx="22" cy="22" r="18" fill="none" stroke="#e2e8f0" stroke-width="3.5"/>
        <circle class="wf-progress-ring-circle"
                cx="22" cy="22" r="18"
                fill="none"
                stroke="{{ $pct === 100 ? '#2eca6a' : '#4154f1' }}"
                stroke-width="3.5"
                stroke-linecap="round"
                stroke-dasharray="{{ 2 * M_PI * 18 }}"
                stroke-dashoffset="{{ 2 * M_PI * 18 }}"
        />
        <text x="22" y="26" text-anchor="middle"
              style="font-size:10px;font-weight:800;fill:{{ $pct === 100 ? '#2eca6a' : '#4154f1' }};font-family:'Nunito',sans-serif">
            {{ $pct }}%
        </text>
    </svg>

    {{-- Stats --}}
    <div class="wf-summary-stat">
        <i class="bi bi-check-circle-fill" style="color:#2eca6a;font-size:13px"></i>
        <span style="color:#2eca6a">{{ $doneCount }}</span>
        <span>/ {{ $total }} Done</span>
    </div>

    @if($skipCount > 0)
    <div class="wf-summary-stat">
        <i class="bi bi-skip-forward-fill" style="color:#f59e0b;font-size:13px"></i>
        <span style="color:#f59e0b">{{ $skipCount }}</span>
        <span>Skipped</span>
    </div>
    @endif

    @if($pct === 100)
    <div class="wf-summary-stat ms-auto">
        <span class="status-pill status-pill--done">
            <i class="bi bi-check2-all"></i> Complete
        </span>
    </div>
    @else
    <div class="wf-summary-stat ms-auto" style="font-size:11px;color:#94a3b8">
        {{ $total - $doneCount - $skipCount }} remaining
    </div>
    @endif
</div>

{{-- ── Progress fill bar ─────────────────────────────────────────────── --}}
<div class="wf-progress-track mb-3">
    <div class="wf-progress-fill" style="width:{{ $pct }}%"></div>
</div>

{{-- ── Desktop pill strip ────────────────────────────────────────────── --}}
<div class="wf-track d-none d-lg-flex mb-3">
    @foreach($steps as $i => $step)
    @php
        $sid     = is_array($step) ? $step['id'] : $step->id();
        $skm     = is_array($step) ? $step['km'] : $step->labelKm();
        $sen     = is_array($step) ? $step['en'] : $step->labelEn();
        $sicon   = is_array($step) ? $step['icon'] : $step->icon();
        $isDone  = in_array($sid, $done);
        $isSkip  = in_array($sid, $skipped);
        $isActive= $sid === $currentStep;
        $state   = $isDone ? 'done' : ($isActive ? 'active' : ($isSkip ? 'skipped' : 'pending'));
    @endphp
    <a class="wf-pill wf-pill--{{ $state }}"
       href="{{ url('/workflow/' . $visit->code . '/' . $sid) }}"
       data-tooltip="{{ $skm }}"
       title="{{ $skm }} / {{ $sen }}">
        <div class="wf-dot wf-dot--{{ $state }}">
            @if($isDone)  <i class="bi bi-check" style="font-size:12px"></i>
            @elseif($isSkip) <i class="bi bi-skip-forward" style="font-size:10px"></i>
            @elseif($isActive) {{ $i + 1 }}
            @else {{ $i + 1 }}
            @endif
        </div>
        <span class="d-none d-xl-inline">{{ $skm }}</span>
        @if($isSkip)
            <span style="font-size:9px;opacity:.7">skip</span>
        @endif
    </a>
    @endforeach
</div>

{{-- ── Mobile accordion step selector ───────────────────────────────── --}}
<div class="step-select-bar card-emr d-lg-none mb-3">
    <div class="step-select-hd" onclick="toggleStepList()">
        @php
            $cur     = collect($steps)->first(fn($s) => (is_array($s) ? $s['id'] : $s->id()) === $currentStep);
            $curIcon = $cur ? (is_array($cur) ? $cur['icon'] : $cur->icon()) : '📋';
            $curKm   = $cur ? (is_array($cur) ? $cur['km']   : $cur->labelKm()) : '';
            $curEn   = $cur ? (is_array($cur) ? $cur['en']   : $cur->labelEn()) : '';
        @endphp
        <div>
            <div style="font-size:10.5px;color:#aaa;margin-bottom:2px">ជំហានបច្ចុប្បន្ន / Current Step</div>
            <div class="cur-step">{{ $curIcon }} {{ $curKm }} <small style="font-size:10px;color:#aaa">/ {{ $curEn }}</small></div>
        </div>
        <div style="display:flex;align-items:center;gap:8px">
            <div style="font-size:11px;color:#4154f1;font-weight:700">{{ $doneCount }}/{{ $total }}</div>
            <i class="bi bi-chevron-down" id="stepListChevron" style="transition:transform .2s;color:#aaa"></i>
        </div>
    </div>
    <div class="step-select-list" id="stepSelectList">
        @foreach($steps as $i => $step)
        @php
            $sid     = is_array($step) ? $step['id'] : $step->id();
            $skm     = is_array($step) ? $step['km'] : $step->labelKm();
            $sen     = is_array($step) ? $step['en'] : $step->labelEn();
            $sicon   = is_array($step) ? $step['icon'] : $step->icon();
            $isDone  = in_array($sid, $done);
            $isSkip  = in_array($sid, $skipped);
            $isActive= $sid === $currentStep;
        @endphp
        <a href="{{ url('/workflow/' . $visit->code . '/' . $sid) }}"
           class="step-sel-item {{ $isDone ? 'done' : '' }} {{ $isActive ? 'active-s' : '' }} {{ $isSkip ? 'skipped-s' : ($isDone ? '' : 'pend') }}">
            <div class="sel-icon">
                @if($isDone)  <i class="bi bi-check2" style="font-size:13px"></i>
                @elseif($isSkip) <i class="bi bi-skip-forward" style="font-size:11px"></i>
                @else {{ $sicon }}
                @endif
            </div>
            <div class="sel-lbl">
                <span style="font-weight:{{ $isActive ? '700' : '500' }}">{{ $skm }}</span>
                <span style="font-size:9.5px;color:#bbb"> / {{ $sen }}</span>
            </div>
            <div style="flex-shrink:0;margin-left:auto">
                @if($isDone)
                <span class="status-pill status-pill--done" style="font-size:9px;padding:1px 7px">✓</span>
                @elseif($isSkip)
                <span class="status-pill status-pill--pending" style="font-size:9px;padding:1px 7px">Skip</span>
                @elseif($isActive)
                <span class="status-pill status-pill--active" style="font-size:9px;padding:1px 7px;background:#4154f1;color:#fff;border-color:#4154f1">Current</span>
                @endif
            </div>
        </a>
        @endforeach
    </div>
</div>
