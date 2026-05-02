{{-- Visit Summary Sidebar --}}
@php
    $visitTypeBadge = $visit->visit_type === 'IPD' ? 'b-ipd' : 'b-opd';

    // Patient demographics
    $dob = $visit->date_of_birth ?? $visit->dob ?? null;
    $age = null;
    if ($dob) { try { $age = \Carbon\Carbon::parse($dob)->age; } catch (\Throwable $e) {} }
    $sex = $visit->sex ?? $visit->gender ?? null;

    // Triage
    $triage        = optional($visit->triage);
    $triageLevel   = $triage->triage_level ?? null;
    $allergies     = $triage->allergies    ?? null;
    $chiefComplaint= $triage->chief_complaint ?? null;
    $triageCols    = ['Emergency'=>['#e74c3c','#fde8e8'],'Urgent'=>['#ff771d','#fff3e8'],'Standard'=>['#4154f1','#eef0fd'],'Low'=>['#64748b','#f1f5f9']];
    [$tCol,$tBg]   = $triageCols[$triageLevel] ?? ['#94a3b8','#f1f5f9'];

    // Latest vitals
    $latestV    = \App\Models\VitalSignModel::where('visit_code', $visit->code)
        ->with('observations')->latest()->first();
    $vObs       = $latestV ? $latestV->observations->pluck('value','name') : collect();

    // Progress
    $doneCount    = count($visit->done_steps ?? []);
    $skippedCount = count($visit->skipped_steps ?? []);
    $totalSteps   = count($steps);
    $pct          = $totalSteps > 0 ? round($doneCount / $totalSteps * 100) : 0;
    $pctWidth     = $pct . '%';
@endphp

<div class="vss">

    <div class="vss-title">
        <i class="bi bi-person-vcard-fill" style="color:#4154f1"></i>
        សង្ខេបករណ៍ / Summary
    </div>

    {{-- Patient identity block --}}
    <div style="background:#f6f9ff;border-radius:10px;padding:10px 12px;margin-bottom:10px;border:1px solid #f0f2ff">
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px">
            <div style="width:32px;height:32px;border-radius:8px;
                        background:{{ $visit->visit_type==='IPD' ? '#fff3e8' : '#eef0fd' }};
                        color:{{ $visit->visit_type==='IPD' ? '#ff771d' : '#4154f1' }};
                        display:flex;align-items:center;justify-content:center;
                        font-size:13px;font-weight:900;flex-shrink:0">
                {{ strtoupper(substr($visit->surname ?? '?', 0, 1)) }}
            </div>
            <div style="min-width:0">
                <div style="font-size:13px;font-weight:800;color:#012970;line-height:1.2;
                            overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                    {{ $visit->surname }}, {{ $visit->name }}
                </div>
                <div style="font-size:10px;color:#aaa;margin-top:1px;font-family:monospace">
                    {{ $visit->code }}
                </div>
            </div>
        </div>
        <div style="display:flex;gap:5px;flex-wrap:wrap;align-items:center">
            <span class="badge-s {{ $visitTypeBadge }}" style="font-size:9.5px">{{ $visit->visit_type }}</span>
            @if($age)
            <span style="font-size:9.5px;color:#64748b;background:#f0f2ff;padding:1px 7px;border-radius:8px">{{ $age }}y</span>
            @endif
            @if($sex)
            <span style="font-size:9.5px;color:{{ $sex==='F'||strtolower($sex)==='female'?'#e91e8c':'#4154f1' }};
                         background:{{ $sex==='F'||strtolower($sex)==='female'?'#fce8f3':'#eef0fd' }};
                         padding:1px 7px;border-radius:8px;font-weight:700">
                {{ strtoupper(substr($sex,0,1)) }}
            </span>
            @endif
        </div>
    </div>

    {{-- Triage level --}}
    @if($triageLevel)
    <div style="display:flex;align-items:center;gap:6px;background:{{ $tBg }};border:1px solid {{ $tCol }}33;
                border-radius:8px;padding:7px 10px;margin-bottom:8px">
        <i class="bi bi-shield-exclamation" style="color:{{ $tCol }};font-size:13px"></i>
        <div>
            <div style="font-size:11px;font-weight:700;color:{{ $tCol }}">{{ $triageLevel }}</div>
            @if($chiefComplaint)
            <div style="font-size:9.5px;color:#94a3b8;margin-top:1px;line-height:1.3">
                {{ \Illuminate\Support\Str::limit($chiefComplaint, 60) }}
            </div>
            @endif
        </div>
    </div>
    @endif

    {{-- Allergy alert --}}
    @if($allergies)
    <div style="display:flex;align-items:flex-start;gap:6px;background:#fde8e8;border:1px solid #e74c3c33;
                border-radius:8px;padding:7px 10px;margin-bottom:8px">
        <i class="bi bi-exclamation-triangle-fill" style="color:#e74c3c;font-size:12px;margin-top:1px;flex-shrink:0"></i>
        <div style="font-size:10.5px;color:#e74c3c;font-weight:600;line-height:1.3">
            Allergy: {{ \Illuminate\Support\Str::limit($allergies, 50) }}
        </div>
    </div>
    @endif

    {{-- Latest vitals snapshot --}}
    @if($vObs->isNotEmpty())
    <div style="margin:10px 0 4px;font-size:10px;text-transform:uppercase;letter-spacing:.5px;color:#bbb;font-weight:700">
        <i class="bi bi-heart-pulse-fill" style="color:#e74c3c"></i> Latest Vitals
        @if($latestV?->recorded_at)
        <span style="font-weight:400;text-transform:none;color:#cbd5e1;margin-left:3px">
            {{ $latestV->recorded_at->format('H:i') }}
        </span>
        @endif
    </div>
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:4px;margin-bottom:10px">
        @php
        $vitalSnap = [
            ['temperature',         'T°',   '°C',    '#e74c3c'],
            ['heart_rate',          'HR',   'bpm',   '#e91e8c'],
            ['oxygen_saturation',   'SpO₂', '%',     '#2eca6a'],
            ['blood_pressure_systolic','SBP','mmHg',  '#9b59b6'],
            ['respiratory_rate',    'RR',   '/min',  '#00bcd4'],
            ['blood_glucose',       'Gluc', 'mmol/L','#3498db'],
        ];
        @endphp
        @foreach($vitalSnap as [$key,$lbl,$unit,$col])
        @if($vObs->has($key))
        <div style="background:#fff;border:1px solid {{ $col }}22;border-radius:7px;padding:4px 6px;text-align:center">
            <div style="font-size:8.5px;color:#94a3b8;font-weight:700">{{ $lbl }}</div>
            <div style="font-size:12px;font-weight:800;color:{{ $col }}">{{ $vObs->get($key) }}</div>
            <div style="font-size:7.5px;color:#cbd5e1">{{ $unit }}</div>
        </div>
        @endif
        @endforeach
    </div>
    @endif

    {{-- Progress --}}
    <div style="margin:10px 0 4px;font-size:10px;text-transform:uppercase;letter-spacing:.5px;color:#bbb;font-weight:700">
        ដំណើរការ / Progress
    </div>

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

    {{-- ── IPD Admission Panel (IPD visits only) ──────────────────── --}}
    @if($visit->visit_type === 'IPD')
    @php
        $activeAdmission = \App\Models\AdmissionModel::where('visit_code', $visit->code)
            ->whereIn('status', ['admitted'])
            ->first();
    @endphp
    <div style="margin:10px 0 4px;font-size:10px;text-transform:uppercase;letter-spacing:.5px;color:#bbb;font-weight:700">
        🏥 IPD Admission
    </div>
    @if($activeAdmission)
        {{-- Already admitted — show badge + link --}}
        <div style="background:rgba(46,202,106,.12);border:1px solid rgba(46,202,106,.35);border-radius:10px;padding:10px 12px;margin-bottom:8px">
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px">
                <span style="font-size:10px;background:#2eca6a;color:#fff;padding:2px 9px;border-radius:6px;font-weight:700">
                    <i class="bi bi-check-circle-fill"></i> Admitted
                </span>
                <code style="font-size:10px;color:#2eca6a">{{ $activeAdmission->code }}</code>
            </div>
            @if($activeAdmission->ward || $activeAdmission->bed)
            <div style="font-size:11px;color:#b8c7e0;margin-bottom:2px">
                <i class="bi bi-building"></i>
                {{ $activeAdmission->ward?->name ?? '—' }}
                @if($activeAdmission->bed) · <i class="bi bi-hospital"></i> {{ $activeAdmission->bed->name }}@endif
            </div>
            @endif
            <div style="font-size:11px;color:#b8c7e0;margin-bottom:8px">
                <i class="bi bi-calendar3"></i> {{ $activeAdmission->admitted_at?->format('d M Y H:i') }}
            </div>
            <a href="{{ route('admissions.show', $activeAdmission->code) }}"
               style="display:flex;align-items:center;justify-content:center;gap:6px;background:rgba(46,202,106,.2);color:#2eca6a;border:1px solid rgba(46,202,106,.4);font-size:11px;padding:5px 10px;border-radius:7px;text-decoration:none;font-weight:600">
                <i class="bi bi-box-arrow-up-right"></i> View Admission
            </a>
        </div>
    @else
        {{-- Not admitted — show "Admit Patient" button that opens popup modal --}}
        @php
            $admitWards = \App\Models\WardModel::where('is_active', true)->orderBy('name')->get(['id','name']);
            $admitBeds  = \App\Models\BedModel::where('status','available')
                ->orderBy('ward_id')->orderBy('name')
                ->get(['id','name','ward_id','code']);
        @endphp
        <div style="background:rgba(255,160,50,.08);border:1px solid rgba(255,160,50,.25);border-radius:10px;padding:10px 12px;margin-bottom:8px">
            <div style="display:flex;align-items:center;gap:6px;font-size:11px;color:#c97700;margin-bottom:8px">
                <i class="bi bi-exclamation-circle-fill"></i>
                Not yet admitted. Assign a bed to begin IPD stay.
            </div>
            <button type="button"
                    onclick="openModal('admitPatientModal')"
                    style="display:flex;align-items:center;justify-content:center;gap:6px;width:100%;background:linear-gradient(135deg,#4154f1,#2f42d9);color:#fff;border:none;border-radius:8px;padding:7px 12px;font-size:12px;font-weight:700;cursor:pointer;box-shadow:0 3px 12px rgba(65,84,241,.35)">
                <i class="bi bi-hospital-fill"></i> Admit Patient
            </button>
        </div>

        {{-- ── Admit Patient Modal (using shared emr-modal-wrap system) ── --}}
        <div id="admitPatientModal" class="emr-modal-wrap" onclick="if(event.target===this)closeModal('admitPatientModal')">
            <div class="emr-modal modal-lg">

                {{-- Modal Header --}}
                <div class="emr-modal-hd">
                    <i class="bi bi-hospital-fill" style="color:#4154f1;font-size:16px;flex-shrink:0"></i>
                    <div class="emr-modal-title">
                        Admit Patient
                        <span style="font-size:11px;font-weight:400;color:#aaa;margin-left:8px">
                            {{ $visit->surname }}, {{ $visit->name }} · <code style="color:#4154f1">{{ $visit->code }}</code>
                        </span>
                    </div>
                    <button type="button" class="emr-modal-close" onclick="closeModal('admitPatientModal')">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>

                {{-- Modal Body --}}
                <form method="POST" action="{{ route('admissions.admit', $visit->code) }}" id="admitForm">
                    @csrf
                    <div class="emr-modal-body" style="overflow-y:auto">

                        {{-- Bed Assignment --}}
                        <div style="background:#f6f8ff;border:1px solid #e4e8ff;border-radius:10px;padding:14px;margin-bottom:16px">
                            <div style="font-size:11px;font-weight:700;color:#4154f1;text-transform:uppercase;letter-spacing:.5px;margin-bottom:12px">
                                <i class="bi bi-building-fill"></i> Ward & Bed Assignment
                            </div>
                            <div class="row g-2">
                                <div class="col-6">
                                    <label style="font-size:11px;font-weight:600;color:#555;display:block;margin-bottom:4px">Ward</label>
                                    <select name="ward_id" id="admitModalWardSel" class="form-select form-select-sm"
                                            onchange="filterAdmitModalBeds(this.value)">
                                        <option value="">— Select Ward —</option>
                                        @foreach($admitWards as $w)
                                        <option value="{{ $w->id }}">{{ $w->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label style="font-size:11px;font-weight:600;color:#555;display:block;margin-bottom:4px">
                                        Bed <span style="color:#e74c3c">*</span>
                                    </label>
                                    <select name="bed_id" id="admitModalBedSel" class="form-select form-select-sm">
                                        <option value="">— Select Bed —</option>
                                        @foreach($admitBeds as $b)
                                        <option value="{{ $b->id }}" data-ward="{{ $b->ward_id }}">{{ $b->name }} ({{ $b->code }})</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- Admission Details --}}
                        <div class="row g-2">
                            <div class="col-12 col-sm-6">
                                <label style="font-size:11px;font-weight:600;color:#555;display:block;margin-bottom:4px">Admission Type</label>
                                <select name="admission_type" class="form-select form-select-sm">
                                    <option value="">— Select —</option>
                                    <option value="Emergency">🚨 Emergency</option>
                                    <option value="Elective">📅 Elective</option>
                                    <option value="Maternity">🤱 Maternity</option>
                                    <option value="Surgical">🔪 Surgical</option>
                                    <option value="Medical">🏥 Medical</option>
                                </select>
                            </div>
                            <div class="col-12 col-sm-6">
                                <label style="font-size:11px;font-weight:600;color:#555;display:block;margin-bottom:4px">Admitted At</label>
                                <input type="datetime-local" name="admitted_at" class="form-control form-control-sm"
                                       value="{{ now()->format('Y-m-d\TH:i') }}"/>
                            </div>
                            <div class="col-12 col-sm-6">
                                <label style="font-size:11px;font-weight:600;color:#555;display:block;margin-bottom:4px">Attending Doctor</label>
                                <input type="text" name="attending_doctor" class="form-control form-control-sm"
                                       placeholder="Dr. Name"/>
                            </div>
                            <div class="col-12 col-sm-6">
                                <label style="font-size:11px;font-weight:600;color:#555;display:block;margin-bottom:4px">Admitting Doctor</label>
                                <input type="text" name="admitting_doctor" class="form-control form-control-sm"
                                       placeholder="Dr. Name"/>
                            </div>
                            <div class="col-12 col-sm-6">
                                <label style="font-size:11px;font-weight:600;color:#555;display:block;margin-bottom:4px">Primary Nurse</label>
                                <input type="text" name="primary_nurse" class="form-control form-control-sm"
                                       placeholder="Nurse name"/>
                            </div>
                            <div class="col-12 col-sm-6">
                                <label style="font-size:11px;font-weight:600;color:#555;display:block;margin-bottom:4px">Expected Discharge</label>
                                <input type="date" name="expected_discharge_at" class="form-control form-control-sm"/>
                            </div>
                            <div class="col-12">
                                <label style="font-size:11px;font-weight:600;color:#555;display:block;margin-bottom:4px">Admission Reason</label>
                                <textarea name="admission_reason" class="form-control form-control-sm" rows="2"
                                          placeholder="Chief complaint / reason for admission…" style="resize:vertical"></textarea>
                            </div>
                        </div>

                        @if($admitBeds->isEmpty())
                        <div style="background:#fff8ee;border:1px solid #ffd080;border-radius:8px;padding:10px 12px;margin-top:12px;font-size:12px;color:#c97700;display:flex;align-items:center;gap:8px">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                            <span>No available beds at this time. Please free a bed first.</span>
                        </div>
                        @endif

                    </div>

                    {{-- Modal Footer --}}
                    <div class="emr-modal-ft">
                        <button type="button" onclick="closeModal('admitPatientModal')" class="btn btn-sm btn-outline-secondary">
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-sm btn-primary"
                                data-confirm="Admit {{ $visit->surname }}, {{ $visit->name }} as an inpatient? A bed will be assigned and an IPD record will be created."
                                data-confirm-type="info" data-confirm-title="Confirm Admission">
                            <i class="bi bi-check-circle-fill"></i> Confirm Admission
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <script>
        function filterAdmitModalBeds(wardId) {
            const sel = document.getElementById('admitModalBedSel');
            Array.from(sel.options).forEach(function(opt) {
                if (!opt.value) return;
                opt.style.display = (!wardId || opt.dataset.ward === wardId) ? '' : 'none';
            });
            sel.value = '';
        }
        </script>
    @endif
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
