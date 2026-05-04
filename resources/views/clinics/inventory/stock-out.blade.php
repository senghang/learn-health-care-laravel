@extends('clinics.layout.app')
@section('title', 'Stock Out')

@section('content')

<x-ui.page-header
    km="ចេញស្តុក"
    title="Stock Out"
    :breadcrumbs="[
        ['label' => __('app.home'), 'url' => route('dashboard')],
        ['label' => 'Inventory', 'url' => route('inventory.products')],
        ['label' => 'Stock Out'],
    ]">
</x-ui.page-header>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

    {{-- ── Form ──────────────────────────────────────────────────── --}}
    <div>
        <x-ui.card class="sticky top-20">
            <x-slot:header>
                <div class="flex items-center gap-2 px-5 py-4" style="border-bottom:1px solid #e6e9f0;background:#fef2f2">
                    <i class="bi bi-box-arrow-up-right" style="color:#e74c3c;font-size:15px" aria-hidden="true"></i>
                    <span class="text-sm font-bold" style="color:#1a1f36">ចេញស្តុក</span>
                    <span class="text-xs" style="color:#6b7280">/ Dispense / Remove</span>
                </div>
            </x-slot:header>

            @if($errors->any())
                <x-ui.alert type="error" class="mb-4">
                    <ul class="list-disc pl-4 text-xs space-y-0.5">
                        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                    </ul>
                </x-ui.alert>
            @endif

            <form method="POST" action="{{ route('inventory.stock-out.store') }}" novalidate class="space-y-3">
                @csrf

                {{-- Product --}}
                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold" style="color:#374151">
                        ថ្នាំ / ផលិតផល <span style="color:#ef4444">*</span>
                    </label>
                    <div class="relative">
                        <select name="medicine_id" id="medSelect" required
                                onchange="updateStock(this)"
                                class="w-full text-sm rounded-lg border border-[#e2e8f0] bg-white px-3 py-2.5 text-[#374151] appearance-none focus:outline-none focus:border-[#4154f1] focus:ring-2 focus:ring-[#4154f1]/20 transition-colors"
                                style="padding-right:2.5rem">
                            <option value="">— ជ្រើស —</option>
                            @foreach($medicines as $med)
                                @php $isLow = $med->stock <= 10 && $med->stock > 0; $isOut = $med->stock === 0; @endphp
                                <option value="{{ $med->id }}"
                                        data-stock="{{ $med->stock }}"
                                        data-unit="{{ $med->unit }}"
                                        style="{{ $isOut ? 'color:#e74c3c' : ($isLow ? 'color:#ff771d' : '') }}"
                                        {{ old('medicine_id') == $med->id ? 'selected' : '' }}>
                                    {{ $med->name }} — {{ $isOut ? '⚠ OUT' : "Stock: {$med->stock}" }}
                                </option>
                            @endforeach
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                            <i class="bi bi-chevron-down text-xs" style="color:#6b7280"></i>
                        </div>
                    </div>
                    <div id="stockInfo" class="text-xs" style="min-height:1rem"></div>
                </div>

                {{-- Quantity + Reason --}}
                <div class="grid grid-cols-2 gap-3">
                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold" style="color:#374151">
                            បរិមាណ <span style="color:#ef4444">*</span>
                        </label>
                        <x-forms.input type="number" name="quantity" :value="old('quantity', 1)" placeholder="0" min="1" required />
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold" style="color:#374151">ហេតុផល / Reason</label>
                        <x-forms.select name="type">
                            <option value="out"        @selected(old('type', 'out') === 'out')>Dispensed</option>
                            <option value="expired"    @selected(old('type', 'out') === 'expired')>Expired</option>
                            <option value="adjustment" @selected(old('type', 'out') === 'adjustment')>Adjustment</option>
                        </x-forms.select>
                    </div>
                </div>

                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold" style="color:#374151">យោង / Reference</label>
                    <x-forms.input name="reference" placeholder="V20260322001 or note" :value="old('reference')" />
                </div>

                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold" style="color:#374151">ចំណាំ / Note</label>
                    <textarea name="note" rows="2"
                              class="w-full text-sm rounded-lg border border-[#e2e8f0] bg-white px-3 py-2.5 text-[#374151] placeholder-[#9ca3af] focus:outline-none focus:border-[#4154f1] focus:ring-2 focus:ring-[#4154f1]/20 transition-colors resize-none">{{ old('note') }}</textarea>
                </div>

                <x-ui.button type="submit" variant="danger" :fullWidth="true">
                    <x-slot:icon><i class="bi bi-check2-circle" aria-hidden="true"></i></x-slot:icon>
                    Save Stock Out
                </x-ui.button>
            </form>
        </x-ui.card>
    </div>

    {{-- ── Movement History ─────────────────────────────────────── --}}
    <div class="lg:col-span-2">
        <x-ui.card :noPadding="true">
            <x-slot:header>
                <div class="flex items-center justify-between px-5 py-4" style="border-bottom:1px solid #e6e9f0">
                    <div class="flex items-center gap-2">
                        <i class="bi bi-clock-history" style="color:#e74c3c;font-size:15px" aria-hidden="true"></i>
                        <span class="text-sm font-bold" style="color:#1a1f36">ប្រវត្តិចេញស្តុក</span>
                        <span class="text-xs" style="color:#6b7280">/ Dispense History</span>
                    </div>
                    <form method="GET" class="flex gap-2">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search…"
                               class="text-xs rounded-lg border border-[#e2e8f0] bg-white px-3 py-1.5 w-32 focus:outline-none focus:border-[#4154f1] transition-colors" />
                        <input type="date" name="date" value="{{ request('date') }}"
                               class="text-xs rounded-lg border border-[#e2e8f0] bg-white px-3 py-1.5 focus:outline-none focus:border-[#4154f1] transition-colors" />
                        <x-ui.button type="submit" variant="primary" size="sm">
                            <x-slot:icon><i class="bi bi-funnel" aria-hidden="true"></i></x-slot:icon>
                        </x-ui.button>
                        @if(request()->hasAny(['search', 'date']))
                            <x-ui.button href="{{ route('inventory.stock-out') }}" variant="secondary" size="sm">
                                <x-slot:icon><i class="bi bi-x" aria-hidden="true"></i></x-slot:icon>
                            </x-ui.button>
                        @endif
                    </form>
                </div>
            </x-slot:header>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr style="background:#f8f9fb;border-bottom:1px solid #e6e9f0">
                            <th class="text-left px-5 py-3 text-xs font-bold" style="color:#6b7280">Date</th>
                            <th class="text-left px-4 py-3 text-xs font-bold" style="color:#6b7280">Product</th>
                            <th class="text-left px-4 py-3 text-xs font-bold hidden sm:table-cell" style="color:#6b7280">Type</th>
                            <th class="text-center px-4 py-3 text-xs font-bold" style="color:#6b7280">Qty</th>
                            <th class="text-center px-4 py-3 text-xs font-bold hidden md:table-cell" style="color:#6b7280">Before</th>
                            <th class="text-center px-4 py-3 text-xs font-bold hidden md:table-cell" style="color:#6b7280">After</th>
                            <th class="text-left px-4 py-3 text-xs font-bold hidden lg:table-cell" style="color:#6b7280">Ref</th>
                            <th class="text-left px-4 py-3 text-xs font-bold hidden lg:table-cell" style="color:#6b7280">By</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($movements as $mv)
                        @php
                            $typeVariants = ['out' => 'danger', 'expired' => 'secondary', 'adjustment' => 'warning'];
                            $typeLabels   = ['out' => 'Dispensed', 'expired' => 'Expired', 'adjustment' => 'Adjustment'];
                            $afterColor = $mv->stock_after <= 0 ? '#e74c3c' : '#1a1f36';
                        @endphp
                        <tr style="border-bottom:1px solid #f8f9fb" class="hover:bg-[#f8f9fb] transition-colors">
                            <td class="px-5 py-3">
                                <div class="text-xs font-semibold" style="color:#1a1f36">{{ $mv->created_at->format('d/m/Y') }}</div>
                                <div class="text-xs" style="color:#9ca3af">{{ $mv->created_at->format('H:i') }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-sm font-semibold" style="color:#1a1f36">{{ $mv->medicine_name }}</div>
                                <code class="text-xs px-1 py-0.5 rounded" style="background:#eef0fd;color:#4154f1">{{ $mv->medicine_code }}</code>
                            </td>
                            <td class="px-4 py-3 hidden sm:table-cell">
                                <x-ui.badge :variant="$typeVariants[$mv->type] ?? 'secondary'">
                                    {{ $typeLabels[$mv->type] ?? $mv->type }}
                                </x-ui.badge>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="text-base font-black" style="color:#e74c3c">-{{ $mv->quantity }}</span>
                            </td>
                            <td class="px-4 py-3 text-center hidden md:table-cell">
                                <span class="text-xs" style="color:#9ca3af">{{ $mv->stock_before }}</span>
                            </td>
                            <td class="px-4 py-3 text-center hidden md:table-cell">
                                <span class="text-sm font-bold" style="color:{{ $afterColor }}">{{ $mv->stock_after }}</span>
                            </td>
                            <td class="px-4 py-3 hidden lg:table-cell">
                                <span class="text-xs font-semibold" style="color:#4154f1">{{ $mv->reference ?? '—' }}</span>
                            </td>
                            <td class="px-4 py-3 hidden lg:table-cell">
                                <span class="text-xs" style="color:#9ca3af">{{ $mv->recorded_by ?? '—' }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-12">
                                <x-ui.empty-state icon="bi-box-arrow-up-right" title="No stock-out records found"
                                    description="Records appear after removing or dispensing stock." />
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui.card>

        @if($movements->hasPages())
            <x-ui.pagination :paginator="$movements" />
        @endif
    </div>

</div>{{-- /grid --}}

@push('scripts')
<script>
function updateStock(sel) {
    const opt = sel.options[sel.selectedIndex];
    const el  = document.getElementById('stockInfo');
    if (!opt || !opt.value) { el.textContent = ''; return; }
    const stock = parseInt(opt.dataset.stock);
    const unit  = opt.dataset.unit || '';
    const col   = stock === 0 ? '#e74c3c' : stock <= 10 ? '#ff771d' : '#2eca6a';
    el.innerHTML = `Available: <strong style="color:${col};font-size:14px">${stock}</strong> ${unit}`
        + (stock === 0 ? ' <span style="color:#e74c3c;font-size:11px">⚠ Out of stock</span>' : '');
}
document.addEventListener('DOMContentLoaded', function() {
    var sel = document.getElementById('medSelect');
    if (sel && sel.value) updateStock(sel);
});
</script>
@endpush

@endsection
