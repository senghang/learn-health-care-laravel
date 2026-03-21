{{-- Visit Summary Sidebar --}}
<div class="vss">

    <div class="vss-title">
        <i class="bi bi-person-vcard-fill" style="color:#4154f1"></i>
        សង្ខេបករណ៍ / Summary
    </div>

    @php
        $visitTypeBadge = $visit->visit_type === 'IPD' ? 'b-ipd' : 'b-opd';
    @endphp

    <div class="vss-row">
        <div class="lbl">OPD/IPD</div>
        <div class="val">
            <span class="badge-s {{ $visitTypeBadge }}">{{ $visit->visit_type }}</span>
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
        $totalSteps   = count($steps);
        $pct          = $totalSteps > 0 ? round($doneCount / $totalSteps * 100) : 0;
        $pctWidth     = $pct . '%';
    @endphp

    <div class="prog-bar mb-2">
        <div class="prog-fill" style="width:{{ $pctWidth }}"></div>
    </div>

    <div class="vss-row" style="margin-bottom:8px">
        <div class="lbl" style="font-size:11px">{{ $doneCount }}/{{ $totalSteps }} ជំហាន</div>
        <div class="val" style="font-size:11px;color:#4154f1">{{ $pct }}%</div>
    </div>

    @if($skippedCount > 0)
    <div style="display:flex;align-items:center;gap:6px;background:#fff8ee;border:1px solid #ffd080;border-radius:7px;padding:7px 10px;margin-bottom:8px;font-size:11px;color:#c97700">
        <i class="bi bi-skip-forward-fill"></i>
        <span><strong>{{ $skippedCount }}</strong> ជំហាន Skip</span>
    </div>
    @endif

    @foreach($steps as $step)
    @php
        $isDone    = in_array($step['id'], $visit->done_steps ?? []);
        $isSkipped = in_array($step['id'], $visit->skipped_steps ?? []);
        $isActive  = $step['id'] === $currentStep;

        $dotClass  = 'pend';
        if ($isDone)    $dotClass = 'done';
        if ($isActive)  $dotClass = 'active';

        $dotStyle  = $isSkipped ? 'background:#fff8ee;border:1.5px solid #ffd080;color:#c97700' : '';
        $nameClass = '';
        if ($isDone)   $nameClass = 'done';
        if ($isActive) $nameClass = 'active';
        $nameStyle = $isSkipped ? 'color:#c97700' : '';

        $dotIcon = '⏭';
        if ($isDone)        $dotIcon = '✓';
        elseif (!$isSkipped) $dotIcon = $step['icon'];

        $stepUrl = url('/workflow/' . $visit->code . '/' . $step['id']);
    @endphp

    <a href="{{ $stepUrl }}" class="vss-step" style="text-decoration:none">
        <div class="vss-dot {{ $dotClass }}" style="{{ $dotStyle }}">
            {{ $dotIcon }}
        </div>
        <div class="vss-sname {{ $nameClass }}" style="{{ $nameStyle }}">
            {{ $step['km'] }}
            <span style="font-size:9px;color:#ccc"> / {{ $step['en'] }}</span>
            @if($isSkipped)
                <span style="font-size:9px;background:#fff8ee;color:#c97700;padding:0 5px;border-radius:8px;margin-left:3px;border:1px solid #ffd080">⏭</span>
            @endif
        </div>
    </a>
    @endforeach

</div>
