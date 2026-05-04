<!DOCTYPE html>
<html lang="km">
<head>
<meta charset="UTF-8"/>
<title>Invoice {{ $invoice->code }}</title>
<style>
*{margin:0;padding:0;box-sizing:border-box}
@page{size:A4;margin:18mm 18mm}
body{font-family:'Hanuman','Khmer OS',Arial,sans-serif;font-size:11.5pt;color:#111;background:#fff}
.page{max-width:210mm;margin:0 auto;padding:10mm 12mm;background:#fff}

/* Header */
.inv-hd{display:flex;align-items:center;gap:14px;padding-bottom:10px;border-bottom:2.5px solid #00bcd4;margin-bottom:10px}
.clinic-logo{width:52px;height:52px;border-radius:50%;background:#e0f7fa;display:flex;align-items:center;justify-content:center;font-size:24px;flex-shrink:0}
.clinic-name{font-size:15pt;font-weight:900;color:#1a1f36}
.inv-title-badge{background:#00bcd4;color:#fff;padding:3px 18px;border-radius:20px;font-size:12pt;font-weight:700;margin-left:auto}

/* Meta row */
.inv-meta{display:grid;grid-template-columns:1fr 1fr 1fr;gap:8px;background:#f0f9ff;border:1.5px solid #00bcd433;border-radius:8px;padding:10px 14px;margin-bottom:12px}
.meta-field .lbl{font-size:8pt;color:#888;text-transform:uppercase;letter-spacing:.4px}
.meta-field .val{font-weight:700;color:#1a1f36;font-size:10.5pt}

/* Status badge */
.status-badge{display:inline-block;padding:3px 12px;border-radius:12px;font-weight:700;font-size:10pt}
.s-pending{background:#fff8e1;color:#b45309;border:1px solid #fde68a}
.s-partial{background:#e0f2fe;color:#0369a1;border:1px solid #7dd3fc}
.s-paid   {background:#e8f8ef;color:#1D9E75;border:1px solid #b7eacf}
.s-void   {background:#fde8e8;color:#dc2626;border:1px solid #fca5a5}

/* Patient */
.pt-box{background:#f9f9f9;border-left:3px solid #00bcd4;padding:8px 12px;margin-bottom:12px;border-radius:0 6px 6px 0;display:grid;grid-template-columns:repeat(3,1fr);gap:6px}
.pt-lbl{font-size:8pt;color:#888}
.pt-val{font-weight:700;color:#1a1f36;font-size:10.5pt}

/* Section */
.sec-title{font-size:9.5pt;font-weight:800;text-transform:uppercase;letter-spacing:.5px;padding:5px 10px;margin-bottom:6px;border-radius:4px}
.sec-svc{background:#e0f2fe;color:#0369a1}
.sec-med{background:#f3e8ff;color:#7c3aed}

/* Line items table */
.items-table{width:100%;border-collapse:collapse;margin-bottom:12px;font-size:10.5pt}
.items-table th{background:#f5f5f5;font-weight:800;font-size:9pt;padding:6px 8px;border:1px solid #ddd;color:#1a1f36}
.items-table td{padding:7px 8px;border:1px solid #e6eaf5;vertical-align:middle}
.items-table tr:nth-child(even) td{background:#f9fafb}
.items-table .num{text-align:right;font-weight:700}
.paid-chip{font-size:8.5pt;padding:1px 7px;border-radius:8px;font-weight:700}
.paid-yes{background:#e8f8ef;color:#1D9E75}
.paid-no {background:#fff8e1;color:#b45309}

/* Total box */
.total-box{margin-left:auto;max-width:260px;border:1.5px solid #e2e8f0;border-radius:8px;overflow:hidden;margin-bottom:14px}
.total-row{display:flex;justify-content:space-between;padding:7px 14px;font-size:10.5pt;border-bottom:1px solid #f0f0f0}
.total-row:last-child{border:none}
.total-grand{background:#1a1f36;color:#fff;font-size:13pt;font-weight:900;padding:10px 14px}

/* Signature */
.sig-row{display:grid;grid-template-columns:1fr 1fr 1fr;gap:30px;margin-top:16px;padding-top:14px;border-top:1px dashed #ddd}
.sig-box{text-align:center}
.sig-line{border-bottom:1px solid #aaa;height:36px;margin-bottom:4px}
.sig-label{font-size:8.5pt;color:#777}

/* Footer */
.inv-footer{margin-top:12px;padding-top:8px;border-top:1px solid #e0f7fa;display:flex;justify-content:space-between;font-size:8pt;color:#aaa}
.stamp{font-size:36pt;font-weight:900;color:#1D9E75;opacity:.12;position:absolute;transform:rotate(-25deg);right:60px;bottom:120px;border:8px solid #1D9E75;padding:4px 14px;border-radius:8px}

@media print{
    body{print-color-adjust:exact;-webkit-print-color-adjust:exact}
    .no-print{display:none!important}
    .stamp{opacity:.08}
}
</style>
</head>
<body>
<script>window.addEventListener('load',function(){window.print()})</script>

<div class="page" style="position:relative">

<div class="no-print" style="text-align:right;margin-bottom:8px">
    <button onclick="window.print()" style="background:#00bcd4;color:#fff;border:none;padding:7px 20px;border-radius:6px;font-size:13px;cursor:pointer;font-weight:700">
        🖨 Print Invoice
    </button>
</div>

@php
    $patient   = $invoice->visit?->patient;
    $svcTotal  = $invoice->services->sum('price');
    $medTotal  = $invoice->medications->sum('payment');
    $grandTotal= $invoice->total ?? ($svcTotal + $medTotal);
    $statusClass = match($invoice->status ?? 'pending') {
        'paid'    => 's-paid',
        'partial' => 's-partial',
        'void'    => 's-void',
        default   => 's-pending',
    };
    $statusLabel = match($invoice->status ?? 'pending') {
        'paid'    => '✅ PAID',
        'partial' => '💳 PARTIAL',
        'void'    => '❌ VOID',
        default   => '⏳ PENDING',
    };
@endphp

{{-- Paid stamp --}}
@if(($invoice->status ?? '') === 'paid')
<div class="stamp">PAID</div>
@endif

{{-- Header --}}
<div class="inv-hd">
    <div class="clinic-logo">⚕</div>
    <div>
        <div class="clinic-name">{{ $patient?->clinic?->name ?? 'MediFlow Clinic' }}</div>
        <div style="font-size:9pt;color:#888">Official Invoice / វិក្កយបត្រផ្លូវការ</div>
    </div>
    <div style="margin-left:auto;text-align:right">
        <div class="inv-title-badge">🧾 Invoice</div>
        <div style="margin-top:4px">
            <span class="status-badge {{ $statusClass }}">{{ $statusLabel }}</span>
        </div>
    </div>
</div>

{{-- Meta --}}
<div class="inv-meta">
    <div class="meta-field">
        <div class="lbl">Invoice No.</div>
        <div class="val" style="font-family:monospace;color:#00838f">{{ $invoice->code }}</div>
    </div>
    <div class="meta-field">
        <div class="lbl">Date</div>
        <div class="val">{{ $invoice->invoice_date?->format('d/m/Y') ?? now()->format('d/m/Y') }}</div>
    </div>
    <div class="meta-field">
        <div class="lbl">Payment Type</div>
        <div class="val">{{ $invoice->payment_type }}</div>
    </div>
    <div class="meta-field">
        <div class="lbl">Cashier</div>
        <div class="val">{{ $invoice->cashier ?? '—' }}</div>
    </div>
    <div class="meta-field">
        <div class="lbl">Visit Code</div>
        <div class="val" style="font-family:monospace">{{ $invoice->visit_code }}</div>
    </div>
    <div class="meta-field">
        <div class="lbl">Printed</div>
        <div class="val">{{ now()->format('d/m/Y H:i') }}</div>
    </div>
</div>

{{-- Patient --}}
<div class="pt-box">
    <div><div class="pt-lbl">Patient</div><div class="pt-val">{{ $patient?->surname }}, {{ $patient?->name }}</div></div>
    <div><div class="pt-lbl">Code</div><div class="pt-val" style="font-family:monospace">{{ $invoice->patient_code }}</div></div>
    <div><div class="pt-lbl">Phone</div><div class="pt-val">{{ $patient?->phone ?? '—' }}</div></div>
</div>

{{-- Services --}}
@if($invoice->services->isNotEmpty())
<div class="sec-title sec-svc">📋 Services / Procedures</div>
<table class="items-table">
    <thead>
        <tr>
            <th style="width:5%">#</th>
            <th style="width:42%">Service</th>
            <th style="width:22%">Category</th>
            <th style="width:18%" class="num">Amount (KHR)</th>
            <th style="width:13%">Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($invoice->services as $i => $svc)
        <tr>
            <td style="text-align:center;color:#888">{{ $i+1 }}</td>
            <td style="font-weight:600">{{ $svc->service_name }}</td>
            <td style="color:#666">{{ $svc->service_category ?? '—' }}</td>
            <td class="num">{{ number_format($svc->price, 0, '.', ',') }}</td>
            <td>
                <span class="paid-chip {{ $svc->paid > 0 ? 'paid-yes' : 'paid-no' }}">
                    {{ $svc->paid > 0 ? 'Paid' : 'Pending' }}
                </span>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif

{{-- Medicines --}}
@if($invoice->medications->isNotEmpty())
<div class="sec-title sec-med">💊 Medicines / Pharmacy</div>
<table class="items-table">
    <thead>
        <tr>
            <th style="width:5%">#</th>
            <th style="width:38%">Medicine</th>
            <th style="width:10%">Qty</th>
            <th style="width:16%">Unit Price</th>
            <th style="width:18%" class="num">Total (KHR)</th>
            <th style="width:13%">Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($invoice->medications as $i => $med)
        <tr>
            <td style="text-align:center;color:#888">{{ $i+1 }}</td>
            <td style="font-weight:600">{{ $med->medicine_name }}</td>
            <td style="text-align:center">{{ $med->quantity }}</td>
            <td class="num">{{ number_format($med->price, 0, '.', ',') }}</td>
            <td class="num">{{ number_format($med->payment ?? ($med->price * $med->quantity), 0, '.', ',') }}</td>
            <td>
                <span class="paid-chip {{ $med->paid > 0 ? 'paid-yes' : 'paid-no' }}">
                    {{ $med->paid > 0 ? 'Paid' : 'Pending' }}
                </span>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif

{{-- Total --}}
<div class="total-box">
    @if($invoice->services->isNotEmpty() && $invoice->medications->isNotEmpty())
    <div class="total-row"><span>Services subtotal</span><span>{{ number_format($svcTotal,0,'.',',') }} KHR</span></div>
    <div class="total-row"><span>Medicines subtotal</span><span>{{ number_format($medTotal,0,'.',',') }} KHR</span></div>
    @endif
    <div class="total-row total-grand">
        <span>TOTAL</span>
        <span>{{ number_format($grandTotal,0,'.',',') }} KHR</span>
    </div>
</div>

{{-- Signatures --}}
<div class="sig-row">
    <div class="sig-box">
        <div class="sig-line"></div>
        <div class="sig-label">Cashier Signature</div>
        <div class="sig-label">{{ $invoice->cashier ?? '________________' }}</div>
    </div>
    <div class="sig-box">
        <div class="sig-line"></div>
        <div class="sig-label">Patient / Guardian</div>
    </div>
    <div class="sig-box">
        <div class="sig-line"></div>
        <div class="sig-label">Authorized Officer</div>
    </div>
</div>

{{-- Footer --}}
<div class="inv-footer">
    <div>MediFlow EMR — {{ $patient?->clinic?->name ?? '' }}</div>
    <div>Thank you for your trust / សូមអរគុណចំពោះការជឿទុកចិត្ត</div>
    <div>Page 1 of 1</div>
</div>

</div>
</body>
</html>
