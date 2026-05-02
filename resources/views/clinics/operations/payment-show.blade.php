@extends('clinics.layout.app')
@section('title', 'Payment · '.$payment->code)
@section('content')

<x-page-header
    title="ការបង់ប្រាក់ / Payment"
    :subtitle="$payment->code"
    :breadcrumbs="[
        ['label'=>__('app.home'),'url'=>route('dashboard')],
        ['label'=>'Payments','url'=>route('payments.index')],
        ['label'=>$payment->code],
    ]">
    @if($payment->invoice)
        <a href="{{ route('invoices.show', $payment->invoice_code) }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-receipt-cutoff"></i> View Invoice
        </a>
    @endif
</x-page-header>

@php
    $methodColors = [
        'CASH'   => ['#2eca6a','#e8f8ef'],
        'HEF'    => ['#4154f1','#eef0fd'],
        'NSSF'   => ['#ff771d','#fff3e8'],
        'CARD'   => ['#9b59b6','#f0e8ff'],
        'BAKONG' => ['#e91e8c','#fde8f5'],
    ];
    [$mc,$mb] = $methodColors[$payment->method] ?? ['#aaa','#f5f5f5'];
@endphp

<div class="row g-3">

    {{-- Payment detail --}}
    <div class="col-12 col-lg-5">
        <div class="card-emr mb-3">
            <div class="card-hd" style="background:#f6f9ff">
                <div class="card-hd-title">
                    <i class="bi bi-cash-stack" style="color:#4154f1"></i> Payment Detail
                </div>
            </div>
            <div class="card-bd">
                @php
                    $detail = [
                        ['Payment Code',  $payment->code,                                   'code'],
                        ['Invoice',       $payment->invoice_code,                            'invoice'],
                        ['Patient',       $payment->patient_code,                            'patient'],
                        ['Amount',        number_format($payment->amount).' KHR',             'amount'],
                        ['Method',        $payment->method,                                   'method'],
                        ['Reference',     $payment->reference ?? '—',                         'ref'],
                        ['Collected By',  $payment->collected_by ?? '—',                      'by'],
                        ['Paid At',       $payment->paid_at?->format('d/m/Y H:i') ?? '—',    'date'],
                        ['Note',          $payment->note ?? '—',                              'note'],
                    ];
                @endphp
                @foreach($detail as [$label, $value, $key])
                <div style="display:flex;justify-content:space-between;align-items:flex-start;padding:9px 0;border-bottom:1px solid #f0f2ff">
                    <span style="font-size:11.5px;color:#888;flex-shrink:0;width:120px">{{ $label }}</span>
                    @if($key === 'amount')
                        <span style="font-weight:800;color:#012970;font-size:15px">{{ $value }}</span>
                    @elseif($key === 'method')
                        <span style="font-size:12px;padding:3px 12px;border-radius:8px;font-weight:700;background:{{ $mb }};color:{{ $mc }}">
                            {{ $value }}
                        </span>
                    @elseif($key === 'invoice')
                        <a href="{{ route('invoices.show', $payment->invoice_code) }}"
                           style="font-size:12px;color:#4154f1;font-weight:600">{{ $value }}</a>
                    @else
                        <span style="font-size:12px;color:#333;text-align:right">{{ $value }}</span>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Invoice summary (if linked) --}}
    <div class="col-12 col-lg-7">
        @if($payment->invoice)
        <div class="card-emr mb-3">
            <div class="card-hd" style="background:#f6f9ff">
                <div class="card-hd-title">
                    <i class="bi bi-receipt-cutoff" style="color:#ff771d"></i>
                    Invoice {{ $payment->invoice_code }}
                </div>
                <span style="font-size:11px;color:#aaa">{{ ucfirst($payment->invoice->status) }}</span>
            </div>
            <div class="card-bd">
                @php $inv = $payment->invoice; @endphp

                {{-- Patient --}}
                @if($inv->patient)
                <div style="display:flex;align-items:center;gap:10px;padding:10px 0;border-bottom:1px solid #f0f2ff;margin-bottom:12px">
                    <div style="width:36px;height:36px;border-radius:50%;background:#eef0fd;color:#4154f1;display:flex;align-items:center;justify-content:center;font-weight:800;flex-shrink:0">
                        {{ strtoupper(substr($inv->patient->surname ?? 'P', 0, 1)) }}
                    </div>
                    <div>
                        <div style="font-weight:700;color:#012970;font-size:13px">
                            {{ $inv->patient->surname }}, {{ $inv->patient->name }}
                        </div>
                        <div style="font-size:11px;color:#aaa">{{ $inv->patient_code }}</div>
                    </div>
                </div>
                @endif

                {{-- Invoice totals --}}
                <div style="display:flex;justify-content:space-between;padding:6px 0">
                    <span style="font-size:12px;color:#666">Invoice Total</span>
                    <span style="font-weight:700;color:#012970">{{ number_format($inv->total) }} KHR</span>
                </div>
                <div style="display:flex;justify-content:space-between;padding:6px 0">
                    <span style="font-size:12px;color:#666">Total Paid</span>
                    <span style="font-weight:700;color:#2eca6a">{{ number_format($inv->paid_amount) }} KHR</span>
                </div>
                <div style="display:flex;justify-content:space-between;padding:6px 0;border-top:2px solid #f0f2ff;margin-top:4px">
                    <span style="font-size:12px;font-weight:700;color:#444">Balance</span>
                    <span style="font-weight:800;font-size:14px;color:{{ $inv->balance > 0 ? '#e74c3c' : '#2eca6a' }}">
                        {{ number_format($inv->balance) }} KHR
                    </span>
                </div>

                {{-- All payments on this invoice --}}
                @if($inv->payments->count() > 1)
                <div style="margin-top:14px;padding-top:14px;border-top:1px solid #f0f2ff">
                    <div style="font-size:11px;font-weight:800;color:#4154f1;text-transform:uppercase;letter-spacing:.5px;margin-bottom:8px">
                        All Payments ({{ $inv->payments->count() }})
                    </div>
                    @foreach($inv->payments->sortByDesc('paid_at') as $p)
                    <div style="display:flex;align-items:center;gap:8px;padding:6px 0;border-bottom:1px solid #f8f9ff">
                        @php [$pc,$pb] = $methodColors[$p->method] ?? ['#aaa','#f5f5f5']; @endphp
                        <span style="font-size:10px;padding:2px 8px;border-radius:6px;font-weight:700;background:{{ $pb }};color:{{ $pc }}">
                            {{ $p->method }}
                        </span>
                        <span style="font-size:12px;font-weight:700;color:#012970;flex:1">
                            {{ number_format($p->amount) }} KHR
                        </span>
                        <span style="font-size:10.5px;color:#aaa">{{ $p->paid_at?->format('d/m H:i') }}</span>
                        @if($p->code === $payment->code)
                            <span style="font-size:9px;background:#eef0fd;color:#4154f1;padding:1px 6px;border-radius:4px;font-weight:700">
                                this
                            </span>
                        @endif
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
        @endif
    </div>

</div>

@endsection
