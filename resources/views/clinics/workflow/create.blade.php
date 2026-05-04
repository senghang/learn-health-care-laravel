@extends('clinics.layout.app')

@section('title', 'ការចូលព្យាបាលថ្មី')

@section('content')

    <div class="pg-header">
        <div>
            <h1 class="pg-title">ការចូលព្យាបាលថ្មី <small>/ New Visit</small></h1>
            <div class="breadcrumb-row">
                <a href="{{ route('dashboard') }}">ដើម</a> <span>›</span>
                <a href="{{ route('visits.index') }}">Visits</a> <span>›</span>
                <span>New</span>
            </div>
        </div>
    </div>

    {{-- Banner --}}
    {{--    <div --}}
    {{--        style="background:linear-gradient(135deg,#1a1f36,#1a3a7c);border-radius:14px;padding:20px 24px;margin-bottom:20px;overflow:hidden;position:relative"> --}}
    {{--        <div --}}
    {{--            style="position:absolute;right:-20px;top:-20px;width:140px;height:140px;border-radius:50%;background:rgba(255,255,255,.04)"></div> --}}
    {{--        <div style="display:flex;align-items:flex-start;gap:14px;flex-wrap:wrap"> --}}
    {{--            <div style="font-size:34px;flex-shrink:0">⚕</div> --}}
    {{--            <div style="flex:1;min-width:200px"> --}}
    {{--                <div style="font-size:16px;font-weight:800;color:#fff;margin-bottom:4px">MediFlow EMR — --}}
    {{--                    ការចូលព្យាបាលថ្មី --}}
    {{--                </div> --}}
    {{--                <div style="font-size:12px;color:#8aabdc;margin-bottom:10px">Search an existing patient to pre-fill --}}
    {{--                    their data, or enter new patient details below. --}}
    {{--                </div> --}}
    {{--                <div style="display:flex;flex-wrap:wrap;gap:5px"> --}}
    {{--                    @foreach ($steps as $step) --}}
    {{--                        <span --}}
    {{--                            style="background:rgba(255,255,255,.1);color:#fff;font-size:10px;padding:2px 9px;border-radius:20px;border:1px solid rgba(255,255,255,.13)"> --}}
    {{--                    {{ $step->icon() }} {{ $step->labelKm() }} --}}
    {{--                </span> --}}
    {{--                    @endforeach --}}
    {{--                </div> --}}
    {{--            </div> --}}
    {{--        </div> --}}
    {{--    </div> --}}

    <div class="row g-3">

        {{-- ─── LEFT: Patient Search + Form ─── --}}
        <div class="col-12 col-lg-8">

            {{-- Patient Search Box --}}
            <div class="card-emr mb-3">
                <div class="card-hd">
                    <div class="card-hd-title">
                        <i class="bi bi-person-search"></i>
                        ស្វែងរកអ្នកជំងឺ <small style="font-weight:400;color:#aaa">/ Search Existing Patient</small>
                    </div>
                </div>
                <div class="card-bd">
                    {{-- Search Input --}}
                    <div style="position:relative">
                        <i class="bi bi-search"
                            style="position:absolute;left:13px;top:50%;transform:translateY(-50%);color:#9b59b6;font-size:15px;z-index:2"></i>
                        <input type="text" id="patientSearch" class="form-control"
                            style="padding-left:40px;font-size:14px;border:2px solid #e6eaf5;border-radius:10px"
                            placeholder="វាយលេខ PT… ឬ ឈ្មោះ ឬ ទូរស័ព្ទ / Type patient code, name or phone…"
                            autocomplete="off" />
                    </div>

                    {{-- Dropdown Results --}}
                    <div id="searchResults"
                        style="display:none;margin-top:8px;border:1.5px solid #e6eaf5;border-radius:10px;overflow:hidden;box-shadow:0 4px 16px rgba(65,84,241,.08)">
                        <div id="searchResultsInner"></div>
                    </div>

                    {{-- Selected Patient Card --}}
                    <div id="selectedPatientCard"
                        style="display:none;margin-top:12px;background:#f6f8fa;border:2px solid #4154f1;border-radius:12px;padding:14px 16px">
                        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px">
                            <div
                                style="font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.5px;color:#4154f1">
                                <i class="bi bi-person-check-fill"></i> អ្នកជំងឺដែលបានជ្រើស / Selected Patient
                            </div>
                            <button type="button" onclick="clearSelectedPatient()"
                                style="background:none;border:none;color:#aaa;font-size:18px;cursor:pointer;padding:0 4px;line-height:1"
                                title="Clear selection">×
                            </button>
                        </div>
                        <div style="display:flex;align-items:center;gap:12px">
                            <div id="spAvatar"
                                style="width:42px;height:42px;border-radius:50%;background:linear-gradient(135deg,#4154f1,#717ff5);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:15px;flex-shrink:0">
                            </div>
                            <div style="flex:1;min-width:0">
                                <div style="font-weight:800;font-size:15px" id="spName"></div>
                                <div style="font-size:11px;color:#888;margin-top:2px" id="spMeta"></div>
                            </div>
                            <div style="text-align:right;flex-shrink:0">
                                <div id="spVisitCount" style="font-size:11px;color:#9b59b6;font-weight:700"></div>
                                <div id="spLastVisit" style="font-size:10px;color:#bbb;margin-top:2px"></div>
                            </div>
                        </div>
                        <div
                            style="margin-top:8px;padding-top:8px;border-top:1px solid #e6eaf5;font-size:11px;color:#6979de">
                            <i class="bi bi-check2-circle"></i> ព័ត៌មានអ្នកជំងឺនឹងត្រូវបានបញ្ចូលដោយស្វ័យប្រវត្តិ /
                            Patient data pre-filled below
                        </div>
                    </div>
                </div>
            </div>

            {{-- Visit + Patient Form --}}
            <div class="card-emr">
                <div class="card-hd">
                    <div class="card-hd-title">
                        <i class="bi bi-clipboard2-plus-fill"></i>
                        ព័ត៌មានការចូលព្យាបាល <small style="font-weight:400;color:#aaa">/ Visit Details</small>
                    </div>
                    <span
                        style="font-size:11px;color:#aaa;background:#f6f8fa;padding:3px 10px;border-radius:20px;border:1px solid #e6eaf5">
                        ជំហានតែមួយ / Step 1 of 10
                    </span>
                </div>
                <div class="card-bd">

                    <div class="note note-info mb-4" style="align-items:flex-start">
                        <i class="bi bi-info-circle-fill" style="margin-top:1px;flex-shrink:0"></i>
                        <div>
                            <strong>តម្រូវការអប្បបរមា:</strong> លេខអ្នកជំងឺ + ឈ្មោះ + ប្រភេទ ប៉ុណ្ណោះ។
                            ព័ត៌មានផ្សេង (អាសយដ្ឋាន ប្រវត្តិ ។ល។) អាចបំពេញបន្ថែមក្នុងជំហានខាងក្រោម។<br>
                            <span style="color:#6979de;font-size:11px">Only Patient Code + Name + Type required. Everything
                                else can be filled in the workflow steps.</span>
                        </div>
                    </div>

                    @if ($errors->any())
                        <div class="note note-danger mb-3">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                            <ul style="margin:0;padding-left:16px">
                                @foreach ($errors->all() as $err)
                                    <li>{{ $err }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('workflow.store') }}" id="visitForm">
                        @csrf

                        {{-- Visit Meta --}}
                        <div class="sec-block" style="border-color:#4154f1;background:#4154f10d">
                            <div
                                style="font-size:10.5px;font-weight:800;text-transform:uppercase;letter-spacing:.6px;color:#4154f1;margin-bottom:12px">
                                ការចូលព្យាបាល / Visit
                            </div>
                            <div class="row g-3">
                                <div class="col-6 col-md-4">
                                    <div class="fld">
                                        <label class="flbl">
                                            <span class="km">លេខការចូល</span><span class="en">/ Code</span>
                                            <span
                                                style="font-size:9px;background:#e8f8ef;color:#2eca6a;padding:1px 7px;border-radius:10px;font-weight:700">AUTO</span>
                                        </label>
                                        <input class="form-control ro" value="{{ $nextCode }}" readonly />
                                    </div>
                                </div>
                                <div class="col-6 col-md-4">
                                    <div class="fld">
                                        <label class="flbl">
                                            <span class="km">ប្រភេទ</span><span class="en">/ Type</span>
                                            <span class="req">*</span>
                                        </label>
                                        <select name="visit_type" class="form-select">
                                            <option value="OPD" {{ old('visit_type') === 'OPD' ? 'selected' : '' }}>OPD
                                                — ព្យាបាលក្រៅ
                                            </option>
                                            <option value="IPD" {{ old('visit_type') === 'IPD' ? 'selected' : '' }}>IPD
                                                — ព្យាបាលក្នុង
                                            </option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-12 col-md-4">
                                    <div class="fld">
                                        <label class="flbl">
                                            <span class="km">ប្រភពចូល</span><span class="en">/ Admission</span>
                                            <span
                                                style="font-size:9px;background:#e6e9f0;color:#9b59b6;padding:1px 7px;border-radius:10px;font-weight:700">OPTIONAL</span>
                                        </label>
                                        <select name="admission_type" class="form-select">
                                            <option>Self Refer — ចូលដោយខ្លួនឯង</option>
                                            <option>Referred — ការបញ្ជូន</option>
                                            <option>Emergency — ករណីបន្ទាន់</option>
                                            <option>Follow-up — តាមដាន</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="fld">
                                        <label class="flbl">
                                            <span class="km">ថ្ងៃចូល</span><span class="en">/ Admitted At</span>
                                        </label>
                                        <input type="datetime-local" name="admitted_at" class="form-control"
                                            value="{{ old('admitted_at', now()->format('Y-m-d\TH:i')) }}" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Patient Identity --}}
                        <div class="sec-block" style="border-color:#2eca6a;background:#2eca6a0d">
                            <div
                                style="font-size:10.5px;font-weight:800;text-transform:uppercase;letter-spacing:.6px;color:#2eca6a;margin-bottom:12px;display:flex;align-items:center;justify-content:space-between">
                                <span>ព័ត៌មានអ្នកជំងឺ / Patient</span>
                                <span id="patientSourceBadge"
                                    style="display:none;font-size:9px;background:#e8f8ef;color:#2eca6a;padding:2px 9px;border-radius:10px;border:1px solid #a8e6c2">
                                    <i class="bi bi-check-circle-fill"></i> Pre-filled
                                </span>
                            </div>
                            <div class="row g-3">
                                <div class="col-12 col-sm-4">
                                    <div class="fld">
                                        <label class="flbl">
                                            <span class="km">លេខអ្នកជំងឺ</span><span class="en">/ Patient
                                                Code</span>
                                            <span class="req">*</span>
                                        </label>
                                        <input id="f_patient_code" name="patient_code" class="form-control ro"
                                            placeholder="PT20250317001" value="{{ old('patient_code') }}" readonly />
                                    </div>
                                </div>
                                <div class="col-6 col-sm-4">
                                    <div class="fld">
                                        <label class="flbl">
                                            <span class="km">នាមត្រកូល</span><span class="en">/ Surname</span>
                                            <span class="req">*</span>
                                        </label>
                                        <input id="f_surname" name="surname" class="form-control"
                                            value="{{ old('surname') }}" />
                                    </div>
                                </div>
                                <div class="col-6 col-sm-4">
                                    <div class="fld">
                                        <label class="flbl">
                                            <span class="km">ឈ្មោះ</span><span class="en">/ Given Name</span>
                                            <span class="req">*</span>
                                        </label>
                                        <input id="f_given_name" name="given_name" class="form-control"
                                            value="{{ old('given_name') }}" />
                                    </div>
                                </div>
                                <div class="col-6 col-sm-3">
                                    <div class="fld">
                                        <label class="flbl"><span class="km">ភេទ</span><span class="en">/
                                                Sex</span></label>
                                        <select id="f_sex" name="sex" class="form-select">
                                            <option value="">—</option>
                                            <option value="M">ប្រុស / Male</option>
                                            <option value="F">ស្រី / Female</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-6 col-sm-3">
                                    <div class="fld">
                                        <label class="flbl"><span class="km">ថ្ងៃខែ</span><span class="en">/
                                                DOB</span></label>
                                        <input id="f_birthdate" type="date" name="birthdate" class="form-control"
                                            value="{{ old('birthdate') }}" />
                                    </div>
                                </div>
                                <div class="col-6 col-sm-3">
                                    <div class="fld">
                                        <label class="flbl"><span class="km">ទូរស័ព្ទ</span><span class="en">/
                                                Phone</span></label>
                                        <input id="f_phone" type="tel" name="phone" class="form-control"
                                            placeholder="012 345 678" value="{{ old('phone') }}" />
                                    </div>
                                </div>
                                <div class="col-6 col-sm-3">
                                    <div class="fld">
                                        <label class="flbl"><span class="km">សញ្ជាតិ</span><span class="en">/
                                                Nationality</span></label>
                                        <input id="f_nationality" name="nationality" class="form-control"
                                            value="{{ old('nationality', 'ខ្មែរ') }}" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex flex-wrap gap-2 mt-3 pt-3" style="border-top:1px solid #e6e9f0">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-check2-circle"></i>
                                បង្កើត & ចាប់ផ្ដើម / Create & Start Workflow
                            </button>
                            <a href="{{ route('visits.index') }}" class="btn btn-outline-primary">
                                <i class="bi bi-x-circle"></i> បោះបង់
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- ─── RIGHT: Guide Panel ─── --}}
        <div class="col-12 col-lg-4">
            @include('clinics.workflow.guide-panel', ['steps' => $steps, 'visit' => null])
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        /* ── Patient Search Autocomplete ─────────────────────────────────────── */
        const searchInput = document.getElementById('patientSearch');
        const resultsBox = document.getElementById('searchResults');
        const resultsInner = document.getElementById('searchResultsInner');
        const selectedCard = document.getElementById('selectedPatientCard');
        let searchTimer = null;

        searchInput.addEventListener('input', () => {
            clearTimeout(searchTimer);
            const q = searchInput.value.trim();
            if (q.length < 2) {
                hideResults();
                return;
            }
            searchTimer = setTimeout(() => doSearch(q), 280);
        });

        searchInput.addEventListener('keydown', e => {
            if (e.key === 'Escape') hideResults();
        });

        document.addEventListener('click', e => {
            if (!e.target.closest('#patientSearch') && !e.target.closest('#searchResults')) hideResults();
        });

        async function doSearch(q) {
            resultsInner.innerHTML =
                `<div style="padding:14px 16px;color:#aaa;font-size:13px"><i class="bi bi-arrow-repeat" style="animation:spin .8s linear infinite"></i> ស្វែងរក…</div>`;
            resultsBox.style.display = 'block';

            try {
                const res = await fetch(`{{ route('patients.search') }}?q=${encodeURIComponent(q)}`);
                const data = await res.json();
                renderResults(data);
            } catch {
                resultsInner.innerHTML =
                    `<div style="padding:14px 16px;color:#e74c3c;font-size:13px">⚠ ស្វែងរកបរាជ័យ / Search failed</div>`;
            }
        }

        function renderResults(patients) {
            if (!patients.length) {
                resultsInner.innerHTML = `
            <div style="padding:14px 16px">
                <div style="color:#aaa;font-size:13px;margin-bottom:8px">🔍 មិនរកឃើញ / No patient found</div>
                <div style="font-size:11px;color:#4154f1;cursor:pointer" onclick="useNewPatient()">
                    <i class="bi bi-plus-circle"></i> បន្តជាអ្នកជំងឺថ្មី / Continue as new patient
                </div>
            </div>`;
                return;
            }

            resultsInner.innerHTML = patients.map(p => {
                const initials = ((p.surname?.[0] || '') + (p.name?.[0] || '')).toUpperCase();
                const colors = ['#4154f1', '#2eca6a', '#ff771d', '#e74c3c', '#9b59b6', '#00bcd4'];
                const color = colors[(p.code.charCodeAt(2) || 0) % colors.length];
                return `
        <div class="patient-result-row" onclick="selectPatient(${JSON.stringify(p).replace(/"/g, '&quot;')})"
             style="display:flex;align-items:center;gap:12px;padding:11px 16px;cursor:pointer;border-bottom:1px solid #f5f6ff;transition:background .12s"
             onmouseover="this.style.background='#f6f8fa'" onmouseout="this.style.background=''">
            <div style="width:36px;height:36px;border-radius:50%;background:${color};color:#fff;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:13px;flex-shrink:0">${initials}</div>
            <div style="flex:1;min-width:0">
                <div style="font-weight:700;font-size:13px">${p.surname}, ${p.name}</div>
                <div style="font-size:11px;color:#aaa">${p.code} ${p.phone ? '· ' + p.phone : ''} ${p.birthdate ? '· DOB: ' + p.birthdate : ''}</div>
            </div>
            <div style="text-align:right;flex-shrink:0">
                <div style="font-size:10px;background:#e6e9f0;color:#4154f1;padding:2px 8px;border-radius:10px;font-weight:700">${p.visits_count} visit${p.visits_count !== 1 ? 's' : ''}</div>
                ${p.last_visit_date ? `<div style="font-size:10px;color:#ccc;margin-top:3px">Last: ${p.last_visit_date}</div>` : ''}
            </div>
        </div>`;
            }).join('');
        }

        function selectPatient(p) {
            hideResults();
            searchInput.value = `${p.surname}, ${p.name} — ${p.code}`;

            // Fill form fields
            setField('f_patient_code', p.code);
            setField('f_surname', p.surname);
            setField('f_given_name', p.name);
            setField('f_phone', p.phone || '');
            setField('f_nationality', p.nationality || 'ខ្មែរ');
            setSelectField('f_sex', p.sex || '');

            if (p.birthdate) {
                // Convert dd/mm/yyyy → yyyy-mm-dd for date input
                const parts = p.birthdate.split('/');
                if (parts.length === 3) document.getElementById('f_birthdate').value =
                `${parts[2]}-${parts[1]}-${parts[0]}`;
            }

            // Show selected card
            const initials = ((p.surname?.[0] || '') + (p.name?.[0] || '')).toUpperCase();
            document.getElementById('spAvatar').textContent = initials;
            document.getElementById('spName').textContent = `${p.surname}, ${p.name}`;
            document.getElementById('spMeta').textContent = [p.code, p.phone, p.sex === 'M' ? 'ប្រុស' : p.sex === 'F' ?
                'ស្រី' : ''
            ].filter(Boolean).join(' · ');
            document.getElementById('spVisitCount').textContent =
                `${p.visits_count} visit${p.visits_count !== 1 ? 's' : ''}`;
            document.getElementById('spLastVisit').textContent = p.last_visit_date ?
                `Last: ${p.last_visit_type} — ${p.last_visit_date}` : 'No previous visits';
            selectedCard.style.display = 'block';

            // Show "Pre-filled" badge
            document.getElementById('patientSourceBadge').style.display = 'inline-flex';

            // Highlight filled fields briefly
            ['f_patient_code', 'f_surname', 'f_given_name', 'f_phone', 'f_sex', 'f_birthdate', 'f_nationality'].forEach(
                id => {
                    const el = document.getElementById(id);
                    if (!el) return;
                    el.style.transition = 'background .3s';
                    el.style.background = '#f0fff6';
                    setTimeout(() => el.style.background = '', 1200);
                });
        }

        function clearSelectedPatient() {
            selectedCard.style.display = 'none';
            searchInput.value = '';
            document.getElementById('patientSourceBadge').style.display = 'none';
            ['f_patient_code', 'f_surname', 'f_given_name', 'f_phone', 'f_birthdate', 'f_nationality'].forEach(id =>
                setField(id, ''));
            setSelectField('f_sex', '');
        }

        function useNewPatient() {
            hideResults();
            searchInput.value = '';
        }

        function hideResults() {
            resultsBox.style.display = 'none';
        }

        function setField(id, val) {
            const el = document.getElementById(id);
            if (el) el.value = val;
        }

        function setSelectField(id, val) {
            const el = document.getElementById(id);
            if (el) el.value = val;
        }
    </script>
    <style>
        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }
    </style>
@endpush
