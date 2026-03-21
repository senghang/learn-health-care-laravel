{{--
    Step: Diagnosis (រោគវិនិច្ឆ័យ)
    Variables: $diagnoses, $visit, $stepIdx, $steps, $currentStep
--}}
@php
    $nowFormatted = now()->format('Y-m-d\TH:i');
    $currentUser  = auth()->user()?->name ?? '';
    $typeOptions  = ['Primary','Secondary','In','Out'];
@endphp

<div class="card-hd" style="flex-wrap:wrap;gap:8px;padding:14px 18px 10px">
    <div style="flex:1;min-width:0">
        <div class="card-hd-title">
            <i class="bi bi-bullseye" style="color:#e74c3c"></i>រោគវិនិច្ឆ័យ
            <small style="font-size:11px;color:#bbb;font-weight:400">/ Diagnosis</small>
        </div>
        <div style="font-size:10.5px;color:#aaa;margin-top:3px">
            ជំហាន {{ $stepIdx+1 }} នៃ {{ count($steps) }} / Step {{ $stepIdx+1 }} of {{ count($steps) }} — ICD-10
        </div>
    </div>
    @if($diagnoses->isNotEmpty())
        <span
            style="font-size:11px;background:#fde8e8;color:#e74c3c;padding:3px 10px;border-radius:20px;border:1px solid #f5c0c0;font-weight:700;flex-shrink:0">
        {{ $diagnoses->count() }} Dx saved
    </span>
    @endif
</div>
<div style="height:3px;background:#f0f2ff">
    <div
        style="height:100%;width:{{ round($stepIdx/count($steps)*100) }}%;background:linear-gradient(90deg,#4154f1,#717ff5)"></div>
</div>

<div class="card-bd">
    <form id="stepForm" method="POST"
          action="{{ route('workflow.step.save', [$visit->code, $currentStep ?? 'diagnosis']) }}">
        @csrf @method('PATCH')

        <div class="d-flex justify-content-end mb-3">
            <button type="button" class="btn btn-sm btn-outline-primary" onclick="addDxRow()">
                <i class="bi bi-plus"></i> Dx បន្ថែម / Add Diagnosis
            </button>
        </div>

        <div id="diagnosisList">

            @if($diagnoses->isNotEmpty())
                {{-- ── Saved diagnoses from DB ──────────────────────────── --}}
                @foreach($diagnoses as $i => $dx)
                    <div class="sec-block dx-block" data-idx="{{ $i }}"
                         style="border-color:#e74c3c;background:#fde8e808;border-radius:10px;padding:14px 16px;margin-bottom:10px">

                        <div style="display:flex;align-items:center;gap:8px;margin-bottom:12px">
                            <span
                                style="width:9px;height:9px;border-radius:50%;background:#e74c3c;display:inline-block;flex-shrink:0"></span>
                            <strong style="color:#c0392b;font-size:13px">
                                @switch($dx->diagnosis_type)
                                    @case('Primary')   ចម្បង / Primary @break
                                    @case('Secondary') ទ្វីបដ្ឋ / Secondary @break
                                    @default           {{ $dx->diagnosis_type }}
                                @endswitch
                                — Dx #{{ $i + 1 }}
                            </strong>
                            @if($dx->diagnosis_code)
                                <code
                                    style="background:#e74c3c;color:#fff;padding:2px 9px;border-radius:20px;font-size:11px">
                                    {{ $dx->diagnosis_code }}
                                </code>
                            @endif
                            <button type="button" class="btn btn-sm btn-outline-danger ms-auto"
                                    onclick="removeDxRow({{ $i }})" style="font-size:11px;padding:2px 8px">
                                ✕ លុប
                            </button>
                        </div>

                        <div class="row g-3">
                            <div class="col-6 col-sm-3">
                                <div class="fld">
                                    <label class="flbl"><span class="km">ប្រភេទ</span><span
                                            class="en">/ Type</span><span class="req">*</span></label>
                                    <select name="diagnoses[{{ $i }}][type]" class="form-select">
                                        @foreach($typeOptions as $t)
                                            <option
                                                value="{{ $t }}" {{ $dx->diagnosis_type === $t ? 'selected' : '' }}>{{ $t }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-6 col-sm-2">
                                <div class="fld">
                                    <label class="flbl"><span class="en">ICD-10</span></label>
                                    <input name="diagnoses[{{ $i }}][code]" class="form-control"
                                           value="{{ $dx->diagnosis_code }}" placeholder="B50.0"/>
                                </div>
                            </div>
                            <div class="col-12 col-sm-7">
                                <div class="fld">
                                    <label class="flbl"><span class="km">ឈ្មោះ</span><span class="en">/ Name</span><span
                                            class="req">*</span></label>
                                    <input name="diagnoses[{{ $i }}][name]" class="form-control"
                                           value="{{ $dx->diagnosis_name }}"
                                           placeholder="Plasmodium falciparum malaria"/>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="fld">
                                    <label class="flbl"><span class="km">ពេលវេលា</span><span
                                            class="en">/ Diagnosed At</span></label>
                                    <input type="datetime-local" name="diagnoses[{{ $i }}][diagnosed_at]"
                                           class="form-control"
                                           value="{{ $dx->diagnosed_at?->format('Y-m-d\TH:i') ?? $nowFormatted }}"/>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="fld">
                                    <label class="flbl"><span class="km">ដោយ</span><span class="en">/ By</span></label>
                                    <input name="diagnoses[{{ $i }}][diagnosed_by]" class="form-control"
                                           value="{{ $dx->diagnosed_by ?? $currentUser }}"/>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="fld">
                                    <label class="flbl"><span class="km">ការពណ៌នា</span><span
                                            class="en">/ Description</span></label>
                                    <input name="diagnoses[{{ $i }}][description]" class="form-control"
                                           value="{{ $dx->diagnosis_description }}"/>
                                </div>
                            </div>
                        </div>

                    </div>
                @endforeach

            @else
                {{-- ── Default empty first row ──────────────────────────── --}}
                <div class="sec-block dx-block" data-idx="0"
                     style="border-color:#e74c3c;background:#fde8e808;border-radius:10px;padding:14px 16px;margin-bottom:10px">

                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:12px">
                        <span
                            style="width:9px;height:9px;border-radius:50%;background:#e74c3c;display:inline-block;flex-shrink:0"></span>
                        <strong style="color:#c0392b;font-size:13px">ចម្បង / Primary — Dx #1</strong>
                    </div>

                    <div class="row g-3">
                        <div class="col-6 col-sm-3">
                            <div class="fld">
                                <label class="flbl"><span class="km">ប្រភេទ</span><span class="en">/ Type</span><span
                                        class="req">*</span></label>
                                <select name="diagnoses[0][type]" class="form-select">
                                    @foreach($typeOptions as $t)
                                        <option
                                            value="{{ $t }}" {{ $t === 'Primary' ? 'selected' : '' }}>{{ $t }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-6 col-sm-2">
                            <div class="fld">
                                <label class="flbl"><span class="en">ICD-10</span></label>
                                <input name="diagnoses[0][code]" class="form-control" placeholder="B50.0"/>
                            </div>
                        </div>
                        <div class="col-12 col-sm-7">
                            <div class="fld">
                                <label class="flbl"><span class="km">ឈ្មោះ</span><span class="en">/ Name</span><span
                                        class="req">*</span></label>
                                <input name="diagnoses[0][name]" class="form-control"
                                       placeholder="Plasmodium falciparum malaria"/>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="fld">
                                <label class="flbl"><span class="km">ពេលវេលា</span><span
                                        class="en">/ Diagnosed At</span></label>
                                <input type="datetime-local" name="diagnoses[0][diagnosed_at]"
                                       class="form-control" value="{{ $nowFormatted }}"/>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="fld">
                                <label class="flbl"><span class="km">ដោយ</span><span class="en">/ By</span></label>
                                <input name="diagnoses[0][diagnosed_by]" class="form-control"
                                       value="{{ $currentUser }}"/>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="fld">
                                <label class="flbl"><span class="km">ការពណ៌នា</span><span
                                        class="en">/ Description</span></label>
                                <input name="diagnoses[0][description]" class="form-control"/>
                            </div>
                        </div>
                    </div>

                </div>
            @endif

        </div>{{-- #diagnosisList --}}

    </form>
</div>

<script>
    const DX_NOW = '{{ $nowFormatted }}';
    const DX_USER = '{{ addslashes($currentUser) }}';
    const DX_TYPES = @json($typeOptions);
    let dxIdx = {{ max($diagnoses->count(), 1) }};

    function addDxRow() {
        const idx = dxIdx++;
        const list = document.getElementById('diagnosisList');

        const typeOpts = DX_TYPES.map(t =>
            `<option value="${t}" ${t === 'Primary' ? 'selected' : ''}>${t}</option>`
        ).join('');

        const block = document.createElement('div');
        block.className = 'sec-block dx-block';
        block.dataset.idx = idx;
        block.style.cssText = 'border:1.5px solid #e74c3c44;background:#fde8e808;border-radius:10px;padding:14px 16px;margin-bottom:10px';
        block.innerHTML = `
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:12px">
            <span style="width:9px;height:9px;border-radius:50%;background:#e74c3c;display:inline-block;flex-shrink:0"></span>
            <strong style="color:#c0392b;font-size:13px">Dx #${idx + 1}</strong>
            <button type="button" class="btn btn-sm btn-outline-danger ms-auto"
                    onclick="removeDxRow(${idx})" style="font-size:11px;padding:2px 8px">✕ លុប</button>
        </div>
        <div class="row g-3">
            <div class="col-6 col-sm-3">
                <div class="fld">
                    <label class="flbl"><span>ប្រភេទ</span><span style="color:#aaa;font-size:10px"> / Type</span><span class="req">*</span></label>
                    <select name="diagnoses[${idx}][type]" class="form-select">${typeOpts}</select>
                </div>
            </div>
            <div class="col-6 col-sm-2">
                <div class="fld">
                    <label class="flbl"><span>ICD-10</span></label>
                    <input name="diagnoses[${idx}][code]" class="form-control" placeholder="B50.0"/>
                </div>
            </div>
            <div class="col-12 col-sm-7">
                <div class="fld">
                    <label class="flbl"><span>ឈ្មោះ</span><span style="color:#aaa;font-size:10px"> / Name</span><span class="req">*</span></label>
                    <input name="diagnoses[${idx}][name]" class="form-control" placeholder="Diagnosis name"/>
                </div>
            </div>
            <div class="col-6">
                <div class="fld">
                    <label class="flbl"><span>ពេលវេលា</span><span style="color:#aaa;font-size:10px"> / At</span></label>
                    <input type="datetime-local" name="diagnoses[${idx}][diagnosed_at]"
                           class="form-control" value="${DX_NOW}"/>
                </div>
            </div>
            <div class="col-6">
                <div class="fld">
                    <label class="flbl"><span>ដោយ</span><span style="color:#aaa;font-size:10px"> / By</span></label>
                    <input name="diagnoses[${idx}][diagnosed_by]" class="form-control" value="${DX_USER}"/>
                </div>
            </div>
            <div class="col-12">
                <div class="fld">
                    <label class="flbl"><span>ការពណ៌នា</span><span style="color:#aaa;font-size:10px"> / Description</span></label>
                    <input name="diagnoses[${idx}][description]" class="form-control"/>
                </div>
            </div>
        </div>`;

        list.appendChild(block);
        block.scrollIntoView({behavior: 'smooth', block: 'nearest'});
    }

    function removeDxRow(idx) {
        const el = document.querySelector(`.dx-block[data-idx="${idx}"]`);
        if (el) el.remove();
    }
</script>
