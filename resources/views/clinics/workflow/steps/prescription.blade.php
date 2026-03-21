<div class="card-hd" style="flex-wrap:wrap;gap:8px;padding:14px 18px 10px">
    <div style="flex:1;min-width:0">
        <div class="card-hd-title">
            <i class="bi bi-capsule-pill" style="color:#e91e8c"></i>បញ្ជាថ្នាំ
            <small style="font-size:11px;color:#bbb;font-weight:400">/ Prescription</small>
        </div>
        <div style="font-size:10.5px;color:#aaa;margin-top:3px">ជំហាន {{ $stepIdx+1 }} នៃ {{ count($steps) }} / Step {{ $stepIdx+1 }} of {{ count($steps) }}</div>
    </div>
</div>
<div style="height:3px;background:#f0f2ff"><div style="height:100%;width:{{ round($stepIdx/count($steps)*100) }}%;background:linear-gradient(90deg,#4154f1,#717ff5)"></div></div>

<div class="card-bd">
    <form id="stepForm" method="POST" action="{{ route('workflow.step.save', [$visit->code, 'prescription']) }}">
        @csrf @method('PATCH')

        <div class="row g-3 mb-4">
            <div class="col-6 col-sm-4">
                <div class="fld">
                    <label class="flbl"><span class="km">Rx Code</span></label>
                    <input class="form-control ro"
                           value="{{ $visit->prescriptions->first()?->code ?? 'RX'.strtoupper($visit->code) }}" readonly/>
                </div>
            </div>
            <div class="col-6 col-sm-4">
                <div class="fld">
                    <label class="flbl"><span class="km">ថ្ងៃ</span><span class="en">/ Prescribed At</span></label>
                    <input type="datetime-local" name="prescribed_at" class="form-control"
                           value="{{ old('prescribed_at', $visit->prescriptions->first()?->prescribed_at?->format('Y-m-d\TH:i') ?? now()->format('Y-m-d\TH:i')) }}"/>
                </div>
            </div>
            <div class="col-12 col-sm-4">
                <div class="fld">
                    <label class="flbl"><span class="km">ផ្ដល់ដោយ</span><span class="en">/ Prescribed By</span><span class="req">*</span></label>
                    <input name="prescribed_by" class="form-control"
                           value="{{ old('prescribed_by', $visit->prescriptions->first()?->prescribed_by ?? '') }}"/>
                </div>
            </div>
        </div>

        <div id="rxList">
            @forelse($visit->prescriptions->first()?->medications ?? [] as $i => $med)
            <div class="rx-row">
                <div class="rx-row-hd">
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <span style="font-size:18px">💊</span>
                        <strong>{{ $med->medicine_name }}</strong>
                        <code style="background:#eef0fd;color:#4154f1;padding:2px 8px;border-radius:5px;font-size:11px">
                            {{ $med->strength }} · {{ $med->form }}
                        </code>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-danger">✕ លុប</button>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-12 col-sm-6 col-md-3">
                        <div class="fld">
                            <label class="flbl"><span class="km">ឈ្មោះថ្នាំ</span><span class="en">/ Medicine</span><span class="req">*</span></label>
                            <input name="meds[{{ $i }}][name]" class="form-control" value="{{ $med->medicine_name }}"/>
                        </div>
                    </div>
                    <div class="col-6 col-md-2">
                        <div class="fld">
                            <label class="flbl"><span class="km">កម្លាំង</span><span class="en">/ Strength</span></label>
                            <input name="meds[{{ $i }}][strength]" class="form-control" value="{{ $med->strength }}"/>
                        </div>
                    </div>
                    <div class="col-6 col-md-2">
                        <div class="fld">
                            <label class="flbl"><span class="km">ទំរង់</span><span class="en">/ Form</span></label>
                            <select name="meds[{{ $i }}][form]" class="form-select">
                                @foreach(['Tablet','Capsule','Syrup','Injection'] as $f)
                                <option value="{{ $f }}" {{ $med->form === $f ? 'selected':'' }}>{{ $f }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-6 col-md-2">
                        <div class="fld">
                            <label class="flbl"><span class="km">វិធី</span><span class="en">/ Method</span></label>
                            <input name="meds[{{ $i }}][method]" class="form-control" value="{{ $med->method }}"/>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="fld">
                            <label class="flbl"><span class="km">ចំណាំ</span><span class="en">/ Note</span></label>
                            <input name="meds[{{ $i }}][note]" class="form-control" value="{{ $med->note }}"/>
                        </div>
                    </div>
                </div>
                <div style="background:#eef0fd;border-radius:10px;padding:12px">
                    <div style="font-size:10px;font-weight:800;color:#4154f1;text-transform:uppercase;letter-spacing:.5px;margin-bottom:10px">
                        កាលវិភាគ / Dosing Schedule
                    </div>
                    <div class="dosing-grid">
                        @foreach(['morning'=>'ព្រឹក', 'afternoon'=>'ថ្ងៃ', 'evening'=>'ល្ងាច', 'night'=>'យប់', 'days'=>'ថ្ងៃ', 'interval'=>'ចន្លោះ'] as $field => $label)
                        <div class="dosing-cell">
                            <div class="dosing-lbl">{{ $label }}<br>{{ ucfirst($field) }}</div>
                            <input class="dosing-in" type="{{ $field === 'interval' ? 'text' : 'number' }}"
                                   name="meds[{{ $i }}][{{ $field }}]"
                                   value="{{ $med->{$field} ?? 0 }}"/>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @empty
            {{-- Seed row --}}
            <div class="rx-row">
                <div class="rx-row-hd">
                    <div class="d-flex align-items-center gap-2"><span style="font-size:18px">💊</span><strong>ថ្នាំ #1</strong></div>
                    <button type="button" class="btn btn-sm btn-outline-danger">✕ លុប</button>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-12 col-sm-6 col-md-3">
                        <div class="fld">
                            <label class="flbl"><span class="km">ឈ្មោះថ្នាំ</span><span class="en">/ Medicine</span><span class="req">*</span></label>
                            <input name="meds[0][name]" class="form-control" placeholder="Artemether-Lumefantrine"/>
                        </div>
                    </div>
                    <div class="col-6 col-md-2">
                        <div class="fld">
                            <label class="flbl"><span class="km">កម្លាំង</span><span class="en">/ Strength</span></label>
                            <input name="meds[0][strength]" class="form-control" placeholder="20/120mg"/>
                        </div>
                    </div>
                    <div class="col-6 col-md-2">
                        <div class="fld">
                            <label class="flbl"><span class="km">ទំរង់</span><span class="en">/ Form</span></label>
                            <select name="meds[0][form]" class="form-select">
                                @foreach(['Tablet','Capsule','Syrup','Injection'] as $f)
                                <option>{{ $f }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-6 col-md-2">
                        <div class="fld">
                            <label class="flbl"><span class="km">វិធី</span><span class="en">/ Method</span></label>
                            <input name="meds[0][method]" class="form-control" placeholder="Oral"/>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="fld">
                            <label class="flbl"><span class="km">ចំណាំ</span><span class="en">/ Note</span></label>
                            <input name="meds[0][note]" class="form-control" placeholder="Take with food"/>
                        </div>
                    </div>
                </div>
                <div style="background:#eef0fd;border-radius:10px;padding:12px">
                    <div style="font-size:10px;font-weight:800;color:#4154f1;text-transform:uppercase;letter-spacing:.5px;margin-bottom:10px">
                        កាលវិភាគ / Dosing Schedule
                    </div>
                    <div class="dosing-grid">
                        @foreach(['morning'=>'ព្រឹក / Morning','afternoon'=>'ថ្ងៃ / Afternoon','evening'=>'ល្ងាច / Evening','night'=>'យប់ / Night','days'=>'ថ្ងៃ / Days','interval'=>'ចន្លោះ / Interval'] as $field => $label)
                        <div class="dosing-cell">
                            <div class="dosing-lbl">{{ $label }}</div>
                            <input class="dosing-in" type="{{ $field === 'interval' ? 'text' : 'number' }}"
                                   name="meds[0][{{ $field }}]" value="{{ in_array($field,['morning','evening']) ? 1 : 0 }}"/>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endforelse
        </div>

        <button type="button" class="btn btn-outline-primary btn-w100 mt-2">
            <i class="bi bi-plus-circle"></i> Add Medication / បន្ថែមថ្នាំ
        </button>
    </form>
</div>
