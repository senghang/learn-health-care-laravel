@extends('clinics.layout.app')
@section('title', 'Payments')
@section('content')

<x-ui.page-header
    km="ការបង់ប្រាក់"
    title="Billing — Payment Ledger"
    :breadcrumbs="[
        ['label' => __('app.home'), 'url' => route('dashboard')],
        ['label' => __('app.invoices'), 'url' => route('invoices.index')],
        ['label' => 'Payments'],
    ]">
</x-ui.page-header>

{{-- KPI --}}
<div class="row g-3 mb-3">
    @foreach([
        [number_format($todayTotal).' KHR', 'bi-cash-stack',       '#4154f1','#eef0fd', 'ប្រាក់ថ្ងៃនេះ',    'Today Revenue'],
        [$todayCount,                        'bi-receipt-cutoff',   '#2eca6a','#e8f8ef', 'ការទូទាត់ថ្ងៃនេះ','Today Payments'],
        [number_format($monthTotal).' KHR',  'bi-graph-up-arrow',   '#ff771d','#fff3e8', 'ខែនេះ',           'This Month'],
    ] as [$val,$ico,$col,$bg,$km,$en])
    <div class="col-12 col-md-4">
        <div class="stat-card">
            <div class="stat-icon" style="background:{{ $bg }};color:{{ $col }}"><i class="bi {{ $ico }}"></i></div>
            <div>
                <div class="stat-num" style="color:{{ $col }};font-size:16px">{{ $val }}</div>
                <div class="stat-lbl">{{ $km }}<br><small>{{ $en }}</small></div>
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- Filters --}}
<x-ui.card class="mb-3">
    <form method="GET" action="{{ route('payments.index') }}">
        <div class="row g-2 align-items-end">
            <div class="col-12 col-sm-3">
                <input type="text" name="search" class="form-control"
                       value="{{ request('search') }}"
                       placeholder="Code, invoice, patient, ref…" autofocus/>
            </div>
            <div class="col-6 col-sm-2">
                <select name="method" class="form-select">
                    <option value="">All Methods</option>
                    @foreach(['CASH'=>'Cash','HEF'=>'HEF','NSSF'=>'NSSF','CARD'=>'Card','BAKONG'=>'Bakong'] as $v=>$l)
                        <option value="{{ $v }}" {{ request('method')===$v?'selected':'' }}>{{ $l }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-sm-2">
                <input type="date" name="from" class="form-control"
                       value="{{ request('from') }}" placeholder="From date"/>
            </div>
            <div class="col-6 col-sm-2">
                <input type="date" name="to" class="form-control"
                       value="{{ request('to') }}" placeholder="To date"/>
            </div>
            <div class="col-auto d-flex gap-2">
                <x-ui.button type="submit" variant="primary">
                    <x-slot:icon><i class="bi bi-funnel-fill"></i></x-slot:icon>
                </x-ui.button>
                @if(request()->hasAny(['search','method','from','to']))
                    <x-ui.button href="{{ route('payments.index') }}" variant="secondary" size="sm">
                        <x-slot:icon><i class="bi bi-x-circle"></i></x-slot:icon>
                    </x-ui.button>
                @endif
            </div>
        </div>
    </form>
</x-ui.card>

{{-- Table --}}
<x-ui.card title="Payment Records" icon="bi-cash-stack" icon-color="#4154f1" :no-padding="true">
    <x-slot:actions>
        <span style="font-size:11px;color:#aaa">{{ $payments->total() }} records</span>
    </x-slot:actions>
    <div class="table-responsive">
        <table class="tbl">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Patient</th>
                    <th>Invoice</th>
                    <th>Amount</th>
                    <th>Method</th>
                    <th>Reference</th>
                    <th>Paid At</th>
                    <th>Collected By</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            @forelse($payments as $pay)
            @php
                $methodColors = [
                    'CASH'   => ['#2eca6a','#e8f8ef'],
                    'HEF'    => ['#4154f1','#eef0fd'],
                    'NSSF'   => ['#ff771d','#fff3e8'],
                    'CARD'   => ['#9b59b6','#f0e8ff'],
                    'BAKONG' => ['#e91e8c','#fde8f5'],
                ];
                [$mc,$mb] = $methodColors[$pay->method] ?? ['#aaa','#f5f5f5'];
            @endphp
            <tr onclick="location.href='{{ route('payments.show', $pay->code) }}'" style="cursor:pointer">
                <td>
                    <code style="color:#4154f1;font-size:11px">{{ $pay->code }}</code>
                </td>
                <td>
                    @if($pay->patient)
                        <div style="font-weight:700;color:#012970;font-size:12.5px">
                            {{ $pay->patient->surname }}, {{ $pay->patient->name }}
                        </div>
                        <div style="font-size:10.5px;color:#aaa">{{ $pay->patient_code }}</div>
                    @else
                        <span style="font-size:11px;color:#bbb">{{ $pay->patient_code }}</span>
                    @endif
                </td>
                <td>
                    @if($pay->invoice)
                        <a href="{{ route('invoices.show', $pay->invoice_code) }}"
                           onclick="event.stopPropagation()"
                           style="font-size:11px;color:#4154f1;font-weight:600">
                            {{ $pay->invoice_code }}
                        </a>
                        <div style="font-size:10.5px;color:#aaa">{{ ucfirst($pay->invoice->status) }}</div>
                    @else
                        <span style="font-size:11px;color:#bbb">{{ $pay->invoice_code }}</span>
                    @endif
                </td>
                <td>
                    <div style="font-weight:800;color:#012970;font-size:13.5px">
                        {{ number_format($pay->amount) }}
                    </div>
                    <div style="font-size:10px;color:#aaa">KHR</div>
                </td>
                <td>
                    <span style="font-size:11px;padding:3px 10px;border-radius:8px;font-weight:700;background:{{ $mb }};color:{{ $mc }}">
                        {{ $pay->method }}
                    </span>
                </td>
                <td>
                    <span style="font-size:11px;color:#777;font-family:monospace">
                        {{ $pay->reference ?? '—' }}
                    </span>
                </td>
                <td>
                    @if($pay->paid_at)
                        <div style="font-size:12px;color:#555">{{ $pay->paid_at->format('d/m/Y') }}</div>
                        <div style="font-size:10.5px;color:#aaa">{{ $pay->paid_at->format('H:i') }}</div>
                    @else
                        <span style="color:#ddd">—</span>
                    @endif
                </td>
                <td style="font-size:11.5px;color:#666">{{ $pay->collected_by ?? '—' }}</td>
                <td onclick="event.stopPropagation()">
                    <x-ui.button href="{{ route('payments.show', $pay->code) }}" variant="secondary" size="sm">
                        <x-slot:icon><i class="bi bi-eye"></i></x-slot:icon>
                    </x-ui.button>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9" style="text-align:center;padding:40px;color:#bbb">
                    <div style="font-size:36px;margin-bottom:10px;opacity:.3">💳</div>
                    No payments found
                </td>
            </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</x-ui.card>

<x-ui.pagination :paginator="$payments" />

@endsection
