@extends('clinics.layout.app')
@section('title', 'INV ' . $invoice->code)
@section('content')

    @php
        $paid    = $invoice->payments->sum('amount');
        $balance = $invoice->total - $paid;
        $status  = $invoice->status ?? 'pending';
        $statusMap = [
            'paid'    => ['bg'=>'#e8f8ef','color'=>'#2eca6a','label'=> __('app.paid')],
            'partial' => ['bg'=>'#fff3e8','color'=>'#ff771d','label'=> __('app.partial')],
            'pending' => ['bg'=>'#fde8e8','color'=>'#e74c3c','label'=> __('app.pending')],
        ];
        $sc = $statusMap[$status] ?? $statusMap['pending'];
    @endphp

    <x-page-header
        title="{{ $invoice->code }}"
        subtitle="Invoice Detail"
        :breadcrumbs="[
        ['label'=>__('app.home'),'url'=>route('dashboard')],
        ['label'=>__('app.invoices'),'url'=>route('invoices.index')],
        ['label'=>$invoice->code],
    ]">
    <span
        style="background:{{ $sc['bg'] }};color:{{ $sc['color'] }};padding:4px 14px;border-radius:20px;font-size:12px;font-weight:700;border:1px solid {{ $sc['color'] }}44">
        {{ $sc['label'] }}
    </span>
    @if(!in_array($status, ['paid', 'void']))
        <a href="{{ route('invoices.edit', $invoice->code) }}" class="btn btn-outline-warning btn-sm">
            <i class="bi bi-pencil-fill"></i> {{ __('app.edit') }}
        </a>
    @endif
    @if($status !== 'void')
        <form method="POST" action="{{ route('invoices.void', $invoice->code) }}" class="d-inline"
              onsubmit="return confirm('Void this invoice?')">
            @csrf
            <button type="submit" class="btn btn-outline-danger btn-sm">
                <i class="bi bi-slash-circle"></i> Void
            </button>
        </form>
    @endif
        <a href="{{ route('print.invoice', $invoice->code) }}" class="btn btn-outline-primary btn-sm" target="_blank">
            <i class="bi bi-printer-fill"></i> {{ __('app.print') }}
        </a>
    </x-page-header>

    @if(session('success'))
        <div class="note note-success mb-3"><i class="bi bi-check-circle-fill"></i> {{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="note note-danger mb-3"><i class="bi bi-exclamation-triangle-fill"></i> {{ session('error') }}</div>
    @endif

    <div class="row g-3">

        {{-- Invoice body --}}
        <div class="col-12 col-lg-8">

            {{-- Header info --}}
            <div class="card-emr mb-3">
                <div class="card-hd" style="background:#f0fcff">
                    <div class="card-hd-title">
                        <i class="bi bi-receipt-cutoff" style="color:#00bcd4"></i>
                        {{ __('app.invoice') }}
                        <code
                            style="font-size:12px;color:#00bcd4;background:#e0f7fa;padding:2px 8px;border-radius:6px">{{ $invoice->code }}</code>
                    </div>
                    <span style="font-size:11px;color:#aaa">{{ $invoice->invoice_date?->format('d/m/Y') }}</span>
                </div>
                <div class="card-bd">
                    <div class="row g-3 mb-4">
                        @foreach([
                            [__('app.patient.name'), $invoice->patient?->surname.', '.$invoice->patient?->name, $invoice->patient_code],
                            [__('app.visit.code'),        $invoice->visit_code ?? '—', null],
                            [__('app.payment_type'), $invoice->payment_type, null],
                            [__('app.cashier'),      $invoice->cashier ?? '—', null],
                        ] as [$label, $val, $sub])
                            <div class="col-6 col-sm-3">
                                <div
                                    style="font-size:10.5px;color:#aaa;font-weight:700;text-transform:uppercase;letter-spacing:.4px;margin-bottom:3px">
                                    {{ is_array($label) ? implode(', ', $label) : $label }}
                                </div>
                                <div style="font-weight:600;color:#012970;font-size:13px">{{ $val }}</div>
                                @if($sub)
                                    <div style="font-size:10.5px;color:#aaa">{{ $sub }}</div>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    {{-- Services --}}
                    @if($invoice->services->count())
                        <div
                            style="font-size:10.5px;font-weight:800;text-transform:uppercase;letter-spacing:.5px;color:#00bcd4;margin-bottom:8px">
                            {{ __('app.services') }}
                        </div>
                        <div class="table-responsive mb-4">
                            <table class="tbl">
                                <thead>
                                <tr>
                                    <th>{{ __('app.service') }}</th>
                                    <th>{{ __('app.category') }}</th>
                                    <th style="text-align:center">Qty</th>
                                    <th style="text-align:right">Unit Price KHR</th>
                                    <th style="text-align:right">Subtotal KHR</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($invoice->services as $svc)
                                    @php $qty = $svc->qty ?? 1; @endphp
                                    <tr>
                                        <td style="font-weight:600">{{ $svc->service_name }}</td>
                                        <td style="font-size:11px;color:#aaa">{{ $svc->service_category ?? '—' }}</td>
                                        <td style="text-align:center;color:#aaa">{{ $qty }}</td>
                                        <td style="text-align:right">{{ number_format($svc->price) }}</td>
                                        <td style="text-align:right;font-weight:700">{{ number_format($qty * $svc->price) }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif

                    {{-- Medications --}}
                    @if($invoice->medications->count())
                        <div
                            style="font-size:10.5px;font-weight:800;text-transform:uppercase;letter-spacing:.5px;color:#9b59b6;margin-bottom:8px">
                            {{ __('app.medications') }}
                        </div>
                        <div class="table-responsive mb-4">
                            <table class="tbl">
                                <thead>
                                <tr>
                                    <th>{{ __('app.medicine') }}</th>
                                    <th>{{ __('app.qty') }}</th>
                                    <th style="text-align:right">{{ __('app.price') }} KHR</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($invoice->medications as $med)
                                    <tr>
                                        <td style="font-weight:600">{{ $med->medicine_name }}</td>
                                        <td style="color:#aaa">{{ $med->quantity }}</td>
                                        <td style="text-align:right;font-weight:700">{{ number_format($med->price) }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif

                    {{-- Totals --}}
                    <div style="background:#f6f9ff;border-radius:10px;padding:14px 16px;border:1px solid #e0e6f5">
                        <div style="display:flex;justify-content:space-between;margin-bottom:6px;font-size:12px">
                            <span style="color:#555">{{ __('app.total') }}</span>
                            <span style="font-weight:800;color:#012970;font-size:16px">{{ number_format($invoice->total) }} KHR</span>
                        </div>
                        <div style="display:flex;justify-content:space-between;margin-bottom:6px;font-size:12px">
                            <span style="color:#2eca6a">{{ __('app.paid') }}</span>
                            <span style="font-weight:700;color:#2eca6a">{{ number_format($paid) }} KHR</span>
                        </div>
                        @if($balance > 0)
                            <div
                                style="display:flex;justify-content:space-between;font-size:13px;padding-top:8px;border-top:1px dashed #e0e6f5;margin-top:8px">
                                <span style="color:#e74c3c;font-weight:700">{{ __('app.balance') }}</span>
                                <span style="font-weight:800;color:#e74c3c;font-size:18px">{{ number_format($balance) }} KHR</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Payment history --}}
            @if($invoice->payments->count())
                <div class="card-emr mb-3">
                    <div class="card-hd">
                        <div class="card-hd-title"><i class="bi bi-cash-stack"
                                                      style="color:#2eca6a"></i> {{ __('app.payment_history') }}</div>
                    </div>
                    <div class="card-bd" style="padding:0">
                        <table class="tbl">
                            <thead>
                            <tr>
                                <th>{{ __('app.date') }}</th>
                                <th>{{ __('app.method') }}</th>
                                <th>{{ __('app.reference') }}</th>
                                <th>{{ __('app.collected_by') }}</th>
                                <th style="text-align:right">{{ __('app.amount') }} KHR</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($invoice->payments as $pay)
                                <tr>
                                    <td style="font-size:12px">{{ $pay->paid_at?->format('d/m/Y H:i') ?? '—' }}</td>
                                    <td><span
                                            style="font-size:10.5px;background:#e8f8ef;color:#2eca6a;padding:2px 9px;border-radius:10px;font-weight:700">{{ $pay->method }}</span>
                                    </td>
                                    <td style="font-size:11px;color:#aaa">{{ $pay->reference ?? '—' }}</td>
                                    <td style="font-size:12px;color:#555">{{ $pay->collected_by ?? '—' }}</td>
                                    <td style="text-align:right;font-weight:700;color:#2eca6a">{{ number_format($pay->amount) }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

        </div>

        {{-- Sidebar: payment form --}}
        <div class="col-12 col-lg-4">
            @if($balance > 0)
                <div class="card-emr mb-3" id="payment" style="position:sticky;top:76px">
                    <div class="card-hd" style="background:#e8f8ef">
                        <div class="card-hd-title">
                            <i class="bi bi-cash-coin" style="color:#2eca6a"></i>
                            {{ __('app.collect_payment') }}
                        </div>
                        <span
                            style="font-size:13px;font-weight:800;color:#e74c3c">{{ number_format($balance) }} KHR {{ __('app.due') }}</span>
                    </div>
                    <div class="card-bd">
                        @if(session('flash'))
                            <div class="note note-success mb-3"><i
                                    class="bi bi-check-circle-fill"></i> {{ session('flash') }}</div>
                        @endif
                        @if($errors->any())
                            <div class="note note-danger mb-3">
                                <i class="bi bi-exclamation-triangle-fill"></i>
                                <ul style="margin:0;padding-left:14px;font-size:12px">@foreach($errors->all() as $e)
                                        <li>{{ $e }}</li>
                                    @endforeach</ul>
                            </div>
                        @endif
                        <form method="POST" action="{{ route('invoices.payment', $invoice->code) }}" novalidate>
                            @csrf
                            <x-form.field name="amount" :km="__('app.amount')" en="Amount KHR"
                                          type="number" :value="old('amount', $balance)" required/>
                            <x-form.select name="method" :km="__('app.method')" en="Method"
                                           :options="['CASH'=>'CASH','HEF'=>'HEF','NSSF'=>'NSSF','CARD'=>'Card','BAKONG'=>'Bakong']"
                                           :value="old('method','CASH')"/>
                            <x-form.field name="reference" :km="__('app.reference')" en="Ref No."
                                          :value="old('reference')"/>
                            <x-form.field name="collected_by" :km="__('app.collected_by')" en="Collected By"
                                          :value="old('collected_by', auth()->user()?->name)"/>
                            <button type="submit" class="btn btn-success btn-w100 mt-1">
                                <i class="bi bi-check2-circle"></i> {{ __('app.confirm_payment') }}
                            </button>
                        </form>
                    </div>
                </div>
            @else
                <div class="card-emr" style="position:sticky;top:76px">
                    <div class="card-bd" style="text-align:center;padding:24px">
                        <div style="font-size:36px;margin-bottom:8px">✅</div>
                        <div style="font-weight:700;color:#2eca6a;font-size:14px">{{ __('app.fully_paid') }}</div>
                        <div style="font-size:11px;color:#aaa;margin-top:4px">{{ number_format($paid) }} KHR</div>
                    </div>
                </div>
            @endif

            {{-- Patient quick info --}}
            @if($invoice->patient)
                <div class="card-emr mt-3">
                    <div class="card-hd" style="background:#f6f9ff">
                        <div class="card-hd-title"><i class="bi bi-person-fill"
                                                      style="color:#4154f1"></i> {{ __('app.patient.name') }}</div>
                    </div>
                    <div class="card-bd">
                        <div style="font-weight:700;color:#012970;margin-bottom:4px">{{ $invoice->patient->surname }}
                            , {{ $invoice->patient->name }}</div>
                        <div style="font-size:11px;color:#aaa;margin-bottom:10px">{{ $invoice->patient->code }}
                            · {{ $invoice->patient->phone ?? '—' }}</div>
                        <a href="{{ route('patients.show', $invoice->patient->code) }}"
                           class="btn btn-outline-primary btn-sm btn-w100">
                            <i class="bi bi-person-fill"></i> {{ __('app.view_patient') }}
                        </a>
                    </div>
                </div>
            @endif
        </div>

    </div>
@endsection
