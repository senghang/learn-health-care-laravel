@extends('clinics.layout.app')
@section('title', 'Inventory Report')
@section('content')

<x-ui.page-header
    km="របាយការណ៍ស្តុក"
    title="Inventory Report"
    :breadcrumbs="[['label'=>'ដើម','url'=>url('/')],['label'=>'Reports','url'=>route('reports.visits')],['label'=>'Inventory']]">
    <x-slot:actions>
        <x-ui.button href="{{ url('/reports/inventory?export=csv') }}" variant="secondary">
            <x-slot:icon><i class="bi bi-download"></i></x-slot:icon>
            Export CSV
        </x-ui.button>
    </x-slot:actions>
</x-ui.page-header>

{{-- KPIs --}}
@php
    $totalMeds  = $medicines->total();
    $lowCount   = $medicines->getCollection()->filter(fn($m)=> $m->stock <= $m->stock_alert && $m->stock > 0)->count();
    $outCount   = $medicines->getCollection()->filter(fn($m)=> $m->stock == 0)->count();
    $totalValue = $medicines->getCollection()->sum(fn($m)=> $m->stock * $m->price);
@endphp
<div class="grid grid-cols-2 xl:grid-cols-4 gap-3 mb-4">
    <x-ui.stats-card km="ថ្នាំទាំងអស់" label="Total Medicines" :value="$totalMeds" icon="bi-capsule-fill" color="#4154f1" bg="#eef0fd"/>
    <x-ui.stats-card km="ស្តុកទាប" label="Low Stock" :value="$lowCount" icon="bi-exclamation-triangle-fill" color="#ff771d" bg="#fff3e8"/>
    <x-ui.stats-card km="អស់ស្តុក" label="Out of Stock" :value="$outCount" icon="bi-x-circle-fill" color="#e74c3c" bg="#fde8e8"/>
    <x-ui.stats-card km="តម្លៃស្តុក" label="Stock Value" :value="number_format($totalValue).' KHR'" icon="bi-cash-stack" color="#2eca6a" bg="#e8f8ef"/>
</div>

{{-- Filter --}}
<x-ui.card class="mb-4" :noPadding="false">
    <form method="GET">
        <div class="flex flex-col sm:flex-row gap-3 flex-wrap">
            <div class="flex-1 min-w-0">
                <input type="text" name="search"
                       class="w-full text-sm rounded-lg border border-[#e2e8f0] bg-white px-3 py-2.5 text-[#374151] placeholder-[#94a3b8] focus:outline-none focus:border-[#4154f1] focus:ring-2 focus:ring-[#4154f1]/20"
                       placeholder="ឈ្មោះថ្នាំ / medicine name…" value="{{ request('search') }}"/>
            </div>
            <div class="sm:w-40">
                <select name="filter" class="w-full text-sm rounded-lg border border-[#e2e8f0] bg-white px-3 py-2.5 text-[#374151] focus:outline-none focus:border-[#4154f1] focus:ring-2 focus:ring-[#4154f1]/20 appearance-none">
                    <option value="">All</option>
                    <option value="low" {{ request('filter')==='low'?'selected':'' }}>Low Stock</option>
                    <option value="out" {{ request('filter')==='out'?'selected':'' }}>Out of Stock</option>
                    <option value="ok"  {{ request('filter')==='ok'?'selected':'' }}>OK</option>
                </select>
            </div>
            <div class="flex gap-2">
                <x-ui.button type="submit" variant="primary">
                    <x-slot:icon><i class="bi bi-funnel-fill"></i></x-slot:icon>
                    Filter
                </x-ui.button>
                @if(request()->hasAny(['search','filter']))
                    <x-ui.button href="{{ url('/reports/inventory') }}" variant="secondary">
                        <x-slot:icon><i class="bi bi-x-circle"></i></x-slot:icon>
                        Clear
                    </x-ui.button>
                @endif
            </div>
        </div>
    </form>
</x-ui.card>

{{-- Table --}}
<x-ui.card>
    <x-slot:header>
        <x-ui.card-header km="ស្តុកថ្នាំ" label="Medicine Stock" icon="bi-table" :count="$medicines->total()"/>
    </x-slot:header>

    <x-ui.table>
        <x-slot:head>
            <tr>
                <x-ui.table-th>Code</x-ui.table-th>
                <x-ui.table-th>Name</x-ui.table-th>
                <x-ui.table-th>Form / Strength</x-ui.table-th>
                <x-ui.table-th align="right">Unit Price</x-ui.table-th>
                <x-ui.table-th align="right">Stock</x-ui.table-th>
                <x-ui.table-th align="right">Alert At</x-ui.table-th>
                <x-ui.table-th align="right">Value</x-ui.table-th>
                <x-ui.table-th>Status</x-ui.table-th>
            </tr>
        </x-slot:head>
        <x-slot:body>
        @forelse($medicines as $med)
        @php
            $isOut  = $med->stock == 0;
            $isLow  = !$isOut && $med->stock <= $med->stock_alert;
        @endphp
        <tr class="{{ $isOut ? 'bg-[#fff5f5]' : ($isLow ? 'bg-[#fffaf4]' : 'hover:bg-[#fafbff]') }} transition-colors">
            <x-ui.table-td>
                <code class="text-[#4154f1] text-[11px]">{{ $med->code }}</code>
            </x-ui.table-td>
            <x-ui.table-td>
                <div class="font-semibold text-[#012970]">{{ $med->name }}</div>
                @if($med->name_kh)<div class="text-[10.5px] text-[#94a3b8]">{{ $med->name_kh }}</div>@endif
                @if($med->generic_name)<div class="text-[10px] text-[#bbb] italic">{{ $med->generic_name }}</div>@endif
            </x-ui.table-td>
            <x-ui.table-td>
                @if($med->form)<x-ui.badge variant="primary" size="sm" class="mr-1">{{ $med->form }}</x-ui.badge>@endif
                @if($med->strength)<span class="text-[11px] text-[#888]">{{ $med->strength }}</span>@endif
            </x-ui.table-td>
            <x-ui.table-td align="right">
                <span class="text-sm font-semibold text-[#012970]">{{ number_format($med->price) }}</span>
            </x-ui.table-td>
            <x-ui.table-td align="right">
                <span class="text-lg font-black" style="color:{{ $isOut ? '#e74c3c' : ($isLow ? '#ff771d' : '#2eca6a') }}">{{ $med->stock }}</span>
                @if($med->unit)<span class="text-[10px] text-[#94a3b8]"> {{ $med->unit }}</span>@endif
            </x-ui.table-td>
            <x-ui.table-td align="right">
                <span class="text-xs text-[#94a3b8]">{{ $med->stock_alert }}</span>
            </x-ui.table-td>
            <x-ui.table-td align="right">
                <span class="text-xs text-[#555]">{{ number_format($med->stock * $med->price) }}</span>
            </x-ui.table-td>
            <x-ui.table-td>
                @if($isOut)
                    <x-ui.badge variant="critical" size="sm"><i class="bi bi-x-circle-fill text-[9px] mr-0.5"></i> Out</x-ui.badge>
                @elseif($isLow)
                    <x-ui.badge variant="warning" size="sm"><i class="bi bi-exclamation-triangle-fill text-[9px] mr-0.5"></i> Low</x-ui.badge>
                @else
                    <x-ui.badge variant="success" size="sm"><i class="bi bi-check-circle-fill text-[9px] mr-0.5"></i> OK</x-ui.badge>
                @endif
            </x-ui.table-td>
        </tr>
        @empty
        <tr>
            <td colspan="8">
                <x-ui.empty-state icon="bi-capsule" title="No medicines found" description="Try adjusting your search or filter." compact/>
            </td>
        </tr>
        @endforelse
        </x-slot:body>
    </x-ui.table>

    <x-ui.pagination :paginator="$medicines" class="mt-4 px-4 pb-4"/>
</x-ui.card>

@endsection
