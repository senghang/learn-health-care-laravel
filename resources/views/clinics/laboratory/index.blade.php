@extends('clinics.layout.app')
@section('title', 'មន្ទីរពិសោធន៍ / Laboratory')

@section('content')
<x-ui.page-header
    km="មន្ទីរពិសោធន៍"
    title="Laboratory Orders"
    :breadcrumbs="[['label'=>'ដើម','url'=>url('/')],['label'=>'Laboratory']]">
    <x-slot:actions>
        <x-ui.button href="{{ route('laboratory.create') }}" variant="primary"><x-slot:icon><i class="bi bi-plus-lg"></i></x-slot:icon>New Lab Order</x-ui.button>
    </x-slot:actions>
</x-ui.page-header>

{{-- Stats --}}
<div class="row g-2 mb-3">
  @foreach([
    ['label'=>'Today','val'=>$stats['today_orders'],'icon'=>'bi-droplet-fill','color'=>'#4154f1'],
    ['label'=>'Pending','val'=>$stats['pending'],'icon'=>'bi-hourglass-split','color'=>'#ff771d'],
    ['label'=>'In Progress','val'=>$stats['in_progress'],'icon'=>'bi-arrow-repeat','color'=>'#9b59b6'],
    ['label'=>'Done Today','val'=>$stats['completed_today'],'icon'=>'bi-check-circle-fill','color'=>'#2eca6a'],
  ] as $c)
  <div class="col-6 col-lg-3">
    <div class="card-emr" style="text-align:center;padding:14px 10px">
      <i class="bi {{ $c['icon'] }}" style="font-size:20px;color:{{ $c['color'] }}"></i>
      <div style="font-size:22px;font-weight:800;color:#012970;margin-top:4px">{{ $c['val'] }}</div>
      <div style="font-size:10px;color:#aaa;font-weight:600">{{ $c['label'] }}</div>
    </div>
  </div>
  @endforeach
</div>

{{-- Filters --}}
<div class="card-emr mb-3">
  <div class="card-bd" style="padding:12px 16px">
    <form method="GET" class="row g-2 align-items-end">
      <div class="col-12 col-sm-4 col-md-3">
        <input type="text" name="search" class="form-control" placeholder="Code, patient name…" value="{{ request('search') }}" style="font-size:13px"/>
      </div>
      <div class="col-6 col-sm-2">
        <select name="status" class="form-select" style="font-size:13px" onchange="this.form.submit()">
          <option value="">Status — All</option>
          @foreach(['requested','collected','processing','completed'] as $s)
          <option value="{{ $s }}" {{ request('status')===$s?'selected':'' }}>{{ ucfirst($s) }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-6 col-sm-2">
        <select name="urgency" class="form-select" style="font-size:13px" onchange="this.form.submit()">
          <option value="">Urgency — All</option>
          @foreach(['normal','urgent','stat'] as $u)
          <option value="{{ $u }}" {{ request('urgency')===$u?'selected':'' }}>{{ ucfirst($u) }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-6 col-sm-2">
        <input type="date" name="date" class="form-control" value="{{ request('date') }}" style="font-size:13px" onchange="this.form.submit()"/>
      </div>
      @if(request()->hasAny(['search','status','urgency','date']))
      <div class="col-auto">
        <a href="{{ route('laboratory.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-x-circle"></i> Clear</a>
      </div>
      @endif
    </form>
  </div>
</div>

{{-- Lab Orders List --}}
@forelse($labs as $lab)
@php
  $rc = $lab->results->count();
  $fc = $lab->results->where('value', '!=', null)->count();
  $hasCrit = $lab->has_critical;
  $sc = $lab->status_color;
  $uc = $lab->urgency_color;
@endphp
<div class="visit-row" onclick="window.location='{{ route('laboratory.show', $lab->code) }}'">
  <div class="v-avatar" style="background:linear-gradient(135deg,{{ $sc }},{{ $sc }}cc);border-radius:10px">
    <i class="bi bi-droplet-fill" style="font-size:15px"></i>
  </div>
  <div class="v-info">
    <div class="v-name">
      {{ $lab->patient?->surname }}, {{ $lab->patient?->name }}
      <span style="font-size:11px;color:#aaa;margin-left:6px">{{ $lab->patient_code }}</span>
    </div>
    <div class="v-meta">
      <code style="font-size:11px;color:#4154f1">{{ $lab->code }}</code>
      · {{ $lab->requested_at?->format('d/m/Y H:i') }}
      @if($lab->category) · {{ $lab->category }} @endif
      · <span style="color:#888">{{ $fc }}/{{ $rc }} results</span>
    </div>
  </div>
  <div class="v-badges">
    @if($hasCrit)
    <span class="badge-s" style="background:#fce4ec;color:#e74c3c"><i class="bi bi-exclamation-triangle-fill"></i> Critical</span>
    @endif
    @if($lab->urgency !== 'normal')
    <span class="badge-s" style="background:{{ $uc }}15;color:{{ $uc }}">{{ ucfirst($lab->urgency) }}</span>
    @endif
    <span class="badge-s" style="background:{{ $sc }}15;color:{{ $sc }}">{{ ucfirst($lab->status) }}</span>
  </div>
  <i class="bi bi-chevron-right" style="color:#ddd;flex-shrink:0"></i>
</div>
@empty
<div class="emr-empty">
    <i class="bi bi-flask emr-empty-icon"></i>
    <div class="emr-empty-title">No lab orders found</div>
    <div class="emr-empty-sub">Lab orders are created during patient visits</div>
    <a href="{{ route('laboratory.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Create Order</a>
</div>
@endforelse

@if($labs->hasPages())
<div class="mt-3"><x-ui.pagination :paginator="$labs"/></div>
@endif

@endsection
