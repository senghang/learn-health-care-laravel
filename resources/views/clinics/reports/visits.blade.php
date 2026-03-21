@extends('clinics.layout.app')

@section('title', 'របាយការណ៍ការចូល / Visit Reports')

@section('content')

@php
    $totalVisits   = $visits->total();
    $opdCount      = $visits->getCollection()->where('visit_type','OPD')->count();
    $ipdCount      = $visits->getCollection()->where('visit_type','IPD')->count();
    $activeCount   = $visits->getCollection()->whereNull('discharged_at')->count();
    $doneCount     = $visits->getCollection()->whereNotNull('discharged_at')->count();
@endphp

{{-- Page header --}}
<div class="pg-header">
    <div>
        <h1 class="pg-title">
            <i class="bi bi-bar-chart-line-fill" style="color:#4154f1;font-size:18px"></i>
            របាយការណ៍ <small>/ Visit Reports</small>
        </h1>
        <div class="breadcrumb-row">
            <a href="{{ route('dashboard') }}">ដើម</a>
            <span>›</span><span>Reports</span>
        </div>
    </div>
    <div class="d-flex gap-2 align-items-center flex-wrap">
        <a href="{{ route('reports.visits', array_merge(request()->query(), ['export' => 'csv'])) }}"
           class="btn-export btn-export-csv">
            <i class="bi bi-filetype-csv"></i> Export CSV
        </a>
        <button onclick="window.print()" class="btn-export btn-export-print">
            <i class="bi bi-printer-fill"></i> Print
        </button>
    </div>
</div>

{{-- ── Filter Bar ─────────────────────────────────────────────────── --}}
<div class="report-filter-bar">
    <form method="GET" action="{{ route('reports.visits') }}" id="filterForm">

        <div class="filter-group">
            <label class="filter-label"><i class="bi bi-calendar3"></i> From</label>
            <input type="date" name="date_from" class="form-control"
                   value="{{ request('date_from', now()->startOfMonth()->format('Y-m-d')) }}"/>
        </div>

        <div class="filter-group">
            <label class="filter-label"><i class="bi bi-calendar3"></i> To</label>
            <input type="date" name="date_to" class="form-control"
                   value="{{ request('date_to', now()->format('Y-m-d')) }}"/>
        </div>

        <div class="filter-group">
            <label class="filter-label">Type</label>
            <select name="visit_type" class="form-select">
                <option value="">All Types</option>
                <option value="OPD" {{ request('visit_type') === 'OPD' ? 'selected' : '' }}>OPD</option>
                <option value="IPD" {{ request('visit_type') === 'IPD' ? 'selected' : '' }}>IPD</option>
            </select>
        </div>

        <div class="filter-group">
            <label class="filter-label">Status</label>
            <select name="status" class="form-select">
                <option value="">All Status</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                <option value="done"   {{ request('status') === 'done'   ? 'selected' : '' }}>Discharged</option>
            </select>
        </div>

        <div class="filter-group">
            <label class="filter-label">Payment</label>
            <select name="payment_type" class="form-select">
                <option value="">All</option>
                <option value="HEF"  {{ request('payment_type') === 'HEF'  ? 'selected' : '' }}>HEF</option>
                <option value="NSSF" {{ request('payment_type') === 'NSSF' ? 'selected' : '' }}>NSSF</option>
                <option value="CASH" {{ request('payment_type') === 'CASH' ? 'selected' : '' }}>CASH</option>
            </select>
        </div>

        <div class="filter-group" style="flex:1;min-width:160px">
            <label class="filter-label"><i class="bi bi-search"></i> Search</label>
            <input type="text" name="search" class="form-control"
                   placeholder="Name, code, phone…"
                   value="{{ request('search') }}"/>
        </div>

        <div class="filter-actions">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-funnel-fill"></i> Filter
            </button>
            <a href="{{ route('reports.visits') }}" class="btn btn-outline-primary">
                <i class="bi bi-x-circle"></i> Reset
            </a>
        </div>

    </form>
</div>

{{-- ── KPI Cards ────────────────────────────────────────────────────── --}}
<div class="row g-3 mb-3" id="reportStats">
    @foreach([
        [$totalStats['total'],   '#4154f1', 'bi-hospital-fill',      'Total Visits',    'in period'],
        [$totalStats['opd'],     '#2eca6a', 'bi-person-fill',         'OPD',             'Outpatient'],
        [$totalStats['ipd'],     '#ff771d', 'bi-bed-fill',            'IPD',             'Inpatient'],
        [$totalStats['active'],  '#00bcd4', 'bi-activity',            'Active',          'Currently admitted'],
        [$totalStats['done'],    '#9b59b6', 'bi-check-circle-fill',   'Discharged',      'Completed visits'],
        [$totalStats['revenue'], '#e74c3c', 'bi-cash-stack',          'Revenue (KHR)',   'Total invoiced'],
    ] as [$val, $col, $ico, $lbl, $sub])
    <div class="col-6 col-sm-4 col-xl-2">
        <div class="report-stat">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px">
                <div style="width:32px;height:32px;border-radius:9px;background:{{ $col }}22;color:{{ $col }};display:flex;align-items:center;justify-content:center;font-size:15px">
                    <i class="bi {{ $ico }}"></i>
                </div>
            </div>
            <div class="report-stat-num" style="color:{{ $col }}">{{ is_numeric($val) ? number_format($val) : $val }}</div>
            <div class="report-stat-lbl">{{ $lbl }}</div>
            <div class="report-stat-sub">{{ $sub }}</div>
        </div>
    </div>
    @endforeach
</div>

{{-- ── Chart bar (weekly breakdown) ─────────────────────────────────── --}}
@if($dailyChart->isNotEmpty())
<div class="card-emr mb-3">
    <div class="card-hd">
        <div class="card-hd-title">
            <i class="bi bi-bar-chart-fill"></i>
            Daily Breakdown
            <small style="font-weight:400;color:#aaa">/ {{ request('date_from', now()->startOfMonth()->format('d/m')) }} – {{ request('date_to', now()->format('d/m/Y')) }}</small>
        </div>
        <div style="display:flex;gap:10px">
            <span style="font-size:10px;color:#4154f1;display:flex;align-items:center;gap:4px">
                <span style="width:10px;height:10px;border-radius:2px;background:#4154f1;display:inline-block"></span>OPD
            </span>
            <span style="font-size:10px;color:#ff771d;display:flex;align-items:center;gap:4px">
                <span style="width:10px;height:10px;border-radius:2px;background:#ff771d;display:inline-block"></span>IPD
            </span>
        </div>
    </div>
    <div class="card-bd" style="padding-top:12px">
        @php $maxBar = $dailyChart->max(fn($d) => $d['opd'] + $d['ipd']); $maxBar = max($maxBar, 1); @endphp
        <div style="height:100px;display:flex;align-items:flex-end;gap:4px;overflow-x:auto;padding-bottom:4px">
            @foreach($dailyChart as $d)
            @php $total = $d['opd'] + $d['ipd']; @endphp
            <div style="flex:1;min-width:24px;max-width:40px;display:flex;flex-direction:column;align-items:center;gap:3px;height:100%">
                <div style="font-size:9px;color:#aaa;font-weight:600">{{ $total ?: '' }}</div>
                <div style="flex:1;width:100%;display:flex;flex-direction:column;justify-content:flex-end;gap:1px">
                    <div style="height:{{ $maxBar>0?round($d['opd']/$maxBar*80):0 }}px;background:linear-gradient(180deg,#717ff5,#4154f1);border-radius:3px 3px 0 0;min-height:{{ $d['opd']>0?2:0 }}px"></div>
                    <div style="height:{{ $maxBar>0?round($d['ipd']/$maxBar*80):0 }}px;background:linear-gradient(180deg,#ffaa6b,#ff771d);border-radius:{{ $d['opd']>0?'0':'3px 3px' }} 0 0;min-height:{{ $d['ipd']>0?2:0 }}px"></div>
                </div>
                <div style="font-size:8.5px;color:#bbb;white-space:nowrap">{{ $d['label'] }}</div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif

{{-- ── Results Table ───────────────────────────────────────────────── --}}
<div class="card-emr">
    <div class="card-hd">
        <div class="card-hd-title">
            <i class="bi bi-table"></i>
            Visit Records
            <small style="font-weight:400;color:#aaa">/ {{ number_format($visits->total()) }} results</small>
        </div>
        <div class="d-flex gap-2 align-items-center">
            <select class="form-select" style="font-size:12px;padding:5px 8px;width:auto"
                    onchange="window.location=updateParam('per_page', this.value)">
                @foreach([20, 50, 100, 200] as $pp)
                <option value="{{ $pp }}" {{ request('per_page', 20) == $pp ? 'selected' : '' }}>
                    {{ $pp }} per page
                </option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="card-bd" style="padding:0">
        <div class="table-responsive">
            <table class="report-tbl" id="reportTable">
                <thead>
                    <tr>
                        <th>
                            <a href="{{ route('reports.visits', array_merge(request()->query(), ['sort' => 'admitted_at', 'dir' => request('sort') === 'admitted_at' && request('dir') === 'asc' ? 'desc' : 'asc'])) }}" style="color:inherit;text-decoration:none">
                                Date
                                @if(request('sort','admitted_at') === 'admitted_at')
                                    <i class="bi bi-arrow-{{ request('dir','desc') === 'desc' ? 'down' : 'up' }}" style="font-size:9px"></i>
                                @endif
                            </a>
                        </th>
                        <th>Visit Code</th>
                        <th>Patient</th>
                        <th>Code</th>
                        <th>Type</th>
                        <th>Admission</th>
                        <th>Status</th>
                        <th>Steps</th>
                        <th>Invoice</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($visits as $v)
                    @php
                        $isActive = is_null($v->discharged_at);
                        $inv      = $v->invoices->first();
                        $doneN    = count($v->done_steps ?? []);
                        $skipN    = count($v->skipped_steps ?? []);
                    @endphp
                    <tr>
                        <td style="white-space:nowrap;color:#888;font-size:11.5px">
                            {{ $v->admitted_at?->format('d/m/Y') }}<br>
                            <span style="font-size:10px">{{ $v->admitted_at?->format('H:i') }}</span>
                        </td>
                        <td>
                            <code style="font-size:11px;color:#4154f1;background:#eef0fd;padding:2px 6px;border-radius:4px">
                                {{ $v->code }}
                            </code>
                        </td>
                        <td style="font-weight:700;color:#012970;white-space:nowrap">
                            {{ $v->surname }}, {{ $v->name }}
                        </td>
                        <td style="font-size:11px;color:#aaa">{{ $v->patient_code }}</td>
                        <td>
                            <span class="badge-s {{ $v->visit_type === 'IPD' ? 'b-ipd' : 'b-opd' }}">
                                {{ $v->visit_type }}
                            </span>
                        </td>
                        <td style="font-size:11.5px;color:#777">{{ $v->admission_type ?? '—' }}</td>
                        <td>
                            @if($isActive)
                                <span class="badge-s b-active"><i class="bi bi-circle-fill" style="font-size:6px"></i> Active</span>
                            @else
                                <span class="badge-s b-done">✓ Done</span>
                            @endif
                            @if($v->discharged_at)
                            <div style="font-size:10px;color:#ccc;margin-top:2px">
                                {{ $v->discharged_at->format('d/m H:i') }}
                            </div>
                            @endif
                        </td>
                        <td>
                            <div style="font-size:11px;color:#666;margin-bottom:3px">
                                {{ $doneN }}/10
                                @if($skipN > 0)<span style="color:#c97700"> ·{{ $skipN }}⏭</span>@endif
                            </div>
                            <div style="width:60px;height:4px;background:#f0f2ff;border-radius:2px;overflow:hidden">
                                <div style="height:100%;width:{{ $doneN * 10 }}%;background:linear-gradient(90deg,#4154f1,#717ff5);border-radius:2px"></div>
                            </div>
                        </td>
                        <td>
                            @if($inv)
                                <div style="font-size:11px;font-weight:700;color:#012970">
                                    {{ number_format($inv->total) }} <span style="font-size:9px;color:#aaa">KHR</span>
                                </div>
                                <span style="font-size:9.5px;background:#e0f7fa;color:#00838f;padding:1px 7px;border-radius:10px;font-weight:700">
                                    {{ $inv->payment_type }}
                                </span>
                            @else
                                <span style="font-size:11px;color:#ddd">—</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ url('/workflow/' . $v->code . '/registration') }}"
                               class="btn btn-sm btn-primary" style="font-size:11px;padding:4px 10px">
                                <i class="bi bi-pencil-fill"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr class="no-data">
                        <td colspan="10">
                            <div style="font-size:32px;margin-bottom:10px;opacity:.3">📋</div>
                            <div style="font-weight:600;color:#bbb;margin-bottom:4px">No visits found</div>
                            <div style="font-size:11px">Try adjusting your filters</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($visits->hasPages())
        <div style="display:flex;justify-content:space-between;align-items:center;padding:12px 16px;border-top:1px solid #f0f2ff;flex-wrap:wrap;gap:8px">
            <div style="font-size:11.5px;color:#aaa">
                Showing {{ $visits->firstItem() }}–{{ $visits->lastItem() }} of {{ number_format($visits->total()) }}
            </div>
            <div>{{ $visits->appends(request()->query())->links() }}</div>
        </div>
        @endif
    </div>
</div>

@endsection

@push('scripts')
<script>
function updateParam(key, value) {
    var url = new URL(window.location.href);
    url.searchParams.set(key, value);
    url.searchParams.delete('page');
    return url.toString();
}
// Auto-submit on per_page / type / status select change
document.querySelectorAll('#filterForm select').forEach(function(sel) {
    sel.addEventListener('change', function() {
        document.getElementById('filterForm').submit();
    });
});
</script>
@endpush
