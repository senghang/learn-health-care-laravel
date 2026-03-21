@php
    $triage = $triage ?? null;

    $recordedAt    = old('recorded_at', df_input_dt($triage?->recorded_at) ?? '');
    $recordedBy    = old('recorded_by', $triage?->recorded_by ?? '');
    $height        = old('height',      $triage?->height ?? '');
    $weight        = old('weight',      $triage?->weight ?? '');
    $complaint     = old('chief_complaint', $triage?->chief_complaint ?? '');
    $title         = old('title',       $triage?->title ?? '');
    $encounterCode = $triage?->encounter_code ?? ('TR' . $visit->code);
@endphp

<x-step.card step-id="triage" :visit="$visit" :step-idx="$stepIdx" :steps="$steps"
    icon="bi-heart-pulse-fill" icon-color="#2eca6a"
    km="ការពិនិត្យចូល" en="Triage">

    <x-step.note type="info">
        ការត្អូញត្អែររបស់អ្នកជំងឺ — ចំណោទ ហើយការវាស់ស្ទង់ / Chief complaint and initial measurements
    </x-step.note>

    <div class="row g-3">
        <div class="col-6">
            <x-form.field name="_encounter_code" km="លេខ Encounter" en="Encounter Code"
                          :value="$encounterCode" :readonly="true"/>
        </div>
        <div class="col-6">
            <x-form.field name="recorded_by" km="ពិនិត្យដោយ" en="Recorded By"
                          :value="$recordedBy" placeholder="ឈ្មោះ / Name"/>
        </div>
        <div class="col-6">
            <x-form.field name="recorded_at" km="ថ្ងៃម៉ោង" en="Recorded At"
                          type="datetime-local" :value="$recordedAt"/>
        </div>
        <div class="col-6">
            <x-form.select name="title" km="តួរបស់" en="Title"
                :options="['វេជ្ជបណ្ឌិត / Doctor' => 'វេជ្ជបណ្ឌិត / Doctor', 'គិលានុបដ្ឋាយិកា / Nurse' => 'គិលានុបដ្ឋាយិកា / Nurse', 'Paramedic' => 'Paramedic']"
                :value="$title"/>
        </div>
        <div class="col-6">
            <x-form.field name="height" km="កម្ពស់ (CM)" en="Height"
                          type="number" placeholder="170" :value="$height"/>
        </div>
        <div class="col-6">
            <x-form.field name="weight" km="ទម្ងន់ (KG)" en="Weight"
                          type="number" placeholder="65" :value="$weight"/>
        </div>
        <div class="col-12">
            <x-form.field name="chief_complaint" km="ហេតុការណ៍ចូល" en="Chief Complaint"
                          :textarea="true" rows="3" :required="true"
                          error-msg="Chief Complaint"
                          placeholder="ពណ៌នា… / Describe…"
                          :value="$complaint"/>
        </div>
    </div>

</x-step.card>
