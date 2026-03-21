@php
    $invoice  = $invoice  ?? null;
    $services = $services ?? collect([]);
    $meds     = $meds     ?? collect([]);

    $invCode     = $invoice?->code ?? ('INV-' . $visit->code);
    $paymentType = old('payment_type', $invoice?->payment_type ?? 'HEF');
    $invoiceDate = old('invoice_date', $invoice?->invoice_date?->format('Y-m-d') ?? now()->format('Y-m-d'));
    $cashier     = old('cashier',      $invoice?->cashier ?? auth()->user()?->name ?? '');
    $totalVal    = old('total',        $invoice?->total ?? 0);
    $svcCount    = $services->count();
@endphp

<x-step.card step-id="invoice" :visit="$visit" :step-idx="$stepIdx" :steps="$steps"
    icon="bi-receipt-cutoff" icon-color="#00bcd4"
    km="វិក្កយបត្រ" en="Invoice"
    :badge="$invoice ? 'Invoice Saved' : null"
    badge-color="#00838f">

    {{-- Patient summary banner --}}
    <div style="background:linear-gradient(135deg,#012970,#1a3a7c);border-radius:12px;padding:14px 18px;margin-bottom:20px;color:#fff">
        <div style="display:flex;align-items:center;gap:14px;flex-wrap:wrap">
            <div style="font-size:28px">🧾</div>
            <div style="flex:1">
                <div style="font-size:14px;font-weight:800">{{ $visit->surname }}, {{ $visit->name }}</div>
                <div style="font-size:11px;color:#8aabdc;margin-top:2px">
                    {{ $visit->patient_code }} ·
                    <span style="font-size:10px;padding:1px 6px;border-radius:10px;font-weight:700;{{ $visit->visit_type === 'IPD' ? 'background:#fff3e8;color:#ff771d' : 'background:#e8f8ef;color:#2eca6a' }}">
                        {{ $visit->visit_type }}
                    </span>
                    · {{ $visit->admitted_at?->format('d/m/Y') }}
                </div>
            </div>
            <div style="text-align:right">
                <div style="font-size:11px;color:#8aabdc">Invoice Code</div>
                <div style="font-size:16px;font-weight:800;font-family:monospace">{{ $invCode }}</div>
            </div>
        </div>
    </div>

    {{-- Invoice meta --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-sm-3">
            <x-form.select name="payment_type" km="ប្រភេទ" en="Payment Type" :required="true"
                :options="['HEF' => 'HEF — មូលនិធិ', 'NSSF' => 'NSSF — ប.ស.ស', 'CASH' => 'CASH']"
                :value="$paymentType"/>
        </div>
        <div class="col-6 col-sm-3">
            <x-form.field name="invoice_date" km="ថ្ងៃ" en="Invoice Date"
                          type="date" :value="$invoiceDate"/>
        </div>
        <div class="col-12 col-sm-6">
            <x-form.field name="cashier" km="អ្នកគិតប្រាក់" en="Cashier"
                          placeholder="Name" :value="$cashier"/>
        </div>
    </div>

    {{-- Services table --}}
    <div class="d-flex justify-content-between align-items-center mb-2">
        <div style="font-weight:700;color:#012970;font-size:13px">
            <i class="bi bi-list-check" style="color:#00bcd4"></i> សេវា / Services
        </div>
        <button type="button" class="btn btn-sm btn-outline-primary" onclick="addServiceRow()">
            <i class="bi bi-plus"></i> Add Service
        </button>
    </div>
    <div class="table-responsive mb-4">
        <table class="tbl" id="servicesTable">
            <thead>
                <tr>
                    <th style="width:30%">សេវា / Service</th>
                    <th style="width:22%">ប្រភេទ / Category</th>
                    <th style="width:18%">ថ្លៃ KHR</th>
                    <th style="width:15%">ស្ថានភាព</th>
                    <th style="width:5%"></th>
                </tr>
            </thead>
            <tbody id="servicesBody">
                @if($svcCount > 0)
                    @foreach($services as $i => $svc)
                    @php $paidStatus = $svc->paid > 0 ? '1' : '0'; @endphp
                    <tr>
                        <td><input name="services[{{ $i }}][name]" class="form-control" value="{{ $svc->service_name }}"/></td>
                        <td><input name="services[{{ $i }}][category]" class="form-control" value="{{ $svc->service_category }}"/></td>
                        <td><input name="services[{{ $i }}][price]" type="number" class="form-control" value="{{ $svc->price }}" data-price oninput="recalcTotal()"/></td>
                        <td>
                            <select name="services[{{ $i }}][paid_status]" class="form-select">
                                <option value="1" {{ $paidStatus === '1' ? 'selected' : '' }}>Paid ✓</option>
                                <option value="0" {{ $paidStatus === '0' ? 'selected' : '' }}>Pending</option>
                            </select>
                        </td>
                        <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('tr').remove();recalcTotal()">✕</button></td>
                    </tr>
                    @endforeach
                @else
                    <tr>
                        <td><input name="services[0][name]" class="form-control" value="OPD Consultation"/></td>
                        <td><input name="services[0][category]" class="form-control" value="Consultation"/></td>
                        <td><input name="services[0][price]" type="number" class="form-control" value="30000" data-price oninput="recalcTotal()"/></td>
                        <td>
                            <select name="services[0][paid_status]" class="form-select">
                                <option value="1">Paid ✓</option>
                                <option value="0">Pending</option>
                            </select>
                        </td>
                        <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('tr').remove();recalcTotal()">✕</button></td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>

    {{-- Total verification --}}
    <div style="background:#f6f9ff;border-radius:12px;padding:16px 20px;border:1px solid #e0e6f5;margin-bottom:20px">
        <div class="row align-items-center g-3">
            <div class="col-12 col-sm-7">
                <x-form.field name="total" km="ប្រាក់សរុបប្រកាស (KHR)" en="Declared Total"
                              type="number" :value="$totalVal"
                              id="totalInput" oninput="checkTotal()"/>
            </div>
            <div class="col-12 col-sm-5 text-sm-end">
                <div style="font-size:11px;color:#bbb;margin-bottom:2px">ប្រាក់គណនា / Computed</div>
                <div style="font-size:30px;font-weight:800;color:#012970;font-family:'Nunito',sans-serif;line-height:1">
                    <span id="computedTotal">30,000</span>
                    <span style="font-size:14px;color:#aaa;font-weight:400">KHR</span>
                </div>
                <div id="totalStatus" class="note note-info d-inline-flex mt-2" style="font-size:11px">
                    <i class="bi bi-info-circle-fill"></i>&nbsp;Enter declared total above
                </div>
            </div>
        </div>
    </div>

    {{-- Action buttons --}}
    <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center pt-2"
         style="border-top:1px solid #f0f2ff">
        <button type="button" class="btn btn-outline-primary" onclick="window.print()">
            <i class="bi bi-printer-fill"></i> Print Invoice
        </button>
        <div class="d-flex gap-2 flex-wrap">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check2-circle"></i> រក្សាទុក / Save Invoice
            </button>
            <button type="submit" name="_complete" value="1" class="btn btn-success">
                <i class="bi bi-check2-all"></i> បញ្ចប់ / Complete Visit
            </button>
        </div>
    </div>

</x-step.card>

<script>
var svcIdx = {{ max($svcCount, 1) }};

function addServiceRow() {
    var tbody = document.getElementById('servicesBody');
    var tr    = document.createElement('tr');
    tr.innerHTML =
        '<td><input name="services[' + svcIdx + '][name]" class="form-control" placeholder="Service name"/></td>'
        + '<td><input name="services[' + svcIdx + '][category]" class="form-control" placeholder="Category"/></td>'
        + '<td><input name="services[' + svcIdx + '][price]" type="number" class="form-control" value="0" data-price oninput="recalcTotal()"/></td>'
        + '<td><select name="services[' + svcIdx + '][paid_status]" class="form-select">'
        + '<option value="1">Paid ✓</option><option value="0">Pending</option></select></td>'
        + '<td><button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest(\'tr\').remove();recalcTotal()">✕</button></td>';
    tbody.appendChild(tr);
    svcIdx++;
    recalcTotal();
}

function getComputedTotal() {
    return Array.from(document.querySelectorAll('[data-price]'))
        .reduce(function(sum, el) { return sum + (parseFloat(el.value) || 0); }, 0);
}

function recalcTotal() {
    var sum = getComputedTotal();
    document.getElementById('computedTotal').textContent = sum.toLocaleString();
    checkTotal();
}

function checkTotal() {
    var declared = parseFloat(document.getElementById('totalInput').value) || 0;
    var computed  = getComputedTotal();
    var status    = document.getElementById('totalStatus');
    if (!declared) {
        status.className = 'note note-info d-inline-flex mt-2';
        status.innerHTML = '<i class="bi bi-info-circle-fill"></i>&nbsp;Enter declared total above';
    } else if (declared === computed) {
        status.className = 'note note-success d-inline-flex mt-2';
        status.innerHTML = '<i class="bi bi-check-circle-fill"></i>&nbsp;ត្រូវគ្នា / Verified ✓';
    } else {
        status.className = 'note note-danger d-inline-flex mt-2';
        status.innerHTML = '<i class="bi bi-exclamation-triangle-fill"></i>&nbsp;មិនត្រូវគ្នា / Mismatch ⚠';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    var totalInput = document.getElementById('totalInput');
    var computed   = getComputedTotal();
    if (!parseFloat(totalInput.value) && computed > 0) totalInput.value = computed;
    recalcTotal();
});
</script>
