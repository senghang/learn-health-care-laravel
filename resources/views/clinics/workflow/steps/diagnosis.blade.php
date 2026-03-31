{{--
    Step: Diagnosis (រោគវិនិច្ឆ័យ)
    Variables from DiagnosisStep::viewData():
      $diagnoses — Collection<DiagnosisModel>
      $visit, $stepIdx, $steps
--}}
@php
    $existingDiags = old('diagnoses', $diagnoses->map(fn($d) => [
        'type'        => $d->diagnosis_type,
        'code'        => $d->diagnosis_code,
        'name'        => $d->diagnosis_name,
        'description' => $d->diagnosis_description,
        'diagnosed_by'=> $d->diagnosed_by,
    ])->toArray());
@endphp

<div class="card-hd" style="flex-wrap:wrap;gap:8px;padding:14px 18px 10px">
    <div style="flex:1;min-width:0">
        <div class="card-hd-title">
            <i class="bi bi-bullseye" style="color:#e74c3c"></i>រោគវិនិច្ឆ័យ
            <small style="font-size:11px;color:#bbb;font-weight:400">/ Diagnosis</small>
        </div>
        <div style="font-size:10.5px;color:#aaa;margin-top:3px">
            ជំហាន {{ $stepIdx+1 }} នៃ {{ count($steps) }} / Step {{ $stepIdx+1 }} of {{ count($steps) }}
        </div>
    </div>
    @if($diagnoses->isNotEmpty())
    <span style="font-size:11px;background:#fce4ec;color:#e74c3c;padding:3px 10px;border-radius:20px;border:1px solid #f8bbd0;font-weight:700;flex-shrink:0">
        {{ $diagnoses->count() }} diagnos{{ $diagnoses->count() > 1 ? 'es' : 'is' }}
    </span>
    @endif
</div>

<div style="height:3px;background:#f0f2ff">
    <div style="height:100%;width:{{ round($stepIdx/count($steps)*100) }}%;background:linear-gradient(90deg,#4154f1,#717ff5)"></div>
</div>

<div class="card-bd">
    <div class="note note-info mb-3">
        <i class="bi bi-info-circle-fill"></i>
        <div>បញ្ចូលរោគវិនិច្ឆ័យ ICD-10 / Enter at least one diagnosis. First row should be Primary.</div>
    </div>

    <form id="stepForm" method="POST" action="{{ route('workflow.step.save', [$visit->code, 'diagnosis']) }}">
        @csrf @method('PATCH')

        <div id="diagContainer">
            @forelse($existingDiags as $i => $diag)
            @include('clinics.workflow.steps._diagnosis_row', ['idx' => $i, 'diag' => $diag])
            @empty
            {{-- Default: one empty Primary row --}}
            @include('clinics.workflow.steps._diagnosis_row', ['idx' => 0, 'diag' => ['type' => 'Primary', 'code' => '', 'name' => '', 'description' => '', 'diagnosed_by' => auth()->user()?->name ?? '']])
            @endforelse
        </div>

        <div style="padding-top:12px;border-top:1px solid #f0f2ff;margin-top:12px">
            <button type="button" class="btn btn-sm btn-outline-primary" onclick="addDiagRow()">
                <i class="bi bi-plus-lg"></i> បន្ថែមរោគវិនិច្ឆ័យ / Add Diagnosis
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
let diagIdx = {{ count($existingDiags) ?: 1 }};

function addDiagRow() {
    const container = document.getElementById('diagContainer');
    const html = `
    <div class="diag-row" style="background:#f6f9ff;border-radius:10px;padding:14px;margin-bottom:10px;border:1px solid #f0f2ff;position:relative">
        <button type="button" class="btn btn-sm btn-outline-danger" style="position:absolute;top:8px;right:8px;padding:2px 8px;font-size:11px"
                onclick="this.closest('.diag-row').remove()">
            <i class="bi bi-trash"></i>
        </button>
        <div class="row g-2">
            <div class="col-6 col-sm-3">
                <div class="fld">
                    <label class="flbl"><span class="km">ប្រភេទ</span><span class="en">/ Type</span><span class="req">*</span></label>
                    <select name="diagnoses[${diagIdx}][type]" class="form-select" required>
                        <option value="Secondary">Secondary</option>
                        <option value="Primary">Primary</option>
                        <option value="In">In</option>
                        <option value="Out">Out</option>
                    </select>
                </div>
            </div>
            <div class="col-6 col-sm-3">
                <div class="fld">
                    <label class="flbl"><span class="en">ICD-10 Code</span></label>
                    <input name="diagnoses[${diagIdx}][code]" class="form-control" placeholder="e.g. J06.9"/>
                </div>
            </div>
            <div class="col-12 col-sm-6">
                <div class="fld">
                    <label class="flbl"><span class="km">ឈ្មោះរោគ</span><span class="en">/ Diagnosis Name</span><span class="req">*</span></label>
                    <input name="diagnoses[${diagIdx}][name]" class="form-control" required placeholder="Diagnosis name"/>
                </div>
            </div>
            <div class="col-12 col-sm-8">
                <div class="fld">
                    <label class="flbl"><span class="km">ការពិពណ៌នា</span><span class="en">/ Description</span></label>
                    <input name="diagnoses[${diagIdx}][description]" class="form-control" placeholder="Optional description"/>
                </div>
            </div>
            <div class="col-12 col-sm-4">
                <div class="fld">
                    <label class="flbl"><span class="km">វិនិច្ឆ័យដោយ</span><span class="en">/ By</span></label>
                    <input name="diagnoses[${diagIdx}][diagnosed_by]" class="form-control" value="{{ auth()->user()?->name ?? '' }}"/>
                </div>
            </div>
        </div>
    </div>`;
    container.insertAdjacentHTML('beforeend', html);
    diagIdx++;
}
</script>
@endpush
