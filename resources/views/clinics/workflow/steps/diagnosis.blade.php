@php
    $diagnoses   = $diagnoses ?? collect([]);
    $nowFormatted = df_now_input();
    $currentUser  = auth()->user()?->name ?? '';
    $typeOptions  = ['Primary' => 'Primary', 'Secondary' => 'Secondary', 'In' => 'In', 'Out' => 'Out'];
    $diagCount    = $diagnoses->count();
@endphp

<x-step.card step-id="diagnosis" :visit="$visit" :step-idx="$stepIdx" :steps="$steps"
    icon="bi-bullseye" icon-color="#e74c3c"
    km="រោគវិនិច្ឆ័យ" en="Diagnosis"
    :badge="$diagCount > 0 ? $diagCount . ' Dx saved' : null"
    badge-color="#e74c3c">

    <div class="d-flex justify-content-end mb-3">
        <button type="button" class="btn btn-sm btn-outline-primary" onclick="addDxRow()">
            <i class="bi bi-plus"></i> Dx បន្ថែម / Add Diagnosis
        </button>
    </div>

    <div id="diagnosisList">
        @forelse($diagnoses as $i => $dx)

        @include('clinics.workflow.steps._dx-row', [
            'i'           => $i,
            'dx'          => $dx,
            'typeOptions' => $typeOptions,
            'nowFormatted'=> $nowFormatted,
            'currentUser' => $currentUser,
        ])

        @empty

        @include('clinics.workflow.steps._dx-row', [
            'i'           => 0,
            'dx'          => null,
            'typeOptions' => $typeOptions,
            'nowFormatted'=> $nowFormatted,
            'currentUser' => $currentUser,
        ])

        @endforelse
    </div>

</x-step.card>

<script>
const DX_NOW   = '{{ $nowFormatted }}';
const DX_USER  = '{{ addslashes($currentUser) }}';
const DX_TYPES = @json(array_keys($typeOptions));
let dxIdx      = {{ max($diagCount, 1) }};

function addDxRow() {
    var idx  = dxIdx++;
    var opts = DX_TYPES.map(t => '<option value="' + t + '">' + t + '</option>').join('');
    var html = `<div class="sec-block dx-block" data-idx="${idx}" style="border-left:4px solid #e74c3c44;background:#fde8e808;border-radius:10px;padding:14px 16px;margin-bottom:10px">
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:12px">
            <span style="width:9px;height:9px;border-radius:50%;background:#e74c3c;display:inline-block;flex-shrink:0"></span>
            <strong style="color:#c0392b;font-size:13px">Dx #${idx + 1}</strong>
            <button type="button" class="btn btn-sm btn-outline-danger ms-auto" onclick="removeDxRow(${idx})" style="font-size:11px;padding:2px 8px">✕ លុប</button>
        </div>
        <div class="row g-3">
            <div class="col-6 col-sm-3"><div class="fld"><label class="flbl"><span class="km">ប្រភេទ</span><span class="en">/ Type</span><span class="req">*</span></label>
                <select name="diagnoses[${idx}][type]" class="form-select">${opts}</select></div></div>
            <div class="col-6 col-sm-2"><div class="fld"><label class="flbl">ICD-10</label>
                <input name="diagnoses[${idx}][code]" class="form-control" placeholder="B50.0"/></div></div>
            <div class="col-12 col-sm-7"><div class="fld"><label class="flbl"><span class="km">ឈ្មោះ</span><span class="en">/ Name</span><span class="req">*</span></label>
                <input name="diagnoses[${idx}][name]" class="form-control" placeholder="Diagnosis name"/></div></div>
            <div class="col-6"><div class="fld"><label class="flbl"><span class="km">ពេលវេលា</span><span class="en">/ At</span></label>
                <input type="datetime-local" name="diagnoses[${idx}][diagnosed_at]" class="form-control" value="${DX_NOW}"/></div></div>
            <div class="col-6"><div class="fld"><label class="flbl"><span class="km">ដោយ</span><span class="en">/ By</span></label>
                <input name="diagnoses[${idx}][diagnosed_by]" class="form-control" value="${DX_USER}"/></div></div>
            <div class="col-12"><div class="fld"><label class="flbl"><span class="km">ការពណ៌នា</span><span class="en">/ Description</span></label>
                <input name="diagnoses[${idx}][description]" class="form-control"/></div></div>
        </div>
    </div>`;
    var el = document.createElement('div');
    el.innerHTML = html;
    document.getElementById('diagnosisList').appendChild(el.firstChild);
}

function removeDxRow(idx) {
    var el = document.querySelector('.dx-block[data-idx="' + idx + '"]');
    if (el) el.remove();
}
</script>
