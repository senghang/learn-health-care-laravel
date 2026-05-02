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
                  class="d-inline"
                  data-confirm="Delete room and all its beds? This cannot be undone."
                  data-confirm-type="danger" data-confirm-title="Delete Room">
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
                          class="d-inline"
                          data-confirm="Delete bed &quot;{{ $bed->name }}&quot;? This cannot be undone."
                          data-confirm-type="danger" data-confirm-title="Delete Bed">
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

{{-- Bed status modal --}}
<div id="bedModal" class="emr-modal-wrap" onclick="if(event.target===this)closeBedModal()">
    <div class="emr-modal modal-sm">
        <div class="emr-modal-hd">
            <i class="bi bi-bed-fill" style="color:#4154f1;font-size:16px;flex-shrink:0"></i>
            <div class="emr-modal-title">🛏 <span id="modalBedName">Bed</span></div>
            <button type="button" class="emr-modal-close" onclick="closeBedModal()">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <div class="emr-modal-body">
            <div style="font-size:10.5px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#94a3b8;margin-bottom:10px">
                <i class="bi bi-circle-half" style="margin-right:4px"></i> Set Status
            </div>
            @php
                $stIcons  = ['available'=>'bi-check-circle-fill','occupied'=>'bi-person-fill','cleaning'=>'bi-stars','reserved'=>'bi-calendar-check-fill','maintenance'=>'bi-tools'];
                $stColors = ['available'=>['#e8f8ef','#2eca6a'],'occupied'=>['#fff3e8','#ff771d'],'cleaning'=>['#f0e8ff','#9b59b6'],'reserved'=>['#e6f1fb','#378ADD'],'maintenance'=>['#f5f5f5','#888780']];
                $stLabels = ['available'=>'Available','occupied'=>'Occupied','cleaning'=>'Cleaning','reserved'=>'Reserved','maintenance'=>'Maintenance'];
            @endphp
            @foreach(\App\Models\BedModel::STATUSES as $st)
            <button onclick="setStatus('{{ $st }}')"
                    id="statusBtn_{{ $st }}"
                    class="bed-status-btn"
                    style="border-left-color:{{ $stColors[$st][1] ?? '#e2e8f0' }}">
                <span style="width:28px;height:28px;border-radius:8px;background:{{ $stColors[$st][0] ?? '#f5f5f5' }};color:{{ $stColors[$st][1] ?? '#aaa' }};display:flex;align-items:center;justify-content:center;font-size:13px;flex-shrink:0">
                    <i class="bi {{ $stIcons[$st] ?? 'bi-circle' }}"></i>
                </span>
                <span>{{ $stLabels[$st] ?? ucfirst($st) }}</span>
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
        var active = (s === status);
        btn.style.background = active ? '#f0f4ff' : '#f8fafc';
        btn.style.borderColor = active ? '#4154f1' : '#e6eaf5';
        btn.style.color = active ? '#4154f1' : '#374151';
        var lbl = btn.querySelector('span:last-child');
        if (lbl) lbl.innerHTML = (active ? '<strong>' : '') + lbl.textContent.replace(' ✓','') + (active ? '</strong> <i class="bi bi-check2" style="color:#4154f1"></i>' : '');
    });
    document.getElementById('bedModal').classList.add('open');
}

function closeBedModal() {
    document.getElementById('bedModal').classList.remove('open');
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
    .catch(function() { toast('Failed to update bed status. Please try again.', 'err', 'Error'); });
}

</script>
@endpush

@endsection
