@extends('clinics.layout.app')
@section('title', 'Pharmacy — Dispensing Queue')

@section('content')

<x-ui.page-header
    km="ឱសថស្ថាន"
    title="Pharmacy — Dispensing Queue"
    :breadcrumbs="[
        ['label' => __('app.home'), 'url' => route('dashboard')],
        ['label' => 'Pharmacy'],
    ]">
</x-ui.page-header>

{{-- ── KPI STRIP ─────────────────────────────────────────────── --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-5">
    <x-ui.stats-card
        km="ត្រូវចែកចាយ" label="Pending"
        :value="$stats['pending']"
        icon="bi-hourglass-split" color="#ff771d" bg="#fff4ec"
        :href="route('pharmacy.index', ['status'=>'pending'])"
    />
    <x-ui.stats-card
        km="ចែកផ្នែក" label="Partial"
        :value="$stats['partial']"
        icon="bi-pie-chart-fill" color="#9b59b6" bg="#f5eeff"
        :href="route('pharmacy.index', ['status'=>'partial'])"
    />
    <x-ui.stats-card
        km="ថ្ងៃនេះ" label="Done Today"
        :value="$stats['today']"
        icon="bi-bag-check-fill" color="#2eca6a" bg="#e8f8ef"
        :href="route('pharmacy.index', ['status'=>'dispensed'])"
    />
    <x-ui.stats-card
        km="ស្តុកទាប" label="Low Stock"
        :value="$stats['low_stock']"
        icon="bi-exclamation-triangle-fill" color="#e74c3c" bg="#fde8e8"
        :href="route('inventory.products', ['status'=>'low'])"
    />
</div>

{{-- ── FILTER ─────────────────────────────────────────────────── --}}
<x-ui.card class="mb-4">
    <form method="GET" action="{{ route('pharmacy.index') }}" role="search" aria-label="Filter prescriptions">
        <div class="flex flex-col sm:flex-row gap-3 flex-wrap">

            {{-- Status tabs --}}
            <div class="flex gap-1 p-1 rounded-xl" style="background:#f1f5f9">
                @foreach([
                    'pending'   => ['Pending',   '#ff771d'],
                    'partial'   => ['Partial',   '#9b59b6'],
                    'dispensed' => ['Done',      '#2eca6a'],
                    'all'       => ['All',       '#64748b'],
                ] as $s => [$lbl, $col])
                    <a href="{{ route('pharmacy.index', array_merge(request()->query(), ['status' => $s])) }}"
                       class="flex-1 text-center text-xs font-bold px-3 py-1.5 rounded-lg transition-all duration-150"
                       style="{{ $status === $s
                            ? 'background:white;color:'.$col.';box-shadow:0 1px 4px rgba(17,24,39,.12)'
                            : 'color:#64748b' }}"
                       aria-current="{{ $status === $s ? 'page' : 'false' }}">
                        {{ $lbl }}
                    </a>
                @endforeach
            </div>

            {{-- Search --}}
            <div class="flex-1 relative min-w-0">
                <input type="hidden" name="status" value="{{ $status }}">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none" aria-hidden="true">
                    <i class="bi bi-search text-sm" style="color:#6b7280"></i>
                </div>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Patient name or Rx code…"
                    class="w-full text-sm rounded-lg border border-[#e2e8f0] bg-white pl-9 pr-4 py-2.5 text-[#374151] placeholder-[#9ca3af] focus:outline-none focus:border-[#4154f1] focus:ring-2 focus:ring-[#4154f1]/20 transition-colors"
                    autofocus />
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
                @if(request()->hasAny(['search', 'date']))
                    <x-ui.button href="{{ route('pharmacy.index', ['status' => $status]) }}" variant="secondary">
                        <x-slot:icon><i class="bi bi-x-circle" aria-hidden="true"></i></x-slot:icon>
                        Clear
                    </x-ui.button>
                @endif
            </div>
        </div>
    </form>
</x-ui.card>

{{-- ── DISPENSING QUEUE TABLE ──────────────────────────────────── --}}
<x-ui.card :noPadding="true">
    <x-slot:header>
        <div class="flex items-center justify-between px-5 py-4" style="border-bottom:1px solid #e6e9f0">
            <div class="flex items-center gap-2">
                <i class="bi bi-bag-heart-fill" style="color:#7c3aed;font-size:15px" aria-hidden="true"></i>
                <span class="text-sm font-bold" style="color:#1a1f36">Dispensing Queue</span>
                <span class="text-xs font-semibold px-2 py-0.5 rounded-full" style="background:#f3f4f6;color:#6b7280">
                    {{ $prescriptions->total() }}
                </span>
            </div>
        </div>
    </x-slot:header>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr style="background:#f8f9fb;border-bottom:1px solid #e6e9f0">
                    <th class="text-left px-5 py-3 text-xs font-bold" style="color:#6b7280">Date / Time</th>
                    <th class="text-left px-4 py-3 text-xs font-bold" style="color:#6b7280">Rx Code</th>
                    <th class="text-left px-4 py-3 text-xs font-bold" style="color:#6b7280">Patient</th>
                    <th class="text-left px-4 py-3 text-xs font-bold hidden md:table-cell" style="color:#6b7280">Doctor</th>
                    <th class="text-center px-4 py-3 text-xs font-bold" style="color:#6b7280">Items</th>
                    <th class="text-left px-4 py-3 text-xs font-bold" style="color:#6b7280">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
            @forelse($prescriptions as $rx)
                @php
                    $hasCatalogMeds  = $rx->medications->where('medication_code', '!=', null)->count();
                    $statusVariant   = match($rx->dispensed_status) {
                        'dispensed' => 'success',
                        'partial'   => 'warning',
                        default     => 'secondary',
                    };
                    $statusLabel = match($rx->dispensed_status) {
                        'dispensed' => 'Dispensed',
                        'partial'   => 'Partial',
                        default     => 'Pending',
                    };
                @endphp
                <tr class="hover:bg-[#f8f9fb] transition-colors cursor-pointer group"
                    style="border-bottom:1px solid #f8f9fb"
                    onclick="window.location='{{ route('pharmacy.show', $rx->code) }}'">

                    <td class="px-5 py-3.5">
                        <div class="text-xs font-semibold" style="color:#1a1f36">
                            {{ $rx->prescribed_at?->format('d/m/Y') ?? '—' }}
                        </div>
                        <div class="text-xs" style="color:#9ca3af">{{ $rx->prescribed_at?->format('H:i') }}</div>
                    </td>

                    <td class="px-4 py-3.5">
                        <code class="text-xs font-bold px-1.5 py-0.5 rounded"
                              style="background:#f5eeff;color:#7c3aed">{{ $rx->code }}</code>
                    </td>

                    <td class="px-4 py-3.5">
                        @if($rx->patient)
                            <div class="text-sm font-semibold" style="color:#1a1f36">
                                {{ $rx->patient->surname }}, {{ $rx->patient->name }}
                            </div>
                            <div class="text-xs" style="color:#9ca3af">{{ $rx->patient_code }}</div>
                        @else
                            <span style="color:#d1d5db">—</span>
                        @endif
                    </td>

                    <td class="px-4 py-3.5 hidden md:table-cell text-xs" style="color:#6b7280">
                        {{ $rx->prescribed_by ?? '—' }}
                    </td>

                    <td class="px-4 py-3.5 text-center">
                        <span class="text-sm font-black" style="color:#7c3aed">{{ $rx->medications->count() }}</span>
                        <div class="text-xs" style="color:#9ca3af">items</div>
                        @if($hasCatalogMeds < $rx->medications->count())
                            <div class="text-xs mt-0.5 flex items-center justify-center gap-1" style="color:#e74c3c">
                                <i class="bi bi-exclamation-triangle-fill" aria-hidden="true"></i>
                                {{ $rx->medications->count() - $hasCatalogMeds }} no code
                            </div>
                        @endif
                    </td>

                    <td class="px-4 py-3.5">
                        <x-ui.badge :variant="$statusVariant">{{ $statusLabel }}</x-ui.badge>
                        @if($rx->dispensed_by && $rx->dispensed_status === 'dispensed')
                            <div class="text-xs mt-1" style="color:#9ca3af">by {{ $rx->dispensed_by }}</div>
                        @endif
                    </td>

                    <td class="px-4 py-3.5" onclick="event.stopPropagation()">
                        @if($rx->dispensed_status !== 'dispensed')
                            <x-ui.button href="{{ route('pharmacy.show', $rx->code) }}" variant="primary" size="sm">
                                <x-slot:icon><i class="bi bi-bag-fill" aria-hidden="true"></i></x-slot:icon>
                                Dispense
                            </x-ui.button>
                        @else
                            <x-ui.button href="{{ route('pharmacy.show', $rx->code) }}" variant="secondary" size="sm">
                                <x-slot:icon><i class="bi bi-eye-fill" aria-hidden="true"></i></x-slot:icon>
                                View
                            </x-ui.button>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-5 py-12">
                        <x-ui.empty-state
                            icon="bi-capsule"
                            title="No prescriptions in the {{ $status }} queue"
                            description="Prescriptions appear here after being ordered during a patient visit." />
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</x-ui.card>

<x-ui.pagination :paginator="$prescriptions" />

@endsection
