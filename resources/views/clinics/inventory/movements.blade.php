@extends('clinics.layout.app')
@section('title', 'Stock Movements')

@section('content')

<x-ui.page-header
    km="ចលនាស្តុក"
    title="Stock Movements"
    :breadcrumbs="[
        ['label' => __('app.home'), 'url' => route('dashboard')],
        ['label' => 'Inventory', 'url' => route('inventory.products')],
        ['label' => 'Movements'],
    ]">
    <x-slot:actions>
        <x-ui.button href="{{ route('inventory.adjustment') }}" variant="warning" size="sm">
            <x-slot:icon><i class="bi bi-calculator-fill" aria-hidden="true"></i></x-slot:icon>
            Adjust
        </x-ui.button>
        <x-ui.button href="{{ route('inventory.stock-in') }}" variant="success" size="sm">
            <x-slot:icon><i class="bi bi-box-arrow-in-down-right" aria-hidden="true"></i></x-slot:icon>
            Stock In
        </x-ui.button>
        <x-ui.button href="{{ route('inventory.stock-out') }}" variant="danger" size="sm">
            <x-slot:icon><i class="bi bi-box-arrow-up-right" aria-hidden="true"></i></x-slot:icon>
            Stock Out
        </x-ui.button>
    </x-slot:actions>
</x-ui.page-header>

@if(session('flash'))
    <x-ui.alert type="success" class="mb-4">{{ session('flash') }}</x-ui.alert>
@endif

{{-- ── TYPE SUMMARY STRIP ─────────────────────────────────────── --}}
<div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-5 gap-4 mb-5">
    @foreach([
        ['in',         'bi-box-arrow-in-down-right', '#2eca6a', '#e8f8ef', 'ចូលស្តុក',   'Stock In'],
        ['return',     'bi-arrow-return-left',        '#ff771d', '#fff3e8', 'ត្រឡប់',     'Returns'],
        ['out',        'bi-box-arrow-up-right',       '#e74c3c', '#fde8e8', 'ចេញ',        'Manual Out'],
        ['expired',    'bi-calendar-x-fill',          '#9b59b6', '#f5eeff', 'ផុតកំណត់',  'Expired'],
        ['adjustment', 'bi-sliders',                  '#4154f1', '#eef0fd', 'កែតម្រូវ',  'Adjusted'],
    ] as [$type, $ico, $col, $bg, $km, $en])
        <x-ui.stats-card
            :href="route('inventory.movements', ['type' => $type])"
            :km="$km"
            :label="$en"
            :value="$typeStats[$type] ?? 0"
            :icon="$ico"
            :color="$col"
            :bg="$bg"
        />
    @endforeach
</div>

{{-- ── FILTER ─────────────────────────────────────────────────── --}}
<x-ui.card class="mb-4">
    <form method="GET" action="{{ route('inventory.movements') }}" role="search" aria-label="Filter movements">
        <div class="flex flex-col sm:flex-row gap-3 flex-wrap">

            {{-- Search --}}
            <div class="flex-1 relative min-w-0">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none" aria-hidden="true">
                    <i class="bi bi-search text-sm" style="color:#6b7280"></i>
                </div>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Medicine name, reference, supplier…"
                    class="w-full text-sm rounded-lg border border-[#e2e8f0] bg-white pl-9 pr-4 py-2.5 text-[#374151] placeholder-[#9ca3af] focus:outline-none focus:border-[#4154f1] focus:ring-2 focus:ring-[#4154f1]/20 transition-colors"
                    autofocus />
            </div>

            {{-- Type --}}
            <div class="relative sm:w-40">
                <select name="type" aria-label="Filter by type"
                    class="w-full text-sm rounded-lg border border-[#e2e8f0] bg-white px-4 py-2.5 text-[#374151] appearance-none focus:outline-none focus:border-[#4154f1] focus:ring-2 focus:ring-[#4154f1]/20 transition-colors"
                    style="padding-right:2.5rem">
                    <option value="">All Types</option>
                    @foreach(['in' => 'Stock In', 'return' => 'Return', 'out' => 'Manual Out', 'expired' => 'Expired', 'adjustment' => 'Adjustment'] as $v => $l)
                        <option value="{{ $v }}" @selected(request('type') === $v)>{{ $l }}</option>
                    @endforeach
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

            {{-- Month --}}
            <div class="sm:w-40">
                <input type="month" name="month" value="{{ request('month') }}"
                    class="w-full text-sm rounded-lg border border-[#e2e8f0] bg-white px-4 py-2.5 text-[#374151] focus:outline-none focus:border-[#4154f1] focus:ring-2 focus:ring-[#4154f1]/20 transition-colors" />
            </div>

            {{-- Buttons --}}
            <div class="flex gap-2">
                <x-ui.button type="submit" variant="primary">
                    <x-slot:icon><i class="bi bi-funnel-fill" aria-hidden="true"></i></x-slot:icon>
                    Filter
                </x-ui.button>
                @if(request()->hasAny(['search', 'type', 'date', 'month']))
                    <x-ui.button href="{{ route('inventory.movements') }}" variant="secondary">
                        <x-slot:icon><i class="bi bi-x-circle" aria-hidden="true"></i></x-slot:icon>
                        Clear
                    </x-ui.button>
                @endif
            </div>
        </div>
    </form>
</x-ui.card>

{{-- ── MOVEMENTS TABLE ──────────────────────────────────────────── --}}
<x-ui.card :noPadding="true">
    <x-slot:header>
        <div class="flex items-center justify-between px-5 py-4" style="border-bottom:1px solid #e6e9f0">
            <div class="flex items-center gap-2">
                <i class="bi bi-journal-text" style="color:#4154f1;font-size:15px" aria-hidden="true"></i>
                <span class="text-sm font-bold" style="color:#1a1f36">Movement Log</span>
                <span class="text-xs font-semibold px-2 py-0.5 rounded-full" style="background:#f3f4f6;color:#6b7280">
                    {{ number_format($movements->total()) }}
                </span>
            </div>
        </div>
    </x-slot:header>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr style="background:#f8f9fb;border-bottom:1px solid #e6e9f0">
                    <th class="text-left px-5 py-3 text-xs font-bold" style="color:#6b7280">Date</th>
                    <th class="text-left px-4 py-3 text-xs font-bold" style="color:#6b7280">Medicine</th>
                    <th class="text-left px-4 py-3 text-xs font-bold" style="color:#6b7280">Type</th>
                    <th class="text-center px-4 py-3 text-xs font-bold" style="color:#6b7280">Qty</th>
                    <th class="text-center px-4 py-3 text-xs font-bold hidden sm:table-cell" style="color:#6b7280">Before</th>
                    <th class="text-center px-4 py-3 text-xs font-bold hidden sm:table-cell" style="color:#6b7280">After</th>
                    <th class="text-left px-4 py-3 text-xs font-bold hidden md:table-cell" style="color:#6b7280">Reference / Supplier</th>
                    <th class="text-left px-4 py-3 text-xs font-bold hidden lg:table-cell" style="color:#6b7280">Note</th>
                    <th class="text-left px-4 py-3 text-xs font-bold hidden lg:table-cell" style="color:#6b7280">By</th>
                </tr>
            </thead>
            <tbody>
            @forelse($movements as $mv)
                @php
                    $isIn  = in_array($mv->type, ['in', 'return']);
                    $isAdj = $mv->type === 'adjustment';
                    $delta = $mv->stock_after - $mv->stock_before;
                    $typeMap = [
                        'in'         => ['Stock In',   'success'],
                        'return'     => ['Return',     'warning'],
                        'out'        => ['Manual Out', 'danger'],
                        'expired'    => ['Expired',    'secondary'],
                        'adjustment' => ['Adjustment', 'primary'],
                    ];
                    [$typeLabel, $typeVariant] = $typeMap[$mv->type] ?? [ucfirst($mv->type), 'secondary'];
                    $qtySign  = $isIn ? '+' : ($isAdj ? ($delta >= 0 ? '+' : '-') : '-');
                    $qtyColor = $isIn ? '#2eca6a' : ($isAdj && $delta >= 0 ? '#2eca6a' : '#e74c3c');
                    $afterColor = $mv->stock_after <= 0 ? '#e74c3c' : ($mv->stock_after <= 10 ? '#ff771d' : '#1a1f36');
                @endphp
                <tr style="border-bottom:1px solid #f8f9fb" class="hover:bg-[#f8f9fb] transition-colors">

                    <td class="px-5 py-3.5">
                        <div class="text-xs font-semibold" style="color:#1a1f36">
                            {{ $mv->created_at->format('d/m/Y') }}
                        </div>
                        <div class="text-xs" style="color:#9ca3af">{{ $mv->created_at->format('H:i') }}</div>
                    </td>

                    <td class="px-4 py-3.5">
                        <a href="{{ route('inventory.product.ledger', $mv->medicine_id) }}"
                           class="text-sm font-semibold hover:underline" style="color:#1a1f36;text-decoration:none">
                            {{ $mv->medicine_name }}
                        </a>
                        <div>
                            <code class="text-xs px-1 py-0.5 rounded" style="background:#eef0fd;color:#4154f1">
                                {{ $mv->medicine_code }}
                            </code>
                        </div>
                    </td>

                    <td class="px-4 py-3.5">
                        <x-ui.badge :variant="$typeVariant">{{ $typeLabel }}</x-ui.badge>
                    </td>

                    <td class="px-4 py-3.5 text-center">
                        <span class="text-base font-black" style="color:{{ $qtyColor }}">
                            {{ $qtySign }}{{ $mv->quantity }}
                        </span>
                    </td>

                    <td class="px-4 py-3.5 text-center hidden sm:table-cell">
                        <span class="text-xs" style="color:#9ca3af">{{ $mv->stock_before }}</span>
                    </td>

                    <td class="px-4 py-3.5 text-center hidden sm:table-cell">
                        <span class="text-sm font-bold" style="color:{{ $afterColor }}">{{ $mv->stock_after }}</span>
                    </td>

                    <td class="px-4 py-3.5 hidden md:table-cell">
                        @if($mv->reference)
                            <div class="text-xs font-semibold" style="color:#4154f1">{{ $mv->reference }}</div>
                        @endif
                        @if($mv->supplier)
                            <div class="text-xs" style="color:#6b7280">{{ $mv->supplier }}</div>
                        @endif
                        @if($mv->batch_no)
                            <div class="text-xs" style="color:#9ca3af">Batch: {{ $mv->batch_no }}</div>
                        @endif
                    </td>

                    <td class="px-4 py-3.5 hidden lg:table-cell">
                        <span class="text-xs" style="color:#6b7280">{{ Str::limit($mv->note, 50) }}</span>
                    </td>

                    <td class="px-4 py-3.5 hidden lg:table-cell">
                        <span class="text-xs" style="color:#9ca3af">{{ $mv->recorded_by ?? '—' }}</span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="px-5 py-12">
                        <x-ui.empty-state
                            icon="bi-journal-text"
                            title="No movements found"
                            description="Stock movements appear here after stock-in, stock-out, or adjustments." />
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</x-ui.card>

<x-ui.pagination :paginator="$movements" />

@endsection
