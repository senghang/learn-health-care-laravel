@extends('clinics.layout.app')
@section('title', 'Ledger — ' . $medicine->name)

@section('content')

<x-ui.page-header
    :km="$medicine->name"
    title="Stock Ledger"
    :breadcrumbs="[
        ['label' => __('app.home'), 'url' => route('dashboard')],
        ['label' => 'Inventory', 'url' => route('inventory.products')],
        ['label' => $medicine->code, 'url' => route('inventory.products')],
        ['label' => 'Ledger'],
    ]">
    <x-slot:actions>
        <x-ui.button href="{{ route('inventory.product.edit', $medicine->id) }}" variant="secondary" size="sm">
            <x-slot:icon><i class="bi bi-pencil" aria-hidden="true"></i></x-slot:icon>
            Edit
        </x-ui.button>
        <x-ui.button href="{{ route('inventory.stock-in') }}?medicine={{ $medicine->id }}" variant="success" size="sm">
            <x-slot:icon><i class="bi bi-plus-circle-fill" aria-hidden="true"></i></x-slot:icon>
            Stock In
        </x-ui.button>
        <x-ui.button href="{{ route('inventory.adjustment') }}" variant="warning" size="sm">
            <x-slot:icon><i class="bi bi-sliders" aria-hidden="true"></i></x-slot:icon>
            Adjust
        </x-ui.button>
    </x-slot:actions>
</x-ui.page-header>

<div class="grid grid-cols-1 lg:grid-cols-4 gap-5">

    {{-- ── Sidebar: Product Info ─────────────────────────────────── --}}
    <div>
        <x-ui.card class="sticky top-20">
            <x-slot:header>
                <div class="flex items-center gap-2 px-5 py-4" style="border-bottom:1px solid #e6e9f0">
                    <i class="bi bi-capsule-pill" style="color:#4154f1;font-size:15px" aria-hidden="true"></i>
                    <span class="text-sm font-bold" style="color:#1a1f36">Product Info</span>
                </div>
            </x-slot:header>

            @php
                $isOut = $medicine->stock <= 0;
                $isLow = !$isOut && $medicine->stock <= $medicine->stock_alert;
                $stockColor = $isOut ? '#e74c3c' : ($isLow ? '#ff771d' : '#2eca6a');
            @endphp

            <div class="text-center mb-4">
                <div class="text-4xl font-black" style="color:{{ $stockColor }}">{{ $medicine->stock }}</div>
                <div class="text-sm mt-1" style="color:#6b7280">{{ $medicine->unit ?? 'units' }} in stock</div>
                @if($isOut)
                    <x-ui.badge variant="danger" class="mt-2">OUT OF STOCK</x-ui.badge>
                @elseif($isLow)
                    <x-ui.badge variant="warning" class="mt-2">LOW STOCK</x-ui.badge>
                @endif
            </div>

            <div class="space-y-2">
                @foreach([
                    ['Code',        $medicine->code],
                    ['Category',    $medicine->category ?? '—'],
                    ['Form',        $medicine->form ?? '—'],
                    ['Strength',    $medicine->strength ?? '—'],
                    ['Unit Price',  number_format($medicine->price) . ' KHR'],
                    ['Alert Level', $medicine->stock_alert],
                    ['Status',      $medicine->is_active ? 'Active' : 'Inactive'],
                ] as [$lbl, $val])
                    <div class="flex items-center justify-between text-xs py-1.5" style="border-bottom:1px solid #f5f6ff">
                        <span style="color:#9ca3af">{{ $lbl }}</span>
                        <span class="font-semibold" style="color:#1a1f36">{{ $val }}</span>
                    </div>
                @endforeach
            </div>

            @if($balance)
                <div class="mt-4 p-3 rounded-lg" style="background:#f0fcff">
                    <div class="text-xs font-bold mb-2" style="color:#00bcd4">Balance Table</div>
                    <div class="space-y-1.5 text-xs">
                        <div class="flex justify-between">
                            <span style="color:#6b7280">On Hand</span>
                            <strong>{{ $balance->quantity_on_hand }}</strong>
                        </div>
                        <div class="flex justify-between">
                            <span style="color:#6b7280">Stock Value</span>
                            <strong>{{ number_format($balance->total_value) }} KHR</strong>
                        </div>
                        <div class="flex justify-between">
                            <span style="color:#6b7280">Last Movement</span>
                            <strong>{{ $balance->last_movement_at?->format('d/m/Y') ?? '—' }}</strong>
                        </div>
                    </div>
                </div>
            @endif
        </x-ui.card>
    </div>

    {{-- ── Ledger Table ───────────────────────────────────────────── --}}
    <div class="lg:col-span-3">
        <x-ui.card :noPadding="true">
            <x-slot:header>
                <div class="flex items-center justify-between px-5 py-4" style="border-bottom:1px solid #e6e9f0">
                    <div class="flex items-center gap-2">
                        <i class="bi bi-journal-text" style="color:#4154f1;font-size:15px" aria-hidden="true"></i>
                        <span class="text-sm font-bold" style="color:#1a1f36">Full Movement History</span>
                        <span class="text-xs font-semibold px-2 py-0.5 rounded-full" style="background:#f3f4f6;color:#6b7280">
                            {{ count($ledger) }} entries
                        </span>
                    </div>
                </div>
                {{-- Legend --}}
                <div class="flex flex-wrap gap-2 px-5 py-2.5" style="border-bottom:1px solid #e6e9f0">
                    @foreach([
                        ['Clinical',      '#eef0fd', '#4154f1'],
                        ['In / Return',   '#e8f8ef', '#2eca6a'],
                        ['Out / Expired', '#fde8e8', '#e74c3c'],
                        ['Adjustment',    '#fff8e1', '#b45309'],
                    ] as [$lbl, $bg, $col])
                        <span class="text-xs font-semibold px-2 py-0.5 rounded-full"
                              style="background:{{ $bg }};color:{{ $col }}">{{ $lbl }}</span>
                    @endforeach
                </div>
            </x-slot:header>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr style="background:#f8f9fb;border-bottom:1px solid #e6e9f0">
                            <th class="text-left px-5 py-3 text-xs font-bold" style="color:#6b7280">Date / Time</th>
                            <th class="text-left px-4 py-3 text-xs font-bold hidden sm:table-cell" style="color:#6b7280">Source</th>
                            <th class="text-left px-4 py-3 text-xs font-bold" style="color:#6b7280">Type</th>
                            <th class="text-center px-4 py-3 text-xs font-bold" style="color:#6b7280">Qty</th>
                            <th class="text-center px-4 py-3 text-xs font-bold hidden md:table-cell" style="color:#6b7280">Before</th>
                            <th class="text-center px-4 py-3 text-xs font-bold hidden md:table-cell" style="color:#6b7280">After</th>
                            <th class="text-left px-4 py-3 text-xs font-bold hidden lg:table-cell" style="color:#6b7280">Reference</th>
                            <th class="text-left px-4 py-3 text-xs font-bold hidden lg:table-cell" style="color:#6b7280">By / Note</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($ledger as $row)
                        @php
                            $qty    = $row['quantity'];
                            $isPlus = str_starts_with($qty, '+');
                            $isClin = $row['source'] === 'clinical';
                            $typeMap = [
                                'in'         => ['Stock In',  'success'],
                                'return'     => ['Return',    'warning'],
                                'out'        => ['Out',       'danger'],
                                'expired'    => ['Expired',   'secondary'],
                                'adjustment' => ['Adjusted',  'primary'],
                                'dispense'   => ['Dispense',  'danger'],
                            ];
                            [$lbl, $variant] = $typeMap[$row['type']] ?? [ucfirst($row['type']), 'secondary'];
                            $afterVal  = $row['after'] ?? 0;
                            $afterColor = $afterVal <= 0 ? '#e74c3c' : ($afterVal <= 10 ? '#ff771d' : '#1a1f36');
                        @endphp
                        <tr style="border-bottom:1px solid #f8f9fb;background:{{ $isClin ? '#f9fafb' : 'transparent' }}"
                            class="hover:bg-[#f8f9fb] transition-colors">

                            <td class="px-5 py-3">
                                <div class="text-xs font-semibold" style="color:#1a1f36">{{ $row['date']->format('d/m/Y') }}</div>
                                <div class="text-xs" style="color:#9ca3af">{{ $row['date']->format('H:i') }}</div>
                            </td>

                            <td class="px-4 py-3 hidden sm:table-cell">
                                <span class="text-xs font-bold px-2 py-0.5 rounded-full"
                                      style="background:{{ $isClin ? '#eef0fd' : '#e8f8ef' }};color:{{ $isClin ? '#4154f1' : '#2eca6a' }}">
                                    {{ $isClin ? 'Clinical' : 'Manual' }}
                                </span>
                            </td>

                            <td class="px-4 py-3">
                                <x-ui.badge :variant="$variant">{{ $lbl }}</x-ui.badge>
                            </td>

                            <td class="px-4 py-3 text-center">
                                <span class="text-base font-black"
                                      style="color:{{ $isPlus ? '#2eca6a' : '#e74c3c' }}">{{ $qty }}</span>
                            </td>

                            <td class="px-4 py-3 text-center hidden md:table-cell">
                                <span class="text-xs" style="color:#9ca3af">{{ $row['before'] }}</span>
                            </td>

                            <td class="px-4 py-3 text-center hidden md:table-cell">
                                <span class="text-sm font-bold" style="color:{{ $afterColor }}">{{ $row['after'] }}</span>
                            </td>

                            <td class="px-4 py-3 hidden lg:table-cell">
                                <span class="text-xs font-mono" style="color:#4154f1">{{ $row['reference'] ?? '—' }}</span>
                            </td>

                            <td class="px-4 py-3 hidden lg:table-cell">
                                <div class="text-xs" style="color:#9ca3af">{{ $row['by'] }}</div>
                                <div class="text-xs" style="color:#6b7280">{{ Str::limit($row['note'] ?? '', 50) }}</div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-12">
                                <x-ui.empty-state icon="bi-journal-text" title="No movement history yet"
                                    description="Stock movements for this product will appear here." />
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui.card>
    </div>

</div>{{-- /grid --}}

@endsection
