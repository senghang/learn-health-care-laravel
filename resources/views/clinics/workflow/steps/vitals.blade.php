{{--
    Step: Vital Signs (សញ្ញានៃជំងឺ)
    Variables from VitalsStep::viewData():
      $latestVitals — VitalSignModel|null (most recent set)
      $allVitals    — Collection of all VitalSignModel for this visit
      $visit, $stepIdx, $steps
--}}
@php
    $obs = collect();
    if (isset($latestVitals) && $latestVitals) {
        $obs = $latestVitals->observations->pluck('value', 'name');
    }

    $vitalFields = [
        [
            'name'        => 'temperature',
            'km'          => 'សីតុណ្ហភាព',
            'en'          => 'Temperature',
            'unit'        => '°C',
            'icon'        => 'bi-thermometer-half',
            'color'       => '#e74c3c',
            'min'         => 30, 'max' => 45, 'step' => '0.1',
            'placeholder' => '36.5',
            'normal'      => [36.1, 37.2],
            'warn'        => [[35.0, 36.0], [37.3, 38.4]],
            'critical'    => [[null, 34.9], [38.5, null]],
            'hint'        => 'Normal 36.1–37.2°C',
        ],
        [
            'name'        => 'heart_rate',
            'km'          => 'បេះដូង',
            'en'          => 'Heart Rate',
            'unit'        => 'bpm',
            'icon'        => 'bi-heart-pulse-fill',
            'color'       => '#e91e8c',
            'min'         => 20, 'max' => 300, 'step' => '1',
            'placeholder' => '72',
            'normal'      => [60, 100],
            'warn'        => [[50, 59], [101, 120]],
            'critical'    => [[null, 49], [121, null]],
            'hint'        => 'Normal 60–100 bpm',
        ],
        [
            'name'        => 'respiratory_rate',
            'km'          => 'ដង្ហើម',
            'en'          => 'Resp. Rate',
            'unit'        => '/min',
            'icon'        => 'bi-wind',
            'color'       => '#00bcd4',
            'min'         => 4, 'max' => 60, 'step' => '1',
            'placeholder' => '16',
            'normal'      => [12, 20],
            'warn'        => [[8, 11], [21, 25]],
            'critical'    => [[null, 7], [26, null]],
            'hint'        => 'Normal 12–20 /min',
        ],
        [
            'name'        => 'blood_pressure_systolic',
            'km'          => 'Systolic',
            'en'          => 'SBP',
            'unit'        => 'mmHg',
            'icon'        => 'bi-arrow-up-circle',
            'color'       => '#9b59b6',
            'min'         => 50, 'max' => 300, 'step' => '1',
            'placeholder' => '120',
            'normal'      => [90, 139],
            'warn'        => [[80, 89], [140, 179]],
            'critical'    => [[null, 79], [180, null]],
            'hint'        => 'Normal 90–139 mmHg',
        ],
        [
            'name'        => 'blood_pressure_diastolic',
            'km'          => 'Diastolic',
            'en'          => 'DBP',
            'unit'        => 'mmHg',
            'icon'        => 'bi-arrow-down-circle',
            'color'       => '#9b59b6',
            'min'         => 20, 'max' => 200, 'step' => '1',
            'placeholder' => '80',
            'normal'      => [60, 89],
            'warn'        => [[50, 59], [90, 109]],
            'critical'    => [[null, 49], [110, null]],
            'hint'        => 'Normal 60–89 mmHg',
        ],
        [
            'name'        => 'oxygen_saturation',
            'km'          => 'អុកស៊ីសែន',
            'en'          => 'SpO₂',
            'unit'        => '%',
            'icon'        => 'bi-lungs-fill',
            'color'       => '#2eca6a',
            'min'         => 50, 'max' => 100, 'step' => '0.1',
            'placeholder' => '98',
            'normal'      => [95, 100],
            'warn'        => [[91, 94], [null, null]],
            'critical'    => [[null, 90], [null, null]],
            'hint'        => 'Normal ≥95%',
        ],
        [
            'name'        => 'blood_glucose',
            'km'          => 'ស្ករ',
            'en'          => 'Glucose',
            'unit'        => 'mmol/L',
            'icon'        => 'bi-droplet-fill',
            'color'       => '#3498db',
            'min'         => 1, 'max' => 50, 'step' => '0.1',
            'placeholder' => '5.5',
            'normal'      => [3.9, 7.8],
            'warn'        => [[3.0, 3.8], [7.9, 11.0]],
            'critical'    => [[null, 2.9], [11.1, null]],
            'hint'        => 'Normal 3.9–7.8 mmol/L',
        ],
    ];
@endphp

{{-- Step header --}}
<div class="card-hd" style="flex-wrap:wrap;gap:8px;padding:14px 18px 10px">
    <div style="flex:1;min-width:0">
        <div class="card-hd-title">
            <i class="bi bi-heart-pulse-fill" style="color:#e74c3c"></i>
            សញ្ញានៃជំងឺ
            <small style="font-size:11px;color:#bbb;font-weight:400">/ Vital Signs</small>
        </div>
        <div style="font-size:10.5px;color:#aaa;margin-top:2px">
            ជំហាន {{ $stepIdx+1 }} នៃ {{ count($steps) }} / Step {{ $stepIdx+1 }} of {{ count($steps) }}
        </div>
    </div>
    <div style="display:flex;gap:6px;align-items:center;flex-shrink:0">
        @if($obs->isNotEmpty())
        <span style="font-size:11px;background:#e8f8ef;color:#2eca6a;padding:3px 10px;border-radius:20px;border:1px solid #a8e6c2;font-weight:700">
            <i class="bi bi-check-circle-fill"></i> {{ $obs->count() }} readings saved
        </span>
        @endif
        {{-- Auto-save indicator (populated by JS) --}}
        <span id="vitalsAutoSave" style="font-size:10px;color:#bbb;display:none">
            <i class="bi bi-cloud-check"></i> <span id="vitalsAutoSaveText">Saved</span>
        </span>
    </div>
</div>

{{-- Progress bar --}}
<div style="height:3px;background:#e6e9f0">
    <div style="height:100%;width:{{ round($stepIdx/count($steps)*100) }}%;background:linear-gradient(90deg,#4154f1,#717ff5)"></div>
</div>

<div class="card-bd">

    {{-- Clinical alert banner (populated by JS when abnormal values detected) --}}
    <div id="vitalAlertBanner" style="display:none;border-radius:10px;padding:10px 14px;margin-bottom:14px;font-size:12px;font-weight:600;border:1.5px solid;gap:8px;align-items:flex-start">
        <i class="bi bi-exclamation-triangle-fill" style="font-size:14px;flex-shrink:0;margin-top:1px"></i>
        <div id="vitalAlertText"></div>
    </div>

    <form id="stepForm" method="POST" action="{{ route('workflow.step.save', [$visit->code, 'vitals']) }}">
        @csrf @method('PATCH')

        {{-- Meta row --}}
        <div class="row g-2 mb-3">
            <div class="col-6 col-sm-4">
                <div class="fld">
                    <label class="flbl"><span class="km">កត់ដោយ</span><span class="en">/ Recorded By</span></label>
                    <input name="recorded_by" class="form-control"
                           value="{{ old('recorded_by', $latestVitals->recorded_by ?? auth()->user()?->name ?? '') }}"/>
                </div>
            </div>
            <div class="col-6 col-sm-4">
                <div class="fld">
                    <label class="flbl"><span class="km">ថ្ងៃម៉ោង</span><span class="en">/ Recorded At</span></label>
                    <input type="datetime-local" name="recorded_at" class="form-control"
                           value="{{ old('recorded_at',
                               (isset($latestVitals) && $latestVitals?->recorded_at)
                                   ? $latestVitals->recorded_at->format('Y-m-d\TH:i')
                                   : now()->format('Y-m-d\TH:i')
                           ) }}"/>
                </div>
            </div>
        </div>

        {{-- Vital sign cards --}}
        <div class="row g-2">
            @foreach($vitalFields as $v)
            @php $savedVal = old('vitals.' . $v['name'], $obs->get($v['name'], '')); @endphp
            <div class="col-6 col-sm-4 col-lg-3">
                <div class="vital-card" id="vcard_{{ $v['name'] }}"
                     style="background:#f6f8fa;border-radius:12px;padding:12px 10px 10px;border:1.5px solid #e6e9f0;text-align:center;position:relative;transition:border-color .2s,background .2s">
                    <div style="position:absolute;top:8px;right:10px">
                        <i class="bi {{ $v['icon'] }}" style="font-size:13px;color:{{ $v['color'] }}33"></i>
                    </div>
                    <div style="font-size:10px;color:{{ $v['color'] }};font-weight:700;margin-bottom:5px;letter-spacing:.2px">
                        {{ $v['en'] }}
                    </div>
                    <input type="number"
                           id="vital_{{ $v['name'] }}"
                           name="vitals[{{ $v['name'] }}]"
                           class="form-control vital-input"
                           data-vital="{{ $v['name'] }}"
                           style="text-align:center;font-size:20px;font-weight:800;color:{{ $v['color'] }};border:2px solid {{ $v['color'] }}22;background:#fff;transition:border-color .2s"
                           step="{{ $v['step'] }}"
                           min="{{ $v['min'] }}"
                           max="{{ $v['max'] }}"
                           placeholder="{{ $v['placeholder'] }}"
                           value="{{ $savedVal }}"
                           oninput="assessVital('{{ $v['name'] }}', this.value)"/>
                    <div style="font-size:9px;color:#aaa;margin-top:4px">{{ $v['km'] }} ({{ $v['unit'] }})</div>
                    {{-- Status tag (updated by JS) --}}
                    <div id="vtag_{{ $v['name'] }}" style="font-size:9px;margin-top:4px;height:14px;font-weight:700"></div>
                    {{-- Hint --}}
                    <div style="font-size:8.5px;color:#cbd5e1;margin-top:2px">{{ $v['hint'] }}</div>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Pulse pressure (auto-calculated) --}}
        <div class="row g-2 mt-1">
            <div class="col-6 col-sm-3">
                <div style="background:#f6f8fa;border-radius:10px;padding:10px;border:1px solid #e6e9f0;text-align:center">
                    <div style="font-size:9.5px;color:#9b59b6;font-weight:700;margin-bottom:3px">Pulse Pressure</div>
                    <div id="ppDisplay" style="font-size:17px;font-weight:800;color:#9b59b6">—</div>
                    <div style="font-size:8.5px;color:#bbb;margin-top:2px">mmHg (SBP − DBP)</div>
                    <div style="font-size:8.5px;color:#cbd5e1">Normal 30–50 mmHg</div>
                </div>
            </div>
        </div>

    </form>

    {{-- Previous vitals history (collapsible) --}}
    @if(isset($allVitals) && $allVitals->count() > 1)
    <div style="margin-top:16px">
        <button type="button" onclick="toggleVitalHistory()"
                style="display:flex;align-items:center;gap:6px;background:none;border:1px solid #e6eaf5;border-radius:8px;padding:6px 12px;font-size:11.5px;color:#64748b;cursor:pointer;font-family:inherit">
            <i class="bi bi-clock-history" style="color:#4154f1"></i>
            <span>Prior readings ({{ $allVitals->count() - 1 }})</span>
            <i class="bi bi-chevron-down" id="vhChevron" style="transition:transform .2s;margin-left:2px"></i>
        </button>
        <div id="vitalHistory" style="display:none;margin-top:8px">
            <div style="overflow-x:auto">
                <table style="width:100%;border-collapse:collapse;font-size:11px">
                    <thead>
                        <tr style="background:#f6f8fa">
                            <th style="padding:6px 10px;text-align:left;color:#64748b;font-weight:700;white-space:nowrap">Recorded At</th>
                            <th style="padding:6px 8px;text-align:center;color:#e74c3c">T°</th>
                            <th style="padding:6px 8px;text-align:center;color:#e91e8c">HR</th>
                            <th style="padding:6px 8px;text-align:center;color:#00bcd4">RR</th>
                            <th style="padding:6px 8px;text-align:center;color:#9b59b6">BP</th>
                            <th style="padding:6px 8px;text-align:center;color:#2eca6a">SpO₂</th>
                            <th style="padding:6px 8px;text-align:center;color:#3498db">Gluc</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($allVitals->skip(1)->take(5) as $vh)
                    @php $vhObs = $vh->observations->pluck('value','name'); @endphp
                    <tr style="border-bottom:1px solid #e6e9f0">
                        <td style="padding:5px 10px;color:#64748b;white-space:nowrap">{{ $vh->recorded_at?->format('d/m H:i') ?? '—' }}</td>
                        <td style="padding:5px 8px;text-align:center">{{ $vhObs->get('temperature','—') }}</td>
                        <td style="padding:5px 8px;text-align:center">{{ $vhObs->get('heart_rate','—') }}</td>
                        <td style="padding:5px 8px;text-align:center">{{ $vhObs->get('respiratory_rate','—') }}</td>
                        <td style="padding:5px 8px;text-align:center;white-space:nowrap">
                            {{ $vhObs->get('blood_pressure_systolic','—') }}/{{ $vhObs->get('blood_pressure_diastolic','—') }}
                        </td>
                        <td style="padding:5px 8px;text-align:center">{{ $vhObs->get('oxygen_saturation','—') }}</td>
                        <td style="padding:5px 8px;text-align:center">{{ $vhObs->get('blood_glucose','—') }}</td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

</div>

@push('scripts')
<script>
// ── Clinical alert ranges ──────────────────────────────────────────────
var VITAL_RANGES = {
    temperature:              { normal:[36.1,37.2], warnLow:[35.0,36.0], warnHigh:[37.3,38.4], critLow:[null,34.9], critHigh:[38.5,null], unit:'°C',     label:'Temperature' },
    heart_rate:               { normal:[60,100],    warnLow:[50,59],     warnHigh:[101,120],   critLow:[null,49],   critHigh:[121,null],  unit:'bpm',    label:'Heart Rate' },
    respiratory_rate:         { normal:[12,20],     warnLow:[8,11],      warnHigh:[21,25],     critLow:[null,7],    critHigh:[26,null],   unit:'/min',   label:'Resp. Rate' },
    blood_pressure_systolic:  { normal:[90,139],    warnLow:[80,89],     warnHigh:[140,179],   critLow:[null,79],   critHigh:[180,null],  unit:'mmHg',   label:'SBP' },
    blood_pressure_diastolic: { normal:[60,89],     warnLow:[50,59],     warnHigh:[90,109],    critLow:[null,49],   critHigh:[110,null],  unit:'mmHg',   label:'DBP' },
    oxygen_saturation:        { normal:[95,100],    warnLow:[91,94],     warnHigh:[null,null], critLow:[null,90],   critHigh:[null,null], unit:'%',      label:'SpO₂' },
    blood_glucose:            { normal:[3.9,7.8],   warnLow:[3.0,3.8],   warnHigh:[7.9,11.0], critLow:[null,2.9], critHigh:[11.1,null],  unit:'mmol/L', label:'Glucose' },
};

// Level: 0=normal, 1=warn, 2=critical  →  colors
var LEVEL_CFG = {
    0: { bg:'#f6f8fa', border:'#e6e9f0', tag:'',         tagColor:'',       cardColor:'#f6f8fa' },
    1: { bg:'#fff8ee', border:'#ffd080', tag:'⚠ Warning', tagColor:'#c97700',cardColor:'#fff8ee' },
    2: { bg:'#fde8e8', border:'#e74c3c', tag:'🔴 Critical',tagColor:'#e74c3c',cardColor:'#fde8e8' },
};

function getVitalLevel(name, val) {
    if (val === '' || val === null || isNaN(val)) return -1; // empty
    val = parseFloat(val);
    var r = VITAL_RANGES[name];
    if (!r) return 0;

    // Critical check
    if (r.critLow[1] !== null && val <= r.critLow[1]) return 2;
    if (r.critHigh[0] !== null && val >= r.critHigh[0]) return 2;
    // Warn check
    if (r.warnLow[0] !== null && val >= r.warnLow[0] && val <= r.warnLow[1]) return 1;
    if (r.warnHigh[0] !== null && val >= r.warnHigh[0] && (r.warnHigh[1] === null || val <= r.warnHigh[1])) return 1;
    return 0;
}

function assessVital(name, val) {
    var level  = getVitalLevel(name, val);
    var card   = document.getElementById('vcard_' + name);
    var input  = document.getElementById('vital_' + name);
    var tag    = document.getElementById('vtag_'  + name);

    if (!card || !input || !tag) return;

    if (level < 0) {
        card.style.background   = '#f6f8fa';
        card.style.borderColor  = '#e6e9f0';
        input.style.borderColor = VITAL_RANGES[name] ? '' : '';
        tag.textContent = '';
        tag.style.color = '';
    } else {
        var cfg = LEVEL_CFG[level];
        card.style.background  = cfg.bg;
        card.style.borderColor = cfg.border;
        tag.textContent        = cfg.tag;
        tag.style.color        = cfg.tagColor;
        if (level === 1) input.style.borderColor = '#ffd080';
        if (level === 2) input.style.borderColor = '#e74c3c';
        if (level === 0) input.style.borderColor = '#a8e6c2';
    }

    // Update pulse pressure
    updatePP();
    // Update global alert banner
    updateAlertBanner();
}

function updatePP() {
    var sbp = parseFloat(document.getElementById('vital_blood_pressure_systolic')?.value);
    var dbp = parseFloat(document.getElementById('vital_blood_pressure_diastolic')?.value);
    var ppEl = document.getElementById('ppDisplay');
    if (!ppEl) return;
    if (!isNaN(sbp) && !isNaN(dbp) && sbp > 0 && dbp > 0) {
        var pp = sbp - dbp;
        ppEl.textContent = pp;
        ppEl.style.color = (pp < 30 || pp > 50) ? '#e74c3c' : '#9b59b6';
    } else {
        ppEl.textContent = '—';
        ppEl.style.color = '#9b59b6';
    }
}

function updateAlertBanner() {
    var criticals = [], warnings = [];
    Object.keys(VITAL_RANGES).forEach(function(name) {
        var el = document.getElementById('vital_' + name);
        if (!el || !el.value) return;
        var level = getVitalLevel(name, el.value);
        var r = VITAL_RANGES[name];
        if (level === 2) criticals.push(r.label + ': ' + el.value + ' ' + r.unit);
        if (level === 1) warnings.push(r.label + ': ' + el.value + ' ' + r.unit);
    });

    var banner = document.getElementById('vitalAlertBanner');
    var text   = document.getElementById('vitalAlertText');
    if (!banner || !text) return;

    if (criticals.length === 0 && warnings.length === 0) {
        banner.style.display = 'none';
        return;
    }
    banner.style.display = 'flex';

    var parts = [];
    if (criticals.length > 0) {
        parts.push('<span style="color:#e74c3c">🔴 CRITICAL: ' + criticals.join(', ') + '</span>');
        banner.style.background   = '#fde8e8';
        banner.style.borderColor  = '#e74c3c';
        banner.style.color        = '#e74c3c';
        banner.querySelector('i').style.color = '#e74c3c';
    }
    if (warnings.length > 0) {
        parts.push('<span style="color:#c97700">⚠ Warning: ' + warnings.join(', ') + '</span>');
        if (criticals.length === 0) {
            banner.style.background  = '#fff8ee';
            banner.style.borderColor = '#ffd080';
            banner.querySelector('i').style.color = '#c97700';
        }
    }
    text.innerHTML = parts.join('<br>');
}

function toggleVitalHistory() {
    var el  = document.getElementById('vitalHistory');
    var chv = document.getElementById('vhChevron');
    if (!el) return;
    var open = el.style.display !== 'none';
    el.style.display = open ? 'none' : 'block';
    if (chv) chv.style.transform = open ? '' : 'rotate(180deg)';
}

// Run on page load to assess any pre-filled values
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.vital-input').forEach(function(el) {
        if (el.value) assessVital(el.dataset.vital, el.value);
    });
    updatePP();
});
</script>
@endpush
