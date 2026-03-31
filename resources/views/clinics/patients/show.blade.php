@extends('clinics.layout.app')
@section('title', $patient->surname . ', ' . $patient->name)

@section('content')

@php
    $ageStr = $patient->birthdate
        ? $patient->birthdate->age . 'y (' . $patient->birthdate->format('d/m/Y') . ')'
        : '—';
    $colors    = ['#4154f1','#2eca6a','#ff771d','#e74c3c','#9b59b6','#00bcd4','#f39c12','#1abc9c'];
    $color     = $colors[abs(crc32($patient->code)) % count($colors)];
    $initials  = strtoupper(substr($patient->surname,0,1).substr($patient->name,0,1));
    $lastVisit = $patient->visits->first();
@endphp

<x-page-header
    title="{{ $patient->surname }}, {{ $patient->name }}"
    subtitle="Patient Profile"
    :breadcrumbs="[
        ['label' => 'ដើម',      'url' => url('/')],
        ['label' => 'អ្នកជំងឺ', 'url' => url('/patients')],
        ['label' => $patient->code],
    ]"
>
    <a href="{{ url('/patients/' . $patient->code . '/edit') }}" class="btn btn-outline-primary btn-sm">
        <i class="bi bi-pencil-fill"></i> កែប្រែ
    </a>
    <a href="{{ url('/workflow/create') }}?patient_code={{ $patient->code }}" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg"></i> ការចូលថ្មី
    </a>
</x-page-header>

<div class="row g-3">

{{-- ══════════════════════════════════════════════════════════════════════════
     LEFT: Profile + Address + Contacts + IDs
     ══════════════════════════════════════════════════════════════════════════ --}}
<div class="col-12 col-lg-4">

  {{-- ── Avatar + Core Info ────────────────────────────────────────────────── --}}
  <div class="card-emr mb-3">
    <div class="card-bd" style="text-align:center;padding:24px 18px 16px">
      <div style="width:72px;height:72px;border-radius:18px;background:linear-gradient(135deg,{{ $color }},{{ $color }}cc);color:#fff;font-size:26px;font-weight:800;display:flex;align-items:center;justify-content:center;margin:0 auto 12px">
        {{ $initials }}
      </div>
      <div style="font-size:18px;font-weight:800;color:#012970">{{ $patient->surname }}, {{ $patient->name }}</div>
      <div style="font-size:12px;color:#aaa;margin-top:3px;font-family:monospace">{{ $patient->code }}</div>

      <div style="display:flex;justify-content:center;gap:6px;margin-top:10px;flex-wrap:wrap">
        <span class="badge-s {{ $patient->sex === 'M' ? 'b-opd' : 'b-ipd' }}">
          {{ $patient->sex === 'M' ? '♂ ប្រុស' : '♀ ស្រី' }}
        </span>
        @if(($patient->visits_count ?? $patient->visits->count()) > 0)
        <span class="badge-s" style="background:#eef0fd;color:#4154f1">
          {{ $patient->visits_count ?? $patient->visits->count() }} Visits
        </span>
        @else
        <span class="badge-s" style="background:#e8f8ef;color:#2eca6a">New Patient</span>
        @endif
        @if($patient->spid)
        <span class="badge-s" style="background:#fff3e8;color:#ff771d">SPID</span>
        @endif
        @if($patient->blood_type)
        <span class="badge-s" style="background:#fce4ec;color:#e74c3c">{{ $patient->blood_type }}</span>
        @endif
        <span class="badge-s {{ $patient->status === 'Active' ? 'b-active' : '' }}" style="{{ $patient->status !== 'Active' ? 'background:#f5f5f5;color:#999' : '' }}">
          {{ $patient->status }}
        </span>
      </div>
    </div>

    {{-- Info rows --}}
    <div style="border-top:1px solid #f0f2ff">
      @php
        $rows = [
          ['bi-calendar3',       'ថ្ងៃខែឆ្នាំ / DOB', $ageStr],
          ['bi-telephone-fill',  'ទូរស័ព្ទ / Phone',   $patient->phone ?? '—'],
          ['bi-flag-fill',       'សញ្ជាតិ / Nationality', $patient->nationality ?? '—'],
          ['bi-briefcase-fill',  'មុខរបរ / Occupation', $patient->occupation ?? '—'],
          ['bi-heart-fill',      'អាពាហ៍ / Marital',   $patient->marital_status ?? '—'],
          ['bi-shield-fill',     'SPID',                $patient->spid ?? '—'],
        ];
      @endphp
      @foreach($rows as [$icon, $label, $val])
      <div style="display:flex;align-items:center;gap:10px;padding:9px 16px;border-bottom:1px solid #f8f9ff">
        <i class="bi {{ $icon }}" style="width:16px;text-align:center;color:#aaa;font-size:12px;flex-shrink:0"></i>
        <div style="flex:1;min-width:0">
          <div style="font-size:10px;color:#bbb">{{ $label }}</div>
          <div style="font-size:12.5px;font-weight:{{ $val === '—' ? '400' : '600' }};color:{{ $val === '—' ? '#ccc' : '#222' }}">{{ $val }}</div>
        </div>
      </div>
      @endforeach
    </div>
  </div>

  {{-- ── Address ───────────────────────────────────────────────────────────── --}}
  @if($patient->address)
  <div class="card-emr mb-3">
    <div class="card-hd">
      <div class="card-hd-title">
        <i class="bi bi-geo-alt-fill" style="color:#ff771d"></i>
        អាសយដ្ឋាន <small style="font-weight:400;color:#aaa">/ Address</small>
      </div>
    </div>
    <div class="card-bd" style="font-size:12.5px;line-height:1.8;color:#555">
      @php $addr = $patient->address; @endphp
      @if($addr->house_number || $addr->street_number)
        <div><i class="bi bi-house-fill" style="color:#bbb;width:14px"></i>
          ផ្ទះ{{ $addr->house_number ?? '' }} ផ្លូវ{{ $addr->street_number ?? '' }}
        </div>
      @endif
      @if($addr->village_name)  <div><i class="bi bi-map" style="color:#bbb;width:14px"></i> ភូមិ{{ $addr->village_name }}</div> @endif
      @if($addr->commune_name)  <div><i class="bi bi-signpost" style="color:#bbb;width:14px"></i> ឃុំ{{ $addr->commune_name }}</div> @endif
      @if($addr->district_name) <div><i class="bi bi-building" style="color:#bbb;width:14px"></i> ស្រុក{{ $addr->district_name }}</div> @endif
      @if($addr->province_name) <div><i class="bi bi-geo" style="color:#bbb;width:14px"></i> ខេត្ត{{ $addr->province_name }}</div> @endif
      @if(!$addr->province_name && !$addr->district_name && !$addr->village_name)
        <div style="color:#ccc;font-size:11px">— No address recorded —</div>
      @endif
    </div>
  </div>
  @endif

  {{-- ── ID Cards ──────────────────────────────────────────────────────────── --}}
  @if($patient->identifications->isNotEmpty())
  <div class="card-emr mb-3">
    <div class="card-hd">
      <div class="card-hd-title">
        <i class="bi bi-credit-card-fill" style="color:#9b59b6"></i>
        ប័ណ្ណ <small style="font-weight:400;color:#aaa">/ ID Cards</small>
      </div>
    </div>
    <div class="card-bd" style="padding:0">
      @foreach($patient->identifications as $idCard)
      <div style="display:flex;align-items:center;gap:10px;padding:10px 16px;border-bottom:1px solid #f8f9ff">
        <div style="width:36px;height:36px;border-radius:10px;background:linear-gradient(135deg,#9b59b6,#9b59b6cc);color:#fff;display:flex;align-items:center;justify-content:center;font-size:14px;flex-shrink:0">
          <i class="bi bi-credit-card"></i>
        </div>
        <div style="flex:1;min-width:0">
          <div style="font-size:12.5px;font-weight:700;color:#012970">{{ $idCard->card_code }}</div>
          <div style="font-size:10.5px;color:#aaa">{{ $idCard->card_type }}</div>
        </div>
      </div>
      @endforeach
    </div>
  </div>
  @endif

  {{-- ── Emergency Contacts ────────────────────────────────────────────────── --}}
  @if($patient->contacts->isNotEmpty())
  <div class="card-emr mb-3">
    <div class="card-hd">
      <div class="card-hd-title">
        <i class="bi bi-people-fill" style="color:#2eca6a"></i>
        ទំនាក់ទំនង <small style="font-weight:400;color:#aaa">/ Contacts</small>
      </div>
    </div>
    <div class="card-bd" style="padding:0">
      @foreach($patient->contacts as $contact)
      <div style="display:flex;align-items:center;gap:10px;padding:10px 16px;border-bottom:1px solid #f8f9ff">
        <div style="width:36px;height:36px;border-radius:10px;background:linear-gradient(135deg,{{ $contact->is_emergency ? '#e74c3c' : '#2eca6a' }},{{ $contact->is_emergency ? '#c0392b' : '#27ae60' }});color:#fff;display:flex;align-items:center;justify-content:center;font-size:14px;flex-shrink:0">
          <i class="bi bi-{{ $contact->is_emergency ? 'exclamation-triangle-fill' : 'person-fill' }}"></i>
        </div>
        <div style="flex:1;min-width:0">
          <div style="font-size:12.5px;font-weight:700;color:#012970">
            {{ $contact->contact_name }}
            @if($contact->is_emergency)
              <span style="font-size:9px;background:#fce4ec;color:#e74c3c;padding:1px 6px;border-radius:8px;margin-left:4px">EMERGENCY</span>
            @endif
          </div>
          <div style="font-size:10.5px;color:#aaa">
            {{ $contact->relationship ?? '' }}
            @if($contact->contact_phone) · {{ $contact->contact_phone }} @endif
          </div>
        </div>
      </div>
      @endforeach
    </div>
  </div>
  @endif

</div>{{-- /col-lg-4 --}}

{{-- ══════════════════════════════════════════════════════════════════════════
     RIGHT: Visit History
     ══════════════════════════════════════════════════════════════════════════ --}}
<div class="col-12 col-lg-8">

  <div class="card-emr">
    <div class="card-hd">
      <div class="card-hd-title">
        <i class="bi bi-clock-history" style="color:#4154f1"></i>
        ប្រវត្តិការព្យាបាល <small style="font-weight:400;color:#aaa">/ Visit History</small>
      </div>
      <a href="{{ url('/workflow/create') }}?patient_code={{ $patient->code }}" class="btn btn-sm btn-primary">
        <i class="bi bi-plus-lg"></i> ការចូលថ្មី
      </a>
    </div>

    @forelse($patient->visits as $visit)
    @php
      $isActive  = is_null($visit->discharged_at);
      $doneCount = count($visit->done_steps ?? []);
      $skipCount = count($visit->skipped_steps ?? []);
    @endphp
    <a href="{{ url('/workflow/' . $visit->code) }}"
       class="visit-row" style="text-decoration:none">
      <div class="v-avatar" style="background:linear-gradient(135deg,{{ $visit->visit_type === 'IPD' ? '#ff771d' : '#4154f1' }},{{ $visit->visit_type === 'IPD' ? '#e65a00' : '#717ff5' }});border-radius:10px">
        <i class="bi bi-{{ $visit->visit_type === 'IPD' ? 'bed-fill' : 'person-fill' }}" style="font-size:15px"></i>
      </div>
      <div class="v-info">
        <div class="v-name" style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
          <code style="font-size:12px;color:#4154f1">{{ $visit->code }}</code>
          <span class="badge-s {{ $visit->visit_type === 'IPD' ? 'b-ipd' : 'b-opd' }}">{{ $visit->visit_type }}</span>
          @if($isActive)
            <span class="badge-s b-active"><i class="bi bi-circle-fill" style="font-size:7px"></i> Active</span>
          @else
            <span class="badge-s b-done">Done</span>
          @endif
        </div>
        <div class="v-meta">
          <i class="bi bi-calendar3" style="font-size:10px"></i>
          {{ $visit->admitted_at?->format('d/m/Y H:i') ?? '—' }}
          @if($visit->admission_type) · {{ $visit->admission_type }} @endif
          @if($doneCount > 0)
            · <span style="color:#2eca6a">{{ $doneCount }} steps done</span>
          @endif
        </div>
      </div>
      <i class="bi bi-chevron-right" style="color:#ddd;flex-shrink:0"></i>
    </a>

    @empty
    <div style="text-align:center;padding:40px 24px;color:#bbb">
      <div style="font-size:36px;margin-bottom:10px;opacity:.3">📋</div>
      <div style="font-size:13px;font-weight:600;margin-bottom:6px">No visits yet</div>
      <div style="font-size:11px;margin-bottom:12px">This patient has no visit records.</div>
      <a href="{{ url('/workflow/create') }}?patient_code={{ $patient->code }}" class="btn btn-sm btn-primary">
        <i class="bi bi-plus-lg"></i> Create First Visit
      </a>
    </div>
    @endforelse
  </div>

</div>{{-- /col-lg-8 --}}

</div>{{-- /row --}}

@endsection
