{{-- Visit Summary Sidebar
     Variables: $visit, $steps (array), $currentStep (string)
--}}
<div class="vss">
    <div class="vss-title">
        <i class="bi bi-person-vcard-fill" style="color:#4154f1"></i>
        សង្ខេបករណ៍ / Summary
    </div>

    <div class="vss-row">
        <div class="lbl">OPD/IPD</div>
        <div class="val">
            <span class="badge-s {{ $visit->visit_type === 'IPD' ? 'b-ipd' : 'b-opd' }}">
                {{ $visit->visit_type }}
            </span>
        </div>
    </div>

    <div class="vss-row">
        <div class="lbl">លេខ</div>
        <div class="val" style="color:#4154f1;font-family:monospace">{{ $visit->code }}</div>
    </div>

    <div class="vss-row">
        <div class="lbl">អ្នកជំងឺ</div>
        <div class="val">{{ $visit->surname }}, {{ $visit->name }}</div>
    </div>

    <div style="margin:10px 0 4px;font-size:10px;text-transform:uppercase;letter-spacing:.5px;color:#bbb;font-weight:700">
        ដំណើរការ / Progress
    </div>

    @php
        $doneCount    = count($visit->done_steps ?? []);
        $skippedCount = count($visit->skipped_steps ?? []);
        {{-- FIXED: use count($steps) instead of hardcoded 10 --}}
        $totalSteps   = count($steps);
        $pct          = $totalSteps > 0 ? round($doneCount / $totalSteps * 100) : 0;
    @endphp

    <div class="prog-bar mb-2">
        <div class="prog-fill" style="width:{{ $pct }}%"></div>
    </div>

    <div class="vss-row" style="margin-bottom:8px">
        <div class="lbl" style="font-size:11px">
            {{ $doneCount }}/{{ $totalSteps }} ជំហាន
        </div>
        <div class="val" style="font-size:11px;color:#4154f1">
            {{ $pct }}%
        </div>
    </div>

    @if($skippedCount > 0)
    <div style="display:flex;align-items:center;gap:6px;background:#fff8ee;border:1px solid #ffd080;border-radius:7px;padding:7px 10px;margin-bottom:8px;font-size:11px;color:#c97700">
        <i class="bi bi-skip-forward-fill"></i>
        <span><strong>{{ $skippedCount }}</strong> ជំហាន Skip — <span style="font-size:9.5px">ចុចជំហានដើម្បីបំពេញ</span></span>
    </div>
    @endif

    {{-- Step list --}}
    @foreach($steps as $step)
    @php
        $isDone    = in_array($step['id'], $visit->done_steps ?? []);
        $isSkipped = in_array($step['id'], $visit->skipped_steps ?? []);
        $isActive  = $step['id'] === $currentStep;
    @endphp
    <a href="{{ route('workflow.step', ['code' => $visit->code, 'step' => $step['id']]) }}"
       class="vss-step" style="text-decoration:none">
        <div class="vss-dot {{ $isDone ? 'done' : ($isActive ? 'active' : 'pend') }}"
             style="{{ $isSkipped ? 'background:#fff8ee;border:1.5px solid #ffd080;color:#c97700' : '' }}">
            {{ $isDone ? '✓' : ($isSkipped ? '⏭' : $step['icon']) }}
        </div>
        <div class="vss-sname {{ $isDone ? 'done' : ($isActive ? 'active' : '') }}"
             style="{{ $isSkipped ? 'color:#c97700' : '' }}">
            {{ $step['km'] }}
            <span style="font-size:9px;color:#ccc"> / {{ $step['en'] }}</span>
            @if($isSkipped)
            <span style="font-size:9px;background:#fff8ee;color:#c97700;padding:0 5px;border-radius:8px;margin-left:3px;border:1px solid #ffd080">⏭</span>
            @endif
        </div>
    </a>
    @endforeach
</div>
