@extends('clinics.layout.app')
@section('title', $ward->name . ' — Beds')
@section('content')

<x-page-header :title="$ward->name_kh ?? $ward->name"
    :subtitle="$ward->name . ' — Bed Management'"
    :breadcrumbs="[
        ['label'=>'ដើម','url'=>route('dashboard')],
        ['label'=>'Wards','url'=>route('beds.index')],
        ['label'=>$ward->name],
    ]">
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('beds.room.create', $ward->id) }}" class="btn btn-outline-success btn-sm">
            <i class="bi bi-door-open"></i> Add Room
        </a>
        <a href="{{ route('beds.bed.create', $ward->id) }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg"></i> Add Bed
        </a>
        <a href="{{ route('beds.index') }}" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>
</x-page-header>

@if(session('flash'))
<div class="note note-success mb-3"><i class="bi bi-check-circle-fill"></i> {{ session('flash') }}</div>
@endif

{{-- Stats bar --}}
@php
    $statCfg = [
        ['available',   'ទំនេរ',   'Available',   '#2eca6a'],
        ['occupied',    'កំពុងប្រើ','Occupied',    '#ff771d'],
        ['cleaning',    'សម្អាត',  'Cleaning',    '#9b59b6'],
        ['reserved',    'រក្សា',   'Reserved',    '#378ADD'],
        ['maintenance', 'ជួស',    'Maintenance', '#888780'],
        ['total',       'សរុប',   'Total',       '#4154f1'],
    ];
@endphp
<div class="row g-2 mb-3">
    @foreach($statCfg as [$key,$km,$en,$col])
    <div class="col-4 col-sm-2">
        <div style="background:#fff;border-radius:10px;padding:12px 8px;text-align:center;
                    box-shadow:0 2px 8px rgba(1,41,112,.06);border-left:3px solid {{ $col }}">
            <div style="font-size:22px;font-weight:800;color:{{ $col }}">{{ $stats[$key] }}</div>
            <div style="font-size:10px;color:#64748b;margin-top:2px">{{ $km }}<br><span style="color:#94a3b8">{{ $en }}</span></div>
        </div>
    </div>
    @endforeach
</div>

{{-- Rooms + Beds --}}
@forelse($rooms as $room)
@php
    $roomAvail = $room->beds->where('status','available')->count();
    $roomOcc   = $room->beds->where('status','occupied')->count();
@endphp
<div class="card-emr mb-3">
    <div class="card-hd" style="flex-wrap:wrap;gap:8px">
        <div class="card-hd-title">
            <i class="bi bi-door-open" style="color:#64748b"></i>
            {{ $room->name }}
            <span style="font-size:11px;color:#94a3b8;font-weight:400">
                {{ ucfirst($room->type) }} · Floor {{ $room->floor }}
            </span>
            <span style="font-size:10.5px;color:#2eca6a;font-weight:700">{{ $roomAvail }} free</span>
            <span style="font-size:10.5px;color:#94a3b8">/ {{ $room->beds->count() }} beds</span>
        </div>
        <div style="display:flex;gap:6px;align-items:center;flex-shrink:0">
            <a href="{{ route('beds.bed.create', $ward->id) }}?room={{ $room->id }}"
               class="btn btn-sm btn-outline-primary" style="font-size:11.5px;padding:4px 10px">
                <i class="bi bi-plus"></i> Add Bed
            </a>
            <a href="{{ route('beds.room.edit', [$ward->id, $room->id]) }}"
               class="btn btn-sm btn-outline-secondary" style="padding:4px 10px">
                <i class="bi bi-pencil" style="font-size:11px"></i>
            </a>
            <form method="POST" action="{{ route('beds.room.delete', [$ward->id, $room->id]) }}"
                  class="d-inline" onsubmit="return confirm('Delete room and all beds inside?')">
                @csrf @method('DELETE')
                <button class="btn btn-sm btn-outline-danger" style="padding:4px 10px">
                    <i class="bi bi-trash3" style="font-size:11px"></i>
                </button>
            </form>
        </div>
    </div>

    <div class="card-bd">
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(110px,1fr));gap:10px">
            @forelse($room->beds as $bed)
            @php
                $bCfg = [
                    'available'   => ['#e8f8ef','#2eca6a','✓ Free'],
                    'occupied'    => ['#fff3e8','#ff771d','🛏 Occupied'],
                    'cleaning'    => ['#f0e8ff','#9b59b6','🧹 Cleaning'],
                    'reserved'    => ['#e6f1fb','#378ADD','📅 Reserved'],
                    'maintenance' => ['#f5f5f5','#888780','🔧 Maint.'],
                ];
                [$bg,$col,$lbl] = $bCfg[$bed->status] ?? ['#f6f9ff','#aaa','?'];
            @endphp
            <div style="background:{{ $bg }};border:1.5px solid {{ $col }};border-radius:10px;
                        padding:12px 8px;text-align:center;position:relative">

                {{-- Status pill --}}
                <div style="font-size:9px;font-weight:700;color:{{ $col }};margin-bottom:6px">{{ $lbl }}</div>

                {{-- Bed name --}}
                <div style="font-size:14px;font-weight:800;color:#012970;margin-bottom:4px">{{ $bed->name }}</div>
                <div style="font-size:10px;color:#94a3b8;margin-bottom:4px;font-family:monospace">{{ $bed->code }}</div>

                @if($bed->current_visit_code)
                <div style="font-size:9.5px;color:#ff771d;font-weight:600;margin-bottom:6px;
                            background:#fff3e8;border-radius:5px;padding:2px 5px">
                    {{ $bed->current_visit_code }}
                </div>
                @endif

                {{-- Actions --}}
                <div style="display:flex;gap:5px;justify-content:center;margin-top:6px">
                    <button onclick="openBedMenu({{ $bed->id }}, '{{ $bed->status }}', '{{ $bed->name }}')"
                            class="btn btn-sm" style="font-size:10px;padding:3px 8px;border:1px solid {{ $col }};color:{{ $col }};background:transparent">
                        Status
                    </button>
                    <a href="{{ route('beds.bed.edit', [$ward->id, $bed->id]) }}"
                       class="btn btn-sm btn-outline-secondary" style="font-size:10px;padding:3px 8px">
                        <i class="bi bi-pencil"></i>
                    </a>
                    <form method="POST" action="{{ route('beds.bed.delete', [$ward->id, $bed->id]) }}"
                          class="d-inline" onsubmit="return confirm('Delete bed {{ $bed->name }}?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger" style="font-size:10px;padding:3px 8px">
                            <i class="bi bi-trash3"></i>
                        </button>
                    </form>
                </div>
            </div>
            @empty
            <div style="grid-column:1/-1;text-align:center;padding:20px;color:#cbd5e1;font-size:12px">
                No beds in this room.
                <a href="{{ route('beds.bed.create', $ward->id) }}?room={{ $room->id }}" style="color:#4154f1">
                    Add first bed →
                </a>
            </div>
            @endforelse
        </div>
    </div>
</div>
@empty
<div style="text-align:center;padding:56px 24px;color:#bbb">
    <i class="bi bi-door-open" style="font-size:44px;opacity:.3;display:block;margin-bottom:12px"></i>
    <div style="font-size:14px;font-weight:600;color:#94a3b8;margin-bottom:6px">No rooms in this ward yet</div>
    <div style="font-size:12px;color:#cbd5e1;margin-bottom:20px">Add rooms first, then add beds inside them</div>
    <a href="{{ route('beds.room.create', $ward->id) }}" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Add First Room
    </a>
</div>
@endforelse

{{-- Status modal --}}
<div id="bedModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:9000;align-items:center;justify-content:center">
    <div style="background:#fff;border-radius:16px;min-width:280px;max-width:320px;box-shadow:0 20px 60px rgba(0,0,0,.25);overflow:hidden">
        <div style="padding:14px 18px;border-bottom:1px solid #f0f2ff;display:flex;align-items:center;justify-content:space-between">
            <div style="font-size:14px;font-weight:700;color:#012970">
                🛏 <span id="modalBedName">Bed</span>
            </div>
            <button onclick="closeBedModal()" style="background:none;border:none;font-size:20px;color:#94a3b8;cursor:pointer;padding:0;line-height:1">×</button>
        </div>
        <div style="padding:12px 16px">
            <div style="font-size:10.5px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#94a3b8;margin-bottom:8px">Set Status</div>
            @foreach(\App\Models\BedModel::STATUSES as $st)
            @php
                $stIcons  = ['available'=>'✓','occupied'=>'🛏','cleaning'=>'🧹','reserved'=>'📅','maintenance'=>'🔧'];
                $stColors = ['available'=>'#2eca6a','occupied'=>'#ff771d','cleaning'=>'#9b59b6','reserved'=>'#378ADD','maintenance'=>'#888780'];
            @endphp
            <button onclick="setStatus('{{ $st }}')"
                    id="statusBtn_{{ $st }}"
                    style="display:flex;align-items:center;gap:10px;width:100%;text-align:left;
                           padding:10px 14px;margin-bottom:5px;border-radius:8px;
                           border:1px solid #e2e8f0;background:#f8fafc;
                           font-size:13px;font-weight:600;color:#374151;cursor:pointer;
                           border-left:3px solid {{ $stColors[$st] ?? '#e2e8f0' }};
                           font-family:inherit;transition:background .12s">
                <span style="font-size:16px">{{ $stIcons[$st] ?? '?' }}</span>
                <span>{{ ucfirst($st) }}</span>
            </button>
            @endforeach
        </div>
        <input type="hidden" id="modalBedId"/>
    </div>
</div>

@push('scripts')
<script>
const CSRF = document.querySelector('meta[name="csrf-token"]').content;

function openBedMenu(id, status, name) {
    document.getElementById('modalBedId').value = id;
    document.getElementById('modalBedName').textContent = name;
    document.querySelectorAll('[id^="statusBtn_"]').forEach(function(btn) {
        var s = btn.id.replace('statusBtn_', '');
        btn.style.background    = s === status ? '#f0f4ff' : '#f8fafc';
        btn.style.borderColor   = s === status ? '#4154f1' : '#e2e8f0';
        btn.style.color         = s === status ? '#4154f1' : '#374151';
        btn.querySelector('span:last-child').textContent = ucfirst(s) + (s === status ? ' ✓' : '');
    });
    document.getElementById('bedModal').style.display = 'flex';
}

function closeBedModal() {
    document.getElementById('bedModal').style.display = 'none';
}

function ucfirst(s) { return s.charAt(0).toUpperCase() + s.slice(1); }

function setStatus(status) {
    var id = document.getElementById('modalBedId').value;
    fetch('/beds/beds/' + id + '/status', {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body: JSON.stringify({ status })
    })
    .then(function(r) { return r.json(); })
    .then(function() {
        closeBedModal();
        window.location.reload(); // simplest — status change reflects correctly
    })
    .catch(function() { alert('Failed to update status'); });
}

document.getElementById('bedModal').addEventListener('click', function(e) {
    if (e.target === this) closeBedModal();
});
</script>
@endpush

@endsection
