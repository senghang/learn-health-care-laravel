@extends('clinics.layout.app')
@section('title', 'អ្នកជំងឺ / Patients')

@section('content')

<x-page-header
    title="អ្នកជំងឺ"
    subtitle="Patients"
    :breadcrumbs="[
        ['label' => 'ដើម', 'url' => url('/')],
        ['label' => 'អ្នកជំងឺ'],
    ]"
>
    <a href="{{ route('patients.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-person-plus-fill"></i> {{ __('app.patient.new') }}
    </a>
</x-page-header>

{{-- Search / Filter bar --}}
<div class="card-emr mb-3">
  <div class="card-bd" style="padding:12px 16px">
    <form method="GET" action="{{ route('patients.index') }}">
      <div class="row g-2 align-items-end">
        <div class="col-12 col-sm-5 col-md-4">
          <input type="text" name="search" class="form-control" value="{{ request('search') }}"
                 placeholder="ស្វែងរក / Search by code, name, phone, SPID…"
                 style="font-size:13px"/>
        </div>
        <div class="col-6 col-sm-3 col-md-2">
          <select name="sex" class="form-select" style="font-size:13px">
            <option value="">{{ __('app.patient.sex') }} — All</option>
            <option value="M" {{ request('sex') === 'M' ? 'selected' : '' }}>{{ __('app.patient.male') }}</option>
            <option value="F" {{ request('sex') === 'F' ? 'selected' : '' }}>{{ __('app.patient.female') }}</option>
          </select>
        </div>
        <div class="col-6 col-sm-3 col-md-2">
          <select name="status" class="form-select" style="font-size:13px">
            <option value="">Status — All</option>
            <option value="Active" {{ request('status') === 'Active' ? 'selected' : '' }}>Active</option>
            <option value="Inactive" {{ request('status') === 'Inactive' ? 'selected' : '' }}>Inactive</option>
          </select>
        </div>
        <div class="col-6 col-sm-3 col-md-2">
          <button type="submit" class="btn btn-primary btn-w100">
            <i class="bi bi-funnel-fill"></i> {{ __('app.search') }}
          </button>
        </div>
        @if(request()->hasAny(['search','sex','status']))
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

{{-- Results count --}}
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
      @if($patient->blood_type) · <span style="color:#e74c3c">{{ $patient->blood_type }}</span> @endif
    </div>
  </div>
  <div class="v-badges">
    @if($patient->status === 'Inactive')
    <span class="badge-s" style="background:#f5f5f5;color:#999">Inactive</span>
    @endif
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
