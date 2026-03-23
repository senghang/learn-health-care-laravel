@php
    use App\Common\Constants\DateFormats;
    $sexValue   = $patient?->sex ?? '';
    $isIPDVisit = $isIPD ?? false;
@endphp

<x-step.card step-id="registration" :visit="$visit" :step-idx="$stepIdx" :steps="$steps"
             icon="bi-clipboard2-plus-fill" icon-color="#4154f1"
             km="ការចុះឈ្មោះ" en="Registration">

    {{-- Visit Info --}}
    <x-form.section title="Visit Info" km="ព័ត៌មានការចូល" color="#4154f1">
        <div class="row g-3">
            <div class="col-6 col-md-3">
                <x-form.field name="code" label="Code" km="លេខការចូល"
                              :value="$visit->code" :readonly="true"/>
            </div>
            <div class="col-6 col-md-3">
                <x-form.field name="visit_type" label="Type" km="ប្រភេទ"
                              :value="$visit->visit_type" :readonly="true"/>
            </div>
            <div class="col-6 col-md-3">
                <x-form.field name="admission_type" label="Admission" km="ប្រភពចូល"
                              :value="$visit->admission_type" :readonly="true"/>
            </div>
            <div class="col-6 col-md-3">
                <x-form.field type="datetime-local" name="admitted_at"
                              label="Admitted At" km="ថ្ងៃចូល"
                              :value="$visit->admitted_at?->format(DateFormats::INPUT_DATETIME)"
                              :readonly="true"/>
            </div>
        </div>
    </x-form.section>

    {{-- IPD / OPD specific --}}
    @if($isIPDVisit)
        <x-form.section title="Inpatient Details" km="ព័ត៌មាន IPD" color="#ff771d">
            <div class="row g-3">
                <div class="col-6 col-sm-4">
                    <x-form.field name="ward" label="Ward" km="សេវា"
                                  :value="$encounter?->ward"/>
                </div>
                <div class="col-6 col-sm-4">
                    <x-form.field name="bed" label="Bed No." km="គ្រែ"
                                  :value="$encounter?->bed"/>
                </div>
                <div class="col-12 col-sm-4">
                    <x-form.select name="service_type" label="Service Type" km="ប្រភេទសេវា"
                                   :options="['' => '— Select —', 'General Medicine' => 'General Medicine', 'Surgery' => 'Surgery', 'Paediatrics' => 'Paediatrics', 'ICU' => 'ICU', 'Emergency' => 'Emergency']"
                                   :value="$encounter?->service_type"/>
                </div>
            </div>
        </x-form.section>
    @else
        <x-form.section title="Outpatient Details" km="ព័ត៌មាន OPD" color="#4154f1">
            <div class="row g-3">
                <div class="col-12 col-sm-4">
                    <x-form.select name="service_type" label="Service Type" km="ប្រភេទសេវា"
                                   :options="['' => '— Select —', 'Consultation' => 'Consultation', 'Emergency' => 'Emergency', 'Follow-up' => 'Follow-up', 'Specialist' => 'Specialist']"
                                   :value="$encounter?->service_type"/>
                </div>
            </div>
        </x-form.section>
    @endif

    {{-- Demographics --}}
    <x-form.section title="Demographics" km="ព័ត៌មានអ្នកជំងឺ" color="#2eca6a">
        <div class="row g-3">
            <div class="col-6">
                <x-form.field name="surname" label="Surname" km="នាមត្រកូល"
                              :value="$patient?->surname" :required="true"/>
            </div>
            <div class="col-6">
                <x-form.field name="name" label="Given Name" km="ឈ្មោះ"
                              :value="$patient?->name" :required="true"/>
            </div>
            <div class="col-6 col-sm-4">
                <x-form.select name="sex" label="Gender" km="ភេទ" :required="true"
                               :options="['M' => 'ប្រុស / Male', 'F' => 'ស្រី / Female']"
                               :value="$sexValue"/>
            </div>
            <div class="col-6 col-sm-4">
                <x-form.field name="birthdate" label="Date of Birth" km="ថ្ងៃខែឆ្នាំ"
                              type="date" :required="true"
                              :value="old('birthdate', $patient?->birthdate?->format(DateFormats::INPUT_DATE))"/>
            </div>
            <div class="col-6">
                <x-form.field name="phone" label="Phone" km="ទូរស័ព្ទ"
                              :value="$patient?->phone"/>
            </div>
            <div class="col-6">
                <x-form.field name="nationality" label="Nationality" km="សញ្ជាតិ"
                              :value="$patient?->nationality ?? 'ខ្មែរ'"/>
            </div>
        </div>
    </x-form.section>

    {{-- Address --}}
    <x-form.section title="Address" km="អាសយដ្ឋាន" color="#ff771d">
        <div class="row g-3">
            <div class="col-6 col-md-3">
                <x-form.field name="province_name" label="Province" km="ខេត្ត"
                              :value="$address?->province_name"/>
            </div>
            <div class="col-6 col-md-3">
                <x-form.field name="district_name" label="District" km="ស្រុក"
                              :value="$address?->district_name"/>
            </div>
            <div class="col-6 col-md-3">
                <x-form.field name="commune_name" label="Commune" km="ឃុំ"
                              :value="$address?->commune_name"/>
            </div>
            <div class="col-6 col-md-3">
                <x-form.field name="village_name" label="Village" km="ភូមិ"
                              :value="$address?->village_name"/>
            </div>
            <div class="col-6">
                <x-form.field name="house_number" label="House No." km="លេខផ្ទះ"
                              :value="$address?->house_number"/>
            </div>
            <div class="col-6">
                <x-form.field name="street_number" label="Street No." km="លេខផ្លូវ"
                              :value="$address?->street_number"/>
            </div>
        </div>
    </x-form.section>

    {{-- Discharge --}}
    <x-form.section title="Discharge" km="ការចេញ" color="#9b59b6">
        <div class="row g-3">
            <div class="col-6">
                <x-form.select name="discharge_type" label="Discharge Type" km="ប្រភេទចេញ"
                               :options="['' => '—', 'Authorized' => 'Authorized', 'AMA' => 'AMA', 'Transfer' => 'Transfer', 'Death' => 'Death']"
                               :value="$visit->discharge_type"/>
            </div>
            <div class="col-6">
                <x-form.select name="visit_outcome" label="Outcome" km="លទ្ធផល"
                               :options="['' => '—', 'Improved' => 'Improved', 'Resolved' => 'Resolved', 'Stable' => 'Stable', 'Deteriorated' => 'Deteriorated', 'Death' => 'Death']"
                               :value="$visit->visit_outcome"/>
            </div>
            <div class="col-6">
                <x-form.field name="discharged_at" label="Discharged At" km="ថ្ងៃចេញ"
                              type="datetime-local"
                              :value="old('discharged_at', $visit->discharged_at?->format(DateFormats::INPUT_DATETIME))"/>
            </div>
            <div class="col-6">
                <x-form.field name="followup_at" label="Follow-up At" km="ការតាមដាន"
                              type="datetime-local"
                              :value="old('followup_at', $visit->followup_at?->format(DateFormats::INPUT_DATETIME))"/>
            </div>
        </div>
    </x-form.section>

</x-step.card>
