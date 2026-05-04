@extends('clinics.layout.app')
@section('title', 'New Invoice')

@section('content')

<x-ui.page-header
    km="វិក្កយបត្រថ្មី"
    title="New Invoice"
    :breadcrumbs="[
        ['label' => __('app.home'), 'url' => route('dashboard')],
        ['label' => __('app.invoices'), 'url' => route('invoices.index')],
        ['label' => 'New'],
    ]">
</x-ui.page-header>

@if(session('error'))
    <x-ui.alert type="error" class="mb-4">{{ session('error') }}</x-ui.alert>
@endif
@if($errors->any())
    <x-ui.alert type="error" class="mb-4">
        <ul class="list-disc pl-4 text-xs space-y-0.5">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </x-ui.alert>
@endif

{{-- Catalogs (hidden, for JS) --}}
<script id="svcCatalog" type="application/json">{!! json_encode($svcCatalog) !!}</script>
<script id="medCatalog" type="application/json">{!! json_encode($medCatalog) !!}</script>

{{-- Billing flow steps --}}
<div class="flex items-center gap-2 mb-4 overflow-x-auto pb-1">
    @foreach(['1. Select patient','2. Add services','3. Add medicines','4. Review total','5. Create invoice'] as $step)
        <span class="text-xs font-semibold px-3 py-1.5 rounded-full whitespace-nowrap flex-shrink-0"
              style="background:#eef0fd;color:#4154f1">{{ $step }}</span>
        @if(!$loop->last)<i class="bi bi-chevron-right text-xs flex-shrink-0" style="color:#c5cbf9" aria-hidden="true"></i>@endif
    @endforeach
</div>

<x-ui.alert type="info" class="mb-4">
    Select patient first, then use quick-add catalogs for services and medicines.
</x-ui.alert>

<form method="POST" action="{{ route('invoices.store') }}" novalidate id="invForm">
@csrf

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

    {{-- ── Main Column ──────────────────────────────────────────── --}}
    <div class="lg:col-span-2 space-y-4">

        {{-- Patient --}}
        <x-ui.card>
            <x-slot:header>
                <div class="flex items-center gap-2 px-5 py-4" style="border-bottom:1px solid #e6e9f0;background:#f9fafb">
                    <i class="bi bi-person-fill" style="color:#4154f1;font-size:15px" aria-hidden="true"></i>
                    <span class="text-sm font-bold" style="color:#1a1f36">Patient</span>
                </div>
            </x-slot:header>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="sm:col-span-2 space-y-1.5">
                    <label class="block text-xs font-semibold" style="color:#374151">Search Patient</label>
                    <div class="relative">
                        <input type="text" id="patientSearch"
                               value="{{ $patient ? $patient->surname.', '.$patient->name : '' }}"
                               placeholder="Name or patient code…" autocomplete="off"
                               class="w-full text-sm rounded-lg border border-[#e2e8f0] bg-white px-3 py-2.5 text-[#374151] placeholder-[#9ca3af] focus:outline-none focus:border-[#4154f1] focus:ring-2 focus:ring-[#4154f1]/20 transition-colors" />
                        <div id="patientDropdown"
                             class="absolute left-0 right-0 mt-1 bg-white rounded-xl shadow-lg overflow-y-auto z-50"
                             style="display:none;border:1px solid #e0e6f5;max-height:240px"></div>
                    </div>
                    <input type="hidden" name="patient_code" id="patientCodeInput"
                           value="{{ old('patient_code', $patient?->code ?? '') }}" required />
                </div>
                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold" style="color:#374151">Patient Code</label>
                    <input type="text" id="patientCodeDisplay" readonly
                           value="{{ $patient?->code ?? '' }}"
                           class="w-full text-sm rounded-lg border border-[#e2e8f0] bg-[#f9fafb] px-3 py-2.5 font-mono font-bold"
                           style="color:#4154f1" />
                </div>
            </div>
        </x-ui.card>

        {{-- Invoice Details --}}
        <x-ui.card>
            <x-slot:header>
                <div class="flex items-center gap-2 px-5 py-4" style="border-bottom:1px solid #e6e9f0;background:#f0fcff">
                    <i class="bi bi-receipt-cutoff" style="color:#00bcd4;font-size:15px" aria-hidden="true"></i>
                    <span class="text-sm font-bold" style="color:#1a1f36">Invoice Details</span>
                </div>
            </x-slot:header>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold" style="color:#374151">Date</label>
                    <x-forms.input type="date" name="invoice_date"
                                   :value="old('invoice_date', today()->toDateString())" />
                </div>
                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold" style="color:#374151">Payment Type</label>
                    <x-forms.select name="payment_type">
                        @foreach(['CASH'=>'CASH','HEF'=>'HEF','NSSF'=>'NSSF','CARD'=>'Card','BAKONG'=>'Bakong'] as $v => $l)
                            <option value="{{ $v }}" @selected(old('payment_type','CASH') === $v)>{{ $l }}</option>
                        @endforeach
                    </x-forms.select>
                </div>
                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold" style="color:#374151">Cashier</label>
                    <x-forms.input name="cashier" :value="old('cashier', auth()->user()?->name)" />
                </div>
                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold" style="color:#374151">Visit Code</label>
                    <x-forms.input name="visit_code" placeholder="VS-…"
                                   :value="old('visit_code', $visitCode ?? '')" />
                </div>
                <div class="sm:col-span-4 space-y-1.5">
                    <label class="block text-xs font-semibold" style="color:#374151">Notes</label>
                    <textarea name="notes" rows="2"
                              class="w-full text-sm rounded-lg border border-[#e2e8f0] bg-white px-3 py-2.5 text-[#374151] placeholder-[#9ca3af] focus:outline-none focus:border-[#4154f1] focus:ring-2 focus:ring-[#4154f1]/20 transition-colors resize-none">{{ old('notes') }}</textarea>
                </div>
            </div>
        </x-ui.card>

        {{-- Services --}}
        <x-ui.card :noPadding="true">
            <x-slot:header>
                <div class="flex items-center justify-between px-5 py-3" style="border-bottom:1px solid #e6e9f0;background:#f0f9ff">
                    <div class="flex items-center gap-2">
                        <i class="bi bi-grid-fill" style="color:#00bcd4;font-size:14px" aria-hidden="true"></i>
                        <span class="text-sm font-bold" style="color:#1a1f36">Services</span>
                    </div>
                    <x-ui.button type="button" variant="primary" size="sm" onclick="addSvcRow()">
                        <x-slot:icon><i class="bi bi-plus-circle" aria-hidden="true"></i></x-slot:icon>
                        Add Service
                    </x-ui.button>
                </div>
                <div class="px-5 py-2.5" style="border-bottom:1px solid #f0f0f0;background:#fafcff">
                    <div class="relative">
                        <select id="svcCatalogSelect" onchange="addSvcFromCatalog(this)"
                                class="w-full sm:w-96 text-sm rounded-lg border border-[#e2e8f0] bg-white px-3 py-2 text-[#374151] appearance-none focus:outline-none focus:border-[#4154f1] transition-colors"
                                style="padding-right:2.5rem">
                            <option value="">Quick add from catalogue…</option>
                        </select>
                        <div class="absolute inset-y-0 right-0 sm:right-auto flex items-center pointer-events-none"
                             style="right:calc(100% - 24rem - 12px)">
                            <i class="bi bi-chevron-down text-xs pr-3" style="color:#6b7280" aria-hidden="true"></i>
                        </div>
                    </div>
                </div>
            </x-slot:header>
            <div class="overflow-x-auto">
                <table class="w-full text-sm" id="svcTable">
                    <thead>
                        <tr style="background:#f8f9fb;border-bottom:1px solid #e6e9f0">
                            <th class="text-left px-4 py-2.5 text-xs font-bold" style="color:#6b7280;min-width:180px">Service Name</th>
                            <th class="text-left px-4 py-2.5 text-xs font-bold hidden md:table-cell" style="color:#6b7280;min-width:100px">Category</th>
                            <th class="text-center px-4 py-2.5 text-xs font-bold" style="color:#6b7280;width:70px">Qty</th>
                            <th class="text-right px-4 py-2.5 text-xs font-bold" style="color:#6b7280;width:130px">Price (KHR)</th>
                            <th class="text-right px-4 py-2.5 text-xs font-bold" style="color:#6b7280;width:120px">Subtotal</th>
                            <th style="width:36px"></th>
                        </tr>
                    </thead>
                    <tbody id="svcBody">
                        @foreach(old('services', []) as $i => $svc)
                            <tr class="svc-row" style="border-bottom:1px solid #f8f9fb">
                                <td class="px-4 py-2">
                                    <input type="text" name="services[{{ $i }}][name]" value="{{ $svc['name'] ?? '' }}"
                                           placeholder="Service name" required class="w-full text-xs rounded border border-[#e2e8f0] px-2 py-1.5 focus:outline-none focus:border-[#4154f1]"/>
                                    <input type="hidden" name="services[{{ $i }}][service_code]" value="{{ $svc['service_code'] ?? '' }}"/>
                                </td>
                                <td class="px-4 py-2 hidden md:table-cell">
                                    <input type="text" name="services[{{ $i }}][category]" value="{{ $svc['category'] ?? '' }}"
                                           class="w-full text-xs rounded border border-[#e2e8f0] px-2 py-1.5 focus:outline-none focus:border-[#4154f1]"/>
                                </td>
                                <td class="px-4 py-2">
                                    <input type="number" name="services[{{ $i }}][qty]" value="{{ $svc['qty'] ?? 1 }}"
                                           min="0" step="0.5" oninput="recalc()"
                                           class="svc-qty w-16 text-xs text-center rounded border border-[#e2e8f0] px-2 py-1.5 focus:outline-none focus:border-[#4154f1]"/>
                                </td>
                                <td class="px-4 py-2">
                                    <input type="number" name="services[{{ $i }}][price]" value="{{ $svc['price'] ?? 0 }}"
                                           min="0" oninput="recalc()"
                                           class="svc-price w-full text-xs text-right rounded border border-[#e2e8f0] px-2 py-1.5 focus:outline-none focus:border-[#4154f1]"/>
                                </td>
                                <td class="px-4 py-2 text-right text-xs font-bold svc-sub" style="color:#1a1f36">
                                    {{ number_format(($svc['qty']??1)*($svc['price']??0)) }}
                                </td>
                                <td class="px-2 py-2">
                                    <button type="button" onclick="this.closest('tr').remove();recalc()"
                                            class="w-7 h-7 flex items-center justify-center rounded-md transition-colors hover:bg-[#fde8e8]"
                                            style="color:#e74c3c;border:none;background:none">
                                        <i class="bi bi-x-lg text-xs" aria-hidden="true"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-ui.card>

        {{-- Medications --}}
        <x-ui.card :noPadding="true">
            <x-slot:header>
                <div class="flex items-center justify-between px-5 py-3" style="border-bottom:1px solid #e6e9f0;background:#f5eeff">
                    <div class="flex items-center gap-2">
                        <i class="bi bi-capsule-pill" style="color:#9b59b6;font-size:14px" aria-hidden="true"></i>
                        <span class="text-sm font-bold" style="color:#1a1f36">Medications</span>
                    </div>
                    <x-ui.button type="button" variant="secondary" size="sm" onclick="addMedRow()">
                        <x-slot:icon><i class="bi bi-plus-circle" aria-hidden="true"></i></x-slot:icon>
                        Add Medicine
                    </x-ui.button>
                </div>
                <div class="px-5 py-2.5" style="border-bottom:1px solid #f0f0f0;background:#fdfaff">
                    <div class="relative">
                        <select id="medCatalogSelect" onchange="addMedFromCatalog(this)"
                                class="w-full sm:w-96 text-sm rounded-lg border border-[#e2e8f0] bg-white px-3 py-2 text-[#374151] appearance-none focus:outline-none focus:border-[#4154f1] transition-colors"
                                style="padding-right:2.5rem">
                            <option value="">Quick add from formulary…</option>
                        </select>
                    </div>
                </div>
            </x-slot:header>
            <div class="overflow-x-auto">
                <table class="w-full text-sm" id="medTable">
                    <thead>
                        <tr style="background:#f8f9fb;border-bottom:1px solid #e6e9f0">
                            <th class="text-left px-4 py-2.5 text-xs font-bold" style="color:#6b7280;min-width:180px">Medicine</th>
                            <th class="text-center px-4 py-2.5 text-xs font-bold" style="color:#6b7280;width:80px">Qty</th>
                            <th class="text-right px-4 py-2.5 text-xs font-bold" style="color:#6b7280;width:130px">Unit Price (KHR)</th>
                            <th class="text-right px-4 py-2.5 text-xs font-bold" style="color:#6b7280;width:120px">Subtotal</th>
                            <th style="width:36px"></th>
                        </tr>
                    </thead>
                    <tbody id="medBody">
                        @foreach(old('inv_meds', []) as $i => $med)
                            <tr class="med-row" style="border-bottom:1px solid #f8f9fb">
                                <td class="px-4 py-2">
                                    <input type="text" name="inv_meds[{{ $i }}][name]" value="{{ $med['name'] ?? '' }}"
                                           placeholder="Medicine name" required
                                           class="w-full text-xs rounded border border-[#e2e8f0] px-2 py-1.5 focus:outline-none focus:border-[#4154f1]"/>
                                    <input type="hidden" name="inv_meds[{{ $i }}][medicine_id]" value="{{ $med['medicine_id'] ?? '' }}"/>
                                    <input type="hidden" name="inv_meds[{{ $i }}][medicine_code]" value="{{ $med['medicine_code'] ?? '' }}"/>
                                </td>
                                <td class="px-4 py-2">
                                    <input type="number" name="inv_meds[{{ $i }}][qty]" value="{{ $med['qty'] ?? 1 }}"
                                           min="0" oninput="recalc()"
                                           class="med-qty w-16 text-xs text-center rounded border border-[#e2e8f0] px-2 py-1.5 focus:outline-none focus:border-[#4154f1]"/>
                                </td>
                                <td class="px-4 py-2">
                                    <input type="number" name="inv_meds[{{ $i }}][price]" value="{{ $med['price'] ?? 0 }}"
                                           min="0" oninput="recalc()"
                                           class="med-price w-full text-xs text-right rounded border border-[#e2e8f0] px-2 py-1.5 focus:outline-none focus:border-[#4154f1]"/>
                                </td>
                                <td class="px-4 py-2 text-right text-xs font-bold med-sub" style="color:#1a1f36">
                                    {{ number_format(($med['qty']??1)*($med['price']??0)) }}
                                </td>
                                <td class="px-2 py-2">
                                    <button type="button" onclick="this.closest('tr').remove();recalc()"
                                            class="w-7 h-7 flex items-center justify-center rounded-md transition-colors hover:bg-[#fde8e8]"
                                            style="color:#e74c3c;border:none;background:none">
                                        <i class="bi bi-x-lg text-xs" aria-hidden="true"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-ui.card>

    </div>{{-- /main --}}

    {{-- ── Sidebar: Summary + Submit ──────────────────────────── --}}
    <div>
        <x-ui.card class="sticky top-20">
            <x-slot:header>
                <div class="flex items-center gap-2 px-5 py-4" style="border-bottom:1px solid #e6e9f0;background:#f9fafb">
                    <i class="bi bi-calculator-fill" style="color:#4154f1;font-size:15px" aria-hidden="true"></i>
                    <span class="text-sm font-bold" style="color:#1a1f36">Summary</span>
                </div>
            </x-slot:header>

            {{-- Totals --}}
            <div class="p-4 rounded-xl mb-4" style="background:#f0f9ff">
                <div class="flex justify-between items-center mb-2 text-sm">
                    <span style="color:#6b7280">Services</span>
                    <span id="svcTotalDisplay" class="font-bold" style="color:#00bcd4">0 KHR</span>
                </div>
                <div class="flex justify-between items-center mb-3 text-sm">
                    <span style="color:#6b7280">Medications</span>
                    <span id="medTotalDisplay" class="font-bold" style="color:#9b59b6">0 KHR</span>
                </div>
                <div class="flex justify-between items-center mb-3 text-sm">
                    <span style="color:#6b7280">Discount</span>
                    <input type="number" name="discount_total" id="discountInput"
                           value="{{ old('discount_total', 0) }}" min="0" oninput="recalc()"
                           class="w-24 text-xs text-right rounded border border-[#e2e8f0] bg-white px-2 py-1.5 focus:outline-none focus:border-[#4154f1] transition-colors" />
                </div>
                <div class="flex justify-between items-center pt-3" style="border-top:1px dashed #c0d8e0">
                    <span class="text-base font-bold" style="color:#1a1f36">Total</span>
                    <span id="grandTotalDisplay" class="text-lg font-black" style="color:#1a1f36">0 KHR</span>
                </div>
            </div>

            <div class="space-y-2">
                <x-ui.button type="submit" variant="primary" :fullWidth="true">
                    <x-slot:icon><i class="bi bi-floppy-fill" aria-hidden="true"></i></x-slot:icon>
                    Create Invoice
                </x-ui.button>
                <x-ui.button href="{{ route('invoices.index') }}" variant="secondary" :fullWidth="true">
                    <x-slot:icon><i class="bi bi-x-circle" aria-hidden="true"></i></x-slot:icon>
                    Cancel
                </x-ui.button>
            </div>

            <x-ui.alert type="info" class="mt-4" style="font-size:11px">
                Review total and discount before saving.
            </x-ui.alert>
        </x-ui.card>
    </div>

</div>{{-- /grid --}}
</form>

@push('scripts')
<script>
const svcs = JSON.parse(document.getElementById('svcCatalog').textContent);
const meds = JSON.parse(document.getElementById('medCatalog').textContent);

// Populate catalog selects
const svcSel = document.getElementById('svcCatalogSelect');
svcs.forEach(function(s) {
    const o = new Option(s.name + ' (' + (s.category||'—') + ') — ' + Number(s.price).toLocaleString() + ' KHR', s.id);
    o._data = s; svcSel.add(o);
});
const medSel = document.getElementById('medCatalogSelect');
meds.forEach(function(m) {
    const lbl = m.name + (m.strength?' '+m.strength:'') + ' ' + (m.form||'') + ' — ' + Number(m.price).toLocaleString() + ' KHR (stock: ' + m.stock + ')';
    const o = new Option(lbl, m.id); o._data = m; medSel.add(o);
});

var svcIdx = {{ count(old('services', [])) }};
var medIdx = {{ count(old('inv_meds', [])) }};

var fldCls = 'w-full text-xs rounded border border-[#e2e8f0] px-2 py-1.5 focus:outline-none focus:border-[#4154f1] transition-colors';

function addSvcRow(data) {
    var i = svcIdx++;
    var tr = document.createElement('tr');
    tr.className = 'svc-row';
    tr.style.borderBottom = '1px solid #f8f9fb';
    tr.innerHTML =
        '<td class="px-4 py-2">'
        + '<input type="text" name="services['+i+'][name]" class="'+fldCls+'" value="'+(data&&data.name||'')+'" placeholder="Service name" required/>'
        + '<input type="hidden" name="services['+i+'][service_code]" value="'+(data&&data.code||'')+'"/>'
        + '</td>'
        + '<td class="px-4 py-2 hidden md:table-cell">'
        + '<input type="text" name="services['+i+'][category]" class="'+fldCls+'" value="'+(data&&data.category||'')+'"/>'
        + '</td>'
        + '<td class="px-4 py-2">'
        + '<input type="number" name="services['+i+'][qty]" class="svc-qty w-16 text-xs text-center rounded border border-[#e2e8f0] px-2 py-1.5 focus:outline-none focus:border-[#4154f1]" value="1" min="0" step="0.5" oninput="recalc()"/>'
        + '</td>'
        + '<td class="px-4 py-2">'
        + '<input type="number" name="services['+i+'][price]" class="svc-price '+fldCls+' text-right" value="'+(data&&data.price||0)+'" min="0" oninput="recalc()"/>'
        + '</td>'
        + '<td class="px-4 py-2 text-right text-xs font-bold svc-sub" style="color:#1a1f36">'+Number(data&&data.price||0).toLocaleString()+'</td>'
        + '<td class="px-2 py-2"><button type="button" onclick="this.closest(\'tr\').remove();recalc()" class="w-7 h-7 flex items-center justify-center rounded-md hover:bg-[#fde8e8] transition-colors" style="color:#e74c3c;border:none;background:none"><i class="bi bi-x-lg text-xs"></i></button></td>';
    document.getElementById('svcBody').appendChild(tr);
    recalc();
}

function addMedRow(data) {
    var i = medIdx++;
    var tr = document.createElement('tr');
    tr.className = 'med-row';
    tr.style.borderBottom = '1px solid #f8f9fb';
    var stockBadge = data && data.stock !== undefined
        ? '<small style="color:'+(data.stock<=0?'#e74c3c':data.stock<=(data.stock_alert||10)?'#ff771d':'#2eca6a')+';font-size:10px"> (stock: '+data.stock+')</small>'
        : '';
    tr.innerHTML =
        '<td class="px-4 py-2">'
        + '<input type="text" name="inv_meds['+i+'][name]" class="'+fldCls+'" value="'+(data&&data.name||'')+'" placeholder="Medicine name" required/>'
        + stockBadge
        + '<input type="hidden" name="inv_meds['+i+'][medicine_id]" value="'+(data&&data.id||'')+'"/>'
        + '<input type="hidden" name="inv_meds['+i+'][medicine_code]" value="'+(data&&data.code||'')+'"/>'
        + '</td>'
        + '<td class="px-4 py-2">'
        + '<input type="number" name="inv_meds['+i+'][qty]" class="med-qty w-16 text-xs text-center rounded border border-[#e2e8f0] px-2 py-1.5 focus:outline-none focus:border-[#4154f1]" value="1" min="0" oninput="recalc()"/>'
        + '</td>'
        + '<td class="px-4 py-2">'
        + '<input type="number" name="inv_meds['+i+'][price]" class="med-price '+fldCls+' text-right" value="'+(data&&data.price||0)+'" min="0" oninput="recalc()"/>'
        + '</td>'
        + '<td class="px-4 py-2 text-right text-xs font-bold med-sub" style="color:#1a1f36">'+Number(data&&data.price||0).toLocaleString()+'</td>'
        + '<td class="px-2 py-2"><button type="button" onclick="this.closest(\'tr\').remove();recalc()" class="w-7 h-7 flex items-center justify-center rounded-md hover:bg-[#fde8e8] transition-colors" style="color:#e74c3c;border:none;background:none"><i class="bi bi-x-lg text-xs"></i></button></td>';
    document.getElementById('medBody').appendChild(tr);
    recalc();
}

function addSvcFromCatalog(sel) {
    var opt = sel.options[sel.selectedIndex];
    if (!opt._data) return;
    addSvcRow(opt._data);
    sel.selectedIndex = 0;
}
function addMedFromCatalog(sel) {
    var opt = sel.options[sel.selectedIndex];
    if (!opt._data) return;
    addMedRow(opt._data);
    sel.selectedIndex = 0;
}

function recalc() {
    var svcTotal = 0, medTotal = 0;
    document.querySelectorAll('.svc-row').forEach(function(tr) {
        var q = parseFloat(tr.querySelector('.svc-qty')?.value||0)||0;
        var p = parseFloat(tr.querySelector('.svc-price')?.value||0)||0;
        var sub = q * p;
        var cell = tr.querySelector('.svc-sub');
        if (cell) cell.textContent = sub.toLocaleString('en-US') + ' KHR';
        svcTotal += sub;
    });
    document.querySelectorAll('.med-row').forEach(function(tr) {
        var q = parseFloat(tr.querySelector('.med-qty')?.value||0)||0;
        var p = parseFloat(tr.querySelector('.med-price')?.value||0)||0;
        var sub = q * p;
        var cell = tr.querySelector('.med-sub');
        if (cell) cell.textContent = sub.toLocaleString('en-US') + ' KHR';
        medTotal += sub;
    });
    var disc = parseFloat(document.getElementById('discountInput').value||0)||0;
    var grand = svcTotal + medTotal - disc;
    document.getElementById('svcTotalDisplay').textContent = svcTotal.toLocaleString('en-US') + ' KHR';
    document.getElementById('medTotalDisplay').textContent = medTotal.toLocaleString('en-US') + ' KHR';
    document.getElementById('grandTotalDisplay').textContent = grand.toLocaleString('en-US') + ' KHR';
}

// Patient AJAX search
var patientSearchInput = document.getElementById('patientSearch');
var patientDropdown    = document.getElementById('patientDropdown');
var patientCodeDisplay = document.getElementById('patientCodeDisplay');
var patientCodeInput   = document.getElementById('patientCodeInput');
var searchTimer;

patientSearchInput.addEventListener('input', function() {
    clearTimeout(searchTimer);
    var q = this.value.trim();
    if (q.length < 2) { patientDropdown.style.display = 'none'; return; }
    searchTimer = setTimeout(function() {
        fetch('{{ route('patients.search') }}?q=' + encodeURIComponent(q))
            .then(function(r) { return r.json(); })
            .then(function(data) {
                patientDropdown.innerHTML = '';
                if (!data.length) { patientDropdown.style.display = 'none'; return; }
                data.forEach(function(p) {
                    var div = document.createElement('div');
                    div.style.cssText = 'padding:8px 12px;cursor:pointer;border-bottom:1px solid #f0f0f0;font-size:12px';
                    div.innerHTML = '<strong>' + p.surname + ', ' + p.name + '</strong> <span style="color:#aaa">' + p.code + '</span>';
                    div.addEventListener('mouseenter', function() { div.style.background = '#f6f8fa'; });
                    div.addEventListener('mouseleave', function() { div.style.background = ''; });
                    div.addEventListener('click', function() {
                        patientSearchInput.value  = p.surname + ', ' + p.name;
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
document.addEventListener('click', function(e) {
    if (!e.target.closest('#patientSearch') && !e.target.closest('#patientDropdown'))
        patientDropdown.style.display = 'none';
});

recalc();
</script>
@endpush

@endsection
