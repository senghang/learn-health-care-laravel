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
        <x-ui.button href="{{ route('invoices.create') }}" variant="primary">
            <x-slot:icon><i class="bi bi-plus-circle-fill" aria-hidden="true"></i></x-slot:icon>
            <span class="hidden sm:inline">New Invoice</span>
            <span class="sm:hidden">New</span>
        </x-ui.button>
    </x-slot:actions>
</x-ui.page-header>

{{-- ── KPI STRIP ─────────────────────────────────────────────── --}}
<div class="grid grid-cols-2 xl:grid-cols-4 gap-4 mb-5">
    <x-ui.stats-card
        :km="__('app.today')" label="Today"
        :value="$stats['today']"
        icon="bi-receipt-cutoff" color="#4154f1" bg="#eef0fd"
        :href="route('invoices.index', ['date' => today()->toDateString()])"
    />
    <x-ui.stats-card
        :km="__('app.pending')" label="Pending"
        :value="$stats['pending']"
        icon="bi-hourglass-split" color="#ff771d" bg="#fff3e8"
        :href="route('invoices.index', ['status' => 'pending'])"
    />
    <x-ui.stats-card
        :km="__('app.total')" label="Total Invoices"
        :value="$stats['total']"
        icon="bi-archive-fill" color="#2eca6a" bg="#e8f8ef"
    />
    <x-ui.stats-card
        :km="__('app.today_revenue')" label="Today Revenue"
        value="${{ number_format($stats['revenue'], 2) }}"
        icon="bi-cash-stack" color="#9b59b6" bg="#f0e8ff"
    />
</div>

{{-- ── FILTER ─────────────────────────────────────────────────── --}}
<x-ui.card class="mb-4">
    <form method="GET" action="{{ route('invoices.index') }}" role="search" aria-label="Filter invoices">
        <div class="flex flex-col sm:flex-row gap-3 flex-wrap">

            {{-- Search --}}
            <div class="flex-1 relative min-w-0">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none" aria-hidden="true">
                    <i class="bi bi-search text-sm" style="color:#6b7280"></i>
                </div>
                <input type="text" name="search"
                    value="{{ old('search', is_array(request('search')) ? '' : request('search')) }}"
                    placeholder="{{ __('app.search') }}… (code, patient code)"
                    class="w-full text-sm rounded-lg border border-[#e2e8f0] bg-white pl-9 pr-4 py-2.5 text-[#374151] placeholder-[#9ca3af] focus:outline-none focus:border-[#4154f1] focus:ring-2 focus:ring-[#4154f1]/20 transition-colors"
                    autofocus />
            </div>

            {{-- Status --}}
            <div class="relative sm:w-36">
                <select name="status" aria-label="Filter by status"
                    class="w-full text-sm rounded-lg border border-[#e2e8f0] bg-white px-4 py-2.5 text-[#374151] appearance-none focus:outline-none focus:border-[#4154f1] focus:ring-2 focus:ring-[#4154f1]/20 transition-colors"
                    style="padding-right:2.5rem">
                    <option value="">{{ __('app.all') }}</option>
                    <option value="pending" @selected(request('status') === 'pending')>{{ __('app.pending') }}</option>
                    <option value="partial" @selected(request('status') === 'partial')>{{ __('app.partial') }}</option>
                    <option value="paid"    @selected(request('status') === 'paid')>{{ __('app.paid') }}</option>
                </select>
                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none" aria-hidden="true">
                    <i class="bi bi-chevron-down text-xs" style="color:#6b7280"></i>
                </div>
            </div>

            {{-- Payment Type --}}
            <div class="relative sm:w-36">
                <select name="payment_type" aria-label="Filter by payment type"
                    class="w-full text-sm rounded-lg border border-[#e2e8f0] bg-white px-4 py-2.5 text-[#374151] appearance-none focus:outline-none focus:border-[#4154f1] focus:ring-2 focus:ring-[#4154f1]/20 transition-colors"
                    style="padding-right:2.5rem">
                    <option value="">All Types</option>
                    <option value="HEF"  @selected(request('payment_type') === 'HEF')>HEF</option>
                    <option value="NSSF" @selected(request('payment_type') === 'NSSF')>NSSF</option>
                    <option value="CASH" @selected(request('payment_type') === 'CASH')>CASH</option>
                </select>
                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none" aria-hidden="true">
                    <i class="bi bi-chevron-down text-xs" style="color:#6b7280"></i>
                </div>
            </div>

            {{-- Date --}}
            <div class="sm:w-44">
                <input type="date" name="date" value="{{ request('date') }}"
                    class="w-full text-sm rounded-lg border border-[#e2e8f0] bg-white px-4 py-2.5 text-[#374151] focus:outline-none focus:border-[#4154f1] focus:ring-2 focus:ring-[#4154f1]/20 transition-colors" />
            </div>

            {{-- Buttons --}}
            <div class="flex gap-2">
                <x-ui.button type="submit" variant="primary">
                    <x-slot:icon><i class="bi bi-funnel-fill" aria-hidden="true"></i></x-slot:icon>
                    Filter
                </x-ui.button>
                @if(request()->hasAny(['search', 'status', 'payment_type', 'date']))
                    <x-ui.button href="{{ route('invoices.index') }}" variant="secondary">
                        <x-slot:icon><i class="bi bi-x-circle" aria-hidden="true"></i></x-slot:icon>
                        Clear
                    </x-ui.button>
                @endif
            </div>
        </div>
    </form>
</x-ui.card>

{{-- ── INVOICE TABLE ────────────────────────────────────────────── --}}
<x-ui.card :noPadding="true">
    <x-slot:header>
        <div class="flex items-center justify-between px-5 py-4" style="border-bottom:1px solid #e6e9f0">
            <div class="flex items-center gap-2">
                <i class="bi bi-receipt-cutoff" style="color:#00bcd4;font-size:15px" aria-hidden="true"></i>
                <span class="text-sm font-bold" style="color:#1a1f36">{{ __('app.invoices') }}</span>
                <span class="text-xs font-semibold px-2 py-0.5 rounded-full" style="background:#f3f4f6;color:#6b7280">
                    {{ number_format($invoices->total()) }}
                </span>
            </div>
        </div>
    </x-slot:header>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr style="background:#f8f9fb;border-bottom:1px solid #e6e9f0">
                    <th class="text-left px-5 py-3 text-xs font-bold" style="color:#6b7280">{{ __('app.date') }}</th>
                    <th class="text-left px-4 py-3 text-xs font-bold" style="color:#6b7280">{{ __('app.invoice') }}</th>
                    <th class="text-left px-4 py-3 text-xs font-bold" style="color:#6b7280">{{ __('app.patient.name') }}</th>
                    <th class="text-left px-4 py-3 text-xs font-bold hidden sm:table-cell" style="color:#6b7280">{{ __('app.payment_type') }}</th>
                    <th class="text-right px-4 py-3 text-xs font-bold hidden md:table-cell" style="color:#6b7280">{{ __('app.total') }}</th>
                    <th class="text-right px-4 py-3 text-xs font-bold hidden md:table-cell" style="color:#6b7280">{{ __('app.paid') }}</th>
                    <th class="text-right px-4 py-3 text-xs font-bold hidden lg:table-cell" style="color:#6b7280">{{ __('app.balance') }}</th>
                    <th class="text-left px-4 py-3 text-xs font-bold" style="color:#6b7280">{{ __('app.status') }}</th>
                    <th class="px-4 py-3"></th>
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
                    $ptVariants = ['HEF' => 'primary', 'NSSF' => 'success', 'CARD' => 'warning'];
                @endphp
                <tr class="hover:bg-[#f8f9fb] transition-colors cursor-pointer"
                    style="border-bottom:1px solid #f8f9fb"
                    onclick="window.location='{{ route('invoices.show', $inv->code) }}'">

                    <td class="px-5 py-3.5">
                        <div class="text-xs font-semibold" style="color:#1a1f36">
                            {{ $inv->invoice_date?->format('d/m/Y') ?? '—' }}
                        </div>
                    </td>

                    <td class="px-4 py-3.5">
                        <code class="text-xs font-bold px-1.5 py-0.5 rounded"
                              style="background:#e0f7fa;color:#00bcd4">{{ $inv->code }}</code>
                    </td>

                    <td class="px-4 py-3.5">
                        @if($inv->patient)
                            <div class="text-sm font-semibold" style="color:#1a1f36">
                                {{ $inv->patient->surname }}, {{ $inv->patient->name }}
                            </div>
                            <div class="text-xs" style="color:#9ca3af">{{ $inv->patient_code }}</div>
                        @else
                            <span style="color:#d1d5db">—</span>
                        @endif
                    </td>

                    <td class="px-4 py-3.5 hidden sm:table-cell">
                        @if($inv->payment_type)
                            <x-ui.badge :variant="$ptVariants[$inv->payment_type] ?? 'secondary'">
                                {{ $inv->payment_type }}
                            </x-ui.badge>
                        @else
                            <span class="text-xs" style="color:#d1d5db">—</span>
                        @endif
                    </td>

                    <td class="px-4 py-3.5 text-right hidden md:table-cell">
                        <span class="text-sm font-bold" style="color:#1a1f36">
                            {{ number_format($inv->total) }}
                        </span>
                    </td>

                    <td class="px-4 py-3.5 text-right hidden md:table-cell">
                        <span class="text-sm font-bold" style="color:#2eca6a">
                            {{ number_format($paid) }}
                        </span>
                    </td>

                    <td class="px-4 py-3.5 text-right hidden lg:table-cell">
                        <span class="text-sm font-bold"
                              style="color:{{ $balance > 0 ? '#e74c3c' : '#2eca6a' }}">
                            {{ $balance > 0 ? number_format($balance) : '—' }}
                        </span>
                    </td>

                    <td class="px-4 py-3.5">
                        <x-ui.badge :variant="$statusVariant">{{ $statusLabel }}</x-ui.badge>
                    </td>

                    <td class="px-4 py-3.5" onclick="event.stopPropagation()">
                        <div class="flex gap-1.5">
                            <x-ui.button href="{{ route('invoices.show', $inv->code) }}" variant="secondary" size="sm">
                                <x-slot:icon><i class="bi bi-eye-fill" aria-hidden="true"></i></x-slot:icon>
                            </x-ui.button>
                            @if($balance > 0)
                                <x-ui.button href="{{ route('invoices.show', $inv->code) }}#payment" variant="primary" size="sm">
                                    <x-slot:icon><i class="bi bi-cash" aria-hidden="true"></i></x-slot:icon>
                                </x-ui.button>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="px-5 py-12">
                        <x-ui.empty-state
                            icon="bi-receipt"
                            title="{{ __('app.no_records') }}"
                            description="{{ __('app.invoices_created_from_workflow') }}" />
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</x-ui.card>

<x-ui.pagination :paginator="$invoices" />

@endsection
