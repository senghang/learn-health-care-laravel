{{-- Diagnosis row partial: clinics.workflow.steps._diagnosis_row
     Variables: $idx (int), $diag (array with type, code, name, description, diagnosed_by) --}}
@php
    $diag = $diag ?? [];
    $type = $diag['type'] ?? 'Primary';
    $typeCfg = [
        'Primary'   => ['color' => '#e74c3c', 'bg' => '#fde8e8', 'border' => '#e74c3c44'],
        'Secondary' => ['color' => '#4154f1', 'bg' => '#eef0fd', 'border' => '#4154f144'],
        'In'        => ['color' => '#2eca6a', 'bg' => '#e8f8ef', 'border' => '#2eca6a44'],
        'Out'       => ['color' => '#64748b', 'bg' => '#f1f5f9', 'border' => '#64748b44'],
    ];
    $tc = $typeCfg[$type] ?? $typeCfg['Secondary'];
@endphp

<div class="diag-row"
     style="background:{{ $tc['bg'] }};border-radius:10px;padding:14px;margin-bottom:10px;
            border:1.5px solid {{ $tc['border'] }};position:relative;
            transition:border-color .2s,background .2s">

    {{-- Row header --}}
    <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px">
        <span class="diag-type-badge"
              style="font-size:10px;font-weight:700;padding:2px 8px;border-radius:6px;
                     background:{{ $tc['bg'] }};color:{{ $tc['color'] }};border:1px solid {{ $tc['border'] }}">
            {{ $type === 'Primary' ? '🔴' : ($type === 'Secondary' ? '🔵' : ($type === 'In' ? '🟢' : '⚪')) }}
            {{ $type }}
        </span>
        @if($idx === 0)
        <span style="font-size:9.5px;background:#012970;color:#fff;padding:1px 7px;border-radius:5px;font-weight:700">
            #1
        </span>
        @else
        <span style="font-size:10px;color:#94a3b8;margin-left:2px">#{{ $idx + 1 }}</span>
        @endif
        @if(!empty($diag['name']))
        <span style="font-size:11px;color:#64748b;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:200px">
            {{ $diag['name'] }}
        </span>
        @endif
        @if($idx > 0)
        <button type="button" class="btn btn-sm btn-outline-danger"
                style="margin-left:auto;padding:2px 8px;font-size:11px;flex-shrink:0"
                onclick="this.closest('.diag-row').remove();if(window.checkPrimary)checkPrimary();if(window.updateDiagCount)updateDiagCount()">
            <i class="bi bi-trash"></i>
        </button>
        @else
        <div style="margin-left:auto"></div>
        @endif
    </div>

    <div class="row g-2">
        {{-- Type --}}
        <div class="col-6 col-sm-3">
            <div class="fld">
                <label class="flbl">
                    <span class="km">ប្រភេទ</span><span class="en">/ Type</span>
                    <span class="req">*</span>
                </label>
                <select name="diagnoses[{{ $idx }}][type]" class="form-select" required
                        onchange="if(window.applyDiagRowStyle)applyDiagRowStyle(this.closest('.diag-row'));if(window.checkPrimary)checkPrimary()">
                    @foreach(['Primary' => '🔴 Primary', 'Secondary' => '🔵 Secondary', 'In' => '🟢 In', 'Out' => '⚪ Out'] as $val => $label)
                        <option value="{{ $val }}" {{ $type === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- ICD-10 Code --}}
        <div class="col-6 col-sm-3">
            <div class="fld">
                <label class="flbl">
                    <span class="en">ICD-10 Code</span>
                    <span style="font-size:9px;color:#cbd5e1;font-weight:400"> (optional)</span>
                </label>
                <input name="diagnoses[{{ $idx }}][code]" class="form-control"
                       style="font-family:monospace;font-size:13px"
                       placeholder="e.g. J06.9"
                       value="{{ $diag['code'] ?? '' }}"/>
            </div>
        </div>

        {{-- Diagnosis Name --}}
        <div class="col-12 col-sm-6">
            <div class="fld">
                <label class="flbl">
                    <span class="km">ឈ្មោះរោគ</span><span class="en">/ Diagnosis Name</span>
                    <span class="req">*</span>
                </label>
                <input name="diagnoses[{{ $idx }}][name]" class="form-control" required
                       placeholder="e.g. Acute upper respiratory infection"
                       value="{{ $diag['name'] ?? '' }}"/>
            </div>
        </div>

        {{-- Description --}}
        <div class="col-12 col-sm-8">
            <div class="fld">
                <label class="flbl">
                    <span class="km">ការពិពណ៌នា</span><span class="en">/ Description</span>
                    <span style="font-size:9px;color:#cbd5e1;font-weight:400"> (optional)</span>
                </label>
                <input name="diagnoses[{{ $idx }}][description]" class="form-control"
                       placeholder="Clinical notes, laterality, severity…"
                       value="{{ $diag['description'] ?? '' }}"/>
            </div>
        </div>

        {{-- Diagnosed By --}}
        <div class="col-12 col-sm-4">
            <div class="fld">
                <label class="flbl"><span class="km">វិនិច្ឆ័យដោយ</span><span class="en">/ Diagnosed By</span></label>
                <input name="diagnoses[{{ $idx }}][diagnosed_by]" class="form-control"
                       value="{{ $diag['diagnosed_by'] ?? auth()->user()?->name ?? '' }}"/>
            </div>
        </div>
    </div>
</div>
