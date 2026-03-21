@php
    $medName   = $med?->medicine_name ?? '';
    $strength  = $med?->strength      ?? '';
    $method    = $med?->method        ?? '';
    $note      = $med?->note          ?? '';
    $morning   = $med?->morning       ?? 1;
    $afternoon = $med?->afternoon     ?? 0;
    $evening   = $med?->evening       ?? 1;
    $night     = $med?->night         ?? 0;
    $days      = $med?->days          ?? 0;
    $interval  = $med?->interval      ?? '';
    $selForm   = $med?->form          ?? 'Tablet';
@endphp

<div class="rx-row-hd">
    <div class="d-flex align-items-center gap-2">
        <span style="font-size:18px">💊</span>
        <strong>ថ្នាំ #{{ $i + 1 }}</strong>
    </div>
    @if($i > 0)
    <button type="button" class="btn btn-sm btn-outline-danger"
            onclick="this.closest('.rx-row').remove()">✕ លុប</button>
    @endif
</div>

<div class="row g-3 mb-3">
    <div class="col-12 col-sm-6 col-md-3">
        <div class="fld">
            <label class="flbl">
                <span class="km">ឈ្មោះថ្នាំ</span><span class="en">/ Medicine</span><span class="req">*</span>
            </label>
            {{-- Field name matches DB column: medicine_name --}}
            <input name="meds[{{ $i }}][medicine_name]" class="form-control" required data-error-msg="Medicine Name"
                   placeholder="Artemether-Lumefantrine" value="{{ $medName }}"/>
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="fld">
            <label class="flbl"><span class="km">កម្លាំង</span><span class="en">/ Strength</span></label>
            <input name="meds[{{ $i }}][strength]" class="form-control"
                   placeholder="20/120mg" value="{{ $strength }}"/>
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="fld">
            <label class="flbl"><span class="km">ទំរង់</span><span class="en">/ Form</span></label>
            <select name="meds[{{ $i }}][form]" class="form-select">
                @foreach($formOptions as $f)
                @php $sel = $selForm === $f ? 'selected' : ''; @endphp
                <option value="{{ $f }}" {{ $sel }}>{{ $f }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="fld">
            <label class="flbl"><span class="km">វិធី</span><span class="en">/ Method</span></label>
            <input name="meds[{{ $i }}][method]" class="form-control"
                   placeholder="Oral" value="{{ $method }}"/>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="fld">
            <label class="flbl"><span class="km">ចំណាំ</span><span class="en">/ Note</span></label>
            <input name="meds[{{ $i }}][note]" class="form-control"
                   placeholder="Take with food" value="{{ $note }}"/>
        </div>
    </div>
</div>

<div style="background:#eef0fd;border-radius:10px;padding:12px">
    <div style="font-size:10px;font-weight:800;color:#4154f1;text-transform:uppercase;letter-spacing:.5px;margin-bottom:10px">
        កាលវិភាគ / Dosing Schedule
    </div>
    <div class="dosing-grid">
        @foreach([
            'morning'   => ['ព្រឹក',  'Morning',   $morning],
            'afternoon' => ['ថ្ងៃ',   'Afternoon', $afternoon],
            'evening'   => ['ល្ងាច',  'Evening',   $evening],
            'night'     => ['យប់',    'Night',     $night],
            'days'      => ['ថ្ងៃ',   'Days',      $days],
        ] as $field => [$lkm, $len, $val])
        <div class="dosing-cell">
            <div class="dosing-lbl">{{ $lkm }}<br>{{ $len }}</div>
            <input class="dosing-in" type="number" step="0.5"
                   name="meds[{{ $i }}][{{ $field }}]" value="{{ $val }}"/>
        </div>
        @endforeach
        <div class="dosing-cell">
            <div class="dosing-lbl">ចន្លោះ<br>Interval</div>
            <input class="dosing-in" type="text"
                   name="meds[{{ $i }}][interval]" value="{{ $interval }}"
                   placeholder="q8h"/>
        </div>
    </div>
</div>
