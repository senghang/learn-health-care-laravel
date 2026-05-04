@extends('clinics.layout.app')
@section('title', 'Stock Adjustment')

@section('content')

<x-ui.page-header
    km="កែតម្រូវស្តុក"
    title="Physical Count Adjustment"
    :breadcrumbs="[
        ['label' => __('app.home'), 'url' => route('dashboard')],
        ['label' => 'Inventory', 'url' => route('inventory.products')],
        ['label' => 'Adjustment'],
    ]">
    <x-slot:actions>
        <x-ui.button href="{{ route('inventory.movements') }}" variant="secondary" size="sm">
            <x-slot:icon><i class="bi bi-journal-text" aria-hidden="true"></i></x-slot:icon>
            All Movements
        </x-ui.button>
    </x-slot:actions>
</x-ui.page-header>

@if(session('flash'))
    <x-ui.alert type="success" class="mb-4">{{ session('flash') }}</x-ui.alert>
@endif
@if(session('error'))
    <x-ui.alert type="error" class="mb-4">{{ session('error') }}</x-ui.alert>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

    {{-- ── Adjustment Form ────────────────────────────────────────── --}}
    <div>
        <x-ui.card class="sticky top-20">
            <x-slot:header>
                <div class="flex items-center gap-2 px-5 py-4" style="border-bottom:1px solid #e6e9f0">
                    <i class="bi bi-sliders" style="color:#4154f1;font-size:15px" aria-hidden="true"></i>
                    <span class="text-sm font-bold" style="color:#1a1f36">Physical Count</span>
                </div>
            </x-slot:header>

            <x-ui.alert type="info" class="mb-4">
                Enter the <strong>actual counted quantity</strong>. The system will compute the delta and update stock accordingly.
            </x-ui.alert>

            @if($errors->any())
                <x-ui.alert type="error" class="mb-3">
                    <ul class="list-disc pl-4 text-xs space-y-0.5">
                        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                    </ul>
                </x-ui.alert>
            @endif

            <form method="POST" action="{{ route('inventory.adjustment.store') }}" novalidate class="space-y-3">
                @csrf

                {{-- Product --}}
                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold" style="color:#374151">
                        ថ្នាំ / ផលិតផល <span style="color:#ef4444">*</span>
                    </label>
                    <div class="relative">
                        <select name="medicine_id" id="medSelect" required
                                onchange="showCurrentStock(this)"
                                class="w-full text-sm rounded-lg border border-[#e2e8f0] bg-white px-3 py-2.5 text-[#374151] appearance-none focus:outline-none focus:border-[#4154f1] focus:ring-2 focus:ring-[#4154f1]/20 transition-colors"
                                style="padding-right:2.5rem">
                            <option value="">— ជ្រើស —</option>
                            @foreach($medicines as $med)
                                <option value="{{ $med->id }}"
                                        data-stock="{{ $med->stock }}"
                                        data-unit="{{ $med->unit }}"
                                        {{ old('medicine_id') == $med->id ? 'selected' : '' }}>
                                    {{ $med->name }} — Current: {{ $med->stock }} {{ $med->unit }}
                                </option>
                            @endforeach
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                            <i class="bi bi-chevron-down text-xs" style="color:#6b7280"></i>
                        </div>
                    </div>
                </div>

                {{-- Book stock display --}}
                <div id="stockDisplay" style="display:none" class="px-3 py-2.5 rounded-lg text-xs" style="background:#f6f8fa">
                    <span style="color:#6b7280">Book stock: </span>
                    <strong id="bookStockVal" style="color:#1a1f36;font-size:15px"></strong>
                    <span id="stockUnit" style="color:#9ca3af"></span>
                </div>

                {{-- Physical Count --}}
                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold" style="color:#374151">
                        ចំនួនរាប់ជាក់ស្ដែង / Physical Count <span style="color:#ef4444">*</span>
                    </label>
                    <x-forms.input type="number" name="new_qty" id="newQtyInput"
                                   :value="old('new_qty', 0)" min="0" required
                                   oninput="calcDelta()" />
                </div>

                {{-- Delta display --}}
                <div id="deltaDisplay" style="display:none;padding:10px;border-radius:8px;text-align:center">
                    <div class="text-xs font-bold mb-1" style="color:#6b7280">Adjustment Delta</div>
                    <div id="deltaVal" style="font-size:22px;font-weight:800"></div>
                </div>

                {{-- Reason --}}
                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold" style="color:#374151">ហេតុផល / Reason</label>
                    <textarea name="note" rows="2"
                              placeholder="Physical inventory count, shrinkage, damage…"
                              class="w-full text-sm rounded-lg border border-[#e2e8f0] bg-white px-3 py-2.5 text-[#374151] placeholder-[#9ca3af] focus:outline-none focus:border-[#4154f1] focus:ring-2 focus:ring-[#4154f1]/20 transition-colors resize-none">{{ old('note') }}</textarea>
                </div>

                <x-ui.button type="submit" variant="warning" :fullWidth="true">
                    <x-slot:icon><i class="bi bi-check2-circle" aria-hidden="true"></i></x-slot:icon>
                    Confirm Adjustment
                </x-ui.button>
            </form>
        </x-ui.card>
    </div>

    {{-- ── Adjustment History ───────────────────────────────────── --}}
    <div class="lg:col-span-2">
        <x-ui.card :noPadding="true">
            <x-slot:header>
                <div class="flex items-center justify-between px-5 py-4" style="border-bottom:1px solid #e6e9f0">
                    <div class="flex items-center gap-2">
                        <i class="bi bi-clock-history" style="color:#4154f1;font-size:15px" aria-hidden="true"></i>
                        <span class="text-sm font-bold" style="color:#1a1f36">Adjustment History</span>
                    </div>
                    <form method="GET" class="flex gap-2">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Medicine…"
                               class="text-xs rounded-lg border border-[#e2e8f0] bg-white px-3 py-1.5 w-36 focus:outline-none focus:border-[#4154f1] transition-colors" />
                        <input type="date" name="date" value="{{ request('date') }}"
                               class="text-xs rounded-lg border border-[#e2e8f0] bg-white px-3 py-1.5 focus:outline-none focus:border-[#4154f1] transition-colors" />
                        <x-ui.button type="submit" variant="primary" size="sm">
                            <x-slot:icon><i class="bi bi-funnel" aria-hidden="true"></i></x-slot:icon>
                        </x-ui.button>
                        @if(request()->hasAny(['search', 'date']))
                            <x-ui.button href="{{ route('inventory.adjustment') }}" variant="secondary" size="sm">
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
                            <th class="text-left px-4 py-3 text-xs font-bold" style="color:#6b7280">Medicine</th>
                            <th class="text-center px-4 py-3 text-xs font-bold hidden sm:table-cell" style="color:#6b7280">Before</th>
                            <th class="text-center px-4 py-3 text-xs font-bold hidden sm:table-cell" style="color:#6b7280">After</th>
                            <th class="text-center px-4 py-3 text-xs font-bold" style="color:#6b7280">Delta</th>
                            <th class="text-left px-4 py-3 text-xs font-bold hidden md:table-cell" style="color:#6b7280">Note</th>
                            <th class="text-left px-4 py-3 text-xs font-bold hidden lg:table-cell" style="color:#6b7280">By</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($movements as $mv)
                        @php $delta = $mv->stock_after - $mv->stock_before; @endphp
                        <tr style="border-bottom:1px solid #f8f9fb" class="hover:bg-[#f8f9fb] transition-colors">
                            <td class="px-5 py-3">
                                <div class="text-xs font-semibold" style="color:#1a1f36">{{ $mv->created_at->format('d/m/Y') }}</div>
                                <div class="text-xs" style="color:#9ca3af">{{ $mv->created_at->format('H:i') }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-sm font-semibold" style="color:#1a1f36">{{ $mv->medicine_name }}</div>
                                <code class="text-xs px-1 py-0.5 rounded" style="background:#eef0fd;color:#4154f1">{{ $mv->medicine_code }}</code>
                            </td>
                            <td class="px-4 py-3 text-center hidden sm:table-cell">
                                <span class="text-xs" style="color:#9ca3af">{{ $mv->stock_before }}</span>
                            </td>
                            <td class="px-4 py-3 text-center hidden sm:table-cell">
                                <span class="text-sm font-bold" style="color:#1a1f36">{{ $mv->stock_after }}</span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="text-base font-black" style="color:{{ $delta >= 0 ? '#2eca6a' : '#e74c3c' }}">
                                    {{ $delta >= 0 ? "+{$delta}" : "{$delta}" }}
                                </span>
                            </td>
                            <td class="px-4 py-3 hidden md:table-cell">
                                <span class="text-xs" style="color:#6b7280">{{ Str::limit($mv->note, 60) }}</span>
                            </td>
                            <td class="px-4 py-3 hidden lg:table-cell">
                                <span class="text-xs" style="color:#9ca3af">{{ $mv->recorded_by ?? '—' }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-12">
                                <x-ui.empty-state icon="bi-sliders" title="No adjustments yet"
                                    description="Physical count adjustments will appear here." />
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
function showCurrentStock(sel) {
    const opt = sel.options[sel.selectedIndex];
    const el  = document.getElementById('stockDisplay');
    if (!opt || !opt.value) { el.style.display = 'none'; return; }
    document.getElementById('bookStockVal').textContent = opt.dataset.stock;
    document.getElementById('stockUnit').textContent = opt.dataset.unit || '';
    el.style.display = 'block';
    calcDelta();
}
function calcDelta() {
    const medSel = document.getElementById('medSelect');
    const newQty = parseInt(document.getElementById('newQtyInput').value || 0);
    const opt    = medSel.options[medSel.selectedIndex];
    const dd     = document.getElementById('deltaDisplay');
    if (!opt || !opt.value) { dd.style.display = 'none'; return; }
    const book  = parseInt(opt.dataset.stock || 0);
    const delta = newQty - book;
    const dv    = document.getElementById('deltaVal');
    dv.textContent = (delta >= 0 ? '+' : '') + delta;
    dv.style.color = delta === 0 ? '#aaa' : (delta > 0 ? '#2eca6a' : '#e74c3c');
    dd.style.background = delta === 0 ? '#f5f5f5' : (delta > 0 ? '#e8f8ef' : '#fde8e8');
    dd.style.display = 'block';
}
document.addEventListener('DOMContentLoaded', function() {
    var sel = document.getElementById('medSelect');
    if (sel && sel.value) showCurrentStock(sel);
});
</script>
@endpush

@endsection
