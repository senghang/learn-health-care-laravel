@extends('clinics.layout.app')
@section('title', 'All Stock Movements')
@section('content')

<x-page-header title="ចលនាស្តុក" subtitle="All Stock Movements"
    :breadcrumbs="[['label'=>'ដើម','url'=>route('dashboard')],['label'=>'Inventory','url'=>route('inventory.products')],['label'=>'Movements']]">
    <div class="d-flex gap-2">
        <a href="{{ route('inventory.adjustment') }}" class="btn btn-outline-warning btn-sm">
            <i class="bi bi-calculator-fill"></i> Adjust Count
        </a>
        <a href="{{ route('inventory.stock-in') }}" class="btn btn-outline-success btn-sm">
            <i class="bi bi-box-arrow-in-down-right"></i> Stock In
        </a>
        <a href="{{ route('inventory.stock-out') }}" class="btn btn-outline-danger btn-sm">
            <i class="bi bi-box-arrow-up-right"></i> Stock Out
        </a>
    </div>
</x-page-header>

@if(session('flash'))
    <div class="note note-success mb-3"><i class="bi bi-check-circle-fill"></i> {{ session('flash') }}</div>
@endif

{{-- Type summary strip --}}
<div class="row g-3 mb-3">
    @foreach([
        ['in',         'bi-box-arrow-in-down-right', '#2eca6a','#e8f8ef', 'Stock In',   'ចូលស្តុក'],
        ['return',     'bi-arrow-return-left',        '#ff771d','#fff3e8', 'Returns',    'ត្រឡប់'],
        ['out',        'bi-box-arrow-up-right',       '#e74c3c','#fde8e8', 'Manual Out', 'ចេញ'],
        ['expired',    'bi-calendar-x-fill',          '#9b59b6','#f5eeff', 'Expired',    'ផុតកំណត់'],
        ['adjustment', 'bi-sliders',                  '#4154f1','#eef0fd', 'Adjusted',   'កែតម្រូវ'],
    ] as [$type,$ico,$col,$bg,$en,$km])
        <div class="col-6 col-xl" style="min-width:0">
            <a href="{{ route('inventory.movements', ['type' => $type]) }}"
               class="stat-card" style="text-decoration:none;background:{{ request('type')===$type ? $bg : '#fff' }};border:{{ request('type')===$type ? "2px solid {$col}" : '1px solid #f0f0f0' }}">
                <div class="stat-icon" style="background:{{ $bg }};color:{{ $col }}"><i class="bi {{ $ico }}"></i></div>
                <div>
                    <div class="stat-num" style="color:{{ $col }};font-size:16px">{{ $typeStats[$type] ?? 0 }}</div>
                    <div class="stat-lbl">{{ $km }}<br><small>{{ $en }}</small></div>
                </div>
            </a>
        </div>
    @endforeach
</div>

{{-- Filter --}}
<div class="card-emr mb-3">
    <div class="card-bd">
        <form method="GET" action="{{ route('inventory.movements') }}">
            <div class="row g-2 align-items-end">
                <div class="col-12 col-sm-4">
                    <input type="text" name="search" class="form-control form-control-sm"
                           placeholder="Medicine name, reference, supplier…"
                           value="{{ request('search') }}" autofocus/>
                </div>
                <div class="col-6 col-sm-2">
                    <select name="type" class="form-select form-select-sm">
                        <option value="">All Types</option>
                        @foreach(['in'=>'Stock In','return'=>'Return','out'=>'Manual Out','expired'=>'Expired','adjustment'=>'Adjustment'] as $v=>$l)
                            <option value="{{ $v }}" {{ request('type')===$v?'selected':'' }}>{{ $l }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-sm-2">
                    <input type="date" name="date" class="form-control form-control-sm"
                           value="{{ request('date') }}"/>
                </div>
                <div class="col-6 col-sm-2">
                    <input type="month" name="month" class="form-control form-control-sm"
                           value="{{ request('month') }}" placeholder="Month"/>
                </div>
                <div class="col-auto d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-funnel-fill"></i></button>
                    @if(request()->hasAny(['search','type','date','month']))
                        <a href="{{ route('inventory.movements') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-x-circle"></i></a>
                    @endif
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Table --}}
<div class="card-emr">
    <div class="card-hd">
        <div class="card-hd-title"><i class="bi bi-journal-text" style="color:#4154f1"></i> Movement Log</div>
        <span style="font-size:11px;color:#aaa">{{ $movements->total() }} records</span>
    </div>
    <div class="card-bd" style="padding:0">
        <div class="table-responsive">
            <table class="tbl">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Medicine</th>
                        <th>Type</th>
                        <th style="text-align:center">Qty</th>
                        <th style="text-align:center">Before</th>
                        <th style="text-align:center">After</th>
                        <th>Reference / Supplier</th>
                        <th>Note</th>
                        <th>By</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($movements as $mv)
                @php
                    $isIn  = in_array($mv->type, ['in','return']);
                    $isAdj = $mv->type === 'adjustment';
                    $delta = $mv->stock_after - $mv->stock_before;
                    $typeMap = [
                        'in'         => ['Stock In',    '#2eca6a','#e8f8ef'],
                        'return'     => ['Return',      '#ff771d','#fff3e8'],
                        'out'        => ['Manual Out',  '#e74c3c','#fde8e8'],
                        'expired'    => ['Expired',     '#9b59b6','#f5eeff'],
                        'adjustment' => ['Adjustment',  '#4154f1','#eef0fd'],
                    ];
                    [$label, $col, $bg] = $typeMap[$mv->type] ?? [ucfirst($mv->type),'#aaa','#f5f5f5'];
                @endphp
                <tr>
                    <td>
                        <div style="font-size:12px;font-weight:600;color:#012970">{{ $mv->created_at->format('d/m/Y') }}</div>
                        <div style="font-size:10px;color:#aaa">{{ $mv->created_at->format('H:i') }}</div>
                    </td>
                    <td>
                        <a href="{{ route('inventory.product.ledger', $mv->medicine_id) }}"
                           style="font-weight:600;font-size:12.5px;color:#012970;text-decoration:none">
                            {{ $mv->medicine_name }}
                        </a>
                        <div><code style="font-size:10px;color:#4154f1">{{ $mv->medicine_code }}</code></div>
                    </td>
                    <td>
                        <span style="font-size:10.5px;padding:2px 9px;border-radius:8px;font-weight:700;background:{{ $bg }};color:{{ $col }}">
                            {{ $label }}
                        </span>
                    </td>
                    <td style="text-align:center;font-size:15px;font-weight:800;color:{{ $isIn?'#2eca6a':($isAdj&&$delta>=0?'#2eca6a':'#e74c3c') }}">
                        {{ $isIn ? '+' : ($isAdj ? ($delta>=0?'+':'-') : '-') }}{{ $mv->quantity }}
                    </td>
                    <td style="text-align:center;color:#aaa;font-size:12px">{{ $mv->stock_before }}</td>
                    <td style="text-align:center;font-weight:700;color:{{ $mv->stock_after<=0?'#e74c3c':($mv->stock_after<=10?'#ff771d':'#012970') }}">
                        {{ $mv->stock_after }}
                    </td>
                    <td style="font-size:11px">
                        @if($mv->reference)<div style="color:#4154f1">{{ $mv->reference }}</div>@endif
                        @if($mv->supplier)<div style="color:#aaa">{{ $mv->supplier }}</div>@endif
                        @if($mv->batch_no)<div style="color:#aaa;font-size:10px">Batch: {{ $mv->batch_no }}</div>@endif
                    </td>
                    <td style="font-size:11px;color:#666;max-width:160px">{{ Str::limit($mv->note, 50) }}</td>
                    <td style="font-size:11px;color:#aaa">{{ $mv->recorded_by ?? '—' }}</td>
                </tr>
                @empty
                <tr><td colspan="9" style="text-align:center;padding:36px;color:#bbb">
                    <div style="font-size:32px;margin-bottom:8px;opacity:.3">📦</div>
                    No movements found
                </td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@if($movements->hasPages())
    <div class="mt-3">{{ $movements->links() }}</div>
@endif

@endsection
