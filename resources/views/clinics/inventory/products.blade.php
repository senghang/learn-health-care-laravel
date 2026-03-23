@extends('clinics.layout.app')
@section('title', 'Inventory — Products')
@section('content')

<x-page-header title="ផលិតផល" subtitle="Products"
    :breadcrumbs="[['label'=>'ដើម','url'=>url('/')],['label'=>'Inventory'],['label'=>'Products']]">
    <a href="{{ route('inventory.product.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg"></i> បន្ថែម
    </a>
</x-page-header>

{{-- KPI Cards --}}
<div class="row g-3 mb-3">
    @foreach([
        [$stats['total'],  'bi-box-seam-fill',              '#4154f1','#eef0fd', 'ផលិតផល',    'Total Products'],
        [$stats['low'],    'bi-exclamation-triangle-fill',   '#ff771d','#fff3e8', 'ស្តុកទាប',  'Low Stock'],
        [$stats['out'],    'bi-x-circle-fill',               '#e74c3c','#fde8e8', 'អស់ស្តុក',  'Out of Stock'],
        [number_format($stats['value']).' KHR', 'bi-cash-stack', '#2eca6a','#e8f8ef', 'តម្លៃស្តុក','Stock Value'],
    ] as [$val,$icon,$col,$bg,$km,$en])
    <div class="col-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:{{ $bg }};color:{{ $col }}"><i class="bi {{ $icon }}"></i></div>
            <div><div class="stat-num" style="color:{{ $col }};font-size:18px">{{ $val }}</div>
                <div class="stat-lbl">{{ $km }}<br><small>{{ $en }}</small></div></div>
        </div>
    </div>
    @endforeach
</div>

{{-- Filter --}}
<div class="card-emr mb-3">
    <div class="card-bd">
        <form method="GET" action="{{ route('inventory.products') }}">
            <div class="row g-2 align-items-end">
                <div class="col-12 col-sm-5">
                    <input type="text" name="search" class="form-control"
                           placeholder="ឈ្មោះ / Code / Generic name…" value="{{ request('search') }}" autofocus/>
                </div>
                <div class="col-6 col-sm-3">
                    <select name="category" class="form-select">
                        <option value="">All Categories</option>
                        @foreach($categories as $cat)
                        <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected':'' }}>{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-sm-2">
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="low" {{ request('status')==='low'?'selected':'' }}>Low Stock</option>
                        <option value="out" {{ request('status')==='out'?'selected':'' }}>Out of Stock</option>
                        <option value="active" {{ request('status')==='active'?'selected':'' }}>Active</option>
                    </select>
                </div>
                <div class="col-auto"><button type="submit" class="btn btn-primary"><i class="bi bi-funnel-fill"></i></button></div>
                @if(request()->hasAny(['search','category','status']))
                <div class="col-auto">
                    <a href="{{ route('inventory.products') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-x-circle"></i></a>
                </div>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- Table --}}
<div class="card-emr">
    <div class="card-hd">
        <div class="card-hd-title"><i class="bi bi-table"></i> បញ្ជីផលិតផល / Product List</div>
        <span style="font-size:11px;color:#aaa">{{ $medicines->total() }} products</span>
    </div>
    <div class="card-bd" style="padding:0">
        <div class="table-responsive">
            <table class="tbl">
                <thead>
                    <tr>
                        <th>Code</th><th>Name / Generic</th><th>Form / Strength</th>
                        <th>Unit Price KHR</th><th>Stock</th><th>Alert</th><th>Status</th><th></th>
                    </tr>
                </thead>
                <tbody>
                @forelse($medicines as $med)
                @php
                    $isOut = $med->stock === 0;
                    $isLow = !$isOut && $med->stock <= $med->stock_alert;
                @endphp
                <tr style="{{ $isOut ? 'background:#fff5f5' : ($isLow ? 'background:#fffaf4':'') }}">
                    <td><code style="color:#4154f1;font-size:11px">{{ $med->code }}</code></td>
                    <td>
                        <div style="font-weight:600;color:#012970">{{ $med->name }}</div>
                        @if($med->name_kh)<div style="font-size:10.5px;color:#aaa">{{ $med->name_kh }}</div>@endif
                        @if($med->generic_name)<div style="font-size:10px;color:#bbb;font-style:italic">{{ $med->generic_name }}</div>@endif
                        @if($med->category)<span style="font-size:9.5px;background:#f0f2ff;color:#4154f1;padding:1px 7px;border-radius:8px">{{ $med->category }}</span>@endif
                    </td>
                    <td>
                        @if($med->form)<span style="font-size:11.5px;background:#eef0fd;color:#4154f1;padding:2px 9px;border-radius:8px">{{ $med->form }}</span>@endif
                        @if($med->strength)<span style="font-size:11px;color:#888;margin-left:4px">{{ $med->strength }}</span>@endif
                    </td>
                    <td style="font-weight:600;color:#012970">{{ number_format($med->price) }}</td>
                    <td>
                        <span style="font-size:16px;font-weight:800;color:{{ $isOut?'#e74c3c':($isLow?'#ff771d':'#2eca6a') }}">
                            {{ $med->stock }}
                        </span>
                        @if($med->unit)<span style="font-size:10px;color:#aaa"> {{ $med->unit }}</span>@endif
                    </td>
                    <td style="color:#aaa;font-size:12px">{{ $med->stock_alert }}</td>
                    <td>
                        @if($isOut)
                            <x-status-badge status="critical" label="Out of Stock"/>
                        @elseif($isLow)
                            <span class="badge-s" style="background:#fff3e8;color:#ff771d;border:1px solid #ffd0a8">
                                <i class="bi bi-exclamation-triangle-fill" style="font-size:9px"></i> Low
                            </span>
                        @else
                            <x-status-badge status="active" label="OK"/>
                        @endif
                    </td>
                    <td>
                        <div style="display:flex;gap:4px">
                            <a href="{{ route('inventory.product.edit', $med->id) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <a href="{{ route('inventory.stock-in') }}?medicine={{ $med->id }}" class="btn btn-sm btn-outline-success" title="Stock In">
                                <i class="bi bi-plus-lg"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" style="text-align:center;padding:32px;color:#bbb">
                    <div style="font-size:32px;margin-bottom:8px;opacity:.3">💊</div>
                    No products found
                    <div><a href="{{ route('inventory.product.create') }}" class="btn btn-primary btn-sm mt-2"><i class="bi bi-plus-lg"></i> Add Product</a></div>
                </td></tr>
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
