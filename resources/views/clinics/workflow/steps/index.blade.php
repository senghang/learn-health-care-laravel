@extends('clinics.layout.app')
@section('title', 'ការចូលព្យាបាល / Visits')

@section('content')

<x-page-header
    title="ការចូលព្យាបាល"
    subtitle="Visits"
    :breadcrumbs="[
        ['label' => 'ដើម', 'url' => url('/')],
        ['label' => 'Visits'],
    ]"
>
    <a href="{{ url('/workflow/create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg"></i> ការចូលថ្មី
    </a>
</x-page-header>

{{-- ── Stats Cards ─────────────────────────────────────────────────────────── --}}
@if(isset($stats))
<div class="row g-2 mb-3">
  @php
    $cards = [
      ['icon' => 'bi-calendar-check', 'color' => '#4154f1', 'label' => 'Today Total',  'val' => $stats['total']],
      ['icon' => 'bi-person-fill',     'color' => '#2eca6a', 'label' => 'OPD',           'val' => $stats['opd']],
      ['icon' => 'bi-bed-fill',        'color' => '#ff771d', 'label' => 'IPD',           'val' => $stats['ipd']],
      ['icon' => 'bi-circle-fill',     'color' => '#3498db', 'label' => 'Active',        'val' => $stats['active']],
      ['icon' => 'bi-check-circle',    'color' => '#27ae60', 'label' => 'Completed',     'val' => $stats['completed']],
      ['icon' => 'bi-exclamation-triangle', 'color' => '#e74c3c', 'label' => 'Emergency','val' => $stats['emergency']],
    ];
  @endphp
  @foreach($cards as $c)
  <div class="col-6 col-sm-4 col-lg-2">
    <div class="card-emr" style="text-align:center;padding:14px 10px">
      <i class="bi {{ $c['icon'] }}" style="font-size:20px;color:{{ $c['color'] }}"></i>
      <div style="font-size:22px;font-weight:800;color:#1a1f36;margin-top:4px">{{ $c['val'] }}</div>
      <div style="font-size:10px;color:#aaa;font-weight:600">{{ $c['label'] }}</div>
    </div>
  </div>
  @endforeach
</div>
@endif

{{-- ── Filter Bar ──────────────────────────────────────────────────────────── --}}
<div class="card-emr mb-3">
  <div class="card-bd" style="padding:12px 16px">
    <form method="GET" action="{{ route('visits.index') }}" id="filterForm">
      <div class="row g-2 align-items-end">
        <div class="col-12 col-sm-4 col-md-3">
          <input type="text" name="search" class="form-control" value="{{ request('search') }}"
                 placeholder="Search code, name, patient…" style="font-size:13px"/>
        </div>
        <div class="col-6 col-sm-2">
          <select name="type" class="form-select" style="font-size:13px" onchange="this.form.submit()">
            <option value="">Type — All</option>
            <option value="OPD" {{ request('type') === 'OPD' ? 'selected' : '' }}>OPD</option>
            <option value="IPD" {{ request('type') === 'IPD' ? 'selected' : '' }}>IPD</option>
          </select>
        </div>
        <div class="col-6 col-sm-2">
          <select name="status" class="form-select" style="font-size:13px" onchange="this.form.submit()">
            <option value="">Status — All</option>
            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
            <option value="done" {{ request('status') === 'done' ? 'selected' : '' }}>Completed</option>
          </select>
        </div>
        <div class="col-6 col-sm-2">
          <select name="priority" class="form-select" style="font-size:13px" onchange="this.form.submit()">
            <option value="">Priority — All</option>
            <option value="Emergency" {{ request('priority') === 'Emergency' ? 'selected' : '' }}>Emergency</option>
            <option value="Urgent" {{ request('priority') === 'Urgent' ? 'selected' : '' }}>Urgent</option>
            <option value="Standard" {{ request('priority') === 'Standard' ? 'selected' : '' }}>Standard</option>
            <option value="Low" {{ request('priority') === 'Low' ? 'selected' : '' }}>Low</option>
          </select>
        </div>
        <div class="col-6 col-sm-2">
          <input type="date" name="date" class="form-control" value="{{ request('date') }}" style="font-size:13px" onchange="this.form.submit()"/>
        </div>
        @if(request()->hasAny(['search','type','status','priority','date']))
        <div class="col-auto">
          <a href="{{ route('visits.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-x-circle"></i> Clear
          </a>
        </div>
        @endif
      </div>
    </form>
  </div>
</div>

{{-- ── Results Count ───────────────────────────────────────────────────────── --}}
@if($visits->total() > 0)
<div style="font-size:11px;color:#aaa;padding:4px 2px;margin-bottom:8px">
  {{ $visits->firstItem() }}–{{ $visits->lastItem() }} of {{ number_format($visits->total()) }} visits
</div>
@endif

{{-- ── Visit List ──────────────────────────────────────────────────────────── --}}
@forelse($visits as $visit)
@php
  $isActive  = is_null($visit->discharged_at);
  $doneCount = count($visit->done_steps ?? []);
  $totalSteps = 10;
  $pct = $totalSteps > 0 ? round(($doneCount / $totalSteps) * 100) : 0;
  $priorityColors = ['Emergency' => '#e74c3c', 'Urgent' => '#ff771d', 'Standard' => '#3498db', 'Low' => '#95a5a6'];
  $pColor = $priorityColors[$visit->priority ?? ''] ?? null;
@endphp
<div class="visit-row" onclick="window.location='{{ $isActive ? url('/workflow/' . $visit->code) : route('visits.show', $visit->code) }}'">
  {{-- Type avatar --}}
  <div class="v-avatar" style="background:linear-gradient(135deg,{{ $visit->visit_type === 'IPD' ? '#ff771d' : '#4154f1' }},{{ $visit->visit_type === 'IPD' ? '#e65a00' : '#717ff5' }});border-radius:10px">
    <i class="bi bi-{{ $visit->visit_type === 'IPD' ? 'bed-fill' : 'person-fill' }}" style="font-size:15px"></i>
  </div>

  {{-- Info --}}
  <div class="v-info">
    <div class="v-name">
      {{ $visit->surname }}, {{ $visit->name }}
      <span style="font-size:11px;color:#aaa;font-weight:400;margin-left:6px">{{ $visit->patient_code }}</span>
    </div>
    <div class="v-meta">
      <code style="font-size:11px;color:#4154f1">{{ $visit->code }}</code>
      · <i class="bi bi-calendar3" style="font-size:9px"></i> {{ $visit->admitted_at?->format('d/m/Y H:i') ?? '—' }}
      @if($visit->admission_type) · {{ $visit->admission_type }} @endif
      @if($doneCount > 0) · <span style="color:#2eca6a">{{ $doneCount }}/{{ $totalSteps }}</span> @endif
    </div>
  </div>

  {{-- Badges --}}
  <div class="v-badges">
    @if($pColor)
    <span class="badge-s" style="background:{{ $pColor }}15;color:{{ $pColor }}">{{ $visit->priority }}</span>
    @endif
    <span class="badge-s {{ $visit->visit_type === 'IPD' ? 'b-ipd' : 'b-opd' }}">{{ $visit->visit_type }}</span>
    @if($isActive)
      <span class="badge-s b-active"><i class="bi bi-circle-fill" style="font-size:7px"></i> Active</span>
    @else
      <span class="badge-s b-done">Done</span>
    @endif
  </div>

  <i class="bi bi-chevron-right" style="color:#ddd;flex-shrink:0"></i>
</div>
@empty
<div style="text-align:center;padding:56px 24px;color:#bbb">
  <div style="font-size:48px;margin-bottom:12px;opacity:.3">📋</div>
  <div style="font-size:14px;font-weight:600;margin-bottom:8px">
    {{ request()->hasAny(['search','type','status','date']) ? 'No visits match filters' : 'No visits yet' }}
  </div>
  @if(!request()->hasAny(['search','type','status','date']))
  <a href="{{ url('/workflow/create') }}" class="btn btn-primary">
    <i class="bi bi-plus-lg"></i> Create First Visit
  </a>
  @endif
</div>
@endforelse

@if($visits->hasPages())
<div class="mt-3">{{ $visits->links() }}</div>
@endif

@endsection
