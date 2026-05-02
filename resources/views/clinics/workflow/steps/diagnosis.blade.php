{{--
    Step: Diagnosis (រោគវិនិច្ឆ័យ)
    Variables from DiagnosisStep::viewData():
      $diagnoses — Collection<DiagnosisModel>
      $visit, $stepIdx, $steps
--}}
@php
    $existingDiags = old('diagnoses', $diagnoses->map(fn($d) => [
        'type'         => $d->diagnosis_type,
        'code'         => $d->diagnosis_code,
        'name'         => $d->diagnosis_name,
        'description'  => $d->diagnosis_description,
        'diagnosed_by' => $d->diagnosed_by,
    ])->toArray());

    $hasPrimary = collect($existingDiags)->contains('type', 'Primary');

    $typeCfg = [
        'Primary'   => ['color' => '#e74c3c', 'bg' => '#fde8e8', 'border' => '#e74c3c44', 'icon' => '🔴'],
        'Secondary' => ['color' => '#4154f1', 'bg' => '#eef0fd', 'border' => '#4154f144', 'icon' => '🔵'],
        'In'        => ['color' => '#2eca6a', 'bg' => '#e8f8ef', 'border' => '#2eca6a44', 'icon' => '🟢'],
        'Out'       => ['color' => '#64748b', 'bg' => '#f1f5f9', 'border' => '#64748b44', 'icon' => '⚪'],
    ];
@endphp

<div class="card-hd" style="flex-wrap:wrap;gap:8px;padding:14px 18px 10px">
    <div style="flex:1;min-width:0">
        <div class="card-hd-title">
            <i class="bi bi-bullseye" style="color:#e74c3c"></i>
            រោគវិនិច្ឆ័យ
            <small style="font-size:11px;color:#bbb;font-weight:400">/ Diagnosis</small>
        </div>
        <div style="font-size:10.5px;color:#aaa;margin-top:3px">
            ជំហាន {{ $stepIdx+1 }} នៃ {{ count($steps) }} / Step {{ $stepIdx+1 }} of {{ count($steps) }}
        </div>
    </div>
    <div style="display:flex;gap:6px;align-items:center;flex-shrink:0">
        @if($diagnoses->isNotEmpty())
        <span style="font-size:11px;background:#fce4ec;color:#e74c3c;padding:3px 10px;border-radius:20px;border:1px solid #f8bbd0;font-weight:700">
            <i class="bi bi-check-circle-fill"></i>
            {{ $diagnoses->count() }} diagnos{{ $diagnoses->count() > 1 ? 'es' : 'is' }} saved
        </span>
        @endif
    </div>
</div>

<div style="height:3px;background:#f0f2ff">
    <div style="height:100%;width:{{ round($stepIdx/count($steps)*100) }}%;background:linear-gradient(90deg,#4154f1,#717ff5)"></div>
</div>

<div class="card-bd">

    {{-- Primary diagnosis required notice --}}
    @if($diagnoses->isEmpty())
    <div class="note note-info mb-3">
        <i class="bi bi-info-circle-fill"></i>
        <div>បញ្ចូលរោគវិនិច្ឆ័យ ICD-10 / Add at least one <strong>Primary</strong> diagnosis. Additional rows can be Secondary or comorbidities.</div>
    </div>
    @elseif(!$hasPrimary)
    <div class="note note-danger mb-3">
        <i class="bi bi-exclamation-triangle-fill"></i>
        <div><strong>No Primary diagnosis set.</strong> The first or most important diagnosis must be typed as Primary.</div>
    </div>
    @endif

    {{-- No-primary JS warning (shown live) --}}
    <div id="noPrimaryWarn" style="display:none;background:#fde8e8;border:1px solid #e74c3c44;border-radius:8px;padding:8px 12px;margin-bottom:12px;font-size:12px;font-weight:600;color:#e74c3c;align-items:center;gap:8px">
        <i class="bi bi-exclamation-triangle-fill"></i>
        No Primary diagnosis — please mark at least one row as Primary before saving.
    </div>

    <form id="stepForm" method="POST" action="{{ route('workflow.step.save', [$visit->code, 'diagnosis']) }}">
        @csrf @method('PATCH')

        {{-- Type legend --}}
        <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:12px;align-items:center">
            <span style="font-size:10px;color:#94a3b8;font-weight:700;text-transform:uppercase;letter-spacing:.4px">Types:</span>
            @foreach($typeCfg as $t => $tc)
            <span style="font-size:10.5px;background:{{ $tc['bg'] }};color:{{ $tc['color'] }};border:1px solid {{ $tc['border'] }};padding:2px 8px;border-radius:6px;font-weight:700">
                {{ $tc['icon'] }} {{ $t }}
            </span>
            @endforeach
        </div>

        <div id="diagContainer">
            @forelse($existingDiags as $i => $diag)
                @include('clinics.workflow.steps._diagnosis_row', ['idx' => $i, 'diag' => $diag])
            @empty
                @include('clinics.workflow.steps._diagnosis_row', [
                    'idx'  => 0,
                    'diag' => ['type' => 'Primary', 'code' => '', 'name' => '', 'description' => '', 'diagnosed_by' => auth()->user()?->name ?? '']
                ])
            @endforelse
        </div>

        <div style="padding-top:12px;border-top:1px solid #f0f2ff;margin-top:8px;display:flex;align-items:center;gap:8px">
            <button type="button" class="btn btn-sm btn-outline-primary" onclick="addDiagRow()">
                <i class="bi bi-plus-lg"></i> បន្ថែម / Add Diagnosis
            </button>
            <span id="diagCountBadge" style="font-size:11px;color:#94a3b8">
                <span id="diagCountNum">{{ count($existingDiags) ?: 1 }}</span> entr{{ count($existingDiags) === 1 ? 'y' : 'ies' }}
            </span>
        </div>
    </form>

</div>

@push('scripts')
<script>
var diagIdx    = {{ count($existingDiags) ?: 1 }};
var doctorName = {{ json_encode(auth()->user()?->name ?? '') }};

var DIAG_TYPE_CFG = {
    Primary:   { color:'#e74c3c', bg:'#fde8e8', border:'#e74c3c44' },
    Secondary: { color:'#4154f1', bg:'#eef0fd', border:'#4154f144' },
    In:        { color:'#2eca6a', bg:'#e8f8ef', border:'#2eca6a44' },
    Out:       { color:'#64748b', bg:'#f1f5f9', border:'#64748b44' },
};

function applyDiagRowStyle(row) {
    var sel = row.querySelector('[name*="[type]"]');
    if (!sel) return;
    var cfg = DIAG_TYPE_CFG[sel.value] || DIAG_TYPE_CFG.Secondary;
    row.style.borderColor = cfg.border;
    row.style.background  = cfg.bg;
    var badge = row.querySelector('.diag-type-badge');
    if (badge) {
        badge.style.background = cfg.bg;
        badge.style.color      = cfg.color;
        badge.style.border     = '1px solid ' + cfg.border;
    }
}

function checkPrimary() {
    var types = Array.from(document.querySelectorAll('[name*="[type]"]')).map(function(s){ return s.value; });
    var hasPrimary = types.indexOf('Primary') !== -1;
    var warn = document.getElementById('noPrimaryWarn');
    if (warn) warn.style.display = hasPrimary ? 'none' : 'flex';
    return hasPrimary;
}

function updateDiagCount() {
    var n   = document.querySelectorAll('.diag-row').length;
    var el  = document.getElementById('diagCountNum');
    if (el) el.textContent = n;
}

function addDiagRow() {
    var container = document.getElementById('diagContainer');
    var i = diagIdx;
    var div = document.createElement('div');
    div.className = 'diag-row';
    div.style.cssText = 'background:#eef0fd;border-radius:10px;padding:14px;margin-bottom:10px;border:1.5px solid #4154f144;position:relative;transition:border-color .2s,background .2s';
    div.innerHTML = `
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px">
            <span class="diag-type-badge" style="font-size:10px;font-weight:700;padding:2px 8px;border-radius:6px;background:#eef0fd;color:#4154f1;border:1px solid #4154f144">Secondary</span>
            <span style="font-size:10px;color:#94a3b8;margin-left:auto">Row ${i+1}</span>
            <button type="button" class="btn btn-sm btn-outline-danger" style="padding:2px 8px;font-size:11px"
                    onclick="this.closest('.diag-row').remove();checkPrimary();updateDiagCount()">
                <i class="bi bi-trash"></i>
            </button>
        </div>
        <div class="row g-2">
            <div class="col-6 col-sm-3">
                <div class="fld">
                    <label class="flbl"><span class="km">ប្រភេទ</span><span class="en">/ Type</span> <span class="req">*</span></label>
                    <select name="diagnoses[${i}][type]" class="form-select" required
                            onchange="applyDiagRowStyle(this.closest('.diag-row'));checkPrimary()">
                        <option value="Secondary">🔵 Secondary</option>
                        <option value="Primary">🔴 Primary</option>
                        <option value="In">🟢 In</option>
                        <option value="Out">⚪ Out</option>
                    </select>
                </div>
            </div>
            <div class="col-6 col-sm-3">
                <div class="fld">
                    <label class="flbl"><span class="en">ICD-10 Code</span></label>
                    <input name="diagnoses[${i}][code]" class="form-control" placeholder="e.g. J06.9"
                           style="font-family:monospace"/>
                </div>
            </div>
            <div class="col-12 col-sm-6">
                <div class="fld">
                    <label class="flbl"><span class="km">ឈ្មោះរោគ</span><span class="en">/ Diagnosis Name</span> <span class="req">*</span></label>
                    <input name="diagnoses[${i}][name]" class="form-control" required placeholder="e.g. Acute upper respiratory infection"/>
                </div>
            </div>
            <div class="col-12 col-sm-8">
                <div class="fld">
                    <label class="flbl"><span class="km">ការពិពណ៌នា</span><span class="en">/ Description (optional)</span></label>
                    <input name="diagnoses[${i}][description]" class="form-control" placeholder="Additional clinical notes"/>
                </div>
            </div>
            <div class="col-12 col-sm-4">
                <div class="fld">
                    <label class="flbl"><span class="km">វិនិច្ឆ័យដោយ</span><span class="en">/ Diagnosed By</span></label>
                    <input name="diagnoses[${i}][diagnosed_by]" class="form-control" value="${doctorName}"/>
                </div>
            </div>
        </div>`;
    container.appendChild(div);
    diagIdx++;
    checkPrimary();
    updateDiagCount();
}

// Validate primary on form submit
document.addEventListener('DOMContentLoaded', function() {
    var form = document.getElementById('stepForm');
    if (form) {
        // Apply initial styles
        document.querySelectorAll('.diag-row').forEach(applyDiagRowStyle);
        checkPrimary();

        // Re-apply styles on type change
        document.getElementById('diagContainer')?.addEventListener('change', function(e) {
            if (e.target.name && e.target.name.includes('[type]')) {
                applyDiagRowStyle(e.target.closest('.diag-row'));
                checkPrimary();
            }
        });

        // Block submit if no primary
        form.addEventListener('submit', function(e) {
            if (!checkPrimary()) {
                e.preventDefault();
                document.getElementById('noPrimaryWarn').scrollIntoView({behavior:'smooth',block:'center'});
            }
        }, true);
    }
});
</script>
@endpush
