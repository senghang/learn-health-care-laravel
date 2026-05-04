@extends('clinics.layout.app')
@section('title', 'Wards & Beds')
@section('content')

    <x-ui.page-header
        km="ផ្នែក & គ្រែ"
        title="Wards & Beds"
        :breadcrumbs="[['label' => 'Dashboard', 'url' => route('dashboard')], ['label' => 'Wards & Beds']]">
        <x-slot:actions>
            <x-ui.button href="{{ route('beds.ward.create') }}" variant="primary">
                <x-slot:icon><i class="bi bi-plus-lg" aria-hidden="true"></i></x-slot:icon>
                ផ្នែកថ្មី / New Ward
            </x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    @if (session('flash'))
        <x-ui.alert type="success" class="mb-4">{{ session('flash') }}</x-ui.alert>
    @endif

    {{-- KPI bar --}}
    @php
        $totalBeds = $wards->sum('beds_count');
        $occupiedBeds = $wards->sum('occupied_count');
        $availBeds = $wards->sum('available_count');
        $occPct = $totalBeds > 0 ? round(($occupiedBeds / $totalBeds) * 100) : 0;
        $typeColors = [
            'IPD' => '#ff771d',
            'OPD' => '#4154f1',
            'ICU' => '#e74c3c',
            'Emergency' => '#e74c3c',
            'Theatre' => '#9b59b6',
            'Outpatient' => '#2eca6a',
        ];
    @endphp

    <div class="grid grid-cols-2 xl:grid-cols-4 gap-3 mb-4">
        <x-ui.stats-card km="ផ្នែក" label="Wards" :value="count($wards)" icon="bi-building-fill" color="#4154f1"
            bg="#eef0fd" />
        <x-ui.stats-card km="ស្ថានី" label="Total Beds" :value="$totalBeds" icon="bi-grid-fill" color="#2eca6a"
            bg="#e8f8ef" />
        <x-ui.stats-card km="ប្រើ" label="Occupied" :value="$occupiedBeds" icon="bi-person-fill" color="#ff771d"
            bg="#fff3e8" />
        <x-ui.stats-card km="ទំនេរ" label="Free" :value="$availBeds" icon="bi-check-circle-fill" color="#9b59b6"
            bg="#f0e8ff" />
    </div>

    {{-- Occupancy bar --}}
    @if ($totalBeds > 0)
        <x-ui.card class="mb-4" :noPadding="false">
            <div class="flex items-center justify-between mb-1.5">
                <span class="text-xs font-bold text-[#1a1f36]">Overall Occupancy</span>
                <span class="text-sm font-black"
                    style="color:{{ $occPct > 80 ? '#e74c3c' : ($occPct > 60 ? '#ff771d' : '#2eca6a') }}">{{ $occPct }}%</span>
            </div>
            <div class="h-2 bg-[#e6e9f0] rounded-full overflow-hidden">
                <div class="h-full rounded-full transition-all duration-300"
                    style="width:{{ $occPct }}%;background:{{ $occPct > 80 ? 'linear-gradient(90deg,#e74c3c,#ff6b5b)' : ($occPct > 60 ? 'linear-gradient(90deg,#ff771d,#ffaa6b)' : 'linear-gradient(90deg,#2eca6a,#5de08a)') }}">
                </div>
            </div>
            <p class="text-[10.5px] text-[#6b7280] mt-1">{{ $occupiedBeds }} occupied · {{ $availBeds }} available ·
                {{ $totalBeds }} total beds</p>
        </x-ui.card>
    @endif

    {{-- Ward cards --}}
    @forelse($wards as $ward)
        @php
            $wOcc = $ward->occupied_count;
            $wTotal = $ward->beds_count;
            $wFree = $wTotal - $wOcc;
            $wPct = $wTotal > 0 ? round(($wOcc / $wTotal) * 100) : 0;
            $tc = $typeColors[$ward->type] ?? '#4154f1';
        @endphp
        <div class="bed-ward-card">
            {{-- Ward header --}}
            <div class="bed-ward-hd">
                <div class="bed-ward-hd-left">
                    <span class="bed-ward-type"
                        style="background:{{ $tc }}22;color:{{ $tc }};border:1px solid {{ $tc }}44">
                        {{ $ward->type }}
                    </span>
                    <div>
                        <div class="bed-ward-name">
                            {{ $ward->name_kh ?? $ward->name }}
                            @if ($ward->name_kh && $ward->name !== $ward->name_kh)
                                <small>/ {{ $ward->name }}</small>
                            @endif
                        </div>
                        <div class="bed-ward-meta">
                            <code>{{ $ward->code }}</code>
                            <span>{{ $ward->rooms->count() }} rooms</span>
                            <span style="color:{{ $tc }};font-weight:700">{{ $wFree }} free</span>
                            <span>/ {{ $wTotal }} beds</span>
                            @if (!$ward->is_active)
                                <x-ui.badge variant="danger" size="sm">Inactive</x-ui.badge>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="bed-ward-hd-actions">
                    <x-ui.button href="{{ route('beds.room.create', $ward->id) }}" variant="primary" size="sm">
                        <x-slot:icon><i class="bi bi-door-open"></i></x-slot:icon>
                        Room
                    </x-ui.button>
                    <x-ui.button href="{{ route('beds.ward', $ward->id) }}" variant="primary" size="sm">
                        <x-slot:icon><i class="bi bi-grid-fill"></i></x-slot:icon>
                        Manage Beds
                    </x-ui.button>
                    <x-ui.button href="{{ route('beds.ward.edit', $ward->id) }}" variant="secondary" size="sm">
                        <x-slot:icon><i class="bi bi-pencil"></i></x-slot:icon>
                    </x-ui.button>
                    <form method="POST" action="{{ route('beds.ward.delete', $ward->id) }}" class="d-inline"
                        data-confirm="Delete ward &quot;{{ $ward->name }}&quot; and ALL rooms and beds inside? This cannot be undone."
                        data-confirm-type="danger" data-confirm-title="Delete Ward">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger" title="Delete ward">
                            <i class="bi bi-trash3"></i>
                        </button>
                    </form>
                </div>
            </div>

            {{-- Occupancy mini bar --}}
            <div style="height:3px;background:#e6e9f0">
                <div
                    style="height:100%;width:{{ $wPct }}%;background:{{ $wPct > 80 ? '#e74c3c' : ($wPct > 60 ? '#ff771d' : '#2eca6a') }};transition:width .3s">
                </div>
            </div>

            {{-- Rooms grid --}}
            <div class="bed-ward-body">
                @forelse($ward->rooms as $room)
                    <div class="bed-room-block">
                        <div class="bed-room-hd">
                            <div style="display:flex;align-items:center;gap:8px">
                                <i class="bi bi-door-open" style="color:#64748b;font-size:13px"></i>
                                <span class="bed-room-name">{{ $room->name }}</span>
                                <span style="font-size:10px;color:#6b7280;font-style:italic">
                                    {{ ucfirst($room->type) }} · Floor {{ $room->floor }}
                                </span>
                            </div>
                            <div style="display:flex;gap:4px">
                                <a href="{{ route('beds.bed.create', $ward->id) }}?room={{ $room->id }}"
                                    class="btn btn-sm btn-outline-primary" style="padding:2px 8px;font-size:11px"
                                    title="Add bed">
                                    <i class="bi bi-plus"></i>
                                </a>
                                <a href="{{ route('beds.room.edit', [$ward->id, $room->id]) }}"
                                    class="btn btn-sm btn-outline-secondary" style="padding:2px 8px;font-size:11px">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form method="POST" action="{{ route('beds.room.delete', [$ward->id, $room->id]) }}"
                                    class="d-inline"
                                    data-confirm="Delete room &quot;{{ $room->name }}&quot; and all its beds? This cannot be undone."
                                    data-confirm-type="danger" data-confirm-title="Delete Room">
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
                                        'available' => ['#e8f8ef', '#2eca6a', '✓'],
                                        'occupied' => ['#fff3e8', '#ff771d', '🛏'],
                                        'cleaning' => ['#f0e8ff', '#9b59b6', '🧹'],
                                        'reserved' => ['#e6f1fb', '#378ADD', '📅'],
                                        'maintenance' => ['#f5f5f5', '#888780', '🔧'],
                                    ];
                                    [$bbg, $bcol, $bicon] = $bColors[$bed->status] ?? ['#f6f8fa', '#aaa', '?'];
                                @endphp
                                <div class="bed-chip"
                                    style="background:{{ $bbg }};border-color:{{ $bcol }}"
                                    title="{{ $bed->name }} — {{ ucfirst($bed->status) }}{{ $bed->current_visit_code ? ' · ' . $bed->current_visit_code : '' }}"
                                    onclick="openBedMenu({{ $bed->id }}, '{{ $bed->status }}', '{{ $bed->name }}')">
                                    <div class="bed-chip-icon">{{ $bicon }}</div>
                                    <div class="bed-chip-name" style="color:{{ $bcol }}">{{ $bed->name }}
                                    </div>
                                    @if ($bed->current_visit_code)
                                        <div class="bed-chip-visit">{{ Str::limit($bed->current_visit_code, 8) }}</div>
                                    @endif
                                </div>
                            @empty
                                <div style="font-size:11px;color:#cbd5e1;padding:8px;font-style:italic">
                                    No beds — <a
                                        href="{{ route('beds.bed.create', $ward->id) }}?room={{ $room->id }}"
                                        style="color:#4154f1">add one</a>
                                </div>
                            @endforelse
                        </div>
                    </div>
                @empty
                    <div class="bed-no-rooms">
                        <i class="bi bi-door-open" style="font-size:24px;opacity:.3"></i>
                        <div style="margin-top:8px;font-size:12px;color:#6b7280">No rooms yet</div>
                        <a href="{{ route('beds.room.create', $ward->id) }}" class="btn btn-sm btn-outline-primary"
                            style="margin-top:8px">
                            <i class="bi bi-plus"></i> Add Room
                        </a>
                    </div>
                @endforelse
            </div>
        </div>
    @empty
        <x-ui.empty-state icon="bi-building" title="No wards configured yet"
            description="Start by creating your first ward">
            <x-ui.button href="{{ route('beds.ward.create') }}" variant="primary">
                <x-slot:icon><i class="bi bi-plus-lg"></i></x-slot:icon>
                Create First Ward
            </x-ui.button>
        </x-ui.empty-state>
    @endforelse

    {{-- Bed status modal --}}
    <div id="bedModal" class="emr-modal-wrap" onclick="if(event.target===this)closeBedModal()">
        <div class="emr-modal modal-sm">
            <div class="emr-modal-hd">
                <i class="bi bi-grid-3x3-gap-fill" style="color:#4154f1;font-size:16px;flex-shrink:0"></i>
                <div class="emr-modal-title"><span id="modalBedName">Bed</span></div>
                <button type="button" class="emr-modal-close" onclick="closeBedModal()">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <div class="emr-modal-body">
                <div
                    style="font-size:10.5px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#6b7280;margin-bottom:10px">
                    <i class="bi bi-arrow-left-right" style="margin-right:4px"></i> Change Status
                </div>
                @php
                    $stIcons = [
                        'available' => 'bi-check-circle-fill',
                        'occupied' => 'bi-person-fill',
                        'cleaning' => 'bi-stars',
                        'reserved' => 'bi-calendar-check-fill',
                        'maintenance' => 'bi-tools',
                    ];
                    $stColors = [
                        'available' => ['#e8f8ef', '#2eca6a'],
                        'occupied' => ['#fff3e8', '#ff771d'],
                        'cleaning' => ['#f0e8ff', '#9b59b6'],
                        'reserved' => ['#e6f1fb', '#378ADD'],
                        'maintenance' => ['#f5f5f5', '#888780'],
                    ];
                    $stLabels = [
                        'available' => 'Available',
                        'occupied' => 'Occupied',
                        'cleaning' => 'Cleaning',
                        'reserved' => 'Reserved',
                        'maintenance' => 'Maintenance',
                    ];
                @endphp
                @foreach (App\Models\BedModel::STATUSES as $st)
                    <button onclick="setStatus('{{ $st }}')" class="bed-status-btn status-modal-btn"
                        id="statusBtn_{{ $st }}"
                        style="border-left-color:{{ $stColors[$st][1] ?? '#e2e8f0' }}">
                        <span
                            style="width:28px;height:28px;border-radius:8px;background:{{ $stColors[$st][0] ?? '#f5f5f5' }};color:{{ $stColors[$st][1] ?? '#aaa' }};display:flex;align-items:center;justify-content:center;font-size:13px;flex-shrink:0">
                            <i class="bi {{ $stIcons[$st] ?? 'bi-circle' }}"></i>
                        </span>
                        <span>{{ $stLabels[$st] ?? ucfirst($st) }}</span>
                    </button>
                @endforeach
            </div>
            <input type="hidden" id="modalBedId" />
        </div>
    </div>

    <script>
        const CSRF = document.querySelector('meta[name="csrf-token"]').content;
        var currentBedId = null;

        function openBedMenu(id, status, name) {
            currentBedId = id;
            document.getElementById('modalBedId').value = id;
            document.getElementById('modalBedName').textContent = name;
            document.querySelectorAll('.status-modal-btn').forEach(function(btn) {
                btn.classList.toggle('current', btn.id === 'statusBtn_' + status);
            });
            document.getElementById('bedModal').classList.add('open');
        }

        function closeBedModal() {
            document.getElementById('bedModal').classList.remove('open');
        }

        function setStatus(status) {
            var id = document.getElementById('modalBedId').value;
            fetch('/beds/beds/' + id + '/status', {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF
                    },
                    body: JSON.stringify({
                        status
                    })
                })
                .then(function(r) {
                    return r.json();
                })
                .then(function(data) {
                    closeBedModal();
                    var colors = {
                        available: ['#e8f8ef', '#2eca6a', '✓'],
                        occupied: ['#fff3e8', '#ff771d', '🛏'],
                        cleaning: ['#f0e8ff', '#9b59b6', '🧹'],
                        reserved: ['#e6f1fb', '#378ADD', '📅'],
                        maintenance: ['#f5f5f5', '#888780', '🔧'],
                    };
                    var chip = document.querySelector('[onclick*="openBedMenu(' + id + ',"]');
                    if (!chip || !colors[data.status]) return;
                    var col = colors[data.status];
                    chip.style.background = col[0];
                    chip.style.borderColor = col[1];
                    chip.querySelector('.bed-chip-icon').textContent = col[2];
                    chip.querySelector('.bed-chip-name').style.color = col[1];
                    chip.setAttribute('onclick', 'openBedMenu(' + id + ', \'' + data.status + '\', \'' + chip
                        .querySelector('.bed-chip-name').textContent.trim() + '\')');
                    chip.setAttribute('title', chip.querySelector('.bed-chip-name').textContent.trim() + ' — ' + data
                        .label);
                })
                .catch(function() {
                    toast('Failed to update bed status. Please try again.', 'err', 'Error');
                });
        }
    </script>
@endsection
