@extends('clinics.layout.app')
@section('title', 'Revenue Report')
@section('content')

<x-page-header title="ប្រាក់ចំណូល" subtitle="Revenue Report"
    :breadcrumbs="[['label'=>'ដើម','url'=>url('/')],['label'=>'Reports'],['label'=>'Revenue']]">
    <a href="{{ url('/reports/revenue?export=csv&'.request()->getQueryString()) }}" class="btn btn-outline-primary btn-sm">
        <i class="bi bi-download"></i> Export CSV
    </a>
</x-page-header>

{{-- Date filter --}}
<div class="card-emr mb-3">
    <div class="card-bd">
        <form method="GET" action="{{ route('reports.revenue') }}" class="row g-2 align-items-end">
            <div class="col-6 col-sm-3">
                <label class="flbl"><span class="km">ពី</span><span class="en">/ From</span></label>
                <input type="date" name="date_from" class="form-control" value="{{ request('date_from', now()->startOfMonth()->format('Y-m-d')) }}"/>
            </div>
            <div class="col-6 col-sm-3">
                <label class="flbl"><span class="km">ដល់</span><span class="en">/ To</span></label>
                <input type="date" name="date_to" class="form-control" value="{{ request('date_to', now()->format('Y-m-d')) }}"/>
            </div>
            <div class="col-6 col-sm-3">
                <label class="flbl"><span class="km">ប្រភេទ</span><span class="en">/ Payment</span></label>
                <select name="payment_type" class="form-select">
                    <option value="">All</option>
                    <option value="HEF"  {{ request('payment_type')==='HEF'?'selected':'' }}>HEF</option>
                    <option value="NSSF" {{ request('payment_type')==='NSSF'?'selected':'' }}>NSSF</option>
                    <option value="CASH" {{ request('payment_type')==='CASH'?'selected':'' }}>CASH</option>
                </select>
            </div>
            <div class="col-6 col-sm-3">
                <button type="submit" class="btn btn-primary btn-w100"><i class="bi bi-funnel-fill"></i> Filter</button>
            </div>
        </form>
    </div>
</div>

{{-- KPI Cards --}}
<div class="row g-3 mb-3">
@foreach([
    [number_format($stats['total_revenue']).' KHR', 'bi-currency-dollar', '#4154f1','#eef0fd', 'ប្រាក់ចំណូលសរុប','Total Revenue'],
    [$stats['count'],                               'bi-receipt',          '#2eca6a','#e8f8ef', 'វិក្កយបត្រ',       'Invoices'],
    [number_format($stats['hef']).' KHR',           'bi-shield-fill',      '#9b59b6','#f0e8ff', 'HEF',             'HEF'],
    [number_format($stats['cash']).' KHR',          'bi-cash-stack',       '#ff771d','#fff3e8', 'CASH',            'Cash'],
] as [$val,$icon,$col,$bg,$km,$en])
<div class="col-6 col-xl-3">
    <div class="stat-card">
        <div class="stat-icon" style="background:{{ $bg }};color:{{ $col }}"><i class="bi {{ $icon }}"></i></div>
        <div><div class="stat-num" style="color:{{ $col }};font-size:16px">{{ $val }}</div>
            <div class="stat-lbl">{{ $km }}<br><small>{{ $en }}</small></div></div>
    </div>
</div>
@endforeach
</div>

{{-- Daily Revenue Chart --}}
@if($dailyRevenue->count() > 1)
<div class="card-emr mb-3">
    <div class="card-hd">
        <div class="card-hd-title"><i class="bi bi-bar-chart-fill"></i> ប្រាក់ចំណូលប្រចាំថ្ងៃ / Daily Revenue</div>
    </div>
    <div class="card-bd" style="padding-top:8px">
        @php $maxRev = max(1, $dailyRevenue->max('revenue')); @endphp
        <div style="height:120px;display:flex;align-items:flex-end;gap:3px;padding:0 4px">
            @foreach($dailyRevenue as $d)
            <div style="flex:1;display:flex;flex-direction:column;align-items:center;gap:2px;height:100%">
                @if($d['revenue'] > 0)
                <div style="font-size:9px;color:#555;font-weight:600">{{ number_format($d['revenue']/1000) }}K</div>
                @else
                <div style="font-size:9px;color:transparent">0</div>
                @endif
                <div style="flex:1;width:100%;display:flex;align-items:flex-end">
                    <div style="width:100%;height:{{ $maxRev > 0 ? round($d['revenue']/$maxRev*100) : 0 }}%;background:linear-gradient(180deg,#717ff5,#4154f1);border-radius:3px 3px 0 0;min-height:{{ $d['revenue']>0?3:0 }}px"></div>
                </div>
                <div style="font-size:9px;color:#aaa;white-space:nowrap">{{ $d['label'] }}</div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif

{{-- Invoice List --}}
<div class="card-emr">
    <div class="card-hd">
        <div class="card-hd-title"><i class="bi bi-table"></i> វិក្កយបត្រ / Invoices</div>
        <span style="font-size:11px;color:#aaa">{{ $invoices->total() }} invoices</span>
    </div>
    <div class="card-bd" style="padding:0">
        <div class="table-responsive">
            <table class="tbl">
                <thead>
                    <tr><th>ថ្ងៃ</th><th>Invoice</th><th>Visit</th><th>Patient</th><th>Payment</th><th>Total KHR</th></tr>
                </thead>
                <tbody>
                @forelse($invoices as $inv)
                <tr>
                    <td style="font-size:12px;color:#555">{{ $inv->invoice_date?->format('d/m/Y') }}</td>
                    <td><code style="font-size:11px;color:#4154f1">{{ $inv->code }}</code></td>
                    <td>
                        @if($inv->visit_code)
                        <a href="{{ url('/workflow/'.$inv->visit_code) }}" style="font-size:11px;color:#4154f1">
                            {{ $inv->visit_code }}
                        </a>
                        @else <span style="color:#ddd">—</span> @endif
                    </td>
                    <td style="font-size:12.5px">
                        {{ $inv->visit?->surname ?? '' }}{{ $inv->visit?->surname ? ', ' : '' }}{{ $inv->visit?->name ?? '—' }}
                    </td>
                    <td>
                        @php $ptColors = ['HEF'=>'#9b59b6','NSSF'=>'#2eca6a','CASH'=>'#ff771d']; @endphp
                        <span class="badge-s" style="background:{{ ($ptColors[$inv->payment_type]??'#aaa') }}22;color:{{ $ptColors[$inv->payment_type]??'#aaa' }};border:1px solid {{ ($ptColors[$inv->payment_type]??'#aaa') }}44;font-size:10px">
                            {{ $inv->payment_type }}
                        </span>
                    </td>
                    <td style="font-weight:700;color:#1a1f36">{{ number_format($inv->total) }}</td>
                </tr>
                @empty
                <tr><td colspan="6" style="text-align:center;padding:32px;color:#bbb">
                    <div style="font-size:28px;margin-bottom:8px;opacity:.3">💰</div>
                    No invoices found in this period
                </td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@if($invoices->hasPages())<div class="mt-3">{{ $invoices->links() }}</div>@endif

@endsection
