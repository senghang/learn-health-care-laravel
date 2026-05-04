@php
    $labs        = $labs        ?? collect([]);
    $hasCritical = $hasCritical ?? false;

    $nowFormatted  = df_now_input();
    $currentUser   = auth()->user()?->name ?? '';
    $interpOptions = ['', 'Normal', 'Negative', 'Positive', 'High', 'Low', 'Critical', 'Borderline'];
    $labsCount     = $labs->count();
    $totalResults  = $labs->sum(fn($l) => $l->results->count());

    $badge = $labsCount > 0
        ? $labsCount . ' Request' . ($labsCount !== 1 ? 's' : '') . ' · ' . $totalResults . ' Result' . ($totalResults !== 1 ? 's' : '')
        : null;
@endphp

<x-step.card step-id="labs" :visit="$visit" :step-idx="$stepIdx" :steps="$steps"
    icon="bi-flask2-fill" icon-color="#ff771d"
    km="មន្ទីរពិសោធន៍" en="Laboratory"
    :badge="$badge" badge-color="#ff771d">

    @if($hasCritical)
    <x-step.note type="danger">
        <strong>លទ្ធផលវិជ្ជមាន / Critical Result(s)</strong> —
        ត្រូវការការយកចិត្តទុកដាក់ជាបន្ទាន់ / Immediate attention required.
    </x-step.note>
    @endif

    {{-- ── Existing Lab Requests ──────────────────────────────────────── --}}
    @if($labs->isNotEmpty())

    <div style="font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.6px;color:#ff771d;margin-bottom:10px">
        <i class="bi bi-clock-history"></i> ការស្នើដែលបានដាក់ / Previous Requests
    </div>

    <form id="stepForm" method="POST" action="{{ url('/workflow/' . $visit->code . '/labs/save') }}">
        @csrf @method('PATCH')

        @foreach($labs as $labItem)
        @php
            $hasCrit = $labItem->results->contains(
                fn($r) => in_array(strtolower($r->interpretation ?? ''), ['positive','high','critical'])
            );
        @endphp
        <div class="sec-block mb-3"
             style="border-left:4px solid #ff771d;background:#ff771d08;border-radius:10px;padding:14px 16px">

            <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:10px">
                <code style="font-size:12px;color:#ff771d;background:#fff3e8;padding:2px 8px;border-radius:6px;border:1px solid #ffd0a8">
                    {{ $labItem->code }}
                </code>
                <span style="font-size:12px;color:#555;font-weight:600">{{ $labItem->title }}</span>
                <span style="font-size:10.5px;color:#aaa;margin-left:auto">
                    {{ df_dt($labItem->requested_at) }}
                </span>
                @if($hasCrit)
                <span style="font-size:10px;background:#fde8e8;color:#e74c3c;padding:2px 8px;border-radius:10px;font-weight:700">
                    ⚠ Critical
                </span>
                @endif
            </div>

            @if($labItem->results->isNotEmpty())
            <div class="table-responsive mb-2">
                <table class="tbl" style="font-size:12px">
                    <thead><tr>
                        <th style="width:26%">Test Name</th>
                        <th style="width:14%">Category</th>
                        <th style="width:18%">Value</th>
                        <th style="width:14%">Ref. Range</th>
                        <th style="width:16%">Interpretation</th>
                        <th style="width:12%">Verified</th>
                    </tr></thead>
                    <tbody>
                    @foreach($labItem->results as $res)
                    @php
                        $interp   = strtolower($res->interpretation ?? '');
                        $isCrit   = in_array($interp, ['positive','high','critical']);
                        $isGood   = in_array($interp, ['normal','negative']);
                    @endphp
                    <tr style="{{ $isCrit ? 'background:#fff5f5' : '' }}">
                        <td><input name="results[{{ $res->id }}][name]"
                                   class="form-control form-control-sm"
                                   value="{{ $res->name }}" style="min-width:100px"/></td>
                        <td><input name="results[{{ $res->id }}][category]"
                                   class="form-control form-control-sm"
                                   value="{{ $res->category }}" placeholder="Category"/></td>
                        <td><input name="results[{{ $res->id }}][value]"
                                   class="form-control form-control-sm {{ $isCrit ? 'border-danger' : '' }}"
                                   value="{{ $res->value }}"
                                   style="{{ $isCrit ? 'color:#e74c3c;font-weight:700' : '' }}"/></td>
                        <td><input name="results[{{ $res->id }}][reference_range]"
                                   class="form-control form-control-sm"
                                   value="{{ $res->reference_range }}" placeholder="0–5"/></td>
                        <td>
                            <select name="results[{{ $res->id }}][interpretation]"
                                    class="form-select form-select-sm interp-select"
                                    style="{{ $isCrit ? 'color:#e74c3c;font-weight:700;border-color:#e74c3c' : ($isGood ? 'color:#2eca6a' : '') }}">
                                @foreach($interpOptions as $opt)
                                <option value="{{ $opt }}" {{ $res->interpretation === $opt ? 'selected' : '' }}>
                                    {{ $opt ?: '— Select —' }}
                                </option>
                                @endforeach
                            </select>
                        </td>
                        <td style="text-align:center;font-size:11px;color:#aaa">
                            @if($res->verified_at)
                                <span style="color:#2eca6a;font-weight:700">✓ {{ df_short($res->verified_at) }}</span>
                            @else
                                <span style="color:#ddd">Pending</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            @endif

        </div>
        @endforeach

        <hr style="border-color:#e6e9f0;margin:20px 0"/>

    {{-- ── New Lab Request (inside same form) ──────────────────────────── --}}
    @else
    <form id="stepForm" method="POST" action="{{ url('/workflow/' . $visit->code . '/labs/save') }}">
        @csrf @method('PATCH')
    @endif

        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;margin-bottom:12px">
            <div style="font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.6px;color:#4154f1">
                <i class="bi bi-plus-circle-fill"></i>
                {{ $labs->isNotEmpty() ? 'ការស្នើថ្មី / New Request' : 'ការស្នើពិសោធន៍ / Lab Request' }}
            </div>
            <button type="button" class="btn btn-sm btn-outline-primary" onclick="addLabRow()">
                <i class="bi bi-plus"></i> Add Another Request
            </button>
        </div>

        <div id="newLabsContainer">
            <div class="new-lab-block" data-idx="0"
                 style="border:1.5px solid #4154f144;background:#4154f108;border-radius:10px;padding:14px 16px;margin-bottom:10px">
                <div style="font-size:11px;font-weight:700;color:#4154f1;margin-bottom:10px">
                    <i class="bi bi-plus-circle"></i> Request #1
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-6 col-sm-4">
                        <x-form.field name="new_labs[0][requested_by]"
                                      km="ស្នើដោយ" en="By" :value="$currentUser"/>
                    </div>
                    <div class="col-6 col-sm-4">
                        <x-form.field name="new_labs[0][requested_at]"
                                      km="ថ្ងៃម៉ោង" en="At"
                                      type="datetime-local" :value="$nowFormatted"/>
                    </div>
                </div>
                <div class="table-responsive mb-2">
                    <table class="tbl" style="font-size:12px">
                        <thead><tr>
                            <th style="width:28%">Test Name *</th>
                            <th style="width:16%">Category</th>
                            <th style="width:18%">Value</th>
                            <th style="width:14%">Ref. Range</th>
                            <th style="width:18%">Interpretation</th>
                            <th style="width:6%"></th>
                        </tr></thead>
                        <tbody id="results-0">
                        <tr>
                            <td><input name="new_labs[0][results][0][name]"
                                       class="form-control form-control-sm"
                                       placeholder="e.g. Malaria Blood Smear"
                                       required data-error-msg="Test Name"/></td>
                            <td><input name="new_labs[0][results][0][category]"
                                       class="form-control form-control-sm" placeholder="Parasitology"/></td>
                            <td><input name="new_labs[0][results][0][value]"
                                       class="form-control form-control-sm" placeholder="Value"/></td>
                            <td><input name="new_labs[0][results][0][reference_range]"
                                       class="form-control form-control-sm" placeholder="0–5"/></td>
                            <td>
                                <select name="new_labs[0][results][0][interpretation]"
                                        class="form-select form-select-sm interp-select">
                                    @foreach($interpOptions as $opt)
                                    <option value="{{ $opt }}">{{ $opt ?: '—' }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td><button type="button" class="btn btn-sm btn-outline-danger"
                                        onclick="removeResultRow(this)" style="padding:2px 6px">✕</button></td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                <button type="button" class="btn btn-sm btn-outline-success" onclick="addResultRow(0)">
                    <i class="bi bi-plus"></i> Add Result Row
                </button>
            </div>
        </div>

    </form>

</x-step.card>

<script>
const LAB_NOW     = '{{ $nowFormatted }}';
const LAB_USER    = '{{ addslashes($currentUser) }}';
const INTERP_OPTS = @json($interpOptions);
let labIdx = 1;

function interpSelectHtml(name) {
    return '<select name="' + name + '" class="form-select form-select-sm interp-select">'
        + INTERP_OPTS.map(o => '<option value="' + o + '">' + (o || '—') + '</option>').join('')
        + '</select>';
}

function addLabRow() {
    var idx = labIdx++;
    var div = document.createElement('div');
    div.className = 'new-lab-block';
    div.dataset.idx = idx;
    div.style.cssText = 'border:1.5px solid #4154f144;background:#4154f108;border-radius:10px;padding:14px 16px;margin-bottom:10px';
    div.innerHTML =
        '<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px">'
        + '<div style="font-size:11px;font-weight:700;color:#4154f1">Request #' + (idx+1) + '</div>'
        + '<button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest(\'.new-lab-block\').remove()" style="font-size:11px;padding:2px 8px">✕ Remove</button>'
        + '</div>'
        + '<div class="row g-3 mb-3">'
        + '<div class="col-6 col-sm-4"><div class="fld"><label class="flbl"><span class="km">ស្នើដោយ</span><span class="en"> / By</span></label>'
        + '<input name="new_labs[' + idx + '][requested_by]" class="form-control" value="' + LAB_USER + '"/></div></div>'
        + '<div class="col-6 col-sm-4"><div class="fld"><label class="flbl"><span class="km">ថ្ងៃម៉ោង</span><span class="en"> / At</span></label>'
        + '<input type="datetime-local" name="new_labs[' + idx + '][requested_at]" class="form-control" value="' + LAB_NOW + '"/></div></div>'
        + '</div>'
        + '<table class="tbl" style="font-size:12px"><thead><tr>'
        + '<th style="width:28%">Test Name *</th><th style="width:16%">Category</th>'
        + '<th style="width:18%">Value</th><th style="width:14%">Ref. Range</th>'
        + '<th style="width:18%">Interpretation</th><th style="width:6%"></th>'
        + '</tr></thead><tbody id="results-' + idx + '"><tr>'
        + '<td><input name="new_labs[' + idx + '][results][0][name]" class="form-control form-control-sm" placeholder="Test name *"/></td>'
        + '<td><input name="new_labs[' + idx + '][results][0][category]" class="form-control form-control-sm"/></td>'
        + '<td><input name="new_labs[' + idx + '][results][0][value]" class="form-control form-control-sm"/></td>'
        + '<td><input name="new_labs[' + idx + '][results][0][reference_range]" class="form-control form-control-sm" placeholder="0–5"/></td>'
        + '<td>' + interpSelectHtml('new_labs[' + idx + '][results][0][interpretation]') + '</td>'
        + '<td><button type="button" class="btn btn-sm btn-outline-danger" onclick="removeResultRow(this)" style="padding:2px 6px">✕</button></td>'
        + '</tr></tbody></table>'
        + '<button type="button" class="btn btn-sm btn-outline-success mt-2" onclick="addResultRow(' + idx + ')"><i class="bi bi-plus"></i> Add Result Row</button>';
    document.getElementById('newLabsContainer').appendChild(div);
}

function addResultRow(labIdx) {
    var tbody  = document.getElementById('results-' + labIdx);
    if (!tbody) return;
    var resIdx = tbody.querySelectorAll('tr').length;
    var tr     = document.createElement('tr');
    tr.innerHTML =
        '<td><input name="new_labs[' + labIdx + '][results][' + resIdx + '][name]" class="form-control form-control-sm" placeholder="Test name *"/></td>'
        + '<td><input name="new_labs[' + labIdx + '][results][' + resIdx + '][category]" class="form-control form-control-sm"/></td>'
        + '<td><input name="new_labs[' + labIdx + '][results][' + resIdx + '][value]" class="form-control form-control-sm"/></td>'
        + '<td><input name="new_labs[' + labIdx + '][results][' + resIdx + '][reference_range]" class="form-control form-control-sm" placeholder="0–5"/></td>'
        + '<td>' + interpSelectHtml('new_labs[' + labIdx + '][results][' + resIdx + '][interpretation]') + '</td>'
        + '<td><button type="button" class="btn btn-sm btn-outline-danger" onclick="removeResultRow(this)" style="padding:2px 6px">✕</button></td>';
    tbody.appendChild(tr);
}

function removeResultRow(btn) { btn.closest('tr').remove(); }

document.addEventListener('change', function(e) {
    if (e.target.classList.contains('interp-select')) highlightInterp(e.target);
});

function highlightInterp(sel) {
    var v = (sel.value || '').toLowerCase();
    var crit = ['positive','high','critical'], good = ['normal','negative'];
    sel.style.color       = crit.includes(v) ? '#e74c3c' : good.includes(v) ? '#2eca6a' : '';
    sel.style.fontWeight  = crit.includes(v) ? '700' : '';
    sel.style.borderColor = crit.includes(v) ? '#e74c3c' : '';
}
document.querySelectorAll('.interp-select').forEach(highlightInterp);
</script>
