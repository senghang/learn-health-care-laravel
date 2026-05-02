@extends('clinics.layout.app')
@section('title', 'New Invoice')

@section('content')

<x-page-header
    title="New Invoice"
    subtitle="វិក្កយបត្រថ្មី"
    :breadcrumbs="[
        ['label' => __('app.home'), 'url' => route('dashboard')],
        ['label' => __('app.invoices'), 'url' => route('invoices.index')],
        ['label' => 'New'],
    ]">
</x-page-header>

@if(session('error'))
    <div class="note note-danger mb-3"><i class="bi bi-exclamation-triangle-fill"></i> {{ session('error') }}</div>
@endif
@if($errors->any())
    <div class="note note-danger mb-3">
        <i class="bi bi-exclamation-triangle-fill"></i>
        <ul style="margin:0;padding-left:14px;font-size:12px">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('invoices.store') }}" novalidate id="invForm">
@csrf

{{-- Catalogs (hidden, for JS) --}}
<script id="svcCatalog" type="application/json">{!! json_encode($svcCatalog) !!}</script>
<script id="medCatalog" type="application/json">{!! json_encode($medCatalog) !!}</script>

<div class="flow-panel mb-3">
    <div class="flow-panel-title">
        <i class="bi bi-diagram-3-fill me-1"></i> Billing Flow
    </div>
    <div class="flow-steps">
        <span class="flow-step">1. Select patient</span>
        <span class="flow-step">2. Add services</span>
        <span class="flow-step">3. Add medicines</span>
        <span class="flow-step">4. Review total</span>
        <span class="flow-step">5. Create invoice</span>
    </div>
</div>

<div class="note note-warn mb-3">
    <i class="bi bi-exclamation-circle-fill"></i>
    For faster checkout: select patient first, then use quick-add catalogs for services and medicines.
</div>

<div class="row g-3">

{{-- ── Main column ─────────────────────────────────────────────────────── --}}
<div class="col-12 col-lg-8">

    {{-- Patient --}}
    <div class="card-emr mb-3">
        <div class="card-hd" style="background:#f6f9ff">
            <div class="card-hd-title"><i class="bi bi-person-fill" style="color:#4154f1"></i> Patient</div>
        </div>
        <div class="card-bd">
            <div class="row g-2">
                <div class="col-12 col-sm-8">
                    <label class="form-label" style="font-size:11px;font-weight:700;color:#666">Search Patient</label>
                    <input type="text" id="patientSearch" class="form-control form-control-sm"
                           placeholder="Name or patient code…"
                           value="{{ $patient ? $patient->surname.', '.$patient->name : '' }}"
                           autocomplete="off"/>
                    <div id="patientDropdown" style="position:absolute;z-index:999;width:340px;display:none;background:#fff;border:1px solid #e0e6f5;border-radius:8px;box-shadow:0 4px 16px rgba(0,0,0,.12);max-height:240px;overflow-y:auto;"></div>
                </div>
                <div class="col-12 col-sm-4">
                    <label class="form-label" style="font-size:11px;font-weight:700;color:#666">Patient Code</label>
                    <input type="text" id="patientCodeDisplay" class="form-control form-control-sm"
                           readonly style="background:#f6f9ff"
                           value="{{ $patient?->code ?? '' }}"/>
                </div>
            </div>
            <input type="hidden" name="patient_code" id="patientCodeInput" value="{{ old('patient_code', $patient?->code ?? '') }}" required/>
        </div>
    </div>

    {{-- Invoice Header --}}
    <div class="card-emr mb-3">
        <div class="card-hd" style="background:#f0fcff">
            <div class="card-hd-title"><i class="bi bi-receipt-cutoff" style="color:#00bcd4"></i> Invoice Details</div>
        </div>
        <div class="card-bd">
            <div class="note note-info mb-3">
                <i class="bi bi-info-circle-fill"></i>
                Confirm patient and visit code before adding bill items.
            </div>
            <div class="row g-2">
                <div class="col-6 col-sm-3">
                    <label class="form-label" style="font-size:11px;font-weight:700;color:#666">Date</label>
                    <input type="date" name="invoice_date" class="form-control form-control-sm"
                           value="{{ old('invoice_date', today()->toDateString()) }}"/>
                </div>
                <div class="col-6 col-sm-3">
                    <label class="form-label" style="font-size:11px;font-weight:700;color:#666">Payment Type</label>
                    <select name="payment_type" class="form-select form-select-sm">
                        @foreach(['CASH'=>'CASH','HEF'=>'HEF','NSSF'=>'NSSF','CARD'=>'Card','BAKONG'=>'Bakong'] as $v=>$l)
                            <option value="{{ $v }}" {{ old('payment_type','CASH')===$v?'selected':'' }}>{{ $l }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-sm-3">
                    <label class="form-label" style="font-size:11px;font-weight:700;color:#666">Cashier</label>
                    <input type="text" name="cashier" class="form-control form-control-sm"
                           value="{{ old('cashier', auth()->user()?->name) }}"/>
                </div>
                <div class="col-6 col-sm-3">
                    <label class="form-label" style="font-size:11px;font-weight:700;color:#666">Visit Code (opt.)</label>
                    <input type="text" name="visit_code" class="form-control form-control-sm"
                           value="{{ old('visit_code', $visitCode ?? '') }}" placeholder="VS-…"/>
                </div>
                <div class="col-12">
                    <label class="form-label" style="font-size:11px;font-weight:700;color:#666">Notes</label>
                    <textarea name="notes" class="form-control form-control-sm" rows="2">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>
    </div>

    {{-- Services --}}
    <div class="card-emr mb-3" id="svcSection">
        <div class="card-hd" style="background:#f0f9ff">
            <div class="card-hd-title"><i class="bi bi-grid-fill" style="color:#00bcd4"></i> Services</div>
            <button type="button" class="btn btn-outline-primary btn-sm" onclick="addSvcRow()">
                <i class="bi bi-plus-circle"></i> Add Service
            </button>
        </div>
        <div class="card-bd" style="padding:0">
            {{-- Catalog quick-add --}}
            <div style="padding:10px 14px;border-bottom:1px solid #e8eef6;background:#fafcff">
                <select id="svcCatalogSelect" class="form-select form-select-sm" onchange="addSvcFromCatalog(this)"
                        style="max-width:380px">
                    <option value="">Quick add from catalogue…</option>
                </select>
            </div>
            <div class="table-responsive">
                <table class="tbl" id="svcTable">
                    <thead>
                        <tr>
                            <th style="min-width:180px">Service Name</th>
                            <th style="min-width:100px">Category</th>
                            <th style="width:70px">Qty</th>
                            <th style="width:120px">Price (KHR)</th>
                            <th style="width:120px;text-align:right">Subtotal</th>
                            <th style="width:36px"></th>
                        </tr>
                    </thead>
                    <tbody id="svcBody">
                        {{-- Rows injected by JS or re-populated on validation fail --}}
                        @foreach(old('services', []) as $i => $svc)
                        <tr class="svc-row">
                            <td><input type="text" name="services[{{ $i }}][name]" class="form-control form-control-sm" value="{{ $svc['name'] ?? '' }}" placeholder="Service name" required/>
                                <input type="hidden" name="services[{{ $i }}][service_code]" value="{{ $svc['service_code'] ?? '' }}"/></td>
                            <td><input type="text" name="services[{{ $i }}][category]" class="form-control form-control-sm" value="{{ $svc['category'] ?? '' }}"/></td>
                            <td><input type="number" name="services[{{ $i }}][qty]" class="form-control form-control-sm svc-qty" value="{{ $svc['qty'] ?? 1 }}" min="0" step="0.5" oninput="recalc()"/></td>
                            <td><input type="number" name="services[{{ $i }}][price]" class="form-control form-control-sm svc-price" value="{{ $svc['price'] ?? 0 }}" min="0" oninput="recalc()"/></td>
                            <td style="text-align:right;font-weight:700;font-size:12px" class="svc-sub">{{ number_format(($svc['qty']??1)*($svc['price']??0)) }}</td>
                            <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('tr').remove();recalc()"><i class="bi bi-x"></i></button></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Medications --}}
    <div class="card-emr mb-3">
        <div class="card-hd" style="background:#f5eeff">
            <div class="card-hd-title"><i class="bi bi-capsule-pill" style="color:#9b59b6"></i> Medications</div>
            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="addMedRow()">
                <i class="bi bi-plus-circle"></i> Add Medicine
            </button>
        </div>
        <div class="card-bd" style="padding:0">
            <div style="padding:10px 14px;border-bottom:1px solid #e8eef6;background:#fdfaff">
                <select id="medCatalogSelect" class="form-select form-select-sm" onchange="addMedFromCatalog(this)"
                        style="max-width:380px">
                    <option value="">Quick add from formulary…</option>
                </select>
            </div>
            <div class="table-responsive">
                <table class="tbl" id="medTable">
                    <thead>
                        <tr>
                            <th style="min-width:180px">Medicine</th>
                            <th style="width:80px">Qty</th>
                            <th style="width:120px">Unit Price (KHR)</th>
                            <th style="width:120px;text-align:right">Subtotal</th>
                            <th style="width:36px"></th>
                        </tr>
                    </thead>
                    <tbody id="medBody">
                        @foreach(old('inv_meds', []) as $i => $med)
                        <tr class="med-row">
                            <td><input type="text" name="inv_meds[{{ $i }}][name]" class="form-control form-control-sm" value="{{ $med['name'] ?? '' }}" placeholder="Medicine name" required/>
                                <input type="hidden" name="inv_meds[{{ $i }}][medicine_id]" value="{{ $med['medicine_id'] ?? '' }}"/>
                                <input type="hidden" name="inv_meds[{{ $i }}][medicine_code]" value="{{ $med['medicine_code'] ?? '' }}"/></td>
                            <td><input type="number" name="inv_meds[{{ $i }}][qty]" class="form-control form-control-sm med-qty" value="{{ $med['qty'] ?? 1 }}" min="0" oninput="recalc()"/></td>
                            <td><input type="number" name="inv_meds[{{ $i }}][price]" class="form-control form-control-sm med-price" value="{{ $med['price'] ?? 0 }}" min="0" oninput="recalc()"/></td>
                            <td style="text-align:right;font-weight:700;font-size:12px" class="med-sub">{{ number_format(($med['qty']??1)*($med['price']??0)) }}</td>
                            <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('tr').remove();recalc()"><i class="bi bi-x"></i></button></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>{{-- /col-lg-8 --}}

{{-- ── Sidebar ──────────────────────────────────────────────────────────── --}}
<div class="col-12 col-lg-4">
    <div class="card-emr" style="position:sticky;top:76px">
        <div class="card-hd" style="background:#f6f9ff">
            <div class="card-hd-title"><i class="bi bi-calculator-fill" style="color:#4154f1"></i> Summary</div>
        </div>
        <div class="card-bd">

            <div style="background:#f0f9ff;border-radius:10px;padding:14px;margin-bottom:14px">
                <div style="display:flex;justify-content:space-between;font-size:12px;margin-bottom:6px">
                    <span style="color:#555">Services</span>
                    <span id="svcTotalDisplay" style="font-weight:700;color:#00bcd4">0 KHR</span>
                </div>
                <div style="display:flex;justify-content:space-between;font-size:12px;margin-bottom:10px">
                    <span style="color:#555">Medications</span>
                    <span id="medTotalDisplay" style="font-weight:700;color:#9b59b6">0 KHR</span>
                </div>
                <div style="display:flex;justify-content:space-between;font-size:12px;margin-bottom:6px">
                    <span style="color:#555">Discount</span>
                    <span>
                        <input type="number" name="discount_total" id="discountInput"
                               class="form-control form-control-sm" style="width:100px;display:inline-block;text-align:right"
                               value="{{ old('discount_total', 0) }}" min="0" oninput="recalc()"/>
                    </span>
                </div>
                <div style="border-top:1px dashed #c0d8e0;padding-top:10px;margin-top:6px">
                    <div style="display:flex;justify-content:space-between;font-size:16px;font-weight:800;color:#012970">
                        <span>Total</span>
                        <span id="grandTotalDisplay">0 KHR</span>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-w100 mb-2">
                <i class="bi bi-floppy-fill"></i> Create Invoice
            </button>
            <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary btn-w100">
                <i class="bi bi-x-circle"></i> Cancel
            </a>

            <div class="note note-info mt-3" style="font-size:11px">
                <i class="bi bi-check2-square"></i>
                Review total and discount before saving.
            </div>

        </div>
    </div>
</div>

</div>{{-- /row --}}
</form>

@endsection

@push('scripts')
<script>
const svcs = JSON.parse(document.getElementById('svcCatalog').textContent);
const meds = JSON.parse(document.getElementById('medCatalog').textContent);

// Populate catalog selects
const svcSel = document.getElementById('svcCatalogSelect');
svcs.forEach(s => {
    const o = new Option(`${s.name} (${s.category||'—'}) — ${Number(s.price).toLocaleString()} KHR`, s.id);
    o._data = s; svcSel.add(o);
});
const medSel = document.getElementById('medCatalogSelect');
meds.forEach(m => {
    const label = `${m.name}${m.strength?' '+m.strength:''} ${m.form||''} — ${Number(m.price).toLocaleString()} KHR (stock: ${m.stock})`;
    const o = new Option(label, m.id); o._data = m; medSel.add(o);
});

let svcIdx = {{ count(old('services', [])) }};
let medIdx = {{ count(old('inv_meds', [])) }};

function addSvcRow(data) {
    const i = svcIdx++;
    const tr = document.createElement('tr');
    tr.className = 'svc-row';
    tr.innerHTML = `
        <td>
            <input type="text" name="services[${i}][name]" class="form-control form-control-sm"
                   value="${data?.name||''}" placeholder="Service name" required/>
            <input type="hidden" name="services[${i}][service_code]" value="${data?.code||''}"/>
        </td>
        <td><input type="text" name="services[${i}][category]" class="form-control form-control-sm" value="${data?.category||''}"/></td>
        <td><input type="number" name="services[${i}][qty]" class="form-control form-control-sm svc-qty"
                   value="1" min="0" step="0.5" oninput="recalc()"/></td>
        <td><input type="number" name="services[${i}][price]" class="form-control form-control-sm svc-price"
                   value="${data?.price||0}" min="0" oninput="recalc()"/></td>
        <td style="text-align:right;font-weight:700;font-size:12px" class="svc-sub">${Number(data?.price||0).toLocaleString()}</td>
        <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('tr').remove();recalc()"><i class="bi bi-x"></i></button></td>`;
    document.getElementById('svcBody').appendChild(tr);
    recalc();
}

function addMedRow(data) {
    const i = medIdx++;
    const tr = document.createElement('tr');
    tr.className = 'med-row';
    const stockBadge = data?.stock !== undefined
        ? `<small style="color:${data.stock<=0?'#e74c3c':data.stock<=(data.stock_alert||10)?'#ff771d':'#2eca6a'};font-size:10px"> (stock: ${data.stock})</small>`
        : '';
    tr.innerHTML = `
        <td>
            <input type="text" name="inv_meds[${i}][name]" class="form-control form-control-sm"
                   value="${data?.name||''}" placeholder="Medicine name" required/>
            ${stockBadge}
            <input type="hidden" name="inv_meds[${i}][medicine_id]" value="${data?.id||''}"/>
            <input type="hidden" name="inv_meds[${i}][medicine_code]" value="${data?.code||''}"/>
        </td>
        <td><input type="number" name="inv_meds[${i}][qty]" class="form-control form-control-sm med-qty"
                   value="1" min="0" oninput="recalc()"/></td>
        <td><input type="number" name="inv_meds[${i}][price]" class="form-control form-control-sm med-price"
                   value="${data?.price||0}" min="0" oninput="recalc()"/></td>
        <td style="text-align:right;font-weight:700;font-size:12px" class="med-sub">${Number(data?.price||0).toLocaleString()}</td>
        <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('tr').remove();recalc()"><i class="bi bi-x"></i></button></td>`;
    document.getElementById('medBody').appendChild(tr);
    recalc();
}

function addSvcFromCatalog(sel) {
    const opt = sel.options[sel.selectedIndex];
    if (!opt._data) return;
    addSvcRow(opt._data);
    sel.selectedIndex = 0;
}
function addMedFromCatalog(sel) {
    const opt = sel.options[sel.selectedIndex];
    if (!opt._data) return;
    addMedRow(opt._data);
    sel.selectedIndex = 0;
}

function recalc() {
    let svcTotal = 0, medTotal = 0;
    document.querySelectorAll('.svc-row').forEach(tr => {
        const q = parseFloat(tr.querySelector('.svc-qty')?.value||0)||0;
        const p = parseFloat(tr.querySelector('.svc-price')?.value||0)||0;
        const sub = q * p;
        const cell = tr.querySelector('.svc-sub');
        if (cell) cell.textContent = sub.toLocaleString('en-US') + ' KHR';
        svcTotal += sub;
    });
    document.querySelectorAll('.med-row').forEach(tr => {
        const q = parseFloat(tr.querySelector('.med-qty')?.value||0)||0;
        const p = parseFloat(tr.querySelector('.med-price')?.value||0)||0;
        const sub = q * p;
        const cell = tr.querySelector('.med-sub');
        if (cell) cell.textContent = sub.toLocaleString('en-US') + ' KHR';
        medTotal += sub;
    });
    const disc = parseFloat(document.getElementById('discountInput').value||0)||0;
    const grand = svcTotal + medTotal - disc;
    document.getElementById('svcTotalDisplay').textContent = svcTotal.toLocaleString('en-US') + ' KHR';
    document.getElementById('medTotalDisplay').textContent = medTotal.toLocaleString('en-US') + ' KHR';
    document.getElementById('grandTotalDisplay').textContent = grand.toLocaleString('en-US') + ' KHR';
}

// Patient AJAX search
const patientSearchInput = document.getElementById('patientSearch');
const patientDropdown    = document.getElementById('patientDropdown');
const patientCodeDisplay = document.getElementById('patientCodeDisplay');
const patientCodeInput   = document.getElementById('patientCodeInput');
let searchTimer;

patientSearchInput.addEventListener('input', function() {
    clearTimeout(searchTimer);
    const q = this.value.trim();
    if (q.length < 2) { patientDropdown.style.display='none'; return; }
    searchTimer = setTimeout(() => {
        fetch(`{{ route('patients.search') }}?q=${encodeURIComponent(q)}`)
            .then(r => r.json()).then(data => {
                patientDropdown.innerHTML = '';
                if (!data.length) { patientDropdown.style.display='none'; return; }
                data.forEach(p => {
                    const div = document.createElement('div');
                    div.style.cssText = 'padding:8px 12px;cursor:pointer;border-bottom:1px solid #f0f0f0;font-size:12px';
                    div.innerHTML = `<strong>${p.surname}, ${p.name}</strong> <span style="color:#aaa">${p.code}</span>`;
                    div.addEventListener('mouseenter', () => div.style.background='#f6f9ff');
                    div.addEventListener('mouseleave', () => div.style.background='');
                    div.addEventListener('click', () => {
                        patientSearchInput.value  = `${p.surname}, ${p.name}`;
                        patientCodeDisplay.value  = p.code;
                        patientCodeInput.value    = p.code;
                        patientDropdown.style.display = 'none';
                    });
                    patientDropdown.appendChild(div);
                });
                patientDropdown.style.display = 'block';
            });
    }, 300);
});
document.addEventListener('click', e => {
    if (!e.target.closest('#patientSearch') && !e.target.closest('#patientDropdown'))
        patientDropdown.style.display = 'none';
});

// Init
recalc();
</script>
@endpush
