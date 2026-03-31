{{--
    Step: Vital Signs (សញ្ញានៃជំងឺ)
    Variables from VitalsStep::viewData():
      $latestVitals — VitalSignModel|null (most recent set)
      $allVitals    — Collection of all VitalSignModel for this visit
      $visit, $stepIdx, $steps
--}}
@php
    // Get latest observations keyed by name
    $obs = collect();
    if (isset($latestVitals) && $latestVitals) {
        $obs = $latestVitals->observations->pluck('value', 'name');
    }

    $vitalFields = [
        ['name' => 'temperature',              'km' => 'សីតុណ្ហភាព',  'en' => 'Temperature',     'unit' => '°C',    'icon' => 'bi-thermometer-half', 'color' => '#e74c3c', 'min' => 30, 'max' => 45,  'step' => '0.1', 'placeholder' => '36.5'],
        ['name' => 'heart_rate',               'km' => 'បេះដូង',      'en' => 'Heart Rate',      'unit' => 'bpm',   'icon' => 'bi-heart-pulse-fill', 'color' => '#e91e8c', 'min' => 20, 'max' => 300, 'step' => '1',   'placeholder' => '72'],
        ['name' => 'respiratory_rate',         'km' => 'ដង្ហើម',      'en' => 'Respiratory Rate', 'unit' => '/min',  'icon' => 'bi-wind',             'color' => '#00bcd4', 'min' => 4,  'max' => 60,  'step' => '1',   'placeholder' => '16'],
        ['name' => 'blood_pressure_systolic',  'km' => 'Systolic',    'en' => 'SBP',             'unit' => 'mmHg',  'icon' => 'bi-arrow-up-circle',  'color' => '#9b59b6', 'min' => 50, 'max' => 300, 'step' => '1',   'placeholder' => '120'],
        ['name' => 'blood_pressure_diastolic', 'km' => 'Diastolic',   'en' => 'DBP',             'unit' => 'mmHg',  'icon' => 'bi-arrow-down-circle','color' => '#9b59b6', 'min' => 20, 'max' => 200, 'step' => '1',   'placeholder' => '80'],
        ['name' => 'oxygen_saturation',        'km' => 'អុកស៊ីសែន',  'en' => 'SpO₂',            'unit' => '%',     'icon' => 'bi-lungs-fill',       'color' => '#2eca6a', 'min' => 50, 'max' => 100, 'step' => '0.1', 'placeholder' => '98'],
        ['name' => 'blood_glucose',            'km' => 'ស្ករ',        'en' => 'Glucose',         'unit' => 'mmol/L','icon' => 'bi-droplet-fill',     'color' => '#3498db', 'min' => 1,  'max' => 50,  'step' => '0.1', 'placeholder' => '5.5'],
    ];
@endphp

<div class="card-hd" style="flex-wrap:wrap;gap:8px;padding:14px 18px 10px">
    <div style="flex:1;min-width:0">
        <div class="card-hd-title">
            <i class="bi bi-heart-pulse-fill" style="color:#e74c3c"></i>សញ្ញានៃជំងឺ
            <small style="font-size:11px;color:#bbb;font-weight:400">/ Vital Signs</small>
        </div>
        <div style="font-size:10.5px;color:#aaa;margin-top:3px">
            ជំហាន {{ $stepIdx+1 }} នៃ {{ count($steps) }} / Step {{ $stepIdx+1 }} of {{ count($steps) }}
        </div>
    </div>
    @if($obs->isNotEmpty())
        <span
            style="font-size:11px;background:#e8f8ef;color:#2eca6a;padding:3px 10px;border-radius:20px;border:1px solid #a8e6c2;font-weight:700;flex-shrink:0">
        <i class="bi bi-check-circle-fill"></i> {{ $obs->count() }} readings saved
    </span>
    @endif
</div>

<div style="height:3px;background:#f0f2ff">
    <div
        style="height:100%;width:{{ round($stepIdx/count($steps)*100) }}%;background:linear-gradient(90deg,#4154f1,#717ff5)"></div>
</div>

<div class="card-bd">
    <div class="note note-info mb-3">
        <i class="bi bi-info-circle-fill"></i>
        <div>សញ្ញានៃជំងឺ — T° HR RR BP SpO₂ Glucose / Record the patient's vital signs. Leave blank to skip any
            reading.
        </div>
    </div>

    <form id="stepForm" method="POST" action="{{ route('workflow.step.save', [$visit->code, 'vitals']) }}">
        @csrf @method('PATCH')

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
                    <label class="flbl">
                        <span class="km">ថ្ងៃម៉ោង</span>
                        <span class="en">/ Recorded At</span>
                    </label>
                    <input type="datetime-local"
                           name="recorded_at"
                           class="form-control"
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
                <div class="col-6 col-sm-4 col-lg-3">
                    <div
                        style="background:#f6f9ff;border-radius:12px;padding:14px;border:1px solid #f0f2ff;text-align:center;position:relative">
                        <div style="position:absolute;top:8px;right:10px">
                            <i class="bi {{ $v['icon'] }}" style="font-size:14px;color:{{ $v['color'] }}33"></i>
                        </div>
                        <div style="font-size:10px;color:{{ $v['color'] }};font-weight:700;margin-bottom:6px">
                            {{ $v['en'] }}
                        </div>
                        <input type="number"
                               name="vitals[{{ $v['name'] }}]"
                               class="form-control"
                               style="text-align:center;font-size:18px;font-weight:800;color:{{ $v['color'] }};border:2px solid {{ $v['color'] }}22;background:#fff"
                               step="{{ $v['step'] }}"
                               min="{{ $v['min'] }}"
                               max="{{ $v['max'] }}"
                               placeholder="{{ $v['placeholder'] }}"
                               value="{{ old('vitals.' . $v['name'], $obs->get($v['name'], '')) }}"/>
                        <div style="font-size:9px;color:#aaa;margin-top:4px">{{ $v['km'] }} ({{ $v['unit'] }})</div>
                    </div>
                </div>
            @endforeach
        </div>
    </form>
</div>
