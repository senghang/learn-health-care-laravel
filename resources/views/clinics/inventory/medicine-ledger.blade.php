@extends('clinics.layout.app')
@section('title', 'Ledger — ' . $medicine->name)
@section('content')

<div class="flex items-center justify-between mb-4 flex-wrap gap-3">
    <div>
        <x-ui.breadcrumbs :items="[
            ['label'=>'ដើម','url'=>route('dashboard')],
            ['label'=>'Inventory','url'=>route('inventory.products')],
            ['label'=>$medicine->code, 'url'=>route('inventory.products')],
            ['label'=>'Ledger'],
        ]" />
        <h1 class="text-xl font-black mt-1" style="color:#012970">
            {{ $medicine->name }}
            <span class="text-sm font-normal text-slate-400">/ Stock Ledger / ប្រវត្តិស្តុក</span>
        </h1>
    </div>
    <div class="flex items-center gap-2">
        <x-ui.button variant="secondary" size="sm" href="{{ route('inventory.product.edit', $medicine->id) }}">
            <i class="bi bi-pencil"></i> Edit Product
        </x-ui.button>
        <x-ui.button variant="success" size="sm" href="{{ route('inventory.stock-in') }}?medicine={{ $medicine->id }}">
            <i class="bi bi-plus-circle-fill"></i> Stock In
        </x-ui.button>
        <x-ui.button variant="warning" size="sm" href="{{ route('inventory.adjustment') }}">
            <i class="bi bi-sliders"></i> Adjust
        </x-ui.button>
    </div>
</div>

<div class="row g-3">

{{-- Sidebar: medicine info + balance --}}
<div class="col-12 col-lg-3">
    <x-ui.card class="mb-3" style="position:sticky;top:76px">
        <x-slot:header>
            <x-ui.card-header label="Product Info" icon="bi-capsule-pill"/>
        </x-slot:header>
        @php
            $isOut = $medicine->stock <= 0;
            $isLow = !$isOut && $medicine->stock <= $medicine->stock_alert;
            $stockColor = $isOut ? '#e74c3c' : ($isLow ? '#ff771d' : '#2eca6a');
        @endphp
        <div style="text-align:center;margin-bottom:16px">
            <div style="font-size:36px;font-weight:900;color:{{ $stockColor }}">{{ $medicine->stock }}</div>
            <div style="font-size:13px;color:#666">{{ $medicine->unit ?? 'units' }} in stock</div>
            @if($isOut)
                <div style="margin-top:6px;font-size:11px;padding:3px 10px;border-radius:10px;background:#fde8e8;color:#e74c3c;font-weight:700;display:inline-block">OUT OF STOCK</div>
            @elseif($isLow)
                <div style="margin-top:6px;font-size:11px;padding:3px 10px;border-radius:10px;background:#fff3e8;color:#ff771d;font-weight:700;display:inline-block">LOW STOCK</div>
            @endif
        </div>

        @foreach([
            ['Code',        $medicine->code],
            ['Category',    $medicine->category ?? '—'],
            ['Form',        $medicine->form ?? '—'],
            ['Strength',    $medicine->strength ?? '—'],
            ['Unit Price',  number_format($medicine->price) . ' KHR'],
            ['Alert Level', $medicine->stock_alert],
            ['Status',      $medicine->is_active ? 'Active' : 'Inactive'],
        ] as [$label, $val])
        <div style="display:flex;justify-content:space-between;font-size:11.5px;padding:4px 0;border-bottom:1px solid #f5f6ff">
            <span style="color:#aaa">{{ $label }}</span>
            <span style="font-weight:600;color:#012970">{{ $val }}</span>
        </div>
        @endforeach

        @if($balance)
        <div style="margin-top:12px;padding:10px;background:#f0fcff;border-radius:8px;font-size:11px">
            <div style="font-weight:700;color:#00bcd4;margin-bottom:6px">Balance Table</div>
            <div style="display:flex;justify-content:space-between;margin-bottom:3px">
                <span style="color:#aaa">On Hand</span>
                <span style="font-weight:700">{{ $balance->quantity_on_hand }}</span>
            </div>
            <div style="display:flex;justify-content:space-between;margin-bottom:3px">
                <span style="color:#aaa">Stock Value</span>
                <span style="font-weight:700">{{ number_format($balance->total_value) }} KHR</span>
            </div>
            <div style="display:flex;justify-content:space-between">
                <span style="color:#aaa">Last Movement</span>
                <span style="font-weight:700">{{ $balance->last_movement_at?->format('d/m/Y') ?? '—' }}</span>
            </div>
        </div>
        @endif
    </x-ui.card>
</div>

{{-- Ledger --}}
<div class="col-12 col-lg-9">
    <x-ui.card :noPadding="true">
        <x-slot:header>
            <x-ui.card-header label="Full Movement History" icon="bi-journal-text">
                <span style="font-size:11px;color:#aaa">{{ count($ledger) }} entries (last 150)</span>
            </x-ui.card-header>
        </x-slot:header>

        {{-- Legend --}}
        <div style="padding:8px 14px;border-bottom:1px solid #f0f2ff;display:flex;gap:14px;flex-wrap:wrap">
            @foreach([
                ['manual clinical', '#eef0fd','#4154f1'],
                ['in / return',     '#e8f8ef','#2eca6a'],
                ['out / expired',   '#fde8e8','#e74c3c'],
                ['adjustment',      '#fff8e1','#b45309'],
            ] as [$lbl,$bg,$col])
            <span style="font-size:10.5px;padding:2px 8px;border-radius:8px;background:{{ $bg }};color:{{ $col }};font-weight:600">{{ $lbl }}</span>
            @endforeach
        </div>

        <div class="table-responsive">
            <table class="tbl">
                <thead>
                    <tr>
                        <th>Date / Time</th>
                        <th>Source</th>
                        <th>Type</th>
                        <th style="text-align:center">Qty</th>
                        <th style="text-align:center">Before</th>
                        <th style="text-align:center">After</th>
                        <th>Reference</th>
                        <th>By / Note</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($ledger as $row)
                @php
                    $qty     = $row['quantity'];
                    $isPlus  = str_starts_with($qty, '+');
                    $isAdj   = $row['type'] === 'adjustment';
                    $isClin  = $row['source'] === 'clinical';

                    $rowBg = $isClin ? '#fafbff' : '';
                    $typeMap = [
                        'in'         => ['Stock In',    '#2eca6a','#e8f8ef'],
                        'return'     => ['Return',      '#ff771d','#fff3e8'],
                        'out'        => ['Out',         '#e74c3c','#fde8e8'],
                        'expired'    => ['Expired',     '#9b59b6','#f5eeff'],
                        'adjustment' => ['Adjusted',    '#b45309','#fff8e1'],
                        'dispense'   => ['Dispense',    '#e74c3c','#fde8e8'],
                    ];
                    [$lbl,$col,$bg] = $typeMap[$row['type']] ?? [ucfirst($row['type']),'#aaa','#f5f5f5'];
                @endphp
                <tr style="background:{{ $rowBg }}">
                    <td>
                        <div style="font-size:12px;font-weight:600;color:#012970">{{ $row['date']->format('d/m/Y') }}</div>
                        <div style="font-size:10px;color:#aaa">{{ $row['date']->format('H:i') }}</div>
                    </td>
                    <td>
                        <span style="font-size:10px;padding:1px 7px;border-radius:8px;font-weight:700;
                            background:{{ $isClin?'#eef0fd':'#e8f8ef' }};
                            color:{{ $isClin?'#4154f1':'#2eca6a' }}">
                            {{ $isClin ? 'Clinical' : 'Manual' }}
                        </span>
                    </td>
                    <td>
                        <span style="font-size:10.5px;padding:2px 8px;border-radius:8px;font-weight:700;background:{{ $bg }};color:{{ $col }}">
                            {{ $lbl }}
                        </span>
                    </td>
                    <td style="text-align:center;font-size:15px;font-weight:800;
                        color:{{ $isAdj ? ($isPlus?'#2eca6a':'#e74c3c') : ($isPlus?'#2eca6a':'#e74c3c') }}">
                        {{ $qty }}
                    </td>
                    <td style="text-align:center;color:#aaa;font-size:12px">{{ $row['before'] }}</td>
                    <td style="text-align:center;font-weight:700;
                        color:{{ ($row['after']??0)<=0?'#e74c3c':(($row['after']??0)<=10?'#ff771d':'#012970') }}">
                        {{ $row['after'] }}
                    </td>
                    <td style="font-size:11px;color:#4154f1;font-family:monospace">
                        {{ $row['reference'] ?? '—' }}
                    </td>
                    <td style="font-size:11px;color:#666;max-width:160px">
                        <div style="color:#aaa;font-size:10px">{{ $row['by'] }}</div>
                        {{ Str::limit($row['note'] ?? '', 50) }}
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" style="text-align:center;padding:36px;color:#bbb">
                    No movement history yet
                </td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.card>
</div>

</div>
@endsection
