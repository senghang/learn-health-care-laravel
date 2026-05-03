@extends('clinics.layout.app')
@section('title', 'Visit ' . $visit->code)

@section('content')

@php
    $s       = $summary;
    $patient = $s['patient'];
    $v       = $s['visit'];
    $triage  = $s['triage'];
    $vitals  = $s['vitals'] ?? [];
    $soap    = $s['soap'] ?? null;
    $dx      = $s['diagnoses'] ?? [];
    $rxList  = $s['prescriptions'] ?? [];
    $labs    = $s['laboratories'] ?? [];
    $imgs    = $s['imageries'] ?? [];
    $invList = $s['invoices'] ?? [];
    $typeColor = $v['type'] === 'IPD' ? '#ff771d' : '#4154f1';
@endphp

<x-page-header
    :title="'Visit: ' . $visit->code"
    subtitle="Completed Visit Summary"
    :breadcrumbs="[
        ['label' => 'ដើម',    'url' => route('dashboard')],
        ['label' => 'Visits', 'url' => route('visits.index')],
        ['label' => $visit->code],
    ]"
>
    @if($patient)
    <a href="{{ route('patients.show', $patient->code) }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-person-fill"></i> Patient Profile
    </a>
    @endif
    <a href="{{ route('print.prescription', $visit->code) }}" class="btn btn-outline-primary btn-sm" target="_blank">
        <i class="bi bi-printer-fill"></i> Print
    </a>
</x-page-header>

{{-- ── Visit Header Banner ────────────────────────────────────────────────── --}}
<div class="card-emr mb-3">
    <div class="card-bd" style="padding:16px 20px">
        <div class="d-flex flex-wrap gap-3 align-items-center">
            <div style="width:52px;height:52px;border-radius:14px;background:linear-gradient(135deg,{{ $typeColor }},{{ $typeColor }}cc);color:#fff;display:flex;align-items:center;justify-content:center;font-size:22px;flex-shrink:0">
                <i class="bi bi-{{ $v['type'] === 'IPD' ? 'bed-fill' : 'person-fill' }}"></i>
            </div>
            <div style="flex:1;min-width:200px">
                <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-bottom:4px">
                    <span style="font-size:17px;font-weight:800;color:#012970;font-family:monospace">{{ $visit->code }}</span>
                    <span class="badge-s {{ $v['type'] === 'IPD' ? 'b-ipd' : 'b-opd' }}">{{ $v['type'] }}</span>
                    <span class="badge-s b-done"><i class="bi bi-check-circle-fill" style="font-size:9px"></i> Completed</span>
                    @if($v['priority'] === 'emergency')
                        <span class="badge-s" style="background:#fce4ec;color:#e74c3c">🚨 Emergency</span>
                    @endif
                </div>
                <div style="font-size:11.5px;color:#94a3b8;display:flex;gap:16px;flex-wrap:wrap">
                    <span><i class="bi bi-calendar3" style="font-size:10px"></i> {{ $v['admitted_at']?->format('d/m/Y H:i') ?? '—' }}</span>
                    @if($v['discharged_at'])
                    <span><i class="bi bi-door-open" style="font-size:10px"></i> {{ $v['discharged_at']->format('d/m/Y H:i') }}</span>
                    @endif
                    @if($v['admission_type'])
                    <span><i class="bi bi-tag-fill" style="font-size:10px"></i> {{ $v['admission_type'] }}</span>
                    @endif
                    <span><i class="bi bi-check2-all" style="font-size:10px"></i> {{ count($v['steps_done']) }}/10 steps</span>
                </div>
            </div>
            @if($patient)
            <div style="text-align:right;flex-shrink:0">
                <div style="font-size:14px;font-weight:700;color:#012970">{{ $patient->surname }}, {{ $patient->name }}</div>
                <div style="font-size:11px;color:#94a3b8;font-family:monospace">{{ $patient->code }}</div>
                @if($patient->birthdate)
                <div style="font-size:11px;color:#94a3b8">{{ $patient->birthdate->age }}y · {{ $patient->sex === 'M' ? '♂' : '♀' }}</div>
                @endif
            </div>
            @endif
        </div>
    </div>
</div>

<div class="row g-3">

{{-- ── LEFT COLUMN ──────────────────────────────────────────────────────────── --}}
<div class="col-12 col-lg-4">

    {{-- Triage --}}
    @if($triage)
    <div class="card-emr mb-3">
        <div class="card-hd">
            <div class="card-hd-title">
                <i class="bi bi-clipboard2-pulse-fill" style="color:#e91e8c"></i>
                ការត្រួតពិនិត្យ <small style="font-weight:400;color:#aaa">/ Triage</small>
            </div>
        </div>
        <div class="card-bd" style="padding:0">
            @php
            $triageRows = [
                ['bi-chat-text-fill','Chief Complaint', $triage['chief_complaint'] ?? '—'],
                ['bi-speedometer2','Triage Level',    $triage['triage_level'] ?? '—'],
                ['bi-arrows-vertical','Height',       ($triage['height'] ?? null) ? $triage['height'].' cm' : '—'],
                ['bi-life-preserver','Weight',        ($triage['weight'] ?? null) ? $triage['weight'].' kg' : '—'],
                ['bi-calculator','BMI',               ($triage['bmi'] ?? null) ? number_format($triage['bmi'],1) : '—'],
            ];
            @endphp
            @foreach($triageRows as [$ico, $lbl, $val])
            <div style="display:flex;align-items:center;gap:10px;padding:9px 16px;border-bottom:1px solid #f8f9ff">
                <i class="bi {{ $ico }}" style="width:16px;text-align:center;color:#aaa;font-size:12px;flex-shrink:0"></i>
                <div style="flex:1">
                    <div style="font-size:10px;color:#bbb">{{ $lbl }}</div>
                    <div style="font-size:12.5px;font-weight:{{ $val === '—' ? '400' : '600' }};color:{{ $val === '—' ? '#ccc' : '#222' }}">{{ $val }}</div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Vitals --}}
    @if(!empty($vitals))
    <div class="card-emr mb-3">
        <div class="card-hd">
            <div class="card-hd-title">
                <i class="bi bi-heart-pulse-fill" style="color:#e74c3c"></i>
                សញ្ញាជីវិត <small style="font-weight:400;color:#aaa">/ Vital Signs</small>
            </div>
        </div>
        <div class="card-bd" style="padding:0">
            @foreach($vitals as $key => $val)
            @if($val !== null)
            <div style="display:flex;align-items:center;gap:10px;padding:8px 16px;border-bottom:1px solid #f8f9ff">
                <div style="flex:1">
                    <div style="font-size:10px;color:#bbb;text-transform:capitalize">{{ str_replace('_',' ',$key) }}</div>
                    <div style="font-size:12.5px;font-weight:600;color:#222">{{ $val }}</div>
                </div>
            </div>
            @endif
            @endforeach
        </div>
    </div>
    @endif

</div>{{-- /col-lg-4 --}}

{{-- ── RIGHT COLUMN ─────────────────────────────────────────────────────────── --}}
<div class="col-12 col-lg-8">

    {{-- SOAP --}}
    @if($soap)
    <div class="card-emr mb-3">
        <div class="card-hd">
            <div class="card-hd-title">
                <i class="bi bi-file-earmark-medical-fill" style="color:#4154f1"></i>
                ការពិគ្រោះ <small style="font-weight:400;color:#aaa">/ SOAP Note</small>
            </div>
        </div>
        <div class="card-bd">
            <div class="row g-3">
                @foreach([['S','Subjective',$soap['subjective'] ?? null],['O','Objective',$soap['objective'] ?? null],['A','Assessment',$soap['assessment'] ?? null],['P','Plan',$soap['plan'] ?? null]] as [$letter,$label,$val])
                @if($val)
                <div class="col-12 col-sm-6">
                    <div style="font-size:11px;font-weight:700;color:#4154f1;margin-bottom:3px">{{ $letter }} — {{ $label }}</div>
                    <div style="font-size:12.5px;color:#374151;line-height:1.6;white-space:pre-line">{{ $val }}</div>
                </div>
                @endif
                @endforeach
            </div>
        </div>
    </div>
    @endif

    {{-- Diagnoses --}}
    @if(!empty($dx))
    <div class="card-emr mb-3">
        <div class="card-hd">
            <div class="card-hd-title">
                <i class="bi bi-patch-check-fill" style="color:#9b59b6"></i>
                រោគវិនិច្ឆ័យ <small style="font-weight:400;color:#aaa">/ Diagnoses</small>
            </div>
        </div>
        <div class="card-bd" style="padding:0">
            @foreach($dx as $d)
            <div style="display:flex;align-items:center;gap:10px;padding:10px 16px;border-bottom:1px solid #f8f9ff">
                <div style="width:28px;height:28px;border-radius:8px;background:#f0e8ff;color:#9b59b6;display:flex;align-items:center;justify-content:center;font-size:12px;flex-shrink:0">
                    <i class="bi bi-check2"></i>
                </div>
                <div style="flex:1">
                    <div style="font-size:12.5px;font-weight:600;color:#012970">{{ $d['name'] ?? $d->diagnosis_name ?? '—' }}</div>
                    @if(!empty($d['icd_code'] ?? $d->icd_code ?? null))
                    <div style="font-size:10.5px;color:#aaa;font-family:monospace">{{ $d['icd_code'] ?? $d->icd_code }}</div>
                    @endif
                </div>
                @if(!empty($d['type'] ?? null))
                <span class="badge-s" style="background:#f0e8ff;color:#9b59b6">{{ $d['type'] }}</span>
                @endif
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Prescriptions --}}
    @if(!empty($rxList))
    <div class="card-emr mb-3">
        <div class="card-hd">
            <div class="card-hd-title">
                <i class="bi bi-capsule-fill" style="color:#e91e8c"></i>
                វេជ្ជបញ្ជា <small style="font-weight:400;color:#aaa">/ Prescriptions</small>
            </div>
        </div>
        <div class="card-bd" style="padding:0">
            @foreach($rxList as $rx)
            <a href="{{ route('prescriptions.show', $rx->code) }}"
               style="display:flex;align-items:center;gap:10px;padding:10px 16px;border-bottom:1px solid #f8f9ff;text-decoration:none">
                <div style="flex:1">
                    <div style="font-size:12px;font-weight:700;color:#e91e8c;font-family:monospace">{{ $rx->code }}</div>
                    <div style="font-size:11px;color:#94a3b8">{{ $rx->medications->count() }} medications · {{ $rx->created_at?->format('d/m/Y') }}</div>
                </div>
                @php $ds = $rx->dispensed_status; $dsCol = $ds === 'dispensed' ? ['#e8f8ef','#2eca6a'] : ($ds === 'partial' ? ['#fff3e8','#ff771d'] : ['#f0f2ff','#4154f1']); @endphp
                <span class="badge-s" style="background:{{ $dsCol[0] }};color:{{ $dsCol[1] }}">{{ ucfirst($ds ?? 'pending') }}</span>
                <i class="bi bi-chevron-right" style="color:#ddd;font-size:11px"></i>
            </a>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Lab + Imaging --}}
    @if(!empty($labs) || !empty($imgs))
    <div class="card-emr mb-3">
        <div class="card-hd">
            <div class="card-hd-title">
                <i class="bi bi-eyedropper" style="color:#2eca6a"></i>
                Lab / Imaging
            </div>
        </div>
        <div class="card-bd" style="padding:0">
            @foreach($labs as $lab)
            <a href="{{ route('laboratory.show', $lab->code) }}"
               style="display:flex;align-items:center;gap:10px;padding:10px 16px;border-bottom:1px solid #f8f9ff;text-decoration:none">
                <div style="width:28px;height:28px;border-radius:8px;background:#e8f8ef;color:#2eca6a;display:flex;align-items:center;justify-content:center;font-size:12px;flex-shrink:0">
                    <i class="bi bi-droplet-fill"></i>
                </div>
                <div style="flex:1">
                    <div style="font-size:12px;font-weight:600;color:#012970;font-family:monospace">{{ $lab->code }}</div>
                    <div style="font-size:11px;color:#94a3b8">{{ $lab->category ?? 'Lab' }} · {{ $lab->created_at?->format('d/m/Y') }}</div>
                </div>
                @php $lc = ['pending'=>['#f0f2ff','#4154f1'],'collected'=>['#fff3e8','#ff771d'],'resulted'=>['#e8f8ef','#2eca6a'],'verified'=>['#f0e8ff','#9b59b6']][$lab->status] ?? ['#f5f5f5','#999']; @endphp
                <span class="badge-s" style="background:{{ $lc[0] }};color:{{ $lc[1] }}">{{ ucfirst($lab->status) }}</span>
                <i class="bi bi-chevron-right" style="color:#ddd;font-size:11px"></i>
            </a>
            @endforeach
            @foreach($imgs as $img)
            <a href="{{ route('imagery.show', $img->code) }}"
               style="display:flex;align-items:center;gap:10px;padding:10px 16px;border-bottom:1px solid #f8f9ff;text-decoration:none">
                <div style="width:28px;height:28px;border-radius:8px;background:#fff3e8;color:#ff771d;display:flex;align-items:center;justify-content:center;font-size:12px;flex-shrink:0">
                    <i class="bi bi-camera-fill"></i>
                </div>
                <div style="flex:1">
                    <div style="font-size:12px;font-weight:600;color:#012970;font-family:monospace">{{ $img->code }}</div>
                    <div style="font-size:11px;color:#94a3b8">{{ $img->category ?? 'Imaging' }} · {{ $img->created_at?->format('d/m/Y') }}</div>
                </div>
                <i class="bi bi-chevron-right" style="color:#ddd;font-size:11px"></i>
            </a>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Invoices --}}
    @if(!empty($invList))
    <div class="card-emr mb-3">
        <div class="card-hd">
            <div class="card-hd-title">
                <i class="bi bi-receipt-cutoff" style="color:#9b59b6"></i>
                វិក្កយបត្រ <small style="font-weight:400;color:#aaa">/ Invoices</small>
            </div>
        </div>
        <div class="card-bd" style="padding:0">
            @foreach($invList as $inv)
            <a href="{{ route('invoices.show', $inv->code) }}"
               style="display:flex;align-items:center;gap:10px;padding:10px 16px;border-bottom:1px solid #f8f9ff;text-decoration:none">
                <div style="flex:1">
                    <div style="font-size:12px;font-weight:700;color:#9b59b6;font-family:monospace">{{ $inv->code }}</div>
                    <div style="font-size:11px;color:#94a3b8">{{ $inv->created_at?->format('d/m/Y') }}</div>
                </div>
                @php $ic = ['draft'=>['#f5f5f5','#999'],'pending'=>['#fff3e8','#ff771d'],'partial'=>['#fff8e1','#f59e0b'],'paid'=>['#e8f8ef','#2eca6a'],'voided'=>['#fce4ec','#e74c3c']][$inv->status] ?? ['#f5f5f5','#999']; @endphp
                <span class="badge-s" style="background:{{ $ic[0] }};color:{{ $ic[1] }}">{{ ucfirst($inv->status) }}</span>
                <span style="font-size:13px;font-weight:700;color:#012970">${{ number_format($inv->grand_total,2) }}</span>
                <i class="bi bi-chevron-right" style="color:#ddd;font-size:11px"></i>
            </a>
            @endforeach
        </div>
    </div>
    @endif

    @if(empty($soap) && empty($dx) && empty($rxList) && empty($labs) && empty($imgs) && empty($invList))
    <div class="card-emr">
        <div class="card-bd" style="text-align:center;padding:48px 24px;color:#bbb">
            <div style="font-size:40px;margin-bottom:12px;opacity:.2">🏥</div>
            <div style="font-size:13px;font-weight:600">No clinical data recorded for this visit.</div>
        </div>
    </div>
    @endif

</div>{{-- /col-lg-8 --}}

</div>{{-- /row --}}

@endsection
