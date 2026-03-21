@php
    $prescription = $prescription ?? null;
    $medications  = $medications  ?? collect([]);

    $rxCode       = $prescription?->code ?? ('RX-' . $visit->code);
    $prescribedAt = old('prescribed_at', df_input_dt($prescription?->prescribed_at) ?: df_now_input());
    $prescribedBy = old('prescribed_by', $prescription?->prescribed_by ?? auth()->user()?->name ?? '');
    $formOptions  = ['Tablet' => 'Tablet', 'Capsule' => 'Capsule', 'Syrup' => 'Syrup', 'Injection' => 'Injection', 'Ointment' => 'Ointment', 'Drops' => 'Drops'];
    $rxCount      = $medications->count();
@endphp

<x-step.card step-id="prescription" :visit="$visit" :step-idx="$stepIdx" :steps="$steps"
    icon="bi-capsule-pill" icon-color="#e91e8c"
    km="បញ្ជាថ្នាំ" en="Prescription"
    :badge="$rxCount > 0 ? $rxCount . ' Rx saved' : null"
    badge-color="#e91e8c">

    @if($rxCount > 0)
    <x-step.note type="success">
        <strong>{{ $rxCount }} medication{{ $rxCount > 1 ? 's' : '' }} saved.</strong>
        Submitting this form replaces all existing medications.
    </x-step.note>
    @endif

    {{-- Header meta --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-sm-4">
            <x-form.field name="_rx_code" km="Rx Code" :value="$rxCode" :readonly="true"/>
        </div>
        <div class="col-6 col-sm-4">
            <x-form.field name="prescribed_at" km="ថ្ងៃ" en="Prescribed At"
                          type="datetime-local" :value="$prescribedAt"/>
        </div>
        <div class="col-12 col-sm-4">
            <x-form.field name="prescribed_by" km="ផ្ដល់ដោយ" en="Prescribed By"
                          :required="true" error-msg="Prescribed By"
                          placeholder="Dr. Name" :value="$prescribedBy"/>
        </div>
    </div>

    <div style="font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.6px;color:#e91e8c;margin-bottom:10px">
        ថ្នាំ / Medications
    </div>

    {{-- Medication rows --}}
    <div id="rxList">
        @if($medications->isNotEmpty())
            @foreach($medications as $i => $med)
            <div class="rx-row" id="rx-row-{{ $i }}">
                @include('clinics.workflow.steps._rx-row', [
                    'i'           => $i,
                    'med'         => $med,
                    'formOptions' => $formOptions,
                ])
            </div>
            @endforeach
        @else
            <div class="rx-row" id="rx-row-0">
                @include('clinics.workflow.steps._rx-row', [
                    'i'           => 0,
                    'med'         => null,
                    'formOptions' => $formOptions,
                ])
            </div>
        @endif
    </div>

    <button type="button" class="btn btn-outline-primary btn-w100 mt-2" onclick="addMedRow()">
        <i class="bi bi-plus-circle"></i> បន្ថែមថ្នាំ / Add Medication
    </button>

</x-step.card>

<script>
var medIdx      = {{ max($rxCount, 1) }};
var formOptions = @json(array_keys($formOptions));

function addMedRow() {
    var idx     = medIdx++;
    var list    = document.getElementById('rxList');
    var wrapper = document.createElement('div');
    wrapper.className = 'rx-row';
    wrapper.id = 'rx-row-' + idx;
    var opts = formOptions.map(f => '<option value="' + f + '">' + f + '</option>').join('');

    wrapper.innerHTML =
        '<div class="rx-row-hd">'
        + '<div class="d-flex align-items-center gap-2"><span style="font-size:18px">💊</span><strong>ថ្នាំ #' + (idx + 1) + '</strong></div>'
        + '<button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest(\'.rx-row\').remove()">✕ លុប</button>'
        + '</div>'
        + '<div class="row g-3 mb-3">'
        + '<div class="col-12 col-sm-6 col-md-3"><div class="fld"><label class="flbl"><span class="km">ឈ្មោះថ្នាំ</span> / Medicine <span class="req">*</span></label>'
        + '<input name="meds[' + idx + '][medicine_name]" class="form-control" placeholder="Medicine name"/></div></div>'
        + '<div class="col-6 col-md-2"><div class="fld"><label class="flbl">កម្លាំង / Strength</label>'
        + '<input name="meds[' + idx + '][strength]" class="form-control" placeholder="500mg"/></div></div>'
        + '<div class="col-6 col-md-2"><div class="fld"><label class="flbl">ទំរង់ / Form</label>'
        + '<select name="meds[' + idx + '][form]" class="form-select">' + opts + '</select></div></div>'
        + '<div class="col-6 col-md-2"><div class="fld"><label class="flbl">វិធី / Method</label>'
        + '<input name="meds[' + idx + '][method]" class="form-control" placeholder="Oral"/></div></div>'
        + '<div class="col-6 col-md-3"><div class="fld"><label class="flbl">ចំណាំ / Note</label>'
        + '<input name="meds[' + idx + '][note]" class="form-control"/></div></div>'
        + '</div>'
        + '<div style="background:#eef0fd;border-radius:10px;padding:12px">'
        + '<div style="font-size:10px;font-weight:800;color:#4154f1;text-transform:uppercase;margin-bottom:10px">កាលវិភាគ / Dosing</div>'
        + '<div class="dosing-grid">'
        + dosingCell(idx,'morning','ព្រឹក','Morning',1)
        + dosingCell(idx,'afternoon','ថ្ងៃ','Afternoon',0)
        + dosingCell(idx,'evening','ល្ងាច','Evening',1)
        + dosingCell(idx,'night','យប់','Night',0)
        + dosingCell(idx,'days','ថ្ងៃ','Days',0)
        + '<div class="dosing-cell"><div class="dosing-lbl">ចន្លោះ<br>Interval</div>'
        + '<input class="dosing-in" type="text" name="meds[' + idx + '][interval]" placeholder="q8h"/></div>'
        + '</div></div>';
    list.appendChild(wrapper);
    medIdx++;
}

function dosingCell(idx, field, lkm, len, val) {
    return '<div class="dosing-cell"><div class="dosing-lbl">' + lkm + '<br>' + len + '</div>'
        + '<input class="dosing-in" type="number" step="0.5" name="meds[' + idx + '][' + field + ']" value="' + val + '"/></div>';
}
</script>
