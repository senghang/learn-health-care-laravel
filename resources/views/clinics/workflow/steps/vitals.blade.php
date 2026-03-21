@php
    $latestVs    = $latestVs    ?? null;
    $savedValues = $savedValues ?? [];
    $vitalFields = $vitalFields ?? [];

    $recordedAt = old('recorded_at', df_input_dt($latestVs?->recorded_at) ?: df_now_input());
    $recordedBy = old('recorded_by', $latestVs?->recorded_by ?? auth()->user()?->name ?? '');
@endphp

<x-step.card step-id="vitals" :visit="$visit" :step-idx="$stepIdx" :steps="$steps"
    icon="bi-heart-fill" icon-color="#e74c3c"
    km="សញ្ញានៃជំងឺ" en="Vital Signs">

    @if($latestVs)
    <x-step.note type="success">
        <strong>ទិន្នន័យមុន / Previous reading:</strong>
        {{ df_dt($latestVs->recorded_at) }}
        @if($latestVs->recorded_by) · {{ $latestVs->recorded_by }} @endif
        · <span style="font-size:11px;color:#555">Values pre-filled</span>
    </x-step.note>
    @endif

    <x-step.note type="info">
        Auto range checking — values outside normal range are highlighted in red
    </x-step.note>

    <div class="row g-3 mb-4">
        <div class="col-6">
            <x-form.field name="recorded_at" km="ថ្ងៃម៉ោង" en="Recorded At"
                          type="datetime-local" :value="$recordedAt"/>
        </div>
        <div class="col-6">
            <x-form.field name="recorded_by" km="ពិនិត្យដោយ" en="Recorded By"
                          :value="$recordedBy" placeholder="ឈ្មោះ / Name"
                          :required="true" error-msg="Recorded By"/>
        </div>
    </div>

    <div class="row g-2">
        @foreach($vitalFields as $field)
        @php
            $key      = $field['key'];
            $val      = old("vitals.{$key}", $savedValues[$key] ?? '');
            $hasVal   = $val !== '' && $val !== null;
            $isNormal = $hasVal && $val >= $field['lo'] && $val <= $field['hi'];
            $cardCls  = 'vital-card' . ($hasVal ? ($isNormal ? ' normal' : ' abnormal') : '');
            $statusHtml = $hasVal
                ? ($isNormal
                    ? '<span class="badge-s b-normal" style="font-size:9px">✓ ធម្មតា</span>'
                    : '<span class="badge-s b-abnormal" style="font-size:9px">⚠ មិនធម្មតា</span>')
                : '';
        @endphp
        <div class="col-6 col-sm-4 col-md-3">
            <div class="{{ $cardCls }}" id="vcard-{{ $key }}">
                <div class="vital-icon">{{ $field['icon'] }}</div>
                <div class="vital-lbl">{{ $field['km'] }}</div>
                <div class="vital-en">{{ $field['en'] }}</div>
                <input class="vital-input" type="number" step="any"
                       name="vitals[{{ $key }}]"
                       value="{{ $val }}"
                       placeholder="{{ $field['placeholder'] }}"
                       oninput="checkRange(this,'{{ $key }}')"/>
                <div class="vital-unit">{{ $field['unit'] }}</div>
                <div class="vital-range">ធម្មតា: {{ $field['normal'] }}</div>
                <div class="vital-status" id="vstatus-{{ $key }}">{!! $statusHtml !!}</div>
            </div>
        </div>
        @endforeach
    </div>

</x-step.card>

<script>
const ranges = {
    @foreach($vitalFields as $field)
    '{{ $field['key'] }}': { lo: {{ $field['lo'] }}, hi: {{ $field['hi'] }} },
    @endforeach
};
function checkRange(input, key) {
    const val    = parseFloat(input.value);
    const card   = document.getElementById('vcard-' + key);
    const status = document.getElementById('vstatus-' + key);
    if (!card || !status) return;
    if (isNaN(val) || input.value === '') {
        card.className = 'vital-card';
        status.innerHTML = '';
        return;
    }
    const r = ranges[key], ok = r && val >= r.lo && val <= r.hi;
    card.className = 'vital-card ' + (ok ? 'normal' : 'abnormal');
    status.innerHTML = ok
        ? '<span class="badge-s b-normal" style="font-size:9px">✓ ធម្មតា</span>'
        : '<span class="badge-s b-abnormal" style="font-size:9px">⚠ មិនធម្មតា</span>';
}
document.querySelectorAll('.vital-input').forEach(function(el) {
    if (el.value !== '') {
        var m = el.name.match(/vitals\[(.+?)\]/);
        if (m) checkRange(el, m[1]);
    }
});
</script>
