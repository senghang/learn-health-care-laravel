@extends('clinics.layout.app')
@section('title', 'INV ' . $invoice->code)
@section('content')

@php
    $paid    = $invoice->payments->sum('amount');
    $balance = $invoice->total - $paid;
    $status  = $invoice->status ?? 'pending';
    $statusVariant = match($status) {
        'paid'    => 'success',
        'partial' => 'warning',
        'void'    => 'secondary',
        default   => 'danger',
    };
@endphp

<x-ui.page-header
    :km="$invoice->code"
    title="Invoice Detail"
    :breadcrumbs="[
        ['label' => __('app.home'), 'url' => route('dashboard')],
        ['label' => __('app.invoices'), 'url' => route('invoices.index')],
        ['label' => $invoice->code],
    ]">
    <x-slot:actions>
        <x-ui.badge :variant="$statusVariant" size="md">{{ strtoupper($status) }}</x-ui.badge>
        @if(!in_array($status, ['paid', 'void']))
            <x-ui.button href="{{ route('invoices.edit', $invoice->code) }}" variant="secondary" size="sm">
                <x-slot:icon><i class="bi bi-pencil-fill" aria-hidden="true"></i></x-slot:icon>
                {{ __('app.edit') }}
            </x-ui.button>
        @endif
        @if($status !== 'void')
            <form method="POST" action="{{ route('invoices.void', $invoice->code) }}"
                  data-confirm="Void invoice #{{ $invoice->code }}? It will be marked as voided and cannot be reversed."
                  data-confirm-type="warn" data-confirm-title="Void Invoice">
                @csrf
                <x-ui.button type="submit" variant="danger" size="sm">
                    <x-slot:icon><i class="bi bi-slash-circle" aria-hidden="true"></i></x-slot:icon>
                    Void
                </x-ui.button>
            </form>
        @endif
        <x-ui.button href="{{ route('print.invoice', $invoice->code) }}" variant="secondary" size="sm"
                     onclick="window.open(this.href,'_blank');return false;">
            <x-slot:icon><i class="bi bi-printer-fill" aria-hidden="true"></i></x-slot:icon>
            {{ __('app.print') }}
        </x-ui.button>
    </x-slot:actions>
</x-ui.page-header>

@if(session('success'))
    <x-ui.alert type="success" class="mb-4">{{ session('success') }}</x-ui.alert>
@endif
@if(session('error'))
    <x-ui.alert type="error" class="mb-4">{{ session('error') }}</x-ui.alert>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

    {{-- ── Invoice body ──────────────────────────────────────────────────── --}}
    <div class="lg:col-span-2 space-y-4">

        {{-- Header info --}}
        <x-ui.card>
            <x-slot:header>
                <x-ui.card-header label="{{ __('app.invoice') }}" icon="bi-receipt-cutoff">
                    <x-slot:actions>
                        <span class="text-xs" style="color:#9ca3af">{{ $invoice->invoice_date?->format('d/m/Y') }}</span>
                    </x-slot:actions>
                </x-ui.card-header>
            </x-slot:header>

            {{-- Meta fields --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-5">
                @foreach([
                    [__('app.patient.name'), $invoice->patient?->surname.', '.$invoice->patient?->name, $invoice->patient_code],
                    [__('app.visit.code'),   $invoice->visit_code ?? '—', null],
                    [__('app.payment_type'), $invoice->payment_type, null],
                    [__('app.cashier'),      $invoice->cashier ?? '—', null],
                ] as [$label, $val, $sub])
                    <div>
                        <div class="text-xs font-bold uppercase tracking-wide mb-1" style="color:#9ca3af">
                            {{ is_array($label) ? implode(', ', $label) : $label }}
                        </div>
                        <div class="text-sm font-semibold" style="color:#1a1f36">{{ $val }}</div>
                        @if($sub)
                            <div class="text-xs" style="color:#9ca3af">{{ $sub }}</div>
                        @endif
                    </div>
                @endforeach
            </div>

            {{-- Services --}}
            @if($invoice->services->count())
                <div class="text-xs font-extrabold uppercase tracking-wider mb-3" style="color:#00bcd4">
                    {{ __('app.services') }}
                </div>
                <div class="overflow-x-auto mb-5 rounded-xl border" style="border-color:#e6eaf5">
                    <table class="w-full text-sm">
                        <thead>
                            <tr style="background:#f9fafb;border-bottom:1px solid #e6eaf5">
                                <th class="text-left text-xs font-semibold px-4 py-2.5" style="color:#6b7280">{{ __('app.service') }}</th>
                                <th class="text-left text-xs font-semibold px-3 py-2.5 hidden sm:table-cell" style="color:#6b7280">{{ __('app.category') }}</th>
                                <th class="text-center text-xs font-semibold px-3 py-2.5" style="color:#6b7280">Qty</th>
                                <th class="text-right text-xs font-semibold px-3 py-2.5 hidden sm:table-cell" style="color:#6b7280">Unit Price KHR</th>
                                <th class="text-right text-xs font-semibold px-4 py-2.5" style="color:#6b7280">Subtotal KHR</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($invoice->services as $svc)
                                @php $qty = $svc->qty ?? 1; @endphp
                                <tr class="border-b" style="border-color:#f1f5f9">
                                    <td class="px-4 py-2.5 text-sm font-semibold" style="color:#1a1f36">{{ $svc->service_name }}</td>
                                    <td class="px-3 py-2.5 text-xs hidden sm:table-cell" style="color:#9ca3af">{{ $svc->service_category ?? '—' }}</td>
                                    <td class="px-3 py-2.5 text-center text-sm" style="color:#9ca3af">{{ $qty }}</td>
                                    <td class="px-3 py-2.5 text-right text-sm hidden sm:table-cell" style="color:#374151">{{ number_format($svc->price) }}</td>
                                    <td class="px-4 py-2.5 text-right text-sm font-bold" style="color:#1a1f36">{{ number_format($qty * $svc->price) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            {{-- Medications --}}
            @if($invoice->medications->count())
                <div class="text-xs font-extrabold uppercase tracking-wider mb-3" style="color:#9b59b6">
                    {{ __('app.medications') }}
                </div>
                <div class="overflow-x-auto mb-5 rounded-xl border" style="border-color:#e6eaf5">
                    <table class="w-full text-sm">
                        <thead>
                            <tr style="background:#f9fafb;border-bottom:1px solid #e6eaf5">
                                <th class="text-left text-xs font-semibold px-4 py-2.5" style="color:#6b7280">{{ __('app.medicine') }}</th>
                                <th class="text-left text-xs font-semibold px-3 py-2.5" style="color:#6b7280">{{ __('app.qty') }}</th>
                                <th class="text-right text-xs font-semibold px-4 py-2.5" style="color:#6b7280">{{ __('app.price') }} KHR</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($invoice->medications as $med)
                                <tr class="border-b" style="border-color:#f1f5f9">
                                    <td class="px-4 py-2.5 text-sm font-semibold" style="color:#1a1f36">{{ $med->medicine_name }}</td>
                                    <td class="px-3 py-2.5 text-sm" style="color:#9ca3af">{{ $med->quantity }}</td>
                                    <td class="px-4 py-2.5 text-right text-sm font-bold" style="color:#1a1f36">{{ number_format($med->price) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            {{-- Totals --}}
            <div class="rounded-xl p-4" style="background:#f6f8fa;border:1px solid #e0e6f5">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-sm" style="color:#6b7280">{{ __('app.total') }}</span>
                    <span class="text-lg font-extrabold" style="color:#1a1f36">{{ number_format($invoice->total) }} KHR</span>
                </div>
                <div class="flex justify-between items-center mb-2">
                    <span class="text-sm" style="color:#2eca6a">{{ __('app.paid') }}</span>
                    <span class="text-sm font-bold" style="color:#2eca6a">{{ number_format($paid) }} KHR</span>
                </div>
                @if($balance > 0)
                    <div class="flex justify-between items-center pt-3 mt-2 border-t" style="border-color:#e0e6f5;border-style:dashed">
                        <span class="text-sm font-bold" style="color:#e74c3c">{{ __('app.balance') }}</span>
                        <span class="text-xl font-extrabold" style="color:#e74c3c">{{ number_format($balance) }} KHR</span>
                    </div>
                @endif
            </div>
        </x-ui.card>

        {{-- Payment history --}}
        @if($invoice->payments->count())
            <x-ui.card noPadding>
                <x-slot:header>
                    <x-ui.card-header label="{{ __('app.payment_history') }}" icon="bi-cash-stack" />
                </x-slot:header>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr style="background:#f9fafb;border-bottom:1px solid #e6eaf5">
                                <th class="text-left text-xs font-semibold px-4 py-2.5" style="color:#6b7280">{{ __('app.date') }}</th>
                                <th class="text-left text-xs font-semibold px-3 py-2.5" style="color:#6b7280">{{ __('app.method') }}</th>
                                <th class="text-left text-xs font-semibold px-3 py-2.5 hidden md:table-cell" style="color:#6b7280">{{ __('app.reference') }}</th>
                                <th class="text-left text-xs font-semibold px-3 py-2.5 hidden md:table-cell" style="color:#6b7280">{{ __('app.collected_by') }}</th>
                                <th class="text-right text-xs font-semibold px-4 py-2.5" style="color:#6b7280">{{ __('app.amount') }} KHR</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($invoice->payments as $pay)
                                <tr class="border-b" style="border-color:#f1f5f9">
                                    <td class="px-4 py-2.5 text-xs" style="color:#374151">{{ $pay->paid_at?->format('d/m/Y H:i') ?? '—' }}</td>
                                    <td class="px-3 py-2.5">
                                        <x-ui.badge variant="success" size="sm">{{ $pay->method }}</x-ui.badge>
                                    </td>
                                    <td class="px-3 py-2.5 text-xs hidden md:table-cell" style="color:#9ca3af">{{ $pay->reference ?? '—' }}</td>
                                    <td class="px-3 py-2.5 text-xs hidden md:table-cell" style="color:#374151">{{ $pay->collected_by ?? '—' }}</td>
                                    <td class="px-4 py-2.5 text-right text-sm font-bold" style="color:#2eca6a">{{ number_format($pay->amount) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-ui.card>
        @endif

    </div>

    {{-- ── Sidebar ──────────────────────────────────────────────────────── --}}
    <div class="space-y-4">

        {{-- Collect payment / Fully paid --}}
        @if($balance > 0)
            <x-ui.card class="sticky top-20" id="payment">
                <x-slot:header>
                    <x-ui.card-header label="{{ __('app.collect_payment') }}" icon="bi-cash-coin">
                        <x-slot:actions>
                            <span class="text-sm font-extrabold" style="color:#e74c3c">{{ number_format($balance) }} KHR {{ __('app.due') }}</span>
                        </x-slot:actions>
                    </x-ui.card-header>
                </x-slot:header>

                @if(session('flash'))
                    <x-ui.alert type="success" class="mb-3">{{ session('flash') }}</x-ui.alert>
                @endif
                @if($errors->any())
                    <x-ui.alert type="error" class="mb-3">
                        <ul class="list-disc pl-4 text-xs space-y-0.5">
                            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                        </ul>
                    </x-ui.alert>
                @endif

                <form method="POST" action="{{ route('invoices.payment', $invoice->code) }}" novalidate>
                    @csrf
                    <div class="space-y-3">
                        <div class="space-y-1.5">
                            <label class="block text-xs font-semibold" style="color:#374151">{{ __('app.amount') }} KHR <span style="color:#ef4444">*</span></label>
                            <x-forms.input type="number" name="amount" :value="old('amount', $balance)" required min="1" />
                        </div>
                        <div class="space-y-1.5">
                            <label class="block text-xs font-semibold" style="color:#374151">{{ __('app.method') }}</label>
                            <x-forms.select name="method">
                                @foreach(['CASH'=>'CASH','HEF'=>'HEF','NSSF'=>'NSSF','CARD'=>'Card','BAKONG'=>'Bakong'] as $v=>$l)
                                    <option value="{{ $v }}" {{ old('method','CASH')===$v?'selected':'' }}>{{ $l }}</option>
                                @endforeach
                            </x-forms.select>
                        </div>
                        <div class="space-y-1.5">
                            <label class="block text-xs font-semibold" style="color:#374151">{{ __('app.reference') }}</label>
                            <x-forms.input type="text" name="reference" :value="old('reference')" placeholder="Ref No." />
                        </div>
                        <div class="space-y-1.5">
                            <label class="block text-xs font-semibold" style="color:#374151">{{ __('app.collected_by') }}</label>
                            <x-forms.input type="text" name="collected_by" :value="old('collected_by', auth()->user()?->name)" placeholder="Staff name" />
                        </div>
                        <x-ui.button type="submit" variant="primary" :fullWidth="true">
                            <x-slot:icon><i class="bi bi-check2-circle" aria-hidden="true"></i></x-slot:icon>
                            {{ __('app.confirm_payment') }}
                        </x-ui.button>
                    </div>
                </form>
            </x-ui.card>
        @else
            <x-ui.card class="sticky top-20">
                <div class="flex flex-col items-center justify-center py-6 text-center">
                    <div class="w-14 h-14 rounded-full flex items-center justify-center mb-3" style="background:#e8f8ef">
                        <i class="bi bi-check-circle-fill text-2xl" style="color:#2eca6a"></i>
                    </div>
                    <div class="font-bold text-sm mb-1" style="color:#2eca6a">{{ __('app.fully_paid') }}</div>
                    <div class="text-xs" style="color:#9ca3af">{{ number_format($paid) }} KHR</div>
                </div>
            </x-ui.card>
        @endif

        {{-- Patient quick info --}}
        @if($invoice->patient)
            <x-ui.card>
                <x-slot:header>
                    <x-ui.card-header label="{{ __('app.patient.name') }}" icon="bi-person-fill" />
                </x-slot:header>
                <div class="font-bold text-sm mb-1" style="color:#1a1f36">{{ $invoice->patient->surname }}, {{ $invoice->patient->name }}</div>
                <div class="text-xs mb-3" style="color:#9ca3af">{{ $invoice->patient->code }} · {{ $invoice->patient->phone ?? '—' }}</div>
                <x-ui.button href="{{ route('patients.show', $invoice->patient->code) }}" variant="secondary" size="sm" :fullWidth="true">
                    <x-slot:icon><i class="bi bi-person-fill" aria-hidden="true"></i></x-slot:icon>
                    {{ __('app.view_patient') }}
                </x-ui.button>
            </x-ui.card>
        @endif

    </div>

</div>
@endsection
