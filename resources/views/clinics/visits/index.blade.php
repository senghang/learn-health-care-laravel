@extends('clinics.layout.app')

@section('title', 'ការចូលព្យាបាល')

@section('content')

    <x-ui.page-header
        km="ការចូលព្យាបាល"
        title="Patient Visits"
        :breadcrumbs="[
            ['label' => 'ដើម', 'url' => url('/')],
            ['label' => 'Visits'],
        ]">
        <x-slot:actions>
            <x-ui.button href="{{ url('/workflow/create') }}" variant="primary">
                <x-slot:icon><i class="bi bi-plus-lg"></i></x-slot:icon>
                ថ្មី / New
            </x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.card>
        <form method="GET" action="{{ url('/visits') }}">
            <div class="row g-2 align-items-end">
                <div class="col-12 col-sm-6 col-md-4">
                    <label class="flbl"><span class="km">ស្វែងរក</span><span class="en">/ Search</span></label>
                    <div style="position:relative">
                        <i class="bi bi-search" style="position:absolute;left:11px;top:50%;transform:translateY(-50%);color:#bbb;font-size:13px"></i>
                        <input type="text" name="search" class="form-control" style="padding-left:32px"
                               placeholder="ឈ្មោះ ឬ លេខ…" value="{{ request('search') }}"/>
                    </div>
                </div>
                <div class="col-6 col-sm-3 col-md-2">
                    <label class="flbl"><span class="km">ប្រភេទ</span><span class="en">/ Type</span></label>
                    <select name="type" class="form-select">
                        <option value="">ទាំងអស់</option>
                        <option value="OPD" {{ request('type') === 'OPD' ? 'selected' : '' }}>OPD</option>
                        <option value="IPD" {{ request('type') === 'IPD' ? 'selected' : '' }}>IPD</option>
                    </select>
                </div>
                <div class="col-6 col-sm-3 col-md-2">
                    <label class="flbl"><span class="km">ស្ថានភាព</span><span class="en">/ Status</span></label>
                    <select name="status" class="form-select">
                        <option value="">ទាំងអស់</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="done"   {{ request('status') === 'done'   ? 'selected' : '' }}>Done</option>
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="flbl"><span class="km">ថ្ងៃ</span><span class="en">/ Date</span></label>
                    <input type="date" name="date" class="form-control" value="{{ request('date') }}"/>
                </div>
                <div class="col-6 col-md-2">
                    <x-ui.button type="submit" variant="primary" :full-width="true">
                        <x-slot:icon><i class="bi bi-funnel-fill"></i></x-slot:icon>
                        តម្រង
                    </x-ui.button>
                </div>
            </div>
        </form>
    </x-ui.card>

    @if($visits->total() > 0)
        <div style="font-size:11px;color:#aaa;padding:6px 2px;margin-bottom:4px">
            បង្ហាញ {{ $visits->firstItem() }}–{{ $visits->lastItem() }} នៃ {{ $visits->total() }}
            @if(request()->hasAny(['search','type','status','date']))
                · <a href="{{ url('/visits') }}" style="color:#e74c3c;text-decoration:none">
                    <i class="bi bi-x-circle"></i> លុបតម្រង
                </a>
            @endif
        </div>
    @endif

    @forelse($visits as $visit)
        @php
            $initials = strtoupper(substr($visit->surname ?? '', 0, 1) . substr($visit->name ?? '', 0, 1));
            $colors   = ['#4154f1','#2eca6a','#ff771d','#e74c3c','#9b59b6','#00bcd4'];
            $color    = $colors[abs(crc32($visit->patient_code)) % count($colors)];
            $isActive = is_null($visit->discharged_at);
        @endphp
        {{-- FIXED: url() instead of route() --}}
        <div class="visit-row" onclick="window.location='{{ url('/workflow/'.$visit->code) }}'">
            <div class="v-avatar" style="background:linear-gradient(135deg,{{ $color }},{{ $color }}cc)">
                {{ $initials ?: '?' }}
            </div>
            <div class="v-info">
                <div class="v-name">{{ $visit->surname }}, {{ $visit->name }}</div>
                <div class="v-meta">
                    {{ $visit->patient_code }} · {{ $visit->code }}
                    · {{ df_dt($visit->admitted_at) ?: '—' }}
                    @if($visit->steps_done > 0)
                        · <span style="color:#4154f1;font-weight:600">{{ $visit->steps_done }}/10 steps</span>
                    @endif
                </div>
                @if($visit->steps_done > 0)
                    <x-progress-bar :done="$visit->steps_done" :total="10" width="120px"/>
                @endif
            </div>
            <div class="v-badges">
                <x-ui.badge :variant="$visit->visit_type === 'IPD' ? 'warning' : 'primary'">{{ $visit->visit_type }}</x-ui.badge>
                @if($isActive)
                    <x-ui.badge variant="success" dot class="d-none d-sm-inline-flex">Active</x-ui.badge>
                @else
                    <x-ui.badge variant="secondary" class="d-none d-sm-inline-flex">Done</x-ui.badge>
                @endif
            </div>
            <i class="bi bi-chevron-right" style="color:#ddd;flex-shrink:0"></i>
        </div>
    @empty
        <x-ui.empty-state icon="bi-hospital" title="{{ request()->hasAny(['search','type','status','date']) ? 'No visits match your filters' : 'No visits yet' }}" description="{{ request()->hasAny(['search','type','status','date']) ? 'Try adjusting your search or filters' : 'Register a new OPD visit to get started' }}">
            @if(request()->hasAny(['search','type','status','date']))
                <x-ui.button href="{{ url('/visits') }}" variant="secondary" size="sm">
                    <x-slot:icon><i class="bi bi-x-circle"></i></x-slot:icon>
                    Clear Filters
                </x-ui.button>
            @else
                <x-ui.button href="{{ url('/workflow/create') }}" variant="primary" size="sm">
                    <x-slot:icon><i class="bi bi-plus-lg"></i></x-slot:icon>
                    New Visit
                </x-ui.button>
            @endif
        </x-ui.empty-state>
    @endforelse

    <x-ui.pagination :paginator="$visits" />

@endsection
