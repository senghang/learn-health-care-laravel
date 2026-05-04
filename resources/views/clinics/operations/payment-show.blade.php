@extends('clinics.layout.app')
@section('title', 'Payment · '.$payment->code)
@section('content')

@php
    $methodVariant = match($payment->method) {
        'CASH'   => 'success',
        'HEF'   => 'primary',
        'NSSF'  => 'warning',
        'CARD'  => 'secondary',
        'BAKONG'=> 'danger',
        default => 'secondary',
    };
    $methodColors = [
        'CASH'   => ['#2eca6a','#e8f8ef'],
        'HEF'    => ['#4154f1','#eef0fd'],
        'NSSF'   => ['#ff771d','#fff3e8'],
        'CARD'   => ['#9b59b6','#f0e8ff'],
        'BAKONG' => ['#e91e8c','#fde8f5'],
    ];
    [$mc,$mb] = $methodColors[$payment->method] ?? ['#9ca3af','#f9fafb'];
@endphp

<x-ui.page-header
    km="ការបង់ប្រាក់"
    title="Payment Detail"
    :breadcrumbs="[
        ['label' => __('app.home'), 'url' => route('dashboard')],
        ['label' => 'Payments', 'url' => route('payments.index')],
        ['label' => $payment->code],
    ]">
    <x-slot:actions>
        <x-ui.button href="{{ route('payments.index') }}" variant="secondary" size="sm">
            <x-slot:icon><i class="bi bi-arrow-left" aria-hidden="true"></i></x-slot:icon>
            Back
        </x-ui.button>
        @if($payment->invoice)
            <x-ui.button href="{{ route('invoices.show', $payment->invoice_code) }}" variant="secondary" size="sm">
                <x-slot:icon><i class="bi bi-receipt-cutoff" aria-hidden="true"></i></x-slot:icon>
                View Invoice
            </x-ui.button>
        @endif
    </x-slot:actions>
</x-ui.page-header>

<div class="grid grid-cols-1 lg:grid-cols-5 gap-5">

    {{-- ── Payment Detail ──────────────────────────────────────────────── --}}
    <div class="lg:col-span-2">
        <x-ui.card>
            <x-slot:header>
                <x-ui.card-header label="Payment Detail" icon="bi-cash-stack" />
            </x-slot:header>

            @php
                $detail = [
                    ['Payment Code',  $payment->code,                                   'code'],
                    ['Invoice',       $payment->invoice_code,                            'invoice'],
                    ['Patient',       $payment->patient_code,                            'patient'],
                    ['Amount',        number_format($payment->amount).' KHR',            'amount'],
                    ['Method',        $payment->method,                                  'method'],
                    ['Reference',     $payment->reference ?? '—',                        'ref'],
                    ['Collected By',  $payment->collected_by ?? '—',                     'by'],
                    ['Paid At',       $payment->paid_at?->format('d/m/Y H:i') ?? '—',   'date'],
                    ['Note',          $payment->note ?? '—',                             'note'],
                ];
            @endphp

            <div class="divide-y" style="border-color:#f1f5f9">
                @foreach($detail as [$label, $value, $key])
                    <div class="flex justify-between items-start py-2.5">
                        <span class="text-xs w-28 flex-shrink-0" style="color:#9ca3af">{{ $label }}</span>
                        @if($key === 'amount')
                            <span class="text-base font-extrabold" style="color:#1a1f36">{{ $value }}</span>
                        @elseif($key === 'method')
                            <x-ui.badge :variant="$methodVariant">{{ $value }}</x-ui.badge>
                        @elseif($key === 'invoice')
                            <a href="{{ route('invoices.show', $payment->invoice_code) }}"
                               class="text-xs font-semibold hover:underline" style="color:#4154f1">{{ $value }}</a>
                        @else
                            <span class="text-xs text-right" style="color:#374151">{{ $value }}</span>
                        @endif
                    </div>
                @endforeach
            </div>
        </x-ui.card>
    </div>

    {{-- ── Invoice Summary ──────────────────────────────────────────────── --}}
    <div class="lg:col-span-3">
        @if($payment->invoice)
            @php $inv = $payment->invoice; @endphp
            <x-ui.card>
                <x-slot:header>
                    <x-ui.card-header :label="'Invoice '.$payment->invoice_code" icon="bi-receipt-cutoff">
                        <x-slot:actions>
                            <x-ui.badge variant="{{ $inv->status === 'paid' ? 'success' : ($inv->status === 'partial' ? 'warning' : 'danger') }}">
                                {{ ucfirst($inv->status) }}
                            </x-ui.badge>
                        </x-slot:actions>
                    </x-ui.card-header>
                </x-slot:header>

                {{-- Patient --}}
                @if($inv->patient)
                    <div class="flex items-center gap-3 pb-4 mb-4 border-b" style="border-color:#f1f5f9">
                        <div class="w-9 h-9 rounded-full flex items-center justify-center flex-shrink-0 font-extrabold text-sm"
                             style="background:#eef0fd;color:#4154f1">
                            {{ strtoupper(substr($inv->patient->surname ?? 'P', 0, 1)) }}
                        </div>
                        <div>
                            <div class="text-sm font-bold" style="color:#1a1f36">
                                {{ $inv->patient->surname }}, {{ $inv->patient->name }}
                            </div>
                            <div class="text-xs" style="color:#9ca3af">{{ $inv->patient_code }}</div>
                        </div>
                    </div>
                @endif

                {{-- Invoice totals --}}
                <div class="space-y-2 mb-4">
                    <div class="flex justify-between items-center">
                        <span class="text-xs" style="color:#6b7280">Invoice Total</span>
                        <span class="text-sm font-bold" style="color:#1a1f36">{{ number_format($inv->total) }} KHR</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-xs" style="color:#6b7280">Total Paid</span>
                        <span class="text-sm font-bold" style="color:#2eca6a">{{ number_format($inv->paid_amount) }} KHR</span>
                    </div>
                    <div class="flex justify-between items-center pt-2 border-t" style="border-color:#e6eaf5">
                        <span class="text-xs font-bold" style="color:#374151">Balance</span>
                        <span class="text-sm font-extrabold" style="color:{{ $inv->balance > 0 ? '#e74c3c' : '#2eca6a' }}">
                            {{ number_format($inv->balance) }} KHR
                        </span>
                    </div>
                </div>

                {{-- All payments on this invoice --}}
                @if($inv->payments->count() > 1)
                    <div class="border-t pt-4" style="border-color:#e6eaf5">
                        <div class="text-xs font-extrabold uppercase tracking-wide mb-3" style="color:#4154f1">
                            All Payments ({{ $inv->payments->count() }})
                        </div>
                        <div class="space-y-2">
                            @foreach($inv->payments->sortByDesc('paid_at') as $p)
                                @php [$pc,$pb] = $methodColors[$p->method] ?? ['#9ca3af','#f9fafb']; @endphp
                                <div class="flex items-center gap-2 py-1.5 border-b" style="border-color:#f9fafb">
                                    <span class="text-xs px-2 py-0.5 rounded-md font-bold"
                                          style="background:{{ $pb }};color:{{ $pc }}">
                                        {{ $p->method }}
                                    </span>
                                    <span class="text-xs font-bold flex-1" style="color:#1a1f36">
                                        {{ number_format($p->amount) }} KHR
                                    </span>
                                    <span class="text-xs" style="color:#9ca3af">{{ $p->paid_at?->format('d/m H:i') }}</span>
                                    @if($p->code === $payment->code)
                                        <span class="text-xs px-1.5 py-0.5 rounded font-bold"
                                              style="background:#eef0fd;color:#4154f1">this</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </x-ui.card>
        @endif
    </div>

</div>

@endsection
