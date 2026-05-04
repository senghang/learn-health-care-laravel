@extends('clinics.layout.app')
@section('title', 'រូបភាព / Imaging')

@section('content')
<x-ui.page-header
    km="រូបភាពវេជ្ជសាស្ត្រ"
    title="Imaging / Radiology"
    :breadcrumbs="[['label'=>'ដើម','url'=>url('/')],['label'=>'Imaging']]">
    <x-slot:actions>
        <x-ui.button href="{{ route('imagery.create') }}" variant="primary"><x-slot:icon><i class="bi bi-plus-lg"></i></x-slot:icon>New Imaging Order</x-ui.button>
    </x-slot:actions>
</x-ui.page-header>

{{-- Stats --}}
<div class="row g-2 mb-3">
  @foreach([
    ['label'=>'Today','val'=>$stats['today_orders'],'icon'=>'bi-camera-fill','color'=>'#4154f1'],
    ['label'=>'Pending','val'=>$stats['pending'],'icon'=>'bi-hourglass-split','color'=>'#ff771d'],
    ['label'=>'Done Today','val'=>$stats['completed_today'],'icon'=>'bi-check-circle-fill','color'=>'#2eca6a'],
  ] as $c)
  <div class="col-6 col-lg-4">
    <div class="card-emr" style="text-align:center;padding:14px 10px">
      <i class="bi {{ $c['icon'] }}" style="font-size:20px;color:{{ $c['color'] }}"></i>
      <div style="font-size:22px;font-weight:800;color:#1a1f36;margin-top:4px">{{ $c['val'] }}</div>
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
        <input type="text" name="search" class="form-control" placeholder="Code, patient…" value="{{ request('search') }}" style="font-size:13px"/>
      </div>
      <div class="col-6 col-sm-2">
        <select name="status" class="form-select" style="font-size:13px" onchange="this.form.submit()">
          <option value="">Status — All</option>
          <option value="requested" {{ request('status')==='requested'?'selected':'' }}>Requested</option>
          <option value="completed" {{ request('status')==='completed'?'selected':'' }}>Completed</option>
        </select>
      </div>
      <div class="col-6 col-sm-2">
        <select name="category" class="form-select" style="font-size:13px" onchange="this.form.submit()">
          <option value="">Category — All</option>
          @foreach(['X-ray','Ultrasound','CT','MRI','ECG','Endoscopy'] as $cat)
          <option value="{{ $cat }}" {{ request('category')===$cat?'selected':'' }}>{{ $cat }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-6 col-sm-2">
        <input type="date" name="date" class="form-control" value="{{ request('date') }}" style="font-size:13px" onchange="this.form.submit()"/>
      </div>
      @if(request()->hasAny(['search','status','category','date']))
      <div class="col-auto">
        <a href="{{ route('imagery.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-x-circle"></i> Clear</a>
      </div>
      @endif
    </form>
  </div>
</div>

{{-- List --}}
@forelse($imageries as $img)
@php
  $sc = $img->status_color;
  $catColors = ['X-ray'=>'#4154f1','Ultrasound'=>'#9b59b6','CT'=>'#ff771d','MRI'=>'#e74c3c','ECG'=>'#2eca6a','Endoscopy'=>'#3498db'];
  $cc = $catColors[$img->category] ?? '#aaa';
@endphp
<div class="visit-row" onclick="window.location='{{ route('imagery.show', $img->code) }}'">
  <div class="v-avatar" style="background:linear-gradient(135deg,{{ $cc }},{{ $cc }}cc);border-radius:10px">
    <i class="bi bi-camera-fill" style="font-size:15px"></i>
  </div>
  <div class="v-info">
    <div class="v-name">
      {{ $img->patient?->surname }}, {{ $img->patient?->name }}
      <span style="font-size:11px;color:#aaa;margin-left:6px">{{ $img->patient_code }}</span>
    </div>
    <div class="v-meta">
      <code style="font-size:11px;color:#4154f1">{{ $img->code }}</code>
      · {{ $img->requested_at?->format('d/m/Y H:i') }}
      @if($img->title) · {{ $img->title }} @endif
    </div>
  </div>
  <div class="v-badges">
    <span class="badge-s" style="background:{{ $cc }}15;color:{{ $cc }}">{{ $img->category }}</span>
    @if($img->urgency !== 'normal')
    <span class="badge-s" style="background:#fce4ec;color:#e74c3c">{{ ucfirst($img->urgency) }}</span>
    @endif
    <span class="badge-s" style="background:{{ $sc }}15;color:{{ $sc }}">{{ ucfirst($img->status) }}</span>
  </div>
  <i class="bi bi-chevron-right" style="color:#ddd;flex-shrink:0"></i>
</div>
@empty
<div style="text-align:center;padding:56px 24px;color:#bbb">
  <div style="font-size:48px;margin-bottom:12px;opacity:.3">📷</div>
  <div style="font-size:14px;font-weight:600;margin-bottom:8px">No imaging orders found</div>
  <a href="{{ route('imagery.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Create Order</a>
</div>
@endforelse

@if($imageries->hasPages())
<div class="mt-3"><x-ui.pagination :paginator="$imageries"/></div>
@endif

@endsection
