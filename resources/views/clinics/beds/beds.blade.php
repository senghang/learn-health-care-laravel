@extends('clinics.layout.app')
@section('title', $ward->name . ' — Beds')

@section('content')
<div class="pg-header">
  <div>
    <h1 class="pg-title">
      {{ $ward->name_kh ?? $ward->name }}
      <small>/ {{ $ward->name }}</small>
    </h1>
    <div class="breadcrumb-row">
      <a href="{{ route('dashboard') }}">ដើម</a><span>›</span>
      <a href="{{ route('beds.index') }}">{{ __('app.bed.title') }}</a><span>›</span>
      <span>{{ $ward->name }}</span>
    </div>
  </div>
  <div class="d-flex gap-2">
    <a href="{{ route('beds.index') }}" class="btn btn-outline-primary btn-sm">
      <i class="bi bi-arrow-left"></i> ត្រឡប់
    </a>
  </div>
</div>

{{-- Stats bar --}}
<div class="row g-2 mb-3">
  @foreach([
    ['ទំនេរ','Available',$stats['available'],'#2eca6a'],
    ['កំពុងប្រើ','Occupied', $stats['occupied'],'#ff771d'],
    ['សម្អាត','Cleaning',  $stats['cleaning'],'#9b59b6'],
    ['សរុប','Total',      $stats['total'],   '#4154f1'],
  ] as [$km,$en,$val,$col])
  <div class="col-6 col-sm-3">
    <div style="background:#fff;border-radius:10px;padding:12px;box-shadow:0 2px 8px rgba(1,41,112,.06);
                border-left:3px solid {{ $col }};display:flex;align-items:center;gap:10px">
      <div style="font-size:22px;font-weight:800;color:{{ $col }}">{{ $val }}</div>
      <div style="font-size:11px;color:#888">{{ $km }}<br><small>{{ $en }}</small></div>
    </div>
  </div>
  @endforeach
</div>

{{-- Bed grid by room --}}
@foreach($rooms as $room)
<div class="card-emr mb-3">
  <div class="card-hd">
    <div class="card-hd-title">
      <i class="bi bi-door-open"></i>
      {{ $room->name }}
      <small style="font-weight:400;color:#aaa">{{ $room->type }} · ជាន់ {{ $room->floor }}</small>
    </div>
    <span style="font-size:11px;color:#aaa">{{ $room->beds->count() }} beds</span>
  </div>
  <div class="card-bd">
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(120px,1fr));gap:10px">
      @foreach($room->beds as $bed)
      @php
        $statusColors = [
          'available'   => ['#e8f8ef','#2eca6a','#2eca6a'],
          'occupied'    => ['#fff3e8','#ff771d','#ff771d'],
          'cleaning'    => ['#f0e8ff','#9b59b6','#9b59b6'],
          'reserved'    => ['#e6f1fb','#378ADD','#378ADD'],
          'maintenance' => ['#f1efe8','#888780','#888780'],
        ];
        [$bg,$border,$text] = $statusColors[$bed->status] ?? ['#f6f9ff','#e6eaf5','#444'];
      @endphp
      <div id="bed-{{ $bed->id }}"
           style="background:{{ $bg }};border:1.5px solid {{ $border }};border-radius:10px;
                  padding:10px;text-align:center;position:relative;cursor:pointer"
           onclick="openBedMenu({{ $bed->id }}, '{{ $bed->status }}')"
           title="{{ $bed->current_visit_code ? 'Visit: '.$bed->current_visit_code : '' }}">
        <div style="font-size:18px;margin-bottom:4px">🛏</div>
        <div style="font-size:12px;font-weight:700;color:{{ $text }}">{{ $bed->name }}</div>
        <div style="font-size:10px;color:{{ $text }};margin-top:2px">
          {{ __('app.bed.'.$bed->status) }}
        </div>
        @if($bed->current_visit_code)
        <div style="font-size:9px;color:#888;margin-top:2px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
          {{ $bed->current_visit_code }}
        </div>
        @endif
      </div>
      @endforeach
    </div>
  </div>
</div>
@endforeach

{{-- Status picker modal (simple) --}}
<div id="bedModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.4);z-index:9000;
     align-items:center;justify-content:center">
  <div style="background:#fff;border-radius:14px;padding:20px;min-width:240px;box-shadow:0 16px 48px rgba(0,0,0,.2)">
    <div style="font-size:14px;font-weight:700;color:#012970;margin-bottom:14px">
      <i class="bi bi-grid-3x3-gap-fill"></i> ប្តូរស្ថានភាពគ្រែ
    </div>
    <input type="hidden" id="modalBedId"/>
    @foreach(\App\Models\BedModel::STATUSES as $st)
    <button onclick="setStatus('{{ $st }}')"
            style="display:block;width:100%;text-align:left;padding:9px 14px;margin-bottom:6px;
                   border-radius:8px;border:1px solid #e6eaf5;background:#f6f9ff;
                   font-size:13px;cursor:pointer;font-family:var(--font)"
            class="status-btn" data-status="{{ $st }}">
      {{ __('app.bed.'.$st) }} <small style="color:#aaa">({{ $st }})</small>
    </button>
    @endforeach
    <button onclick="closeBedModal()"
            style="margin-top:4px;width:100%;padding:8px;border:none;background:none;
                   color:#888;cursor:pointer;font-size:13px;font-family:var(--font)">
      បោះបង់
    </button>
  </div>
</div>

@push('scripts')
<script>
const CSRF = document.querySelector('meta[name="csrf-token"]').content;

function openBedMenu(id, currentStatus) {
  document.getElementById('modalBedId').value = id;
  // Highlight current
  document.querySelectorAll('.status-btn').forEach(btn => {
    btn.style.fontWeight = btn.dataset.status === currentStatus ? '700' : '400';
    btn.style.borderColor = btn.dataset.status === currentStatus ? '#4154f1' : '#e6eaf5';
  });
  document.getElementById('bedModal').style.display = 'flex';
}

function closeBedModal() {
  document.getElementById('bedModal').style.display = 'none';
}

function setStatus(status) {
  const id = document.getElementById('modalBedId').value;
  fetch(`/beds/beds/${id}/status`, {
    method: 'PATCH',
    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
    body: JSON.stringify({ status })
  })
  .then(r => r.json())
  .then(data => {
    closeBedModal();
    // Refresh the card colour without full page reload
    const colors = {
      available:   ['#e8f8ef','#2eca6a'],
      occupied:    ['#fff3e8','#ff771d'],
      cleaning:    ['#f0e8ff','#9b59b6'],
      reserved:    ['#e6f1fb','#378ADD'],
      maintenance: ['#f1efe8','#888780'],
    };
    const el = document.getElementById('bed-' + id);
    if (el && colors[data.status]) {
      el.style.background = colors[data.status][0];
      el.style.borderColor = colors[data.status][1];
      el.querySelectorAll('div')[2].textContent = data.label;
    }
  })
  .catch(() => alert('Failed to update bed status'));
}

document.getElementById('bedModal').addEventListener('click', function(e) {
  if (e.target === this) closeBedModal();
});
</script>
@endpush
@endsection
