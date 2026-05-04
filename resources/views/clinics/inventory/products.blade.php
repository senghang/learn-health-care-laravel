@extends('clinics.layout.app')
@section('title', 'Inventory — Products')

@section('content')

<x-ui.page-header
    km="ផលិតផល"
    title="Products"
    :breadcrumbs="[
        ['label' => __('app.home'), 'url' => route('dashboard')],
        ['label' => 'Inventory'],
        ['label' => 'Products'],
    ]">
    <x-slot:actions>
        <x-ui.button href="{{ route('inventory.movements') }}" variant="secondary" size="sm">
            <x-slot:icon><i class="bi bi-journal-text" aria-hidden="true"></i></x-slot:icon>
            Movements
        </x-ui.button>
        <x-ui.button href="{{ route('inventory.adjustment') }}" variant="warning" size="sm">
            <x-slot:icon><i class="bi bi-sliders" aria-hidden="true"></i></x-slot:icon>
            Adjust
        </x-ui.button>
        <x-ui.button href="{{ route('inventory.product.create') }}" variant="primary">
            <x-slot:icon><i class="bi bi-plus-circle-fill" aria-hidden="true"></i></x-slot:icon>
            <span class="hidden sm:inline">Add Product</span>
            <span class="sm:hidden">Add</span>
        </x-ui.button>
    </x-slot:actions>
</x-ui.page-header>

{{-- ── KPI STRIP ─────────────────────────────────────────────── --}}
<div class="grid grid-cols-2 xl:grid-cols-4 gap-4 mb-5">
    <x-ui.stats-card
        km="ផលិតផល" label="Total Products"
        :value="$stats['total']"
        icon="bi-box-seam-fill" color="#4154f1" bg="#eef0fd"
    />
    <x-ui.stats-card
        km="ស្តុកទាប" label="Low Stock"
        :value="$stats['low']"
        icon="bi-exclamation-triangle-fill" color="#ff771d" bg="#fff3e8"
        :href="route('inventory.products', ['status' => 'low'])"
    />
    <x-ui.stats-card
        km="អស់ស្តុក" label="Out of Stock"
        :value="$stats['out']"
        icon="bi-x-circle-fill" color="#e74c3c" bg="#fde8e8"
        :href="route('inventory.products', ['status' => 'out'])"
    />
    <x-ui.stats-card
        km="តម្លៃស្តុក" label="Stock Value"
        value="{{ number_format($stats['value']) }} KHR"
        icon="bi-cash-stack" color="#2eca6a" bg="#e8f8ef"
    />
</div>

{{-- ── FILTER ─────────────────────────────────────────────────── --}}
<x-ui.card class="mb-4">
    <form method="GET" action="{{ route('inventory.products') }}" role="search" aria-label="Filter products">
        <div class="flex flex-col sm:flex-row gap-3 flex-wrap">

            {{-- Search --}}
            <div class="flex-1 relative min-w-0">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none" aria-hidden="true">
                    <i class="bi bi-search text-sm" style="color:#6b7280"></i>
                </div>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Name, code, or generic name…"
                    class="w-full text-sm rounded-lg border border-[#e2e8f0] bg-white pl-9 pr-4 py-2.5 text-[#374151] placeholder-[#9ca3af] focus:outline-none focus:border-[#4154f1] focus:ring-2 focus:ring-[#4154f1]/20 transition-colors"
                    autofocus />
            </div>

            {{-- Category --}}
            <div class="relative sm:w-44">
                <select name="category" aria-label="Filter by category"
                    class="w-full text-sm rounded-lg border border-[#e2e8f0] bg-white px-4 py-2.5 text-[#374151] appearance-none focus:outline-none focus:border-[#4154f1] focus:ring-2 focus:ring-[#4154f1]/20 transition-colors"
                    style="padding-right:2.5rem">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}" @selected(request('category') === $cat)>{{ $cat }}</option>
                    @endforeach
                </select>
                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none" aria-hidden="true">
                    <i class="bi bi-chevron-down text-xs" style="color:#6b7280"></i>
                </div>
            </div>

            {{-- Status --}}
            <div class="relative sm:w-36">
                <select name="status" aria-label="Filter by stock status"
                    class="w-full text-sm rounded-lg border border-[#e2e8f0] bg-white px-4 py-2.5 text-[#374151] appearance-none focus:outline-none focus:border-[#4154f1] focus:ring-2 focus:ring-[#4154f1]/20 transition-colors"
                    style="padding-right:2.5rem">
                    <option value="">All Status</option>
                    <option value="low"    @selected(request('status') === 'low')>Low Stock</option>
                    <option value="out"    @selected(request('status') === 'out')>Out of Stock</option>
                    <option value="active" @selected(request('status') === 'active')>Active</option>
                </select>
                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none" aria-hidden="true">
                    <i class="bi bi-chevron-down text-xs" style="color:#6b7280"></i>
                </div>
            </div>

            {{-- Buttons --}}
            <div class="flex gap-2">
                <x-ui.button type="submit" variant="primary">
                    <x-slot:icon><i class="bi bi-funnel-fill" aria-hidden="true"></i></x-slot:icon>
                    Filter
                </x-ui.button>
                @if(request()->hasAny(['search', 'category', 'status']))
                    <x-ui.button href="{{ route('inventory.products') }}" variant="secondary">
                        <x-slot:icon><i class="bi bi-x-circle" aria-hidden="true"></i></x-slot:icon>
                        Clear
                    </x-ui.button>
                @endif
            </div>
        </div>
    </form>
</x-ui.card>

{{-- ── PRODUCTS TABLE ──────────────────────────────────────────── --}}
<x-ui.card :noPadding="true">
    <x-slot:header>
        <div class="flex items-center justify-between px-5 py-4" style="border-bottom:1px solid #e6e9f0">
            <div class="flex items-center gap-2">
                <i class="bi bi-box-seam-fill" style="color:#4154f1;font-size:15px" aria-hidden="true"></i>
                <span class="text-sm font-bold" style="color:#1a1f36">Product List</span>
                <span class="text-xs font-semibold px-2 py-0.5 rounded-full" style="background:#f3f4f6;color:#6b7280">
                    {{ number_format($medicines->total()) }}
                </span>
            </div>
        </div>
    </x-slot:header>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr style="background:#f8f9fb;border-bottom:1px solid #e6e9f0">
                    <th class="text-left px-5 py-3 text-xs font-bold" style="color:#6b7280">Code</th>
                    <th class="text-left px-4 py-3 text-xs font-bold" style="color:#6b7280">Name / Generic</th>
                    <th class="text-left px-4 py-3 text-xs font-bold hidden md:table-cell" style="color:#6b7280">Form / Strength</th>
                    <th class="text-right px-4 py-3 text-xs font-bold hidden sm:table-cell" style="color:#6b7280">Price (KHR)</th>
                    <th class="text-right px-4 py-3 text-xs font-bold" style="color:#6b7280">Stock</th>
                    <th class="text-right px-4 py-3 text-xs font-bold hidden lg:table-cell" style="color:#6b7280">Alert</th>
                    <th class="text-left px-4 py-3 text-xs font-bold" style="color:#6b7280">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
            @forelse($medicines as $med)
                @php
                    $isOut = $med->stock === 0;
                    $isLow = !$isOut && $med->stock <= $med->stock_alert;
                    $stockVariant = $isOut ? 'danger' : ($isLow ? 'warning' : 'success');
                    $stockLabel   = $isOut ? 'Out of Stock' : ($isLow ? 'Low Stock' : 'OK');
                    $rowBg = $isOut ? '#fff5f5' : ($isLow ? '#fffaf4' : 'transparent');
                @endphp
                <tr style="border-bottom:1px solid #f8f9fb;background:{{ $rowBg }}"
                    class="hover:bg-[#f8f9fb] transition-colors">

                    <td class="px-5 py-3.5">
                        <code class="text-xs font-bold px-1.5 py-0.5 rounded"
                              style="background:#eef0fd;color:#4154f1">{{ $med->code }}</code>
                    </td>

                    <td class="px-4 py-3.5">
                        <div class="text-sm font-semibold" style="color:#1a1f36">{{ $med->name }}</div>
                        @if($med->name_kh)
                            <div class="text-xs" style="color:#9ca3af">{{ $med->name_kh }}</div>
                        @endif
                        @if($med->generic_name)
                            <div class="text-xs italic" style="color:#b0b8c8">{{ $med->generic_name }}</div>
                        @endif
                        @if($med->category)
                            <span class="inline-block text-xs px-1.5 py-0.5 rounded-full mt-0.5"
                                  style="background:#e6e9f0;color:#4154f1">{{ $med->category }}</span>
                        @endif
                    </td>

                    <td class="px-4 py-3.5 hidden md:table-cell">
                        @if($med->form)
                            <span class="inline-block text-xs px-2 py-0.5 rounded-full"
                                  style="background:#eef0fd;color:#4154f1">{{ $med->form }}</span>
                        @endif
                        @if($med->strength)
                            <span class="text-xs ml-1" style="color:#6b7280">{{ $med->strength }}</span>
                        @endif
                    </td>

                    <td class="px-4 py-3.5 text-right hidden sm:table-cell">
                        <span class="text-sm font-bold" style="color:#1a1f36">{{ number_format($med->price) }}</span>
                    </td>

                    <td class="px-4 py-3.5 text-right">
                        <span class="text-base font-black"
                              style="color:{{ $isOut ? '#e74c3c' : ($isLow ? '#ff771d' : '#2eca6a') }}">
                            {{ $med->stock }}
                        </span>
                        @if($med->unit)
                            <span class="text-xs" style="color:#9ca3af"> {{ $med->unit }}</span>
                        @endif
                    </td>

                    <td class="px-4 py-3.5 text-right hidden lg:table-cell">
                        <span class="text-xs" style="color:#9ca3af">{{ $med->stock_alert }}</span>
                    </td>

                    <td class="px-4 py-3.5">
                        <x-ui.badge :variant="$stockVariant">{{ $stockLabel }}</x-ui.badge>
                    </td>

                    <td class="px-4 py-3.5">
                        <div class="flex gap-1.5">
                            <x-ui.button href="{{ route('inventory.product.ledger', $med->id) }}" variant="secondary" size="sm">
                                <x-slot:icon><i class="bi bi-journal-text" aria-hidden="true"></i></x-slot:icon>
                            </x-ui.button>
                            <x-ui.button href="{{ route('inventory.product.edit', $med->id) }}" variant="primary" size="sm">
                                <x-slot:icon><i class="bi bi-pencil" aria-hidden="true"></i></x-slot:icon>
                            </x-ui.button>
                            <x-ui.button href="{{ route('inventory.stock-in') }}?medicine={{ $med->id }}" variant="success" size="sm">
                                <x-slot:icon><i class="bi bi-plus-lg" aria-hidden="true"></i></x-slot:icon>
                            </x-ui.button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="px-5 py-12">
                        <x-ui.empty-state
                            icon="bi-capsule"
                            title="No products found"
                            description="Add your first medicine or adjust your filters.">
                            @if(!request()->hasAny(['search', 'category', 'status']))
                                <x-ui.button href="{{ route('inventory.product.create') }}" variant="primary" size="sm">
                                    <x-slot:icon><i class="bi bi-plus-lg" aria-hidden="true"></i></x-slot:icon>
                                    Add Product
                                </x-ui.button>
                            @endif
                        </x-ui.empty-state>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</x-ui.card>

<x-ui.pagination :paginator="$medicines" />

@endsection
