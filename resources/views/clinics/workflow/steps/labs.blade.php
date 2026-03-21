@php
    $nowFormatted  = now()->format('Y-m-d\TH:i');
    $currentUser   = auth()->user()?->name ?? '';
    $saveRoute     = route('workflow.step.save', [$visit->code, $currentStep ?? 'labs']);
    $interpOptions = ['', 'Normal', 'Negative', 'Positive', 'High', 'Low', 'Critical', 'Borderline'];
@endphp

<div class="card-hd" style="flex-wrap:wrap;gap:8px;padding:14px 18px 10px">
    <div style="flex:1;min-width:0">
        <div class="card-hd-title">
            <i class="bi bi-flask2-fill" style="color:#ff771d"></i>
            មន្ទីរពិសោធន៍
            <small style="font-size:11px;color:#bbb;font-weight:400">/ Laboratory</small>
        </div>
        <div style="font-size:10.5px;color:#aaa;margin-top:3px">
            ជំហាន {{ $stepIdx+1 }} នៃ {{ count($steps) }} / Step {{ $stepIdx+1 }} of {{ count($steps) }}
        </div>
    </div>
    <div style="display:flex;gap:8px;align-items:center;flex-shrink:0">
        @if($labs->isNotEmpty())
            <span
                style="font-size:11px;background:#fff3e8;color:#ff771d;padding:3px 10px;border-radius:20px;border:1px solid #ffd0a8;font-weight:700">
            {{ $labs->count() }} Request{{ $labs->count() !== 1 ? 's' : '' }}
            · {{ $labs->sum(fn($l) => $l->results->count()) }} Result{{ $labs->sum(fn($l) => $l->results->count()) !== 1 ? 's' : '' }}
        </span>
        @endif
        @if($hasCritical)
            <span
                style="font-size:11px;background:#fde8e8;color:#e74c3c;padding:3px 10px;border-radius:20px;border:1px solid #f5c0c0;font-weight:700">
            <i class="bi bi-exclamation-triangle-fill"></i> Critical
        </span>
        @endif
    </div>
</div>

<div style="height:3px;background:#f0f2ff">
    <div
        style="height:100%;width:{{ round($stepIdx / count($steps) * 100) }}%;background:linear-gradient(90deg,#4154f1,#717ff5)"></div>
</div>

<div class="card-bd">

    @if($hasCritical)
        <div class="note note-danger mb-3">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <div><strong>លទ្ធផលវិជ្ជមាន / Critical Result(s)</strong> — ត្រូវការការយកចិត្តទុកដាក់ជាបន្ទាន់ / Immediate
                attention required.
            </div>
        </div>
    @endif

    {{-- ══ SINGLE FORM — handles both existing edits and new requests ══ --}}
    <form id="stepForm" method="POST" action="{{ $saveRoute }}">
        @csrf
        @method('PATCH')

        {{-- ── Existing Labs ──────────────────────────────────────────── --}}
        @if($labs->isNotEmpty())
            <div
                style="font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.6px;color:#ff771d;margin-bottom:10px">
                <i class="bi bi-clock-history"></i> ការស្នើដែលបានដាក់ / Previous Requests
            </div>

            @foreach($labs as $lab)
                @php
                    $hasCrit = $lab->results->contains(
                        fn($r) => in_array(strtolower($r->interpretation ?? ''), ['positive','high','critical'])
                    );
                @endphp
                <div class="sec-block mb-3"
                     style="border-color:#ff771d;background:#ff771d08;border-radius:10px;padding:14px 16px">

                    <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:10px">
                        <code
                            style="font-size:12px;color:#ff771d;background:#fff3e8;padding:2px 8px;border-radius:6px;border:1px solid #ffd0a8">{{ $lab->code }}</code>
                        <span style="font-size:12px;color:#555;font-weight:600">{{ $lab->title }}</span>
                        <span style="font-size:10.5px;color:#aaa;margin-left:auto">
                    {{ $lab->requested_at?->format('d/m/Y H:i') ?? '—' }}
                            @if($lab->requested_by)
                                · {{ $lab->requested_by }}
                            @endif
                </span>
                        @if($hasCrit)
                            <span
                                style="font-size:10px;background:#fde8e8;color:#e74c3c;padding:2px 8px;border-radius:10px;font-weight:700">⚠ Critical</span>
                        @endif
                    </div>

                    @if($lab->results->isNotEmpty())
                        <div class="table-responsive" style="margin-bottom:10px">
                            <table class="tbl" style="font-size:12px">
                                <thead>
                                <tr>
                                    <th style="width:26%">Test Name</th>
                                    <th style="width:14%">Category</th>
                                    <th style="width:18%">Value</th>
                                    <th style="width:14%">Ref. Range</th>
                                    <th style="width:16%">Interpretation</th>
                                    <th style="width:12%">Verified</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($lab->results as $res)
                                    @php
                                        $interp  = strtolower($res->interpretation ?? '');
                                        $isCrit  = in_array($interp, ['positive','high','critical']);
                                        $isGood  = in_array($interp, ['normal','negative']);
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
                                                    <option
                                                        value="{{ $opt }}" {{ $res->interpretation === $opt ? 'selected' : '' }}>
                                                        {{ $opt ?: '— Select —' }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td style="text-align:center;font-size:11px;color:#aaa">
                                            @if($res->verified_at)
                                                <span style="color:#2eca6a;font-weight:700"
                                                      title="{{ $res->verified_by }} · {{ $res->verified_at->format('d/m H:i') }}">✓ {{ $res->verified_at->format('d/m') }}</span>
                                            @else
                                                <span style="color:#ddd">Pending</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div style="font-size:12px;color:#bbb;padding:4px 0 10px">
                            <i class="bi bi-hourglass-split"></i> Pending — no results entered yet
                        </div>
                    @endif

                    {{-- Add result row to this existing lab --}}
                    <div id="addrow-{{ $loop->index }}"
                         style="display:none;background:#f6f9ff;border-radius:8px;padding:10px 12px;margin-bottom:8px">
                        <div style="font-size:11px;font-weight:700;color:#4154f1;margin-bottom:8px">
                            <i class="bi bi-plus-circle"></i> Add Result to {{ $lab->code }}
                        </div>
                        <table class="tbl" style="font-size:12px;margin-bottom:8px">
                            <thead>
                            <tr>
                                <th style="width:28%">Test Name *</th>
                                <th style="width:16%">Category</th>
                                <th style="width:18%">Value</th>
                                <th style="width:16%">Ref. Range</th>
                                <th style="width:18%">Interpretation</th>
                                <th style="width:4%"></th>
                            </tr>
                            </thead>
                            <tbody id="existing-results-{{ $loop->index }}">
                            <tr>
                                <td><input name="new_labs[ex{{ $loop->index }}][results][0][name]"
                                           class="form-control form-control-sm" placeholder="Test name *"/></td>
                                <td><input name="new_labs[ex{{ $loop->index }}][results][0][category]"
                                           class="form-control form-control-sm" placeholder="Category"/></td>
                                <td><input name="new_labs[ex{{ $loop->index }}][results][0][value]"
                                           class="form-control form-control-sm" placeholder="Value"/></td>
                                <td><input name="new_labs[ex{{ $loop->index }}][results][0][reference_range]"
                                           class="form-control form-control-sm" placeholder="0–5"/></td>
                                <td>
                                    <select name="new_labs[ex{{ $loop->index }}][results][0][interpretation]"
                                            class="form-select form-select-sm interp-select">
                                        @foreach($interpOptions as $opt)
                                            <option value="{{ $opt }}">{{ $opt ?: '—' }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td></td>
                            </tr>
                            </tbody>
                        </table>
                        {{-- Carry the parent lab's metadata so save() knows which request these belong to --}}
                        <input type="hidden" name="new_labs[ex{{ $loop->index }}][requested_by]"
                               value="{{ $lab->requested_by }}"/>
                        <input type="hidden" name="new_labs[ex{{ $loop->index }}][requested_at]"
                               value="{{ $lab->requested_at?->format('Y-m-d\TH:i') }}"/>
                        <div style="display:flex;gap:6px">
                            <button type="button" class="btn btn-sm btn-outline-success"
                                    onclick="addExistingResultRow({{ $loop->index }})">
                                <i class="bi bi-plus"></i> More rows
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary"
                                    onclick="document.getElementById('addrow-{{ $loop->index }}').style.display='none'">
                                Cancel
                            </button>
                        </div>
                    </div>

                    <button type="button" class="btn btn-sm btn-outline-primary"
                            onclick="document.getElementById('addrow-{{ $loop->index }}').style.display='block'">
                        <i class="bi bi-plus"></i> Add Result
                    </button>

                </div>
            @endforeach

            <hr style="border-color:#f0f2ff;margin:20px 0"/>
        @endif

        {{-- ── New Lab Requests ───────────────────────────────────────── --}}
        <div
            style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;margin-bottom:12px">
            <div style="font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.6px;color:#4154f1">
                <i class="bi bi-plus-circle-fill"></i>
                {{ $labs->isNotEmpty() ? 'ការស្នើថ្មី / New Request' : 'ការស្នើពិសោធន៍ / Lab Request' }}
            </div>
            <button type="button" class="btn btn-sm btn-outline-primary" onclick="addLabRow()">
                <i class="bi bi-plus"></i> Add Another Request
            </button>
        </div>

        <div id="newLabsContainer">
            {{-- Static first block (idx=0) --}}
            <div class="new-lab-block" data-idx="0"
                 style="border:1.5px solid #4154f144;background:#4154f108;border-radius:10px;padding:14px 16px;margin-bottom:10px">
                <div style="font-size:11px;font-weight:700;color:#4154f1;margin-bottom:10px">
                    <i class="bi bi-plus-circle"></i> Request #1
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-6 col-sm-4">
                        <div class="fld">
                            <label class="flbl"><span class="km">ស្នើដោយ</span><span class="en">/ By</span></label>
                            <input name="new_labs[0][requested_by]" class="form-control"
                                   value="{{ $currentUser }}" placeholder="Name"/>
                        </div>
                    </div>
                    <div class="col-6 col-sm-4">
                        <div class="fld">
                            <label class="flbl"><span class="km">ថ្ងៃម៉ោង</span><span class="en">/ At</span></label>
                            <input type="datetime-local" name="new_labs[0][requested_at]"
                                   class="form-control" value="{{ $nowFormatted }}"/>
                        </div>
                    </div>
                </div>
                <div
                    style="font-size:10.5px;font-weight:700;color:#aaa;text-transform:uppercase;letter-spacing:.5px;margin-bottom:8px">
                    Results
                </div>
                <div class="table-responsive" style="margin-bottom:8px">
                    <table class="tbl" style="font-size:12px">
                        <thead>
                        <tr>
                            <th style="width:28%">Test Name *</th>
                            <th style="width:16%">Category</th>
                            <th style="width:18%">Value</th>
                            <th style="width:14%">Ref. Range</th>
                            <th style="width:18%">Interpretation</th>
                            <th style="width:6%"></th>
                        </tr>
                        </thead>
                        <tbody id="results-0">
                        <tr>
                            <td><input name="new_labs[0][results][0][name]" class="form-control form-control-sm"
                                       placeholder="e.g. Malaria Blood Smear"/></td>
                            <td><input name="new_labs[0][results][0][category]" class="form-control form-control-sm"
                                       placeholder="Parasitology"/></td>
                            <td><input name="new_labs[0][results][0][value]" class="form-control form-control-sm"
                                       placeholder="Value"/></td>
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
                            <td>
                                <button type="button" class="btn btn-sm btn-outline-danger"
                                        onclick="removeResultRow(this)" style="padding:2px 6px">✕
                                </button>
                            </td>
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

</div>

<script>
    /* ── Config baked in from PHP (no Blade inside JS template literals) ── */
    const LAB_NOW = '{{ $nowFormatted }}';
    const LAB_USER = '{{ addslashes($currentUser) }}';
    const INTERP_OPTS = @json($interpOptions);

    let labIdx = 1; // next new-block index

    /* ── New lab request block ─────────────────────────────────────────── */
    function addLabRow() {
        const idx = labIdx++;
        const container = document.getElementById('newLabsContainer');

        const block = document.createElement('div');
        block.className = 'new-lab-block';
        block.dataset.idx = idx;
        block.style.cssText = 'border:1.5px solid #4154f144;background:#4154f108;border-radius:10px;padding:14px 16px;margin-bottom:10px';
        block.innerHTML = buildLabBlockHtml(idx);
        container.appendChild(block);
        block.scrollIntoView({behavior: 'smooth', block: 'nearest'});
    }

    function removeLabRow(idx) {
        const el = document.querySelector(`.new-lab-block[data-idx="${idx}"]`);
        if (el) el.remove();
    }

    /* ── Result rows for new lab blocks ────────────────────────────────── */
    function addResultRow(labIdx) {
        const tbody = document.getElementById('results-' + labIdx);
        if (!tbody) return;
        const resIdx = tbody.querySelectorAll('tr').length;
        const tr = document.createElement('tr');
        tr.innerHTML = buildResultRowHtml(labIdx, resIdx);
        tbody.appendChild(tr);
    }

    function removeResultRow(btn) {
        btn.closest('tr').remove();
    }

    /* ── Result rows for existing lab "Add Result" panels ──────────────── */
    function addExistingResultRow(labLoopIdx) {
        const tbody = document.getElementById('existing-results-' + labLoopIdx);
        if (!tbody) return;
        const resIdx = tbody.querySelectorAll('tr').length;
        const tr = document.createElement('tr');
        tr.innerHTML = buildExistingResultRowHtml(labLoopIdx, resIdx);
        tbody.appendChild(tr);
    }

    /* ── Interpretation select highlight ───────────────────────────────── */
    function highlightInterp(sel) {
        const crit = ['positive', 'high', 'critical'];
        const good = ['normal', 'negative'];
        const v = (sel.value || '').toLowerCase();
        sel.style.color = crit.includes(v) ? '#e74c3c' : good.includes(v) ? '#2eca6a' : '';
        sel.style.fontWeight = crit.includes(v) ? '700' : '';
        sel.style.borderColor = crit.includes(v) ? '#e74c3c' : '';
    }

    /* Delegate change events so dynamically added selects also work */
    document.addEventListener('change', function (e) {
        if (e.target.classList.contains('interp-select')) highlightInterp(e.target);
    });

    /* Run on existing selects at load */
    document.querySelectorAll('.interp-select').forEach(highlightInterp);

    /* ── HTML builders (pure JS — no Blade syntax) ─────────────────────── */
    function interpSelectHtml(name) {
        const opts = INTERP_OPTS.map(o => `<option value="${o}">${o || '— Select —'}</option>`).join('');
        return `<select name="${name}" class="form-select form-select-sm interp-select">${opts}</select>`;
    }

    function buildResultRowHtml(labIdx, resIdx) {
        return `
        <td><input name="new_labs[${labIdx}][results][${resIdx}][name]"
                   class="form-control form-control-sm" placeholder="Test name *"/></td>
        <td><input name="new_labs[${labIdx}][results][${resIdx}][category]"
                   class="form-control form-control-sm" placeholder="Category"/></td>
        <td><input name="new_labs[${labIdx}][results][${resIdx}][value]"
                   class="form-control form-control-sm" placeholder="Value"/></td>
        <td><input name="new_labs[${labIdx}][results][${resIdx}][reference_range]"
                   class="form-control form-control-sm" placeholder="0–5"/></td>
        <td>${interpSelectHtml('new_labs[' + labIdx + '][results][' + resIdx + '][interpretation]')}</td>
        <td><button type="button" class="btn btn-sm btn-outline-danger"
                    onclick="removeResultRow(this)" style="padding:2px 6px">✕</button></td>`;
    }

    function buildExistingResultRowHtml(loopIdx, resIdx) {
        return `
        <td><input name="new_labs[ex${loopIdx}][results][${resIdx}][name]"
                   class="form-control form-control-sm" placeholder="Test name *"/></td>
        <td><input name="new_labs[ex${loopIdx}][results][${resIdx}][category]"
                   class="form-control form-control-sm" placeholder="Category"/></td>
        <td><input name="new_labs[ex${loopIdx}][results][${resIdx}][value]"
                   class="form-control form-control-sm" placeholder="Value"/></td>
        <td><input name="new_labs[ex${loopIdx}][results][${resIdx}][reference_range]"
                   class="form-control form-control-sm" placeholder="0–5"/></td>
        <td>${interpSelectHtml('new_labs[ex' + loopIdx + '][results][' + resIdx + '][interpretation]')}</td>
        <td><button type="button" class="btn btn-sm btn-outline-danger"
                    onclick="removeResultRow(this)" style="padding:2px 6px">✕</button></td>`;
    }

    function buildLabBlockHtml(idx) {
        return `
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px">
            <div style="font-size:11px;font-weight:700;color:#4154f1">
                <i class="bi bi-plus-circle"></i> Request #${idx + 1}
            </div>
            <button type="button" class="btn btn-sm btn-outline-danger"
                    onclick="removeLabRow(${idx})" style="font-size:11px;padding:2px 8px">✕ Remove</button>
        </div>
        <div class="row g-3 mb-3">
            <div class="col-6 col-sm-4">
                <div class="fld">
                    <label class="flbl"><span>ស្នើដោយ</span><span style="color:#aaa;font-size:10px"> / By</span></label>
                    <input name="new_labs[${idx}][requested_by]" class="form-control"
                           value="${LAB_USER}" placeholder="Name"/>
                </div>
            </div>
            <div class="col-6 col-sm-4">
                <div class="fld">
                    <label class="flbl"><span>ថ្ងៃម៉ោង</span><span style="color:#aaa;font-size:10px"> / At</span></label>
                    <input type="datetime-local" name="new_labs[${idx}][requested_at]"
                           class="form-control" value="${LAB_NOW}"/>
                </div>
            </div>
        </div>
        <div style="font-size:10.5px;font-weight:700;color:#aaa;text-transform:uppercase;letter-spacing:.5px;margin-bottom:8px">Results</div>
        <div class="table-responsive" style="margin-bottom:8px">
            <table class="tbl" style="font-size:12px">
                <thead><tr>
                    <th style="width:28%">Test Name *</th>
                    <th style="width:16%">Category</th>
                    <th style="width:18%">Value</th>
                    <th style="width:14%">Ref. Range</th>
                    <th style="width:18%">Interpretation</th>
                    <th style="width:6%"></th>
                </tr></thead>
                <tbody id="results-${idx}">
                    <tr>${buildResultRowHtml(idx, 0)}</tr>
                </tbody>
            </table>
        </div>
        <button type="button" class="btn btn-sm btn-outline-success"
                onclick="addResultRow(${idx})">
            <i class="bi bi-plus"></i> Add Result Row
        </button>`;
    }
</script>
