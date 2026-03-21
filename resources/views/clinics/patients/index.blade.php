@extends('clinics.layout.app')
@section('title', __('app.patient.title'))
@section('content')

<div class="pg-header">
  <div>
    <h1 class="pg-title">{{ __('app.patient.title') }} <small>/ Patients</small></h1>
    <div class="breadcrumb-row">
      <a href="{{ route('dashboard') }}">ដើម</a><span>›</span>
      <span>{{ __('app.patient.title') }}</span>
    </div>
  </div>
  <a href="{{ route('patients.create') }}" class="btn btn-primary">
    <i class="bi bi-person-plus-fill"></i> {{ __('app.patient.new') }}
  </a>
</div>

{{-- Filter bar --}}
<div class="card-emr mb-3">
  <div class="card-bd">
    <form method="GET" action="{{ route('patients.index') }}">
      <div class="row g-2 align-items-end">
        <div class="col-12 col-sm-6 col-md-5">
          <label class="flbl"><span class="km">{{ __('app.search') }}</span></label>
          <div style="position:relative">
            <i class="bi bi-search" style="position:absolute;left:11px;top:50%;transform:translateY(-50%);color:#bbb;font-size:13px"></i>
            <input type="text" name="search" class="form-control" style="padding-left:32px"
                   placeholder="{{ __('app.patient.code') }}, {{ __('app.patient.name') }}, {{ __('app.patient.phone') }}…"
                   value="{{ request('search') }}" autofocus/>
          </div>
        </div>
        <div class="col-6 col-sm-3 col-md-2">
          <label class="flbl"><span class="km">{{ __('app.patient.sex') }}</span></label>
          <select name="sex" class="form-select">
            <option value="">{{ __('app.all') }}</option>
            <option value="M" {{ request('sex') === 'M' ? 'selected' : '' }}>{{ __('app.patient.male') }}</option>
            <option value="F" {{ request('sex') === 'F' ? 'selected' : '' }}>{{ __('app.patient.female') }}</option>
          </select>
        </div>
        <div class="col-6 col-sm-3 col-md-2">
          <button type="submit" class="btn btn-primary btn-w100">
            <i class="bi bi-funnel-fill"></i> {{ __('app.search') }}
          </button>
        </div>
        @if(request()->hasAny(['search','sex']))
        <div class="col-12 col-md-auto">
          <a href="{{ route('patients.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-x-circle"></i> Clear
          </a>
        </div>
        @endif
      </div>
    </form>
  </div>
</div>

@if($patients->total() > 0)
<div style="font-size:11px;color:#aaa;padding:4px 2px;margin-bottom:8px">
  {{ $patients->firstItem() }}–{{ $patients->lastItem() }} of {{ number_format($patients->total()) }} patients
</div>
@endif

{{-- Patient list --}}
@forelse($patients as $patient)
@php
  $initials = strtoupper(substr($patient->surname, 0, 1).substr($patient->name, 0, 1));
  $colors   = ['#4154f1','#2eca6a','#ff771d','#e74c3c','#9b59b6','#00bcd4','#f39c12','#1abc9c'];
  $color    = $colors[abs(crc32($patient->code)) % count($colors)];
@endphp
<div class="visit-row" onclick="window.location='{{ route('patients.show', $patient->code) }}'">
  <div class="v-avatar" style="background:linear-gradient(135deg,{{ $color }},{{ $color }}cc)">
    {{ $initials ?: '?' }}
  </div>
  <div class="v-info">
    <div class="v-name">{{ $patient->surname }}, {{ $patient->name }}</div>
    <div class="v-meta">
      {{ $patient->code }}
      @if($patient->phone) · {{ $patient->phone }} @endif
      @if($patient->sex) · {{ $patient->sex === 'M' ? __('app.patient.male') : __('app.patient.female') }} @endif
      @if($patient->birthdate) · {{ $patient->birthdate->age }}y @endif
    </div>
  </div>
  <div class="v-badges">
    @if($patient->visits_count > 0)
    <span class="badge-s" style="background:#eef0fd;color:#4154f1">
      {{ $patient->visits_count }} {{ __('app.patient.visits_count') }}
    </span>
    @else
    <span class="badge-s" style="background:#e8f8ef;color:#2eca6a">New</span>
    @endif
  </div>
  <i class="bi bi-chevron-right" style="color:#ddd;flex-shrink:0"></i>
</div>
@empty
<div style="text-align:center;padding:56px 24px;color:#bbb">
  <div style="font-size:48px;margin-bottom:12px;opacity:.3">👤</div>
  <div style="font-size:14px;font-weight:600;margin-bottom:8px">
    {{ request('search') ? 'No patients found' : 'No patients registered yet' }}
  </div>
  @if(!request('search'))
  <a href="{{ route('patients.create') }}" class="btn btn-primary">
    <i class="bi bi-person-plus-fill"></i> {{ __('app.patient.new') }}
  </a>
  @endif
</div>
@endforelse

@if($patients->hasPages())
<div class="mt-3">{{ $patients->links() }}</div>
@endif

@endsection
