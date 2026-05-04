@extends('clinics.layout.app')
@section('title', __('app.new_prescription', [], 'New Prescription'))

@php
    $formOptions = $formOptions ?? ['Tablet','Capsule','Syrup','Injection','Ointment','Drops','Inhaler','Powder'];
    $catalog     = $catalog ?? collect([]);
    $patient     = $patient ?? null;
    $visitCode   = $visitCode ?? null;

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

<x-ui.page-header
    km="បញ្ជាថ្នាំថ្មី"
    title="New Prescription"
    :breadcrumbs="[
        ['label' => __('app.home'), 'url' => route('dashboard')],
        ['label' => __('app.prescriptions'), 'url' => route('prescriptions.index')],
        ['label' => 'New'],
    ]">
    <x-slot:actions>
        <x-ui.button href="{{ route('prescriptions.index') }}" variant="secondary">
            <x-slot:icon><i class="bi bi-arrow-left" aria-hidden="true"></i></x-slot:icon>
            Back
        </x-ui.button>
    </x-slot:actions>
</x-ui.page-header>

@if($errors->any())
    <x-ui.alert type="error" class="mb-4">
        <ul class="list-disc pl-4 text-xs space-y-0.5">
            @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
        </ul>
    </x-ui.alert>
@endif

{{-- Prescription flow steps --}}
<div class="flex items-center gap-2 mb-4 overflow-x-auto pb-1">
    @foreach(['Select patient','Add medications','Review dosage','Save prescription','Dispense in pharmacy'] as $step => $label)
        <span class="flex-shrink-0 text-xs font-semibold px-3 py-1.5 rounded-full"
              style="background:#eef0fd;color:#4154f1;border:1px solid #c5cbf9">
            {{ $step + 1 }}. {{ $label }}
        </span>
        @if(!$loop->last)
            <i class="bi bi-chevron-right flex-shrink-0 text-xs" style="color:#9ca3af"></i>
        @endif
    @endforeach
</div>

<x-ui.alert type="warning" class="mb-4">
    <i class="bi bi-exclamation-circle-fill"></i>
    Prescribe from formulary when possible to avoid stock mismatch at dispensing time.
</x-ui.alert>

<form method="POST" action="{{ route('prescriptions.store') }}" id="rxForm">
@csrf

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

    {{-- ── Main column ─────────────────────────────────────────────────── --}}
    <div class="lg:col-span-2 space-y-4">

        {{-- Patient Search --}}
        <x-ui.card>
            <x-slot:header>
                <x-ui.card-header label="Patient / អ្នកជំងឺ" icon="bi-person-vcard-fill" />
            </x-slot:header>

            <x-ui.alert type="info" class="mb-4">
                <i class="bi bi-info-circle-fill"></i>
                Check stock badges before finalizing to avoid dispensing delays.
            </x-ui.alert>

            <div class="space-y-3">
                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold" style="color:#374151">
                        Search Patient <span style="color:#ef4444">*</span>
                    </label>
                    <div class="relative" style="z-index:1055">
                        <x-forms.input type="text" id="patientSearch"
                                       placeholder="Type name or patient code…"
                                       autocomplete="off"
                                       :value="old('patient_search', $patient ? $patient->surname.', '.$patient->name : '')" />
                        <div id="patientDropdown"
                             class="absolute w-full rounded-xl overflow-hidden"
                             style="display:none;top:calc(100% + 4px);left:0;right:0;background:#fff;border:1.5px solid #c5cbf9;box-shadow:0 4px 20px rgba(65,84,241,.12);max-height:280px;overflow-y:auto;z-index:1056">
                        </div>
                    </div>
                </div>

                <div id="selectedPatientCard" style="display:{{ $patient ? 'flex' : 'none' }}"
                     class="items-center gap-3 p-3 rounded-xl" style="background:#e6e9f0;border:1.5px solid #c5cbf9">
                    <div id="ptAvatar"
                         class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 text-white text-sm font-extrabold"
                         style="background:#4154f1">—</div>
                    <div class="flex-1">
                        <div id="ptName" class="text-sm font-bold" style="color:#1a1f36">{{ $patient ? $patient->surname.', '.$patient->name : '—' }}</div>
                        <div class="flex gap-3 mt-0.5">
                            <code id="ptCode" class="text-xs" style="color:#4154f1">{{ $patient?->code ?? '—' }}</code>
                            <span id="ptDob" class="text-xs" style="color:#9ca3af">{{ $patient?->birthdate ?? '—' }}</span>
                        </div>
                    </div>
                    <button type="button" onclick="clearPatient()"
                            class="w-7 h-7 flex items-center justify-center rounded-lg transition-colors"
                            style="color:#6b7280;border:1px solid #e2e8f0;background:#fff">
                        <i class="bi bi-x text-sm" aria-hidden="true"></i>
                    </button>
                </div>
            </div>
            <input type="hidden" name="patient_code" id="patientCode" value="{{ old('patient_code', $patient?->code ?? '') }}"/>
        </x-ui.card>

        {{-- Prescription Details --}}
        <x-ui.card>
            <x-slot:header>
                <x-ui.card-header label="Prescription Details" icon="bi-capsule-fill" />
            </x-slot:header>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold" style="color:#374151">
                        Rx Code
                        <span class="ml-1 text-xs px-1.5 py-0.5 rounded-md font-bold" style="background:#e8f8ef;color:#1D9E75">AUTO</span>
                    </label>
                    <div class="w-full text-sm rounded-lg border px-3 py-2 font-bold font-mono"
                         style="background:#f9fafb;border-color:#e6eaf5;color:#e91e8c">AUTO-GENERATED</div>
                </div>
                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold" style="color:#374151">Prescribed At</label>
                    <x-forms.input type="datetime-local" name="prescribed_at"
                                   :value="old('prescribed_at', now()->format('Y-m-d\TH:i'))" />
                </div>
                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold" style="color:#374151">
                        Prescribed By <span style="color:#ef4444">*</span>
                    </label>
                    <x-forms.input type="text" name="prescribed_by" required
                                   placeholder="Dr. Name"
                                   :value="old('prescribed_by', auth()->user()?->name ?? '')" />
                </div>
            </div>
        </x-ui.card>

        {{-- Medications --}}
        <x-ui.card>
            <x-slot:header>
                <x-ui.card-header label="Medications / ថ្នាំ" icon="bi-capsule-fill">
                    <x-slot:actions>
                        <span id="rxCount" class="text-xs font-bold px-2 py-0.5 rounded-full"
                              style="background:#fce4f4;color:#e91e8c;border:1px solid #f9a8d4">0</span>
                        <x-ui.button type="button" variant="secondary" size="sm" onclick="addMedCard()">
                            <x-slot:icon><i class="bi bi-plus-circle-fill" aria-hidden="true"></i></x-slot:icon>
                            Add Medication
                        </x-ui.button>
                    </x-slot:actions>
                </x-ui.card-header>
            </x-slot:header>

            <div id="medList">
                @include('clinics.operations._rx-card', ['i' => 0, 'med' => null, 'formOptions' => $formOptions, 'catalog' => $catalog])
            </div>

            <x-ui.button type="button" variant="secondary" :fullWidth="true" onclick="addMedCard()" class="mt-2">
                <x-slot:icon><i class="bi bi-plus-circle-fill" aria-hidden="true"></i></x-slot:icon>
                បន្ថែមថ្នាំ / Add Medication
            </x-ui.button>
        </x-ui.card>

    </div>

    {{-- ── Sidebar ──────────────────────────────────────────────────────── --}}
    <div>
        <x-ui.card class="sticky top-20">
            <x-slot:header>
                <x-ui.card-header label="Pharmacy / ឱសថស្ថាន" icon="bi-bag-heart-fill" />
            </x-slot:header>

            <div class="space-y-3">
                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold" style="color:#374151">Dispensing Status</label>
                    <x-forms.select name="dispensed_status">
                        <option value="">— Not dispensed yet</option>
                        <option value="partial"   {{ old('dispensed_status') === 'partial'   ? 'selected' : '' }}>Partially dispensed</option>
                        <option value="dispensed" {{ old('dispensed_status') === 'dispensed' ? 'selected' : '' }}>Fully dispensed</option>
                    </x-forms.select>
                </div>
                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold" style="color:#374151">Dispensed By</label>
                    <x-forms.input type="text" name="dispensed_by"
                                   placeholder="Pharmacist name"
                                   :value="old('dispensed_by')" />
                </div>

                <div class="pt-3 border-t space-y-1.5" style="border-color:#e6eaf5;border-style:dashed">
                    <label class="block text-xs font-semibold" style="color:#374151">Visit Code <span class="text-xs font-normal" style="color:#9ca3af">(optional)</span></label>
                    <x-forms.input type="text" name="visit_code"
                                   placeholder="e.g. V202603220001"
                                   :value="old('visit_code', $visitCode ?? '')" />
                    <p class="text-xs" style="color:#9ca3af">Link to an existing visit record</p>
                </div>

                <div id="stockWarnings" style="display:none"></div>

                <div class="pt-2 space-y-2">
                    <x-ui.button type="submit" variant="primary" :fullWidth="true">
                        <x-slot:icon><i class="bi bi-floppy-fill" aria-hidden="true"></i></x-slot:icon>
                        Save Prescription
                    </x-ui.button>
                    <x-ui.button href="{{ route('prescriptions.index') }}" variant="secondary" :fullWidth="true">
                        <x-slot:icon><i class="bi bi-x-circle" aria-hidden="true"></i></x-slot:icon>
                        Cancel
                    </x-ui.button>
                </div>

                <x-ui.alert type="info" class="text-xs">
                    <i class="bi bi-check2-square"></i>
                    Confirm patient and medication rows before saving.
                </x-ui.alert>
            </div>
        </x-ui.card>
    </div>

</div>
</form>

{{-- Medication Card Template (hidden, cloned by JS) --}}
<template id="medCardTemplate">
    @include('clinics.operations._rx-card', ['i' => '__IDX__', 'med' => null, 'formOptions' => $formOptions, 'catalog' => $catalog])
</template>

@endsection

@push('scripts')
<script>
let rxIdx = 1;
let rxCatalog = @json($catalogJs);
let formOpts = @json($formOptions);

// ==================== PATIENT SEARCH ====================
let searchTimer = null;
const patientSearch = document.getElementById('patientSearch');
const patientDropdown = document.getElementById('patientDropdown');

patientSearch.addEventListener('input', function () {
    clearTimeout(searchTimer);
    const q = this.value.trim();
    if (q.length < 2) { closePatientDropdown(); return; }
    searchTimer = setTimeout(() => searchPatients(q), 300);
});

document.addEventListener('click', function (e) {
    if (!e.target.closest('#patientSearch') && !e.target.closest('#patientDropdown')) {
        closePatientDropdown();
    }
});

patientDropdown.addEventListener('click', function (e) { e.stopImmediatePropagation(); });

function searchPatients(q) {
    const token = document.querySelector('meta[name="csrf-token"]').content;
    fetch('{{ route("patients.search") }}?q=' + encodeURIComponent(q), {
        method: 'GET',
        headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => renderPatientDropdown(data))
    .catch(err => {
        patientDropdown.innerHTML = '<div style="padding:14px 16px;color:#e74c3c;font-size:12px">Search error. Please try again.</div>';
        patientDropdown.style.display = 'block';
    });
}

function renderPatientDropdown(patients) {
    if (!patients || patients.length === 0) {
        patientDropdown.innerHTML = '<div style="padding:14px 16px;font-size:12px;color:#9ca3af;text-align:center">No patients found</div>';
        patientDropdown.style.display = 'block';
        return;
    }
    const colors = ['#4154f1', '#2eca6a', '#ff771d', '#e74c3c', '#9b59b6'];
    patientDropdown.innerHTML = patients.map(p => {
        const initials = ((p.surname || '') + (p.name || '')).slice(0, 2).toUpperCase();
        const col = colors[Math.abs((p.code || '').split('').reduce((a, c) => a + c.charCodeAt(0), 0)) % colors.length];
        return `
            <div onclick='selectPatient(${JSON.stringify(p)})'
                 style="display:flex;align-items:center;gap:10px;padding:12px 14px;cursor:pointer;border-bottom:1px solid #e6e9f0;transition:background .15s"
                 onmouseenter="this.style.background='#f9fafb'" onmouseleave="this.style.background='#fff'">
                <div style="width:36px;height:36px;border-radius:8px;background:${col};color:#fff;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;flex-shrink:0">
                    ${initials}
                </div>
                <div style="flex:1;min-width:0">
                    <div style="font-weight:700;color:#1a1f36;font-size:13px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                        ${p.surname || ''}, ${p.name || ''}
                    </div>
                    <div style="font-size:11px;color:#6b7280">
                        ${p.code || ''} ${p.phone ? '· ' + p.phone : ''}
                    </div>
                </div>
            </div>`;
    }).join('');
    patientDropdown.style.display = 'block';
}

function selectPatient(p) {
    document.getElementById('patientCode').value = p.code || '';
    document.getElementById('patientSearch').value = `${p.surname || ''}, ${p.name || ''}`;
    document.getElementById('ptName').textContent = `${p.surname || ''}, ${p.name || ''}`;
    document.getElementById('ptCode').textContent = p.code || '—';
    document.getElementById('ptDob').textContent = p.birthdate || '—';
    const colors = ['#4154f1', '#2eca6a', '#ff771d', '#e74c3c', '#9b59b6'];
    const col = colors[Math.abs((p.code || '').split('').reduce((a, c) => a + c.charCodeAt(0), 0)) % colors.length];
    const avatar = document.getElementById('ptAvatar');
    avatar.textContent = ((p.surname || '') + (p.name || '')).slice(0, 2).toUpperCase();
    avatar.style.background = col;
    const card = document.getElementById('selectedPatientCard');
    card.style.display = 'flex';
    card.style.background = '#e6e9f0';
    card.style.border = '1.5px solid #c5cbf9';
    card.style.borderRadius = '0.75rem';
    card.style.padding = '12px';
    closePatientDropdown();
}

function clearPatient() {
    document.getElementById('patientCode').value = '';
    document.getElementById('patientSearch').value = '';
    document.getElementById('selectedPatientCard').style.display = 'none';
    closePatientDropdown();
}

function closePatientDropdown() { patientDropdown.style.display = 'none'; }

// ==================== MEDICATION CARDS ====================
function addMedCard() {
    const idx = rxIdx++;
    const template = document.getElementById('medCardTemplate');
    let html = template.innerHTML.replace(/__IDX__/g, idx);
    const wrapper = document.createElement('div');
    wrapper.innerHTML = html;
    const card = wrapper.firstElementChild;
    document.getElementById('medList').appendChild(card);
    card.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    initCardDosing(idx);
    updateCount();
}

function removeCard(idx) {
    const card = document.getElementById('rx-card-' + idx);
    if (card) { card.remove(); updateCount(); }
}

function updateCount() {
    const n = document.querySelectorAll('.rx-med-card').length;
    const el = document.getElementById('rxCount');
    if (el) el.textContent = n;
}

function fillFromCatalog(sel, idx) {
    const code = sel.value;
    if (!code) return;
    const med = rxCatalog.find(m => m.code === code);
    if (!med) return;
    const set = (id, val) => { const el = document.getElementById(id); if (el) el.value = val; };
    set('med_name_'     + idx, med.name);
    set('med_strength_' + idx, med.strength || '');
    set('med_unit_'     + idx, med.unit || '');
    set('med_code_'     + idx, med.code);
    const titleEl = document.getElementById('rx-card-title-' + idx);
    if (titleEl) titleEl.textContent = med.name;
    const card = document.getElementById('rx-card-' + idx);
    if (card && med.form) {
        const formSel = card.querySelector(`select[name="meds[${idx}][form]"]`);
        if (formSel) formSel.value = med.form;
    }
    updateStockBadge(idx, med);
}

function updateStockBadge(idx, med) {
    const badge = document.getElementById('stock-badge-' + idx);
    if (!badge) return;
    if (med.stock <= 0) {
        badge.className = 'stock-badge stock-out';
        badge.textContent = 'Out of stock';
    } else if (med.stock <= med.alert) {
        badge.className = 'stock-badge stock-low';
        badge.textContent = 'Low: ' + med.stock;
    } else {
        badge.className = 'stock-badge stock-ok';
        badge.textContent = '✓ ' + med.stock;
    }
    badge.style.display = '';
}

function calcTotal(idx) {
    const card = document.getElementById('rx-card-' + idx);
    if (!card) return;
    const get = f => parseFloat(card.querySelector(`input[name="meds[${idx}][${f}]"]`)?.value || 0) || 0;
    const total = (get('morning') + get('afternoon') + get('evening') + get('night')) * Math.max(1, get('days'));
    const el = document.getElementById('rx-total-' + idx);
    if (el) el.textContent = total % 1 === 0 ? total : total.toFixed(1);
}

function initCardDosing(idx) { calcTotal(idx); }

document.addEventListener('DOMContentLoaded', function () {
    initCardDosing(0);
    updateCount();
    const oldCode = '{{ old('patient_code') }}';
    if (oldCode) document.getElementById('patientSearch').value = oldCode;
});
</script>
@endpush
