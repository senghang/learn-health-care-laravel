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
<div class="grid grid-cols-3 gap-4 mb-5">
    @foreach([
        [number_format($todayTotal).' KHR', 'bi-cash-stack',      '#4154f1','#eef0fd', 'ប្រាក់ថ្ងៃនេះ',     'Today Revenue'],
        [$todayCount,                        'bi-receipt-cutoff',  '#2eca6a','#e8f8ef', 'ការទូទាត់ថ្ងៃនេះ', 'Today Payments'],
        [number_format($monthTotal).' KHR',  'bi-graph-up-arrow',  '#ff771d','#fff3e8', 'ខែនេះ',            'This Month'],
    ] as [$val,$ico,$col,$bg,$km,$en])
        <x-ui.stats-card :value="$val" :km="$km" :label="$en" :icon="$ico" :color="$col" :bg="$bg" />
    @endforeach
</div>

{{-- Filters --}}
<x-ui.card class="mb-4">
    <form method="GET" action="{{ route('payments.index') }}">
        <div class="flex flex-wrap gap-2 items-end">
            <div class="flex-1 min-w-40">
                <x-forms.input type="text" name="search"
                               :value="request('search')"
                               placeholder="Code, invoice, patient, ref…" autofocus />
            </div>
            <div class="w-36">
                <x-forms.select name="method">
                    <option value="">All Methods</option>
                    @foreach(['CASH'=>'Cash','HEF'=>'HEF','NSSF'=>'NSSF','CARD'=>'Card','BAKONG'=>'Bakong'] as $v=>$l)
                        <option value="{{ $v }}" {{ request('method')===$v?'selected':'' }}>{{ $l }}</option>
                    @endforeach
                </x-forms.select>
            </div>
            <div class="w-36">
                <x-forms.input type="date" name="from" :value="request('from')" />
            </div>
            <div class="w-36">
                <x-forms.input type="date" name="to" :value="request('to')" />
            </div>
            <div class="flex gap-2">
                <x-ui.button type="submit" variant="primary">
                    <x-slot:icon><i class="bi bi-funnel-fill" aria-hidden="true"></i></x-slot:icon>
                </x-ui.button>
                @if(request()->hasAny(['search','method','from','to']))
                    <x-ui.button href="{{ route('payments.index') }}" variant="secondary">
                        <x-slot:icon><i class="bi bi-x-circle" aria-hidden="true"></i></x-slot:icon>
                    </x-ui.button>
                @endif
            </div>
        </div>
    </form>
</x-ui.card>

{{-- Table --}}
<x-ui.card noPadding>
    <x-slot:header>
        <x-ui.card-header label="Payment Records" icon="bi-cash-stack">
            <x-slot:actions>
                <span class="text-xs" style="color:#9ca3af">{{ $payments->total() }} records</span>
            </x-slot:actions>
        </x-ui.card-header>
    </x-slot:header>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr style="background:#f9fafb;border-bottom:1px solid #e6eaf5">
                    <th class="text-left text-xs font-semibold px-4 py-2.5" style="color:#6b7280">Code</th>
                    <th class="text-left text-xs font-semibold px-3 py-2.5" style="color:#6b7280">Patient</th>
                    <th class="text-left text-xs font-semibold px-3 py-2.5 hidden md:table-cell" style="color:#6b7280">Invoice</th>
                    <th class="text-left text-xs font-semibold px-3 py-2.5" style="color:#6b7280">Amount</th>
                    <th class="text-left text-xs font-semibold px-3 py-2.5" style="color:#6b7280">Method</th>
                    <th class="text-left text-xs font-semibold px-3 py-2.5 hidden lg:table-cell" style="color:#6b7280">Reference</th>
                    <th class="text-left text-xs font-semibold px-3 py-2.5 hidden md:table-cell" style="color:#6b7280">Paid At</th>
                    <th class="text-left text-xs font-semibold px-3 py-2.5 hidden lg:table-cell" style="color:#6b7280">Collected By</th>
                    <th style="width:50px"></th>
                </tr>
            </thead>
            <tbody>
            @forelse($payments as $pay)
                @php
                    $methodVariant = match($pay->method) {
                        'CASH'   => 'success',
                        'HEF'   => 'primary',
                        'NSSF'  => 'warning',
                        'CARD'  => 'secondary',
                        'BAKONG'=> 'danger',
                        default => 'secondary',
                    };
                @endphp
                <tr class="border-b hover:bg-[#f9fafb] transition-colors cursor-pointer" style="border-color:#f1f5f9"
                    onclick="location.href='{{ route('payments.show', $pay->code) }}'">
                    <td class="px-4 py-3">
                        <code class="text-xs font-bold" style="color:#4154f1">{{ $pay->code }}</code>
                    </td>
                    <td class="px-3 py-3">
                        @if($pay->patient)
                            <div class="text-xs font-bold" style="color:#1a1f36">
                                {{ $pay->patient->surname }}, {{ $pay->patient->name }}
                            </div>
                            <div class="text-xs" style="color:#9ca3af">{{ $pay->patient_code }}</div>
                        @else
                            <span class="text-xs" style="color:#d1d5db">{{ $pay->patient_code }}</span>
                        @endif
                    </td>
                    <td class="px-3 py-3 hidden md:table-cell">
                        @if($pay->invoice)
                            <a href="{{ route('invoices.show', $pay->invoice_code) }}"
                               onclick="event.stopPropagation()"
                               class="text-xs font-semibold hover:underline" style="color:#4154f1">
                                {{ $pay->invoice_code }}
                            </a>
                            <div class="text-xs" style="color:#9ca3af">{{ ucfirst($pay->invoice->status) }}</div>
                        @else
                            <span class="text-xs" style="color:#d1d5db">{{ $pay->invoice_code }}</span>
                        @endif
                    </td>
                    <td class="px-3 py-3">
                        <div class="text-sm font-extrabold" style="color:#1a1f36">{{ number_format($pay->amount) }}</div>
                        <div class="text-xs" style="color:#9ca3af">KHR</div>
                    </td>
                    <td class="px-3 py-3">
                        <x-ui.badge :variant="$methodVariant" size="sm">{{ $pay->method }}</x-ui.badge>
                    </td>
                    <td class="px-3 py-3 hidden lg:table-cell">
                        <span class="text-xs font-mono" style="color:#374151">{{ $pay->reference ?? '—' }}</span>
                    </td>
                    <td class="px-3 py-3 hidden md:table-cell">
                        @if($pay->paid_at)
                            <div class="text-xs" style="color:#374151">{{ $pay->paid_at->format('d/m/Y') }}</div>
                            <div class="text-xs" style="color:#9ca3af">{{ $pay->paid_at->format('H:i') }}</div>
                        @else
                            <span style="color:#d1d5db">—</span>
                        @endif
                    </td>
                    <td class="px-3 py-3 text-xs hidden lg:table-cell" style="color:#374151">{{ $pay->collected_by ?? '—' }}</td>
                    <td class="px-3 py-3" onclick="event.stopPropagation()">
                        <x-ui.button href="{{ route('payments.show', $pay->code) }}" variant="secondary" size="sm">
                            <x-slot:icon><i class="bi bi-eye" aria-hidden="true"></i></x-slot:icon>
                        </x-ui.button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="px-4 py-12 text-center">
                        <x-ui.empty-state icon="bi-cash-stack" title="No payments found" description="Payments are recorded when invoices are collected" />
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</x-ui.card>

<x-ui.pagination :paginator="$payments" />

@endsection
