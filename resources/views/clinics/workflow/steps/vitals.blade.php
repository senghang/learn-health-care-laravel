<div class="card-hd" style="flex-wrap:wrap;gap:8px;padding:14px 18px 10px">
    <div style="flex:1;min-width:0">
        <div class="card-hd-title">
            <i class="bi bi-heart-fill" style="color:#e74c3c"></i>
            សញ្ញានៃជំងឺ
            <small style="font-size:11px;color:#bbb;font-weight:400">/ Vital Signs</small>
        </div>
        <div style="font-size:10.5px;color:#aaa;margin-top:3px">
            ជំហាន {{ $stepIdx + 1 }} នៃ {{ count($steps) }} / Step {{ $stepIdx + 1 }} of {{ count($steps) }}
        </div>
    </div>
</div>

<div style="height:3px;background:#f0f2ff">
    <div
        style="height:100%;width:{{ round($stepIdx / count($steps) * 100) }}%;background:linear-gradient(90deg,#4154f1,#717ff5)"></div>
</div>

<div class="card-bd">

    {{-- Previous reading notice --}}
    @if($latestVs)
        <div class="note note-success mb-3" style="align-items:flex-start">
            <i class="bi bi-check-circle-fill" style="flex-shrink:0;margin-top:1px"></i>
            <div>
                <strong>ទិន្នន័យមុន / Previous reading:</strong>
                {{ $latestVs->recorded_at?->format('d/m/Y H:i') }}
                @if($latestVs->recorded_by)
                    · {{ $latestVs->recorded_by }}
                @endif
                · <span style="font-size:11px;color:#555">ទិន្នន័យត្រូវបានបំពេញស្វ័យប្រវត្តិ / Values pre-filled</span>
            </div>
        </div>
    @endif

    <div class="note note-info mb-3">
        <i class="bi bi-info-circle-fill"></i>
        <div>ការត្រួតពិនិត្យស្វ័យប្រវត្តិ / Auto range checking — values outside normal range are highlighted in red
        </div>
    </div>

    <form id="stepForm" method="POST"
          action="{{ route('workflow.step.save', [$visit->code, $currentStep ?? 'vitals']) }}">
        @csrf
        @method('PATCH')

        {{-- Header fields --}}
        <div class="row g-3 mb-4">
            <div class="col-6">
                <div class="fld">
                    <label class="flbl">
                        <span class="km">ថ្ងៃម៉ោង</span><span class="en">/ Recorded At</span>
                    </label>
                    <input type="datetime-local" name="recorded_at" class="form-control"
                           value="{{ old('recorded_at', $latestVs?->recorded_at?->format('Y-m-d\TH:i') ?? now()->format('Y-m-d\TH:i')) }}"/>
                </div>
            </div>
            <div class="col-6">
                <div class="fld">
                    <label class="flbl">
                        <span class="km">ពិនិត្យដោយ</span><span class="en">/ Recorded By</span>
                    </label>
                    <input name="recorded_by" class="form-control" placeholder="ឈ្មោះ / Name"
                           value="{{ old('recorded_by', $latestVs?->recorded_by ?? auth()->user()?->name ?? '') }}"/>
                </div>
            </div>
        </div>

        {{-- Vital sign cards --}}
        <div class="row g-2">
            @foreach($vitalFields as $field)
                @php
                    $key = $field['key'];
                    $val = old("vitals.{$key}", $savedValues[$key] ?? '');
                    $hasVal   = $val !== '' && $val !== null;
                    $isNormal = $hasVal && $val >= $field['lo'] && $val <= $field['hi'];
                    $cardClass = $hasVal ? ($isNormal ? 'vital-card normal' : 'vital-card abnormal') : 'vital-card';
                @endphp
                <div class="col-6 col-sm-4 col-md-3">
                    <div class="{{ $cardClass }}" id="vcard-{{ $key }}">
                        <div class="vital-icon">{{ $field['icon'] }}</div>
                        <div class="vital-lbl">{{ $field['km'] }}</div>
                        <div class="vital-en">{{ $field['en'] }}</div>
                        <input
                            class="vital-input"
                            type="number"
                            step="any"
                            name="vitals[{{ $key }}]"
                            value="{{ $val }}"
                            placeholder="{{ $field['placeholder'] }}"
                            oninput="checkRange(this, '{{ $key }}')"/>
                        <div class="vital-unit">{{ $field['unit'] }}</div>
                        <div class="vital-range">ធម្មតា: {{ $field['normal'] }}</div>
                        <div class="vital-status" id="vstatus-{{ $key }}">
                            @if($hasVal)
                                @if($isNormal)
                                    <span class="badge-s b-normal" style="font-size:9px">✓ ធម្មតា</span>
                                @else
                                    <span class="badge-s b-abnormal" style="font-size:9px">⚠ មិនធម្មតា</span>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

    </form>
</div>

@push('scripts')
    <script>
        // Ranges come from PHP — no duplication
        const ranges = {
            @foreach($vitalFields as $field)
            '{{ $field['key'] }}': {lo: {{ $field['lo'] }}, hi: {{ $field['hi'] }}},
            @endforeach
        };

        function checkRange(input, key) {
            const val = parseFloat(input.value);
            const card = document.getElementById('vcard-' + key);
            const status = document.getElementById('vstatus-' + key);
            if (!card || !status) return;

            if (isNaN(val) || input.value === '') {
                card.className = 'vital-card';
                status.innerHTML = '';
                return;
            }

            const r = ranges[key];
            const isNormal = r && val >= r.lo && val <= r.hi;

            card.className = 'vital-card ' + (isNormal ? 'normal' : 'abnormal');
            status.innerHTML = isNormal
                ? '<span class="badge-s b-normal" style="font-size:9px">✓ ធម្មតា</span>'
                : '<span class="badge-s b-abnormal" style="font-size:9px">⚠ មិនធម្មតា</span>';
        }

        // Run on load for pre-filled values
        document.querySelectorAll('.vital-input').forEach(input => {
            if (input.value !== '') {
                const match = input.name.match(/vitals\[(.+?)\]/);
                if (match) checkRange(input, match[1]);
            }
        });
    </script>
@endpush
