@extends('clinics.layout.app')
@section('title', 'Inventory Report')
@section('content')

<x-page-header title="របាយការណ៍ស្តុក" subtitle="Inventory Report"
    :breadcrumbs="[['label'=>'ដើម','url'=>url('/')],['label'=>'Reports','url'=>route('reports.visits')],['label'=>'Inventory']]"
>
    <a href="{{ url('/reports/inventory?export=csv') }}" class="btn btn-outline-primary btn-sm">
        <i class="bi bi-download"></i> Export CSV
    </a>
</x-page-header>

{{-- KPIs --}}
<div class="row g-3 mb-3">
    @php
        $totalMeds   = $medicines->total();
        $lowCount    = $medicines->getCollection()->filter(fn($m)=> $m->stock <= $m->stock_alert && $m->stock > 0)->count();
        $outCount    = $medicines->getCollection()->filter(fn($m)=> $m->stock == 0)->count();
        $totalValue  = $medicines->getCollection()->sum(fn($m)=> $m->stock * $m->price);
    @endphp
    @foreach([
        ['ថ្នាំទាំងអស់','Total Medicines',$totalMeds,'bi-capsule-fill','#4154f1','#eef0fd'],
        ['ស្តុកទាប','Low Stock',$lowCount,'bi-exclamation-triangle-fill','#ff771d','#fff3e8'],
        ['អស់ស្តុក','Out of Stock',$outCount,'bi-x-circle-fill','#e74c3c','#fde8e8'],
        ['តម្លៃស្តុក','Stock Value',number_format($totalValue).' KHR','bi-cash-stack','#2eca6a','#e8f8ef'],
    ] as [$km,$en,$val,$icon,$color,$bg])
    <div class="col-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:{{ $bg }};color:{{ $color }}"><i class="bi {{ $icon }}"></i></div>
            <div>
                <div class="stat-num" style="color:{{ $color }};font-size:20px">{{ $val }}</div>
                <div class="stat-lbl">{{ $km }}<br><small>{{ $en }}</small></div>
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- Filter --}}
<div class="card-emr mb-3">
    <div class="card-bd">
        <form method="GET">
            <div class="row g-2 align-items-end">
                <div class="col-12 col-sm-5">
                    <input type="text" name="search" class="form-control" placeholder="ឈ្មោះថ្នាំ / medicine name…" value="{{ request('search') }}"/>
                </div>
                <div class="col-6 col-sm-3">
                    <select name="filter" class="form-select">
                        <option value="">All</option>
                        <option value="low" {{ request('filter')==='low'?'selected':'' }}>Low Stock</option>
                        <option value="out" {{ request('filter')==='out'?'selected':'' }}>Out of Stock</option>
                        <option value="ok"  {{ request('filter')==='ok'?'selected':'' }}>OK</option>
                    </select>
                </div>
                <div class="col-6 col-sm-2">
                    <button type="submit" class="btn btn-primary btn-w100"><i class="bi bi-funnel-fill"></i> Filter</button>
                </div>
                @if(request()->hasAny(['search','filter']))
                <div class="col-auto">
                    <a href="{{ url('/reports/inventory') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-x-circle"></i></a>
                </div>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- Table --}}
<div class="card-emr">
    <div class="card-hd">
        <div class="card-hd-title"><i class="bi bi-table"></i> ស្តុកថ្នាំ / Medicine Stock</div>
        <span style="font-size:11px;color:#aaa">{{ $medicines->total() }} items</span>
    </div>
    <div class="card-bd" style="padding:0">
        <div class="table-responsive">
            <table class="tbl">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Form / Strength</th>
                        <th>Unit Price</th>
                        <th>Stock</th>
                        <th>Alert At</th>
                        <th>Value</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($medicines as $med)
                @php
                    $isOut  = $med->stock == 0;
                    $isLow  = !$isOut && $med->stock <= $med->stock_alert;
                    $status = $isOut ? 'out' : ($isLow ? 'low' : 'ok');
                @endphp
                <tr style="{{ $isOut ? 'background:#fff5f5' : ($isLow ? 'background:#fffaf4' : '') }}">
                    <td><code style="font-size:11px;color:#4154f1">{{ $med->code }}</code></td>
                    <td>
                        <div style="font-weight:600;color:#012970">{{ $med->name }}</div>
                        @if($med->name_kh)<div style="font-size:10.5px;color:#aaa">{{ $med->name_kh }}</div>@endif
                        @if($med->generic_name)<div style="font-size:10px;color:#bbb;font-style:italic">{{ $med->generic_name }}</div>@endif
                    </td>
                    <td>
                        @if($med->form)<span style="font-size:11px;background:#eef0fd;color:#4154f1;padding:1px 8px;border-radius:8px">{{ $med->form }}</span>@endif
                        @if($med->strength)<span style="font-size:11px;color:#888;margin-left:4px">{{ $med->strength }}</span>@endif
                    </td>
                    <td style="font-size:12.5px;font-weight:600;color:#012970">{{ number_format($med->price) }}</td>
                    <td>
                        <span style="font-size:15px;font-weight:800;color:{{ $isOut ? '#e74c3c' : ($isLow ? '#ff771d' : '#2eca6a') }}">{{ $med->stock }}</span>
                        @if($med->unit)<span style="font-size:10px;color:#aaa"> {{ $med->unit }}</span>@endif
                    </td>
                    <td style="font-size:12px;color:#aaa">{{ $med->stock_alert }}</td>
                    <td style="font-size:12px;color:#555">{{ number_format($med->stock * $med->price) }}</td>
                    <td>
                        @if($isOut)
                            <span class="badge-s b-critical"><i class="bi bi-x-circle-fill" style="font-size:9px"></i> Out</span>
                        @elseif($isLow)
                            <span class="badge-s" style="background:#fff3e8;color:#ff771d;border:1px solid #ffd0a8"><i class="bi bi-exclamation-triangle-fill" style="font-size:9px"></i> Low</span>
                        @else
                            <span class="badge-s b-active"><i class="bi bi-check-circle-fill" style="font-size:9px"></i> OK</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" style="text-align:center;padding:32px;color:#bbb">No medicines found</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@if($medicines->hasPages())
<div class="mt-3">{{ $medicines->links() }}</div>
@endif

@endsection
