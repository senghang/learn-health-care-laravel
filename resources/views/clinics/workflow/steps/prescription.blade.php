@php
    $prescription = $prescription ?? null;
    $medications  = $medications  ?? collect([]);
    $catalog      = $catalog      ?? collect([]);

    $rxCode       = $prescription?->code ?? ('RX-' . $visit->code);
    $prescribedAt = old('prescribed_at', df_input_dt($prescription?->prescribed_at) ?: df_now_input());
    $prescribedBy = old('prescribed_by', $prescription?->prescribed_by ?? auth()->user()?->name ?? '');
    $formOptions  = ['Tablet','Capsule','Syrup','Injection','Ointment','Drops','Inhaler','Powder'];
    $rxCount      = $medications->count();

    // Pre-compute catalog for JS (avoids Blade ParseError with arrow fn inside @json)
    $catalogJs = $catalog->map(fn($c) => [
        'id'       => $c->id,
        'code'     => $c->code,
        'name'     => $c->name,
        'strength' => $c->strength ?? '',
        'form'     => $c->form ?? '',
        'unit'     => $c->unit ?? '',
        'stock'    => (int)($c->stock ?? 0),
        'alert'    => (int)($c->stock_alert ?? 10),
        'price'    => (float)($c->price ?? 0),
    ])->values()->all();
@endphp

<x-step.card step-id="prescription" :visit="$visit" :step-idx="$stepIdx" :steps="$steps"
    icon="bi-capsule-pill" icon-color="#e91e8c"
    km="បញ្ជាថ្នាំ" en="Prescription"
    :badge="$rxCount > 0 ? $rxCount.' Rx saved' : null" badge-color="#e91e8c">

{{-- ══ DOCTOR SECTION ══════════════════════════════════════════════════════ --}}
<div class="rx-section rx-section--doctor">
    <i class="bi bi-person-badge-fill"></i>
    Doctor Section — Prescription Order
</div>

@if($rxCount > 0)
<div class="note note-success mb-3">
    <i class="bi bi-check-circle-fill"></i>
    <div><strong>{{ $rxCount }} medication{{ $rxCount>1?'s':'' }} saved.</strong>
    Submitting replaces all existing medications for this visit.</div>
</div>
@endif

{{-- Header meta --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-sm-4">
        <div class="fld">
            <label class="flbl">
                <span class="km">Rx Code</span>
                <span class="rx-auto-badge">AUTO</span>
            </label>
            <div class="form-control ro" style="font-family:monospace;color:#e91e8c;font-weight:700">{{ $rxCode }}</div>
        </div>
    </div>
    <div class="col-6 col-sm-4">
        <x-form.field name="prescribed_at" km="ថ្ងៃ" en="Prescribed At"
                      type="datetime-local" :value="$prescribedAt"/>
    </div>
    <div class="col-12 col-sm-4">
        <x-form.field name="prescribed_by" km="ផ្ដល់ដោយ" en="Prescribed By"
                      :required="true" placeholder="Dr. Name" :value="$prescribedBy"/>
    </div>
</div>

{{-- Medications label --}}
<div class="rx-med-label">
    <i class="bi bi-capsule-fill"></i> ថ្នាំ / Medications
    <span id="rxCount" class="rx-count-badge">{{ $rxCount }}</span>
</div>

{{-- ── Medication Cards ─────────────────────────────────────────────────── --}}
<div id="rxList">
@forelse($medications as $i => $med)
@php
    $stock     = $catalog->firstWhere('code', $med->medication_code)?->stock ?? null;
    $alert     = $catalog->firstWhere('code', $med->medication_code)?->stock_alert ?? 10;
    $stockCls  = $stock === null ? '' : ($stock <= 0 ? 'stock-out' : ($stock <= $alert ? 'stock-low' : 'stock-ok'));
    $stockLbl  = $stock === null ? '' : ($stock <= 0 ? 'Out of stock' : ($stock <= $alert ? "Low: {$stock}" : "In stock: {$stock}"));
@endphp
<div class="rx-card" id="rx-card-{{ $i }}">
    <div class="rx-card-hd" onclick="toggleCard({{ $i }})">
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <span class="rx-card-icon">💊</span>
            <strong class="rx-card-name">{{ $med->medicine_name ?: ('ថ្នាំ #'.($i+1)) }}</strong>
            @if($med->strength)
                <code class="rx-strength-chip">{{ $med->strength }}</code>
            @endif
            @php
                $doses = array_filter(['M'=>$med->morning,'A'=>$med->afternoon,'E'=>$med->evening,'N'=>$med->night]);
                $doseSummary = collect($doses)->map(fn($v,$k)=>$v.$k)->implode(' · ');
            @endphp
            @if($doseSummary)
                <span class="rx-dose-chip">{{ $doseSummary }} × {{ $med->days ?? '?' }}d</span>
            @endif
            @if($stockCls)
                <span class="stock-badge {{ $stockCls }}">{{ $stockLbl }}</span>
            @endif
        </div>
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-sm btn-outline-danger"
                    onclick="event.stopPropagation();document.getElementById('rx-card-{{ $i }}').remove();updateCount()">
                <i class="bi bi-trash3"></i>
            </button>
            <i class="bi bi-chevron-up rx-chev" id="rx-chev-{{ $i }}"></i>
        </div>
    </div>
    <div class="rx-card-body" id="rx-body-{{ $i }}" style="display:block">
        @include('clinics.workflow.steps._rx-card-body', [
            'i'           => $i,
            'med'         => $med,
            'formOptions' => $formOptions,
            'catalog'     => $catalog,
        ])
    </div>
</div>
@empty
{{-- Empty first card --}}
<div class="rx-card" id="rx-card-0">
    <div class="rx-card-hd" onclick="toggleCard(0)">
        <div class="d-flex align-items-center gap-2">
            <span class="rx-card-icon">💊</span>
            <strong class="rx-card-name">ថ្នាំ #1</strong>
            <span style="font-size:11px;color:#aaa">/ Click to expand</span>
        </div>
        <i class="bi bi-chevron-up rx-chev" id="rx-chev-0"></i>
    </div>
    <div class="rx-card-body" id="rx-body-0" style="display:block">
        @include('clinics.workflow.steps._rx-card-body', [
            'i'           => 0,
            'med'         => null,
            'formOptions' => $formOptions,
            'catalog'     => $catalog,
        ])
    </div>
</div>
@endforelse
</div>

<button type="button" class="btn btn-outline-primary btn-w100 mt-3" onclick="addRxCard()" style="padding:11px">
    <i class="bi bi-plus-circle-fill"></i> បន្ថែមថ្នាំ / Add Medication
</button>

{{-- ══ PHARMACY SECTION ═════════════════════════════════════════════════════ --}}
<div class="rx-section rx-section--pharmacy">
    <i class="bi bi-bag-heart-fill"></i>
    Pharmacy Section — Dispensing Status
</div>

<div class="rx-pharmacy-panel">
    <div class="row g-3 align-items-center">
        <div class="col-12 col-sm-6">
            <div class="fld">
                <label class="flbl" style="color:#7c3aed;font-weight:800">Dispensing Status</label>
                <select name="dispensed_status" class="form-select" style="border-color:#c4b5fd"
                        onchange="updateDispenseNote(this.value)">
                    <option value=""          {{ ($prescription?->dispensed_status ?? '') === '' ? 'selected' : '' }}>— Not dispensed yet</option>
                    <option value="partial"   {{ ($prescription?->dispensed_status ?? '') === 'partial'   ? 'selected' : '' }}>⏳ Partially dispensed</option>
                    <option value="dispensed" {{ ($prescription?->dispensed_status ?? '') === 'dispensed' ? 'selected' : '' }}>✅ Fully dispensed</option>
                </select>
            </div>
        </div>
        <div class="col-12 col-sm-6">
            <div class="fld">
                <label class="flbl" style="color:#7c3aed;font-weight:800">Dispensed By</label>
                <input name="dispensed_by" class="form-control" style="border-color:#c4b5fd"
                       value="{{ old('dispensed_by', $prescription?->dispensed_by ?? '') }}"
                       placeholder="Pharmacist name"/>
            </div>
        </div>
    </div>
    <div id="dispenseNote" style="display:none;margin-top:10px"></div>
</div>

{{-- ══ A4 PRINT BUTTON ═════════════════════════════════════════════════════ --}}
@if($prescription)
<div style="margin-top:16px;text-align:right">
    <a href="{{ route('print.prescription', $prescription->code) }}" target="_blank"
       class="btn btn-outline-secondary" style="font-size:12px">
        <i class="bi bi-printer-fill"></i> Print Prescription (A4)
    </a>
</div>
@endif

</x-step.card>

<style>
.rx-section {
    display:flex;align-items:center;gap:8px;
    color:#fff;font-size:11.5px;font-weight:800;
    padding:9px 16px;border-radius:8px;margin-bottom:14px;
    text-transform:uppercase;letter-spacing:.5px;
}
.rx-section--doctor   { background:#e91e8c; }
.rx-section--pharmacy { background:#7c3aed; margin-top:28px; }
.rx-auto-badge {
    font-size:9px;background:#e8f8ef;color:#1D9E75;
    padding:1px 6px;border-radius:8px;margin-left:4px;font-weight:700;
}
.rx-med-label {
    font-size:11px;font-weight:800;text-transform:uppercase;
    letter-spacing:.6px;color:#e91e8c;margin-bottom:12px;
    display:flex;align-items:center;gap:6px;
}
.rx-count-badge {
    background:#e91e8c22;color:#e91e8c;border:1px solid #e91e8c66;
    font-size:10px;padding:1px 8px;border-radius:10px;
}
.rx-card {
    border:1.5px solid #fce7f3;border-radius:12px;
    margin-bottom:10px;overflow:hidden;
    transition:box-shadow .2s;background:#fff;
}
.rx-card:hover { box-shadow:0 2px 14px rgba(233,30,140,.1); }
.rx-card-hd {
    display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;
    gap:8px;padding:12px 16px;background:#fdf4ff;cursor:pointer;
    border-bottom:1px solid #fce7f3;
}
.rx-card-icon { font-size:18px; }
.rx-card-name { font-size:14px;color:#012970; }
.rx-strength-chip {
    font-size:11px;background:#eef0fd;color:#4154f1;
    padding:1px 7px;border-radius:5px;
}
.rx-dose-chip {
    font-size:10px;background:#f0f2ff;color:#4154f1;
    padding:2px 9px;border-radius:10px;font-weight:700;
}
.rx-chev {
    font-size:14px;color:#aaa;transition:transform .2s;
}
.rx-card-body { padding:16px; }
.stock-badge {
    font-size:10px;padding:2px 8px;border-radius:10px;
    font-weight:700;display:inline-flex;align-items:center;gap:3px;
}
.stock-ok  { background:#e8f8ef;color:#1D9E75;border:1px solid #b7eacf; }
.stock-low { background:#fff8e1;color:#b45309;border:1px solid #fde68a; }
.stock-out { background:#fde8e8;color:#dc2626;border:1px solid #fca5a5; }
.rx-pharmacy-panel {
    background:#f5f3ff;border:1.5px solid #c4b5fd;border-radius:12px;
    padding:16px 18px;
}
.dosing-grid {
    display:grid;grid-template-columns:repeat(6,1fr);gap:8px;
}
.dosing-cell {
    background:#fff;border:1px solid #e6eaf5;border-radius:8px;
    padding:9px 4px;text-align:center;
}
.dosing-lbl {
    font-size:9px;color:#aaa;font-weight:700;
    text-transform:uppercase;letter-spacing:.3px;margin-bottom:4px;
}
.dosing-in {
    border:none;background:transparent;text-align:center;
    font-size:20px;font-weight:800;color:#012970;width:100%;
    outline:none;font-family:'Nunito',sans-serif;
}
@media(max-width:480px){.dosing-grid{grid-template-columns:repeat(3,1fr)}}
</style>

<script>
var rxIdx       = {{ max($rxCount, 1) }};
var formOptions = @json($formOptions);
var rxCatalog   = @json($catalogJs);

function toggleCard(idx) {
    var body = document.getElementById('rx-body-' + idx);
    var chev = document.getElementById('rx-chev-' + idx);
    if (!body) return;
    var open = body.style.display !== 'none';
    body.style.display = open ? 'none' : 'block';
    if (chev) chev.style.transform = open ? 'rotate(-90deg)' : '';
}

function updateCount() {
    var n   = document.querySelectorAll('.rx-card').length;
    var el  = document.getElementById('rxCount');
    if (el) el.textContent = n;
}

function fillFromCatalog(selectEl, idx) {
    var medId = selectEl.value;
    var item  = rxCatalog.find(function(c){ return c.id == medId; });
    if (!item) return;
    setField('meds_name_'+idx,     item.name);
    setField('meds_strength_'+idx, item.strength);
    setField('meds_unit_'+idx,     item.unit);
    // Set hidden medicine_id
    var hidId = document.querySelector('[name="meds['+idx+'][medicine_id]"]');
    if (hidId) hidId.value = item.id;
    // Show stock badge
    updateStockBadge(idx, item);
    // Update form select to matching form
    var formSel = document.querySelector('[name="meds['+idx+'][form]"]');
    if (formSel && item.form) {
        Array.from(formSel.options).forEach(function(o){ o.selected = (o.value === item.form); });
    }
}

function updateStockBadge(idx, item) {
    var badge = document.getElementById('stock-badge-'+idx);
    if (!badge) return;
    if (!item) { badge.textContent = ''; badge.className = 'stock-badge'; return; }
    if (item.stock <= 0) {
        badge.textContent = 'Out of stock';
        badge.className   = 'stock-badge stock-out';
    } else if (item.stock <= item.alert) {
        badge.textContent = 'Low: ' + item.stock;
        badge.className   = 'stock-badge stock-low';
    } else {
        badge.textContent = 'In stock: ' + item.stock;
        badge.className   = 'stock-badge stock-ok';
    }
}

function setField(id, val) {
    var el = document.getElementById(id);
    if (el) el.value = val;
}

function addRxCard() {
    var idx  = rxIdx++;
    var el   = document.createElement('div');
    el.className = 'rx-card';
    el.id = 'rx-card-' + idx;

    var formOpts    = formOptions.map(f => '<option value="'+f+'">'+f+'</option>').join('');
    var catalogOpts = '<option value="">— Select medicine —</option>'
        + rxCatalog.map(c => '<option value="'+c.id+'">'+c.name+' ('+c.strength+' · '+c.form+') — '+(c.stock<=0?'❌ Out':(c.stock<=c.alert?'⚠ Low: '+c.stock:'✓ '+c.stock))+'</option>').join('');

    el.innerHTML =
        '<div class="rx-card-hd" onclick="toggleCard('+idx+')">'
        + '<div class="d-flex align-items-center gap-2 flex-wrap">'
        + '<span class="rx-card-icon">💊</span>'
        + '<strong class="rx-card-name" id="rx-card-title-'+idx+'">ថ្នាំ #'+(idx+1)+'</strong>'
        + '<span id="stock-badge-'+idx+'" class="stock-badge"></span>'
        + '</div>'
        + '<div class="d-flex align-items-center gap-2">'
        + '<button type="button" class="btn btn-sm btn-outline-danger" onclick="event.stopPropagation();this.closest(\'.rx-card\').remove();updateCount()"><i class="bi bi-trash3"></i></button>'
        + '<i class="bi bi-chevron-up rx-chev" id="rx-chev-'+idx+'"></i>'
        + '</div></div>'
        + '<div class="rx-card-body" id="rx-body-'+idx+'" style="display:block">'
        // Catalog selector
        + '<div class="fld mb-3" style="background:#f0f2ff;border-radius:8px;padding:10px 12px">'
        + '<label class="flbl" style="color:#4154f1;font-size:11px;font-weight:800"><i class="bi bi-search"></i> Quick Select from Formulary</label>'
        + '<select class="form-select" onchange="fillFromCatalog(this,'+idx+')" style="border-color:#c5cbf9">'
        + catalogOpts + '</select>'
        + '<input type="hidden" name="meds['+idx+'][medicine_id]"/>'
        + '</div>'
        // Main fields
        + '<div class="row g-3 mb-3">'
        + '<div class="col-12 col-sm-5"><div class="fld"><label class="flbl"><span class="km">ឈ្មោះថ្នាំ</span><span class="en">/ Medicine</span><span class="req">*</span></label>'
        + '<input id="meds_name_'+idx+'" name="meds['+idx+'][medicine_name]" class="form-control" required placeholder="Medicine name"/></div></div>'
        + '<div class="col-6 col-sm-2"><div class="fld"><label class="flbl"><span class="km">កម្លាំង</span></label>'
        + '<input id="meds_strength_'+idx+'" name="meds['+idx+'][strength]" class="form-control" placeholder="500mg"/></div></div>'
        + '<div class="col-6 col-sm-2"><div class="fld"><label class="flbl"><span class="km">ទំរង់</span></label>'
        + '<select name="meds['+idx+'][form]" class="form-select">'+formOpts+'</select></div></div>'
        + '<div class="col-6 col-sm-2"><div class="fld"><label class="flbl"><span class="km">វិធី</span></label>'
        + '<input name="meds['+idx+'][method]" class="form-control" placeholder="Oral"/></div></div>'
        + '<div class="col-6 col-sm-1"><div class="fld"><label class="flbl"><span class="km">ឯកតា</span></label>'
        + '<input id="meds_unit_'+idx+'" name="meds['+idx+'][unit]" class="form-control" placeholder="tab"/></div></div>'
        + '</div>'
        // Dosing
        + '<div style="background:#eef0fd;border-radius:10px;padding:14px">'
        + '<div style="font-size:10px;font-weight:800;color:#4154f1;text-transform:uppercase;margin-bottom:10px"><i class="bi bi-clock"></i> Dosing Schedule</div>'
        + '<div class="dosing-grid">'
        + dCell(idx,'morning','ព្រឹក','Morning',1)
        + dCell(idx,'afternoon','ថ្ងៃ','Afternoon',0)
        + dCell(idx,'evening','ល្ងាច','Evening',1)
        + dCell(idx,'night','យប់','Night',0)
        + dCell(idx,'days','ថ្ងៃ','Days',0)
        + '<div class="dosing-cell"><div class="dosing-lbl">ចន្លោះ<br><span style="font-size:8px;color:#aaa">Interval</span></div>'
        + '<input class="dosing-in" type="text" name="meds['+idx+'][interval]" placeholder="q8h" style="font-size:13px"/></div>'
        + '</div>'
        + '<div class="mt-2"><div class="fld"><label class="flbl"><span class="km">ចំណាំ</span><span class="en">/ Note</span></label>'
        + '<input name="meds['+idx+'][note]" class="form-control" placeholder="Take with food…"/></div></div>'
        + '</div>'
        + '</div>';

    document.getElementById('rxList').appendChild(el);
    el.scrollIntoView({ behavior:'smooth', block:'nearest' });
    updateCount();
}

function dCell(idx, field, lkm, len, val) {
    return '<div class="dosing-cell">'
        + '<div class="dosing-lbl">'+lkm+'<br><span style="font-size:8px;color:#aaa">'+len+'</span></div>'
        + '<input class="dosing-in" type="number" step="0.5" min="0" name="meds['+idx+']['+field+']" value="'+val+'"/>'
        + '</div>';
}

function updateDispenseNote(val) {
    var note = document.getElementById('dispenseNote');
    if (val === 'dispensed') {
        note.style.display = 'block';
        note.innerHTML = '<div class="note note-success" style="font-size:12px"><i class="bi bi-check-circle-fill"></i>&nbsp;All medications dispensed. "Complete Visit" will be unlocked on the Invoice step.</div>';
    } else if (val === 'partial') {
        note.style.display = 'block';
        note.innerHTML = '<div class="note note-warn" style="font-size:12px"><i class="bi bi-exclamation-triangle-fill"></i>&nbsp;Partial — complete dispense required before closing visit.</div>';
    } else {
        note.style.display = 'none';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // Init dispense note
    var sel = document.querySelector('[name="dispensed_status"]');
    if (sel && sel.value) updateDispenseNote(sel.value);

    // Open all saved cards
    document.querySelectorAll('.rx-card-body').forEach(function(b){ b.style.display='block'; });
});
</script>
