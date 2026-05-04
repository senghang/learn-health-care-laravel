@extends('clinics.layout.app')
@section('title', 'Edit ' . $invoice->code)

@section('content')

<x-ui.page-header
    km="កែសម្រួលវិក្កយបត្រ"
    :title="'Edit ' . $invoice->code"
    :breadcrumbs="[
        ['label' => __('app.home'), 'url' => route('dashboard')],
        ['label' => __('app.invoices'), 'url' => route('invoices.index')],
        ['label' => $invoice->code, 'url' => route('invoices.show', $invoice->code)],
        ['label' => 'Edit'],
    ]">
    <x-slot:actions>
        <x-ui.button href="{{ route('invoices.show', $invoice->code) }}" variant="secondary">
            <x-slot:icon><i class="bi bi-arrow-left" aria-hidden="true"></i></x-slot:icon>
            Back
        </x-ui.button>
        <form method="POST" action="{{ route('invoices.void', $invoice->code) }}"
              data-confirm="Void invoice #{{ $invoice->code }}? It will be marked as voided and cannot be reversed."
              data-confirm-type="warn" data-confirm-title="Void Invoice">
            @csrf
            <x-ui.button type="submit" variant="danger">
                <x-slot:icon><i class="bi bi-slash-circle" aria-hidden="true"></i></x-slot:icon>
                Void Invoice
            </x-ui.button>
        </form>
    </x-slot:actions>
</x-ui.page-header>

@if(session('error'))
    <x-ui.alert type="error" class="mb-4">
        <i class="bi bi-exclamation-triangle-fill"></i> {{ session('error') }}
    </x-ui.alert>
@endif
@if($errors->any())
    <x-ui.alert type="error" class="mb-4">
        <ul class="list-disc pl-4 text-xs space-y-0.5">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </x-ui.alert>
@endif

<form method="POST" action="{{ route('invoices.update', $invoice->code) }}" novalidate id="invForm">
@csrf @method('PATCH')

{{-- Catalogs (hidden, for JS) --}}
<script id="svcCatalog" type="application/json">{!! json_encode($svcCatalog) !!}</script>
<script id="medCatalog" type="application/json">{!! json_encode($medCatalog) !!}</script>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

{{-- ── Main column ─────────────────────────────────────────────────────── --}}
<div class="lg:col-span-2 space-y-4">

    {{-- Patient (read-only on edit) --}}
    <x-ui.card>
        <x-slot:header>
            <x-ui.card-header label="Patient" icon="bi-person-fill">
                <x-slot:actions>
                    <span class="text-xs" style="color:#9ca3af">Cannot change patient on edit</span>
                </x-slot:actions>
            </x-ui.card-header>
        </x-slot:header>
        @if($invoice->patient)
            <div class="flex items-center gap-3 p-2 rounded-xl" style="background:#f9fafb;border:1px solid #e6eaf5">
                <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0 text-white text-sm font-bold"
                     style="background:#4154f1">
                    {{ strtoupper(substr($invoice->patient->name, 0, 1)) }}
                </div>
                <div>
                    <div class="text-sm font-bold" style="color:#1a1f36">
                        {{ $invoice->patient->surname }}, {{ $invoice->patient->name }}
                    </div>
                    <div class="text-xs" style="color:#9ca3af">
                        {{ $invoice->patient->code }} · {{ $invoice->patient->phone ?? '—' }}
                    </div>
                </div>
            </div>
        @endif
    </x-ui.card>

    {{-- Invoice Details --}}
    <x-ui.card>
        <x-slot:header>
            <x-ui.card-header label="Invoice Details" icon="bi-receipt-cutoff" />
        </x-slot:header>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div class="space-y-1.5">
                <label class="block text-xs font-semibold" style="color:#374151">Date</label>
                <x-forms.input type="date" name="invoice_date"
                               :value="old('invoice_date', $invoice->invoice_date?->toDateString())" />
            </div>
            <div class="space-y-1.5">
                <label class="block text-xs font-semibold" style="color:#374151">Payment Type</label>
                <x-forms.select name="payment_type">
                    @foreach(['CASH'=>'CASH','HEF'=>'HEF','NSSF'=>'NSSF','CARD'=>'Card'] as $v=>$l)
                        <option value="{{ $v }}" {{ old('payment_type',$invoice->payment_type)===$v?'selected':'' }}>{{ $l }}</option>
                    @endforeach
                </x-forms.select>
            </div>
            <div class="space-y-1.5">
                <label class="block text-xs font-semibold" style="color:#374151">Cashier</label>
                <x-forms.input type="text" name="cashier"
                               :value="old('cashier', $invoice->cashier)"
                               placeholder="Cashier name" />
            </div>
            <div class="space-y-1.5">
                <label class="block text-xs font-semibold" style="color:#374151">Status</label>
                <div class="w-full text-xs font-bold rounded-lg px-3 py-2.5 border" style="color:#374151;background:#f9fafb;border-color:#e6eaf5">
                    {{ strtoupper($invoice->status) }}
                </div>
            </div>
            <div class="col-span-2 sm:col-span-4 space-y-1.5">
                <label class="block text-xs font-semibold" style="color:#374151">Notes</label>
                <textarea name="notes" rows="2"
                    class="w-full text-sm rounded-lg border px-3 py-2 focus:outline-none transition-colors resize-none"
                    style="border-color:#e2e8f0;color:#374151"
                    placeholder="Optional notes…">{{ old('notes', $invoice->notes) }}</textarea>
            </div>
        </div>
    </x-ui.card>

    {{-- Services --}}
    <x-ui.card noPadding>
        <x-slot:header>
            <x-ui.card-header label="Services" icon="bi-grid-fill">
                <x-slot:actions>
                    <x-ui.button type="button" variant="secondary" size="sm" onclick="addSvcRow()">
                        <x-slot:icon><i class="bi bi-plus-circle" aria-hidden="true"></i></x-slot:icon>
                        Add Service
                    </x-ui.button>
                </x-slot:actions>
            </x-ui.card-header>
        </x-slot:header>

        {{-- Quick add from catalogue --}}
        <div class="px-4 py-3 border-b" style="border-color:#e6eaf5;background:#fafbff">
            <x-forms.select id="svcCatalogSelect" onchange="addSvcFromCatalog(this)">
                <option value="">Quick add from catalogue…</option>
            </x-forms.select>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm" id="svcTable">
                <thead>
                    <tr style="background:#f9fafb;border-bottom:1px solid #e6eaf5">
                        <th class="text-left text-xs font-semibold px-4 py-2.5" style="color:#6b7280;min-width:200px">Service Name</th>
                        <th class="text-left text-xs font-semibold px-3 py-2.5 hidden md:table-cell" style="color:#6b7280;min-width:110px">Category</th>
                        <th class="text-left text-xs font-semibold px-3 py-2.5" style="color:#6b7280;width:70px">Qty</th>
                        <th class="text-left text-xs font-semibold px-3 py-2.5" style="color:#6b7280;width:130px">Price (KHR)</th>
                        <th class="text-right text-xs font-semibold px-4 py-2.5" style="color:#6b7280;width:130px">Subtotal</th>
                        <th style="width:40px"></th>
                    </tr>
                </thead>
                <tbody id="svcBody">
                    @php $svcOld = old('services'); @endphp
                    @if($svcOld !== null)
                        @foreach($svcOld as $i => $svc)
                        <tr class="svc-row border-b" style="border-color:#f1f5f9">
                            <td class="px-4 py-2">
                                <input type="text" name="services[{{ $i }}][name]"
                                    class="w-full text-xs rounded border border-[#e2e8f0] px-2 py-1.5 focus:outline-none focus:border-[#4154f1] transition-colors"
                                    value="{{ $svc['name']??'' }}" placeholder="Service name" required/>
                                <input type="hidden" name="services[{{ $i }}][service_code]" value="{{ $svc['service_code']??'' }}"/>
                            </td>
                            <td class="px-3 py-2 hidden md:table-cell">
                                <input type="text" name="services[{{ $i }}][category]"
                                    class="w-full text-xs rounded border border-[#e2e8f0] px-2 py-1.5 focus:outline-none focus:border-[#4154f1] transition-colors"
                                    value="{{ $svc['category']??'' }}"/>
                            </td>
                            <td class="px-3 py-2">
                                <input type="number" name="services[{{ $i }}][qty]"
                                    class="w-full text-xs rounded border border-[#e2e8f0] px-2 py-1.5 focus:outline-none focus:border-[#4154f1] transition-colors svc-qty"
                                    value="{{ $svc['qty']??1 }}" min="0" step="0.5" oninput="recalc()"/>
                            </td>
                            <td class="px-3 py-2">
                                <input type="number" name="services[{{ $i }}][price]"
                                    class="w-full text-xs rounded border border-[#e2e8f0] px-2 py-1.5 focus:outline-none focus:border-[#4154f1] transition-colors svc-price"
                                    value="{{ $svc['price']??0 }}" min="0" oninput="recalc()"/>
                            </td>
                            <td class="px-4 py-2 text-right text-xs font-bold svc-sub" style="color:#1a1f36">
                                {{ number_format(($svc['qty']??1)*($svc['price']??0)) }}
                            </td>
                            <td class="px-2 py-2">
                                <button type="button" onclick="this.closest('tr').remove();recalc()"
                                    class="w-7 h-7 flex items-center justify-center rounded-md hover:bg-[#fde8e8] transition-colors"
                                    style="color:#e74c3c;border:none;background:none">
                                    <i class="bi bi-x text-sm" aria-hidden="true"></i>
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    @else
                        @foreach($invoice->services as $i => $svc)
                        <tr class="svc-row border-b" style="border-color:#f1f5f9">
                            <td class="px-4 py-2">
                                <input type="text" name="services[{{ $i }}][name]"
                                    class="w-full text-xs rounded border border-[#e2e8f0] px-2 py-1.5 focus:outline-none focus:border-[#4154f1] transition-colors"
                                    value="{{ $svc->service_name }}" placeholder="Service name" required/>
                                <input type="hidden" name="services[{{ $i }}][service_code]" value="{{ $svc->service_code }}"/>
                            </td>
                            <td class="px-3 py-2 hidden md:table-cell">
                                <input type="text" name="services[{{ $i }}][category]"
                                    class="w-full text-xs rounded border border-[#e2e8f0] px-2 py-1.5 focus:outline-none focus:border-[#4154f1] transition-colors"
                                    value="{{ $svc->service_category }}"/>
                            </td>
                            <td class="px-3 py-2">
                                <input type="number" name="services[{{ $i }}][qty]"
                                    class="w-full text-xs rounded border border-[#e2e8f0] px-2 py-1.5 focus:outline-none focus:border-[#4154f1] transition-colors svc-qty"
                                    value="{{ $svc->qty ?? 1 }}" min="0" step="0.5" oninput="recalc()"/>
                            </td>
                            <td class="px-3 py-2">
                                <input type="number" name="services[{{ $i }}][price]"
                                    class="w-full text-xs rounded border border-[#e2e8f0] px-2 py-1.5 focus:outline-none focus:border-[#4154f1] transition-colors svc-price"
                                    value="{{ $svc->price }}" min="0" oninput="recalc()"/>
                            </td>
                            <td class="px-4 py-2 text-right text-xs font-bold svc-sub" style="color:#1a1f36">
                                {{ number_format(($svc->qty??1)*$svc->price) }}
                            </td>
                            <td class="px-2 py-2">
                                <button type="button" onclick="this.closest('tr').remove();recalc()"
                                    class="w-7 h-7 flex items-center justify-center rounded-md hover:bg-[#fde8e8] transition-colors"
                                    style="color:#e74c3c;border:none;background:none">
                                    <i class="bi bi-x text-sm" aria-hidden="true"></i>
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>
    </x-ui.card>

    {{-- Medications --}}
    <x-ui.card noPadding>
        <x-slot:header>
            <x-ui.card-header label="Medications" icon="bi-capsule-pill">
                <x-slot:actions>
                    <x-ui.button type="button" variant="secondary" size="sm" onclick="addMedRow()">
                        <x-slot:icon><i class="bi bi-plus-circle" aria-hidden="true"></i></x-slot:icon>
                        Add Medicine
                    </x-ui.button>
                </x-slot:actions>
            </x-ui.card-header>
        </x-slot:header>

        {{-- Quick add from formulary --}}
        <div class="px-4 py-3 border-b" style="border-color:#e6eaf5;background:#fdfaff">
            <x-forms.select id="medCatalogSelect" onchange="addMedFromCatalog(this)">
                <option value="">Quick add from formulary…</option>
            </x-forms.select>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm" id="medTable">
                <thead>
                    <tr style="background:#f9fafb;border-bottom:1px solid #e6eaf5">
                        <th class="text-left text-xs font-semibold px-4 py-2.5" style="color:#6b7280;min-width:200px">Medicine</th>
                        <th class="text-left text-xs font-semibold px-3 py-2.5" style="color:#6b7280;width:80px">Qty</th>
                        <th class="text-left text-xs font-semibold px-3 py-2.5" style="color:#6b7280;width:140px">Unit Price (KHR)</th>
                        <th class="text-right text-xs font-semibold px-4 py-2.5" style="color:#6b7280;width:130px">Subtotal</th>
                        <th style="width:40px"></th>
                    </tr>
                </thead>
                <tbody id="medBody">
                    @php $medOld = old('inv_meds'); @endphp
                    @if($medOld !== null)
                        @foreach($medOld as $i => $med)
                        <tr class="med-row border-b" style="border-color:#f1f5f9">
                            <td class="px-4 py-2">
                                <input type="text" name="inv_meds[{{ $i }}][name]"
                                    class="w-full text-xs rounded border border-[#e2e8f0] px-2 py-1.5 focus:outline-none focus:border-[#4154f1] transition-colors"
                                    value="{{ $med['name']??'' }}" placeholder="Medicine name" required/>
                                <input type="hidden" name="inv_meds[{{ $i }}][medicine_id]" value="{{ $med['medicine_id']??'' }}"/>
                                <input type="hidden" name="inv_meds[{{ $i }}][medicine_code]" value="{{ $med['medicine_code']??'' }}"/>
                            </td>
                            <td class="px-3 py-2">
                                <input type="number" name="inv_meds[{{ $i }}][qty]"
                                    class="w-full text-xs rounded border border-[#e2e8f0] px-2 py-1.5 focus:outline-none focus:border-[#4154f1] transition-colors med-qty"
                                    value="{{ $med['qty']??1 }}" min="0" oninput="recalc()"/>
                            </td>
                            <td class="px-3 py-2">
                                <input type="number" name="inv_meds[{{ $i }}][price]"
                                    class="w-full text-xs rounded border border-[#e2e8f0] px-2 py-1.5 focus:outline-none focus:border-[#4154f1] transition-colors med-price"
                                    value="{{ $med['price']??0 }}" min="0" oninput="recalc()"/>
                            </td>
                            <td class="px-4 py-2 text-right text-xs font-bold med-sub" style="color:#1a1f36">
                                {{ number_format(($med['qty']??1)*($med['price']??0)) }}
                            </td>
                            <td class="px-2 py-2">
                                <button type="button" onclick="this.closest('tr').remove();recalc()"
                                    class="w-7 h-7 flex items-center justify-center rounded-md hover:bg-[#fde8e8] transition-colors"
                                    style="color:#e74c3c;border:none;background:none">
                                    <i class="bi bi-x text-sm" aria-hidden="true"></i>
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    @else
                        @foreach($invoice->medications as $i => $med)
                        <tr class="med-row border-b" style="border-color:#f1f5f9">
                            <td class="px-4 py-2">
                                <input type="text" name="inv_meds[{{ $i }}][name]"
                                    class="w-full text-xs rounded border border-[#e2e8f0] px-2 py-1.5 focus:outline-none focus:border-[#4154f1] transition-colors"
                                    value="{{ $med->medicine_name }}" placeholder="Medicine name" required/>
                                <input type="hidden" name="inv_meds[{{ $i }}][medicine_id]" value="{{ $med->medicine_id }}"/>
                                <input type="hidden" name="inv_meds[{{ $i }}][medicine_code]" value="{{ $med->medicine_code }}"/>
                            </td>
                            <td class="px-3 py-2">
                                <input type="number" name="inv_meds[{{ $i }}][qty]"
                                    class="w-full text-xs rounded border border-[#e2e8f0] px-2 py-1.5 focus:outline-none focus:border-[#4154f1] transition-colors med-qty"
                                    value="{{ $med->quantity }}" min="0" oninput="recalc()"/>
                            </td>
                            <td class="px-3 py-2">
                                <input type="number" name="inv_meds[{{ $i }}][price]"
                                    class="w-full text-xs rounded border border-[#e2e8f0] px-2 py-1.5 focus:outline-none focus:border-[#4154f1] transition-colors med-price"
                                    value="{{ $med->price }}" min="0" oninput="recalc()"/>
                            </td>
                            <td class="px-4 py-2 text-right text-xs font-bold med-sub" style="color:#1a1f36">
                                {{ number_format($med->quantity * $med->price) }}
                            </td>
                            <td class="px-2 py-2">
                                <button type="button" onclick="this.closest('tr').remove();recalc()"
                                    class="w-7 h-7 flex items-center justify-center rounded-md hover:bg-[#fde8e8] transition-colors"
                                    style="color:#e74c3c;border:none;background:none">
                                    <i class="bi bi-x text-sm" aria-hidden="true"></i>
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>
    </x-ui.card>

</div>{{-- /main col --}}

{{-- ── Sidebar ──────────────────────────────────────────────────────────── --}}
<div class="lg:col-span-1">
    <x-ui.card class="sticky top-20">
        <x-slot:header>
            <x-ui.card-header label="Summary" icon="bi-calculator-fill" />
        </x-slot:header>

        {{-- Payments already collected warning --}}
        @php $paid = $invoice->payments->sum('amount'); @endphp
        @if($paid > 0)
            <x-ui.alert type="warning" class="mb-4">
                <div class="text-xs">
                    <i class="bi bi-info-circle-fill"></i>
                    <strong>{{ number_format($paid) }} KHR</strong> already collected. Editing the total may affect balance.
                </div>
            </x-ui.alert>
        @endif

        {{-- Totals box --}}
        <div class="rounded-xl p-4 mb-4" style="background:#f0f4ff;border:1px solid #dde3f8">
            <div class="flex justify-between items-center mb-2">
                <span class="text-xs" style="color:#6b7280">Services</span>
                <span id="svcTotalDisplay" class="text-xs font-bold" style="color:#4154f1">0 KHR</span>
            </div>
            <div class="flex justify-between items-center mb-3">
                <span class="text-xs" style="color:#6b7280">Medications</span>
                <span id="medTotalDisplay" class="text-xs font-bold" style="color:#9b59b6">0 KHR</span>
            </div>
            <div class="flex justify-between items-center mb-3">
                <span class="text-xs" style="color:#6b7280">Discount (KHR)</span>
                <input type="number" name="discount_total" id="discountInput"
                       class="text-xs rounded border border-[#e2e8f0] px-2 py-1 text-right focus:outline-none focus:border-[#4154f1] transition-colors"
                       style="width:100px"
                       value="{{ old('discount_total', $invoice->discount_total) }}"
                       min="0" oninput="recalc()"/>
            </div>
            <div class="border-t pt-3" style="border-color:#c7d2f8">
                <div class="flex justify-between items-center">
                    <span class="text-sm font-bold" style="color:#1a1f36">New Total</span>
                    <span id="grandTotalDisplay" class="text-lg font-extrabold" style="color:#1a1f36">0 KHR</span>
                </div>
            </div>
        </div>

        <div class="space-y-2">
            <x-ui.button type="submit" variant="warning" :fullWidth="true">
                <x-slot:icon><i class="bi bi-floppy-fill" aria-hidden="true"></i></x-slot:icon>
                Save Changes
            </x-ui.button>
            <x-ui.button href="{{ route('invoices.show', $invoice->code) }}" variant="secondary" :fullWidth="true">
                <x-slot:icon><i class="bi bi-x-circle" aria-hidden="true"></i></x-slot:icon>
                Cancel
            </x-ui.button>

            {{-- Delete (only if pending + no payments) --}}
            @if($invoice->status === 'pending' && $paid == 0)
                <form method="POST" action="{{ route('invoices.destroy', $invoice->code) }}"
                      data-confirm="Permanently delete invoice #{{ $invoice->code }}? This cannot be undone."
                      data-confirm-type="danger" data-confirm-title="Delete Invoice">
                    @csrf @method('DELETE')
                    <x-ui.button type="submit" variant="danger" :fullWidth="true" size="sm">
                        <x-slot:icon><i class="bi bi-trash3-fill" aria-hidden="true"></i></x-slot:icon>
                        Delete Invoice
                    </x-ui.button>
                </form>
            @endif
        </div>

    </x-ui.card>
</div>

</div>{{-- /grid --}}
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

const fldCls = 'w-full text-xs rounded border border-[#e2e8f0] px-2 py-1.5 focus:outline-none focus:border-[#4154f1] transition-colors';

function addSvcRow(data) {
    const i = svcIdx++;
    const tr = document.createElement('tr');
    tr.className = 'svc-row border-b';
    tr.style.borderColor = '#f1f5f9';
    tr.innerHTML = `
        <td class="px-4 py-2">
            <input type="text" name="services[${i}][name]" class="${fldCls}"
                   value="${data?.name||''}" placeholder="Service name" required/>
            <input type="hidden" name="services[${i}][service_code]" value="${data?.code||''}"/>
        </td>
        <td class="px-3 py-2 hidden md:table-cell">
            <input type="text" name="services[${i}][category]" class="${fldCls}" value="${data?.category||''}"/>
        </td>
        <td class="px-3 py-2">
            <input type="number" name="services[${i}][qty]" class="${fldCls} svc-qty"
                   value="1" min="0" step="0.5" oninput="recalc()"/>
        </td>
        <td class="px-3 py-2">
            <input type="number" name="services[${i}][price]" class="${fldCls} svc-price"
                   value="${data?.price||0}" min="0" oninput="recalc()"/>
        </td>
        <td class="px-4 py-2 text-right text-xs font-bold svc-sub" style="color:#1a1f36">
            ${Number(data?.price||0).toLocaleString()}
        </td>
        <td class="px-2 py-2">
            <button type="button" onclick="this.closest('tr').remove();recalc()"
                class="w-7 h-7 flex items-center justify-center rounded-md hover:bg-[#fde8e8] transition-colors"
                style="color:#e74c3c;border:none;background:none">
                <i class="bi bi-x text-sm"></i>
            </button>
        </td>`;
    document.getElementById('svcBody').appendChild(tr);
    recalc();
}

function addMedRow(data) {
    const i = medIdx++;
    const tr = document.createElement('tr');
    tr.className = 'med-row border-b';
    tr.style.borderColor = '#f1f5f9';
    const stockBadge = data?.stock !== undefined
        ? `<span style="font-size:10px;color:${data.stock<=0?'#e74c3c':data.stock<=(data.stock_alert||10)?'#ff771d':'#2eca6a'}"> (stock: ${data.stock})</span>`
        : '';
    tr.innerHTML = `
        <td class="px-4 py-2">
            <input type="text" name="inv_meds[${i}][name]" class="${fldCls}"
                   value="${data?.name||''}" placeholder="Medicine name" required/>
            ${stockBadge}
            <input type="hidden" name="inv_meds[${i}][medicine_id]" value="${data?.id||''}"/>
            <input type="hidden" name="inv_meds[${i}][medicine_code]" value="${data?.code||''}"/>
        </td>
        <td class="px-3 py-2">
            <input type="number" name="inv_meds[${i}][qty]" class="${fldCls} med-qty"
                   value="1" min="0" oninput="recalc()"/>
        </td>
        <td class="px-3 py-2">
            <input type="number" name="inv_meds[${i}][price]" class="${fldCls} med-price"
                   value="${data?.price||0}" min="0" oninput="recalc()"/>
        </td>
        <td class="px-4 py-2 text-right text-xs font-bold med-sub" style="color:#1a1f36">
            ${Number(data?.price||0).toLocaleString()}
        </td>
        <td class="px-2 py-2">
            <button type="button" onclick="this.closest('tr').remove();recalc()"
                class="w-7 h-7 flex items-center justify-center rounded-md hover:bg-[#fde8e8] transition-colors"
                style="color:#e74c3c;border:none;background:none">
                <i class="bi bi-x text-sm"></i>
            </button>
        </td>`;
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
