{{--
    Step: Medical History (ប្រវត្តិជំងឺ)
    Variables from HistoryStep::viewData():
      $histories    — Collection<MedicalHistory> keyed by name
      $examinations — Collection<PhysicalExamination> keyed by name
      $visit        — Visit model
      $stepIdx      — 0-based index
      $steps        — all steps array
--}}
@php
    // Helper: get saved history value (stored as JSON array, take first element)
    $h = fn(string $key) => old("history.{$key}", $histories[$key]->value[0] ?? '');
    // Helper: get saved PE value
    $pe = fn(string $key) => old("pe.{$key}", $examinations[$key]->value ?? '');

    $peSystems = [
        ['key'=>'general',     'km'=>'ទូទៅ',          'en'=>'General Appearance'],
        ['key'=>'skin',        'km'=>'ស្បែក',          'en'=>'Skin'],
        ['key'=>'heent',       'km'=>'ក-ត-ភ-ត',       'en'=>'HEENT'],
        ['key'=>'chest',       'km'=>'ទ្រូង / សួត',    'en'=>'Chest / Lungs'],
        ['key'=>'heart',       'km'=>'បេះដូង',         'en'=>'Cardiovascular'],
        ['key'=>'abdomen',     'km'=>'ក្រពះ',           'en'=>'Abdomen'],
        ['key'=>'extremities', 'km'=>'ដៃជើង',          'en'=>'Extremities'],
        ['key'=>'neuro',       'km'=>'ប្រព័ន្ធប្រសាទ', 'en'=>'Neurological'],
    ];
@endphp

<div class="card-hd" style="flex-wrap:wrap;gap:8px;padding:14px 18px 10px">
    <div style="flex:1;min-width:0">
        <div class="card-hd-title">
            <i class="bi bi-book-fill" style="color:#9b59b6"></i>ប្រវត្តិជំងឺ
            <small style="font-size:11px;color:#bbb;font-weight:400">/ Medical History</small>
        </div>
        <div style="font-size:10.5px;color:#aaa;margin-top:3px">
            ជំហាន {{ $stepIdx+1 }} នៃ {{ count($steps) }} / Step {{ $stepIdx+1 }} of {{ count($steps) }}
        </div>
    </div>
    {{-- Show saved counts --}}
    @if($histories->isNotEmpty() || $examinations->isNotEmpty())
        <div style="display:flex;gap:6px;align-items:center;flex-shrink:0">
            @if($histories->isNotEmpty())
                <span
                    style="font-size:11px;background:#f0e8ff;color:#9b59b6;padding:3px 10px;border-radius:20px;border:1px solid #d5c0f0;font-weight:700">
            {{ $histories->count() }} History items
        </span>
            @endif
            @if($examinations->isNotEmpty())
                <span
                    style="font-size:11px;background:#fff3e8;color:#ff771d;padding:3px 10px;border-radius:20px;border:1px solid #ffd0a8;font-weight:700">
            {{ $examinations->count() }} PE systems
        </span>
            @endif
        </div>
    @endif
</div>

<div style="height:3px;background:#f0f2ff">
    <div
        style="height:100%;width:{{ round($stepIdx / count($steps) * 100) }}%;background:linear-gradient(90deg,#4154f1,#717ff5)"></div>
</div>

<div class="card-bd">
    <form id="stepForm" method="POST"
          action="{{ route('workflow.step.save', [$visit->code, $currentStep ?? 'history']) }}">
        @csrf
        @method('PATCH')

        {{-- ── Medical Histories ──────────────────────────────────────── --}}
        <div class="sec-block" style="border-color:#9b59b6;background:#9b59b60d">
            <div
                style="font-size:10.5px;font-weight:800;text-transform:uppercase;letter-spacing:.6px;color:#9b59b6;margin-bottom:14px">
                ប្រវត្តិ / Medical Histories
            </div>
            <div class="row g-3">

                <div class="col-12 col-md-6">
                    <div class="fld">
                        <label class="flbl">
                            <span class="km">ប្រវត្តិជំងឺបច្ចុប្បន្ន</span>
                            <span class="en">/ History of Present Illness</span>
                        </label>
                        <textarea name="history[hpi]" class="form-control" rows="3"
                                  placeholder="Onset, duration, character, severity…">{{ $h('hpi') }}</textarea>
                    </div>
                </div>

                <div class="col-12 col-md-6">
                    <div class="fld">
                        <label class="flbl">
                            <span class="km">ប្រវត្តិជំងឺ</span>
                            <span class="en">/ Past Medical History</span>
                        </label>
                        <textarea name="history[past_medical]" class="form-control" rows="3"
                                  placeholder="Diabetes, Hypertension…">{{ $h('past_medical') }}</textarea>
                    </div>
                </div>

                <div class="col-12 col-md-6">
                    <div class="fld">
                        <label class="flbl">
                            <span class="km">អាឡែហ្ស</span>
                            <span class="en">/ Allergies</span>
                        </label>
                        <input name="history[allergies]" class="form-control"
                               placeholder="Penicillin, Sulfa…"
                               value="{{ $h('allergies') }}"/>
                    </div>
                </div>

                <div class="col-12 col-md-6">
                    <div class="fld">
                        <label class="flbl">
                            <span class="km">ថ្នាំដែលកំពុងប្រើ</span>
                            <span class="en">/ Current Medications</span>
                        </label>
                        <input name="history[current_meds]" class="form-control"
                               placeholder="Metformin 500mg, Amlodipine 5mg…"
                               value="{{ $h('current_meds') }}"/>
                    </div>
                </div>

                <div class="col-12 col-md-6">
                    <div class="fld">
                        <label class="flbl">
                            <span class="km">ប្រវត្តិការវះកាត់</span>
                            <span class="en">/ Past Surgical History</span>
                        </label>
                        <input name="history[past_surgical]" class="form-control"
                               placeholder="Appendectomy 2018…"
                               value="{{ $h('past_surgical') }}"/>
                    </div>
                </div>

                <div class="col-12 col-md-6">
                    <div class="fld">
                        <label class="flbl">
                            <span class="km">ប្រវត្តិគ្រួសារ</span>
                            <span class="en">/ Family History</span>
                        </label>
                        <input name="history[family]" class="form-control"
                               placeholder="Father: DM, Mother: HTN…"
                               value="{{ $h('family') }}"/>
                    </div>
                </div>

                <div class="col-12 col-md-6">
                    <div class="fld">
                        <label class="flbl">
                            <span class="km">ប្រវត្តិការចាក់វ៉ាក់ស</span>
                            <span class="en">/ Immunizations</span>
                        </label>
                        <input name="history[immunizations]" class="form-control"
                               placeholder="BCG, OPV, DTP…"
                               value="{{ $h('immunizations') }}"/>
                    </div>
                </div>

                <div class="col-12 col-md-6">
                    <div class="fld">
                        <label class="flbl">
                            <span class="km">ប្រវត្តិសង្គម</span>
                            <span class="en">/ Social History</span>
                        </label>
                        <input name="history[social]" class="form-control"
                               placeholder="Smoking, alcohol, occupation…"
                               value="{{ $h('social') }}"/>
                    </div>
                </div>

            </div>
        </div>

        {{-- ── Physical Examination ───────────────────────────────────── --}}
        <div class="sec-block" style="border-color:#ff771d;background:#ff771d0d">
            <div
                style="font-size:10.5px;font-weight:800;text-transform:uppercase;letter-spacing:.6px;color:#ff771d;margin-bottom:14px">
                ការពិនិត្យរាងកាយ / Physical Examination
            </div>

            <div class="row g-3 mb-3">
                <div class="col-6 col-sm-4">
                    <div class="fld">
                        <label class="flbl">
                            <span class="km">ពិនិត្យដោយ</span>
                            <span class="en">/ Examined By</span>
                        </label>
                        <input name="pe_by" class="form-control"
                               placeholder="Dr. Name"
                               value="{{ old('pe_by', auth()->user()?->name ?? '') }}"/>
                    </div>
                </div>
                <div class="col-6 col-sm-4">
                    <div class="fld">
                        <label class="flbl">
                            <span class="km">ថ្ងៃពិនិត្យ</span>
                            <span class="en">/ Date</span>
                        </label>
                        <input type="date" name="pe_date" class="form-control"
                               value="{{ old('pe_date', now()->format('Y-m-d')) }}"/>
                    </div>
                </div>
            </div>

            <div class="row g-3">
                @foreach($peSystems as $sys)
                    <div class="col-12 col-md-6">
                        <div class="fld">
                            <label class="flbl">
                                <span class="km">{{ $sys['km'] }}</span>
                                <span class="en">/ {{ $sys['en'] }}</span>
                                @if(!empty($examinations[$sys['key']]))
                                    <span
                                        style="font-size:9px;background:#e8f8ef;color:#2eca6a;padding:1px 7px;border-radius:10px;font-weight:700;margin-left:4px">Saved</span>
                                @endif
                            </label>
                            <textarea name="pe[{{ $sys['key'] }}]" class="form-control" rows="2"
                                      placeholder="{{ $sys['en'] }} findings…">{{ $pe($sys['key']) }}</textarea>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

    </form>
</div>
