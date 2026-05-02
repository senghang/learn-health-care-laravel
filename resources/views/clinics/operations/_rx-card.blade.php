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

<div class="rx-med-card" id="rx-card-{{ $i }}"
     style="border:1.5px solid #fce7f3;border-radius:12px;margin-bottom:10px;overflow:hidden;background:#fff">

    {{-- Card header --}}
    <div
        style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;padding:12px 16px;background:#fdf4ff;cursor:pointer;border-bottom:1px solid #fce7f3"
        onclick="this.nextElementSibling.style.display === 'none' ? this.nextElementSibling.style.display='block' : this.nextElementSibling.style.display='none'">
        <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
            <span style="font-size:18px">💊</span>
            <strong id="rx-card-title-{{ $i }}" style="font-size:14px;color:#012970">
                {{ $med?->medicine_name ?: ('ថ្នាំ #' . ((int)$i + 1)) }}
            </strong>
            @if($med?->strength)
                <code
                    style="font-size:11px;background:#eef0fd;color:#4154f1;padding:1px 7px;border-radius:5px">{{ $med->strength }}</code>
            @endif
            <span id="stock-badge-{{ $i }}"
                  class="stock-badge {{ $stockCls }}"
                  style="{{ $stockQty === null ? 'display:none' : '' }}">{{ $stockLbl }}</span>
        </div>
        <div style="display:flex;gap:6px;align-items:center">
            <button type="button" class="btn btn-sm btn-outline-danger"
                    onclick="event.stopPropagation();removeCard({{ $i }})">
                <i class="bi bi-trash3"></i>
            </button>
            <i class="bi bi-chevron-up" style="font-size:14px;color:#aaa;transition:transform .2s"></i>
        </div>
    </div>

    {{-- Card body --}}
    <div style="padding:16px">

        {{-- Catalog quick-select --}}
        <div class="fld mb-3" style="background:#f0f2ff;border-radius:8px;padding:10px 12px">
            <label class="flbl" style="color:#4154f1;font-size:11px;font-weight:800">
                <i class="bi bi-search"></i> Quick Select from Formulary
            </label>
            <select class="form-select" style="border-color:#c5cbf9"
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
                        {{ $c->name }}@if($c->strength)
                            ({{ $c->strength }})
                        @endif · {{ $c->form }} — {{ $badge }}
                    </option>
                @endforeach
            </select>
        </div>
        {{-- Hidden medicine_code field set by catalog select --}}
        <input type="hidden" name="meds[{{ $i }}][medicine_code]"
               id="med_code_{{ $i }}" value="{{ $v('medicine_code') }}"/>

        {{-- Main fields --}}
        <div class="row g-3 mb-3">
            <div class="col-12 col-sm-5">
                <div class="fld">
                    <label class="flbl">
                        <span class="km">ឈ្មោះថ្នាំ</span><span class="en">/ Medicine Name</span>
                        <span class="req">*</span>
                    </label>
                    <input id="med_name_{{ $i }}"
                           name="meds[{{ $i }}][medicine_name]"
                           class="form-control" required
                           placeholder="Medicine name…"
                           value="{{ $v('medicine_name') }}"
                           oninput="document.getElementById('rx-card-title-{{ $i }}').textContent = this.value || 'ថ្នាំ #{{ (int)$i + 1 }}'"/>
                </div>
            </div>
            <div class="col-6 col-sm-3">
                <div class="fld">
                    <label class="flbl"><span class="km">កម្លាំង</span><span class="en">/ Strength</span></label>
                    <input id="med_strength_{{ $i }}"
                           name="meds[{{ $i }}][strength]"
                           class="form-control" placeholder="500mg"
                           value="{{ $v('strength') }}"/>
                </div>
            </div>
            <div class="col-6 col-sm-2">
                <div class="fld">
                    <label class="flbl"><span class="km">ទំរង់</span><span class="en">/ Form</span></label>
                    <select name="meds[{{ $i }}][form]" class="form-select">
                        @foreach($formOptions as $f)
                            <option value="{{ $f }}" {{ $v('form') === $f ? 'selected' : '' }}>{{ $f }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-6 col-sm-4">
                <div class="fld">
                    <label class="flbl"><span class="km">វិធី</span><span class="en">/ Method</span></label>
                    <input name="meds[{{ $i }}][method]"
                           class="form-control" placeholder="Oral"
                           value="{{ $v('method', 'Oral') }}"/>
                </div>
            </div>
            <div class="col-6 col-sm-2">
                <div class="fld">
                    <label class="flbl"><span class="km">ឯកតា</span><span class="en">/ Unit</span></label>
                    <input id="med_unit_{{ $i }}"
                           name="meds[{{ $i }}][unit]"
                           class="form-control" placeholder="tab"
                           value="{{ $v('unit') }}"/>
                </div>
            </div>
            <div class="col-12 col-sm-6">
                <div class="fld">
                    <label class="flbl"><span class="km">ចំណាំ</span><span class="en">/ Note</span></label>
                    <input name="meds[{{ $i }}][note]"
                           class="form-control" placeholder="Take with food…"
                           value="{{ $v('note') }}"/>
                </div>
            </div>
        </div>

        {{-- Dosing schedule --}}
        <div style="background:#eef0fd;border-radius:10px;padding:14px">
            <div style="font-size:10px;font-weight:800;color:#4154f1;text-transform:uppercase;margin-bottom:10px">
                <i class="bi bi-clock-fill"></i> កាលវិភាគ / Dosing Schedule
            </div>
            <div style="display:grid;grid-template-columns:repeat(6,1fr);gap:8px">
                @foreach(['morning'=>['ព្រឹក','Morning'],'afternoon'=>['ថ្ងៃ','Afternoon'],'evening'=>['ល្ងាច','Evening'],'night'=>['យប់','Night']] as $field=>[$km,$en])
                    <div
                        style="background:#fff;border:1px solid #e6eaf5;border-radius:8px;padding:9px 4px;text-align:center">
                        <div
                            style="font-size:9px;color:#aaa;font-weight:700;text-transform:uppercase;letter-spacing:.3px;margin-bottom:4px">
                            {{ $km }}<br><span style="font-size:8px;opacity:.7">{{ $en }}</span>
                        </div>
                        <input type="number" step="0.5" min="0"
                               name="meds[{{ $i }}][{{ $field }}]"
                               value="{{ $v($field, 0) }}"
                               style="border:none;background:transparent;text-align:center;font-size:20px;font-weight:800;color:#012970;width:100%;outline:none"
                               oninput="calcTotal({{ $i }})"/>
                    </div>
                @endforeach

                {{-- Days --}}
                <div
                    style="background:#fff;border:1px solid #e6eaf5;border-radius:8px;padding:9px 4px;text-align:center">
                    <div
                        style="font-size:9px;color:#aaa;font-weight:700;text-transform:uppercase;letter-spacing:.3px;margin-bottom:4px">
                        ថ្ងៃ<br><span style="font-size:8px;opacity:.7">Days</span>
                    </div>
                    <input type="number" min="1"
                           name="meds[{{ $i }}][days]"
                           value="{{ $v('days', 1) }}"
                           style="border:none;background:transparent;text-align:center;font-size:20px;font-weight:800;color:#012970;width:100%;outline:none"
                           oninput="calcTotal({{ $i }})"/>
                </div>

                {{-- Total qty (calculated) --}}
                <div
                    style="background:#eef0fd;border:2px solid #c5cbf9;border-radius:8px;padding:9px 4px;text-align:center">
                    <div
                        style="font-size:9px;color:#4154f1;font-weight:700;text-transform:uppercase;letter-spacing:.3px;margin-bottom:4px">
                        Total<br><span style="font-size:8px;opacity:.7">qty</span>
                    </div>
                    <div id="rx-total-{{ $i }}"
                         style="font-size:18px;font-weight:800;color:#4154f1;text-align:center">
                        @php
                            $total = (($v('morning',0))+($v('afternoon',0))+($v('evening',0))+($v('night',0))) * max(1,$v('days',1));
                        @endphp
                        {{ $total > 0 ? $total : 0 }}
                    </div>
                </div>
            </div>

            {{-- Interval --}}
            <div class="fld mt-3">
                <label class="flbl" style="font-size:11px"><span class="km">ចន្លោះ</span><span
                        class="en">/ Interval</span></label>
                <input type="text" name="meds[{{ $i }}][interval]"
                       class="form-control form-control-sm"
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

    .stock-ok {
        background: #e8f8ef;
        color: #1D9E75;
        border: 1px solid #b7eacf;
    }

    .stock-low {
        background: #fff8e1;
        color: #b45309;
        border: 1px solid #fde68a;
    }

    .stock-out {
        background: #fde8e8;
        color: #dc2626;
        border: 1px solid #fca5a5;
    }
</style>
