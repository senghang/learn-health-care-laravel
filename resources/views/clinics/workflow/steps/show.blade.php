@extends('clinics.layout.app')
@section('title', $visit->code . ' — Clinical Summary')

@section('content')

@php
    $s = $summary;
    $patient = $s['patient'];
    $triage  = $s['triage'];
    $vitals  = $s['vitals'];
    $soap    = $s['soap'];
    $colors  = ['#4154f1','#2eca6a','#ff771d','#e74c3c','#9b59b6','#00bcd4','#f39c12','#1abc9c'];
    $color   = $colors[abs(crc32($visit->code)) % count($colors)];
@endphp

<x-page-header
    title="{{ $visit->surname }}, {{ $visit->name }}"
    subtitle="Visit {{ $visit->code }}"
    :breadcrumbs="[
        ['label' => 'ដើម',  'url' => url('/')],
        ['label' => 'Visits', 'url' => route('visits.index')],
        ['label' => $visit->code],
    ]"
>
    <a href="{{ route('visits.index') }}" class="btn btn-outline-primary btn-sm">
        <i class="bi bi-arrow-left"></i> ត្រឡប់
    </a>
    @if($visit->isActive())
    <a href="{{ url('/workflow/' . $visit->code) }}" class="btn btn-primary btn-sm">
        <i class="bi bi-pencil-fill"></i> Continue Workflow
    </a>
    @endif
</x-page-header>

<div class="row g-3">

{{-- ══════════════════════════════════════════════════════════════════════════
     LEFT: Visit Header + Patient Info
     ══════════════════════════════════════════════════════════════════════════ --}}
<div class="col-12 col-lg-4">

  {{-- Visit header card --}}
  <div class="card-emr mb-3">
    <div class="card-bd" style="padding:18px">
      <div style="display:flex;align-items:center;gap:12px;margin-bottom:14px">
        <div style="width:48px;height:48px;border-radius:14px;background:linear-gradient(135deg,{{ $visit->visit_type === 'IPD' ? '#ff771d' : '#4154f1' }},{{ $visit->visit_type === 'IPD' ? '#e65a00' : '#717ff5' }});color:#fff;display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0">
          <i class="bi bi-{{ $visit->visit_type === 'IPD' ? 'bed-fill' : 'clipboard2-pulse-fill' }}"></i>
        </div>
        <div>
          <div style="font-size:16px;font-weight:800;color:#1a1f36">{{ $visit->code }}</div>
          <div style="font-size:11px;color:#aaa">{{ $visit->admitted_at?->format('d/m/Y H:i') }}</div>
        </div>
      </div>

      <div style="display:flex;flex-wrap:wrap;gap:6px;margin-bottom:12px">
        <span class="badge-s {{ $visit->visit_type === 'IPD' ? 'b-ipd' : 'b-opd' }}">{{ $visit->visit_type }}</span>
        @if($visit->isActive())
          <span class="badge-s b-active"><i class="bi bi-circle-fill" style="font-size:7px"></i> Active</span>
        @else
          <span class="badge-s b-done">Discharged</span>
        @endif
        @if($visit->priority)
        @php $pc = ['Emergency'=>'#e74c3c','Urgent'=>'#ff771d','Standard'=>'#3498db','Low'=>'#95a5a6'][$visit->priority] ?? '#999'; @endphp
        <span class="badge-s" style="background:{{ $pc }}15;color:{{ $pc }}">{{ $visit->priority }}</span>
        @endif
        @if($visit->admission_type)
        <span class="badge-s" style="background:#e6e9f0;color:#666">{{ $visit->admission_type }}</span>
        @endif
      </div>

      @php
        $infoRows = [
          ['bi-person-fill',    'Patient',     $patient ? ($patient->surname . ', ' . $patient->name) : '—'],
          ['bi-tag',            'Patient Code', $visit->patient_code],
          ['bi-calendar-check', 'Admitted',     $visit->admitted_at?->format('d/m/Y H:i')],
          ['bi-calendar-x',    'Discharged',   $visit->discharged_at?->format('d/m/Y H:i') ?? '—'],
          ['bi-arrow-right',   'Discharge Type',$visit->discharge_type ?? '—'],
          ['bi-check2-circle', 'Outcome',       $visit->visit_outcome ?? '—'],
          ['bi-calendar-plus', 'Follow-up',     $visit->followup_at?->format('d/m/Y') ?? '—'],
        ];
      @endphp
      @foreach($infoRows as [$icon, $label, $val])
      <div style="display:flex;align-items:center;gap:8px;padding:6px 0;border-bottom:1px solid #f8f9ff;font-size:12px">
        <i class="bi {{ $icon }}" style="width:14px;color:#aaa;font-size:11px"></i>
        <span style="color:#999;min-width:90px">{{ $label }}</span>
        <span style="color:{{ $val === '—' ? '#ccc' : '#222' }};font-weight:{{ $val === '—' ? '400' : '600' }}">{{ $val }}</span>
      </div>
      @endforeach

      {{-- Progress bar --}}
      <div style="margin-top:12px">
        <div style="display:flex;justify-content:space-between;font-size:10px;color:#aaa;margin-bottom:4px">
          <span>Progress</span>
          <span>{{ count($s['visit']['steps_done']) }}/10 steps</span>
        </div>
        <div style="height:6px;background:#e6e9f0;border-radius:4px;overflow:hidden">
          <div style="height:100%;width:{{ $s['visit']['progress'] }}%;background:linear-gradient(90deg,#4154f1,#2eca6a);border-radius:4px"></div>
        </div>
      </div>
    </div>
  </div>

  {{-- Patient card (compact) --}}
  @if($patient)
  <div class="card-emr mb-3">
    <div class="card-hd">
      <div class="card-hd-title"><i class="bi bi-person-fill" style="color:#4154f1"></i> អ្នកជំងឺ</div>
      <a href="{{ url('/patients/' . $patient->code) }}" class="btn btn-sm btn-outline-primary" style="font-size:10px">Profile</a>
    </div>
    <div class="card-bd" style="font-size:12px;line-height:1.9;color:#555">
      <div><strong>{{ $patient->surname }}, {{ $patient->name }}</strong></div>
      <div style="color:#aaa;font-family:monospace;font-size:11px">{{ $patient->code }}</div>
      @if($patient->sex) <div>{{ $patient->sex === 'M' ? '♂ Male' : '♀ Female' }} @if($patient->age) · {{ $patient->age }}y @endif</div> @endif
      @if($patient->phone) <div><i class="bi bi-telephone" style="color:#bbb"></i> {{ $patient->phone }}</div> @endif
    </div>
  </div>
  @endif

</div>{{-- /col-lg-4 --}}

{{-- ══════════════════════════════════════════════════════════════════════════
     RIGHT: Clinical Data (all steps)
     ══════════════════════════════════════════════════════════════════════════ --}}
<div class="col-12 col-lg-8">

  {{-- ── 1. Triage ─────────────────────────────────────────────────────────── --}}
  @if($triage)
  <div class="card-emr mb-3">
    <div class="card-hd">
      <div class="card-hd-title"><span style="margin-right:6px">🩺</span> Triage</div>
    </div>
    <div class="card-bd">
      @if($triage['chief_complaint'])
      <div style="background:#f6f8fa;border-radius:8px;padding:10px 14px;margin-bottom:10px;font-size:13px;border-left:3px solid #4154f1">
        <div style="font-size:10px;color:#aaa;margin-bottom:2px">Chief Complaint</div>
        {{ $triage['chief_complaint'] }}
      </div>
      @endif
      <div style="display:flex;flex-wrap:wrap;gap:12px;font-size:12px">
        @if($triage['height']) <div><span style="color:#aaa">Height:</span> <strong>{{ $triage['height'] }} cm</strong></div> @endif
        @if($triage['weight']) <div><span style="color:#aaa">Weight:</span> <strong>{{ $triage['weight'] }} kg</strong></div> @endif
        @if($triage['bmi']) <div><span style="color:#aaa">BMI:</span> <strong>{{ $triage['bmi'] }}</strong></div> @endif
        @if($triage['triage_level']) <div><span style="color:#aaa">Level:</span> <strong>{{ $triage['triage_level'] }}</strong></div> @endif
      </div>
    </div>
  </div>
  @endif

  {{-- ── 2. Vital Signs ───────────────────────────────────────────────────── --}}
  @if(!empty($vitals) && count(array_filter($vitals, fn($v, $k) => !in_array($k, ['recorded_at','recorded_by']), ARRAY_FILTER_USE_BOTH)) > 0)
  <div class="card-emr mb-3">
    <div class="card-hd">
      <div class="card-hd-title"><span style="margin-right:6px">❤️</span> Vital Signs</div>
      @if(isset($vitals['recorded_at']))
      <span style="font-size:10px;color:#aaa">{{ \Carbon\Carbon::parse($vitals['recorded_at'])->format('d/m/Y H:i') }}</span>
      @endif
    </div>
    <div class="card-bd">
      @php
        $vitalLabels = [
          'temperature' => ['T°', '°C', '#e74c3c'],
          'heart_rate' => ['HR', 'bpm', '#e91e8c'],
          'respiratory_rate' => ['RR', '/min', '#ff771d'],
          'blood_pressure_systolic' => ['SBP', 'mmHg', '#9b59b6'],
          'blood_pressure_diastolic' => ['DBP', 'mmHg', '#9b59b6'],
          'oxygen_saturation' => ['SpO₂', '%', '#2eca6a'],
          'blood_glucose' => ['Glucose', 'mmol/L', '#3498db'],
        ];
      @endphp
      <div style="display:flex;flex-wrap:wrap;gap:8px">
        @foreach($vitalLabels as $key => [$label, $unit, $clr])
          @if(isset($vitals[$key]) && $vitals[$key] !== null)
          <div style="background:#f6f8fa;border-radius:10px;padding:10px 14px;min-width:90px;text-align:center;border:1px solid #e6e9f0">
            <div style="font-size:20px;font-weight:800;color:{{ $clr }}">{{ $vitals[$key] }}</div>
            <div style="font-size:10px;color:#aaa">{{ $label }} <span style="font-size:9px">({{ $unit }})</span></div>
          </div>
          @endif
        @endforeach
      </div>
    </div>
  </div>
  @endif

  {{-- ── 3. SOAP Notes ─────────────────────────────────────────────────────── --}}
  @if($soap)
  <div class="card-emr mb-3">
    <div class="card-hd">
      <div class="card-hd-title"><span style="margin-right:6px">📝</span> SOAP Notes</div>
    </div>
    <div class="card-bd">
      @php
        $soapCards = [
          ['S', 'Subjective', '#4154f1', $soap['subjective']],
          ['O', 'Objective',  '#2eca6a', $soap['objective']],
          ['A', 'Assessment', '#ff771d', $soap['assessment']],
          ['E', 'Evaluation', '#9b59b6', $soap['evaluation']],
          ['P', 'Plan',       '#e74c3c', $soap['plan']],
        ];
      @endphp
      @foreach($soapCards as [$letter, $label, $clr, $text])
        @if($text)
        <div style="display:flex;gap:10px;margin-bottom:10px">
          <div style="width:30px;height:30px;border-radius:8px;background:{{ $clr }};color:#fff;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:14px;flex-shrink:0">{{ $letter }}</div>
          <div style="flex:1">
            <div style="font-size:10px;color:#aaa;margin-bottom:2px">{{ $label }}</div>
            <div style="font-size:12.5px;color:#333;line-height:1.6">{{ $text }}</div>
          </div>
        </div>
        @endif
      @endforeach
    </div>
  </div>
  @endif

  {{-- ── 4. Diagnoses ──────────────────────────────────────────────────────── --}}
  @if($s['diagnoses']->isNotEmpty())
  <div class="card-emr mb-3">
    <div class="card-hd">
      <div class="card-hd-title"><span style="margin-right:6px">🎯</span> Diagnoses</div>
      <span style="font-size:11px;color:#aaa">{{ $s['diagnoses']->count() }}</span>
    </div>
    <div class="card-bd" style="padding:0">
      @foreach($s['diagnoses'] as $diag)
      <div style="display:flex;align-items:flex-start;gap:10px;padding:10px 16px;border-bottom:1px solid #f8f9ff">
        <span class="badge-s {{ $diag['type'] === 'Primary' ? 'b-opd' : '' }}" style="flex-shrink:0;margin-top:2px;{{ $diag['type'] !== 'Primary' ? 'background:#e6e9f0;color:#666' : '' }}">
          {{ $diag['type'] }}
        </span>
        <div style="flex:1">
          <div style="font-size:12.5px;font-weight:700;color:#1a1f36">
            {{ $diag['name'] }}
            @if($diag['code']) <code style="font-size:10px;color:#9b59b6;margin-left:4px">{{ $diag['code'] }}</code> @endif
          </div>
          @if($diag['description'])
          <div style="font-size:11px;color:#888;margin-top:2px">{{ $diag['description'] }}</div>
          @endif
        </div>
      </div>
      @endforeach
    </div>
  </div>
  @endif

  {{-- ── 5. Prescriptions ──────────────────────────────────────────────────── --}}
  @if($s['prescriptions']->isNotEmpty())
  <div class="card-emr mb-3">
    <div class="card-hd">
      <div class="card-hd-title"><span style="margin-right:6px">💊</span> Prescriptions</div>
    </div>
    <div class="card-bd" style="padding:0">
      @foreach($s['prescriptions'] as $rx)
      <div style="padding:12px 16px;border-bottom:1px solid #e6e9f0">
        <div style="display:flex;justify-content:space-between;margin-bottom:8px">
          <code style="font-size:11px;color:#4154f1">{{ $rx['code'] }}</code>
          <span style="font-size:10px;color:#aaa">{{ $rx['prescribed_by'] }} · {{ $rx['prescribed_at']?->format('d/m H:i') ?? '' }}</span>
        </div>
        @if($rx['medications']->isNotEmpty())
        <div class="table-responsive">
          <table style="width:100%;font-size:11.5px;border-collapse:collapse">
            <thead>
              <tr style="background:#f6f8fa;color:#888;font-size:10px;text-transform:uppercase">
                <th style="padding:6px 8px;text-align:left">Medicine</th>
                <th style="padding:6px 4px;text-align:center">Morning</th>
                <th style="padding:6px 4px;text-align:center">Noon</th>
                <th style="padding:6px 4px;text-align:center">Eve</th>
                <th style="padding:6px 4px;text-align:center">Night</th>
                <th style="padding:6px 4px;text-align:center">Days</th>
              </tr>
            </thead>
            <tbody>
              @foreach($rx['medications'] as $med)
              <tr style="border-bottom:1px solid #f8f9ff">
                <td style="padding:6px 8px;font-weight:600;color:#1a1f36">
                  {{ $med['name'] }}
                  @if($med['strength']) <span style="color:#aaa;font-weight:400">{{ $med['strength'] }}</span> @endif
                </td>
                <td style="text-align:center;color:#888">{{ $med['morning'] ?: '—' }}</td>
                <td style="text-align:center;color:#888">{{ $med['afternoon'] ?: '—' }}</td>
                <td style="text-align:center;color:#888">{{ $med['evening'] ?: '—' }}</td>
                <td style="text-align:center;color:#888">{{ $med['night'] ?: '—' }}</td>
                <td style="text-align:center;font-weight:700;color:#4154f1">{{ $med['days'] ?: '—' }}</td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        @endif
      </div>
      @endforeach
    </div>
  </div>
  @endif

  {{-- ── 6. Lab Results ────────────────────────────────────────────────────── --}}
  @if($s['labs']->isNotEmpty())
  <div class="card-emr mb-3">
    <div class="card-hd">
      <div class="card-hd-title"><span style="margin-right:6px">🔬</span> Laboratory</div>
    </div>
    <div class="card-bd" style="padding:0">
      @foreach($s['labs'] as $lab)
      <div style="padding:10px 16px;border-bottom:1px solid #e6e9f0">
        <div style="font-size:11px;margin-bottom:6px">
          <code style="color:#4154f1">{{ $lab['code'] }}</code>
          <span style="color:#aaa;margin-left:6px">{{ $lab['category'] }}</span>
        </div>
        @foreach($lab['results'] as $r)
        <div style="display:flex;gap:8px;font-size:12px;padding:3px 0;border-bottom:1px dotted #e6e9f0">
          <span style="color:#666;min-width:120px">{{ $r['name'] }}</span>
          <span style="font-weight:700;color:{{ ($r['flag'] ?? '') === 'H' || ($r['flag'] ?? '') === 'L' ? '#e74c3c' : '#1a1f36' }}">{{ $r['result'] }}</span>
          <span style="color:#aaa">{{ $r['unit'] }}</span>
          @if($r['range']) <span style="color:#bbb;font-size:10px">({{ $r['range'] }})</span> @endif
          @if($r['flag']) <span style="font-size:9px;font-weight:800;color:#e74c3c;background:#fce4ec;padding:0 4px;border-radius:4px">{{ $r['flag'] }}</span> @endif
        </div>
        @endforeach
      </div>
      @endforeach
    </div>
  </div>
  @endif

  {{-- ── 7. Billing Summary ────────────────────────────────────────────────── --}}
  @if($s['invoices']->isNotEmpty())
  <div class="card-emr mb-3">
    <div class="card-hd">
      <div class="card-hd-title"><span style="margin-right:6px">🧾</span> Billing</div>
    </div>
    <div class="card-bd" style="padding:0">
      @foreach($s['invoices'] as $inv)
      <div style="display:flex;align-items:center;gap:12px;padding:10px 16px;border-bottom:1px solid #f8f9ff">
        <code style="font-size:11px;color:#4154f1">{{ $inv['code'] }}</code>
        <div style="flex:1">
          <span class="badge-s" style="background:#e6e9f0;color:#666">{{ $inv['payment_type'] }}</span>
          <span class="badge-s {{ $inv['status'] === 'paid' ? 'b-done' : 'b-active' }}">{{ ucfirst($inv['status']) }}</span>
        </div>
        <div style="text-align:right">
          <div style="font-size:14px;font-weight:800;color:#1a1f36">{{ number_format($inv['total']) }} ៛</div>
          @if($inv['balance'] > 0)
          <div style="font-size:10px;color:#e74c3c">Balance: {{ number_format($inv['balance']) }} ៛</div>
          @endif
        </div>
      </div>
      @endforeach
    </div>
  </div>
  @endif

  {{-- ── Clinical Summary (if exists) ──────────────────────────────────────── --}}
  @if($visit->clinical_summary)
  <div class="card-emr mb-3">
    <div class="card-hd">
      <div class="card-hd-title"><i class="bi bi-file-text-fill" style="color:#3498db"></i> Clinical Summary</div>
    </div>
    <div class="card-bd" style="font-size:13px;line-height:1.8;color:#333">
      {!! nl2br(e($visit->clinical_summary)) !!}
    </div>
  </div>
  @endif

</div>{{-- /col-lg-8 --}}
</div>{{-- /row --}}

@endsection
