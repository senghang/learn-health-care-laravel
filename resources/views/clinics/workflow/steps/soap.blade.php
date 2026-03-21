{{--
    Step: SOAP Notes
    Variables from SoapStep::viewData():
      $soap      — Soap model|null
      $encounter — Outpatient model|null
      $visit     — Visit model
      $stepIdx, $steps
--}}
@php
    $soapCards = [
        ['key'=>'subjective', 'color'=>'#4154f1', 'letter'=>'S', 'km'=>'ការរៀបរាប់',   'en'=>'Subjective',  'desc'=>'អ្វីដែលអ្នកជំងឺរៀបរាប់ / Patient-reported symptoms & complaints'],
        ['key'=>'objective',  'color'=>'#2eca6a', 'letter'=>'O', 'km'=>'ការរក្ខ',      'en'=>'Objective',   'desc'=>'ការវាស់ ការពិនិត្យ / Measurements & physical examination findings'],
        ['key'=>'assessment', 'color'=>'#ff771d', 'letter'=>'A', 'km'=>'ការវាយតម្លៃ',  'en'=>'Assessment',  'desc'=>'រោគវិនិច្ឆ័យ / Diagnosis & clinical interpretation'],
        ['key'=>'evaluation', 'color'=>'#9b59b6', 'letter'=>'E', 'km'=>'ការប្រតិកម្ម', 'en'=>'Evaluation',  'desc'=>'ការឆ្លើយតប / Response to treatment so far'],
        ['key'=>'plan',       'color'=>'#e74c3c', 'letter'=>'P', 'km'=>'ផែនការ',       'en'=>'Plan',        'desc'=>'ការព្យាបាល ការតាមដាន / Treatment & follow-up plan'],
    ];
@endphp

<div class="card-hd" style="flex-wrap:wrap;gap:8px;padding:14px 18px 10px">
    <div style="flex:1;min-width:0">
        <div class="card-hd-title">
            <i class="bi bi-file-earmark-medical-fill" style="color:#3498db"></i>SOAP Notes
            <small style="font-size:11px;color:#bbb;font-weight:400">/ SOAP Documentation</small>
        </div>
        <div style="font-size:10.5px;color:#aaa;margin-top:3px">
            ជំហាន {{ $stepIdx+1 }} នៃ {{ count($steps) }} / Step {{ $stepIdx+1 }} of {{ count($steps) }}
        </div>
    </div>
    @if($soap)
        <span
            style="font-size:11px;background:#e8f8ef;color:#2eca6a;padding:3px 10px;border-radius:20px;border:1px solid #a8e6c2;font-weight:700;flex-shrink:0">
        <i class="bi bi-check-circle-fill"></i> Saved · {{ $soap->updated_at?->format('d/m H:i') }}
    </span>
    @endif
</div>
<div style="height:3px;background:#f0f2ff">
    <div
        style="height:100%;width:{{ round($stepIdx/count($steps)*100) }}%;background:linear-gradient(90deg,#4154f1,#717ff5)"></div>
</div>

<div class="card-bd">

    @if($encounter)
        <div style="font-size:11px;color:#aaa;margin-bottom:16px">
            <i class="bi bi-link-45deg"></i>
            Encounter: <code style="color:#4154f1;font-size:11px">{{ $encounter->code }}</code>
            @if($encounter->started_at)
                · {{ $encounter->started_at->format('d/m/Y H:i') }}
            @endif
        </div>
    @endif

    <form id="stepForm" method="POST"
          action="{{ route('workflow.step.save', [$visit->code, $currentStep ?? 'soap']) }}">
        @csrf @method('PATCH')

        @foreach($soapCards as $card)
            @php $savedValue = old($card['key'], $soap?->{$card['key']} ?? ''); @endphp
            <div
                style="border:1.5px solid {{ $card['color'] }}33;border-radius:12px;padding:16px 18px;margin-bottom:14px;background:{{ $card['color'] }}06">
                <div style="display:flex;align-items:baseline;gap:10px;margin-bottom:6px">
                    <div
                        style="width:32px;height:32px;border-radius:8px;background:{{ $card['color'] }};color:#fff;display:flex;align-items:center;justify-content:center;font-size:16px;font-weight:900;flex-shrink:0">
                        {{ $card['letter'] }}
                    </div>
                    <div>
                        <div style="font-weight:800;font-size:13px;color:{{ $card['color'] }}">
                            {{ $card['km'] }} <span
                                style="font-weight:400;color:#aaa;font-size:12px">/ {{ $card['en'] }}</span>
                        </div>
                        <div style="font-size:10.5px;color:#bbb;margin-top:1px">{{ $card['desc'] }}</div>
                    </div>
                    @if($savedValue)
                        <span
                            style="margin-left:auto;font-size:9.5px;background:#e8f8ef;color:#2eca6a;padding:2px 8px;border-radius:10px;font-weight:700;flex-shrink:0">✓ Filled</span>
                    @endif
                </div>
                <textarea name="{{ $card['key'] }}" class="form-control" rows="3"
                          placeholder="{{ $card['en'] }} notes…"
                          style="border-color:{{ $card['color'] }}33">{{ $savedValue }}</textarea>
            </div>
        @endforeach

    </form>
</div>
