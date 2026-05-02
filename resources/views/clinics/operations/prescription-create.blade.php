@extends('clinics.layout.app')
@section('title', __('app.new_prescription', [], 'New Prescription'))

@php
    $formOptions = $formOptions ?? ['Tablet','Capsule','Syrup','Injection','Ointment','Drops','Inhaler','Powder'];
    $catalog     = $catalog ?? collect([]);
    $patient     = $patient ?? null;
    $visitCode   = $visitCode ?? null;

    // Pre-compute catalog for JS — maps to {id, code, name, strength, form, unit, stock, alert, price}
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
        title="New Prescription"
        subtitle="បញ្ជាថ្នាំថ្មី"
        :breadcrumbs="[
        ['label' => __('app.home'), 'url' => route('dashboard')],
        ['label' => __('app.prescriptions'), 'url' => route('prescriptions.index')],
        ['label' => 'New'],
    ]">
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

    <form method="POST" action="{{ route('prescriptions.store') }}" id="rxForm">
        @csrf

        <div class="flow-panel mb-3">
            <div class="flow-panel-title">
                <i class="bi bi-diagram-3-fill me-1"></i> Prescription Flow
            </div>
            <div class="flow-steps">
                <span class="flow-step">1. Select patient</span>
                <span class="flow-step">2. Add medications</span>
                <span class="flow-step">3. Review dosage</span>
                <span class="flow-step">4. Save prescription</span>
                <span class="flow-step">5. Dispense in pharmacy</span>
            </div>
        </div>

        <div class="note note-warn mb-3">
            <i class="bi bi-exclamation-circle-fill"></i>
            Prescribe from formulary when possible to avoid stock mismatch at dispensing time.
        </div>

        <div class="row g-3">

            {{-- ── LEFT COLUMN: Patient + Doctor + Medications ─────────────────────── --}}
            <div class="col-12 col-lg-8">

                {{-- Patient Search --}}
                <div class="card-emr mb-3">
                    <div class="card-hd" style="background:#f6f9ff">
                        <div class="card-hd-title">
                            <i class="bi bi-person-vcard-fill" style="color:#4154f1"></i>
                            Patient / អ្នកជំងឺ <span class="req" style="color:#e91e8c">*</span>
                        </div>
                    </div>
                    <div class="card-bd">
                        <div class="note note-info mb-3">
                            <i class="bi bi-info-circle-fill"></i>
                            Check stock badges before finalizing to avoid dispensing delays.
                        </div>
                        <div class="row g-3">
                            <div class="col-12">
                                <div class="fld">
                                    <label class="flbl">
                                        <span class="km">ស្វែងរកអ្នកជំងឺ</span>
                                        <span class="en">/ Search Patient</span>
                                        <span class="req">*</span>
                                    </label>
                                    <div style="position:relative; z-index: 1055;">
                                        <input type="text" id="patientSearch"
                                               class="form-control"
                                               placeholder="Type name or patient code…"
                                               autocomplete="off"
                                               value="{{ old('patient_search', $patient ? $patient->surname.', '.$patient->name : '') }}"/>

                                        <div id="patientDropdown"
                                             style="display:none;top:100%;left:0;right:0;background:#fff;border:1.5px solid #c5cbf9;border-radius:10px;box-shadow:0 4px 20px rgba(65,84,241,.12);max-height:280px;overflow-y:auto">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12" id="selectedPatientCard" style="display:{{ $patient ? 'block' : 'none' }}">
                                <div
                                    style="background:#f0f2ff;border:1.5px solid #c5cbf9;border-radius:10px;padding:12px 16px;display:flex;align-items:center;gap:12px">
                                    <div id="ptAvatar"
                                         style="width:38px;height:38px;border-radius:10px;background:#4154f1;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:14px;flex-shrink:0">
                                        —
                                    </div>
                                    <div style="flex:1">
                                        <div id="ptName" style="font-weight:700;color:#012970;font-size:14px">{{ $patient ? $patient->surname.', '.$patient->name : '—' }}</div>
                                        <div style="display:flex;gap:10px;margin-top:2px">
                                            <code id="ptCode" style="font-size:11px;color:#4154f1">{{ $patient?->code ?? '—' }}</code>
                                            <span id="ptDob" style="font-size:11px;color:#aaa">{{ $patient?->birthdate ?? '—' }}</span>
                                        </div>
                                    </div>
                                    <button type="button" onclick="clearPatient()"
                                            class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-x"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="patient_code" id="patientCode" value="{{ old('patient_code', $patient?->code ?? '') }}"/>
                    </div>
                </div>

                {{-- Prescription Header --}}
                <div class="card-emr mb-3">
                    <div class="card-hd" style="background:#fdf0f8">
                        <div class="card-hd-title">
                            <i class="bi bi-capsule-fill" style="color:#e91e8c"></i>
                            Prescription Details
                        </div>
                    </div>
                    <div class="card-bd">
                        <div class="row g-3">
                            <div class="col-6 col-sm-4">
                                <div class="fld">
                                    <label class="flbl">
                                        <span class="km">Rx Code</span>
                                        <span
                                            style="font-size:9px;background:#e8f8ef;color:#1D9E75;padding:1px 6px;border-radius:8px;margin-left:4px;font-weight:700">AUTO</span>
                                    </label>
                                    <div class="form-control ro"
                                         style="font-family:monospace;color:#e91e8c;font-weight:700">
                                        AUTO-GENERATED
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-sm-4">
                                <div class="fld">
                                    <label class="flbl"><span class="km">ថ្ងៃ</span><span
                                            class="en">/ Prescribed At</span></label>
                                    <input type="datetime-local" name="prescribed_at" class="form-control"
                                           value="{{ old('prescribed_at', now()->format('Y-m-d\TH:i')) }}"/>
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
                                           value="{{ old('prescribed_by', auth()->user()?->name ?? '') }}"/>
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
                        0
                    </span>
                        </div>
                        <button type="button" onclick="addMedCard()" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-plus-circle-fill"></i> Add Medication
                        </button>
                    </div>
                    <div class="card-bd">
                        <div id="medList">
                            @include('clinics.operations._rx-card', ['i' => 0, 'med' => null, 'formOptions' => $formOptions, 'catalog' => $catalog])
                        </div>

                        <button type="button" onclick="addMedCard()"
                                class="btn btn-outline-primary btn-w100 mt-2" style="padding:11px">
                            <i class="bi bi-plus-circle-fill"></i> បន្ថែមថ្នាំ / Add Medication
                        </button>
                    </div>
                </div>

            </div>

            {{-- ── RIGHT COLUMN: Dispensing + Submit ───────────────────────────────── --}}
            <div class="col-12 col-lg-4">

                {{-- Dispensing --}}
                <div class="card-emr mb-3" style="position:sticky;top:76px">
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
                                <option value="partial" {{ old('dispensed_status') === 'partial'   ? 'selected' : '' }}>
                                    ⏳ Partially dispensed
                                </option>
                                <option
                                    value="dispensed" {{ old('dispensed_status') === 'dispensed' ? 'selected' : '' }}>✅
                                    Fully dispensed
                                </option>
                            </select>
                        </div>
                        <div class="fld mb-3">
                            <label class="flbl" style="color:#7c3aed;font-weight:800">Dispensed By</label>
                            <input type="text" name="dispensed_by" class="form-control" style="border-color:#c4b5fd"
                                   placeholder="Pharmacist name"
                                   value="{{ old('dispensed_by') }}"/>
                        </div>

                        {{-- Visit linkage (optional) --}}
                        <div class="fld mb-3" style="padding-top:12px;border-top:1px dashed #e8e0ff">
                            <label class="flbl" style="color:#aaa;font-size:10.5px">Visit Code (optional)</label>
                            <input type="text" name="visit_code" class="form-control form-control-sm"
                                   placeholder="e.g. V202603220001"
                                   value="{{ old('visit_code', $visitCode ?? '') }}"/>
                            <div style="font-size:10px;color:#bbb;margin-top:3px">Link to an existing visit record</div>
                        </div>

                        {{-- Stock warning panel --}}
                        <div id="stockWarnings" style="display:none;margin-top:8px"></div>

                        <button type="submit" class="btn btn-primary btn-w100"
                                style="padding:12px 0;font-size:15px;font-weight:700">
                            <i class="bi bi-floppy-fill"></i> Save Prescription
                        </button>
                        <a href="{{ route('prescriptions.index') }}" class="btn btn-outline-secondary btn-w100 mt-2">
                            Cancel
                        </a>

                        <div class="note note-info mt-3" style="font-size:11px">
                            <i class="bi bi-check2-square"></i>
                            Confirm patient and medication rows before saving.
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </form>

    {{-- ── Medication Card Template (hidden, cloned by JS) ────────────────────── --}}
    <template id="medCardTemplate">
        @include('clinics.operations._rx-card', ['i' => '__IDX__', 'med' => null, 'formOptions' => $formOptions, 'catalog' => $catalog])
    </template>

@endsection

@push('scripts')
    <script>
        let rxIdx = 1;                    // next medication card index
        let rxCatalog = @json($catalogJs);
        let formOpts = @json($formOptions);

        // ==================== PATIENT SEARCH ====================
        let searchTimer = null;

        const patientSearch = document.getElementById('patientSearch');
        const patientDropdown = document.getElementById('patientDropdown');

        // Input handler with debounce
        patientSearch.addEventListener('input', function () {
            clearTimeout(searchTimer);
            const q = this.value.trim();

            if (q.length < 2) {
                closePatientDropdown();
                return;
            }

            searchTimer = setTimeout(() => {
                searchPatients(q);
            }, 300);
        });

        // Click outside to close dropdown
        document.addEventListener('click', function (e) {
            if (!e.target.closest('#patientSearch') &&
                !e.target.closest('#patientDropdown')) {
                closePatientDropdown();
            }
        });

        // Prevent closing when clicking inside dropdown items
        patientDropdown.addEventListener('click', function (e) {
            e.stopImmediatePropagation();
        });

        function searchPatients(q) {
            const token = document.querySelector('meta[name="csrf-token"]').content;

            fetch('{{ route("patients.search") }}?q=' + encodeURIComponent(q), {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json'
                }
            })
                .then(r => r.json())
                .then(data => {
                    renderPatientDropdown(data);
                })
                .catch(err => {
                    console.error('Patient search failed:', err);
                    patientDropdown.innerHTML = '<div style="padding:14px 16px;color:#e74c3c">Search error. Please try again.</div>';
                    patientDropdown.style.display = 'block';
                });
        }

        function renderPatientDropdown(patients) {
            if (!patients || patients.length === 0) {
                patientDropdown.innerHTML = `
                    <div style="padding:14px 16px;font-size:12px;color:#aaa;text-align:center">
                        No patients found
                    </div>`;
                patientDropdown.style.display = 'block';
                return;
            }

            patientDropdown.innerHTML = patients.map(p => {
                const initials = ((p.surname || '') + (p.name || '')).slice(0, 2).toUpperCase();
                const colors = ['#4154f1', '#2eca6a', '#ff771d', '#e74c3c', '#9b59b6'];
                const col = colors[Math.abs((p.code || '').split('').reduce((a, c) => a + c.charCodeAt(0), 0)) % colors.length];

                return `
                    <div class="pt-item"
                         onclick='selectPatient(${JSON.stringify(p)})'
                         style="display:flex;align-items:center;gap:10px;padding:12px 14px;cursor:pointer;border-bottom:1px solid #f0f2ff">
                        <div style="width:36px;height:36px;border-radius:8px;background:${col};color:#fff;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;flex-shrink:0">
                            ${initials}
                        </div>
                        <div style="flex:1;min-width:0">
                            <div style="font-weight:700;color:#012970;font-size:13.5px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                                ${p.surname || ''}, ${p.name || ''}
                            </div>
                            <div style="font-size:11.5px;color:#666">
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

            // Fill selected patient card
            document.getElementById('ptName').textContent = `${p.surname || ''}, ${p.name || ''}`;
            document.getElementById('ptCode').textContent = p.code || '—';
            document.getElementById('ptDob').textContent = p.birthdate || '—';

            // Avatar
            const initials = ((p.surname || '') + (p.name || '')).slice(0, 2).toUpperCase();
            const colors = ['#4154f1', '#2eca6a', '#ff771d', '#e74c3c', '#9b59b6'];
            const col = colors[Math.abs((p.code || '').split('').reduce((a, c) => a + c.charCodeAt(0), 0)) % colors.length];

            const avatar = document.getElementById('ptAvatar');
            avatar.textContent = initials;
            avatar.style.background = col;

            document.getElementById('selectedPatientCard').style.display = 'block';
            closePatientDropdown();
        }

        function clearPatient() {
            document.getElementById('patientCode').value = '';
            document.getElementById('patientSearch').value = '';
            document.getElementById('selectedPatientCard').style.display = 'none';
            closePatientDropdown();
        }

        function closePatientDropdown() {
            patientDropdown.style.display = 'none';
        }

        // ==================== MEDICATION CARDS (unchanged but cleaned) ====================
        function addMedCard() {
            const idx = rxIdx++;
            const template = document.getElementById('medCardTemplate');
            let html = template.innerHTML.replace(/__IDX__/g, idx);

            const wrapper = document.createElement('div');
            wrapper.innerHTML = html;
            const card = wrapper.firstElementChild;

            document.getElementById('medList').appendChild(card);
            card.scrollIntoView({behavior: 'smooth', block: 'nearest'});

            initCardDosing(idx);
            updateCount();
        }

        function removeCard(idx) {
            const card = document.getElementById('rx-card-' + idx);
            if (card) {
                card.remove();
                updateCount();
            }
        }

        function updateCount() {
            const n = document.querySelectorAll('.rx-med-card').length;
            const el = document.getElementById('rxCount');
            if (el) el.textContent = n;
        }

        // ==================== CATALOG FILL ====================
        function fillFromCatalog(sel, idx) {
            const code = sel.value;
            if (!code) return;
            const med = rxCatalog.find(m => m.code === code);
            if (!med) return;

            const n = document.getElementById('med_name_' + idx);
            const s = document.getElementById('med_strength_' + idx);
            const u = document.getElementById('med_unit_' + idx);
            const c = document.getElementById('med_code_' + idx);
            const t = document.getElementById('rx-card-title-' + idx);

            if (n) n.value = med.name;
            if (s) s.value = med.strength || '';
            if (u) u.value = med.unit || '';
            if (c) c.value = med.code;
            if (t) t.textContent = med.name;

            // Set form select
            const card = document.getElementById('rx-card-' + idx);
            if (card && med.form) {
                const formSel = card.querySelector('select[name="meds[' + idx + '][form]"]');
                if (formSel) {
                    const opt = Array.from(formSel.options).find(o => o.value === med.form);
                    if (opt) formSel.value = med.form;
                }
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

        // ==================== DOSING CALC ====================
        function calcTotal(idx) {
            const card = document.getElementById('rx-card-' + idx);
            if (!card) return;
            const get = f => parseFloat(card.querySelector('input[name="meds[' + idx + '][' + f + ']"]')?.value || 0) || 0;
            const total = (get('morning') + get('afternoon') + get('evening') + get('night')) * Math.max(1, get('days'));
            const el = document.getElementById('rx-total-' + idx);
            if (el) el.textContent = total % 1 === 0 ? total : total.toFixed(1);
        }

        function initCardDosing(idx) {
            calcTotal(idx);
        }

        // ==================== INIT ====================
        document.addEventListener('DOMContentLoaded', function () {
            initCardDosing(0);
            updateCount();

            // Restore old patient data after validation error
            const oldCode = '{{ old('patient_code') }}';
            if (oldCode) {
                // You may want to trigger a search or just show the code
                document.getElementById('patientSearch').value = oldCode;
            }
        });
    </script>
@endpush
