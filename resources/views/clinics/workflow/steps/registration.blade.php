@php
    use App\Common\Constants\DateFormats;
    $sexValue   = $patient?->gender ?? '';
    $isIPDVisit = $isIPD ?? false;
@endphp

<div class="card-hd" style="flex-wrap:wrap;gap:8px;padding:14px 18px 10px">
    <div style="flex:1;min-width:0">
        <div class="card-hd-title">
            <i class="bi bi-clipboard2-plus-fill" style="color:#4154f1"></i>
            ការចុះឈ្មោះ
            <small style="font-size:11px;color:#bbb;font-weight:400">/ Registration</small>
        </div>
        <div style="font-size:10.5px;color:#aaa;margin-top:3px">
            ជំហាន {{ $stepIdx+1 }} នៃ {{ count($steps) }} / Step {{ $stepIdx+1 }} of {{ count($steps) }}
        </div>
    </div>
</div>
<div style="height:3px;background:#f0f2ff">
    <div style="height:100%;width:{{ round($stepIdx/count($steps)*100) }}%;background:linear-gradient(90deg,#4154f1,#717ff5)"></div>
</div>

<div class="card-bd">
    {{-- FIXED: url() instead of route() for form action --}}
    <form id="stepForm" method="POST"
          action="{{ url('/workflow/'.$visit->code.'/registration/save') }}">
        @csrf
        @method('PATCH')

        {{-- Visit Info (read-only) --}}
        <x-form.section title="Visit Info" km="ព័ត៌មានការចូល" color="#4154f1">
            <div class="row g-3">
                <div class="col-6 col-md-3">
                    <x-form.input name="code" label="Code" km="លេខការចូល"
                                  :value="$visit->code" :readonly="true"/>
                </div>
                <div class="col-6 col-md-3">
                    <x-form.input name="visit_type" label="Type" km="ប្រភេទ"
                                  :value="$visit->visit_type" :readonly="true"/>
                </div>
                <div class="col-6 col-md-3">
                    <x-form.input name="admission_type" label="Admission" km="ប្រភពចូល"
                                  :value="$visit->admission_type" :readonly="true"/>
                </div>
                <div class="col-6 col-md-3">
                    <x-form.input type="datetime-local" name="admitted_at_display"
                                  label="Admitted At" km="ថ្ងៃចូល"
                                  :value="$visit->admitted_at?->format(DateFormats::DATETIME_TIME)"
                                  :readonly="true"/>
                </div>
            </div>
        </x-form.section>

        {{-- IPD / OPD --}}
        @if($isIPDVisit)
        <x-form.section title="Inpatient Details" km="ព័ត៌មាន IPD" color="#ff771d">
            <div class="row g-3">
                <div class="col-6 col-sm-4">
                    <x-form.input name="ward" label="Ward" km="វ៉ាត" :value="$encounter?->name"/>
                </div>
                <div class="col-6 col-sm-4">
                    <x-form.input name="bed" label="Bed No." km="គ្រែ" :value="$encounter?->bed"/>
                </div>
                <div class="col-12 col-sm-4">
                    <x-form.select name="service_type" label="Service Type" km="ប្រភេទសេវា"
                        :options="[''=>'—','General Medicine'=>'General Medicine','Surgery'=>'Surgery','Paediatrics'=>'Paediatrics','ICU'=>'ICU','Emergency'=>'Emergency']"
                        :value="$encounter?->service_type"/>
                </div>
            </div>
        </x-form.section>
        @else
        <x-form.section title="Outpatient Details" km="ព័ត៌មាន OPD" color="#4154f1">
            <div class="row g-3">
                <div class="col-12 col-sm-4">
                    <x-form.select name="service_type" label="Service Type" km="ប្រភេទសេវា"
                        :options="[''=>'—','Consultation'=>'Consultation','Emergency'=>'Emergency','Follow-up'=>'Follow-up','Specialist'=>'Specialist']"
                        :value="$encounter?->service_type"/>
                </div>
            </div>
        </x-form.section>
        @endif

        {{-- Patient Demographics --}}
        <x-form.section title="Patient Demographics" km="ព័ត៌មានអ្នកជំងឺ" color="#2eca6a">
            <div class="row g-3">
                <div class="col-6 col-sm-4">
                    <x-form.input name="surname" label="Surname" km="នាមត្រកូល"
                                  :value="$patient?->surname" :required="true"/>
                </div>
                <div class="col-6 col-sm-4">
                    <x-form.input name="name" label="Given Name" km="ឈ្មោះ"
                                  :value="$patient?->name" :required="true"/>
                </div>
                <div class="col-6 col-sm-4">
                    <x-form.select name="sex" label="Gender" km="ភេទ"
                        :options="[''=>'—','M'=>'ប្រុស / Male','F'=>'ស្រី / Female']"
                        :value="$sexValue" :required="true"/>
                </div>
                <div class="col-6 col-sm-4">
                    <x-form.input type="date" name="birthdate" label="Date of Birth" km="ថ្ងៃខែ"
                                  :value="old('birthdate', $patient?->birthdate?->format(DateFormats::DATE))"/>
                </div>
                <div class="col-6 col-sm-4">
                    <x-form.input name="phone" label="Phone" km="ទូរស័ព្ទ"
                                  :value="$patient?->phone" placeholder="012 345 678"/>
                </div>
                <div class="col-6 col-sm-4">
                    <x-form.input name="nationality" label="Nationality" km="សញ្ជាតិ"
                                  :value="$patient?->nationality ?? 'ខ្មែរ'"/>
                </div>
                <div class="col-6 col-sm-4">
                    <x-form.input name="occupation" label="Occupation" km="មុខរបរ"
                                  :value="$patient?->occupation"/>
                </div>
                <div class="col-6 col-sm-4">
                    <x-form.select name="marital_status" label="Marital Status" km="ស្ថានភាពអាពាហ៍"
                        :options="[''=>'—','Single'=>'Single / នៅលីវ','Married'=>'Married / រៀបការ','Divorced'=>'Divorced / លែងលះ','Widowed'=>'Widowed / មេម៉ាយ']"
                        :value="$patient?->marital_status"/>
                </div>
            </div>
        </x-form.section>

        {{-- Address --}}
        <x-form.section title="Address (NCDD)" km="អាសយដ្ឋាន" color="#ff771d">
            <div class="row g-3">
                <div class="col-6 col-md-3">
                    <x-form.input name="province_name" label="Province" km="ខេត្ត" :value="$address?->province_name"/>
                </div>
                <div class="col-6 col-md-3">
                    <x-form.input name="district_name" label="District" km="ស្រុក" :value="$address?->district_name"/>
                </div>
                <div class="col-6 col-md-3">
                    <x-form.input name="commune_name" label="Commune" km="ឃុំ" :value="$address?->commune_name"/>
                </div>
                <div class="col-6 col-md-3">
                    <x-form.input name="village_name" label="Village" km="ភូមិ" :value="$address?->village_name"/>
                </div>
                <div class="col-6">
                    <x-form.input name="house_number" label="House No." km="លេខផ្ទះ" :value="$address?->house_number"/>
                </div>
                <div class="col-6">
                    <x-form.input name="street_number" label="Street No." km="លេខផ្លូវ" :value="$address?->street_number"/>
                </div>
            </div>
        </x-form.section>

        {{-- Discharge --}}
        <x-form.section title="Discharge" km="ការចេញ" color="#9b59b6">
            <x-alert type="info">ទំនេរទុក ឬ បំពេញនៅពេលចេញ / Leave blank — fill when discharging.</x-alert>
            <div class="row g-3">
                <div class="col-6 col-md-3">
                    <x-form.select name="discharge_type" label="Discharge Type" km="ប្រភេទចេញ"
                        :options="[''=>'—','Authorized'=>'Authorized','AMA'=>'AMA','Transfer'=>'Transfer','Death'=>'Death']"
                        :value="$visit->discharge_type"/>
                </div>
                <div class="col-6 col-md-3">
                    <x-form.select name="visit_outcome" label="Outcome" km="លទ្ធផល"
                        :options="[''=>'—','Improved'=>'Improved','Resolved'=>'Resolved','Stable'=>'Stable','Deteriorated'=>'Deteriorated','Death'=>'Death']"
                        :value="$visit->visit_outcome"/>
                </div>
                <div class="col-6 col-md-3">
                    <x-form.input type="datetime-local" name="discharged_at" label="Discharged At" km="ថ្ងៃចេញ"
                                  :value="old('discharged_at', $visit->discharged_at?->format(DateFormats::DATETIME_TIME))"/>
                </div>
                <div class="col-6 col-md-3">
                    <x-form.input type="datetime-local" name="followup_at" label="Follow-up At" km="ការតាមដាន"
                                  :value="old('followup_at', $visit->followup_at?->format(DateFormats::DATETIME_TIME))"/>
                </div>
            </div>
        </x-form.section>

    </form>
</div>
