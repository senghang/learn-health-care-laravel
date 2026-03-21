@php
    $histories    = $histories    ?? collect([]);
    $examinations = $examinations ?? collect([]);

    $h  = fn(string $key) => old("history.{$key}", $histories[$key]->value[0] ?? '');
    $pe = fn(string $key) => old("pe.{$key}", $examinations[$key]->value ?? '');

    $peSystems = [
        ['key' => 'general',     'km' => 'ទូទៅ',          'en' => 'General Appearance'],
        ['key' => 'skin',        'km' => 'ស្បែក',          'en' => 'Skin'],
        ['key' => 'heent',       'km' => 'ក-ត-ភ-ត',       'en' => 'HEENT'],
        ['key' => 'chest',       'km' => 'ទ្រូង / សួត',    'en' => 'Chest / Lungs'],
        ['key' => 'heart',       'km' => 'បេះដូង',         'en' => 'Cardiovascular'],
        ['key' => 'abdomen',     'km' => 'ក្រពះ',          'en' => 'Abdomen'],
        ['key' => 'extremities', 'km' => 'ដៃជើង',          'en' => 'Extremities'],
        ['key' => 'neuro',       'km' => 'ប្រព័ន្ធប្រសាទ', 'en' => 'Neurological'],
    ];

    $histBadge = $histories->isNotEmpty()   ? $histories->count() . ' history items' : null;
    $peBadge   = $examinations->isNotEmpty() ? $examinations->count() . ' PE systems'  : null;
    $peBy      = old('pe_by', auth()->user()?->name ?? '');
    $peDate    = old('pe_date', now()->format('Y-m-d'));
@endphp

<x-step.card step-id="history" :visit="$visit" :step-idx="$stepIdx" :steps="$steps"
    icon="bi-book-fill" icon-color="#9b59b6"
    km="ប្រវត្តិជំងឺ" en="Medical History"
    :badge="$histBadge" badge-color="#9b59b6">

    {{-- ── Medical Histories ──────────────────────────────────────────── --}}
    <x-form.section title="Medical Histories" km="ប្រវត្តិ" color="#9b59b6">
        <div class="row g-3">
            <div class="col-12 col-md-6">
                <x-form.field name="history[hpi]" km="ប្រវត្តិជំងឺបច្ចុប្បន្ន" en="History of Present Illness"
                              :textarea="true" rows="3" :required="true"
                              placeholder="Onset, duration, character, severity…"
                              :value="$h('hpi')"/>
            </div>
            <div class="col-12 col-md-6">
                <x-form.field name="history[past_medical]" km="ប្រវត្តិជំងឺ" en="Past Medical History"
                              :textarea="true" rows="3"
                              placeholder="Diabetes, Hypertension…"
                              :value="$h('past_medical')"/>
            </div>
            <div class="col-12 col-md-6">
                <x-form.field name="history[allergies]" km="អាឡែហ្ស" en="Allergies"
                              placeholder="Penicillin, Sulfa…" :value="$h('allergies')"/>
            </div>
            <div class="col-12 col-md-6">
                <x-form.field name="history[current_meds]" km="ថ្នាំដែលកំពុងប្រើ" en="Current Medications"
                              placeholder="Metformin 500mg…" :value="$h('current_meds')"/>
            </div>
            <div class="col-12 col-md-6">
                <x-form.field name="history[past_surgical]" km="ប្រវត្តិការវះកាត់" en="Past Surgical History"
                              placeholder="Appendectomy 2018…" :value="$h('past_surgical')"/>
            </div>
            <div class="col-12 col-md-6">
                <x-form.field name="history[family]" km="ប្រវត្តិគ្រួសារ" en="Family History"
                              placeholder="Father: DM, Mother: HTN…" :value="$h('family')"/>
            </div>
            <div class="col-12 col-md-6">
                <x-form.field name="history[immunizations]" km="ប្រវត្តិការចាក់វ៉ាក់ស" en="Immunizations"
                              placeholder="BCG, OPV, DTP…" :value="$h('immunizations')"/>
            </div>
            <div class="col-12 col-md-6">
                <x-form.field name="history[social]" km="ប្រវត្តិសង្គម" en="Social History"
                              placeholder="Smoking, alcohol, occupation…" :value="$h('social')"/>
            </div>
        </div>
    </x-form.section>

    {{-- ── Physical Examination ─────────────────────────────────────────── --}}
    <x-form.section title="Physical Examination" km="ការពិនិត្យរាងកាយ" color="#ff771d">
        <div class="row g-3 mb-3">
            <div class="col-6 col-sm-4">
                <x-form.field name="pe_by" km="ពិនិត្យដោយ" en="Examined By"
                              placeholder="Dr. Name" :value="$peBy"/>
            </div>
            <div class="col-6 col-sm-4">
                <x-form.field name="pe_date" km="ថ្ងៃពិនិត្យ" en="Date"
                              type="date" :value="$peDate"/>
            </div>
        </div>
        <div class="row g-3">
            @foreach($peSystems as $sys)
            <div class="col-12 col-md-6">
                <x-form.field name="pe[{{ $sys['key'] }}]"
                              km="{{ $sys['km'] }}" en="{{ $sys['en'] }}"
                              :textarea="true" rows="2"
                              placeholder="{{ $sys['en'] }} findings…"
                              :value="$pe($sys['key'])"/>
            </div>
            @endforeach
        </div>
    </x-form.section>

</x-step.card>
