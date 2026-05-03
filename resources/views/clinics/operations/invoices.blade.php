@extends('clinics.layout.app')
@section('title', __('app.invoices'))
@section('content')

    <x-ui.page-header
        :km="__('app.invoices')"
        title="Invoices"
        :breadcrumbs="[
            ['label' => __('app.home'), 'url' => route('dashboard')],
            ['label' => __('app.invoices')],
        ]">
        <x-slot:actions>
            <x-ui.button href="{{ route('invoices.create') }}" variant="primary" size="sm">
                <x-slot:icon><i class="bi bi-plus-circle-fill"></i></x-slot:icon>
                New Invoice
            </x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    {{-- KPI --}}
    <div class="row g-3 mb-3">
        @foreach([
            [$stats['today'],                        'bi-receipt-cutoff',   '#4154f1','#eef0fd', __('app.today'),    'Today'],
            [$stats['pending'],                      'bi-hourglass-split',  '#ff771d','#fff3e8', __('app.pending'),  'Pending'],
            [$stats['total'],                        'bi-archive-fill',     '#2eca6a','#e8f8ef', __('app.total'),    'Total'],
            ['$'.number_format($stats['revenue'],2),'bi-cash-stack',       '#9b59b6','#f0e8ff', __('app.today_revenue'),'Today Revenue'],
        ] as [$val,$ico,$col,$bg,$km,$en])
            <div class="col-6 col-xl-3">
                <div class="stat-card">
                    <div class="stat-icon" style="background:{{ $bg }};color:{{ $col }}"><i class="bi {{ $ico }}"></i>
                    </div>
                    <div>
                        <div class="stat-num" style="color:{{ $col }};font-size:18px">{{ $val }}</div>
                        <div class="stat-lbl">{{ $km }}<br><small>{{ $en }}</small></div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Filter --}}
    <x-ui.card class="mb-3">
        <form method="GET" action="{{ route('invoices.index') }}">
            <div class="row g-2 align-items-end">
                <div class="col-12 col-sm-4">
                    <input type="text" name="search" class="form-control"
                           placeholder="{{ __('app.search') }}… (code, {{ __('app.patient.code') }})"
                           value="{{ old('search', is_array(request('search')) ? '' : request('search')) }}"
                           autofocus/>
                </div>
                <div class="col-6 col-sm-2">
                    <select name="status" class="form-select">
                        <option value="">{{ __('app.all') }}</option>
                        <option value="pending" {{ request('status')==='pending'?'selected':'' }}>{{ __('app.pending') }}</option>
                        <option value="partial" {{ request('status')==='partial'?'selected':'' }}>{{ __('app.partial') }}</option>
                        <option value="paid" {{ request('status')==='paid'?'selected':'' }}>{{ __('app.paid') }}</option>
                    </select>
                </div>
                <div class="col-6 col-sm-2">
                    <select name="payment_type" class="form-select">
                        <option value="">{{ __('app.all') }} Types</option>
                        <option value="HEF" {{ request('payment_type')==='HEF'?'selected':'' }}>HEF</option>
                        <option value="NSSF" {{ request('payment_type')==='NSSF'?'selected':'' }}>NSSF</option>
                        <option value="CASH" {{ request('payment_type')==='CASH'?'selected':'' }}>CASH</option>
                    </select>
                </div>
                <div class="col-6 col-sm-2">
                    <input type="date" name="date" class="form-control" value="{{ request('date') }}"/>
                </div>
                <div class="col-auto d-flex gap-2">
                    <x-ui.button type="submit" variant="primary">
                        <x-slot:icon><i class="bi bi-funnel-fill"></i></x-slot:icon>
                    </x-ui.button>
                    @if(request()->hasAny(['search','status','payment_type','date']))
                        <x-ui.button href="{{ route('invoices.index') }}" variant="secondary">
                            <x-slot:icon><i class="bi bi-x-circle"></i></x-slot:icon>
                        </x-ui.button>
                    @endif
                </div>
            </div>
        </form>
    </x-ui.card>

    {{-- Invoice list --}}
    <x-ui.card title="{{ __('app.invoices') }}" km="{{ __('app.invoices') }}" icon="bi-receipt-cutoff" icon-color="#00bcd4" :no-padding="true">
        <x-slot:actions>
            <span style="font-size:11px;color:#aaa">{{ $invoices->total() }} {{ __('app.records') }}</span>
        </x-slot:actions>
        <div class="table-responsive">
            <table class="tbl">
                <thead>
                <tr>
                    <th>{{ __('app.date') }}</th>
                    <th>{{ __('app.invoice') }}</th>
                    <th>{{ __('app.patient.name') }}</th>
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
                        $statusVariant = match($status) {
                            'paid'    => 'success',
                            'partial' => 'warning',
                            'voided'  => 'danger',
                            'issued'  => 'primary',
                            default   => 'secondary',
                        };
                        $statusLabel = match($status) {
                            'paid'    => __('app.paid'),
                            'partial' => __('app.partial'),
                            'voided'  => 'Voided',
                            'issued'  => 'Issued',
                            default   => __('app.pending'),
                        };
                        $ptVariants = ['HEF'=>'primary','NSSF'=>'success','INSURANCE'=>'warning','CARD'=>'warning'];
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
                            @else
                                <span style="color:#bbb">—</span>
                            @endif
                        </td>
                        <td>
                            @if($inv->payment_type)
                                <x-ui.badge :variant="$ptVariants[$inv->payment_type] ?? 'secondary'">{{ $inv->payment_type }}</x-ui.badge>
                            @else
                                <span style="color:#bbb;font-size:11px">—</span>
                            @endif
                        </td>
                        <td style="font-weight:700;color:#012970">{{ number_format($inv->total) }}</td>
                        <td style="color:#2eca6a;font-weight:700">{{ number_format($paid) }}</td>
                        <td style="color:{{ $balance > 0 ? 'var(--danger)' : 'var(--accent)' }};font-weight:700">
                            {{ $balance > 0 ? number_format($balance) : '—' }}
                        </td>
                        <td><x-ui.badge :variant="$statusVariant">{{ $statusLabel }}</x-ui.badge></td>
                        <td onclick="event.stopPropagation()">
                            <div style="display:flex;gap:4px">
                                <x-ui.button href="{{ route('invoices.show', $inv->code) }}" variant="secondary" size="sm">
                                    <x-slot:icon><i class="bi bi-eye-fill"></i></x-slot:icon>
                                </x-ui.button>
                                @if($balance > 0)
                                    <x-ui.button href="{{ route('invoices.show', $inv->code) }}#payment" variant="primary" size="sm">
                                        <x-slot:icon><i class="bi bi-cash"></i></x-slot:icon>
                                    </x-ui.button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9">
                            <x-ui.empty-state icon="bi-receipt" title="{{ __('app.no_records') }}" description="{{ __('app.invoices_created_from_workflow') }}" />
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.card>

    <x-ui.pagination :paginator="$invoices" />

@endsection
