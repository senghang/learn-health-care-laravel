@php
    $progressPct   = round($stepIdx / count($steps) * 100);
    $progressWidth = $progressPct . '%';
    $saveUrl       = url('/workflow/' . $visit->code . '/history/save');

    $h  = fn(string $key) => old("history.{$key}", $histories[$key]->value[0] ?? '');
    $pe = fn(string $key) => old("pe.{$key}", $examinations[$key]->value ?? '');

    $peSystems = [
        ['key'=>'general',     'km'=>'ទូទៅ',          'en'=>'General Appearance'],
        ['key'=>'skin',        'km'=>'ស្បែក',          'en'=>'Skin'],
        ['key'=>'heent',       'km'=>'ក-ត-ភ-ត',       'en'=>'HEENT'],
        ['key'=>'chest',       'km'=>'ទ្រូង / សួត',    'en'=>'Chest / Lungs'],
        ['key'=>'heart',       'km'=>'បេះដូង',         'en'=>'Cardiovascular'],
        ['key'=>'abdomen',     'km'=>'ក្រពះ',          'en'=>'Abdomen'],
        ['key'=>'extremities', 'km'=>'ដៃជើង',          'en'=>'Extremities'],
        ['key'=>'neuro',       'km'=>'ប្រព័ន្ធប្រសាទ', 'en'=>'Neurological'],
    ];

    $historiesCount   = $histories->count();
    $examinationsCount = $examinations->count();
    $peBy   = old('pe_by', auth()->user()?->name ?? '');
    $peDate = old('pe_date', now()->format('Y-m-d'));
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
    @if($historiesCount > 0 || $examinationsCount > 0)
    <div style="display:flex;gap:6px;align-items:center;flex-shrink:0">
        @if($historiesCount > 0)
        <span style="font-size:11px;background:#f0e8ff;color:#9b59b6;padding:3px 10px;border-radius:20px;border:1px solid #d5c0f0;font-weight:700">
            {{ $historiesCount }} History items
        </span>
        @endif
        @if($examinationsCount > 0)
        <span style="font-size:11px;background:#fff3e8;color:#ff771d;padding:3px 10px;border-radius:20px;border:1px solid #ffd0a8;font-weight:700">
            {{ $examinationsCount }} PE systems
        </span>
        @endif
    </div>
    @endif
</div>

<div style="height:3px;background:#f0f2ff">
    <div style="height:100%;width:{{ $progressWidth }};background:linear-gradient(90deg,#4154f1,#717ff5)"></div>
</div>

<div class="card-bd">
    <form id="stepForm" method="POST" action="{{ $saveUrl }}">
        @csrf
        @method('PATCH')

        {{-- Medical Histories --}}
        <div class="sec-block mb-3" style="border-left:4px solid #9b59b6;background:#9b59b60d;border-radius:10px;padding:14px 16px">
            <div style="font-size:10.5px;font-weight:800;text-transform:uppercase;letter-spacing:.6px;color:#9b59b6;margin-bottom:14px">
                ប្រវត្តិ / Medical Histories
            </div>
            <div class="row g-3">
                @foreach([
                    ['hpi',          'ប្រវត្តិជំងឺបច្ចុប្បន្ន', 'History of Present Illness', 'Onset, duration, character…', 'textarea'],
                    ['past_medical', 'ប្រវត្តិជំងឺ',            'Past Medical History',       'Diabetes, Hypertension…',    'textarea'],
                    ['allergies',    'អាឡែហ្ស',                 'Allergies',                  'Penicillin, Sulfa…',         'input'],
                    ['current_meds', 'ថ្នាំដែលកំពុងប្រើ',      'Current Medications',        'Metformin 500mg…',           'input'],
                    ['past_surgical','ប្រវត្តិការវះកាត់',        'Past Surgical History',      'Appendectomy 2018…',         'input'],
                    ['family',       'ប្រវត្តិគ្រួសារ',          'Family History',             'Father: DM…',                'input'],
                    ['immunizations','ប្រវត្តិចាក់វ៉ាក់ស',       'Immunizations',              'BCG, OPV, DTP…',             'input'],
                    ['social',       'ប្រវត្តិសង្គម',             'Social History',             'Smoking, alcohol…',          'input'],
                ] as [$key, $km, $en, $placeholder, $type])
                @php $val = $h($key); @endphp
                <div class="col-12 col-md-6">
                    <div class="fld">
                        <label class="flbl"><span class="km">{{ $km }}</span><span class="en">/ {{ $en }}</span></label>
                        @if($type === 'textarea')
                            <textarea name="history[{{ $key }}]" class="form-control" rows="3"
                                      placeholder="{{ $placeholder }}"
                                      {{ $key === 'hpi' ? 'required data-error-msg="History of Present Illness"' : '' }}>{{ $val }}</textarea>
                        @else
                            <input name="history[{{ $key }}]" class="form-control"
                                   placeholder="{{ $placeholder }}" value="{{ $val }}"/>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Physical Examination --}}
        <div class="sec-block mb-3" style="border-left:4px solid #ff771d;background:#ff771d0d;border-radius:10px;padding:14px 16px">
            <div style="font-size:10.5px;font-weight:800;text-transform:uppercase;letter-spacing:.6px;color:#ff771d;margin-bottom:14px">
                ការពិនិត្យរាងកាយ / Physical Examination
            </div>
            <div class="row g-3 mb-3">
                <div class="col-6 col-sm-4">
                    <div class="fld">
                        <label class="flbl"><span class="km">ពិនិត្យដោយ</span><span class="en">/ Examined By</span></label>
                        <input name="pe_by" class="form-control" placeholder="Dr. Name" value="{{ $peBy }}"/>
                    </div>
                </div>
                <div class="col-6 col-sm-4">
                    <div class="fld">
                        <label class="flbl"><span class="km">ថ្ងៃពិនិត្យ</span><span class="en">/ Date</span></label>
                        <input type="date" name="pe_date" class="form-control" value="{{ $peDate }}"/>
                    </div>
                </div>
            </div>
            <div class="row g-3">
                @foreach($peSystems as $sys)
                @php
                    $saved     = $pe($sys['key']);
                    $savedBadge = isset($examinations[$sys['key']]) ? '<span style="font-size:9px;background:#e8f8ef;color:#2eca6a;padding:1px 7px;border-radius:10px;font-weight:700;margin-left:4px">Saved</span>' : '';
                @endphp
                <div class="col-12 col-md-6">
                    <div class="fld">
                        <label class="flbl">
                            <span class="km">{{ $sys['km'] }}</span>
                            <span class="en">/ {{ $sys['en'] }}</span>
                            {!! $savedBadge !!}
                        </label>
                        <textarea name="pe[{{ $sys['key'] }}]" class="form-control" rows="2"
                                  placeholder="{{ $sys['en'] }} findings…">{{ $saved }}</textarea>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

    </form>
</div>
