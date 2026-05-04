<!DOCTYPE html>
<html lang="km">
<head>
<meta charset="UTF-8"/>
<title>Prescription {{ $rx->code }}</title>
<style>
*{margin:0;padding:0;box-sizing:border-box}
@page{size:A4;margin:20mm 18mm}
body{font-family:'Hanuman','Khmer OS',Arial,sans-serif;font-size:12pt;color:#111;background:#fff}
.page{max-width:210mm;margin:0 auto;padding:12mm 14mm;background:#fff}

/* Header */
.clinic-hd{display:flex;align-items:center;gap:16px;padding-bottom:10px;border-bottom:2.5px solid #e91e8c;margin-bottom:10px}
.clinic-logo{width:56px;height:56px;border-radius:50%;background:#fce7f3;display:flex;align-items:center;justify-content:center;font-size:28px;flex-shrink:0}
.clinic-name{font-size:16pt;font-weight:900;color:#1a1f36}
.clinic-sub{font-size:9pt;color:#888;margin-top:2px}
.rx-badge{background:#e91e8c;color:#fff;padding:3px 16px;border-radius:20px;font-size:11pt;font-weight:700;margin-left:auto;flex-shrink:0}

/* Patient box */
.patient-box{background:#f9f0ff;border:1.5px solid #e91e8c33;border-radius:8px;padding:10px 14px;margin-bottom:12px;display:grid;grid-template-columns:1fr 1fr 1fr;gap:6px}
.pt-field{font-size:10pt}
.pt-label{font-size:8pt;color:#888;text-transform:uppercase;letter-spacing:.4px;margin-bottom:1px}
.pt-val{font-weight:700;color:#1a1f36}

/* Section title */
.sec-title{font-size:10pt;font-weight:800;text-transform:uppercase;letter-spacing:.5px;color:#e91e8c;padding:6px 0 4px;border-bottom:1px solid #fce7f3;margin-bottom:8px;display:flex;align-items:center;gap:6px}

/* Med table */
.med-table{width:100%;border-collapse:collapse;margin-bottom:14px;font-size:10.5pt}
.med-table th{background:#fce7f3;color:#1a1f36;font-weight:800;font-size:9pt;padding:6px 8px;border:1px solid #e91e8c44;text-align:left}
.med-table td{padding:7px 8px;border:1px solid #e6eaf5;vertical-align:top}
.med-table tr:nth-child(even) td{background:#fdf4ff}
.med-name{font-weight:700;font-size:11pt;color:#1a1f36}
.med-detail{font-size:9pt;color:#666;margin-top:2px}
.dose-row{display:flex;gap:8px;flex-wrap:wrap;margin-top:4px}
.dose-chip{background:#e91e8c11;color:#e91e8c;border:1px solid #e91e8c44;border-radius:4px;padding:1px 6px;font-size:8.5pt;font-weight:700}

/* Signature */
.sig-row{display:grid;grid-template-columns:1fr 1fr;gap:40px;margin-top:20px;padding-top:16px;border-top:1px dashed #ddd}
.sig-box{text-align:center}
.sig-line{border-bottom:1px solid #aaa;margin-bottom:4px;height:40px}
.sig-label{font-size:9pt;color:#888}

/* Footer */
.rx-footer{margin-top:12px;padding-top:8px;border-top:1px solid #f0e7ff;display:flex;justify-content:space-between;align-items:center;font-size:8.5pt;color:#aaa}
.footer-stamp{background:#fce7f3;color:#e91e8c;border:1px solid #e91e8c44;border-radius:4px;padding:2px 10px;font-weight:700;font-size:9pt}

@media print{
    body{print-color-adjust:exact;-webkit-print-color-adjust:exact}
    .no-print{display:none!important}
}
</style>
</head>
<body>
<script>window.addEventListener('load',function(){window.print()})</script>

<div class="page">

{{-- Print button (no-print) --}}
<div class="no-print" style="text-align:right;margin-bottom:8px">
    <button onclick="window.print()" style="background:#e91e8c;color:#fff;border:none;padding:7px 20px;border-radius:6px;font-size:13px;cursor:pointer;font-weight:700">
        🖨 Print
    </button>
</div>

{{-- Clinic Header --}}
<div class="clinic-hd">
    <div class="clinic-logo">⚕</div>
    <div>
        <div class="clinic-name">{{ $rx->visit?->patient?->clinic?->name ?? 'MediFlow Clinic' }}</div>
        <div class="clinic-sub">Clinical Prescription / វេជ្ជបញ្ជា</div>
    </div>
    <div class="rx-badge">💊 Rx</div>
</div>

{{-- Prescription meta --}}
<div style="display:flex;justify-content:space-between;margin-bottom:10px;font-size:10pt">
    <div>
        <span style="color:#888">Rx Code: </span>
        <strong style="font-family:monospace;color:#e91e8c">{{ $rx->code }}</strong>
    </div>
    <div>
        <span style="color:#888">Date: </span>
        <strong>{{ $rx->prescribed_at?->format('d/m/Y H:i') ?? now()->format('d/m/Y H:i') }}</strong>
    </div>
    <div>
        <span style="color:#888">Visit: </span>
        <strong style="font-family:monospace">{{ $rx->visit_code }}</strong>
    </div>
</div>

{{-- Patient --}}
@php $patient = $rx->visit?->patient; @endphp
<div class="patient-box">
    <div class="pt-field">
        <div class="pt-label">Patient Name / ឈ្មោះ</div>
        <div class="pt-val">{{ $patient?->surname }}, {{ $patient?->name }}</div>
    </div>
    <div class="pt-field">
        <div class="pt-label">Code / លេខ</div>
        <div class="pt-val" style="font-family:monospace">{{ $rx->visit?->patient_code }}</div>
    </div>
    <div class="pt-field">
        <div class="pt-label">Visit / ករណី</div>
        <div class="pt-val" style="font-family:monospace">{{ $rx->visit_code }}</div>
    </div>
    <div class="pt-field">
        <div class="pt-label">Sex / ភេទ</div>
        <div class="pt-val">{{ $patient?->gender === 'M' ? 'ប្រុស / Male' : 'ស្រី / Female' }}</div>
    </div>
    <div class="pt-field">
        <div class="pt-label">DOB / ថ្ងៃខែ</div>
        <div class="pt-val">{{ $patient?->birthdate?->format('d/m/Y') ?? '—' }}</div>
    </div>
    <div class="pt-field">
        <div class="pt-label">Phone / ទូរស័ព្ទ</div>
        <div class="pt-val">{{ $patient?->phone ?? '—' }}</div>
    </div>
</div>

{{-- Medications --}}
<div class="sec-title">💊 Medications / ថ្នាំ</div>

<table class="med-table">
    <thead>
        <tr>
            <th style="width:5%">#</th>
            <th style="width:32%">Medicine / ថ្នាំ</th>
            <th style="width:12%">Strength</th>
            <th style="width:10%">Form</th>
            <th style="width:26%">Dosing / កម្រិត</th>
            <th style="width:10%">Days</th>
            <th style="width:5%">Unit</th>
        </tr>
    </thead>
    <tbody>
        @foreach($rx->medications as $i => $med)
        <tr>
            <td style="text-align:center;color:#888">{{ $i+1 }}</td>
            <td>
                <div class="med-name">{{ $med->medicine_name }}</div>
                @if($med->method)<div class="med-detail">Route: {{ $med->method }}</div>@endif
                @if($med->note)<div class="med-detail" style="color:#e91e8c">⚠ {{ $med->note }}</div>@endif
            </td>
            <td style="font-weight:700">{{ $med->strength ?? '—' }}</td>
            <td>{{ $med->form ?? '—' }}</td>
            <td>
                <div class="dose-row">
                    @if($med->morning)   <span class="dose-chip">ព្រឹក {{ $med->morning }}</span>@endif
                    @if($med->afternoon) <span class="dose-chip">ថ្ងៃ {{ $med->afternoon }}</span>@endif
                    @if($med->evening)   <span class="dose-chip">ល្ងាច {{ $med->evening }}</span>@endif
                    @if($med->night)     <span class="dose-chip">យប់ {{ $med->night }}</span>@endif
                    @if($med->interval)  <span class="dose-chip">{{ $med->interval }}</span>@endif
                </div>
            </td>
            <td style="text-align:center;font-weight:700">{{ $med->days ?? '—' }}</td>
            <td style="text-align:center">{{ $med->unit ?? '—' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

{{-- Instructions --}}
<div style="background:#fff8e1;border-left:3px solid #f59e0b;padding:8px 12px;font-size:10pt;margin-bottom:14px;border-radius:0 6px 6px 0">
    <strong>⚠ Instructions:</strong> Take medications as prescribed. Complete the full course.
    Do not share medications. Contact the clinic if symptoms worsen.
</div>

{{-- Signatures --}}
<div class="sig-row">
    <div class="sig-box">
        <div class="sig-line"></div>
        <div class="sig-label">Prescribed by: {{ $rx->prescribed_by ?? '——————————' }}</div>
        <div class="sig-label" style="margin-top:2px">Date: {{ now()->format('d/m/Y') }}</div>
    </div>
    <div class="sig-box">
        <div class="sig-line"></div>
        <div class="sig-label">Patient / Guardian Signature</div>
        <div class="sig-label" style="margin-top:2px">Date: _______________</div>
    </div>
</div>

{{-- Footer --}}
<div class="rx-footer">
    <div>MediFlow EMR — Printed: {{ now()->format('d/m/Y H:i') }}</div>
    <div class="footer-stamp">ORIGINAL</div>
    <div>Page 1 of 1</div>
</div>

</div>
</body>
</html>
