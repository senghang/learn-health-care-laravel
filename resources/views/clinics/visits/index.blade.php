@extends('clinics.layout.app')

@section('title', 'ការចូលព្យាបាល')

@section('content')

    <div class="pg-header">
        <div>
            <h1 class="pg-title">ការចូលព្យាបាល <small>/ Patient Visits</small></h1>
            <div class="breadcrumb-row">
                <a href="{{ route('dashboard') }}">ដើម</a>
                <span>›</span><span>Visits</span>
            </div>
        </div>
        <a href="{{ route('workflow.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> ថ្មី / New
        </a>
    </div>

    {{-- Filter Bar --}}
    <div class="card-emr">
        <div class="card-bd">
            <form method="GET" action="{{ route('visits.index') }}">
                <div class="row g-2 align-items-end">
                    <div class="col-12 col-sm-6 col-md-4">
                        <label class="flbl"><span class="km">ស្វែងរក</span><span class="en">/ Search</span></label>
                        <div style="position:relative">
                            <i class="bi bi-search"
                               style="position:absolute;left:11px;top:50%;transform:translateY(-50%);color:#bbb;font-size:13px"></i>
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
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active
                            </option>
                            <option value="done" {{ request('status') === 'done'   ? 'selected' : '' }}>Done</option>
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

    {{-- Results summary --}}
    @if($visits->total() > 0)
        <div style="font-size:11px;color:#aaa;padding:6px 2px;margin-bottom:4px">
            បង្ហាញ {{ $visits->firstItem() }}–{{ $visits->lastItem() }} នៃ {{ $visits->total() }} ការចូលព្យាបាល
            @if(request()->hasAny(['search','type','status','date']))
                · <a href="{{ route('visits.index') }}" style="color:#e74c3c;text-decoration:none">
                    <i class="bi bi-x-circle"></i> លុបតម្រង
                </a>
            @endif
        </div>
    @endif

    {{-- Visit Rows --}}
    @forelse($visits as $visit)
        @php
            $initials  = strtoupper(substr($visit->surname ?? '', 0, 1) . substr($visit->given_name ?? '', 0, 1));
            $colors    = ['#4154f1','#2eca6a','#ff771d','#e74c3c','#9b59b6','#00bcd4'];
            $color     = $colors[abs(crc32($visit->patient_code)) % count($colors)];
            $isActive  = is_null($visit->discharged_at);
            $doneCount = count($visit->done_steps    ?? []);
            $skipCount = count($visit->skipped_steps ?? []);
        @endphp
        <div class="visit-row" onclick="window.location='{{ route('workflow.show', $visit->code) }}'">
            <div class="v-avatar" style="background:linear-gradient(135deg,{{ $color }},{{ $color }}cc)">
                {{ $initials ?: '?' }}
            </div>
            <div class="v-info">
                <div class="v-name">{{ $visit->surname }}, {{ $visit->given_name }}</div>
                <div class="v-meta">
                    {{ $visit->patient_code }} · {{ $visit->code }}
                    · {{ $visit->admitted_at?->format('d/m/Y H:i') ?? '—' }}
                    @if($doneCount > 0)
                        · <span style="color:#4154f1;font-weight:600">{{ $doneCount }}/10 steps</span>
                    @endif
                    @if($skipCount > 0)
                        · <span style="color:#c97700">{{ $skipCount }} skipped</span>
                    @endif
                </div>
                @if($doneCount > 0)
                    <div
                        style="height:3px;background:#f0f2ff;border-radius:2px;margin-top:5px;width:120px;overflow:hidden">
                        <div
                            style="height:100%;width:{{ $doneCount * 10 }}%;background:linear-gradient(90deg,#4154f1,#717ff5);border-radius:2px"></div>
                    </div>
                @endif
            </div>
            <div class="v-badges">
        <span class="badge-s {{ $visit->visit_type === 'IPD' ? 'b-ipd' : 'b-opd' }}">
            {{ $visit->visit_type }}
        </span>
                @if($isActive)
                    <span class="badge-s b-active d-none d-sm-inline-flex">
                <i class="bi bi-circle-fill" style="font-size:7px"></i>Active
            </span>
                @else
                    <span class="badge-s b-done d-none d-sm-inline-flex">✓ Done</span>
                @endif
            </div>
            <i class="bi bi-chevron-right" style="color:#ddd;flex-shrink:0"></i>
        </div>
    @empty
        {{-- Empty state — no mock data --}}
        <div style="text-align:center;padding:56px 24px;color:#aaa">
            <div style="font-size:48px;margin-bottom:12px;opacity:.3">🏥</div>
            <div style="font-size:16px;font-weight:700;color:#bbb;margin-bottom:6px">
                @if(request()->hasAny(['search','type','status','date']))
                    រកមិនឃើញការចូលព្យាបាល / No visits match your filters
                @else
                    មិនទាន់មានការចូលព្យាបាល / No visits yet
                @endif
            </div>
            <div style="font-size:12px;margin-bottom:20px">
                @if(request()->hasAny(['search','type','status','date']))
                    សាកល្បងផ្លាស់ប្ដូរការស្វែងរក / Try adjusting your search or filters
                @else
                    ចុចប៊ូតុង "ថ្មី" ខាងលើដើម្បីចាប់ផ្ដើម / Click "New" above to register the first visit
                @endif
            </div>
            @if(request()->hasAny(['search','type','status','date']))
                <a href="{{ route('visits.index') }}" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-x-circle"></i> លុបតម្រង / Clear filters
                </a>
            @else
                <a href="{{ route('workflow.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i> ការចូលព្យាបាលថ្មី / New Visit
                </a>
            @endif
        </div>
    @endforelse

    {{-- Pagination --}}
    @if($visits->hasPages())
        <div class="mt-3">{{ $visits->links() }}</div>
    @endif

@endsection
