@php
    /**
     * _rx-card-body.blade.php
     * Partial: body of one medication card in the Prescription step.
     * Variables: $i, $med (nullable PrescriptionMedicationModel), $formOptions, $catalog
     */
    $v = fn($field, $default='') => old("meds.{$i}.{$field}", $med?->{$field} ?? $default);
    $currentId = $med?->medication_id ?? '';
@endphp

{{-- ── Autocomplete: Quick-select from formulary ──────────────── --}}
<x-form.autocomplete
    :name           = "'meds['.$i.'][medicine_id]'"
    :name-text      = "'meds['.$i.'][medicine_search]'"
    km              = "ស្វែងរកថ្នាំ"
    en              = "Search Medicine"
    :items          = "$catalog"
    item-id         = "id"
    item-label      = "name"
    item-sub        = "strength"
    item-meta       = "form"
    item-badge      = "stock"
    badge-type      = "stock"
    :value-id       = "$currentId"
    :value-text     = "$med?->medicine_name ?? ''"
    placeholder     = "ស្វែងរក... / Type medicine name or strength…"
    :input-name     = "'rx_'.$i"
/>

{{-- Wire autocomplete select → fill sibling fields --}}
<script>
(function() {
    var wrap = document.querySelector('[id="acWrap_rx_{{ $i }}"]');
    if (!wrap) return;
    wrap.addEventListener('ac:select', function(e) {
        var d = e.detail;
        var idx = {{ $i }};
        var set = function(id, val) { var el = document.getElementById(id); if(el) el.value = val; };
        set('meds_name_'     + idx, d.label   || '');
        set('meds_strength_' + idx, d.sub     || '');
        set('meds_unit_'     + idx, d.meta    || '');
        // form select
        var sel = document.querySelector('[name="meds['+idx+'][form]"]');
        if (sel && d.form) sel.value = d.form;
        // stock indicator update
        var si = document.getElementById('rx_stock_' + idx);
        if (si && d.badge !== undefined) {
            var n = parseInt(d.badge);
            si.className = 'stock-badge ' + (n <= 0 ? 'stock-out' : n <= 10 ? 'stock-low' : 'stock-ok');
            si.textContent = n <= 0 ? 'Out of stock' : n <= 10 ? 'Low: ' + n : '✓ ' + n;
            si.style.display = 'inline-block';
        }
        // Update card header live
        var nameEl = document.querySelector('#rx-card-{{ $i }} .rx-card-name');
        if (nameEl) nameEl.textContent = d.label;
    });
})();
</script>

{{-- ── Main fields ────────────────────────────────────────────── --}}
<div class="row g-3 mb-3 mt-1">
    <div class="col-12 col-sm-5">
        <div class="fld">
            <label class="flbl">
                <span class="km">ឈ្មោះថ្នាំ</span><span class="en">/ Medicine Name</span>
                <span class="req">*</span>
            </label>
            <input id="meds_name_{{ $i }}"
                   name="meds[{{ $i }}][medicine_name]"
                   class="form-control" required
                   placeholder="Medicine name…"
                   value="{{ $v('medicine_name') }}"/>
        </div>
    </div>
    <div class="col-6 col-sm-3">
        <div class="fld">
            <label class="flbl"><span class="km">កម្លាំង</span><span class="en">/ Strength</span></label>
            <input id="meds_strength_{{ $i }}"
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
    <div class="col-6 col-sm-2">
        <div class="fld">
            <label class="flbl"><span class="km">រង្វាស់</span><span class="en">/ Unit</span></label>
            <input id="meds_unit_{{ $i }}"
                   name="meds[{{ $i }}][unit]"
                   class="form-control" placeholder="tab"
                   value="{{ $v('unit') }}"/>
        </div>
    </div>
    <div class="col-6 col-sm-4">
        <div class="fld">
            <label class="flbl"><span class="km">វិធី</span><span class="en">/ Method</span></label>
            <input name="meds[{{ $i }}][method]"
                   class="form-control" placeholder="Oral"
                   value="{{ $v('method','Oral') }}"/>
        </div>
    </div>
    <div class="col-12 col-sm-4">
        <div class="fld">
            <label class="flbl">
                <span class="km">ស្តុក</span><span class="en">/ Stock</span>
            </label>
            @php
                $stockQty  = $catalog->firstWhere('id', $currentId)?->stock ?? null;
                $stockCls  = $stockQty === null ? 'stock-ok' : ($stockQty <= 0 ? 'stock-out' : ($stockQty <= 10 ? 'stock-low' : 'stock-ok'));
                $stockLbl  = $stockQty === null ? '—' : ($stockQty <= 0 ? 'Out of stock' : ($stockQty <= 10 ? 'Low: '.$stockQty : '✓ '.$stockQty));
            @endphp
            <span id="rx_stock_{{ $i }}"
                  class="stock-badge {{ $stockCls }}"
                  style="{{ $stockQty === null ? 'display:none' : '' }}">
                {{ $stockLbl }}
            </span>
            <span style="font-size:11px;color:#6b7280" id="rx_stock_empty_{{ $i }}"
                  style="{{ $stockQty !== null ? 'display:none' : '' }}">
                Select medicine first
            </span>
        </div>
    </div>
    <div class="col-6 col-sm-4">
        <div class="fld">
            <label class="flbl"><span class="km">ចំណាំ</span><span class="en">/ Note</span></label>
            <input name="meds[{{ $i }}][note]"
                   class="form-control" placeholder="Take with food"
                   value="{{ $v('note') }}"/>
        </div>
    </div>
</div>

{{-- ── Dosing schedule ─────────────────────────────────────────── --}}
<div class="rx-dosing-wrap">
    <div class="rx-dosing-label">
        <i class="bi bi-clock-fill"></i> កាលវិភាគ / Dosing Schedule
    </div>
    <div class="dosing-grid">
        @foreach(['morning'=>['ព្រឹក','Morning'],'afternoon'=>['ថ្ងៃ','Afternoon'],'evening'=>['ល្ងាច','Evening'],'night'=>['យប់','Night']] as $field=>[$km,$en])
        <div class="dosing-cell">
            <div class="dosing-lbl">{{ $km }}<br><span style="font-size:9px;opacity:.7">{{ $en }}</span></div>
            <input class="dosing-in" type="number" step="0.5" min="0"
                   name="meds[{{ $i }}][{{ $field }}]"
                   value="{{ $v($field, 0) }}"/>
        </div>
        @endforeach
        <div class="dosing-cell">
            <div class="dosing-lbl">ថ្ងៃ<br><span style="font-size:9px;opacity:.7">Days</span></div>
            <input class="dosing-in" type="number" min="1"
                   name="meds[{{ $i }}][days]"
                   value="{{ $v('days', 1) }}"/>
        </div>
        <div class="dosing-cell dosing-cell--total" id="rx_total_{{ $i }}">
            <div class="dosing-lbl">Total<br><span style="font-size:9px;opacity:.7">qty</span></div>
            <div class="dosing-in" style="font-weight:800;color:#4154f1;font-size:18px;border:none;background:none;text-align:center;width:100%">0</div>
        </div>
    </div>
</div>

{{-- Dosing total calculator for this card --}}
<script>
(function() {
    var idx = {{ $i }};
    function calcTotal() {
        var fields = ['morning','afternoon','evening','night'];
        var sum = 0;
        fields.forEach(function(f) {
            var el = document.querySelector('[name="meds['+idx+']['+f+']"]');
            sum += parseFloat(el?.value || 0);
        });
        var days = parseFloat(document.querySelector('[name="meds['+idx+'][days]"]')?.value || 1);
        var totalEl = document.querySelector('#rx_total_'+idx+' div.dosing-in');
        if (totalEl) totalEl.textContent = Math.round(sum * days);
    }
    ['morning','afternoon','evening','night','days'].forEach(function(f) {
        var el = document.querySelector('[name="meds['+idx+']['+f+']"]');
        if (el) el.addEventListener('input', calcTotal);
    });
    calcTotal();
})();
</script>
