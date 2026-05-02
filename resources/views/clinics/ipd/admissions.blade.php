@extends('clinics.layout.app')
@section('title', 'IPD — Admissions')
@section('content')

<x-page-header title="អ្នកជំងឺសម្រាក" subtitle="Inpatient Admissions"
    :breadcrumbs="[['label'=>'ដើម','url'=>route('dashboard')],['label'=>'IPD'],['label'=>'Admissions']]">
</x-page-header>

{{-- Flow Banner --}}
<div class="flow-panel mb-3">
    <div class="flow-panel-title">
        <i class="bi bi-diagram-3-fill me-1"></i> Admission Workflow
    </div>
    <div class="flow-steps">
        <span class="flow-step"><i class="bi bi-person-plus-fill me-1"></i>1. Admit patient</span>
        <span class="flow-step"><i class="bi bi-hospital me-1"></i>2. Assign bed</span>
        <span class="flow-step"><i class="bi bi-clipboard2-heart me-1"></i>3. Treatments &amp; medications</span>
        <span class="flow-step"><i class="bi bi-calendar2-week me-1"></i>4. Monitor LOS</span>
        <span class="flow-step"><i class="bi bi-box-arrow-right me-1"></i>5. Discharge</span>
    </div>
</div>

@if(session('flash'))
<div class="note note-success mb-3"><i class="bi bi-check-circle-fill"></i> {{ session('flash') }}</div>
@endif
@if(session('flash_error'))
<div class="note note-danger mb-3"><i class="bi bi-exclamation-triangle-fill"></i> {{ session('flash_error') }}</div>
@endif

{{-- KPI Cards --}}
<div class="row g-3 mb-3">
    @foreach([
        [$stats['active_admissions'],        'bi-hospital-fill',      '#4154f1','#eef0fd', 'សម្រាកនៅ',       'Active Admissions'],
        [$stats['today_admissions'],         'bi-box-arrow-in-right', '#2eca6a','#e8f8ef', 'ថ្ងៃនេះចូល',     'Admitted Today'],
        [$stats['today_discharges'],         'bi-box-arrow-right',    '#ff771d','#fff3e8', 'ថ្ងៃនេះចេញ',     'Discharged Today'],
        [$stats['avg_length_of_stay'].'d',   'bi-calendar2-week',     '#9b59b6','#f5eeff', 'ថ្ងៃ​ជា​មធ្យម',   'Avg Stay (days)'],
    ] as [$val,$icon,$col,$bg,$km,$en])
    <div class="col-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:{{ $bg }};color:{{ $col }}"><i class="bi {{ $icon }}"></i></div>
            <div>
                <div class="stat-num" style="color:{{ $col }};font-size:20px;font-weight:800">{{ $val }}</div>
                <div class="stat-lbl">{{ $km }}<br><small style="color:#aaa">{{ $en }}</small></div>
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- Filter --}}
<div class="card-emr mb-3">
    <div class="card-hd">
        <div class="card-hd-title"><i class="bi bi-funnel-fill"></i> Filter</div>
        @if(request()->hasAny(['search','status','ward_id','date']))
        <a href="{{ route('admissions.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-x-circle"></i> Clear
        </a>
        @endif
    </div>
    <div class="card-bd">
        <form method="GET" action="{{ route('admissions.index') }}">
            <div class="row g-2 align-items-end">
                <div class="col-12 col-sm-4">
                    <input type="text" name="search" class="form-control"
                           placeholder="Search code, patient name…" value="{{ request('search') }}" autofocus/>
                </div>
                <div class="col-6 col-sm-2">
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="admitted"    {{ request('status')==='admitted'   ?'selected':'' }}>🟢 Admitted</option>
                        <option value="discharged"  {{ request('status')==='discharged' ?'selected':'' }}>🔵 Discharged</option>
                        <option value="transferred" {{ request('status')==='transferred'?'selected':'' }}>🟠 Transferred</option>
                        <option value="deceased"    {{ request('status')==='deceased'   ?'selected':'' }}>⚫ Deceased</option>
                        <option value="cancelled"   {{ request('status')==='cancelled'  ?'selected':'' }}>🔴 Cancelled</option>
                    </select>
                </div>
                <div class="col-6 col-sm-2">
                    <select name="ward_id" class="form-select">
                        <option value="">All Wards</option>
                        @foreach($wards as $ward)
                        <option value="{{ $ward->id }}" {{ request('ward_id') == $ward->id ? 'selected':'' }}>{{ $ward->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-sm-2">
                    <input type="date" name="date" class="form-control" value="{{ request('date') }}"
                           title="Filter by admission date"/>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> Search</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Table --}}
<div class="card-emr">
    <div class="card-hd">
        <div class="card-hd-title">
            <i class="bi bi-hospital"></i> Admission List
            <span style="font-size:11px;background:#eef0fd;color:#4154f1;padding:1px 8px;border-radius:8px;font-weight:700;margin-left:6px">
                {{ $admissions->total() }}
            </span>
        </div>
        <span style="font-size:11px;color:#aaa">Click a row to open details</span>
    </div>
    <div class="card-bd" style="padding:0">
        <div class="table-responsive">
            <table class="tbl">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Patient</th>
                        <th>Ward / Bed</th>
                        <th>Type</th>
                        <th>Attending</th>
                        <th>Admitted</th>
                        <th>LOS</th>
                        <th>Status</th>
                        <th style="text-align:right;padding-right:16px">Action</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($admissions as $adm)
                @php
                    $isAdmitted = $adm->status === 'admitted';
                    $statusMeta = [
                        'admitted'    => ['#e8f8ef','#2eca6a', 'bi-check-circle-fill',   'Admitted'],
                        'discharged'  => ['#f0f2ff','#4154f1', 'bi-box-arrow-right',      'Discharged'],
                        'transferred' => ['#fff3e8','#ff771d', 'bi-arrow-left-right',     'Transferred'],
                        'deceased'    => ['#f5f5f5','#666',    'bi-x-circle-fill',        'Deceased'],
                        'cancelled'   => ['#fde8e8','#e74c3c', 'bi-slash-circle-fill',   'Cancelled'],
                    ];
                    [$sbg, $scol, $sicon, $slabel] = $statusMeta[$adm->status] ?? ['#f5f5f5','#888','bi-circle','Unknown'];
                    $los = $adm->length_of_stay ?? 0;
                    $overdueStyle = $isAdmitted && $adm->expected_discharge_at?->isPast() ? 'color:#e74c3c;font-weight:700' : '';
                @endphp
                <tr style="cursor:pointer" onclick="location.href='{{ route('admissions.show', $adm->code) }}'">
                    <td>
                        <code style="color:#4154f1;font-size:11px">{{ $adm->code }}</code>
                        @if($isAdmitted && $adm->expected_discharge_at?->isPast())
                        <span title="Expected discharge date passed" style="font-size:10px;color:#e74c3c;margin-left:4px">
                            <i class="bi bi-alarm-fill"></i>
                        </span>
                        @endif
                    </td>
                    <td>
                        <div style="font-weight:700;color:#012970;font-size:13px">
                            {{ $adm->patient?->surname }} {{ $adm->patient?->name }}
                        </div>
                        <div style="font-size:10.5px;color:#aaa">{{ $adm->patient_code }}</div>
                    </td>
                    <td>
                        <div style="font-size:12px;color:#444;font-weight:600">{{ $adm->ward?->name ?? '—' }}</div>
                        @if($adm->bed)
                        <div style="font-size:10.5px;color:#aaa">
                            <i class="bi bi-hospital" style="font-size:9px"></i> {{ $adm->bed->name }}
                        </div>
                        @endif
                    </td>
                    <td>
                        @if($adm->admission_type)
                        <span style="font-size:10.5px;background:#f0f2ff;color:#4154f1;padding:2px 9px;border-radius:8px">{{ $adm->admission_type }}</span>
                        @else<span style="color:#bbb">—</span>@endif
                    </td>
                    <td style="font-size:12px;color:#555">{{ $adm->attending_doctor ?? '—' }}</td>
                    <td>
                        <div style="font-size:12px;font-weight:600">{{ $adm->admitted_at?->format('d M Y') }}</div>
                        <div style="font-size:10px;color:#aaa">{{ $adm->admitted_at?->format('H:i') }}</div>
                    </td>
                    <td>
                        <span style="font-weight:800;font-size:14px;{{ $overdueStyle ?: 'color:'.($isAdmitted ? '#2eca6a':'#888') }}">
                            {{ $los }}d
                        </span>
                    </td>
                    <td>
                        <span style="font-size:10.5px;background:{{ $sbg }};color:{{ $scol }};padding:3px 10px;border-radius:8px;font-weight:700;white-space:nowrap;display:inline-flex;align-items:center;gap:5px">
                            <i class="bi {{ $sicon }}" style="font-size:9px"></i> {{ $slabel }}
                        </span>
                    </td>
                    <td onclick="event.stopPropagation()" style="text-align:right;padding-right:12px">
                        <a href="{{ route('admissions.show', $adm->code) }}"
                           class="btn btn-sm btn-outline-primary" title="View details">
                            <i class="bi bi-eye"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" style="text-align:center;padding:50px;color:#bbb">
                        <div style="font-size:40px;margin-bottom:10px;opacity:.25">🏥</div>
                        <div style="font-size:14px;color:#ccc">No admissions found</div>
                        @if(request()->hasAny(['search','status','ward_id','date']))
                        <a href="{{ route('admissions.index') }}" class="btn btn-sm btn-outline-secondary mt-3">
                            <i class="bi bi-x-circle"></i> Clear filters
                        </a>
                        @endif
                    </td>
                </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@if($admissions->hasPages())
<div class="mt-3 d-flex justify-content-center">{{ $admissions->links() }}</div>
@endif

@endsection
