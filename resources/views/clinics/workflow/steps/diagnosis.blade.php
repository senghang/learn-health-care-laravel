@php
    $progressPct   = round($stepIdx / count($steps) * 100);
    $progressWidth = $progressPct . '%';
    $saveUrl       = url('/workflow/' . $visit->code . '/diagnosis/save');
    $nowFormatted  = now()->format('Y-m-d\TH:i');
    $currentUser   = auth()->user()?->name ?? '';
    $typeOptions   = ['Primary', 'Secondary', 'In', 'Out'];
    $diagCount     = $diagnoses->count();
@endphp

<div class="card-hd" style="flex-wrap:wrap;gap:8px;padding:14px 18px 10px">
    <div style="flex:1;min-width:0">
        <div class="card-hd-title">
            <i class="bi bi-bullseye" style="color:#e74c3c"></i>រោគវិនិច្ឆ័យ
            <small style="font-size:11px;color:#bbb;font-weight:400">/ Diagnosis</small>
        </div>
        <div style="font-size:10.5px;color:#aaa;margin-top:3px">
            ជំហាន {{ $stepIdx+1 }} នៃ {{ count($steps) }} / Step {{ $stepIdx+1 }} of {{ count($steps) }}
        </div>
    </div>
    @if($diagCount > 0)
    <span style="font-size:11px;background:#fde8e8;color:#e74c3c;padding:3px 10px;border-radius:20px;border:1px solid #f5c0c0;font-weight:700;flex-shrink:0">
        {{ $diagCount }} Dx saved
    </span>
    @endif
</div>

<div style="height:3px;background:#f0f2ff">
    <div style="height:100%;width:{{ $progressWidth }};background:linear-gradient(90deg,#4154f1,#717ff5)"></div>
</div>

<div class="card-bd">
    <form id="stepForm" method="POST" action="{{ $saveUrl }}">
        @csrf
        @method('PATCH')

        <div class="d-flex justify-content-end mb-3">
            <button type="button" class="btn btn-sm btn-outline-primary" onclick="addDxRow()">
                <i class="bi bi-plus"></i> Dx បន្ថែម / Add Diagnosis
            </button>
        </div>

        <div id="diagnosisList">
            @if($diagnoses->isNotEmpty())
                @foreach($diagnoses as $i => $dx)
                @php
                    $typeLabel = match($dx->diagnosis_type) {
                        'Primary'   => 'ចម្បង / Primary',
                        'Secondary' => 'ទ្វីបដ្ឋ / Secondary',
                        default     => $dx->diagnosis_type,
                    };
                    $dxCode = $dx->diagnosis_code ?? '';
                @endphp
                <div class="sec-block dx-block" data-idx="{{ $i }}"
                     style="border-left:4px solid #e74c3c;background:#fde8e808;border-radius:10px;padding:14px 16px;margin-bottom:10px">
                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:12px">
                        <span style="width:9px;height:9px;border-radius:50%;background:#e74c3c;display:inline-block;flex-shrink:0"></span>
                        <strong style="color:#c0392b;font-size:13px">{{ $typeLabel }} — Dx #{{ $i + 1 }}</strong>
                        @if($dxCode)
                        <code style="background:#e74c3c;color:#fff;padding:2px 9px;border-radius:20px;font-size:11px">{{ $dxCode }}</code>
                        @endif
                        <button type="button" class="btn btn-sm btn-outline-danger ms-auto"
                                onclick="removeDxRow({{ $i }})" style="font-size:11px;padding:2px 8px">✕ លុប</button>
                    </div>
                    <div class="row g-3">
                        <div class="col-6 col-sm-3">
                            <div class="fld">
                                <label class="flbl"><span class="km">ប្រភេទ</span><span class="en">/ Type</span><span class="req">*</span></label>
                                <select name="diagnoses[{{ $i }}][type]" class="form-select">
                                    @foreach($typeOptions as $t)
                                    @php $sel = $dx->diagnosis_type === $t ? 'selected' : ''; @endphp
                                    <option value="{{ $t }}" {{ $sel }}>{{ $t }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-6 col-sm-2">
                            <div class="fld">
                                <label class="flbl"><span class="en">ICD-10</span></label>
                                <input name="diagnoses[{{ $i }}][code]" class="form-control" value="{{ $dxCode }}" placeholder="B50.0"/>
                            </div>
                        </div>
                        <div class="col-12 col-sm-7">
                            <div class="fld">
                                <label class="flbl"><span class="km">ឈ្មោះ</span><span class="en">/ Name</span><span class="req">*</span></label>
                                <input name="diagnoses[{{ $i }}][name]" class="form-control" value="{{ $dx->diagnosis_name }}"/>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="fld">
                                <label class="flbl"><span class="km">ពេលវេលា</span><span class="en">/ At</span></label>
                                <input type="datetime-local" name="diagnoses[{{ $i }}][diagnosed_at]" class="form-control"
                                       value="{{ $dx->diagnosed_at?->format('Y-m-d\TH:i') ?? $nowFormatted }}"/>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="fld">
                                <label class="flbl"><span class="km">ដោយ</span><span class="en">/ By</span></label>
                                <input name="diagnoses[{{ $i }}][diagnosed_by]" class="form-control"
                                       value="{{ $dx->diagnosed_by ?? $currentUser }}"/>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="fld">
                                <label class="flbl"><span class="km">ការពណ៌នា</span><span class="en">/ Description</span></label>
                                <input name="diagnoses[{{ $i }}][description]" class="form-control"
                                       value="{{ $dx->diagnosis_description }}"/>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            @else
            {{-- Default empty first row --}}
            <div class="sec-block dx-block" data-idx="0"
                 style="border-left:4px solid #e74c3c;background:#fde8e808;border-radius:10px;padding:14px 16px;margin-bottom:10px">
                <div style="display:flex;align-items:center;gap:8px;margin-bottom:12px">
                    <span style="width:9px;height:9px;border-radius:50%;background:#e74c3c;display:inline-block"></span>
                    <strong style="color:#c0392b;font-size:13px">ចម្បង / Primary — Dx #1</strong>
                </div>
                <div class="row g-3">
                    <div class="col-6 col-sm-3">
                        <div class="fld">
                            <label class="flbl"><span class="km">ប្រភេទ</span><span class="en">/ Type</span><span class="req">*</span></label>
                            <select name="diagnoses[0][type]" class="form-select">
                                @foreach($typeOptions as $t)
                                @php $sel = $t === 'Primary' ? 'selected' : ''; @endphp
                                <option value="{{ $t }}" {{ $sel }}>{{ $t }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-6 col-sm-2">
                        <div class="fld">
                            <label class="flbl"><span class="en">ICD-10</span></label>
                            <input name="diagnoses[0][code]" class="form-control" placeholder="B50.0"/>
                        </div>
                    </div>
                    <div class="col-12 col-sm-7">
                        <div class="fld">
                            <label class="flbl"><span class="km">ឈ្មោះ</span><span class="en">/ Name</span><span class="req">*</span></label>
                            <input name="diagnoses[0][name]" class="form-control" placeholder="Plasmodium falciparum malaria" required data-error-msg="Diagnosis Name"/>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="fld">
                            <label class="flbl"><span class="km">ពេលវេលា</span><span class="en">/ At</span></label>
                            <input type="datetime-local" name="diagnoses[0][diagnosed_at]" class="form-control" value="{{ $nowFormatted }}"/>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="fld">
                            <label class="flbl"><span class="km">ដោយ</span><span class="en">/ By</span></label>
                            <input name="diagnoses[0][diagnosed_by]" class="form-control" value="{{ $currentUser }}"/>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="fld">
                            <label class="flbl"><span class="km">ការពណ៌នា</span><span class="en">/ Description</span></label>
                            <input name="diagnoses[0][description]" class="form-control"/>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>

    </form>
</div>

<script>
const DX_NOW  = '{{ $nowFormatted }}';
const DX_USER = '{{ addslashes($currentUser) }}';
const DX_TYPES = @json($typeOptions);
let dxIdx = {{ max($diagnoses->count(), 1) }};

function addDxRow() {
    var idx  = dxIdx++;
    var list = document.getElementById('diagnosisList');
    var opts = DX_TYPES.map(function(t) {
        return '<option value="' + t + '">' + t + '</option>';
    }).join('');
    var html = '<div class="sec-block dx-block" data-idx="' + idx + '" style="border-left:4px solid #e74c3c;background:#fde8e808;border-radius:10px;padding:14px 16px;margin-bottom:10px">'
        + '<div style="display:flex;align-items:center;gap:8px;margin-bottom:12px">'
        + '<span style="width:9px;height:9px;border-radius:50%;background:#e74c3c;display:inline-block"></span>'
        + '<strong style="color:#c0392b;font-size:13px">Dx #' + (idx + 1) + '</strong>'
        + '<button type="button" class="btn btn-sm btn-outline-danger ms-auto" onclick="removeDxRow(' + idx + ')" style="font-size:11px;padding:2px 8px">✕ លុប</button>'
        + '</div>'
        + '<div class="row g-3">'
        + '<div class="col-6 col-sm-3"><div class="fld"><label class="flbl">ប្រភេទ / Type<span class="req">*</span></label>'
        + '<select name="diagnoses[' + idx + '][type]" class="form-select">' + opts + '</select></div></div>'
        + '<div class="col-6 col-sm-2"><div class="fld"><label class="flbl">ICD-10</label>'
        + '<input name="diagnoses[' + idx + '][code]" class="form-control" placeholder="B50.0"/></div></div>'
        + '<div class="col-12 col-sm-7"><div class="fld"><label class="flbl">ឈ្មោះ / Name<span class="req">*</span></label>'
        + '<input name="diagnoses[' + idx + '][name]" class="form-control" placeholder="Diagnosis name"/></div></div>'
        + '<div class="col-6"><div class="fld"><label class="flbl">ពេលវេលា / At</label>'
        + '<input type="datetime-local" name="diagnoses[' + idx + '][diagnosed_at]" class="form-control" value="' + DX_NOW + '"/></div></div>'
        + '<div class="col-6"><div class="fld"><label class="flbl">ដោយ / By</label>'
        + '<input name="diagnoses[' + idx + '][diagnosed_by]" class="form-control" value="' + DX_USER + '"/></div></div>'
        + '<div class="col-12"><div class="fld"><label class="flbl">ការពណ៌នា / Description</label>'
        + '<input name="diagnoses[' + idx + '][description]" class="form-control"/></div></div>'
        + '</div></div>';
    var el = document.createElement('div');
    el.innerHTML = html;
    list.appendChild(el.firstChild);
}

function removeDxRow(idx) {
    var el = document.querySelector('.dx-block[data-idx="' + idx + '"]');
    if (el) el.remove();
}
</script>
