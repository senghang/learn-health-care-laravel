@extends('clinics.layout.app')
@section('title', 'Wards & Beds')
@section('content')

<x-page-header title="ផ្នែក & គ្រែ" subtitle="Wards & Beds"
    :breadcrumbs="[['label'=>'ដើម','url'=>route('dashboard')],['label'=>'Wards & Beds']]">
    <a href="{{ route('beds.ward.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> ផ្នែកថ្មី / New Ward
    </a>
</x-page-header>

@if(session('flash'))
<div class="note note-success mb-3"><i class="bi bi-check-circle-fill"></i> {{ session('flash') }}</div>
@endif

{{-- KPI bar --}}
@php
    $totalBeds    = $wards->sum('beds_count');
    $occupiedBeds = $wards->sum('occupied_count');
    $availBeds    = $wards->sum('available_count');
    $occPct       = $totalBeds > 0 ? round($occupiedBeds / $totalBeds * 100) : 0;
    $typeColors   = ['IPD'=>'#ff771d','OPD'=>'#4154f1','ICU'=>'#e74c3c','Emergency'=>'#e74c3c','Theatre'=>'#9b59b6','Outpatient'=>'#2eca6a'];
@endphp

<div class="row g-3 mb-3">
    @foreach([
        ['ផ្នែក','Wards',    count($wards),   'bi-building-fill',    '#4154f1','#eef0fd'],
        ['ស្ថានី','Total',    $totalBeds,      'bi-grid-fill',        '#2eca6a','#e8f8ef'],
        ['ប្រើ','Occupied',  $occupiedBeds,   'bi-person-fill',      '#ff771d','#fff3e8'],
        ['ទំនេរ','Free',     $availBeds,      'bi-check-circle-fill','#9b59b6','#f0e8ff'],
    ] as [$km,$en,$val,$icon,$color,$bg])
    <div class="col-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:{{ $bg }};color:{{ $color }}">
                <i class="bi {{ $icon }}"></i>
            </div>
            <div>
                <div class="stat-num" style="color:{{ $color }}">{{ $val }}</div>
                <div class="stat-lbl">{{ $km }}<br><small>{{ $en }}</small></div>
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- Occupancy bar --}}
@if($totalBeds > 0)
<div class="card-emr mb-3">
    <div class="card-bd" style="padding:14px 20px">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px">
            <span style="font-size:12.5px;font-weight:700;color:#012970">Overall Occupancy</span>
            <span style="font-size:15px;font-weight:800;color:{{ $occPct > 80 ? '#e74c3c' : ($occPct > 60 ? '#ff771d' : '#2eca6a') }}">{{ $occPct }}%</span>
        </div>
        <div style="height:8px;background:#f0f2ff;border-radius:4px;overflow:hidden">
            <div style="height:100%;width:{{ $occPct }}%;background:{{ $occPct > 80 ? 'linear-gradient(90deg,#e74c3c,#ff6b5b)' : ($occPct > 60 ? 'linear-gradient(90deg,#ff771d,#ffaa6b)' : 'linear-gradient(90deg,#2eca6a,#5de08a)') }};border-radius:4px;transition:width .4s"></div>
        </div>
        <div style="font-size:10.5px;color:#aaa;margin-top:5px">{{ $occupiedBeds }} occupied · {{ $availBeds }} available · {{ $totalBeds }} total beds</div>
    </div>
</div>
@endif

{{-- Ward cards --}}
@forelse($wards as $ward)
@php
    $wOcc   = $ward->occupied_count;
    $wTotal = $ward->beds_count;
    $wFree  = $wTotal - $wOcc;
    $wPct   = $wTotal > 0 ? round($wOcc / $wTotal * 100) : 0;
    $tc     = $typeColors[$ward->type] ?? '#4154f1';
@endphp
<div class="bed-ward-card">
    {{-- Ward header --}}
    <div class="bed-ward-hd">
        <div class="bed-ward-hd-left">
            <span class="bed-ward-type" style="background:{{ $tc }}22;color:{{ $tc }};border:1px solid {{ $tc }}44">
                {{ $ward->type }}
            </span>
            <div>
                <div class="bed-ward-name">
                    {{ $ward->name_kh ?? $ward->name }}
                    @if($ward->name_kh && $ward->name !== $ward->name_kh)
                        <small>/ {{ $ward->name }}</small>
                    @endif
                </div>
                <div class="bed-ward-meta">
                    <code>{{ $ward->code }}</code>
                    <span>{{ $ward->rooms->count() }} rooms</span>
                    <span style="color:{{ $tc }};font-weight:700">{{ $wFree }} free</span>
                    <span>/ {{ $wTotal }} beds</span>
                    @if(!$ward->is_active)
                        <span class="badge-s b-done" style="background:#fde8e8;color:#dc2626">Inactive</span>
                    @endif
                </div>
            </div>
        </div>
        <div class="bed-ward-hd-actions">
            <a href="{{ route('beds.room.create', $ward->id) }}"
               class="btn btn-sm btn-outline-success" title="Add Room">
                <i class="bi bi-door-open"></i> Room
            </a>
            <a href="{{ route('beds.ward', $ward->id) }}"
               class="btn btn-sm btn-outline-primary">
                <i class="bi bi-grid-fill"></i> Manage Beds
            </a>
            <a href="{{ route('beds.ward.edit', $ward->id) }}"
               class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-pencil"></i>
            </a>
            <form method="POST" action="{{ route('beds.ward.delete', $ward->id) }}" class="d-inline"
                  onsubmit="return confirm('Delete ward and all rooms/beds?')">
                @csrf @method('DELETE')
                <button class="btn btn-sm btn-outline-danger" title="Delete ward">
                    <i class="bi bi-trash3"></i>
                </button>
            </form>
        </div>
    </div>

    {{-- Occupancy mini bar --}}
    <div style="height:3px;background:#f0f2ff">
        <div style="height:100%;width:{{ $wPct }}%;background:{{ $wPct > 80 ? '#e74c3c' : ($wPct > 60 ? '#ff771d' : '#2eca6a') }};transition:width .3s"></div>
    </div>

    {{-- Rooms grid --}}
    <div class="bed-ward-body">
        @forelse($ward->rooms as $room)
        <div class="bed-room-block">
            <div class="bed-room-hd">
                <div style="display:flex;align-items:center;gap:8px">
                    <i class="bi bi-door-open" style="color:#64748b;font-size:13px"></i>
                    <span class="bed-room-name">{{ $room->name }}</span>
                    <span style="font-size:10px;color:#94a3b8;font-style:italic">
                        {{ ucfirst($room->type) }} · Floor {{ $room->floor }}
                    </span>
                </div>
                <div style="display:flex;gap:4px">
                    <a href="{{ route('beds.bed.create', $ward->id) }}?room={{ $room->id }}"
                       class="btn btn-sm btn-outline-primary" style="padding:2px 8px;font-size:11px" title="Add bed">
                        <i class="bi bi-plus"></i>
                    </a>
                    <a href="{{ route('beds.room.edit', [$ward->id, $room->id]) }}"
                       class="btn btn-sm btn-outline-secondary" style="padding:2px 8px;font-size:11px">
                        <i class="bi bi-pencil"></i>
                    </a>
                    <form method="POST" action="{{ route('beds.room.delete', [$ward->id, $room->id]) }}" class="d-inline"
                          onsubmit="return confirm('Delete this room and all its beds?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger" style="padding:2px 8px;font-size:11px">
                            <i class="bi bi-trash3"></i>
                        </button>
                    </form>
                </div>
            </div>

            {{-- Bed grid --}}
            <div class="bed-grid">
                @forelse($room->beds as $bed)
                @php
                    $bColors = [
                        'available'   => ['#e8f8ef','#2eca6a','✓'],
                        'occupied'    => ['#fff3e8','#ff771d','🛏'],
                        'cleaning'    => ['#f0e8ff','#9b59b6','🧹'],
                        'reserved'    => ['#e6f1fb','#378ADD','📅'],
                        'maintenance' => ['#f5f5f5','#888780','🔧'],
                    ];
                    [$bbg,$bcol,$bicon] = $bColors[$bed->status] ?? ['#f6f9ff','#aaa','?'];
                @endphp
                <div class="bed-chip"
                     style="background:{{ $bbg }};border-color:{{ $bcol }}"
                     title="{{ $bed->name }} — {{ ucfirst($bed->status) }}{{ $bed->current_visit_code ? ' · '.$bed->current_visit_code : '' }}"
                     onclick="openBedMenu({{ $bed->id }}, '{{ $bed->status }}', '{{ $bed->name }}')">
                    <div class="bed-chip-icon">{{ $bicon }}</div>
                    <div class="bed-chip-name" style="color:{{ $bcol }}">{{ $bed->name }}</div>
                    @if($bed->current_visit_code)
                        <div class="bed-chip-visit">{{ Str::limit($bed->current_visit_code, 8) }}</div>
                    @endif
                </div>
                @empty
                <div style="font-size:11px;color:#cbd5e1;padding:8px;font-style:italic">
                    No beds — <a href="{{ route('beds.bed.create', $ward->id) }}?room={{ $room->id }}" style="color:#4154f1">add one</a>
                </div>
                @endforelse
            </div>
        </div>
        @empty
        <div class="bed-no-rooms">
            <i class="bi bi-door-open" style="font-size:24px;opacity:.3"></i>
            <div style="margin-top:8px;font-size:12px;color:#94a3b8">No rooms yet</div>
            <a href="{{ route('beds.room.create', $ward->id) }}" class="btn btn-sm btn-outline-primary" style="margin-top:8px">
                <i class="bi bi-plus"></i> Add Room
            </a>
        </div>
        @endforelse
    </div>
</div>
@empty
<div style="text-align:center;padding:64px 24px;color:#bbb">
    <div style="font-size:52px;margin-bottom:12px;opacity:.3">🏥</div>
    <div style="font-size:15px;font-weight:700;color:#94a3b8;margin-bottom:6px">No wards configured yet</div>
    <div style="font-size:12px;color:#cbd5e1;margin-bottom:20px">Start by creating your first ward</div>
    <a href="{{ route('beds.ward.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Create First Ward
    </a>
</div>
@endforelse

{{-- Bed status modal --}}
<div id="bedModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:9000;align-items:center;justify-content:center">
    <div style="background:#fff;border-radius:16px;padding:0;min-width:280px;max-width:340px;box-shadow:0 20px 60px rgba(0,0,0,.25);overflow:hidden">
        <div style="padding:16px 20px;border-bottom:1px solid #f0f2ff;display:flex;align-items:center;justify-content:space-between">
            <div style="font-size:14px;font-weight:700;color:#012970">
                <i class="bi bi-grid-3x3-gap-fill"></i>
                <span id="modalBedName">Bed</span>
            </div>
            <button onclick="closeBedModal()" style="background:none;border:none;font-size:18px;color:#94a3b8;cursor:pointer;padding:0;line-height:1">×</button>
        </div>
        <div style="padding:12px 16px">
            <div style="font-size:10.5px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#94a3b8;margin-bottom:8px">Change Status</div>
            @foreach(App\Models\BedModel::STATUSES as $st)
            @php
                $stIcons = ['available'=>'✓','occupied'=>'🛏','cleaning'=>'🧹','reserved'=>'📅','maintenance'=>'🔧'];
                $stColors = ['available'=>'#2eca6a','occupied'=>'#ff771d','cleaning'=>'#9b59b6','reserved'=>'#378ADD','maintenance'=>'#888780'];
            @endphp
            <button onclick="setStatus('{{ $st }}')"
                    class="status-modal-btn" id="statusBtn_{{ $st }}"
                    style="border-left:3px solid {{ $stColors[$st] ?? '#e2e8f0' }}">
                <span style="font-size:16px">{{ $stIcons[$st] ?? '?' }}</span>
                <span>{{ ucfirst($st) }}</span>
            </button>
            @endforeach
        </div>
        <input type="hidden" id="modalBedId"/>
    </div>
</div>

<style>
.bed-ward-card {
    background:#fff;border-radius:14px;
    box-shadow:0 2px 16px rgba(1,41,112,.07);
    border:1.5px solid #f0f2ff;
    margin-bottom:16px;overflow:hidden;
}
.bed-ward-hd {
    display:flex;align-items:flex-start;justify-content:space-between;
    flex-wrap:wrap;gap:10px;padding:16px 20px;
    background:#fafbff;border-bottom:1px solid #f0f2ff;
}
.bed-ward-hd-left { display:flex;align-items:flex-start;gap:12px;flex:1;min-width:0; }
.bed-ward-type {
    display:inline-block;font-size:10px;font-weight:800;padding:4px 10px;
    border-radius:20px;flex-shrink:0;margin-top:2px;
}
.bed-ward-name { font-size:15px;font-weight:800;color:#012970; }
.bed-ward-name small { font-weight:400;color:#94a3b8;font-size:12px; }
.bed-ward-meta {
    display:flex;flex-wrap:wrap;gap:8px;align-items:center;
    font-size:11px;color:#64748b;margin-top:3px;
}
.bed-ward-meta code { font-size:10.5px;background:#eef0fd;color:#4154f1;padding:1px 6px;border-radius:4px; }
.bed-ward-hd-actions { display:flex;gap:6px;align-items:center;flex-wrap:wrap;flex-shrink:0; }
.bed-ward-body { padding:16px 20px; }
.bed-room-block {
    background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;
    padding:12px 14px;margin-bottom:10px;
}
.bed-room-block:last-child { margin-bottom:0; }
.bed-room-hd {
    display:flex;align-items:center;justify-content:space-between;
    flex-wrap:wrap;gap:6px;margin-bottom:10px;
}
.bed-room-name { font-size:13px;font-weight:700;color:#374151; }
.bed-grid {
    display:flex;flex-wrap:wrap;gap:7px;
}
.bed-chip {
    width:72px;border-radius:8px;border:1.5px solid #e2e8f0;
    padding:8px 4px;text-align:center;cursor:pointer;
    transition:transform .12s,box-shadow .12s;
    display:flex;flex-direction:column;align-items:center;gap:2px;
}
.bed-chip:hover { transform:translateY(-2px);box-shadow:0 4px 12px rgba(0,0,0,.1); }
.bed-chip-icon  { font-size:18px;line-height:1; }
.bed-chip-name  { font-size:10.5px;font-weight:700;line-height:1.2;word-break:break-word; }
.bed-chip-visit { font-size:9px;color:#94a3b8;font-family:monospace; }
.bed-no-rooms { text-align:center;padding:24px;color:#94a3b8; }
.status-modal-btn {
    display:flex;align-items:center;gap:10px;
    width:100%;text-align:left;padding:10px 14px;
    margin-bottom:5px;border-radius:8px;
    border:1px solid #e2e8f0;background:#f8fafc;
    font-size:13px;cursor:pointer;font-weight:600;color:#374151;
    font-family:inherit;transition:background .12s;
}
.status-modal-btn:hover   { background:#f0f4ff; }
.status-modal-btn.current { background:#eef0fd;border-color:#4154f1;color:#4154f1; }
</style>

<script>
const CSRF = document.querySelector('meta[name="csrf-token"]').content;
var currentBedId = null;

function openBedMenu(id, status, name) {
    currentBedId = id;
    document.getElementById('modalBedId').value  = id;
    document.getElementById('modalBedName').textContent = name;
    document.querySelectorAll('.status-modal-btn').forEach(function(btn) {
        btn.classList.toggle('current', btn.id === 'statusBtn_' + status);
    });
    document.getElementById('bedModal').style.display = 'flex';
}
function closeBedModal() { document.getElementById('bedModal').style.display = 'none'; }

function setStatus(status) {
    var id = document.getElementById('modalBedId').value;
    fetch('/beds/beds/' + id + '/status', {
        method:'PATCH',
        headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF},
        body:JSON.stringify({ status })
    })
    .then(function(r){ return r.json(); })
    .then(function(data) {
        closeBedModal();
        // Update the chip visuals without reload
        var colors = {
            available:   ['#e8f8ef','#2eca6a','✓'],
            occupied:    ['#fff3e8','#ff771d','🛏'],
            cleaning:    ['#f0e8ff','#9b59b6','🧹'],
            reserved:    ['#e6f1fb','#378ADD','📅'],
            maintenance: ['#f5f5f5','#888780','🔧'],
        };
        var chip = document.querySelector('[onclick*="openBedMenu('+id+',"]');
        if (!chip || !colors[data.status]) return;
        var col = colors[data.status];
        chip.style.background   = col[0];
        chip.style.borderColor  = col[1];
        chip.querySelector('.bed-chip-icon').textContent  = col[2];
        chip.querySelector('.bed-chip-name').style.color  = col[1];
        chip.setAttribute('onclick', 'openBedMenu('+id+', \''+data.status+'\', \''+chip.querySelector('.bed-chip-name').textContent.trim()+'\')');
        chip.setAttribute('title', chip.querySelector('.bed-chip-name').textContent.trim() + ' — ' + data.label);
    })
    .catch(function(){ alert('Failed to update bed status'); });
}

document.getElementById('bedModal').addEventListener('click', function(e) {
    if (e.target === this) closeBedModal();
});
</script>
@endsection
