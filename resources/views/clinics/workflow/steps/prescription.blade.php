@php
    $progressPct   = round($stepIdx / count($steps) * 100);
    $progressWidth = $progressPct . '%';
    $saveUrl       = url('/workflow/' . $visit->code . '/prescription/save');
    $firstRx       = $prescriptions->first() ?? null;
    $rxCode        = $firstRx?->code ?? ('RX-' . strtoupper($visit->code));
    $prescribedAt  = old('prescribed_at', $firstRx?->prescribed_at?->format('Y-m-d\TH:i') ?? now()->format('Y-m-d\TH:i'));
    $prescribedBy  = old('prescribed_by', $firstRx?->prescribed_by ?? auth()->user()?->name ?? '');
    $formOptions   = ['Tablet', 'Capsule', 'Syrup', 'Injection', 'Ointment', 'Drops'];
    $rxCount       = $prescriptions->count();
@endphp

<div class="card-hd" style="flex-wrap:wrap;gap:8px;padding:14px 18px 10px">
    <div style="flex:1;min-width:0">
        <div class="card-hd-title">
            <i class="bi bi-capsule-pill" style="color:#e91e8c"></i>បញ្ជាថ្នាំ
            <small style="font-size:11px;color:#bbb;font-weight:400">/ Prescription</small>
        </div>
        <div style="font-size:10.5px;color:#aaa;margin-top:3px">
            ជំហាន {{ $stepIdx+1 }} នៃ {{ count($steps) }} / Step {{ $stepIdx+1 }} of {{ count($steps) }}
        </div>
    </div>
    @if($rxCount > 0)
    <span style="font-size:11px;background:#fce8f5;color:#e91e8c;padding:3px 10px;border-radius:20px;border:1px solid #f5b8e0;font-weight:700;flex-shrink:0">
        <i class="bi bi-check-circle-fill"></i> {{ $rxCount }} Rx Saved
    </span>
    @endif
</div>

<div style="height:3px;background:#f0f2ff">
    <div style="height:100%;width:{{ $progressWidth }};background:linear-gradient(90deg,#4154f1,#717ff5)"></div>
</div>

<div class="card-bd">

    @if($rxCount > 0)
    <div class="note note-success mb-3">
        <i class="bi bi-check-circle-fill"></i>
        <div>
            <strong>{{ $rxCount }} Prescription{{ $rxCount > 1 ? 's' : '' }} saved.</strong>
            Submitting adds a new prescription.
        </div>
    </div>
    @endif

    <form id="stepForm" method="POST" action="{{ $saveUrl }}">
        @csrf
        @method('PATCH')

        <div class="row g-3 mb-4">
            <div class="col-6 col-sm-4">
                <div class="fld">
                    <label class="flbl"><span class="km">Rx Code</span></label>
                    <input class="form-control ro" value="{{ $rxCode }}" readonly/>
                </div>
            </div>
            <div class="col-6 col-sm-4">
                <div class="fld">
                    <label class="flbl"><span class="km">ថ្ងៃ</span><span class="en">/ Prescribed At</span></label>
                    <input type="datetime-local" name="prescribed_at" class="form-control" value="{{ $prescribedAt }}"/>
                </div>
            </div>
            <div class="col-12 col-sm-4">
                <div class="fld">
                    <label class="flbl">
                        <span class="km">ផ្ដល់ដោយ</span><span class="en">/ Prescribed By</span><span class="req">*</span>
                    </label>
                    <input name="prescribed_by" class="form-control" value="{{ $prescribedBy }}" placeholder="Dr. Name" required data-error-msg="Prescribed By"/>
                </div>
            </div>
        </div>

        <div style="font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.6px;color:#e91e8c;margin-bottom:10px">
            ថ្នាំ / Medications
        </div>

        <div id="rxList">
            <div class="rx-row" id="rx-row-0">
                @include('clinics.workflow.steps._rx-row', [
                    'i'           => 0,
                    'med'         => null,
                    'formOptions' => $formOptions,
                ])
            </div>
        </div>

        <button type="button" class="btn btn-outline-primary btn-w100 mt-2" onclick="addMedRow()">
            <i class="bi bi-plus-circle"></i> បន្ថែមថ្នាំ / Add Medication
        </button>

    </form>
</div>

<script>
var medIdx      = 1;
var formOptions = @json($formOptions);

function addMedRow() {
    var list    = document.getElementById('rxList');
    var wrapper = document.createElement('div');
    wrapper.className = 'rx-row';
    wrapper.id = 'rx-row-' + medIdx;

    var opts = formOptions.map(function(f) {
        return '<option value="' + f + '">' + f + '</option>';
    }).join('');

    wrapper.innerHTML =
        '<div class="rx-row-hd">'
        + '<div class="d-flex align-items-center gap-2">'
        + '<span style="font-size:18px">💊</span>'
        + '<strong>ថ្នាំ #' + (medIdx + 1) + '</strong>'
        + '</div>'
        + '<button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest(\'.rx-row\').remove()">✕ លុប</button>'
        + '</div>'
        + '<div class="row g-3 mb-3">'
        + '<div class="col-12 col-sm-6 col-md-3"><div class="fld">'
        + '<label class="flbl"><span class="km">ឈ្មោះថ្នាំ</span> / Medicine <span class="req">*</span></label>'
        /* medicine_name — must match DB column */
        + '<input name="meds[' + medIdx + '][medicine_name]" class="form-control" placeholder="Medicine name"/>'
        + '</div></div>'
        + '<div class="col-6 col-md-2"><div class="fld">'
        + '<label class="flbl">កម្លាំង / Strength</label>'
        + '<input name="meds[' + medIdx + '][strength]" class="form-control" placeholder="500mg"/>'
        + '</div></div>'
        + '<div class="col-6 col-md-2"><div class="fld">'
        + '<label class="flbl">ទំរង់ / Form</label>'
        + '<select name="meds[' + medIdx + '][form]" class="form-select">' + opts + '</select>'
        + '</div></div>'
        + '<div class="col-6 col-md-2"><div class="fld">'
        + '<label class="flbl">វិធី / Method</label>'
        + '<input name="meds[' + medIdx + '][method]" class="form-control" placeholder="Oral"/>'
        + '</div></div>'
        + '<div class="col-6 col-md-3"><div class="fld">'
        + '<label class="flbl">ចំណាំ / Note</label>'
        + '<input name="meds[' + medIdx + '][note]" class="form-control" placeholder="Take with food"/>'
        + '</div></div>'
        + '</div>'
        + '<div style="background:#eef0fd;border-radius:10px;padding:12px">'
        + '<div style="font-size:10px;font-weight:800;color:#4154f1;text-transform:uppercase;letter-spacing:.5px;margin-bottom:10px">កាលវិភាគ / Dosing</div>'
        + '<div class="dosing-grid">'
        + dosingCell(medIdx, 'morning',   'ព្រឹក',  'Morning',   1)
        + dosingCell(medIdx, 'afternoon', 'ថ្ងៃ',   'Afternoon', 0)
        + dosingCell(medIdx, 'evening',   'ល្ងាច',  'Evening',   1)
        + dosingCell(medIdx, 'night',     'យប់',    'Night',     0)
        + dosingCell(medIdx, 'days',      'ថ្ងៃ',   'Days',      0)
        + '<div class="dosing-cell">'
        + '<div class="dosing-lbl">ចន្លោះ<br>Interval</div>'
        + '<input class="dosing-in" type="text" name="meds[' + medIdx + '][interval]" placeholder="q8h"/>'
        + '</div></div></div>';

    list.appendChild(wrapper);
    medIdx++;
}

function dosingCell(idx, field, lkm, len, val) {
    return '<div class="dosing-cell">'
        + '<div class="dosing-lbl">' + lkm + '<br>' + len + '</div>'
        + '<input class="dosing-in" type="number" step="0.5" name="meds[' + idx + '][' + field + ']" value="' + val + '"/>'
        + '</div>';
}
</script>
