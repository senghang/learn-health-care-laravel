@php
    /**
     * _rx-card.blade.php
     *
     * Reusable medication card partial for standalone prescription CRUD forms.
     * Used by prescription-create.blade.php and prescription-edit.blade.php.
     *
     * Variables:
     *   $i           int                          — card index (0-based)
     *   $med         ?PrescriptionMedicationModel — null for new cards
     *   $formOptions array                        — form type options
     *   $catalog     Collection<MedicineModel>    — active medicine catalog
     */
    $v = fn(string $field, mixed $default = '') => old("meds.{$i}.{$field}", $med?->{$field} ?? $default);

    // Stock indicator for pre-filled cards
    $currentCode = $med?->medication_code ?? null;
    $stockItem   = $currentCode ? $catalog->firstWhere('code', $currentCode) : null;
    $stockQty    = $stockItem?->stock;
    $stockAlert  = $stockItem?->stock_alert ?? 10;
    $stockCls    = $stockQty === null ? 'stock-ok'
                 : ($stockQty <= 0           ? 'stock-out'
                 : ($stockQty <= $stockAlert ? 'stock-low' : 'stock-ok'));
    $stockLbl    = $stockQty === null ? '' : ($stockQty <= 0 ? 'Out of stock' : ($stockQty <= $stockAlert ? 'Low: '.$stockQty : '✓ '.$stockQty));
@endphp

<div class="rx-med-card rounded-xl mb-3 overflow-hidden"
     id="rx-card-{{ $i }}"
     style="border:1.5px solid #fce7f3;background:#fff">

    {{-- Card header --}}
    <div class="flex items-center justify-between flex-wrap gap-2 px-4 py-3 cursor-pointer"
         style="background:#fdf4ff;border-bottom:1px solid #fce7f3"
         onclick="this.nextElementSibling.style.display === 'none' ? this.nextElementSibling.style.display='block' : this.nextElementSibling.style.display='none'">
        <div class="flex items-center gap-2 flex-wrap">
            <span class="text-lg">💊</span>
            <strong id="rx-card-title-{{ $i }}" class="text-sm" style="color:#1a1f36">
                {{ $med?->medicine_name ?: ('ថ្នាំ #' . ((int)$i + 1)) }}
            </strong>
            @if($med?->strength)
                <code class="text-xs px-1.5 py-0.5 rounded-md" style="background:#eef0fd;color:#4154f1">{{ $med->strength }}</code>
            @endif
            <span id="stock-badge-{{ $i }}"
                  class="stock-badge {{ $stockCls }}"
                  style="{{ $stockQty === null ? 'display:none' : '' }}">{{ $stockLbl }}</span>
        </div>
        <div class="flex gap-2 items-center">
            <button type="button"
                    class="w-7 h-7 flex items-center justify-center rounded-lg hover:bg-[#fde8e8] transition-colors"
                    style="color:#e74c3c;border:1px solid #fca5a5;background:#fff"
                    onclick="event.stopPropagation();removeCard({{ $i }})">
                <i class="bi bi-trash3 text-xs" aria-hidden="true"></i>
            </button>
            <i class="bi bi-chevron-up text-sm" style="color:#9ca3af"></i>
        </div>
    </div>

    {{-- Card body --}}
    <div class="p-4">

        {{-- Catalog quick-select --}}
        <div class="rounded-xl p-3 mb-4" style="background:#eef0fd">
            <label class="block text-xs font-extrabold mb-1.5" style="color:#4154f1">
                <i class="bi bi-search" aria-hidden="true"></i> Quick Select from Formulary
            </label>
            <select class="w-full text-sm rounded-lg px-3 py-2 focus:outline-none transition-colors appearance-none"
                    style="border:1.5px solid #c5cbf9;background:#fff;color:#374151"
                    onchange="fillFromCatalog(this, {{ $i }})">
                <option value="">— Search catalogue —</option>
                @foreach($catalog as $c)
                    @php
                        $s = $c->stock ?? 0;
                        $a = $c->stock_alert ?? 10;
                        $badge = $s <= 0 ? '❌ Out' : ($s <= $a ? '⚠ Low:'.$s : '✓ '.$s);
                    @endphp
                    <option value="{{ $c->code }}"
                        {{ ($v('medicine_code') === $c->code) ? 'selected' : '' }}>
                        {{ $c->name }}@if($c->strength) ({{ $c->strength }})@endif · {{ $c->form }} — {{ $badge }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Hidden medicine_code --}}
        <input type="hidden" name="meds[{{ $i }}][medicine_code]"
               id="med_code_{{ $i }}" value="{{ $v('medicine_code') }}"/>

        {{-- Main fields --}}
        <div class="grid grid-cols-2 sm:grid-cols-6 gap-3 mb-4">
            <div class="col-span-2 sm:col-span-3 space-y-1">
                <label class="block text-xs font-semibold" style="color:#374151">
                    Medicine Name <span style="color:#ef4444">*</span>
                </label>
                <input id="med_name_{{ $i }}"
                       name="meds[{{ $i }}][medicine_name]"
                       class="w-full text-sm rounded-lg border border-[#e2e8f0] px-3 py-2 focus:outline-none focus:border-[#4154f1] transition-colors"
                       required placeholder="Medicine name…"
                       value="{{ $v('medicine_name') }}"
                       oninput="document.getElementById('rx-card-title-{{ $i }}').textContent = this.value || 'ថ្នាំ #{{ (int)$i + 1 }}'"/>
            </div>
            <div class="space-y-1">
                <label class="block text-xs font-semibold" style="color:#374151">Strength</label>
                <input id="med_strength_{{ $i }}"
                       name="meds[{{ $i }}][strength]"
                       class="w-full text-sm rounded-lg border border-[#e2e8f0] px-3 py-2 focus:outline-none focus:border-[#4154f1] transition-colors"
                       placeholder="500mg" value="{{ $v('strength') }}"/>
            </div>
            <div class="space-y-1">
                <label class="block text-xs font-semibold" style="color:#374151">Form</label>
                <select name="meds[{{ $i }}][form]"
                        class="w-full text-sm rounded-lg border border-[#e2e8f0] px-3 py-2 focus:outline-none focus:border-[#4154f1] transition-colors appearance-none"
                        style="background:#fff">
                    @foreach($formOptions as $f)
                        <option value="{{ $f }}" {{ $v('form') === $f ? 'selected' : '' }}>{{ $f }}</option>
                    @endforeach
                </select>
            </div>
            <div class="space-y-1">
                <label class="block text-xs font-semibold" style="color:#374151">Method</label>
                <input name="meds[{{ $i }}][method]"
                       class="w-full text-sm rounded-lg border border-[#e2e8f0] px-3 py-2 focus:outline-none focus:border-[#4154f1] transition-colors"
                       placeholder="Oral" value="{{ $v('method', 'Oral') }}"/>
            </div>
            <div class="space-y-1">
                <label class="block text-xs font-semibold" style="color:#374151">Unit</label>
                <input id="med_unit_{{ $i }}"
                       name="meds[{{ $i }}][unit]"
                       class="w-full text-sm rounded-lg border border-[#e2e8f0] px-3 py-2 focus:outline-none focus:border-[#4154f1] transition-colors"
                       placeholder="tab" value="{{ $v('unit') }}"/>
            </div>
            <div class="col-span-2 sm:col-span-3 space-y-1">
                <label class="block text-xs font-semibold" style="color:#374151">Note</label>
                <input name="meds[{{ $i }}][note]"
                       class="w-full text-sm rounded-lg border border-[#e2e8f0] px-3 py-2 focus:outline-none focus:border-[#4154f1] transition-colors"
                       placeholder="Take with food…" value="{{ $v('note') }}"/>
            </div>
        </div>

        {{-- Dosing schedule --}}
        <div class="rounded-xl p-3" style="background:#eef0fd">
            <div class="text-xs font-extrabold uppercase tracking-wide mb-3" style="color:#4154f1">
                <i class="bi bi-clock-fill" aria-hidden="true"></i> Dosing Schedule
            </div>
            <div class="grid gap-2" style="grid-template-columns:repeat(6,1fr)">
                @foreach(['morning'=>['ព្រឹក','Morning'],'afternoon'=>['ថ្ងៃ','Afternoon'],'evening'=>['ល្ងាច','Evening'],'night'=>['យប់','Night']] as $field=>[$km,$en])
                    <div class="rounded-lg p-2 text-center" style="background:#fff;border:1px solid #e6eaf5">
                        <div class="text-xs font-bold uppercase mb-1.5" style="color:#9ca3af;font-size:9px;letter-spacing:.3px">
                            {{ $km }}<br><span style="font-size:8px;opacity:.7">{{ $en }}</span>
                        </div>
                        <input type="number" step="0.5" min="0"
                               name="meds[{{ $i }}][{{ $field }}]"
                               value="{{ $v($field, 0) }}"
                               class="w-full text-center text-xl font-extrabold bg-transparent border-none outline-none"
                               style="color:#1a1f36"
                               oninput="calcTotal({{ $i }})"/>
                    </div>
                @endforeach

                {{-- Days --}}
                <div class="rounded-lg p-2 text-center" style="background:#fff;border:1px solid #e6eaf5">
                    <div class="font-bold uppercase mb-1.5" style="color:#9ca3af;font-size:9px;letter-spacing:.3px">
                        ថ្ងៃ<br><span style="font-size:8px;opacity:.7">Days</span>
                    </div>
                    <input type="number" min="1"
                           name="meds[{{ $i }}][days]"
                           value="{{ $v('days', 1) }}"
                           class="w-full text-center text-xl font-extrabold bg-transparent border-none outline-none"
                           style="color:#1a1f36"
                           oninput="calcTotal({{ $i }})"/>
                </div>

                {{-- Total qty (calculated) --}}
                <div class="rounded-lg p-2 text-center" style="background:#eef0fd;border:2px solid #c5cbf9">
                    <div class="font-bold uppercase mb-1.5" style="color:#4154f1;font-size:9px;letter-spacing:.3px">
                        Total<br><span style="font-size:8px;opacity:.7">qty</span>
                    </div>
                    <div id="rx-total-{{ $i }}" class="text-xl font-extrabold" style="color:#4154f1">
                        @php
                            $total = (($v('morning',0))+($v('afternoon',0))+($v('evening',0))+($v('night',0))) * max(1,$v('days',1));
                        @endphp
                        {{ $total > 0 ? $total : 0 }}
                    </div>
                </div>
            </div>

            {{-- Interval --}}
            <div class="mt-3 space-y-1">
                <label class="block text-xs font-semibold" style="color:#374151">Interval</label>
                <input type="text" name="meds[{{ $i }}][interval]"
                       class="w-full text-sm rounded-lg border border-[#c5cbf9] px-3 py-2 focus:outline-none focus:border-[#4154f1] transition-colors"
                       style="background:#fff"
                       placeholder="e.g. q8h, q12h, PRN"
                       value="{{ $v('interval') }}"/>
            </div>
        </div>

    </div>{{-- end card body --}}
</div>{{-- end rx-med-card --}}

<style>
    .stock-badge {
        font-size: 10px;
        padding: 2px 8px;
        border-radius: 10px;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 3px;
    }
    .stock-ok  { background:#e8f8ef; color:#1D9E75; border:1px solid #b7eacf; }
    .stock-low { background:#fff8e1; color:#b45309; border:1px solid #fde68a; }
    .stock-out { background:#fde8e8; color:#dc2626; border:1px solid #fca5a5; }
</style>
