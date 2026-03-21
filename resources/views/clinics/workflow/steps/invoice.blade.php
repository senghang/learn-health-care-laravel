<div class="card-hd" style="flex-wrap:wrap;gap:8px;padding:14px 18px 10px">
    <div style="flex:1;min-width:0">
        <div class="card-hd-title">
            <i class="bi bi-receipt-cutoff" style="color:#00bcd4"></i>វិក្កយបត្រ
            <small style="font-size:11px;color:#bbb;font-weight:400">/ Invoice</small>
        </div>
        <div style="font-size:10.5px;color:#aaa;margin-top:3px">ជំហាន {{ $stepIdx+1 }} នៃ {{ count($steps) }} / Step {{ $stepIdx+1 }} of {{ count($steps) }}</div>
    </div>
</div>
<div style="height:3px;background:#f0f2ff"><div style="height:100%;width:{{ round($stepIdx/count($steps)*100) }}%;background:linear-gradient(90deg,#4154f1,#717ff5)"></div></div>

<div class="card-bd">
    <form id="stepForm" method="POST" action="{{ route('workflow.step.save', [$visit->code, 'invoice']) }}">
        @csrf @method('PATCH')

        @php $invoice = $visit->invoices->first() ?? null; @endphp

        <div class="row g-3 mb-4">
            <div class="col-6 col-sm-3">
                <div class="fld">
                    <label class="flbl"><span class="km">Invoice Code</span></label>
                    <input class="form-control ro"
                           value="{{ $invoice?->code ?? 'INV'.strtoupper($visit->code) }}" readonly/>
                </div>
            </div>
            <div class="col-6 col-sm-3">
                <div class="fld">
                    <label class="flbl"><span class="km">ប្រភេទ</span><span class="en">/ Payment Type</span><span class="req">*</span></label>
                    <select name="payment_type" class="form-select">
                        @foreach(['HEF' => 'HEF — មូលនិធិ', 'NSSF' => 'NSSF — ប.ស.ស', 'CASH' => 'CASH'] as $val => $label)
                        <option value="{{ $val }}" {{ ($invoice?->payment_type ?? 'HEF') === $val ? 'selected':'' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-6 col-sm-3">
                <div class="fld">
                    <label class="flbl"><span class="km">ថ្ងៃ</span><span class="en">/ Date</span></label>
                    <input type="datetime-local" name="invoice_date" class="form-control"
                           value="{{ old('invoice_date', $invoice?->invoice_date?->format('Y-m-d\TH:i') ?? now()->format('Y-m-d\TH:i')) }}"/>
                </div>
            </div>
            <div class="col-6 col-sm-3">
                <div class="fld">
                    <label class="flbl"><span class="km">អ្នកគិតប្រាក់</span><span class="en">/ Cashier</span></label>
                    <input name="cashier" class="form-control"
                           value="{{ old('cashier', $invoice?->cashier ?? '') }}"/>
                </div>
            </div>
        </div>

        {{-- Services --}}
        <div class="d-flex justify-content-between align-items-center mb-2">
            <div style="font-weight:700;color:#012970;font-size:13px">សេវា / Services</div>
            <button type="button" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-plus"></i> Add Service
            </button>
        </div>
        <div class="table-responsive mb-4">
            <table class="tbl">
                <thead>
                    <tr>
                        <th>លេខ</th><th>សេវា / Service</th><th>ប្រភេទ / Category</th>
                        <th>ថ្លៃ KHR</th><th>ស្ថានភាព</th><th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoice?->services ?? [] as $i => $svc)
                    <tr>
                        <td><code>{{ $svc->service_code }}</code></td>
                        <td><input name="services[{{ $i }}][name]" class="form-control" value="{{ $svc->service_name }}" style="min-width:130px"/></td>
                        <td><input name="services[{{ $i }}][category]" class="form-control" value="{{ $svc->service_category }}" style="min-width:100px"/></td>
                        <td><input name="services[{{ $i }}][price]" type="number" class="form-control" value="{{ $svc->price }}" style="width:100px" data-price/></td>
                        <td>
                            <select name="services[{{ $i }}][paid]" class="form-select" style="width:100px">
                                <option value="1" {{ $svc->paid ? 'selected':'' }}>Paid</option>
                                <option value="0" {{ !$svc->paid ? 'selected':'' }}>Pending</option>
                            </select>
                        </td>
                        <td><button type="button" class="btn btn-sm btn-outline-danger">✕</button></td>
                    </tr>
                    @empty
                    <tr>
                        <td><code>SRV001</code></td>
                        <td><input name="services[0][name]" class="form-control" value="OPD Consultation" style="min-width:130px"/></td>
                        <td><input name="services[0][category]" class="form-control" value="Consultation" style="min-width:100px"/></td>
                        <td><input name="services[0][price]" type="number" class="form-control" value="30000" style="width:100px" data-price oninput="recalcTotal()"/></td>
                        <td><select name="services[0][paid]" class="form-select" style="width:100px"><option value="1">Paid</option><option value="0">Pending</option></select></td>
                        <td><button type="button" class="btn btn-sm btn-outline-danger">✕</button></td>
                    </tr>
                    <tr>
                        <td><code>SRV002</code></td>
                        <td><input name="services[1][name]" class="form-control" value="Malaria Test" style="min-width:130px"/></td>
                        <td><input name="services[1][category]" class="form-control" value="Laboratory" style="min-width:100px"/></td>
                        <td><input name="services[1][price]" type="number" class="form-control" value="30000" style="width:100px" data-price oninput="recalcTotal()"/></td>
                        <td><select name="services[1][paid]" class="form-select" style="width:100px"><option value="0">Pending</option><option value="1">Paid</option></select></td>
                        <td><button type="button" class="btn btn-sm btn-outline-danger">✕</button></td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Total Verification --}}
        <div style="background:#f6f9ff;border-radius:12px;padding:16px;border:1px solid #e0e6f5">
            <div class="row align-items-center g-3">
                <div class="col-12 col-sm-7">
                    <div class="fld">
                        <label class="flbl"><span class="km">ប្រាក់សរុបប្រកាស (KHR)</span><span class="en">/ Declared Total</span></label>
                        <input name="total" type="number" class="form-control" id="totalInput"
                               value="{{ old('total', $invoice?->total ?? 60000) }}" oninput="checkTotal()"/>
                    </div>
                </div>
                <div class="col-12 col-sm-5 text-sm-end">
                    <div style="font-size:11px;color:#bbb">ប្រាក់គណនា / Computed</div>
                    <div style="font-size:28px;font-weight:800;color:#012970;font-family:'Nunito',sans-serif">
                        <span id="computedTotal">60,000</span>
                        <span style="font-size:13px;color:#aaa">KHR</span>
                    </div>
                    <div id="totalStatus" class="note note-success d-inline-flex mt-1">
                        <i class="bi bi-check-circle-fill"></i>&nbsp;ត្រូវគ្នា / Verified ✓
                    </div>
                </div>
            </div>
        </div>

        {{-- Complete visit button --}}
        <div class="d-flex flex-wrap gap-2 justify-content-end mt-3 pt-3" style="border-top:1px solid #f0f2ff">
            <button type="button" class="btn btn-outline-primary" onclick="window.print()">
                <i class="bi bi-printer-fill"></i> Print
            </button>
            <button type="submit" name="_complete" value="1" class="btn btn-success">
                <i class="bi bi-check2-all"></i> បញ្ចប់ / Complete Visit
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
function recalcTotal() {
    const prices = [...document.querySelectorAll('[data-price]')];
    const sum = prices.reduce((acc, el) => acc + (parseFloat(el.value) || 0), 0);
    document.getElementById('computedTotal').textContent = sum.toLocaleString();
    checkTotal();
}
function checkTotal() {
    const declared = parseFloat(document.getElementById('totalInput').value) || 0;
    const computed  = [...document.querySelectorAll('[data-price]')]
        .reduce((a, el) => a + (parseFloat(el.value)||0), 0);
    const match  = declared === computed;
    const status = document.getElementById('totalStatus');
    status.className = 'note d-inline-flex mt-1 ' + (match ? 'note-success' : 'note-danger');
    status.innerHTML = match
        ? '<i class="bi bi-check-circle-fill"></i>&nbsp;ត្រូវគ្នា / Verified ✓'
        : '<i class="bi bi-exclamation-triangle-fill"></i>&nbsp;មិនត្រូវគ្នា / Mismatch ⚠';
}
document.addEventListener('DOMContentLoaded', recalcTotal);
</script>
@endpush
