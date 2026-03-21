@php
    $progressPct   = round($stepIdx / count($steps) * 100);
    $progressWidth = $progressPct . '%';
    $saveUrl       = url('/workflow/' . $visit->code . '/triage/save');
    $recordedAt    = old('recorded_at', $triage?->recorded_at?->format('Y-m-d\TH:i') ?? '');
    $recordedBy    = old('recorded_by', $triage?->recorded_by ?? '');
    $height        = old('height',  $triage?->height ?? '');
    $weight        = old('weight',  $triage?->weight ?? '');
    $complaint     = old('chief_complaint', $triage?->chief_complaint ?? '');
    $title         = old('title', $triage?->title ?? '');
    $encounterCode = $triage?->encounter_code ?? ('TR' . $visit->code);
@endphp

<div class="card-hd" style="flex-wrap:wrap;gap:8px;padding:14px 18px 10px">
    <div style="flex:1;min-width:0">
        <div class="card-hd-title">
            <i class="bi bi-heart-pulse-fill"></i>ការពិនិត្យចូល
            <small style="font-size:11px;color:#bbb;font-weight:400">/ Triage</small>
        </div>
        <div style="font-size:10.5px;color:#aaa;margin-top:3px">
            ជំហាន {{ $stepIdx+1 }} នៃ {{ count($steps) }} / Step {{ $stepIdx+1 }} of {{ count($steps) }}
        </div>
    </div>
</div>

<div style="height:3px;background:#f0f2ff">
    <div style="height:100%;width:{{ $progressWidth }};background:linear-gradient(90deg,#4154f1,#717ff5)"></div>
</div>

<div class="card-bd">
    <div class="note note-info mb-3">
        <i class="bi bi-info-circle-fill"></i>
        <div>ការត្អូញត្អែររបស់អ្នកជំងឺ — ចំណោទ ហើយការវាស់ស្ទង់ / Chief complaint and initial measurements</div>
    </div>

    <form id="stepForm" method="POST" action="{{ $saveUrl }}">
        @csrf
        @method('PATCH')

        <div class="row g-3">
            <div class="col-6">
                <div class="fld">
                    <label class="flbl"><span class="km">លេខ Encounter</span><span class="en">/ Encounter Code</span></label>
                    <input class="form-control ro" value="{{ $encounterCode }}" readonly/>
                </div>
            </div>
            <div class="col-6">
                <div class="fld">
                    <label class="flbl"><span class="km">ពិនិត្យដោយ</span><span class="en">/ Recorded By</span></label>
                    <input name="recorded_by" class="form-control" placeholder="ឈ្មោះ / Name" value="{{ $recordedBy }}"/>
                </div>
            </div>
            <div class="col-6">
                <div class="fld">
                    <label class="flbl"><span class="km">ថ្ងៃម៉ោង</span><span class="en">/ Recorded At</span></label>
                    <input type="datetime-local" name="recorded_at" class="form-control" value="{{ $recordedAt }}"/>
                </div>
            </div>
            <div class="col-6">
                <div class="fld">
                    <label class="flbl"><span class="km">តួរបស់</span><span class="en">/ Title</span></label>
                    <select name="title" class="form-select">
                        @foreach(['វេជ្ជបណ្ឌិត / Doctor', 'គិលានុបដ្ឋាយិកា / Nurse', 'Paramedic'] as $t)
                            @php $selected = $title === $t ? 'selected' : ''; @endphp
                            <option value="{{ $t }}" {{ $selected }}>{{ $t }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-6">
                <div class="fld">
                    <label class="flbl"><span class="km">កម្ពស់ (CM)</span><span class="en">/ Height</span></label>
                    <input type="number" name="height" class="form-control" placeholder="170" value="{{ $height }}"/>
                </div>
            </div>
            <div class="col-6">
                <div class="fld">
                    <label class="flbl"><span class="km">ទម្ងន់ (KG)</span><span class="en">/ Weight</span></label>
                    <input type="number" name="weight" class="form-control" placeholder="65" value="{{ $weight }}"/>
                </div>
            </div>
            <div class="col-12">
                <div class="fld">
                    <label class="flbl"><span class="km">ហេតុការណ៍ចូល</span><span class="en">/ Chief Complaint</span></label>
                    <textarea name="chief_complaint" class="form-control" rows="3" required data-error-msg="Chief Complaint"
                              placeholder="ពណ៌នា… / Describe…">{{ $complaint }}</textarea>
                </div>
            </div>
        </div>
    </form>
</div>
