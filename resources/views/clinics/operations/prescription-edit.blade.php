@extends('clinics.layout.app')
@section('title', 'Edit ' . $prescription->code)

@php
    $formOptions  = $formOptions ?? ['Tablet','Capsule','Syrup','Injection','Ointment','Drops','Inhaler','Powder'];
    $catalog      = $catalog ?? collect([]);
    $medications  = $prescription->medications ?? collect([]);
    $rxCount      = $medications->count();

    $catalogJs = $catalog->map(fn($c) => [
        'code'     => $c->code,
        'name'     => $c->name,
        'strength' => $c->strength ?? '',
        'form'     => $c->form     ?? '',
        'unit'     => $c->unit     ?? '',
        'stock'    => (int)($c->stock       ?? 0),
        'alert'    => (int)($c->stock_alert ?? 10),
        'price'    => (float)($c->price     ?? 0),
    ])->values()->all();
@endphp

@section('content')

<x-page-header
    title="Edit {{ $prescription->code }}"
    subtitle="កែប្រែបញ្ជាថ្នាំ"
    :breadcrumbs="[
        ['label' => __('app.home'), 'url' => route('dashboard')],
        ['label' => __('app.prescriptions'), 'url' => route('prescriptions.index')],
        ['label' => $prescription->code, 'url' => route('prescriptions.show', $prescription->code)],
        ['label' => 'Edit'],
    ]">
    <a href="{{ route('prescriptions.show', $prescription->code) }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Back
    </a>
</x-page-header>

@if($errors->any())
    <div class="alert alert-danger mb-3">
        <ul class="mb-0">
            @foreach($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('prescriptions.update', $prescription->code) }}" id="rxForm">
@csrf
@method('PATCH')

<div class="row g-3">

    {{-- ── LEFT COLUMN: Patient info (read-only) + Doctor + Medications ─────── --}}
    <div class="col-12 col-lg-8">

        {{-- Patient (read-only on edit) --}}
        @if($prescription->patient)
        @php $pt = $prescription->patient; @endphp
        <div class="card-emr mb-3">
            <div class="card-hd" style="background:#f6f9ff">
                <div class="card-hd-title">
                    <i class="bi bi-person-vcard-fill" style="color:#4154f1"></i>
                    Patient / អ្នកជំងឺ
                </div>
                <a href="{{ route('patients.show', $pt->code) }}" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-person-fill"></i> View
                </a>
            </div>
            <div class="card-bd">
                @php
                    $colors = ['#4154f1','#2eca6a','#ff771d','#e74c3c','#9b59b6'];
                    $col    = $colors[abs(crc32($pt->code)) % count($colors)];
                @endphp
                <div style="display:flex;align-items:center;gap:12px">
                    <div style="width:42px;height:42px;border-radius:12px;background:{{ $col }};color:#fff;display:flex;align-items:center;justify-content:center;font-size:15px;font-weight:800;flex-shrink:0">
                        {{ strtoupper(substr($pt->surname,0,1) . substr($pt->name,0,1)) }}
                    </div>
                    <div>
                        <div style="font-weight:800;color:#012970;font-size:15px">{{ $pt->surname }}, {{ $pt->name }}</div>
                        <code style="font-size:11px;color:#4154f1">{{ $pt->code }}</code>
                        @if($pt->phone) <span style="font-size:11px;color:#aaa"> · {{ $pt->phone }}</span> @endif
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- Prescription Header --}}
        <div class="card-emr mb-3">
            <div class="card-hd" style="background:#fdf0f8">
                <div class="card-hd-title">
                    <i class="bi bi-capsule-fill" style="color:#e91e8c"></i>
                    Prescription Details
                    <code style="font-size:12px;color:#e91e8c;background:#fce4f4;padding:2px 8px;border-radius:6px;margin-left:6px">
                        {{ $prescription->code }}
                    </code>
                </div>
            </div>
            <div class="card-bd">
                <div class="row g-3">
                    <div class="col-6 col-sm-4">
                        <div class="fld">
                            <label class="flbl"><span class="km">Rx Code</span></label>
                            <div class="form-control ro" style="font-family:monospace;color:#e91e8c;font-weight:700">
                                {{ $prescription->code }}
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-sm-4">
                        <div class="fld">
                            <label class="flbl"><span class="km">ថ្ងៃ</span><span class="en">/ Prescribed At</span></label>
                            <input type="datetime-local" name="prescribed_at" class="form-control"
                                   value="{{ old('prescribed_at', $prescription->prescribed_at?->format('Y-m-d\TH:i') ?? now()->format('Y-m-d\TH:i')) }}"/>
                        </div>
                    </div>
                    <div class="col-12 col-sm-4">
                        <div class="fld">
                            <label class="flbl">
                                <span class="km">គ្រូពេទ្យ</span><span class="en">/ Prescribed By</span>
                                <span class="req">*</span>
                            </label>
                            <input type="text" name="prescribed_by" class="form-control" required
                                   placeholder="Dr. Name"
                                   value="{{ old('prescribed_by', $prescription->prescribed_by ?? '') }}"/>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Medications --}}
        <div class="card-emr mb-3">
            <div class="card-hd" style="background:#fdf0f8">
                <div class="card-hd-title">
                    <i class="bi bi-capsule-fill" style="color:#e91e8c"></i>
                    ថ្នាំ / Medications
                    <span id="rxCount"
                          style="background:#e91e8c22;color:#e91e8c;border:1px solid #e91e8c66;font-size:10px;padding:1px 8px;border-radius:10px;margin-left:6px">
                        {{ $rxCount }}
                    </span>
                </div>
                <button type="button" onclick="addMedCard()" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-plus-circle-fill"></i> Add
                </button>
            </div>
            <div class="card-bd">

                @if(session('success') || $rxCount > 0)
                <div class="note note-success mb-3" style="font-size:12px">
                    <i class="bi bi-info-circle-fill"></i>
                    Saving replaces all existing medications. Remove unwanted items before submitting.
                </div>
                @endif

                <div id="medList">
                    @forelse($medications as $i => $med)
                        @include('clinics.operations._rx-card', [
                            'i'           => $i,
                            'med'         => $med,
                            'formOptions' => $formOptions,
                            'catalog'     => $catalog,
                        ])
                    @empty
                        @include('clinics.operations._rx-card', ['i' => 0, 'med' => null, 'formOptions' => $formOptions, 'catalog' => $catalog])
                    @endforelse
                </div>

                <button type="button" onclick="addMedCard()"
                        class="btn btn-outline-primary btn-w100 mt-2" style="padding:11px">
                    <i class="bi bi-plus-circle-fill"></i> បន្ថែមថ្នាំ / Add Medication
                </button>
            </div>
        </div>

    </div>

    {{-- ── RIGHT COLUMN: Dispensing + Actions ──────────────────────────────── --}}
    <div class="col-12 col-lg-4">
        <div class="card-emr mb-3" style="position:sticky;top:76px">

            {{-- Dispensing --}}
            <div class="card-hd" style="background:#f5f3ff">
                <div class="card-hd-title">
                    <i class="bi bi-bag-heart-fill" style="color:#7c3aed"></i>
                    Pharmacy / ឱសថស្ថាន
                </div>
            </div>
            <div class="card-bd">
                <div class="fld mb-3">
                    <label class="flbl" style="color:#7c3aed;font-weight:800">Dispensing Status</label>
                    <select name="dispensed_status" class="form-select" style="border-color:#c4b5fd">
                        <option value="">— Not dispensed yet</option>
                        <option value="partial"   {{ old('dispensed_status', $prescription->dispensed_status) === 'partial'   ? 'selected' : '' }}>⏳ Partially dispensed</option>
                        <option value="dispensed" {{ old('dispensed_status', $prescription->dispensed_status) === 'dispensed' ? 'selected' : '' }}>✅ Fully dispensed</option>
                    </select>
                </div>
                <div class="fld mb-3">
                    <label class="flbl" style="color:#7c3aed;font-weight:800">Dispensed By</label>
                    <input type="text" name="dispensed_by" class="form-control" style="border-color:#c4b5fd"
                           placeholder="Pharmacist name"
                           value="{{ old('dispensed_by', $prescription->dispensed_by ?? '') }}"/>
                </div>

                <button type="submit" class="btn btn-primary btn-w100" style="padding:12px 0;font-size:15px;font-weight:700">
                    <i class="bi bi-floppy-fill"></i> Update Prescription
                </button>
                <a href="{{ route('prescriptions.show', $prescription->code) }}"
                   class="btn btn-outline-secondary btn-w100 mt-2">
                    Cancel
                </a>

                {{-- Delete --}}
                <div style="margin-top:20px;padding-top:16px;border-top:1px solid #f0f2ff">
                    <form method="POST" action="{{ route('prescriptions.destroy', $prescription->code) }}"
                          data-confirm="Delete prescription #{{ $prescription->code }}? All dispensed medicines will be returned to stock. This cannot be undone."
                          data-confirm-type="danger" data-confirm-title="Delete Prescription">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-w100 btn-sm">
                            <i class="bi bi-trash3"></i> Delete Prescription
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>
</form>

{{-- Template for dynamically added cards --}}
<template id="medCardTemplate">
    @include('clinics.operations._rx-card', ['i' => '__IDX__', 'med' => null, 'formOptions' => $formOptions, 'catalog' => $catalog])
</template>

@endsection

@push('scripts')
<script>
var rxIdx     = {{ max($rxCount, 1) }};
var rxCatalog = @json($catalogJs);
var formOpts  = @json($formOptions);

function addMedCard() {
    var idx      = rxIdx++;
    var template = document.getElementById('medCardTemplate');
    var html     = template.innerHTML.replace(/__IDX__/g, idx);
    var wrapper  = document.createElement('div');
    wrapper.innerHTML = html;
    var card = wrapper.firstElementChild;
    document.getElementById('medList').appendChild(card);
    card.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    initCardDosing(idx);
    updateCount();
}

function removeCard(idx) {
    var card = document.getElementById('rx-card-' + idx);
    if (card) { card.remove(); updateCount(); }
}

function updateCount() {
    var n  = document.querySelectorAll('.rx-med-card').length;
    var el = document.getElementById('rxCount');
    if (el) el.textContent = n;
}

function fillFromCatalog(selectEl, idx) {
    var code = selectEl.value;
    var item = rxCatalog.find(function(c) { return c.code === code; });
    if (!item) return;

    var set = function(id, val) { var el = document.getElementById(id); if (el) el.value = val; };
    set('med_name_'     + idx, item.name);
    set('med_strength_' + idx, item.strength);
    set('med_unit_'     + idx, item.unit);

    var formSel = document.querySelector('[name="meds[' + idx + '][form]"]');
    if (formSel && item.form) formSel.value = item.form;

    var codeInput = document.getElementById('med_code_' + idx);
    if (codeInput) codeInput.value = item.code;

    updateStockBadge(idx, item);

    var titleEl = document.getElementById('rx-card-title-' + idx);
    if (titleEl) titleEl.textContent = item.name;
}

function updateStockBadge(idx, item) {
    var badge = document.getElementById('stock-badge-' + idx);
    if (!badge || !item) return;
    if (item.stock <= 0) {
        badge.textContent = 'Out of stock';
        badge.className   = 'stock-badge stock-out';
    } else if (item.stock <= item.alert) {
        badge.textContent = 'Low: ' + item.stock;
        badge.className   = 'stock-badge stock-low';
    } else {
        badge.textContent = '✓ ' + item.stock;
        badge.className   = 'stock-badge stock-ok';
    }
    badge.style.display = 'inline-block';
}

function initCardDosing(idx) {
    var fields = ['morning', 'afternoon', 'evening', 'night', 'days'];
    fields.forEach(function(f) {
        var el = document.querySelector('[name="meds[' + idx + '][' + f + ']"]');
        if (el) el.addEventListener('input', function() { calcTotal(idx); });
    });
    calcTotal(idx);
}

function calcTotal(idx) {
    var sum = 0;
    ['morning','afternoon','evening','night'].forEach(function(f) {
        var el = document.querySelector('[name="meds[' + idx + '][' + f + ']"]');
        sum += parseFloat(el ? (el.value || 0) : 0);
    });
    var daysEl  = document.querySelector('[name="meds[' + idx + '][days]"]');
    var days    = parseFloat(daysEl ? (daysEl.value || 1) : 1);
    var totalEl = document.getElementById('rx-total-' + idx);
    if (totalEl) totalEl.textContent = Math.round(sum * days);
}

document.addEventListener('DOMContentLoaded', function() {
    // Init dosing calculators for all pre-rendered cards
    document.querySelectorAll('.rx-med-card').forEach(function(card) {
        var idx = parseInt(card.id.replace('rx-card-', ''));
        if (!isNaN(idx)) initCardDosing(idx);
    });
    updateCount();
});
</script>
@endpush
