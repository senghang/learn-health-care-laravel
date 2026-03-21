@php
    $progressPct    = round($stepIdx / count($steps) * 100);
    $progressWidth  = $progressPct . '%';
    $saveUrl        = url('/workflow/' . $visit->code . '/invoice/save');
    $invoice        = $invoices->first() ?? null;
    $invCode        = $invoice?->code ?? ('INV-' . strtoupper($visit->code));
    $paymentType    = old('payment_type', $invoice?->payment_type ?? 'HEF');
    $invoiceDate    = old('invoice_date', $invoice?->invoice_date?->format('Y-m-d\TH:i') ?? now()->format('Y-m-d\TH:i'));
    $cashier        = old('cashier',      $invoice?->cashier ?? auth()->user()?->name ?? '');
    $totalVal       = old('total',        $invoice?->total ?? 0);
    $invCount       = $invoices->count();
    $services       = $invoice?->services ?? collect([]);
    $svcCount       = $services->count();
    $selHEF         = $paymentType === 'HEF'  ? 'selected' : '';
    $selNSSF        = $paymentType === 'NSSF' ? 'selected' : '';
    $selCASH        = $paymentType === 'CASH' ? 'selected' : '';
@endphp

<div class="card-hd" style="flex-wrap:wrap;gap:8px;padding:14px 18px 10px">
    <div style="flex:1;min-width:0">
        <div class="card-hd-title">
            <i class="bi bi-receipt-cutoff" style="color:#00bcd4"></i>វិក្កយបត្រ
            <small style="font-size:11px;color:#bbb;font-weight:400">/ Invoice</small>
        </div>
        <div style="font-size:10.5px;color:#aaa;margin-top:3px">
            ជំហាន {{ $stepIdx+1 }} នៃ {{ count($steps) }} / Step {{ $stepIdx+1 }} of {{ count($steps) }}
        </div>
    </div>
    @if($invCount > 0)
    <span style="font-size:11px;background:#e0f7fa;color:#00838f;padding:3px 10px;border-radius:20px;border:1px solid #80deea;font-weight:700;flex-shrink:0">
        <i class="bi bi-check-circle-fill"></i> Invoice Saved
    </span>
    @endif
</div>
<div style="height:3px;background:#f0f2ff">
    <div style="height:100%;width:{{ $progressWidth }};background:linear-gradient(90deg,#4154f1,#717ff5)"></div>
</div>

<div class="card-bd">

    {{-- Patient summary banner --}}
    <div style="background:linear-gradient(135deg,#012970,#1a3a7c);border-radius:12px;padding:14px 18px;margin-bottom:20px;color:#fff">
        <div style="display:flex;align-items:center;gap:14px;flex-wrap:wrap">
            <div style="font-size:28px">🧾</div>
            <div style="flex:1">
                <div style="font-size:14px;font-weight:800">{{ $visit->surname }}, {{ $visit->name }}</div>
                <div style="font-size:11px;color:#8aabdc;margin-top:2px">
                    {{ $visit->patient_code }} ·
                    <span style="font-size:10px;padding:1px 6px;border-radius:10px;font-weight:700;
                        {{ $visit->visit_type === 'IPD' ? 'background:#fff3e8;color:#ff771d' : 'background:#e8f8ef;color:#2eca6a' }}">
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

    <form id="stepForm" method="POST" action="{{ $saveUrl }}">
        @csrf
        @method('PATCH')

        {{-- Invoice meta --}}
        <div class="row g-3 mb-4">
            <div class="col-6 col-sm-3">
                <div class="fld">
                    <label class="flbl"><span class="km">ប្រភេទ</span><span class="en">/ Payment Type</span><span class="req">*</span></label>
                    <select name="payment_type" class="form-select" required data-error-msg="Payment Type">
                        <option value="HEF"  {{ $selHEF  }}>HEF — មូលនិធិ</option>
                        <option value="NSSF" {{ $selNSSF }}>NSSF — ប.ស.ស</option>
                        <option value="CASH" {{ $selCASH }}>CASH</option>
                    </select>
                </div>
            </div>
            <div class="col-6 col-sm-3">
                <div class="fld">
                    <label class="flbl"><span class="km">ថ្ងៃ</span><span class="en">/ Invoice Date</span></label>
                    <input type="datetime-local" name="invoice_date" class="form-control" value="{{ $invoiceDate }}"/>
                </div>
            </div>
            <div class="col-12 col-sm-6">
                <div class="fld">
                    <label class="flbl"><span class="km">អ្នកគិតប្រាក់</span><span class="en">/ Cashier</span></label>
                    <input name="cashier" class="form-control" value="{{ $cashier }}" placeholder="Name"/>
                </div>
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
                        @php
                            $paidSel   = $svc->paid ? 'selected' : '';
                            $unpaidSel = !$svc->paid ? 'selected' : '';
                        @endphp
                        <tr>
                            <td><input name="services[{{ $i }}][name]" class="form-control" value="{{ $svc->service_name }}"/></td>
                            <td><input name="services[{{ $i }}][category]" class="form-control" value="{{ $svc->service_category }}"/></td>
                            <td><input name="services[{{ $i }}][price]" type="number" class="form-control" value="{{ $svc->price }}" data-price oninput="recalcTotal()"/></td>
                            <td>
                                <select name="services[{{ $i }}][paid]" class="form-select">
                                    <option value="1" {{ $paidSel }}>Paid ✓</option>
                                    <option value="0" {{ $unpaidSel }}>Pending</option>
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
                                <select name="services[0][paid]" class="form-select">
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
                    <div class="fld" style="margin-bottom:0">
                        <label class="flbl">
                            <span class="km">ប្រាក់សរុបប្រកាស (KHR)</span>
                            <span class="en">/ Declared Total</span>
                        </label>
                        <input name="total" type="number" class="form-control" id="totalInput"
                               value="{{ $totalVal }}" oninput="checkTotal()"
                               style="font-size:16px;font-weight:700"/>
                    </div>
                </div>
                <div class="col-12 col-sm-5 text-sm-end">
                    <div style="font-size:11px;color:#bbb;margin-bottom:2px">ប្រាក់គណនា / Computed</div>
                    <div style="font-size:30px;font-weight:800;color:#012970;font-family:'Nunito',sans-serif;line-height:1">
                        <span id="computedTotal">30,000</span>
                        <span style="font-size:14px;color:#aaa;font-weight:400">KHR</span>
                    </div>
                    <div id="totalStatus" class="note note-info d-inline-flex mt-2" style="font-size:11px">
                        <i class="bi bi-info-circle-fill"></i>&nbsp;កំណត់ / Set declared total
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

    </form>
</div>

<script>
var svcIdx = {{ max($svcCount, 1) }};

function addServiceRow() {
    var tbody = document.getElementById('servicesBody');
    var tr    = document.createElement('tr');
    tr.innerHTML =
        '<td><input name="services[' + svcIdx + '][name]" class="form-control" placeholder="Service name"/></td>'
        + '<td><input name="services[' + svcIdx + '][category]" class="form-control" placeholder="Category"/></td>'
        + '<td><input name="services[' + svcIdx + '][price]" type="number" class="form-control" value="0"'
        + ' data-price oninput="recalcTotal()"/></td>'
        + '<td><select name="services[' + svcIdx + '][paid]" class="form-select">'
        + '<option value="1">Paid ✓</option><option value="0">Pending</option></select></td>'
        + '<td><button type="button" class="btn btn-sm btn-outline-danger"'
        + ' onclick="this.closest(\'tr\').remove();recalcTotal()">✕</button></td>';
    tbody.appendChild(tr);
    svcIdx++;
    recalcTotal();
}

function getComputedTotal() {
    var sum = 0;
    document.querySelectorAll('[data-price]').forEach(function(el) {
        sum += parseFloat(el.value) || 0;
    });
    return sum;
}

function recalcTotal() {
    var sum = getComputedTotal();
    document.getElementById('computedTotal').textContent = sum.toLocaleString();
    checkTotal();
}

function checkTotal() {
    var declared = parseFloat(document.getElementById('totalInput').value) || 0;
    var computed = getComputedTotal();
    var match    = declared > 0 && declared === computed;
    var noInput  = !declared;
    var status   = document.getElementById('totalStatus');

    if (noInput) {
        status.className = 'note note-info d-inline-flex mt-2';
        status.innerHTML = '<i class="bi bi-info-circle-fill"></i>&nbsp;Enter declared total above';
    } else if (match) {
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
    // Auto-fill declared total if not yet set
    if (!parseFloat(totalInput.value) && computed > 0) {
        totalInput.value = computed;
    }
    recalcTotal();
});
</script>
