{{--
    <x-form.autocomplete
        name         = "medicine_id"          -- hidden ID field name
        name-text    = "medicine_name"         -- visible text field name
        km           = "ថ្នាំ"
        en           = "Medicine"
        :items       = "$catalog"              -- Collection of items
        item-id      = "id"                    -- item field for the hidden value
        item-label   = "name"                  -- primary display field
        item-sub     = "strength"              -- subtitle field (optional)
        item-meta    = "form"                  -- secondary meta (optional)
        item-badge   = "stock"                 -- badge value (stock/price)
        badge-type   = "stock"                 -- 'stock' | 'price' | null
        :value-id    = "$savedId"              -- currently selected id
        :value-text  = "$savedText"            -- currently selected display text
        placeholder  = "Type to search…"
        :required    = "true"
        input-name   = "svc"                   -- unique JS prefix to avoid conflicts
    />

    Renders a live-search autocomplete with:
    - visible text input (typing filters)
    - hidden ID input (submitted value)
    - dropdown with rich result rows
    - keyboard navigation (↑↓ Enter Esc)
    - badge showing stock level or price
--}}
@props([
    'name'        => 'item_id',
    'nameText'    => 'item_name',
    'km'          => '',
    'en'          => '',
    'items'       => collect([]),
    'itemId'      => 'id',
    'itemLabel'   => 'name',
    'itemSub'     => null,
    'itemMeta'    => null,
    'itemBadge'   => null,
    'badgeType'   => null,      // 'stock' | 'price' | null
    'valueId'     => null,
    'valueText'   => null,
    'placeholder' => 'Search…',
    'required'    => false,
    'inputName'   => null,      // unique prefix for JS vars
])

@php
    // Unique JS instance ID to allow multiple autocompletes on one page
    $uid = $inputName ?? ('ac_' . str_replace([' ','-','.','[',']'], '_', $name));

    // Serialise items as compact JS array
    $jsItems = $items->map(function($item) use ($itemId, $itemLabel, $itemSub, $itemMeta, $itemBadge) {
        return [
            'id'    => data_get($item, $itemId),
            'label' => data_get($item, $itemLabel) ?? '',
            'sub'   => $itemSub  ? (data_get($item, $itemSub)  ?? '') : null,
            'meta'  => $itemMeta ? (data_get($item, $itemMeta) ?? '') : null,
            'badge' => $itemBadge ? data_get($item, $itemBadge) : null,
        ];
    })->values()->all();

    $initText = old($nameText, $valueText ?? '');
    $initId   = old($name,     $valueId   ?? '');
@endphp

<div class="fld ac-wrap" id="acWrap_{{ $uid }}" style="position:relative">
    <label class="flbl">
        @if($km)<span class="km">{{ $km }}</span>@endif
        @if($en)<span class="en">/ {{ $en }}</span>@endif
        @if($required)<span class="req">*</span>@endif
    </label>

    {{-- Hidden ID value (submitted) --}}
    <input type="hidden"
           name="{{ $name }}"
           id="acId_{{ $uid }}"
           value="{{ $initId }}"/>

    {{-- Visible text input --}}
    <div style="position:relative">
        <i class="bi bi-search" style="position:absolute;left:11px;top:50%;transform:translateY(-50%);color:#94a3b8;font-size:13px;pointer-events:none;z-index:1"></i>
        <input type="text"
               name="{{ $nameText }}"
               id="acText_{{ $uid }}"
               class="form-control {{ $errors->has($name) ? 'is-invalid' : '' }}"
               style="padding-left:34px;padding-right:30px"
               placeholder="{{ $placeholder }}"
               value="{{ $initText }}"
               autocomplete="off"
               spellcheck="false"
               {{ $required && !$initId ? 'required' : '' }}
               data-ac-uid="{{ $uid }}"/>
        <button type="button"
                id="acClear_{{ $uid }}"
                style="position:absolute;right:8px;top:50%;transform:translateY(-50%);background:none;border:none;color:#94a3b8;font-size:14px;cursor:pointer;padding:0;display:{{ $initText ? 'block' : 'none' }};line-height:1;z-index:2"
                onclick="acClear('{{ $uid }}')">
            <i class="bi bi-x-circle-fill"></i>
        </button>
    </div>

    {{-- Dropdown --}}
    <div id="acDd_{{ $uid }}"
         class="ac-dropdown"
         style="display:none"
         role="listbox">
    </div>

    @error($name)
        <div class="field-error">{{ $message }}</div>
    @enderror
</div>

<script>
(function() {
    var UID   = '{{ $uid }}';
    var ITEMS = @json($jsItems);
    var BTYPE = '{{ $badgeType }}';

    var elText  = document.getElementById('acText_'  + UID);
    var elId    = document.getElementById('acId_'    + UID);
    var elDd    = document.getElementById('acDd_'    + UID);
    var elClear = document.getElementById('acClear_' + UID);
    var elWrap  = document.getElementById('acWrap_'  + UID);

    if (!elText) return;

    var filtered = [];
    var activeIdx = -1;

    /* ── Filter items ────────────────────────────────────────────── */
    function filterItems(q) {
        if (!q) return ITEMS.slice(0, 12);
        q = q.toLowerCase();
        return ITEMS.filter(function(item) {
            return (item.label || '').toLowerCase().indexOf(q) !== -1
                || (item.sub  || '').toLowerCase().indexOf(q) !== -1
                || (item.meta || '').toLowerCase().indexOf(q) !== -1;
        }).slice(0, 12);
    }

    /* ── Badge HTML ──────────────────────────────────────────────── */
    function badgeHtml(val) {
        if (val === null || val === undefined) return '';
        if (BTYPE === 'stock') {
            var n = parseInt(val);
            if (n <= 0)  return '<span class="stock-badge stock-out">Out</span>';
            if (n <= 10) return '<span class="stock-badge stock-low">Low: ' + n + '</span>';
            return '<span class="stock-badge stock-ok">✓ ' + n + '</span>';
        }
        if (BTYPE === 'price') {
            return '<span style="font-size:11px;color:#4154f1;font-weight:700">' + parseInt(val).toLocaleString() + ' KHR</span>';
        }
        return '<span style="font-size:11px;color:#64748b">' + esc(String(val)) + '</span>';
    }

    /* ── Highlight match in string ───────────────────────────────── */
    function highlight(str, q) {
        if (!str) return '';
        if (!q)   return esc(str);
        var idx = str.toLowerCase().indexOf(q.toLowerCase());
        if (idx === -1) return esc(str);
        return esc(str.slice(0, idx))
             + '<mark style="background:#fef9c3;color:#7a5e00;border-radius:2px;padding:0 1px">'
             + esc(str.slice(idx, idx + q.length))
             + '</mark>'
             + esc(str.slice(idx + q.length));
    }

    function esc(s) {
        return String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    /* ── Render dropdown ─────────────────────────────────────────── */
    function renderDd(q) {
        filtered  = filterItems(q);
        activeIdx = -1;

        if (!filtered.length) {
            elDd.innerHTML = '<div class="ac-empty"><i class="bi bi-search" style="font-size:20px;opacity:.3;margin-bottom:6px;display:block"></i>No results for <strong>' + esc(q) + '</strong></div>';
        } else {
            elDd.innerHTML = filtered.map(function(item, i) {
                return '<div class="ac-row" role="option" data-idx="' + i + '" onmousedown="acSelect(\'' + UID + '\',' + i + ')">'
                    + '<div class="ac-row-body">'
                    + '<div class="ac-row-label">' + highlight(item.label, q) + '</div>'
                    + ((item.sub || item.meta) ?
                        '<div class="ac-row-sub">'
                        + (item.sub  ? '<span>' + esc(item.sub)  + '</span>' : '')
                        + (item.meta ? '<span class="ac-meta">' + esc(item.meta) + '</span>' : '')
                        + '</div>' : '')
                    + '</div>'
                    + (item.badge !== null && item.badge !== undefined ? '<div class="ac-row-badge">' + badgeHtml(item.badge) + '</div>' : '')
                    + '</div>';
            }).join('');
        }
        openDd();
    }

    function openDd()  { elDd.style.display = 'block'; }
    function closeDd() { elDd.style.display = 'none'; activeIdx = -1; }

    /* ── Select item ─────────────────────────────────────────────── */
    window['acSelect'] = window['acSelect'] || function() {};
    var oldSelect = window['acSelect'];
    window['acSelect'] = function(uid, idx) {
        if (uid !== UID) { if (oldSelect) oldSelect(uid, idx); return; }
        var item = filtered[idx];
        if (!item) return;
        elText.value  = item.label;
        elId.value    = item.id;
        elText.removeAttribute('required');  // hidden id is the real value
        elClear.style.display = 'block';
        closeDd();
        // Dispatch change so parent form can react
        elText.dispatchEvent(new CustomEvent('ac:select', { detail: item, bubbles: true }));
    };

    /* ── Clear ───────────────────────────────────────────────────── */
    window['acClear'] = window['acClear'] || function() {};
    var oldClear = window['acClear'];
    window['acClear'] = function(uid) {
        if (uid !== UID) { if (oldClear) oldClear(uid); return; }
        elText.value = '';
        elId.value   = '';
        elClear.style.display = 'none';
        closeDd();
        elText.focus();
    };

    /* ── Keyboard navigation ─────────────────────────────────────── */
    function highlightRow() {
        elDd.querySelectorAll('.ac-row').forEach(function(r, i) {
            r.classList.toggle('active', i === activeIdx);
        });
        var row = elDd.querySelector('.ac-row.active');
        if (row) row.scrollIntoView({ block: 'nearest' });
    }

    elText.addEventListener('input', function() {
        var q = this.value.trim();
        elClear.style.display = q ? 'block' : 'none';
        if (!elId.value || this.value !== filtered[activeIdx]?.label) {
            elId.value = ''; // user is typing again — clear hidden value
        }
        renderDd(q);
    });

    elText.addEventListener('keydown', function(e) {
        var rows = elDd.querySelectorAll('.ac-row');
        if (!rows.length) return;

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            activeIdx = Math.min(activeIdx + 1, rows.length - 1);
            highlightRow();
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            activeIdx = Math.max(activeIdx - 1, 0);
            highlightRow();
        } else if (e.key === 'Enter') {
            e.preventDefault();
            if (activeIdx >= 0) window['acSelect'](UID, activeIdx);
        } else if (e.key === 'Escape') {
            closeDd();
        }
    });

    elText.addEventListener('focus', function() {
        if (this.value) renderDd(this.value.trim());
        else renderDd('');
    });

    document.addEventListener('click', function(e) {
        if (!elWrap.contains(e.target)) closeDd();
    });
})();
</script>
