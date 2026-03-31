{{-- Diagnosis row partial: clinics.workflow.steps._diagnosis_row
     Variables: $idx (int), $diag (array with type, code, name, description, diagnosed_by) --}}
@php $diag = $diag ?? []; @endphp

<div class="diag-row" style="background:#f6f9ff;border-radius:10px;padding:14px;margin-bottom:10px;border:1px solid {{ ($diag['type'] ?? '') === 'Primary' ? '#e74c3c33' : '#f0f2ff' }};position:relative">
    @if($idx > 0)
    <button type="button" class="btn btn-sm btn-outline-danger" style="position:absolute;top:8px;right:8px;padding:2px 8px;font-size:11px"
            onclick="this.closest('.diag-row').remove()">
        <i class="bi bi-trash"></i>
    </button>
    @endif

    <div class="row g-2">
        {{-- Type --}}
        <div class="col-6 col-sm-3">
            <div class="fld">
                <label class="flbl">
                    <span class="km">ប្រភេទ</span><span class="en">/ Type</span>
                    <span class="req">*</span>
                </label>
                <select name="diagnoses[{{ $idx }}][type]" class="form-select" required>
                    @foreach(['Primary' => '🔴 Primary', 'Secondary' => '🔵 Secondary', 'In' => 'In', 'Out' => 'Out'] as $val => $label)
                        <option value="{{ $val }}" {{ ($diag['type'] ?? '') === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- ICD-10 Code --}}
        <div class="col-6 col-sm-3">
            <div class="fld">
                <label class="flbl"><span class="en">ICD-10 Code</span></label>
                <input name="diagnoses[{{ $idx }}][code]" class="form-control"
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
                       placeholder="Acute upper respiratory infection"
                       value="{{ $diag['name'] ?? '' }}"/>
            </div>
        </div>

        {{-- Description --}}
        <div class="col-12 col-sm-8">
            <div class="fld">
                <label class="flbl"><span class="km">ការពិពណ៌នា</span><span class="en">/ Description</span></label>
                <input name="diagnoses[{{ $idx }}][description]" class="form-control"
                       placeholder="Optional clinical notes"
                       value="{{ $diag['description'] ?? '' }}"/>
            </div>
        </div>

        {{-- Diagnosed By --}}
        <div class="col-12 col-sm-4">
            <div class="fld">
                <label class="flbl"><span class="km">វិនិច្ឆ័យដោយ</span><span class="en">/ Diagnosed By</span></label>
                <input name="diagnoses[{{ $idx }}][diagnosed_by]" class="form-control"
                       value="{{ $diag['diagnosed_by'] ?? '' }}"/>
            </div>
        </div>
    </div>
</div>
