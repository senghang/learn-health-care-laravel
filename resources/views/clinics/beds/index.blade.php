@extends('clinics.layout.app')
@section('title', __('app.bed.title'))

@section('content')
<div class="pg-header">
  <div>
    <h1 class="pg-title">{{ __('app.bed.title') }} <small>/ Bed Management</small></h1>
    <div class="breadcrumb-row">
      <a href="{{ route('dashboard') }}">{{ __('app.nav.dashboard') }}</a>
      <span>›</span><span>{{ __('app.bed.title') }}</span>
    </div>
  </div>
  <a href="{{ route('beds.ward.create') }}" class="btn btn-primary">
    <i class="bi bi-plus-lg"></i> {{ __('app.bed.ward') }}ថ្មី
  </a>
</div>

{{-- Summary bar --}}
@php
  $totalCap  = $wards->sum('capacity');
  $totalOcc  = $wards->sum('occupied_count');
  $totalFree = $totalCap - $totalOcc;
  $pct       = $totalCap > 0 ? round($totalOcc / $totalCap * 100) : 0;
@endphp
<div class="row g-3 mb-3">
  @foreach([
    ['ចំណុះសរុប','Total Beds',   $totalCap,  '#4154f1','bi-hospital'],
    ['កំពុងប្រើ', 'Occupied',    $totalOcc,  '#ff771d','bi-person-fill'],
    ['ទំនេរ',    'Available',   $totalFree, '#2eca6a','bi-check-circle-fill'],
    ['អត្រា',    'Occupancy',   $pct.'%',   '#e74c3c','bi-activity'],
  ] as [$km,$en,$val,$col,$ico])
  <div class="col-6 col-md-3">
    <div class="stat-card">
      <div class="stat-icon" style="background:{{ $col }}22;color:{{ $col }}">
        <i class="bi {{ $ico }}"></i>
      </div>
      <div>
        <div class="stat-num" style="color:{{ $col }}">{{ $val }}</div>
        <div class="stat-lbl">{{ $km }}<br><small>{{ $en }}</small></div>
      </div>
    </div>
  </div>
  @endforeach
</div>

{{-- Ward cards --}}
@forelse($wards as $ward)
@php
  $occ  = $ward->occupied_count;
  $cap  = $ward->capacity ?: 1;
  $pctW = round($occ / $cap * 100);
  $barColor = $pctW >= 90 ? '#e74c3c' : ($pctW >= 70 ? '#ff771d' : '#2eca6a');
@endphp
<div class="card-emr mb-3">
  <div class="card-hd" style="flex-wrap:wrap;gap:8px">
    <div>
      <div class="card-hd-title">
        <i class="bi bi-building" style="color:#4154f1"></i>
        {{ $ward->name_kh ?? $ward->name }}
        <small style="font-weight:400;color:#aaa">/ {{ $ward->name_en ?? $ward->name }}</small>
        <span style="font-size:10px;background:#eef0fd;color:#4154f1;padding:2px 8px;border-radius:10px;font-weight:700">
          {{ $ward->type }}
        </span>
      </div>
      <div style="font-size:11px;color:#aaa;margin-top:3px">{{ $ward->code }}</div>
    </div>
    <div style="display:flex;align-items:center;gap:12px">
      <div style="text-align:right">
        <div style="font-size:18px;font-weight:800;color:{{ $barColor }}">{{ $occ }}/{{ $ward->capacity }}</div>
        <div style="font-size:10px;color:#aaa">{{ __('app.bed.occupancy') }}</div>
      </div>
      <div style="width:60px">
        <div style="height:6px;background:#f0f2ff;border-radius:3px;overflow:hidden">
          <div style="height:100%;width:{{ $pctW }}%;background:{{ $barColor }};border-radius:3px"></div>
        </div>
        <div style="font-size:10px;color:#aaa;text-align:center;margin-top:2px">{{ $pctW }}%</div>
      </div>
      <a href="{{ route('beds.ward', $ward->id) }}" class="btn btn-sm btn-outline-primary">
        <i class="bi bi-grid-3x3-gap-fill"></i> {{ __('app.bed.bed') }}
      </a>
      <a href="{{ route('beds.ward.edit', $ward->id) }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-pencil"></i>
      </a>
    </div>
  </div>

  {{-- Rooms mini grid --}}
  @if($ward->rooms->isNotEmpty())
  <div class="card-bd" style="padding:10px 16px">
    <div style="display:flex;flex-wrap:wrap;gap:8px">
      @foreach($ward->rooms as $room)
        <a href="{{ route('beds.ward', $ward->id) }}?room={{ $room->id }}"
           style="display:flex;align-items:center;gap:6px;padding:5px 10px;background:#f6f9ff;border-radius:8px;border:1px solid #e6eaf5;text-decoration:none;font-size:12px;color:#444">
          <i class="bi bi-door-open" style="color:#4154f1;font-size:13px"></i>
          {{ $room->name }}
          <span style="font-size:10px;color:#aaa">{{ $room->type }}</span>
        </a>
      @endforeach
    </div>
  </div>
  @endif
</div>
@empty
<div style="text-align:center;padding:48px;color:#bbb">
  <div style="font-size:40px;margin-bottom:12px;opacity:.3">🏥</div>
  <div style="font-size:14px;font-weight:600;margin-bottom:8px">មិនទាន់មានវ៉ាន / No wards yet</div>
  <a href="{{ route('beds.ward.create') }}" class="btn btn-primary">
    <i class="bi bi-plus-lg"></i> បន្ថែមវ៉ាន
  </a>
</div>
@endforelse

@endsection
