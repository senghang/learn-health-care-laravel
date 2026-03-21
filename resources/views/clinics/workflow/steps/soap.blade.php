@php
    $soap      = $soap      ?? null;
    $encounter = $encounter ?? null;

    $savedTime = $soap?->updated_at?->format('d/m H:i') ?? '';

    $soapCards = [
        ['key' => 'subjective', 'color' => '#4154f1', 'letter' => 'S', 'km' => 'ការរៀបរាប់',   'en' => 'Subjective',  'desc' => 'Patient-reported symptoms & complaints',  'required' => true],
        ['key' => 'objective',  'color' => '#2eca6a', 'letter' => 'O', 'km' => 'ការរក្ខ',      'en' => 'Objective',   'desc' => 'Measurements & physical examination',      'required' => false],
        ['key' => 'assessment', 'color' => '#ff771d', 'letter' => 'A', 'km' => 'ការវាយតម្លៃ',  'en' => 'Assessment',  'desc' => 'Diagnosis & clinical interpretation',      'required' => false],
        ['key' => 'evaluation', 'color' => '#9b59b6', 'letter' => 'E', 'km' => 'ការប្រតិកម្ម', 'en' => 'Evaluation',  'desc' => 'Response to treatment so far',             'required' => false],
        ['key' => 'plan',       'color' => '#e74c3c', 'letter' => 'P', 'km' => 'ផែនការ',       'en' => 'Plan',        'desc' => 'Treatment & follow-up plan',               'required' => false],
    ];
@endphp

<x-step.card step-id="soap" :visit="$visit" :step-idx="$stepIdx" :steps="$steps"
    icon="bi-file-earmark-medical-fill" icon-color="#3498db"
    km="SOAP Notes" en="SOAP Documentation"
    :badge="$soap ? 'Saved · ' . $savedTime : null">

    @if($encounter)
    <div style="font-size:11px;color:#aaa;margin-bottom:16px">
        <i class="bi bi-link-45deg"></i>
        Encounter: <code style="color:#4154f1;font-size:11px">{{ $encounter->code }}</code>
        @if($encounter->started_at) · {{ $encounter->started_at->format('d/m/Y H:i') }} @endif
    </div>
    @endif

    @foreach($soapCards as $card)
    @php
        $val     = old($card['key'], $soap?->{$card['key']} ?? '');
        $filled  = !empty($val);
        $border  = $card['color'] . '33';
        $bg      = $card['color'] . '06';
    @endphp
    <div style="border:1.5px solid {{ $border }};border-radius:12px;padding:16px 18px;margin-bottom:14px;background:{{ $bg }}">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:8px">
            <div style="width:32px;height:32px;border-radius:8px;background:{{ $card['color'] }};color:#fff;display:flex;align-items:center;justify-content:center;font-size:16px;font-weight:900;flex-shrink:0">
                {{ $card['letter'] }}
            </div>
            <div style="flex:1">
                <div style="font-weight:800;font-size:13px;color:{{ $card['color'] }}">
                    {{ $card['km'] }}
                    <span style="font-weight:400;color:#aaa;font-size:12px">/ {{ $card['en'] }}</span>
                    @if($card['required'])<span class="req">*</span>@endif
                </div>
                <div style="font-size:10.5px;color:#bbb;margin-top:1px">{{ $card['desc'] }}</div>
            </div>
            @if($filled)
            <span style="font-size:9.5px;background:#e8f8ef;color:#2eca6a;padding:2px 8px;border-radius:10px;font-weight:700;flex-shrink:0">✓ Filled</span>
            @endif
        </div>
        <textarea name="{{ $card['key'] }}" class="form-control" rows="3"
                  placeholder="{{ $card['en'] }} notes…"
                  style="border-color:{{ $border }}"
                  {{ $card['required'] ? 'required data-error-msg="Subjective (S)"' : '' }}>{{ $val }}</textarea>
    </div>
    @endforeach

</x-step.card>
