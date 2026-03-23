@extends('clinics.layout.app')
@section('title', __('app.invoices'))
@section('content')

<x-page-header
    :title="__('app.invoices')"
    subtitle="Invoices"
    :breadcrumbs="[['label'=>__('app.home'),'url'=>route('dashboard')],['label'=>__('app.invoices')]]">
</x-page-header>

{{-- KPI --}}
<div class="row g-3 mb-3">
    @foreach([
        [$stats['today'],                        'bi-receipt-cutoff',   '#4154f1','#eef0fd', __('app.today'),    'Today'],
        [$stats['pending'],                      'bi-hourglass-split',  '#ff771d','#fff3e8', __('app.pending'),  'Pending'],
        [$stats['total'],                        'bi-archive-fill',     '#2eca6a','#e8f8ef', __('app.total'),    'Total'],
        [number_format($stats['revenue']).' KHR','bi-cash-stack',       '#9b59b6','#f0e8ff', __('app.today_revenue'),'Today Revenue'],
    ] as [$val,$ico,$col,$bg,$km,$en])
    <div class="col-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:{{ $bg }};color:{{ $col }}"><i class="bi {{ $ico }}"></i></div>
            <div>
                <div class="stat-num" style="color:{{ $col }};font-size:18px">{{ $val }}</div>
                <div class="stat-lbl">{{ $km }}<br><small>{{ $en }}</small></div>
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- Filter --}}
<div class="card-emr mb-3">
    <div class="card-bd">
        <form method="GET" action="{{ route('invoices.index') }}">
            <div class="row g-2 align-items-end">
                <div class="col-12 col-sm-4">
                    <input type="text" name="search" class="form-control"
                           placeholder="{{ __('app.search') }}… (code, {{ __('app.patient') }})"
                           value="{{ request('search') }}" autofocus/>
                </div>
                <div class="col-6 col-sm-2">
                    <select name="status" class="form-select">
                        <option value="">{{ __('app.all') }}</option>
                        <option value="pending" {{ request('status')==='pending'?'selected':'' }}>{{ __('app.pending') }}</option>
                        <option value="partial" {{ request('status')==='partial'?'selected':'' }}>{{ __('app.partial') }}</option>
                        <option value="paid"    {{ request('status')==='paid'?'selected':'' }}>{{ __('app.paid') }}</option>
                    </select>
                </div>
                <div class="col-6 col-sm-2">
                    <select name="payment_type" class="form-select">
                        <option value="">{{ __('app.all') }} Types</option>
                        <option value="HEF"  {{ request('payment_type')==='HEF'?'selected':'' }}>HEF</option>
                        <option value="NSSF" {{ request('payment_type')==='NSSF'?'selected':'' }}>NSSF</option>
                        <option value="CASH" {{ request('payment_type')==='CASH'?'selected':'' }}>CASH</option>
                    </select>
                </div>
                <div class="col-6 col-sm-2">
                    <input type="date" name="date" class="form-control" value="{{ request('date') }}"/>
                </div>
                <div class="col-auto d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-funnel-fill"></i></button>
                    @if(request()->hasAny(['search','status','payment_type','date']))
                    <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary"><i class="bi bi-x-circle"></i></a>
                    @endif
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Invoice list --}}
<div class="card-emr">
    <div class="card-hd">
        <div class="card-hd-title"><i class="bi bi-receipt-cutoff" style="color:#00bcd4"></i>
            {{ __('app.invoices') }}
        </div>
        <span style="font-size:11px;color:#aaa">{{ $invoices->total() }} {{ __('app.records') }}</span>
    </div>
    <div class="card-bd" style="padding:0">
        <div class="table-responsive">
            <table class="tbl">
                <thead>
                    <tr>
                        <th>{{ __('app.date') }}</th>
                        <th>{{ __('app.invoice') }}</th>
                        <th>{{ __('app.patient') }}</th>
                        <th>{{ __('app.payment_type') }}</th>
                        <th>{{ __('app.total') }} KHR</th>
                        <th>{{ __('app.paid') }} KHR</th>
                        <th>{{ __('app.balance') }}</th>
                        <th>{{ __('app.status') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                @forelse($invoices as $inv)
                @php
                    $paid    = $inv->payments->sum('amount');
                    $balance = $inv->total - $paid;
                    $status  = $inv->status ?? 'pending';
                    $statusColors = [
                        'paid'    => ['bg'=>'#e8f8ef','color'=>'#2eca6a','label'=> __('app.paid')],
                        'partial' => ['bg'=>'#fff3e8','color'=>'#ff771d','label'=> __('app.partial')],
                        'pending' => ['bg'=>'#fde8e8','color'=>'#e74c3c','label'=> __('app.pending')],
                    ];
                    $sc = $statusColors[$status] ?? $statusColors['pending'];
                    $ptColors = ['HEF'=>'#9b59b6','NSSF'=>'#2eca6a','CASH'=>'#ff771d'];
                @endphp
                <tr onclick="window.location='{{ route('invoices.show', $inv->code) }}'" style="cursor:pointer">
                    <td>
                        <div style="font-size:12px;font-weight:600;color:#012970">{{ $inv->invoice_date?->format('d/m/Y') ?? '—' }}</div>
                    </td>
                    <td><code style="font-size:11px;color:#00bcd4">{{ $inv->code }}</code></td>
                    <td>
                        @if($inv->patient)
                        <div style="font-weight:600;color:#012970;font-size:13px">{{ $inv->patient->surname }}, {{ $inv->patient->name }}</div>
                        <div style="font-size:10.5px;color:#aaa">{{ $inv->patient_code }}</div>
                        @else <span style="color:#bbb">—</span> @endif
                    </td>
                    <td>
                        <span style="font-size:10.5px;background:{{ ($ptColors[$inv->payment_type]??'#aaa') }}22;color:{{ $ptColors[$inv->payment_type]??'#aaa' }};padding:2px 9px;border-radius:10px;font-weight:700;border:1px solid {{ ($ptColors[$inv->payment_type]??'#aaa') }}44">
                            {{ $inv->payment_type }}
                        </span>
                    </td>
                    <td style="font-weight:700;color:#012970">{{ number_format($inv->total) }}</td>
                    <td style="color:#2eca6a;font-weight:700">{{ number_format($paid) }}</td>
                    <td style="color:{{ $balance > 0 ? '#e74c3c' : '#2eca6a' }};font-weight:700">
                        {{ $balance > 0 ? number_format($balance) : '—' }}
                    </td>
                    <td>
                        <span style="font-size:10.5px;background:{{ $sc['bg'] }};color:{{ $sc['color'] }};padding:2px 10px;border-radius:10px;font-weight:700;border:1px solid {{ $sc['color'] }}44">
                            {{ $sc['label'] }}
                        </span>
                    </td>
                    <td onclick="event.stopPropagation()">
                        <div style="display:flex;gap:4px">
                            <a href="{{ route('invoices.show', $inv->code) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye-fill"></i>
                            </a>
                            @if($balance > 0)
                            <a href="{{ route('invoices.show', $inv->code) }}#payment" class="btn btn-sm btn-success">
                                <i class="bi bi-cash"></i>
                            </a>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="9" style="text-align:center;padding:40px;color:#bbb">
                    <div style="font-size:36px;margin-bottom:10px;opacity:.3">🧾</div>
                    <div style="font-size:13px;font-weight:600;margin-bottom:6px">{{ __('app.no_records') }}</div>
                    <div style="font-size:11px">{{ __('app.invoices_created_from_workflow') }}</div>
                </td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@if($invoices->hasPages())
<div class="mt-3">{{ $invoices->links() }}</div>
@endif

@endsection
