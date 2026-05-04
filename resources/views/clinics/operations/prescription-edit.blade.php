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

<x-ui.page-header
    km="កែប្រែបញ្ជាថ្នាំ"
    :title="'Edit ' . $prescription->code"
    :breadcrumbs="[
        ['label' => __('app.home'), 'url' => route('dashboard')],
        ['label' => __('app.prescriptions'), 'url' => route('prescriptions.index')],
        ['label' => $prescription->code, 'url' => route('prescriptions.show', $prescription->code)],
        ['label' => 'Edit'],
    ]">
    <x-slot:actions>
        <x-ui.button href="{{ route('prescriptions.show', $prescription->code) }}" variant="secondary">
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

<form method="POST" action="{{ route('prescriptions.update', $prescription->code) }}" id="rxForm">
@csrf
@method('PATCH')

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

    {{-- ── Main column ─────────────────────────────────────────────────── --}}
    <div class="lg:col-span-2 space-y-4">

        {{-- Patient (read-only on edit) --}}
        @if($prescription->patient)
            @php
                $pt = $prescription->patient;
                $colors = ['#4154f1','#2eca6a','#ff771d','#e74c3c','#9b59b6'];
                $col    = $colors[abs(crc32($pt->code)) % count($colors)];
            @endphp
            <x-ui.card>
                <x-slot:header>
                    <x-ui.card-header label="Patient / អ្នកជំងឺ" icon="bi-person-vcard-fill">
                        <x-slot:actions>
                            <x-ui.button href="{{ route('patients.show', $pt->code) }}" variant="secondary" size="sm">
                                <x-slot:icon><i class="bi bi-person-fill" aria-hidden="true"></i></x-slot:icon>
                                View
                            </x-ui.button>
                        </x-slot:actions>
                    </x-ui.card-header>
                </x-slot:header>
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0 text-white text-sm font-extrabold"
                         style="background:{{ $col }}">
                        {{ strtoupper(substr($pt->surname,0,1) . substr($pt->name,0,1)) }}
                    </div>
                    <div>
                        <div class="text-sm font-extrabold" style="color:#1a1f36">{{ $pt->surname }}, {{ $pt->name }}</div>
                        <code class="text-xs" style="color:#4154f1">{{ $pt->code }}</code>
                        @if($pt->phone) <span class="text-xs" style="color:#9ca3af"> · {{ $pt->phone }}</span> @endif
                    </div>
                </div>
            </x-ui.card>
        @endif

        {{-- Prescription Details --}}
        <x-ui.card>
            <x-slot:header>
                <x-ui.card-header label="Prescription Details" icon="bi-capsule-fill">
                    <x-slot:actions>
                        <code class="text-xs px-2 py-0.5 rounded-md font-bold" style="background:#fce4f4;color:#e91e8c">
                            {{ $prescription->code }}
                        </code>
                    </x-slot:actions>
                </x-ui.card-header>
            </x-slot:header>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold" style="color:#374151">Rx Code</label>
                    <div class="w-full text-sm rounded-lg border px-3 py-2 font-bold font-mono"
                         style="background:#f9fafb;border-color:#e6eaf5;color:#e91e8c">{{ $prescription->code }}</div>
                </div>
                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold" style="color:#374151">Prescribed At</label>
                    <x-forms.input type="datetime-local" name="prescribed_at"
                                   :value="old('prescribed_at', $prescription->prescribed_at?->format('Y-m-d\TH:i') ?? now()->format('Y-m-d\TH:i'))" />
                </div>
                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold" style="color:#374151">
                        Prescribed By <span style="color:#ef4444">*</span>
                    </label>
                    <x-forms.input type="text" name="prescribed_by" required
                                   placeholder="Dr. Name"
                                   :value="old('prescribed_by', $prescription->prescribed_by ?? '')" />
                </div>
            </div>
        </x-ui.card>

        {{-- Medications --}}
        <x-ui.card>
            <x-slot:header>
                <x-ui.card-header label="Medications / ថ្នាំ" icon="bi-capsule-fill">
                    <x-slot:actions>
                        <span id="rxCount" class="text-xs font-bold px-2 py-0.5 rounded-full"
                              style="background:#fce4f4;color:#e91e8c;border:1px solid #f9a8d4">{{ $rxCount }}</span>
                        <x-ui.button type="button" variant="secondary" size="sm" onclick="addMedCard()">
                            <x-slot:icon><i class="bi bi-plus-circle-fill" aria-hidden="true"></i></x-slot:icon>
                            Add
                        </x-ui.button>
                    </x-slot:actions>
                </x-ui.card-header>
            </x-slot:header>

            @if(session('success') || $rxCount > 0)
                <x-ui.alert type="info" class="mb-4 text-xs">
                    <i class="bi bi-info-circle-fill"></i>
                    Saving replaces all existing medications. Remove unwanted items before submitting.
                </x-ui.alert>
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
                        <option value="partial"   {{ old('dispensed_status', $prescription->dispensed_status) === 'partial'   ? 'selected' : '' }}>Partially dispensed</option>
                        <option value="dispensed" {{ old('dispensed_status', $prescription->dispensed_status) === 'dispensed' ? 'selected' : '' }}>Fully dispensed</option>
                    </x-forms.select>
                </div>
                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold" style="color:#374151">Dispensed By</label>
                    <x-forms.input type="text" name="dispensed_by"
                                   placeholder="Pharmacist name"
                                   :value="old('dispensed_by', $prescription->dispensed_by ?? '')" />
                </div>

                <div class="pt-3 space-y-2" style="border-top:1px solid #e6eaf5">
                    <x-ui.button type="submit" variant="primary" :fullWidth="true">
                        <x-slot:icon><i class="bi bi-floppy-fill" aria-hidden="true"></i></x-slot:icon>
                        Update Prescription
                    </x-ui.button>
                    <x-ui.button href="{{ route('prescriptions.show', $prescription->code) }}" variant="secondary" :fullWidth="true">
                        <x-slot:icon><i class="bi bi-x-circle" aria-hidden="true"></i></x-slot:icon>
                        Cancel
                    </x-ui.button>
                </div>

                <div class="pt-3" style="border-top:1px solid #e6eaf5">
                    <form method="POST" action="{{ route('prescriptions.destroy', $prescription->code) }}"
                          data-confirm="Delete prescription #{{ $prescription->code }}? All dispensed medicines will be returned to stock. This cannot be undone."
                          data-confirm-type="danger" data-confirm-title="Delete Prescription">
                        @csrf @method('DELETE')
                        <x-ui.button type="submit" variant="danger" :fullWidth="true" size="sm">
                            <x-slot:icon><i class="bi bi-trash3" aria-hidden="true"></i></x-slot:icon>
                            Delete Prescription
                        </x-ui.button>
                    </form>
                </div>
            </div>
        </x-ui.card>
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
    document.querySelectorAll('.rx-med-card').forEach(function(card) {
        var idx = parseInt(card.id.replace('rx-card-', ''));
        if (!isNaN(idx)) initCardDosing(idx);
    });
    updateCount();
});
</script>
@endpush
