@extends('clinics.layout.app')
@section('title', 'Inventory Report')
@section('content')

@php
    $lowMeds    = $lowMeds    ?? collect([]);
    $outMeds    = $outMeds    ?? collect([]);
    $recentTxns = $recentTxns ?? collect([]);
    $stats      = $stats      ?? [];
@endphp

<div class="pg-header">
    <div>
        <h1 class="pg-title">Inventory Report <small>/ ស្ថិតិស្តុក</small></h1>
        <div class="breadcrumb-row">
            <a href="{{ route('dashboard') }}">ដើម</a><span>›</span>
            <a href="{{ route('inventory.products') }}">Inventory</a><span>›</span>
            <span>Report</span>
        </div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('inventory.stock-in') }}" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-box-arrow-in-down"></i> Stock In
        </a>
        <a href="{{ route('inventory.products') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-grid"></i> All Products
        </a>
    </div>
</div>

{{-- ── KPI Tiles ──────────────────────────────────────────────────────── --}}
<div class="row g-3 mb-4">
    @php
        $tiles = [
            ['icon'=>'bi-boxes',            'color'=>'#4154f1','bg'=>'#eef0fd', 'val'=>$stats['total']     ?? 0,  'lbl'=>'Total Products',    'sub'=>'ផលិតផល'],
            ['icon'=>'bi-exclamation-circle','color'=>'#b45309','bg'=>'#fff8e1', 'val'=>$stats['low']       ?? 0,  'lbl'=>'Low Stock Alert',   'sub'=>'ស្តុកទាប'],
            ['icon'=>'bi-x-circle',          'color'=>'#dc2626','bg'=>'#fde8e8', 'val'=>$stats['out']       ?? 0,  'lbl'=>'Out of Stock',      'sub'=>'អស់ស្តុក'],
            ['icon'=>'bi-currency-dollar',   'color'=>'#1D9E75','bg'=>'#e8f8ef', 'val'=>khr($stats['value'] ?? 0),'lbl'=>'Stock Value (KHR)', 'sub'=>'តម្លៃស្តុក'],
        ];
    @endphp
    @foreach($tiles as $tile)
    <div class="col-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:{{ $tile['bg'] }};color:{{ $tile['color'] }}">
                <i class="bi {{ $tile['icon'] }}"></i>
            </div>
            <div style="flex:1">
                <div class="stat-num" style="color:{{ $tile['color'] }};font-size:20px">{{ $tile['val'] }}</div>
                <div class="stat-lbl">{{ $tile['sub'] }}<br><small>{{ $tile['lbl'] }}</small></div>
            </div>
        </div>
    </div>
    @endforeach
</div>

<div class="row g-3">

    {{-- ── Low Stock Alert ──────────────────────────────────────────────── --}}
    <div class="col-12 col-lg-6">
        <div class="card-emr">
            <div class="card-hd">
                <div class="card-hd-title">
                    <i class="bi bi-exclamation-triangle-fill" style="color:#b45309"></i>
                    Low Stock Alert
                    @if($lowMeds->isNotEmpty())
                    <span style="background:#fff8e1;color:#b45309;border:1px solid #fde68a;font-size:10px;padding:1px 8px;border-radius:10px;font-weight:700">
                        {{ $lowMeds->count() }} items
                    </span>
                    @endif
                </div>
            </div>
            <div class="card-bd" style="padding:0">
                @forelse($lowMeds as $med)
                @php
                    $pct = $med->stock_alert > 0 ? round($med->stock / $med->stock_alert * 100) : 0;
                    $isOut = $med->stock <= 0;
                @endphp
                <div style="padding:10px 16px;border-bottom:1px solid #f5f6ff;display:flex;align-items:center;gap:12px">
                    <div style="flex:1;min-width:0">
                        <div style="font-weight:700;font-size:13px;color:#012970;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                            {{ $med->name }}
                        </div>
                        <div style="font-size:11px;color:#aaa;margin-top:1px">
                            {{ $med->form }} · {{ $med->strength }} · <code style="font-size:10px">{{ $med->code }}</code>
                        </div>
                        <div style="margin-top:5px">
                            <div style="height:4px;background:#f0f2ff;border-radius:2px;width:120px;overflow:hidden">
                                <div style="height:100%;width:{{ min($pct, 100) }}%;background:{{ $isOut ? '#dc2626' : '#b45309' }};border-radius:2px;transition:width .3s"></div>
                            </div>
                        </div>
                    </div>
                    <div style="text-align:right;flex-shrink:0">
                        <div style="font-size:18px;font-weight:800;color:{{ $isOut ? '#dc2626' : '#b45309' }}">
                            {{ $med->stock }}
                        </div>
                        <div style="font-size:10px;color:#aaa">/ Alert: {{ $med->stock_alert }}</div>
                        <span class="stock-badge {{ $isOut ? 'stock-out' : 'stock-low' }}" style="font-size:9px;margin-top:3px">
                            {{ $isOut ? 'Out of stock' : 'Low stock' }}
                        </span>
                    </div>
                    <a href="{{ route('inventory.stock-in') }}?medicine={{ $med->id }}"
                       class="btn btn-sm btn-outline-primary" style="flex-shrink:0;font-size:11px">
                        <i class="bi bi-plus"></i> Restock
                    </a>
                </div>
                @empty
                <div style="text-align:center;padding:32px;color:#aaa">
                    <div style="font-size:32px;margin-bottom:8px;opacity:.3">✅</div>
                    <div style="font-weight:600">All stock levels are healthy</div>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- ── Recent Transactions ───────────────────────────────────────────── --}}
    <div class="col-12 col-lg-6">
        <div class="card-emr">
            <div class="card-hd">
                <div class="card-hd-title">
                    <i class="bi bi-journal-text" style="color:#4154f1"></i>
                    Recent Inventory Transactions
                </div>
            </div>
            <div class="card-bd" style="padding:0">
                <div class="table-responsive">
                    <table class="tbl">
                        <thead>
                            <tr>
                                <th>Medicine</th>
                                <th>Type</th>
                                <th style="text-align:right">Qty</th>
                                <th style="text-align:right">After</th>
                                <th>Reference</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentTxns as $txn)
                            <tr>
                                <td>
                                    <div style="font-weight:600;font-size:12px">{{ Str::limit($txn->medicine_name, 22) }}</div>
                                    <div style="font-size:10px;color:#aaa">{{ $txn->medicine_code }}</div>
                                </td>
                                <td>
                                    @php
                                        $typeConfig = [
                                            'dispense'   => ['bg'=>'#fde8e8','color'=>'#dc2626','label'=>'Dispense'],
                                            'return'     => ['bg'=>'#e8f8ef','color'=>'#1D9E75','label'=>'Return'],
                                            'adjustment' => ['bg'=>'#f0f2ff','color'=>'#4154f1','label'=>'Adjust'],
                                        ];
                                        $tc = $typeConfig[$txn->type] ?? ['bg'=>'#f5f5f5','color'=>'#666','label'=>ucfirst($txn->type)];
                                    @endphp
                                    <span style="font-size:10px;padding:2px 7px;border-radius:8px;font-weight:700;background:{{ $tc['bg'] }};color:{{ $tc['color'] }}">
                                        {{ $tc['label'] }}
                                    </span>
                                </td>
                                <td style="text-align:right;font-weight:700;color:{{ $txn->quantity < 0 ? '#dc2626' : '#1D9E75' }}">
                                    {{ $txn->quantity > 0 ? '+'.$txn->quantity : $txn->quantity }}
                                </td>
                                <td style="text-align:right;font-weight:700;color:#012970">
                                    {{ $txn->stock_after }}
                                </td>
                                <td style="font-size:11px;color:#4154f1;font-family:monospace">
                                    {{ $txn->invoice_code ?? $txn->visit_code ?? '—' }}
                                </td>
                                <td style="font-size:11px;color:#aaa;white-space:nowrap">
                                    {{ $txn->created_at?->format('d/m H:i') }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" style="text-align:center;padding:24px;color:#aaa">No transactions yet</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>

<style>
.stock-badge { font-size:10px;padding:2px 8px;border-radius:10px;font-weight:700;display:inline-flex;align-items:center;gap:3px; }
.stock-ok    { background:#e8f8ef;color:#1D9E75;border:1px solid #b7eacf; }
.stock-low   { background:#fff8e1;color:#b45309;border:1px solid #fde68a; }
.stock-out   { background:#fde8e8;color:#dc2626;border:1px solid #fca5a5; }
</style>

@endsection
