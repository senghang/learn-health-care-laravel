@extends('clinics.layout.app')

@section('title', 'ការចូលព្យាបាល')

@section('content')

    <x-page-header
        title="ការចូលព្យាបាល"
        subtitle="Patient Visits"
        :breadcrumbs="[
            ['label' => 'ដើម', 'url' => url('/')],
            ['label' => 'Visits'],
        ]"
    >
        <a href="{{ url('/workflow/create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> ថ្មី / New
        </a>
    </x-page-header>

    <div class="card-emr">
        <div class="card-bd">
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
                        <button type="submit" class="btn btn-primary btn-w100">
                            <i class="bi bi-funnel-fill"></i> តម្រង
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

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
                    · {{ $visit->admitted_at?->format('d/m/Y H:i') ?? '—' }}
                    @if($visit->steps_done > 0)
                        · <span style="color:#4154f1;font-weight:600">{{ $visit->steps_done }}/10 steps</span>
                    @endif
                </div>
                @if($visit->steps_done > 0)
                    <x-progress-bar :done="$visit->steps_done" :total="10" width="120px"/>
                @endif
            </div>
            <div class="v-badges">
                <x-status-badge :status="strtolower($visit->visit_type)" :label="$visit->visit_type"/>
                @if($isActive)
                    <x-status-badge status="active" class="d-none d-sm-inline-flex"/>
                @else
                    <x-status-badge status="done" class="d-none d-sm-inline-flex"/>
                @endif
            </div>
            <i class="bi bi-chevron-right" style="color:#ddd;flex-shrink:0"></i>
        </div>
    @empty
        <div style="text-align:center;padding:56px 24px;color:#aaa">
            <div style="font-size:48px;margin-bottom:12px;opacity:.3">🏥</div>
            <div style="font-size:16px;font-weight:700;color:#bbb;margin-bottom:6px">
                @if(request()->hasAny(['search','type','status','date']))
                    រកមិនឃើញ / No visits match your filters
                @else
                    មិនទាន់មានការចូលព្យាបាល / No visits yet
                @endif
            </div>
            @if(request()->hasAny(['search','type','status','date']))
                <a href="{{ url('/visits') }}" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-x-circle"></i> លុបតម្រង
                </a>
            @else
                <a href="{{ url('/workflow/create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i> ការចូលព្យាបាលថ្មី / New Visit
                </a>
            @endif
        </div>
    @endforelse

    @if($visits->hasPages())
        <div class="mt-3">{{ $visits->links() }}</div>
    @endif

@endsection
