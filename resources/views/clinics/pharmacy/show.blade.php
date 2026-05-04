@php use Illuminate\Support\MessageBag; @endphp
@extends('clinics.layout.app')
@section('title', 'Dispense ' . $prescription->code)

@php
    $items       = $stockCheck['items'];
    $checkErrors = $stockCheck['errors'];
    $warnings    = $stockCheck['warnings'];
    $canFull     = $stockCheck['can_full_dispense'];
    $anyPending  = collect($items)->where('already_dispensed', false)->where('can_dispense', true)->count();
    $isFullyDone = $prescription->dispensed_status === 'dispensed';
@endphp

@section('content')

<x-ui.page-header
    title="Dispense {{ $prescription->code }}"
    km="ចែកចាយថ្នាំ"
    :breadcrumbs="[
        ['label' => __('app.home'),   'url' => route('dashboard')],
        ['label' => 'Pharmacy',       'url' => route('pharmacy.index')],
        ['label' => $prescription->code],
    ]">
    <x-slot:actions>
        <x-ui.button href="{{ route('print.prescription', $prescription->code) }}"
            variant="secondary" target="_blank">
            <x-slot:icon><i class="bi bi-printer-fill" aria-hidden="true"></i></x-slot:icon>
            Print Rx
        </x-ui.button>
        @if($prescription->visit_code)
            <x-ui.button href="{{ url('/workflow/'.$prescription->visit_code) }}" variant="secondary">
                <x-slot:icon><i class="bi bi-diagram-3-fill" aria-hidden="true"></i></x-slot:icon>
                Visit
            </x-ui.button>
        @endif
    </x-slot:actions>
</x-ui.page-header>

{{-- Flash messages --}}
@if(session('success'))
    <x-ui.alert type="success" class="mb-4">{{ session('success') }}</x-ui.alert>
@endif
@if(session('info'))
    <x-ui.alert type="info" class="mb-4">{{ session('info') }}</x-ui.alert>
@endif
@if(isset($errors) && $errors instanceof MessageBag && $errors->has('dispense'))
    <x-ui.alert type="error" class="mb-4">{{ $errors->first('dispense') }}</x-ui.alert>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

    {{-- ── LEFT: Medication Items ──────────────────────────────────── --}}
    <div class="lg:col-span-2 space-y-4">

        {{-- Stock errors --}}
        @if(count($checkErrors) > 0)
            <div class="flex gap-3 p-4 rounded-xl" style="background:#fde8e8;border:1px solid #fca5a5">
                <i class="bi bi-exclamation-triangle-fill flex-shrink-0 mt-0.5" style="color:#dc2626"></i>
                <div>
                    <p class="text-sm font-bold mb-1" style="color:#dc2626">
                        Stock Errors — Cannot fully dispense
                    </p>
                    @foreach($checkErrors as $err)
                        <p class="text-xs" style="color:#b91c1c">• {{ $err }}</p>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Warnings --}}
        @if(count($warnings) > 0)
            <div class="flex gap-3 p-4 rounded-xl" style="background:#fff8e1;border:1px solid #fde68a">
                <i class="bi bi-exclamation-circle-fill flex-shrink-0 mt-0.5" style="color:#b45309"></i>
                <div>
                    <p class="text-sm font-bold mb-1" style="color:#b45309">Warnings</p>
                    @foreach($warnings as $warn)
                        <p class="text-xs" style="color:#92400e">• {{ $warn }}</p>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Medication items --}}
        <x-ui.card :noPadding="true">
            <x-slot:header>
                <div class="flex items-center justify-between px-5 py-4" style="border-bottom:1px solid #e6e9f0">
                    <div class="flex items-center gap-2">
                        <i class="bi bi-capsule-fill" style="color:#e91e8c;font-size:15px" aria-hidden="true"></i>
                        <span class="text-sm font-bold" style="color:#1a1f36">ថ្នាំ / Medication Items</span>
                        <span class="text-xs font-semibold px-2 py-0.5 rounded-full"
                              style="background:#fde8f5;color:#e91e8c">{{ count($items) }}</span>
                    </div>
                    @if(!$isFullyDone)
                        <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold" style="color:#4154f1">
                            <input type="checkbox" id="selectAll" onchange="toggleAll(this)"
                                   class="w-3.5 h-3.5 rounded accent-[#4154f1]">
                            Select all
                        </label>
                    @endif
                </div>
            </x-slot:header>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr style="background:#f8f9fb;border-bottom:1px solid #e6e9f0">
                            @if(!$isFullyDone)
                                <th class="w-9 px-3 py-3"></th>
                            @endif
                            <th class="text-left px-3 py-3 text-xs font-bold" style="color:#6b7280">#</th>
                            <th class="text-left px-3 py-3 text-xs font-bold" style="color:#6b7280">Medicine</th>
                            <th class="text-center px-3 py-3 text-xs font-bold" style="color:#6b7280">Dose (M/A/E/N)</th>
                            <th class="text-center px-3 py-3 text-xs font-bold" style="color:#6b7280">Days</th>
                            <th class="text-center px-3 py-3 text-xs font-bold" style="color:#6b7280">Need</th>
                            <th class="text-center px-3 py-3 text-xs font-bold" style="color:#6b7280">Stock</th>
                            <th class="text-center px-3 py-3 text-xs font-bold" style="color:#6b7280">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($items as $i => $row)
                        @php
                            $med       = $row['medication'];
                            $medicine  = $row['medicine'];
                            $needed    = $row['needed'];
                            $available = $row['available'];
                            $canDispense = $row['can_dispense'];
                            $done      = $row['already_dispensed'];

                            if ($available === null) {
                                $stockVariant = 'secondary'; $stockTxt = '— no code';
                            } elseif ($available <= 0) {
                                $stockVariant = 'danger'; $stockTxt = 'Out of stock';
                            } elseif (!$row['sufficient']) {
                                $stockVariant = 'danger'; $stockTxt = "Only {$available}";
                            } elseif ($medicine && $available <= ($medicine->stock_alert ?? 10)) {
                                $stockVariant = 'warning'; $stockTxt = "Low: {$available}";
                            } else {
                                $stockVariant = 'success'; $stockTxt = "✓ {$available}";
                            }
                        @endphp
                        <tr class="{{ $done ? 'opacity-50' : '' }} transition-colors"
                            style="border-bottom:1px solid #f8f9fb">
                            @if(!$isFullyDone)
                                <td class="px-3 py-3 text-center">
                                    @if($canDispense)
                                        <input type="checkbox" class="item-check w-3.5 h-3.5 rounded accent-[#4154f1]"
                                               name="item_ids[]" value="{{ $med->id }}"
                                               form="partialForm" checked>
                                    @elseif($done)
                                        <i class="bi bi-check-circle-fill text-sm" style="color:#2eca6a" aria-label="Dispensed"></i>
                                    @else
                                        <i class="bi bi-x-circle-fill text-sm" style="color:#e74c3c" aria-label="Cannot dispense"></i>
                                    @endif
                                </td>
                            @endif

                            <td class="px-3 py-3 text-xs" style="color:#9ca3af">{{ $i + 1 }}</td>

                            <td class="px-3 py-3">
                                <div class="text-sm font-bold" style="color:#1a1f36">{{ $med->medicine_name }}</div>
                                <div class="flex items-center gap-1.5 mt-0.5 flex-wrap">
                                    @if($med->strength)
                                        <code class="text-xs px-1.5 py-0.5 rounded font-semibold"
                                              style="background:#eef0fd;color:#4154f1">{{ $med->strength }}</code>
                                    @endif
                                    @if($med->form)
                                        <span class="text-xs" style="color:#9b59b6">{{ $med->form }}</span>
                                    @endif
                                </div>
                                @if($med->note)
                                    <div class="text-xs italic mt-0.5" style="color:#9ca3af">{{ $med->note }}</div>
                                @endif
                                @if(!$med->medication_code)
                                    <div class="flex items-center gap-1 text-xs mt-1" style="color:#e74c3c">
                                        <i class="bi bi-exclamation-triangle-fill" aria-hidden="true"></i>
                                        No catalogue code
                                    </div>
                                @endif
                            </td>

                            <td class="px-3 py-3 text-center text-xs" style="color:#6b7280">
                                @php
                                    $parts = array_filter([
                                        $med->morning   > 0 ? $med->morning   . 'M' : null,
                                        $med->afternoon > 0 ? $med->afternoon . 'A' : null,
                                        $med->evening   > 0 ? $med->evening   . 'E' : null,
                                        $med->night     > 0 ? $med->night     . 'N' : null,
                                    ]);
                                @endphp
                                {{ implode(' · ', $parts) ?: '—' }}
                            </td>

                            <td class="px-3 py-3 text-center text-xs font-bold" style="color:#4154f1">
                                {{ $med->days ?? '—' }}
                            </td>

                            <td class="px-3 py-3 text-center">
                                <span class="text-base font-black" style="color:#e91e8c">{{ $needed }}</span>
                                @if($med->unit)
                                    <span class="text-xs" style="color:#9ca3af"> {{ $med->unit }}</span>
                                @endif
                            </td>

                            <td class="px-3 py-3 text-center">
                                @if($available !== null)
                                    <x-ui.badge :variant="$stockVariant" size="sm">{{ $stockTxt }}</x-ui.badge>
                                @else
                                    <span class="text-xs" style="color:#d1d5db">{{ $stockTxt }}</span>
                                @endif
                            </td>

                            <td class="px-3 py-3 text-center">
                                @if($done)
                                    <x-ui.badge variant="success" size="sm">Dispensed</x-ui.badge>
                                @elseif($canDispense)
                                    <x-ui.badge variant="warning" size="sm">Pending</x-ui.badge>
                                @else
                                    <x-ui.badge variant="danger" size="sm">Cannot</x-ui.badge>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </x-ui.card>

        {{-- Dispense History --}}
        @if($prescription->dispenses && $prescription->dispenses->count() > 0)
            <x-ui.card :noPadding="true">
                <x-slot:header>
                    <div class="flex items-center gap-2 px-5 py-4" style="border-bottom:1px solid #e6e9f0">
                        <i class="bi bi-clock-history" style="color:#7c3aed;font-size:15px" aria-hidden="true"></i>
                        <span class="text-sm font-bold" style="color:#1a1f36">Dispense History</span>
                    </div>
                </x-slot:header>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr style="background:#f8f9fb;border-bottom:1px solid #e6e9f0">
                                <th class="text-left px-5 py-3 text-xs font-bold" style="color:#6b7280">Code</th>
                                <th class="text-left px-4 py-3 text-xs font-bold" style="color:#6b7280">Medicine</th>
                                <th class="text-center px-4 py-3 text-xs font-bold" style="color:#6b7280">Qty</th>
                                <th class="text-left px-4 py-3 text-xs font-bold" style="color:#6b7280">Dispensed By</th>
                                <th class="text-left px-4 py-3 text-xs font-bold" style="color:#6b7280">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($prescription->dispenses->where('status','dispensed') as $d)
                            <tr style="border-bottom:1px solid #f8f9fb">
                                <td class="px-5 py-3">
                                    <code class="text-xs px-1.5 py-0.5 rounded font-bold"
                                          style="background:#f5eeff;color:#7c3aed">{{ $d->code }}</code>
                                </td>
                                <td class="px-4 py-3 text-sm font-semibold" style="color:#1a1f36">{{ $d->medicine_name }}</td>
                                <td class="px-4 py-3 text-center text-sm font-black" style="color:#e91e8c">{{ $d->quantity }}</td>
                                <td class="px-4 py-3 text-xs" style="color:#6b7280">{{ $d->dispensed_by ?? '—' }}</td>
                                <td class="px-4 py-3 text-xs" style="color:#9ca3af">{{ $d->dispensed_at?->format('d/m/Y H:i') }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </x-ui.card>
        @endif
    </div>

    {{-- ── RIGHT: Info + Dispense actions ───────────────────────────── --}}
    <div class="space-y-4">

        {{-- Prescription info --}}
        <x-ui.card>
            <div class="flex items-center gap-2 mb-4">
                <i class="bi bi-capsule-fill" style="color:#e91e8c" aria-hidden="true"></i>
                <code class="text-sm font-bold" style="color:#e91e8c">{{ $prescription->code }}</code>
            </div>
            <dl class="space-y-2.5">
                @foreach([
                    ['Doctor',        $prescription->prescribed_by ?? '—'],
                    ['Prescribed At', $prescription->prescribed_at?->format('d/m/Y H:i') ?? '—'],
                    ['Visit',         $prescription->visit_code ?? '—'],
                ] as [$lbl, $val])
                    <div class="flex justify-between text-xs">
                        <dt style="color:#9ca3af">{{ $lbl }}</dt>
                        <dd class="font-semibold" style="color:#374151">{{ $val }}</dd>
                    </div>
                @endforeach
            </dl>
            @php
                $sVariant = match($prescription->dispensed_status) {
                    'dispensed' => 'success',
                    'partial'   => 'warning',
                    default     => 'secondary',
                };
                $sLabel = match($prescription->dispensed_status) {
                    'dispensed' => 'Fully Dispensed',
                    'partial'   => 'Partially Dispensed',
                    default     => 'Pending',
                };
            @endphp
            <div class="flex justify-center mt-4 pt-4" style="border-top:1px solid #e6e9f0">
                <x-ui.badge :variant="$sVariant">{{ $sLabel }}</x-ui.badge>
            </div>
        </x-ui.card>

        {{-- Patient --}}
        @if($prescription->patient)
            @php
                $pt     = $prescription->patient;
                $colors = ['#4154f1','#2eca6a','#ff771d','#e74c3c','#9b59b6'];
                $pcol   = $colors[abs(crc32($pt->code)) % count($colors)];
            @endphp
            <x-ui.card>
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2">
                        <i class="bi bi-person-vcard-fill" style="color:#4154f1" aria-hidden="true"></i>
                        <span class="text-sm font-bold" style="color:#1a1f36">Patient</span>
                    </div>
                    <x-ui.button href="{{ route('patients.show', $pt->code) }}" variant="ghost" size="sm">
                        <x-slot:icon><i class="bi bi-arrow-right" aria-hidden="true"></i></x-slot:icon>
                    </x-ui.button>
                </div>
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center text-sm font-black text-white flex-shrink-0"
                         style="background:{{ $pcol }}" aria-hidden="true">
                        {{ strtoupper(substr($pt->surname,0,1) . substr($pt->name,0,1)) }}
                    </div>
                    <div>
                        <div class="text-sm font-black" style="color:#1a1f36">{{ $pt->surname }}, {{ $pt->name }}</div>
                        <code class="text-xs font-bold" style="color:#4154f1">{{ $pt->code }}</code>
                    </div>
                </div>
                <dl class="space-y-2">
                    @foreach([['DOB', $pt->birthdate?->format('d/m/Y') ?? '—'], ['Phone', $pt->phone ?? '—']] as [$l, $v])
                        <div class="flex justify-between text-xs">
                            <dt style="color:#9ca3af">{{ $l }}</dt>
                            <dd class="font-semibold" style="color:#374151">{{ $v }}</dd>
                        </div>
                    @endforeach
                </dl>
            </x-ui.card>
        @endif

        {{-- Dispense Actions --}}
        @if(!$isFullyDone)
            <div class="sticky" style="top:76px">
                <x-ui.card>
                    <div class="flex items-center gap-2 mb-4">
                        <i class="bi bi-bag-heart-fill" style="color:#7c3aed" aria-hidden="true"></i>
                        <span class="text-sm font-bold" style="color:#1a1f36">Dispense</span>
                    </div>

                    {{-- Dispensed by --}}
                    <div class="mb-4">
                        <label for="dispensedByInput" class="block text-xs font-semibold mb-1.5"
                               style="color:#7c3aed">Dispensed By <span style="color:#ef4444">*</span></label>
                        <input type="text" id="dispensedByInput"
                               class="w-full text-sm rounded-lg border px-3 py-2.5 text-[#374151] focus:outline-none focus:ring-2 transition-colors"
                               style="border-color:#c4b5fd;focus-ring-color:#7c3aed"
                               placeholder="Pharmacist name"
                               value="{{ auth()->user()?->name ?? '' }}"
                               oninput="syncDispensedBy(this.value)">
                    </div>

                    @if($anyPending > 0)
                        {{-- Full dispense --}}
                        @if($canFull)
                            <form method="POST" action="{{ route('pharmacy.dispense', $prescription->code) }}"
                                  id="fullForm">
                                @csrf
                                <input type="hidden" name="mode" value="full">
                                <input type="hidden" name="dispensed_by" id="dispensedBy_full"
                                       value="{{ auth()->user()?->name ?? '' }}">
                                <button type="submit"
                                        onclick="document.getElementById('dispensedBy_full').value = document.getElementById('dispensedByInput').value"
                                        class="w-full flex items-center justify-center gap-2 text-sm font-bold text-white rounded-xl px-4 py-3 mb-2.5 transition-all hover:opacity-90 active:scale-95"
                                        style="background:linear-gradient(135deg,#7c3aed,#9b59b6)">
                                    <i class="bi bi-bag-check-fill" aria-hidden="true"></i>
                                    Dispense All ({{ $anyPending }}) Items
                                </button>
                            </form>
                        @endif

                        {{-- Partial dispense --}}
                        <form method="POST" action="{{ route('pharmacy.dispense', $prescription->code) }}"
                              id="partialForm">
                            @csrf
                            <input type="hidden" name="mode" value="partial">
                            <input type="hidden" name="dispensed_by" id="dispensedBy_partial"
                                   value="{{ auth()->user()?->name ?? '' }}">
                            <button type="submit"
                                    onclick="document.getElementById('dispensedBy_partial').value = document.getElementById('dispensedByInput').value"
                                    class="w-full flex items-center justify-center gap-2 text-sm font-bold rounded-xl px-4 py-2.5 border-2 transition-all hover:bg-[#eef0fd] active:scale-95"
                                    style="color:#4154f1;border-color:#4154f1">
                                <i class="bi bi-pie-chart-fill" aria-hidden="true"></i>
                                Dispense Selected Items
                            </button>
                        </form>
                        <p class="text-xs text-center mt-2" style="color:#9ca3af">
                            Check items in the table to select for partial dispense
                        </p>

                    @else
                        <div class="flex gap-2 p-3 rounded-lg" style="background:#fff8e1;border:1px solid #fde68a">
                            <i class="bi bi-exclamation-triangle-fill flex-shrink-0" style="color:#b45309"></i>
                            <p class="text-xs" style="color:#92400e">
                                No items can be dispensed. Fix stock errors above.
                            </p>
                        </div>
                    @endif

                    <p class="flex items-start gap-1.5 text-xs mt-4 pt-4" style="border-top:1px solid #e6e9f0;color:#9ca3af">
                        <i class="bi bi-info-circle flex-shrink-0 mt-0.5" aria-hidden="true"></i>
                        Full dispense deducts stock for all pending items. Partial dispense processes checked items only.
                    </p>
                </x-ui.card>
            </div>

        @else
            <x-ui.card>
                <div class="flex flex-col items-center justify-center py-6 text-center">
                    <div class="w-16 h-16 rounded-2xl flex items-center justify-center mb-3"
                         style="background:#e8f8ef">
                        <i class="bi bi-bag-check-fill text-3xl" style="color:#2eca6a" aria-hidden="true"></i>
                    </div>
                    <div class="text-base font-black" style="color:#1D9E75">Fully Dispensed</div>
                    @if($prescription->dispensed_by)
                        <p class="text-xs mt-1" style="color:#9ca3af">by {{ $prescription->dispensed_by }}</p>
                    @endif
                </div>
            </x-ui.card>
        @endif
    </div>

</div>

@endsection

@push('scripts')
<script>
function toggleAll(cb) {
    document.querySelectorAll('.item-check').forEach(el => { el.checked = cb.checked; });
}
function syncDispensedBy(val) {
    var f = document.getElementById('dispensedBy_full');
    var p = document.getElementById('dispensedBy_partial');
    if (f) f.value = val;
    if (p) p.value = val;
}
</script>
@endpush
