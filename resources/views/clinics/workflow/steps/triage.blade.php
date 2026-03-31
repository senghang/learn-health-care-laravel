{{--
    Step: Triage (ពិនិត្យចូល)
    Variables from TriageStep::viewData():
      $triage   — TriageModel|null
      $visit    — Visit model
      $stepIdx  — 0-based index
      $steps    — all steps array
--}}

<div class="card-hd" style="flex-wrap:wrap;gap:8px;padding:14px 18px 10px">
    <div style="flex:1;min-width:0">
        <div class="card-hd-title">
            <i class="bi bi-heart-pulse-fill"></i>ការពិនិត្យចូល
            <small style="font-size:11px;color:#bbb;font-weight:400">/ Triage</small>
        </div>
        <div style="font-size:10.5px;color:#aaa;margin-top:3px">
            ជំហាន {{ $stepIdx+1 }} នៃ {{ count($steps) }} /
            Step {{ $stepIdx+1 }} of {{ count($steps) }}
        </div>
    </div>
    @if($triage)
    <span style="font-size:11px;background:#e8f8ef;color:#2eca6a;padding:3px 10px;border-radius:20px;border:1px solid #a8e6c2;font-weight:700;flex-shrink:0">
        <i class="bi bi-check-circle-fill"></i> Saved
    </span>
    @endif
</div>

{{-- Progress bar --}}
<div style="height:3px;background:#f0f2ff">
    <div style="height:100%;width:{{ round($stepIdx/count($steps)*100) }}%;background:linear-gradient(90deg,#4154f1,#717ff5)"></div>
</div>

<div class="card-bd">
    <div class="note note-info mb-3">
        <i class="bi bi-info-circle-fill"></i>
        <div>ការត្អូញត្អែររបស់អ្នកជំងឺ — ចំណោទ ហើយការវាស់ស្ទង់ / Chief complaint, measurements, and triage severity</div>
    </div>

    <form id="stepForm" method="POST" action="{{ route('workflow.step.save', [$visit->code, 'triage']) }}">
        @csrf @method('PATCH')

        <div class="row g-3">
            {{-- Encounter code --}}
            <div class="col-6 col-sm-4">
                <div class="fld">
                    <label class="flbl"><span class="km">លេខ Encounter</span><span class="en">/ Encounter Code</span></label>
                    <input class="form-control ro" value="{{ $triage->encounter_code ?? 'TR-'.$visit->code }}" readonly/>
                </div>
            </div>

            {{-- Triage Level (NEW) --}}
            <div class="col-6 col-sm-4">
                <div class="fld">
                    <label class="flbl">
                        <span class="km">កម្រិតបន្ទាន់</span><span class="en">/ Triage Level</span>
                    </label>
                    <select name="triage_level" class="form-select">
                        @php
                            $levels = [
                                ''          => '— ជ្រើស / Select —',
                                'Emergency' => '🔴 បន្ទាន់ / Emergency',
                                'Urgent'    => '🟠 បន្ទាន់មធ្យម / Urgent',
                                'Standard'  => '🔵 ធម្មតា / Standard',
                                'Low'       => '⚪ ទាប / Low',
                            ];
                        @endphp
                        @foreach($levels as $val => $label)
                            <option value="{{ $val }}" {{ old('triage_level', $triage->triage_level ?? '') === $val ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
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
                        <span class="km">ការត្អូញត្អែរ</span><span class="en">/ Chief Complaint</span>
                        <span class="req">*</span>
                    </label>
                    <textarea name="chief_complaint" class="form-control" rows="3"
                              placeholder="អ្នកជំងឺត្អូញត្អែរអំពី... / Patient complains of..."
                    >{{ old('chief_complaint', $triage->chief_complaint ?? '') }}</textarea>
                </div>
            </div>

            {{-- Measurements --}}
            <div class="col-12">
                <div style="font-size:11px;font-weight:700;color:#aaa;text-transform:uppercase;margin-bottom:8px;padding-top:8px;border-top:1px solid #f0f2ff">
                    <i class="bi bi-rulers"></i> Measurements
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

            {{-- BMI (auto-calculated, display only) --}}
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

        {{-- Save / Skip buttons are handled by the parent show.blade.php --}}
    </form>
</div>

@push('scripts')
<script>
function calcBMI() {
    const h = parseFloat(document.querySelector('[name="height"]').value);
    const w = parseFloat(document.querySelector('[name="weight"]').value);
    const bmiEl = document.getElementById('bmiDisplay');
    const catEl = document.getElementById('bmiCategory');

    if (h > 0 && w > 0) {
        const hm = h / 100;
        const bmi = (w / (hm * hm)).toFixed(1);
        bmiEl.textContent = bmi;

        let cat = '—';
        let color = '#333';
        if (bmi < 18.5) { cat = 'Underweight'; color = '#3498db'; }
        else if (bmi < 25) { cat = 'Normal'; color = '#2eca6a'; }
        else if (bmi < 30) { cat = 'Overweight'; color = '#ff771d'; }
        else { cat = 'Obese'; color = '#e74c3c'; }

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
</script>
@endpush
