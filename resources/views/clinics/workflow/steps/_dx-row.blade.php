{{--
    Partial: _dx-row
    Used by diagnosis.blade.php for both saved rows and the default empty row.
    Variables: $i, $dx (DiagnosisModel|null), $typeOptions, $nowFormatted, $currentUser
--}}
@php
    $typeLabel = $dx ? match($dx->diagnosis_type) {
        'Primary'   => 'ចម្បង / Primary',
        'Secondary' => 'ទ្វីបដ្ឋ / Secondary',
        default     => $dx->diagnosis_type,
    } : 'ចម្បង / Primary';

    $dxCode    = $dx?->diagnosis_code ?? '';
    $dxName    = $dx?->diagnosis_name ?? '';
    $dxBy      = $dx?->diagnosed_by ?? $currentUser;
    $dxAt      = $dx?->diagnosed_at?->format('Y-m-d\TH:i') ?? $nowFormatted;
    $dxDesc    = $dx?->diagnosis_description ?? '';
    $dxType    = $dx?->diagnosis_type ?? 'Primary';
@endphp

<div class="sec-block dx-block" data-idx="{{ $i }}"
     style="border-left:4px solid #e74c3c44;background:#fde8e808;border-radius:10px;padding:14px 16px;margin-bottom:10px">

    <div style="display:flex;align-items:center;gap:8px;margin-bottom:12px">
        <span style="width:9px;height:9px;border-radius:50%;background:#e74c3c;display:inline-block;flex-shrink:0"></span>
        <strong style="color:#c0392b;font-size:13px">
            {{ $typeLabel }} — Dx #{{ $i + 1 }}
        </strong>
        @if($dxCode)
        <code style="background:#e74c3c;color:#fff;padding:2px 9px;border-radius:20px;font-size:11px">{{ $dxCode }}</code>
        @endif
        @if($i > 0)
        <button type="button" class="btn btn-sm btn-outline-danger ms-auto"
                onclick="removeDxRow({{ $i }})" style="font-size:11px;padding:2px 8px">✕ លុប</button>
        @endif
    </div>

    <div class="row g-3">
        <div class="col-6 col-sm-3">
            <x-form.select name="diagnoses[{{ $i }}][type]"
                km="ប្រភេទ" en="Type" :required="true"
                :options="$typeOptions" :value="$dxType"/>
        </div>
        <div class="col-6 col-sm-2">
            <x-form.field name="diagnoses[{{ $i }}][code]"
                en="ICD-10" placeholder="B50.0" :value="$dxCode"/>
        </div>
        <div class="col-12 col-sm-7">
            <x-form.field name="diagnoses[{{ $i }}][name]"
                km="ឈ្មោះ" en="Name" :required="true"
                error-msg="Diagnosis Name"
                placeholder="Plasmodium falciparum malaria"
                :value="$dxName"/>
        </div>
        <div class="col-6">
            <x-form.field name="diagnoses[{{ $i }}][diagnosed_at]"
                km="ពេលវេលា" en="At"
                type="datetime-local" :value="$dxAt"/>
        </div>
        <div class="col-6">
            <x-form.field name="diagnoses[{{ $i }}][diagnosed_by]"
                km="ដោយ" en="By" :value="$dxBy"/>
        </div>
        <div class="col-12">
            <x-form.field name="diagnoses[{{ $i }}][description]"
                km="ការពណ៌នា" en="Description" :value="$dxDesc"/>
        </div>
    </div>

</div>
