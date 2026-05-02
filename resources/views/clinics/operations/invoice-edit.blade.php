@extends('clinics.layout.app')
@section('title', 'Edit ' . $invoice->code)

@section('content')

<x-page-header
    title="Edit {{ $invoice->code }}"
    subtitle="កែសម្រួលវិក្កយបត្រ"
    :breadcrumbs="[
        ['label' => __('app.home'), 'url' => route('dashboard')],
        ['label' => __('app.invoices'), 'url' => route('invoices.index')],
        ['label' => $invoice->code, 'url' => route('invoices.show', $invoice->code)],
        ['label' => 'Edit'],
    ]">
    {{-- Void button --}}
    <form method="POST" action="{{ route('invoices.void', $invoice->code) }}"
          data-confirm="Void invoice #{{ $invoice->code }}? It will be marked as voided and cannot be reversed."
          data-confirm-type="warn" data-confirm-title="Void Invoice">
        @csrf
        <button type="submit" class="btn btn-outline-danger btn-sm">
            <i class="bi bi-slash-circle"></i> Void Invoice
        </button>
    </form>
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

<form method="POST" action="{{ route('invoices.update', $invoice->code) }}" novalidate id="invForm">
@csrf @method('PATCH')

{{-- Catalogs (hidden, for JS) --}}
<script id="svcCatalog" type="application/json">{!! json_encode($svcCatalog) !!}</script>
<script id="medCatalog" type="application/json">{!! json_encode($medCatalog) !!}</script>

<div class="row g-3">

{{-- ── Main column ─────────────────────────────────────────────────────── --}}
<div class="col-12 col-lg-8">

    {{-- Patient (read-only on edit) --}}
    <div class="card-emr mb-3">
        <div class="card-hd" style="background:#f6f9ff">
            <div class="card-hd-title"><i class="bi bi-person-fill" style="color:#4154f1"></i> Patient</div>
            <span style="font-size:11px;color:#aaa">Cannot change patient on edit</span>
        </div>
        <div class="card-bd">
            @if($invoice->patient)
                <div style="font-weight:700;color:#012970">{{ $invoice->patient->surname }}, {{ $invoice->patient->name }}</div>
                <div style="font-size:11px;color:#aaa">{{ $invoice->patient->code }} · {{ $invoice->patient->phone ?? '—' }}</div>
            @endif
        </div>
    </div>

    {{-- Invoice Header --}}
    <div class="card-emr mb-3">
        <div class="card-hd" style="background:#f0fcff">
            <div class="card-hd-title"><i class="bi bi-receipt-cutoff" style="color:#00bcd4"></i> Invoice Details</div>
        </div>
        <div class="card-bd">
            <div class="row g-2">
                <div class="col-6 col-sm-3">
                    <label class="form-label" style="font-size:11px;font-weight:700;color:#666">Date</label>
                    <input type="date" name="invoice_date" class="form-control form-control-sm"
                           value="{{ old('invoice_date', $invoice->invoice_date?->toDateString()) }}"/>
                </div>
                <div class="col-6 col-sm-3">
                    <label class="form-label" style="font-size:11px;font-weight:700;color:#666">Payment Type</label>
                    <select name="payment_type" class="form-select form-select-sm">
                        @foreach(['CASH'=>'CASH','HEF'=>'HEF','NSSF'=>'NSSF','CARD'=>'Card'] as $v=>$l)
                            <option value="{{ $v }}" {{ old('payment_type',$invoice->payment_type)===$v?'selected':'' }}>{{ $l }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-sm-3">
                    <label class="form-label" style="font-size:11px;font-weight:700;color:#666">Cashier</label>
                    <input type="text" name="cashier" class="form-control form-control-sm"
                           value="{{ old('cashier', $invoice->cashier) }}"/>
                </div>
                <div class="col-6 col-sm-3">
                    <label class="form-label" style="font-size:11px;font-weight:700;color:#666">Status</label>
                    <div style="padding:6px 10px;font-size:12px;font-weight:700;color:#555;background:#f6f9ff;border-radius:6px;border:1px solid #e0e6f5">
                        {{ strtoupper($invoice->status) }}
                    </div>
                </div>
                <div class="col-12">
                    <label class="form-label" style="font-size:11px;font-weight:700;color:#666">Notes</label>
                    <textarea name="notes" class="form-control form-control-sm" rows="2">{{ old('notes', $invoice->notes) }}</textarea>
                </div>
            </div>
        </div>
    </div>

    {{-- Services --}}
    <div class="card-emr mb-3">
        <div class="card-hd" style="background:#f0f9ff">
            <div class="card-hd-title"><i class="bi bi-grid-fill" style="color:#00bcd4"></i> Services</div>
            <button type="button" class="btn btn-outline-primary btn-sm" onclick="addSvcRow()">
                <i class="bi bi-plus-circle"></i> Add Service
            </button>
        </div>
        <div class="card-bd" style="padding:0">
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
                        @php $svcOld = old('services'); @endphp
                        @if($svcOld !== null)
                            @foreach($svcOld as $i => $svc)
                            <tr class="svc-row">
                                <td><input type="text" name="services[{{ $i }}][name]" class="form-control form-control-sm" value="{{ $svc['name']??'' }}" required/>
                                    <input type="hidden" name="services[{{ $i }}][service_code]" value="{{ $svc['service_code']??'' }}"/></td>
                                <td><input type="text" name="services[{{ $i }}][category]" class="form-control form-control-sm" value="{{ $svc['category']??'' }}"/></td>
                                <td><input type="number" name="services[{{ $i }}][qty]" class="form-control form-control-sm svc-qty" value="{{ $svc['qty']??1 }}" min="0" step="0.5" oninput="recalc()"/></td>
                                <td><input type="number" name="services[{{ $i }}][price]" class="form-control form-control-sm svc-price" value="{{ $svc['price']??0 }}" min="0" oninput="recalc()"/></td>
                                <td style="text-align:right;font-weight:700;font-size:12px" class="svc-sub">{{ number_format(($svc['qty']??1)*($svc['price']??0)) }}</td>
                                <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('tr').remove();recalc()"><i class="bi bi-x"></i></button></td>
                            </tr>
                            @endforeach
                        @else
                            @foreach($invoice->services as $i => $svc)
                            <tr class="svc-row">
                                <td><input type="text" name="services[{{ $i }}][name]" class="form-control form-control-sm" value="{{ $svc->service_name }}" required/>
                                    <input type="hidden" name="services[{{ $i }}][service_code]" value="{{ $svc->service_code }}"/></td>
                                <td><input type="text" name="services[{{ $i }}][category]" class="form-control form-control-sm" value="{{ $svc->service_category }}"/></td>
                                <td><input type="number" name="services[{{ $i }}][qty]" class="form-control form-control-sm svc-qty" value="{{ $svc->qty ?? 1 }}" min="0" step="0.5" oninput="recalc()"/></td>
                                <td><input type="number" name="services[{{ $i }}][price]" class="form-control form-control-sm svc-price" value="{{ $svc->price }}" min="0" oninput="recalc()"/></td>
                                <td style="text-align:right;font-weight:700;font-size:12px" class="svc-sub">{{ number_format(($svc->qty??1)*$svc->price) }}</td>
                                <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('tr').remove();recalc()"><i class="bi bi-x"></i></button></td>
                            </tr>
                            @endforeach
                        @endif
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
                        @php $medOld = old('inv_meds'); @endphp
                        @if($medOld !== null)
                            @foreach($medOld as $i => $med)
                            <tr class="med-row">
                                <td><input type="text" name="inv_meds[{{ $i }}][name]" class="form-control form-control-sm" value="{{ $med['name']??'' }}" required/>
                                    <input type="hidden" name="inv_meds[{{ $i }}][medicine_id]" value="{{ $med['medicine_id']??'' }}"/>
                                    <input type="hidden" name="inv_meds[{{ $i }}][medicine_code]" value="{{ $med['medicine_code']??'' }}"/></td>
                                <td><input type="number" name="inv_meds[{{ $i }}][qty]" class="form-control form-control-sm med-qty" value="{{ $med['qty']??1 }}" min="0" oninput="recalc()"/></td>
                                <td><input type="number" name="inv_meds[{{ $i }}][price]" class="form-control form-control-sm med-price" value="{{ $med['price']??0 }}" min="0" oninput="recalc()"/></td>
                                <td style="text-align:right;font-weight:700;font-size:12px" class="med-sub">{{ number_format(($med['qty']??1)*($med['price']??0)) }}</td>
                                <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('tr').remove();recalc()"><i class="bi bi-x"></i></button></td>
                            </tr>
                            @endforeach
                        @else
                            @foreach($invoice->medications as $i => $med)
                            <tr class="med-row">
                                <td><input type="text" name="inv_meds[{{ $i }}][name]" class="form-control form-control-sm" value="{{ $med->medicine_name }}" required/>
                                    <input type="hidden" name="inv_meds[{{ $i }}][medicine_id]" value="{{ $med->medicine_id }}"/>
                                    <input type="hidden" name="inv_meds[{{ $i }}][medicine_code]" value="{{ $med->medicine_code }}"/></td>
                                <td><input type="number" name="inv_meds[{{ $i }}][qty]" class="form-control form-control-sm med-qty" value="{{ $med->quantity }}" min="0" oninput="recalc()"/></td>
                                <td><input type="number" name="inv_meds[{{ $i }}][price]" class="form-control form-control-sm med-price" value="{{ $med->price }}" min="0" oninput="recalc()"/></td>
                                <td style="text-align:right;font-weight:700;font-size:12px" class="med-sub">{{ number_format($med->quantity * $med->price) }}</td>
                                <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('tr').remove();recalc()"><i class="bi bi-x"></i></button></td>
                            </tr>
                            @endforeach
                        @endif
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

            {{-- Payments already collected --}}
            @php $paid = $invoice->payments->sum('amount'); @endphp
            @if($paid > 0)
            <div class="note note-warn mb-3" style="font-size:12px">
                <i class="bi bi-info-circle-fill"></i>
                {{ number_format($paid) }} KHR already collected. Editing the total may affect balance.
            </div>
            @endif

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
                               value="{{ old('discount_total', $invoice->discount_total) }}" min="0" oninput="recalc()"/>
                    </span>
                </div>
                <div style="border-top:1px dashed #c0d8e0;padding-top:10px;margin-top:6px">
                    <div style="display:flex;justify-content:space-between;font-size:16px;font-weight:800;color:#012970">
                        <span>New Total</span>
                        <span id="grandTotalDisplay">0 KHR</span>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-warning btn-w100 mb-2">
                <i class="bi bi-floppy-fill"></i> Save Changes
            </button>
            <a href="{{ route('invoices.show', $invoice->code) }}" class="btn btn-outline-secondary btn-w100 mb-2">
                <i class="bi bi-x-circle"></i> Cancel
            </a>

            {{-- Delete (only if pending + no payments) --}}
            @if($invoice->status === 'pending' && $paid == 0)
            <form method="POST" action="{{ route('invoices.destroy', $invoice->code) }}"
                  data-confirm="Permanently delete invoice #{{ $invoice->code }}? This cannot be undone."
                  data-confirm-type="danger" data-confirm-title="Delete Invoice">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-outline-danger btn-w100 btn-sm mt-1">
                    <i class="bi bi-trash3-fill"></i> Delete Invoice
                </button>
            </form>
            @endif

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

let svcIdx = document.querySelectorAll('.svc-row').length;
let medIdx = document.querySelectorAll('.med-row').length;

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

recalc();
</script>
@endpush
