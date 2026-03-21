@extends('clinics.layout.app')
@section('title', 'Daily Summary Report')

@section('content')

<div class="pg-header">
    <div>
        <h1 class="pg-title">
            <i class="bi bi-calendar-check-fill" style="color:#2eca6a;font-size:18px"></i>
            Daily Summary <small>/ របាយការណ៍ប្រចាំថ្ងៃ</small>
        </h1>
        <div class="breadcrumb-row">
            <a href="{{ route('dashboard') }}">ដើម</a>
            <span>›</span>
            <a href="{{ route('reports.visits') }}">Reports</a>
            <span>›</span><span>Daily</span>
        </div>
    </div>
    <div class="d-flex gap-2">
        <button onclick="window.print()" class="btn-export btn-export-print">
            <i class="bi bi-printer-fill"></i> Print
        </button>
    </div>
</div>

{{-- Date picker --}}
<div class="report-filter-bar" style="padding:12px 20px">
    <form method="GET" action="{{ route('reports.daily') }}" style="display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap">
        <div class="filter-group">
            <label class="filter-label">Date</label>
            <input type="date" name="date" class="form-control"
                   value="{{ request('date', today()->format('Y-m-d')) }}"/>
        </div>
        <div class="filter-actions">
            <button type="submit" class="btn btn-primary"><i class="bi bi-funnel-fill"></i> Load</button>
            <a href="{{ route('reports.daily') }}" class="btn btn-outline-primary">Today</a>
        </div>
    </form>
</div>

@php $dateLabel = \Carbon\Carbon::parse(request('date', today()))->format('l, d F Y'); @endphp

{{-- Header banner --}}
<div style="background:linear-gradient(135deg,#012970,#1a3a7c);border-radius:14px;padding:20px 24px;margin-bottom:20px;color:#fff">
    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px">
        <div>
            <div style="font-size:11px;color:#8aabdc;text-transform:uppercase;letter-spacing:.8px;margin-bottom:4px">
                Daily Report
            </div>
            <div style="font-size:20px;font-weight:800">{{ $dateLabel }}</div>
            <div style="font-size:12px;color:#8aabdc;margin-top:4px">{{ currentClinic()?->name }}</div>
        </div>
        <div style="display:flex;gap:20px;flex-wrap:wrap">
            @foreach([
                ['Total',    $summary['total'],   '#fff'],
                ['OPD',      $summary['opd'],     '#a0c4ff'],
                ['IPD',      $summary['ipd'],     '#ffb385'],
                ['Active',   $summary['active'],  '#90ee90'],
                ['Done',     $summary['done'],    '#d4b8ff'],
            ] as [$lbl, $val, $col])
            <div style="text-align:center">
                <div style="font-size:26px;font-weight:800;color:{{ $col }};font-family:'Nunito',sans-serif">{{ $val }}</div>
                <div style="font-size:10px;color:#8aabdc;font-weight:700;text-transform:uppercase;letter-spacing:.5px">{{ $lbl }}</div>
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- OPD List --}}
@if($opdVisits->isNotEmpty())
<div class="card-emr mb-3">
    <div class="card-hd">
        <div class="card-hd-title">
            <span style="width:10px;height:10px;border-radius:2px;background:#2eca6a;display:inline-block"></span>
            OPD Visits
            <span style="font-size:11px;color:#aaa;font-weight:400">({{ $opdVisits->count() }})</span>
        </div>
    </div>
    <div class="card-bd" style="padding:0">
        <div class="table-responsive">
            <table class="report-tbl">
                <thead>
                    <tr>
                        <th>#</th><th>Time</th><th>Patient</th><th>Code</th>
                        <th>Admission</th><th>Status</th><th>Invoice</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($opdVisits as $i => $v)
                    @php $inv = $v->invoices->first(); @endphp
                    <tr>
                        <td style="color:#aaa;font-size:11px">{{ $i + 1 }}</td>
                        <td style="font-size:11.5px;color:#888">{{ $v->admitted_at?->format('H:i') }}</td>
                        <td style="font-weight:700;color:#012970">{{ $v->surname }}, {{ $v->name }}</td>
                        <td><code style="font-size:11px;color:#4154f1">{{ $v->code }}</code></td>
                        <td style="font-size:11.5px;color:#777">{{ $v->admission_type ?? '—' }}</td>
                        <td>
                            @if(is_null($v->discharged_at))
                                <span class="badge-s b-active">Active</span>
                            @else
                                <span class="badge-s b-done">Done</span>
                            @endif
                        </td>
                        <td style="font-size:11.5px">
                            @if($inv) {{ number_format($inv->total) }} KHR @else — @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

{{-- IPD List --}}
@if($ipdVisits->isNotEmpty())
<div class="card-emr mb-3">
    <div class="card-hd">
        <div class="card-hd-title">
            <span style="width:10px;height:10px;border-radius:2px;background:#ff771d;display:inline-block"></span>
            IPD Visits
            <span style="font-size:11px;color:#aaa;font-weight:400">({{ $ipdVisits->count() }})</span>
        </div>
    </div>
    <div class="card-bd" style="padding:0">
        <div class="table-responsive">
            <table class="report-tbl">
                <thead>
                    <tr><th>#</th><th>Time</th><th>Patient</th><th>Code</th><th>Status</th><th>Invoice</th></tr>
                </thead>
                <tbody>
                    @foreach($ipdVisits as $i => $v)
                    @php $inv = $v->invoices->first(); @endphp
                    <tr>
                        <td style="color:#aaa;font-size:11px">{{ $i + 1 }}</td>
                        <td style="font-size:11.5px;color:#888">{{ $v->admitted_at?->format('H:i') }}</td>
                        <td style="font-weight:700;color:#012970">{{ $v->surname }}, {{ $v->name }}</td>
                        <td><code style="font-size:11px;color:#ff771d">{{ $v->code }}</code></td>
                        <td>
                            @if(is_null($v->discharged_at))
                                <span class="badge-s b-active">Active</span>
                            @else
                                <span class="badge-s b-done">Done · {{ $v->discharged_at->format('H:i') }}</span>
                            @endif
                        </td>
                        <td style="font-size:11.5px">
                            @if($inv) {{ number_format($inv->total) }} KHR @else — @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

@if($opdVisits->isEmpty() && $ipdVisits->isEmpty())
<div class="card-emr">
    <div class="card-bd" style="text-align:center;padding:60px">
        <div style="font-size:48px;margin-bottom:12px;opacity:.25">📋</div>
        <div style="font-size:15px;font-weight:700;color:#bbb;margin-bottom:6px">No visits on this date</div>
        <a href="{{ route('workflow.create') }}" class="btn btn-primary btn-sm" style="margin-top:8px">
            <i class="bi bi-plus-lg"></i> Register Visit
        </a>
    </div>
</div>
@endif

@endsection
