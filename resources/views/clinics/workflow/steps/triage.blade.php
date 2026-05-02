{{--
    Step: Triage (ពិនិត្យចូល)
    Variables from TriageStep::viewData():
      $triage   — TriageModel|null
      $visit    — Visit model
      $stepIdx  — 0-based index
      $steps    — all steps array
--}}
@php
    $currentLevel = old('triage_level', $triage->triage_level ?? '');

    $triageLevels = [
        'Emergency' => [
            'label'   => 'Emergency',
            'km'      => 'បន្ទាន់ខ្លាំង',
            'desc'    => 'Life-threatening — immediate action',
            'color'   => '#e74c3c',
            'bg'      => '#fde8e8',
            'border'  => '#e74c3c',
            'icon'    => '🔴',
            'biIcon'  => 'bi-exclamation-octagon-fill',
        ],
        'Urgent' => [
            'label'   => 'Urgent',
            'km'      => 'បន្ទាន់',
            'desc'    => 'Serious — treat within 30 min',
            'color'   => '#ff771d',
            'bg'      => '#fff3e8',
            'border'  => '#ffad6b',
            'icon'    => '🟠',
            'biIcon'  => 'bi-exclamation-triangle-fill',
        ],
        'Standard' => [
            'label'   => 'Standard',
            'km'      => 'ធម្មតា',
            'desc'    => 'Stable — routine care',
            'color'   => '#4154f1',
            'bg'      => '#eef0fd',
            'border'  => '#9aaaf8',
            'icon'    => '🔵',
            'biIcon'  => 'bi-person-fill',
        ],
        'Low' => [
            'label'   => 'Low',
            'km'      => 'ទាប',
            'desc'    => 'Minor — can wait safely',
            'color'   => '#64748b',
            'bg'      => '#f1f5f9',
            'border'  => '#cbd5e1',
            'icon'    => '⚪',
            'biIcon'  => 'bi-clock',
        ],
    ];
@endphp

{{-- Emergency banner (shown when Emergency level is active) --}}
<div id="triageEmergencyBanner"
     style="display:{{ $currentLevel === 'Emergency' ? 'flex' : 'none' }};
            align-items:center;gap:10px;
            background:#e74c3c;color:#fff;
            padding:10px 18px;font-size:13px;font-weight:700;
            border-bottom:2px solid #c0392b;
            animation:none">
    <i class="bi bi-exclamation-octagon-fill" style="font-size:18px;animation:pulse 1s infinite"></i>
    🔴 EMERGENCY — Immediate intervention required. Alert attending staff now.
</div>

<div class="card-hd" style="flex-wrap:wrap;gap:8px;padding:14px 18px 10px">
    <div style="flex:1;min-width:0">
        <div class="card-hd-title">
            <i class="bi bi-shield-exclamation" style="color:#ff771d"></i>
            ការពិនិត្យចូល
            <small style="font-size:11px;color:#bbb;font-weight:400">/ Triage</small>
        </div>
        <div style="font-size:10.5px;color:#aaa;margin-top:3px">
            ជំហាន {{ $stepIdx+1 }} នៃ {{ count($steps) }} / Step {{ $stepIdx+1 }} of {{ count($steps) }}
        </div>
    </div>
    @if($triage)
    <span style="font-size:11px;background:#e8f8ef;color:#2eca6a;padding:3px 10px;border-radius:20px;border:1px solid #a8e6c2;font-weight:700;flex-shrink:0">
        <i class="bi bi-check-circle-fill"></i> Saved
        @if($currentLevel)
            · <span style="color:{{ $triageLevels[$currentLevel]['color'] ?? '#333' }}">{{ $currentLevel }}</span>
        @endif
    </span>
    @endif
</div>

{{-- Progress bar --}}
<div style="height:3px;background:#f0f2ff">
    <div style="height:100%;width:{{ round($stepIdx/count($steps)*100) }}%;background:linear-gradient(90deg,#4154f1,#717ff5)"></div>
</div>

<div class="card-bd">

    <form id="stepForm" method="POST" action="{{ route('workflow.step.save', [$visit->code, 'triage']) }}">
        @csrf @method('PATCH')

        {{-- ── Triage Level (visual selector) ─────────────────────── --}}
        <div class="fld mb-3">
            <label class="flbl">
                <span class="km">កម្រិតបន្ទាន់</span>
                <span class="en">/ Triage Level</span>
                <span class="req">*</span>
            </label>
            <input type="hidden" id="triageLevelInput" name="triage_level" value="{{ $currentLevel }}"
                   required data-error-msg="Triage Level"/>
            <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:8px;margin-top:6px" id="triageBtns">
                @foreach($triageLevels as $key => $cfg)
                <button type="button"
                        id="tlBtn_{{ $key }}"
                        onclick="selectTriageLevel('{{ $key }}')"
                        style="display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:10px;
                               border:2px solid {{ $currentLevel === $key ? $cfg['border'] : '#e2e8f0' }};
                               background:{{ $currentLevel === $key ? $cfg['bg'] : '#f8fafc' }};
                               cursor:pointer;text-align:left;font-family:inherit;transition:all .15s;
                               {{ $currentLevel === $key ? 'box-shadow:0 2px 10px rgba(0,0,0,.1)' : '' }}">
                    <span style="font-size:20px;flex-shrink:0">{{ $cfg['icon'] }}</span>
                    <div>
                        <div style="font-size:13px;font-weight:800;color:{{ $currentLevel === $key ? $cfg['color'] : '#374151' }}">
                            {{ $cfg['label'] }}
                            <span style="font-size:10.5px;font-weight:400;color:#94a3b8">/ {{ $cfg['km'] }}</span>
                        </div>
                        <div style="font-size:10px;color:#94a3b8;margin-top:1px">{{ $cfg['desc'] }}</div>
                    </div>
                </button>
                @endforeach
            </div>
        </div>

        <div class="row g-3">
            {{-- Encounter code --}}
            <div class="col-6 col-sm-4">
                <div class="fld">
                    <label class="flbl"><span class="km">លេខ Encounter</span><span class="en">/ Encounter Code</span></label>
                    <input class="form-control ro" value="{{ $triage->encounter_code ?? 'TR-'.$visit->code }}" readonly/>
                </div>
            </div>

            {{-- Recorded By --}}
            <div class="col-6 col-sm-4">
                <div class="fld">
                    <label class="flbl"><span class="km">ពិនិត្យដោយ</span><span class="en">/ Recorded By</span></label>
                    <input name="recorded_by" class="form-control" placeholder="ឈ្មោះ / Name"
                           value="{{ old('recorded_by', $triage->recorded_by ?? '') }}"/>
                </div>
            </div>

            {{-- Recorded At --}}
            <div class="col-6 col-sm-4">
                <div class="fld">
                    <label class="flbl"><span class="km">ថ្ងៃម៉ោង</span><span class="en">/ Recorded At</span></label>
                    <input type="datetime-local" name="recorded_at" class="form-control"
                           value="{{ old('recorded_at', $triage?->recorded_at?->format('Y-m-d\TH:i') ?? now()->format('Y-m-d\TH:i')) }}"/>
                </div>
            </div>

            {{-- Title --}}
            <div class="col-6 col-sm-4">
                <div class="fld">
                    <label class="flbl"><span class="km">តួរបស់</span><span class="en">/ Title</span></label>
                    <select name="title" class="form-select">
                        @foreach(['វេជ្ជបណ្ឌិត / Doctor','គិលានុបដ្ឋាយិកា / Nurse','Paramedic'] as $t)
                            <option value="{{ $t }}" {{ old('title', $triage->title ?? '') === $t ? 'selected' : '' }}>{{ $t }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Chief Complaint --}}
            <div class="col-12">
                <div class="fld">
                    <label class="flbl">
                        <span class="km">ការត្អូញត្អែរ</span>
                        <span class="en">/ Chief Complaint</span>
                        <span class="req">*</span>
                    </label>
                    <textarea name="chief_complaint" class="form-control" rows="3"
                              placeholder="អ្នកជំងឺត្អូញត្អែរអំពី... / Patient complains of..."
                              required data-error-msg="Chief Complaint"
                    >{{ old('chief_complaint', $triage->chief_complaint ?? '') }}</textarea>
                </div>
            </div>

            {{-- Measurements --}}
            <div class="col-12">
                <div style="font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;
                            letter-spacing:.5px;margin-bottom:8px;padding-top:4px;
                            border-top:1px solid #f0f2ff;display:flex;align-items:center;gap:6px">
                    <i class="bi bi-rulers"></i> Measurements
                    <span style="font-size:9.5px;font-weight:400;color:#cbd5e1;text-transform:none">(optional)</span>
                </div>
            </div>

            {{-- Height --}}
            <div class="col-6 col-sm-3">
                <div class="fld">
                    <label class="flbl"><span class="km">កម្ពស់</span><span class="en">/ Height (cm)</span></label>
                    <input type="number" name="height" class="form-control" step="0.1" min="30" max="250"
                           placeholder="170"
                           value="{{ old('height', $triage->height ?? '') }}"
                           oninput="calcBMI()"/>
                </div>
            </div>

            {{-- Weight --}}
            <div class="col-6 col-sm-3">
                <div class="fld">
                    <label class="flbl"><span class="km">ទម្ងន់</span><span class="en">/ Weight (kg)</span></label>
                    <input type="number" name="weight" class="form-control" step="0.01" min="1" max="300"
                           placeholder="65"
                           value="{{ old('weight', $triage->weight ?? '') }}"
                           oninput="calcBMI()"/>
                </div>
            </div>

            {{-- BMI --}}
            <div class="col-6 col-sm-3">
                <div class="fld">
                    <label class="flbl">
                        <span class="en">BMI</span>
                        <span style="font-size:9px;background:#e8f8ef;color:#1D9E75;padding:1px 6px;border-radius:8px;margin-left:4px">AUTO</span>
                    </label>
                    <div class="form-control ro" id="bmiDisplay" style="font-weight:700">
                        @php
                            $h = $triage->height ?? 0;
                            $w = $triage->weight ?? 0;
                            $bmi = ($h > 0 && $w > 0) ? round($w / (($h/100) * ($h/100)), 1) : '—';
                        @endphp
                        {{ $bmi }}
                    </div>
                </div>
            </div>

            {{-- BMI Category --}}
            <div class="col-6 col-sm-3">
                <div class="fld">
                    <label class="flbl"><span class="en">Category</span></label>
                    <div class="form-control ro" id="bmiCategory">
                        @if($triage && $triage->calculated_bmi)
                            {{ $triage->bmi_category }}
                        @else
                            —
                        @endif
                    </div>
                </div>
            </div>

        </div>
    </form>
</div>

@push('scripts')
<script>
// ── Triage level button selector ───────────────────────────────────────
var TRIAGE_CFG = {
    Emergency: { color:'#e74c3c', bg:'#fde8e8', border:'#e74c3c' },
    Urgent:    { color:'#ff771d', bg:'#fff3e8', border:'#ffad6b' },
    Standard:  { color:'#4154f1', bg:'#eef0fd', border:'#9aaaf8' },
    Low:       { color:'#64748b', bg:'#f1f5f9', border:'#cbd5e1' },
};

function selectTriageLevel(level) {
    document.getElementById('triageLevelInput').value = level;

    // Update button styles
    Object.keys(TRIAGE_CFG).forEach(function(key) {
        var btn = document.getElementById('tlBtn_' + key);
        if (!btn) return;
        if (key === level) {
            btn.style.background  = TRIAGE_CFG[key].bg;
            btn.style.borderColor = TRIAGE_CFG[key].border;
            btn.style.boxShadow   = '0 2px 10px rgba(0,0,0,.1)';
            btn.querySelector('div div:first-child').style.color = TRIAGE_CFG[key].color;
        } else {
            btn.style.background  = '#f8fafc';
            btn.style.borderColor = '#e2e8f0';
            btn.style.boxShadow   = 'none';
            btn.querySelector('div div:first-child').style.color = '#374151';
        }
    });

    // Emergency banner
    var banner = document.getElementById('triageEmergencyBanner');
    if (banner) {
        banner.style.display = (level === 'Emergency') ? 'flex' : 'none';
    }

    // Mark input as valid
    document.getElementById('triageLevelInput').classList.remove('is-invalid');
}

// ── BMI calculation ────────────────────────────────────────────────────
function calcBMI() {
    var h = parseFloat(document.querySelector('[name="height"]').value);
    var w = parseFloat(document.querySelector('[name="weight"]').value);
    var bmiEl = document.getElementById('bmiDisplay');
    var catEl = document.getElementById('bmiCategory');
    if (!bmiEl || !catEl) return;

    if (h > 0 && w > 0) {
        var hm  = h / 100;
        var bmi = (w / (hm * hm)).toFixed(1);
        bmiEl.textContent = bmi;

        var cat = '—', color = '#333';
        if (bmi < 18.5)      { cat = 'Underweight'; color = '#3498db'; }
        else if (bmi < 25)   { cat = 'Normal';       color = '#2eca6a'; }
        else if (bmi < 30)   { cat = 'Overweight';   color = '#ff771d'; }
        else                 { cat = 'Obese';         color = '#e74c3c'; }

        catEl.textContent = cat;
        catEl.style.color = color;
        bmiEl.style.color = color;
    } else {
        bmiEl.textContent = '—';
        catEl.textContent = '—';
        bmiEl.style.color = '#333';
        catEl.style.color = '#333';
    }
}

// ── Pulse animation for emergency banner icon ──────────────────────────
var style = document.createElement('style');
style.textContent = '@keyframes pulse{0%,100%{opacity:1}50%{opacity:.5}}';
document.head.appendChild(style);
</script>
@endpush
