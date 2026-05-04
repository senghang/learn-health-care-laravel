@php
    use App\Models\PrescriptionModel;

    $invoice  = $invoice  ?? null;
    $services = $services ?? collect([]);
    $meds     = $meds     ?? collect([]);
    $svcCatalog = $svcCatalog ?? collect([]);
    $medCatalog = $medCatalog ?? collect([]);

    $invCode     = $invoice?->code ?? ('INV-' . $visit->code);
    $paymentType = old('payment_type', $invoice?->payment_type ?? 'HEF');
    $invoiceDate = old('invoice_date', df_input($invoice?->invoice_date) ?: df_today_input());
    $cashier     = old('cashier',      $invoice?->cashier ?? auth()->user()?->name ?? '');
    $totalVal    = old('total',        $invoice?->total ?? 0);
    $invStatus   = old('status',       $invoice?->status ?? 'pending');
    $svcCount    = $services->count();
    $medCount    = $meds->count();

    // Prescription check for Complete Visit gate
    $latestRx   = PrescriptionModel::where('visit_code', $visit->code)->latest()->first();
    $hasRx      = $latestRx !== null;
    $rxDispensed= $latestRx?->dispensed_status === 'dispensed';
    $invoicePaid= $invStatus === 'paid';
    $canComplete= $invoicePaid && (!$hasRx || $rxDispensed);

    $statusCfg = [
        'pending'  => ['color'=>'#b45309','bg'=>'#fff8e1','bdr'=>'#fde68a','icon'=>'bi-clock',          'label'=>'Pending Payment'],
        'partial'  => ['color'=>'#0369a1','bg'=>'#e0f2fe','bdr'=>'#7dd3fc','icon'=>'bi-half',           'label'=>'Partially Paid'],
        'paid'     => ['color'=>'#1D9E75','bg'=>'#e8f8ef','bdr'=>'#b7eacf','icon'=>'bi-check-circle-fill','label'=>'Paid ✓'],
        'void'     => ['color'=>'#dc2626','bg'=>'#fde8e8','bdr'=>'#fca5a5','icon'=>'bi-x-circle-fill',  'label'=>'Void'],
    ];
    $sc = $statusCfg[$invStatus] ?? $statusCfg['pending'];

    $payLabels = ['HEF'=>'HEF — មូលនិធិ','NSSF'=>'NSSF — ប.ស.ស','CASH'=>'CASH','CARD'=>'CARD'];

    // Pre-compute catalogs for JS (avoid Blade ParseError)
    $svcJs = $svcCatalog->map(fn($c) => ['code'=>$c->code,'name'=>$c->name,'category'=>$c->category??'','price'=>(float)$c->price])->values()->all();
    $medJs = $medCatalog->map(fn($c) => ['id'=>$c->id,'code'=>$c->code,'name'=>$c->name,'strength'=>$c->strength??'','form'=>$c->form??'','unit'=>$c->unit??'','price'=>(float)$c->price,'stock'=>(int)$c->stock,'alert'=>(int)($c->stock_alert??10)])->values()->all();
@endphp

<x-step.card step-id="invoice" :visit="$visit" :step-idx="$stepIdx" :steps="$steps"
             icon="bi-receipt-cutoff" icon-color="#00bcd4"
             km="វិក្កយបត្រ" en="Invoice"
             :badge="$invoice ? $sc['label'] : null" badge-color="{{ $sc['color'] }}">

    {{-- Validation errors --}}
    @if($errors->has('inv_meds'))
        <div class="note note-danger mb-3">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <div>
                <strong>Out-of-stock / Validation Error:</strong>
                <ul style="margin:4px 0 0;padding-left:16px">
                    @foreach($errors->get('inv_meds') as $err)
                        @foreach((array)$err as $e)
                            <li style="font-size:12px">{{ $e }}</li>
                        @endforeach
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    {{-- ══ PATIENT BANNER ══════════════════════════════════════════════════════ --}}
    <div class="inv-banner">
        <div class="inv-banner-left">
            <div class="inv-banner-icon">🧾</div>
            <div>
                <div class="inv-banner-name">{{ $visit->surname }}, {{ $visit->name }}</div>
                <div class="inv-banner-meta">
                    <span>{{ $visit->patient_code }}</span>
                    <span class="badge-s {{ $visit->visit_type==='IPD'?'b-ipd':'b-opd' }}"
                          style="font-size:9px">{{ $visit->visit_type }}</span>
                    <span>{{ df_d($visit->admitted_at) }}</span>
                    <span class="inv-pay-badge"
                          style="background:{{ ['HEF'=>'#e0f2fe','NSSF'=>'#f0fdf4','CASH'=>'#fefce8','CARD'=>'#f5f3ff'][$paymentType]??'#f5f5f5' }};color:{{ ['HEF'=>'#0369a1','NSSF'=>'#166534','CASH'=>'#854d0e','CARD'=>'#6d28d9'][$paymentType]??'#444' }}">
                    {{ $payLabels[$paymentType] ?? $paymentType }}
                </span>
                </div>
            </div>
        </div>
        <div class="inv-banner-right">
            <div style="font-size:10px;color:#8aabdc;margin-bottom:2px">Invoice</div>
            <div class="inv-banner-code">{{ $invCode }}</div>
            @if($invoice)
                <div class="inv-status-pill"
                     style="background:{{ $sc['bg'] }};color:{{ $sc['color'] }};border:1px solid {{ $sc['bdr'] }}">
                    <i class="bi {{ $sc['icon'] }}"></i> {{ $sc['label'] }}
                </div>
            @endif
        </div>
    </div>

    {{-- ══ META ═════════════════════════════════════════════════════════════════ --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-sm-3">
            <div class="fld">
                <label class="flbl"><span class="km">ប្រភេទ</span><span class="en">/ Payment Type</span><span
                        class="req">*</span></label>
                <select name="payment_type" class="form-select" onchange="updatePayBadge(this.value)">
                    @foreach($payLabels as $val=>$lbl)
                        <option value="{{ $val }}" {{ $paymentType===$val?'selected':'' }}>{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-6 col-sm-3">
            <x-form.field name="invoice_date" km="ថ្ងៃ" en="Date" type="date" :value="$invoiceDate"/>
        </div>
        <div class="col-12 col-sm-6">
            <x-form.field name="cashier" km="អ្នកគិតប្រាក់" en="Cashier" placeholder="Name" :value="$cashier"/>
        </div>
    </div>

    {{-- ══ SERVICES ════════════════════════════════════════════════════════════ --}}
    <div class="inv-section-hd" style="background:#0369a1">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-clipboard2-check-fill"></i>
            <span>Services / Procedures</span>
            <span class="inv-count-chip" id="svcCount">{{ $svcCount > 0 ? $svcCount.' items' : '' }}</span>
        </div>
        <button type="button" class="inv-add-btn" onclick="addSvcRow()"><i class="bi bi-plus"></i> Add</button>
    </div>

    <div class="table-responsive mb-1">
        <table class="tbl" id="svcTable">
            <thead>
            <tr>
                <th style="width:36%">សេវា / Service</th>
                <th style="width:22%">ប្រភេទ / Category</th>
                <th style="width:22%">ថ្លៃ KHR</th>
                <th style="width:14%">ស្ថានភាព</th>
                <th style="width:6%"></th>
            </tr>
            </thead>
            <tbody id="svcBody">
            @forelse($services as $i => $svc)
                @php $paid = $svc->paid > 0 ? '1' : '0'; @endphp
                <tr id="svc-row-{{ $i }}">
                    <td style="min-width:200px">
                        <x-form.autocomplete
                            :name="'services['.$i.'][service_code]'"
                            :name-text="'services['.$i.'][name]'"
                            km="សេវា" en="Service"
                            :items="$svcCatalog"
                            item-id="code"
                            item-label="name"
                            item-sub="category"
                            item-badge="price"
                            badge-type="price"
                            :value-id="$svc->service_code ?? $svc->service_name"
                            :value-text="$svc->service_name"
                            placeholder="Type service…"
                            :input-name="'svc_'.$i"/>
                    </td>
                    <td><input name="services[{{ $i }}][category]" class="form-control" id="svc-cat-{{ $i }}"
                               value="{{ $svc->service_category }}"/></td>
                    <td><input name="services[{{ $i }}][price]" type="number" class="form-control svc-price"
                               id="svc-price-{{ $i }}" value="{{ $svc->price }}" oninput="recalc()"/></td>
                    <td><select name="services[{{ $i }}][paid_status]" class="form-select status-sel"
                                onchange="styleStatus(this)">
                            <option value="1" {{ $paid==='1'?'selected':'' }}>Paid ✓</option>
                            <option value="0" {{ $paid==='0'?'selected':'' }}>Pending</option>
                        </select></td>
                    <td>
                        <button type="button" class="btn btn-sm btn-outline-danger"
                                onclick="this.closest('tr').remove();recalc()">✕
                        </button>
                    </td>
                </tr>
            @empty
                <tr id="svc-row-0">
                    <td style="min-width:200px">
                        <x-form.autocomplete
                            name="services[0][service_code]"
                            name-text="services[0][name]"
                            km="សេវា" en="Service"
                            :items="$svcCatalog"
                            item-id="code"
                            item-label="name"
                            item-sub="category"
                            item-badge="price"
                            badge-type="price"
                            placeholder="OPD Consultation…"
                            input-name="svc_0"/>
                    </td>
                    <td><input name="services[0][category]" class="form-control" id="svc-cat-0" value="Consultation"/>
                    </td>
                    <td><input name="services[0][price]" type="number" class="form-control svc-price" id="svc-price-0"
                               value="30000" oninput="recalc()"/></td>
                    <td><select name="services[0][paid_status]" class="form-select status-sel"
                                onchange="styleStatus(this)">
                            <option value="1">Paid ✓</option>
                            <option value="0">Pending</option>
                        </select></td>
                    <td>
                        <button type="button" class="btn btn-sm btn-outline-danger"
                                onclick="this.closest('tr').remove();recalc()">✕
                        </button>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="inv-subtotal" style="color:#0369a1">
        <i class="bi bi-receipt"></i> Services: <strong id="svcTotal">{{ khr($services->sum('price')) }}</strong>
    </div>

    {{-- ══ MEDICINES ═══════════════════════════════════════════════════════════ --}}
    <div class="inv-section-hd" style="background:#7c3aed;margin-top:16px">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-capsule-fill"></i>
            <span>Medicines / Pharmacy</span>
            <span class="inv-count-chip" id="medCount">{{ $medCount > 0 ? $medCount.' items' : '' }}</span>
        </div>
        <button type="button" class="inv-add-btn" onclick="addMedRow()"><i class="bi bi-plus"></i> Add</button>
    </div>

    <div class="table-responsive mb-1">
        <table class="tbl" id="medTable">
            <thead>
            <tr>
                <th style="width:34%">ថ្នាំ / Medicine</th>
                <th style="width:12%">ចំនួន</th>
                <th style="width:18%">ថ្លៃ/ឯកតា KHR</th>
                <th style="width:14%">សរុប</th>
                <th style="width:14%">ស្ថានភាព</th>
                <th style="width:8%"></th>
            </tr>
            </thead>
            <tbody id="medBody">
            @forelse($meds as $i => $med)
                @php
                    $paid    = $med->paid > 0 ? '1' : '0';
                    $medItem = $medCatalog->firstWhere('code', $med->medicine_code);
                @endphp
                <tr id="med-row-{{ $i }}">
                    <td style="min-width:200px">
                        <x-form.autocomplete
                            :name="'inv_meds['.$i.'][medicine_id]'"
                            :name-text="'inv_meds['.$i.'][name]'"
                            km="ថ្នាំ" en="Medicine"
                            :items="$medCatalog"
                            item-id="id"
                            item-label="name"
                            item-sub="strength"
                            item-meta="form"
                            item-badge="stock"
                            badge-type="stock"
                            :value-id="$medItem?->id ?? ''"
                            :value-text="$med->medicine_name"
                            placeholder="Type medicine…"
                            :input-name="'med_'.$i"/>
                        <input type="hidden" name="inv_meds[{{ $i }}][medicine_code]" id="med-code-{{ $i }}"
                               value="{{ $med->medicine_code }}"/>
                    </td>
                    <td style="min-width:70px"><input name="inv_meds[{{ $i }}][qty]" type="number"
                                                      class="form-control med-qty" value="{{ $med->quantity }}" min="1"
                                                      oninput="calcRow(this)"/></td>
                    <td style="min-width:100px"><input name="inv_meds[{{ $i }}][price]" id="med-unit-{{ $i }}"
                                                       type="number" class="form-control med-unit"
                                                       value="{{ $med->price }}" oninput="calcRow(this)"/></td>
                    <td>
                        <div class="inv-row-total" id="row-total-m{{ $i }}">{{ khr($med->payment) }}</div>
                    </td>
                    <td><select name="inv_meds[{{ $i }}][paid_status]" class="form-select status-sel"
                                onchange="styleStatus(this)">
                            <option value="1" {{ $paid==='1'?'selected':'' }}>Paid ✓</option>
                            <option value="0" {{ $paid==='0'?'selected':'' }}>Pending</option>
                        </select></td>
                    <td>
                        <button type="button" class="btn btn-sm btn-outline-danger"
                                onclick="this.closest('tr').remove();recalc()">✕
                        </button>
                    </td>
                </tr>
            @empty
                <tr id="med-empty-row">
                    <td colspan="6" style="text-align:center;padding:14px;color:#aaa;font-size:12px">
                        No medicines added —
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="addMedRow()">
                            <i class="bi bi-plus"></i> Add medicine
                        </button>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="inv-subtotal" style="color:#7c3aed">
        <i class="bi bi-capsule-fill"></i> Medicines: <strong id="medTotal">{{ khr($meds->sum('payment')) }}</strong>
    </div>

    {{-- ══ DISCOUNT & NOTES ════════════════════════════════════════════════════ --}}
    <div class="row g-2 mt-2 mb-2">
        <div class="col-6 col-sm-3">
            <div class="fld">
                <label class="flbl">
                    <span class="km">បញ្ចុះតម្លៃ</span><span class="en">/ Discount (KHR)</span>
                </label>
                <input name="discount" type="number" id="discountInput" class="form-control"
                       min="0" placeholder="0"
                       value="{{ old('discount', $invoice?->discount ?? 0) }}"
                       oninput="recalc()"/>
            </div>
        </div>
        <div class="col-6 col-sm-3">
            <div class="fld">
                <label class="flbl"><span class="en">Discount %</span></label>
                <input type="number" id="discountPct" class="form-control"
                       min="0" max="100" placeholder="0"
                       oninput="applyDiscountPct(this.value)"/>
            </div>
        </div>
        <div class="col-12 col-sm-6">
            <div class="fld">
                <label class="flbl"><span class="km">កំណត់ចំណាំ</span><span class="en">/ Notes</span></label>
                <input name="notes" class="form-control" placeholder="Internal notes…"
                       value="{{ old('notes', $invoice?->notes ?? '') }}"/>
            </div>
        </div>
    </div>

    {{-- ══ TOTAL AREA ══════════════════════════════════════════════════════════ --}}
    <div class="inv-total-area">
        <div class="inv-breakdown">
            <div class="inv-breakdown-row">
                <span><i class="bi bi-clipboard2-check" style="color:#0369a1"></i> Services</span>
                <span id="bdSvc" style="color:#0369a1;font-weight:700">{{ khr($services->sum('price')) }}</span>
            </div>
            <div class="inv-breakdown-row">
                <span><i class="bi bi-capsule-fill" style="color:#7c3aed"></i> Medicines</span>
                <span id="bdMed" style="color:#7c3aed;font-weight:700">{{ khr($meds->sum('payment')) }}</span>
            </div>
            <div id="bdDiscountRow" class="inv-breakdown-row"
                 style="{{ ($invoice?->discount ?? 0) > 0 ? '' : 'display:none' }};color:#2eca6a">
                <span><i class="bi bi-tag-fill"></i> Discount</span>
                <span id="bdDiscount" style="color:#2eca6a;font-weight:700">
                    − {{ khr($invoice?->discount ?? 0) }}
                </span>
            </div>
            <div style="border-top:1px dashed #cbd5e1;margin:8px 0"></div>
            <div class="inv-breakdown-row" style="font-size:12px;color:#64748b">
                <span>Subtotal (after discount)</span>
                <span id="bdComputed" style="font-weight:700;color:#1a1f36">
                    {{ khr(max(0, $services->sum('price') + $meds->sum('payment') - ($invoice?->discount ?? 0))) }}
                </span>
            </div>
        </div>
        <div class="inv-declared">
            <div style="font-size:10px;color:#64748b;font-weight:700;text-transform:uppercase;letter-spacing:.4px;margin-bottom:6px">
                ប្រាក់សរុបប្រកាស / Declared Total (KHR)
            </div>
            <div style="display:flex;align-items:center;gap:8px">
                <input name="total" type="number" id="totalInput"
                       class="form-control"
                       style="font-size:24px;font-weight:800;color:#1a1f36;text-align:right;border:2px solid #e2e8f0;max-width:200px"
                       value="{{ $totalVal }}" oninput="checkTotal()" placeholder="0"/>
                <span style="font-size:14px;color:#6b7280;font-weight:700">KHR</span>
            </div>
            <div id="totalStatus" class="note note-info mt-2"
                 style="font-size:12px;padding:8px 12px;display:inline-flex">
                <i class="bi bi-info-circle-fill"></i>&nbsp;Enter declared total
            </div>
        </div>
    </div>

    {{-- ══ PAYMENT STATUS ══════════════════════════════════════════════════════ --}}
    <div class="row g-3 mt-2 mb-4">
        <div class="col-6 col-sm-4">
            <div class="fld">
                <label class="flbl"><span class="km">ស្ថានភាព</span><span class="en">/ Payment Status</span></label>
                <select name="status" class="form-select" onchange="updateStatus(this.value)">
                    @foreach(['pending'=>'⏳ Pending','partial'=>'💳 Partial','paid'=>'✅ Paid','void'=>'❌ Void'] as $val=>$lbl)
                        <option value="{{ $val }}" {{ $invStatus===$val?'selected':'' }}>{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-6 col-sm-4">
            <x-form.field name="amount_paid" km="បានទូទាត់" en="Amount Paid" type="number"
                          :value="old('amount_paid', $invoice?->payments?->sum('amount') ?? 0)"/>
        </div>
        <div class="col-12 col-sm-4">
            <div class="fld">
                <div style="height:24px"></div>
                <div id="statusPill" class="form-control ro"
                     style="text-align:center;font-weight:700;background:{{ $sc['bg'] }};color:{{ $sc['color'] }};border:1px solid {{ $sc['bdr'] }}">
                    <i class="bi {{ $sc['icon'] }}"></i> {{ $sc['label'] }}
                </div>
            </div>
        </div>
    </div>

    {{-- ══ COMPLETE VISIT GATE ═════════════════════════════════════════════════ --}}
    <div class="inv-gate">
        <div class="inv-gate-checks">
            <div class="inv-gate-item" id="gate-inv">
                <i class="bi {{ $invoicePaid ? 'bi-check-circle-fill' : 'bi-circle' }}" id="gate-inv-icon"
                   style="color:{{ $invoicePaid ? '#1D9E75' : '#6b7280' }}"></i>
                <span id="gate-inv-txt">Invoice {{ $invoicePaid ? 'Paid ✓' : 'not paid yet' }}</span>
            </div>
            <div class="inv-gate-item">
                @if(!$hasRx)
                    <i class="bi bi-dash-circle" style="color:#6b7280"></i>
                    <span style="color:#6b7280">No prescription (OK — not required)</span>
                @else
                    <i class="bi {{ $rxDispensed ? 'bi-check-circle-fill' : 'bi-circle' }}"
                       style="color:{{ $rxDispensed ? '#1D9E75' : '#6b7280' }}"></i>
                    <span>Prescription {{ $rxDispensed ? 'Dispensed ✓' : 'not fully dispensed yet' }}</span>
                @endif
            </div>
        </div>
        <div class="d-flex gap-2 flex-wrap align-items-center">
            @if($invoice)
                <a href="{{ route('print.invoice', $invoice->code) }}" target="_blank"
                   class="btn btn-outline-secondary" style="padding:9px 14px;font-size:12px">
                    <i class="bi bi-printer-fill"></i> Print A4
                </a>
            @endif
            <button type="submit" class="btn btn-primary" style="padding:10px 20px">
                <i class="bi bi-check2-circle"></i> Save Invoice
            </button>
            <button type="submit" name="_complete" value="1" id="completeBtn"
                    class="btn {{ $canComplete ? 'btn-success' : 'btn-secondary' }}"
                    style="padding:10px 22px;font-size:14px"
                    {{ $canComplete ? '' : 'disabled' }}
                    title="{{ !$canComplete ? 'Requires Invoice Paid + Prescription Dispensed (if any)' : 'All conditions met' }}">
                <i class="bi bi-check2-all"></i> បញ្ចប់ / Complete Visit
                @if(!$canComplete)
                    <i class="bi bi-lock-fill" style="font-size:11px;margin-left:2px"></i>
                @endif
            </button>
        </div>
    </div>

</x-step.card>

{{-- Invoice CSS is in public/css/app-layout.css --}}
<style>
    .inv-banner {
        background: linear-gradient(135deg, #1a1f36, #1a3a7c);
        border-radius: 12px;
        padding: 14px 18px;
        margin-bottom: 20px;
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
    }

    .inv-banner-left {
        display: flex;
        align-items: center;
        gap: 14px;
        flex: 1;
        min-width: 0;
    }

    .inv-banner-icon {
        font-size: 28px;
        flex-shrink: 0;
    }

    .inv-banner-name {
        font-size: 15px;
        font-weight: 800;
        color: #fff;
    }

    .inv-banner-meta {
        font-size: 11px;
        color: #8aabdc;
        margin-top: 4px;
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .inv-banner-right {
        text-align: right;
        flex-shrink: 0;
    }

    .inv-banner-code {
        font-size: 15px;
        font-weight: 800;
        color: #fff;
        font-family: monospace;
    }

    .inv-status-pill {
        margin-top: 4px;
        font-size: 11px;
        padding: 2px 10px;
        border-radius: 10px;
        font-weight: 700;
        display: inline-block;
    }

    .inv-pay-badge {
        font-size: 10px;
        padding: 2px 8px;
        border-radius: 10px;
        font-weight: 700;
    }

    .inv-section-hd {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 6px;
        color: #fff;
        font-size: 11.5px;
        font-weight: 800;
        padding: 9px 14px;
        border-radius: 8px;
        margin-bottom: 10px;
        text-transform: uppercase;
        letter-spacing: .5px;
    }

    .inv-count-chip {
        background: rgba(255, 255, 255, .2);
        padding: 1px 8px;
        border-radius: 10px;
        font-size: 10px;
    }

    .inv-add-btn {
        background: rgba(255, 255, 255, .15);
        color: #fff;
        border: 1px solid rgba(255, 255, 255, .25);
        border-radius: 6px;
        padding: 3px 12px;
        font-size: 12px;
        font-weight: 700;
        cursor: pointer;
        transition: background .15s;
    }

    .inv-add-btn:hover {
        background: rgba(255, 255, 255, .3);
    }

    .inv-subtotal {
        font-size: 12px;
        font-weight: 600;
        text-align: right;
        padding: 4px 8px;
        margin-bottom: 4px;
    }

    .inv-total-area {
        background: #f8fafc;
        border: 2px solid #e2e8f0;
        border-radius: 14px;
        padding: 20px 24px;
        margin: 16px 0;
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 20px;
    }

    .inv-breakdown {
        flex: 1;
        min-width: 180px;
    }

    .inv-breakdown-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 13px;
        padding: 4px 0;
        color: #374151;
    }

    .inv-declared {
        text-align: right;
        flex-shrink: 0;
    }

    .inv-row-total {
        font-size: 13px;
        font-weight: 700;
        color: #1a1f36;
        white-space: nowrap;
    }

    .inv-gate {
        background: #f8fafc;
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        padding: 16px 20px;
        margin-top: 16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
    }

    .inv-gate-checks {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .inv-gate-item {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
        color: #374151;
        font-weight: 600;
    }

    .stock-badge {
        font-size: 10px;
        padding: 2px 8px;
        border-radius: 10px;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 3px;
    }

    .stock-ok {
        background: #e8f8ef;
        color: #1D9E75;
        border: 1px solid #b7eacf;
    }

    .stock-low {
        background: #fff8e1;
        color: #b45309;
        border: 1px solid #fde68a;
    }

    .stock-out {
        background: #fde8e8;
        color: #dc2626;
        border: 1px solid #fca5a5;
    }

    .status-sel option[value="1"] {
        color: #1D9E75;
        font-weight: 700;
    }

    .status-sel option[value="0"] {
        color: #b45309;
    }
</style>

<script>
    var svcIdx = {{ max($svcCount, 1) }};
    var medIdx = {{ max($medCount, 1) }};
    var svcCatalog = @json($svcJs);
    var medCatalog = @json($medJs);

    // ── Formatting ──────────────────────────────────────────────────────────────
    function fmt(n) {
        return Math.round(n).toLocaleString() + ' KHR';
    }

    // ── Totals ──────────────────────────────────────────────────────────────────
    function recalc() {
        var svcSum  = sum('.svc-price');
        var medSum  = medRowSum();
        var disc    = parseFloat(document.getElementById('discountInput')?.value || 0);
        var subtotal = Math.max(0, svcSum + medSum - disc);

        setText('svcTotal',  fmt(svcSum));
        setText('medTotal',  fmt(medSum));
        setText('bdSvc',     fmt(svcSum));
        setText('bdMed',     fmt(medSum));
        setText('bdComputed',fmt(subtotal));

        // Discount row visibility
        var bdDiscRow = document.getElementById('bdDiscountRow');
        var bdDisc    = document.getElementById('bdDiscount');
        if (bdDiscRow) bdDiscRow.style.display = (disc > 0) ? 'flex' : 'none';
        if (bdDisc)    bdDisc.textContent = '− ' + fmt(disc);

        var inp = document.getElementById('totalInput');
        if (inp && (!inp.value || parseFloat(inp.value) === 0) && subtotal > 0) {
            inp.value = Math.round(subtotal);
        }
        checkTotal();
        updateSvcCount();
        updateMedCount();
    }

    function applyDiscountPct(pct) {
        var svcSum = sum('.svc-price');
        var medSum = medRowSum();
        var gross  = svcSum + medSum;
        var disc   = Math.round(gross * (parseFloat(pct) || 0) / 100);
        var inp = document.getElementById('discountInput');
        if (inp) { inp.value = disc; recalc(); }
    }

    function sum(sel) {
        return Array.from(document.querySelectorAll(sel))
            .reduce(function (s, e) {
                return s + (parseFloat(e.value) || 0);
            }, 0);
    }

    function medRowSum() {
        var rows = document.querySelectorAll('#medBody tr:not(#med-empty-row)');
        var total = 0;
        rows.forEach(function (tr) {
            var qty = parseFloat(tr.querySelector('.med-qty')?.value || 0);
            var price = parseFloat(tr.querySelector('.med-unit')?.value || 0);
            total += qty * price;
        });
        return total;
    }

    function calcRow(input) {
        var tr = input.closest('tr');
        var qty = parseFloat(tr.querySelector('.med-qty')?.value || 0);
        var price = parseFloat(tr.querySelector('.med-unit')?.value || 0);
        var rowTot = tr.querySelector('.inv-row-total');
        if (rowTot) rowTot.textContent = fmt(qty * price);
        recalc();
    }

    function checkTotal() {
        var declared = parseFloat(document.getElementById('totalInput')?.value || 0);
        var disc     = parseFloat(document.getElementById('discountInput')?.value || 0);
        var computed = Math.max(0, sum('.svc-price') + medRowSum() - disc);
        var status = document.getElementById('totalStatus');
        if (!status) return;
        if (!declared) {
            status.className = 'note note-info mt-2';
            status.innerHTML = '<i class="bi bi-info-circle-fill"></i>&nbsp;Enter declared total';
        } else if (Math.abs(declared - computed) < 1) {
            status.className = 'note note-success mt-2';
            status.innerHTML = '<i class="bi bi-check-circle-fill"></i>&nbsp;<strong>ត្រូវគ្នា / Verified ✓</strong>';
        } else {
            status.className = 'note note-danger mt-2';
            status.innerHTML = '<i class="bi bi-exclamation-triangle-fill"></i>&nbsp;Mismatch — computed: <strong>' + fmt(computed) + '</strong>';
        }
    }

    function setText(id, val) {
        var el = document.getElementById(id);
        if (el) el.textContent = val;
    }

    // ── Counts ──────────────────────────────────────────────────────────────────
    function updateSvcCount() {
        var n = document.querySelectorAll('#svcBody tr').length;
        var el = document.getElementById('svcCount');
        if (el) el.textContent = n > 0 ? n + ' items' : '';
    }

    function updateMedCount() {
        var rows = document.querySelectorAll('#medBody tr:not(#med-empty-row)');
        var el = document.getElementById('medCount');
        if (el) el.textContent = rows.length > 0 ? rows.length + ' items' : '';
    }

    // ── Status select styling ────────────────────────────────────────────────────
    function styleStatus(sel) {
        sel.style.color = sel.value === '1' ? '#1D9E75' : '#b45309';
        sel.style.fontWeight = '700';
    }

    // ── Payment status update ────────────────────────────────────────────────────
    var statusCfg = {
        pending: {bg: '#fff8e1', color: '#b45309', bdr: '#fde68a', label: '⏳ Pending Payment'},
        partial: {bg: '#e0f2fe', color: '#0369a1', bdr: '#7dd3fc', label: '💳 Partially Paid'},
        paid: {bg: '#e8f8ef', color: '#1D9E75', bdr: '#b7eacf', label: '✅ Paid'},
        void: {bg: '#fde8e8', color: '#dc2626', bdr: '#fca5a5', label: '❌ Void'},
    };

    function updateStatus(val) {
        var cfg = statusCfg[val] || statusCfg['pending'];
        var pill = document.getElementById('statusPill');
        if (pill) {
            pill.style.background = cfg.bg;
            pill.style.color = cfg.color;
            pill.style.border = '1px solid ' + cfg.bdr;
            pill.innerHTML = cfg.label;
        }
        var isPaid = val === 'paid';
        var hasRx = {{ $hasRx ? 'true' : 'false' }};
        var rxDone = {{ $rxDispensed ? 'true' : 'false' }};
        var canDo = isPaid && (!hasRx || rxDone);
        var btn = document.getElementById('completeBtn');
        if (btn) {
            btn.disabled = !canDo;
            btn.className = 'btn ' + (canDo ? 'btn-success' : 'btn-secondary');
            btn.style.padding = '10px 22px';
            btn.style.fontSize = '14px';
        }
        var icon = document.getElementById('gate-inv-icon');
        var txt = document.getElementById('gate-inv-txt');
        if (icon) {
            icon.className = 'bi ' + (isPaid ? 'bi-check-circle-fill' : 'bi-circle');
            icon.style.color = isPaid ? '#1D9E75' : '#6b7280';
        }
        if (txt) {
            txt.textContent = 'Invoice ' + (isPaid ? 'Paid ✓' : 'not paid yet');
        }
    }

    function updatePayBadge(val) {
        // visual only — form submit carries the value
    }

    // ── Add Service Row ──────────────────────────────────────────────────────────
    function addSvcRow() {
        var empty = document.getElementById('svc-empty-row');
        if (empty) empty.remove();
        var tbody = document.getElementById('svcBody');
        var idx = svcIdx;
        var tr = document.createElement('tr');
        tr.id = 'svc-row-' + idx;
        tr.innerHTML =
            '<td style="min-width:200px">'
            + '<div class="fld ac-wrap" id="acWrap_svc_' + idx + '" style="position:relative">'
            + '<div style="position:relative">'
            + '<i class="bi bi-search" style="position:absolute;left:11px;top:50%;transform:translateY(-50%);color:#6b7280;font-size:13px;pointer-events:none;z-index:1"></i>'
            + '<input type="text" id="acText_svc_' + idx + '" class="form-control" style="padding-left:34px;padding-right:30px" placeholder="Type service…" autocomplete="off" data-ac-uid="svc_' + idx + '"/>'
            + '<button type="button" id="acClear_svc_' + idx + '" style="position:absolute;right:8px;top:50%;transform:translateY(-50%);background:none;border:none;color:#6b7280;font-size:14px;cursor:pointer;padding:0;display:none;line-height:1;z-index:2" onclick="acClear('
        svc_
        '+idx+'
        ')">'
        + '<i class="bi bi-x-circle-fill"></i></button>'
        + '</div>'
        + '<input type="hidden" name="services[' + idx + '][service_code]" id="acId_svc_' + idx + '"/>'
        + '<input type="hidden" name="services[' + idx + '][name]" id="svc-name-' + idx + '"/>'
        + '<div id="acDd_svc_' + idx + '" class="ac-dropdown" style="display:none"></div>'
        + '</div>'
        + '</td>'
        + '<td><input name="services[' + idx + '][category]" class="form-control" id="svc-cat-' + idx + '" placeholder="Category"/></td>'
        + '<td><input name="services[' + idx + '][price]" type="number" class="form-control svc-price" id="svc-price-' + idx + '" value="0" oninput="recalc()"/></td>'
        + '<td><select name="services[' + idx + '][paid_status]" class="form-select status-sel" onchange="styleStatus(this)"><option value="1">Paid ✓</option><option value="0">Pending</option></select></td>'
        + '<td><button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('
        tr
        ').remove();recalc()">✕</button></td>';
        tbody.appendChild(tr);
        initSvcAC(idx);
        svcIdx++;
        recalc();
    }

    function initSvcAC(idx) {
        var elText = document.getElementById('acText_svc_' + idx);
        var elId = document.getElementById('acId_svc_' + idx);
        var elName = document.getElementById('svc-name-' + idx);
        var elDd = document.getElementById('acDd_svc_' + idx);
        var elClear = document.getElementById('acClear_svc_' + idx);
        var elWrap = document.getElementById('acWrap_svc_' + idx);
        if (!elText) return;

        var filtered = [], activeIdx = -1;

        function filterSvc(q) {
            if (!q) return svcCatalog.slice(0, 12);
            q = q.toLowerCase();
            return svcCatalog.filter(function (c) {
                return (c.name || '').toLowerCase().indexOf(q) !== -1 || (c.category || '').toLowerCase().indexOf(q) !== -1;
            }).slice(0, 12);
        }

        function esc(s) {
            return String(s || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
        }

        function renderSvcDd(q) {
            filtered = filterSvc(q);
            activeIdx = -1;
            if (!filtered.length) {
                elDd.innerHTML = '<div class="ac-empty">No results for <strong>' + esc(q) + '</strong></div>';
            } else {
                elDd.innerHTML = filtered.map(function (item, i) {
                    return '<div class="ac-row" onmousedown="pickSvc(' + idx + ',' + i + ')">'
                        + '<div class="ac-row-body"><div class="ac-row-label">' + esc(item.name) + '</div>'
                        + '<div class="ac-row-sub"><span>' + esc(item.category || '') + '</span></div></div>'
                        + '<div class="ac-row-badge"><span style="font-size:11px;color:#4154f1;font-weight:700">' + Math.round(item.price || 0).toLocaleString() + ' KHR</span></div>'
                        + '</div>';
                }).join('');
            }
            elDd.style.display = 'block';
        }

        window['pickSvc_' + idx] = function (i) {
            var item = filtered[i];
            if (!item) return;
            elText.value = item.name;
            elId.value = item.code || '';
            if (elName) elName.value = item.name;
            var catEl = document.getElementById('svc-cat-' + idx);
            var priEl = document.getElementById('svc-price-' + idx);
            if (catEl) catEl.value = item.category || '';
            if (priEl) {
                priEl.value = item.price || 0;
                recalc();
            }
            elClear.style.display = 'block';
            elDd.style.display = 'none';
        };

        function pickSvc(ri, i) {
            if (ri === idx) window['pickSvc_' + idx](i);
        }

        window['pickSvc'] = function (ri, i) {
            if (ri === idx) window['pickSvc_' + idx](i);
        };

        elText.addEventListener('input', function () {
            elClear.style.display = this.value ? 'block' : 'none';
            renderSvcDd(this.value.trim());
        });
        elText.addEventListener('focus', function () {
            renderSvcDd(this.value.trim());
        });
        document.addEventListener('click', function (e) {
            if (!elWrap?.contains(e.target)) elDd.style.display = 'none';
        });

        // Also patch onclick of rows to use local pickSvc
        elDd.addEventListener('mousedown', function (e) {
            var row = e.target.closest('.ac-row');
            if (!row) return;
            var rows = Array.from(elDd.querySelectorAll('.ac-row'));
            window['pickSvc_' + idx](rows.indexOf(row));
        });
    }

    function autoFillSvc(input, idx) {
        var item = svcCatalog.find(function (c) {
            return c.name === input.value;
        });
        if (!item) return;
        var catEl = document.getElementById('svc-cat-' + idx);
        var priEl = document.getElementById('svc-price-' + idx);
        if (catEl) catEl.value = item.category;
        if (priEl) {
            priEl.value = item.price;
            recalc();
        }
    }

    // ── Add Medicine Row ─────────────────────────────────────────────────────────
    function addMedRow() {
        var empty = document.getElementById('med-empty-row');
        if (empty) empty.remove();
        var tbody = document.getElementById('medBody');
        var idx = medIdx;
        var tr = document.createElement('tr');
        tr.id = 'med-row-' + idx;
        tr.innerHTML =
            '<td style="min-width:200px">'
            + '<div class="fld ac-wrap" id="acWrap_med_' + idx + '" style="position:relative">'
            + '<div style="position:relative">'
            + '<i class="bi bi-search" style="position:absolute;left:11px;top:50%;transform:translateY(-50%);color:#6b7280;font-size:13px;pointer-events:none;z-index:1"></i>'
            + '<input type="text" id="acText_med_' + idx + '" class="form-control" style="padding-left:34px;padding-right:30px" placeholder="Type medicine\u2026" autocomplete="off"/>'
            + '<button type="button" id="acClear_med_' + idx + '" style="position:absolute;right:8px;top:50%;transform:translateY(-50%);background:none;border:none;color:#6b7280;font-size:14px;cursor:pointer;padding:0;display:none;line-height:1;z-index:2" onclick="acClearMed(' + idx + ')">'
            + '<i class="bi bi-x-circle-fill"></i></button>'
            + '</div>'
            + '<input type="hidden" name="inv_meds[' + idx + '][medicine_id]" id="acId_med_' + idx + '"/>'
            + '<input type="hidden" name="inv_meds[' + idx + '][medicine_code]" id="med-code-' + idx + '"/>'
            + '<input type="hidden" name="inv_meds[' + idx + '][name]" id="med-name-' + idx + '"/>'
            + '<span id="med-stock-' + idx + '" class="stock-badge" style="display:none;margin-top:3px"></span>'
            + '<div id="acDd_med_' + idx + '" class="ac-dropdown" style="display:none"></div>'
            + '</div>'
            + '</td>'
            + '<td style="min-width:70px"><input name="inv_meds[' + idx + '][qty]" id="med-qty-' + idx + '" type="number" class="form-control med-qty" value="1" min="1" oninput="calcRow(this)"/></td>'
            + '<td style="min-width:100px"><input name="inv_meds[' + idx + '][price]" id="med-unit-' + idx + '" type="number" class="form-control med-unit" value="0" oninput="calcRow(this)"/></td>'
            + '<td><div class="inv-row-total" id="row-total-m' + idx + '">0 KHR</div></td>'
            + '<td><select name="inv_meds[' + idx + '][paid_status]" class="form-select status-sel" onchange="styleStatus(this)"><option value="1">Paid \u2713</option><option value="0">Pending</option></select></td>'
            + '<td><button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest(\'tr\').remove();recalc()">\u2715</button></td>';
        tbody.appendChild(tr);
        initMedAC(idx);
        medIdx++;
        recalc();
    }

    function acClearMed(idx) {
        var elText = document.getElementById('acText_med_' + idx);
        var elId = document.getElementById('acId_med_' + idx);
        var elClear = document.getElementById('acClear_med_' + idx);
        var elDd = document.getElementById('acDd_med_' + idx);
        if (elText) elText.value = '';
        if (elId) elId.value = '';
        if (elClear) elClear.style.display = 'none';
        if (elDd) elDd.style.display = 'none';
    }

    function initMedAC(idx) {
        var elText = document.getElementById('acText_med_' + idx);
        var elId = document.getElementById('acId_med_' + idx);
        var elDd = document.getElementById('acDd_med_' + idx);
        var elClear = document.getElementById('acClear_med_' + idx);
        var elWrap = document.getElementById('acWrap_med_' + idx);
        if (!elText) return;
        var filtered = [];

        function filterMed(q) {
            if (!q) return medCatalog.slice(0, 12);
            q = q.toLowerCase();
            return medCatalog.filter(function (c) {
                return (c.name || '').toLowerCase().indexOf(q) !== -1 || (c.strength || '').toLowerCase().indexOf(q) !== -1 || (c.form || '').toLowerCase().indexOf(q) !== -1;
            }).slice(0, 12);
        }

        function esc(s) {
            return String(s || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
        }

        function renderMedDd(q) {
            filtered = filterMed(q);
            if (!filtered.length) {
                elDd.innerHTML = '<div class="ac-empty">No results for <strong>' + esc(q) + '</strong></div>';
            } else {
                elDd.innerHTML = filtered.map(function (item) {
                    var stk = item.stock <= 0 ? '<span class="stock-badge stock-out" style="font-size:10px">Out</span>' : item.stock <= item.alert ? '<span class="stock-badge stock-low" style="font-size:10px">Low: ' + item.stock + '</span>' : '<span class="stock-badge stock-ok" style="font-size:10px">\u2713 ' + item.stock + '</span>';
                    return '<div class="ac-row" style="cursor:pointer">'
                        + '<div class="ac-row-body">'
                        + '<div class="ac-row-label">' + esc(item.name) + '</div>'
                        + '<div class="ac-row-sub"><span>' + esc(item.strength || '') + '</span>' + (item.form ? '<span class="ac-meta"> ' + esc(item.form) + '</span>' : '') + '</div>'
                        + '</div><div class="ac-row-badge">' + stk + '</div>'
                        + '</div>';
                }).join('');
            }
            elDd.style.display = 'block';
        }

        elDd.addEventListener('mousedown', function (e) {
            var row = e.target.closest('.ac-row');
            if (!row) return;
            e.preventDefault();
            var rows = Array.from(elDd.querySelectorAll('.ac-row'));
            var item = filtered[rows.indexOf(row)];
            if (!item) return;
            elText.value = item.name;
            elId.value = item.id;
            elClear.style.display = 'block';
            elDd.style.display = 'none';
            setVal('med-code-' + idx, item.code);
            setVal('med-name-' + idx, item.name);
            setVal('med-unit-' + idx, item.price);
            var badge = document.getElementById('med-stock-' + idx);
            if (badge) {
                badge.style.display = 'inline-block';
                if (item.stock <= 0) {
                    badge.textContent = 'Out of stock';
                    badge.className = 'stock-badge stock-out';
                } else if (item.stock <= item.alert) {
                    badge.textContent = 'Low: ' + item.stock;
                    badge.className = 'stock-badge stock-low';
                } else {
                    badge.textContent = '\u2713 In stock: ' + item.stock;
                    badge.className = 'stock-badge stock-ok';
                }
            }
            calcRow(document.getElementById('med-qty-' + idx));
        });
        elText.addEventListener('input', function () {
            elClear.style.display = this.value ? 'block' : 'none';
            renderMedDd(this.value.trim());
        });
        elText.addEventListener('focus', function () {
            renderMedDd(this.value.trim());
        });
        document.addEventListener('click', function (e) {
            if (!elWrap || !elWrap.contains(e.target)) elDd.style.display = 'none';
        });
    }

    function fillMedRow(sel, idx) {
        var opt = sel.options[sel.selectedIndex];
        if (!opt.value) return;
        var item = medCatalog.find(function (c) {
            return c.id == opt.value;
        });
        if (!item) return;
        setVal('med-code-' + idx, item.code);
        setVal('med-name-' + idx, item.name);
        setVal('med-unit-' + idx, item.price);
        calcRow(document.getElementById('med-qty-' + idx));
    }

    function setVal(id, val) {
        var el = document.getElementById(id);
        if (el) el.value = val;
    }

    document.addEventListener('DOMContentLoaded', function () {
        recalc();
        document.querySelectorAll('.status-sel').forEach(styleStatus);
        updateStatus('{{ $invStatus }}');
    });
</script>
