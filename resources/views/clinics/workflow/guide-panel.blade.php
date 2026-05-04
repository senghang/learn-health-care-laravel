{{--
    Reusable Guide Panel
    @param $steps     — array of WorkflowStepStrategy objects (or compatible)
    @param $visit     — VisitModel|null (null = new visit / create mode)
    @param $currentStep — string|null (current step id when $visit exists)
--}}
<div class="card-emr" style="position:sticky;top:76px">
    <div class="card-hd" style="background:#f6f8fa">
        <div class="card-hd-title">
            <i class="bi bi-map-fill" style="color:#4154f1"></i>
            ប្រព័ន្ធណែនាំ
            <small style="font-size:11px;color:#aaa;font-weight:400">/ Workflow Guide</small>
        </div>
    </div>
    <div class="card-bd" style="padding:0">

        {{-- Skip tip --}}
        <div style="padding:14px 16px;background:#fffbee;border-bottom:1px solid #fff0c0">
            <div style="display:flex;align-items:flex-start;gap:8px">
                <span style="font-size:18px;flex-shrink:0">💡</span>
                <div>
                    <div style="font-size:12.5px;font-weight:700;color:#b07800;margin-bottom:2px">
                        អ្នកអាចលុបចោល (Skip) ជំហានណាមួយ!
                    </div>
                    <div style="font-size:11px;color:#c97700;line-height:1.5">
                        Skip ជំហានដែលមិនចាំបាច់ ហើយត្រឡប់មកបំពេញពេលក្រោយ។<br>
                        <span style="color:#d4920a">You can skip any step and return later.</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Step List --}}
        <div style="padding:8px 0">
            @foreach($steps as $i => $step)
                @php
                    $visit = null;
                    $isDone    = $visit && in_array($step->id(), $visit->done_steps ?? []);
                    $isSkipped = $visit && in_array($step->id(), $visit->skipped_steps ?? []);
                    $isActive  = $visit && ($currentStep ?? null) === $step->id();

                    $dotBg     = '#e6eaf5';
                    $dotColor  = '#aaa';
                    $dotContent = $step->icon ?? '📋';

                    $nameColor = '#555';
                    $nameFw    = '400';
                    $badge     = '';
                    $rowBg     = 'transparent';

                    if ($isDone) {
                        $dotBg     = '#4154f1';
                        $dotColor  = '#fff';
                        $dotContent = '✓';
                        $nameColor = '#4154f1';
                        $nameFw    = '700';
                    } elseif ($isSkipped) {
                        $dotBg     = '#fff8ee';
                        $dotColor  = '#c97700';
                        $dotContent = '⏭';
                        $nameColor = '#c97700';
                        $nameFw    = '600';
                        $rowBg     = '#fffdf5';
                    } elseif ($isActive) {
                        $dotBg     = '#ff771d';
                        $dotColor  = '#fff';
                        $dotContent = '▶';
                        $nameColor = '#ff771d';
                        $nameFw    = '700';
                    }
                @endphp

                @if($visit)
                    <a href="{{ route('workflow.step', [$visit->code, $step->id()]) }}"
                       style="display:flex;align-items:center;gap:10px;padding:9px 16px;border-bottom:1px solid #f5f6ff;text-decoration:none;background:{{ $rowBg }};transition:background .15s"
                       onmouseover="this.style.background='#f6f8fa'"
                       onmouseout="this.style.background='{{ $rowBg }}'">
                        @else
                            <div
                                style="display:flex;align-items:center;gap:10px;padding:9px 16px;border-bottom:1px solid #f5f6ff;text-decoration:none;background:{{ $rowBg }};transition:background .15s"
                                onmouseover="this.style.background='#f6f8fa'"
                                onmouseout="this.style.background='{{ $rowBg }}'">
                                @endif

                                <div
                                    style="width:28px;height:28px;border-radius:50%;background:{{ $dotBg }};color:{{ $dotColor }};display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:800;flex-shrink:0;{{ $isSkipped ? 'border:1.5px solid #ffd080' : '' }}">
                                    {{ $dotContent }}
                                </div>

                                <div style="flex:1;min-width:0">
                                    <div
                                        style="font-size:12.5px;font-weight:{{ $nameFw }};color:{{ $nameColor }};white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                                        {{ $i+1 }}. {{ $step->labelKm() ?? $step->km() ?? '—' }}
                                        <span
                                            style="font-size:10px;color:#ccc;font-weight:400">/ {{ $step->labelEn() ?? $step->en ?? '—' }}</span>
                                    </div>
                                    <div style="font-size:10.5px;color:#bbb;margin-top:1px">
                                        {{ $step->desc ?? $step->description ?? '—' }}
                                    </div>
                                    @if($isSkipped && $visit)
                                        <div style="font-size:10px;color:#c97700;margin-top:2px">
                                            ← ចុចដើម្បីបំពេញ / Click to complete
                                        </div>
                                    @endif
                                </div>

                                @if($isDone)
                                    <i class="bi bi-check-circle-fill"
                                       style="color:#4154f1;font-size:14px;flex-shrink:0"></i>
                                @elseif($isSkipped)
                                    <span
                                        style="font-size:9px;background:#fff8ee;color:#c97700;padding:1px 8px;border-radius:10px;font-weight:700;flex-shrink:0;border:1px solid #ffd080">SKIPPED</span>
                                @elseif(!$isActive && !$isDone && !$isSkipped)
                                    <span
                                        style="font-size:9px;background:#e6e9f0;color:#9b59b6;padding:1px 8px;border-radius:10px;font-weight:700;flex-shrink:0">Optional</span>
                        @endif

                        @if($visit)
                    </a>
                @else
        </div>
        @endif
        @endforeach
    </div>

    {{-- Legend --}}
    <div style="padding:12px 16px;border-top:1px solid #e6e9f0;background:#f9fafb">
        <div
            style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#aaa;margin-bottom:8px">
            សញ្ញា / Legend
        </div>
        <div style="display:flex;flex-direction:column;gap:5px">
            <div style="display:flex;align-items:center;gap:7px;font-size:11px;color:#555">
                <span
                    style="width:16px;height:16px;border-radius:50%;background:#4154f1;display:flex;align-items:center;justify-content:center;color:#fff;font-size:8px;font-weight:800;flex-shrink:0">✓</span>
                ជំហានបានបំពេញ / Completed
            </div>
            <div style="display:flex;align-items:center;gap:7px;font-size:11px;color:#555">
                <span
                    style="width:16px;height:16px;border-radius:50%;background:#ff771d;display:flex;align-items:center;justify-content:center;color:#fff;font-size:9px;flex-shrink:0">▶</span>
                ជំហានបច្ចុប្បន្ន / Current
            </div>
            <div style="display:flex;align-items:center;gap:7px;font-size:11px;color:#555">
                <span
                    style="width:16px;height:16px;border-radius:50%;background:#e6eaf5;display:inline-block;flex-shrink:0"></span>
                ជំហាននៅខាងមុខ / Upcoming
            </div>
            <div style="display:flex;align-items:center;gap:7px;font-size:11px;color:#555">
                <span
                    style="width:16px;height:16px;border-radius:4px;background:#fff8ee;border:1px solid #ffd080;display:inline-block;flex-shrink:0"></span>
                ជំហាន Skip / Skipped
            </div>
        </div>
    </div>
</div>
