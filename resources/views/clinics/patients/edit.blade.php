@extends('clinics.layout.app')
@section('title', 'Edit — ' . $patient->surname . ', ' . $patient->name)

@section('content')

@php $addr = $patient->address; @endphp

<x-page-header
    title="កែប្រែអ្នកជំងឺ"
    subtitle="Edit Patient"
    :breadcrumbs="[
        ['label' => 'ដើម',       'url' => url('/')],
        ['label' => 'អ្នកជំងឺ',  'url' => url('/patients')],
        ['label' => $patient->code, 'url' => url('/patients/' . $patient->code)],
        ['label' => 'Edit'],
    ]"
>
    <a href="{{ url('/patients/' . $patient->code) }}" class="btn btn-outline-primary btn-sm">
        <i class="bi bi-arrow-left"></i> Cancel
    </a>
</x-page-header>

@if($errors->any())
<div class="note note-danger mb-3">
    <i class="bi bi-exclamation-triangle-fill"></i>
    <ul style="margin:0;padding-left:16px;font-size:12px">
        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
    </ul>
</div>
@endif

<form method="POST" action="{{ url('/patients/' . $patient->code) }}" id="patientForm" novalidate>
@csrf @method('PATCH')

<div class="row g-3">

{{-- ── Left: core info ────────────────────────────────── --}}
<div class="col-12 col-lg-8">

  {{-- Identity --}}
  <div class="card-emr mb-3">
    <div class="card-hd">
      <div class="card-hd-title">
        <i class="bi bi-person-fill" style="color:#4154f1"></i>
        អត្តសញ្ញាណ <small style="font-weight:400;color:#aaa">/ Identity</small>
      </div>
    </div>
    <div class="card-bd">
      <div class="row g-3">
        {{-- Code is readonly on edit --}}
        <div class="col-12 col-sm-4">
          <x-form.field name="code_display" km="លេខអ្នកជំងឺ" en="Patient Code"
                        :value="$patient->code" :readonly="true"/>
        </div>
        <div class="col-6 col-sm-4">
          <x-form.field name="surname" km="នាមត្រកូល" en="Surname"
                        :value="old('surname', $patient->surname)" required/>
        </div>
        <div class="col-6 col-sm-4">
          <x-form.field name="name" km="ឈ្មោះ" en="Given Name"
                        :value="old('name', $patient->name)" required/>
        </div>
        <div class="col-6 col-sm-3">
          <x-form.select name="sex" km="ភេទ" en="Sex" required
              :options="['' => '— ជ្រើស —', 'M' => '♂ ប្រុស / Male', 'F' => '♀ ស្រី / Female']"
              :value="old('sex', $patient->gender)"/>
        </div>
        <div class="col-6 col-sm-3">
          <x-form.field name="birthdate" km="ថ្ងៃខែឆ្នាំ" en="Date of Birth"
                        type="date" :value="old('birthdate', $patient->birthdate?->format('Y-m-d'))"/>
        </div>
        <div class="col-6 col-sm-3">
          <x-form.field name="phone" km="ទូរស័ព្ទ" en="Phone"
                        type="tel" placeholder="012 345 678"
                        :value="old('phone', $patient->phone)"/>
        </div>
        <div class="col-6 col-sm-3">
          <x-form.field name="nationality" km="សញ្ជាតិ" en="Nationality"
                        :value="old('nationality', $patient->nationality ?? 'ខ្មែរ')"/>
        </div>
        <div class="col-6 col-sm-4">
          <x-form.select name="marital_status" km="ស្ថានភាពអាពាហ៍ពិពាហ៍" en="Marital Status"
              :options="['' => '—', 'Single' => 'នៅលីវ / Single', 'Married' => 'រៀបការ / Married', 'Widowed' => 'មេម៉ាយ / Widowed', 'Divorced' => 'លែងលះ / Divorced']"
              :value="old('marital_status', $patient->marital_status)"/>
        </div>
        <div class="col-6 col-sm-4">
          <x-form.field name="occupation" km="មុខរបរ" en="Occupation"
                        :value="old('occupation', $patient->occupation)"/>
        </div>
        <div class="col-12 col-sm-4">
          <x-form.field name="spid" km="លេខ SPID" en="Social Protection ID"
                        placeholder="HEF / NSSF card no."
                        :value="old('spid', $patient->spid)"/>
        </div>
      </div>
    </div>
  </div>

  {{-- Address --}}
  <div class="card-emr">
    <div class="card-hd">
      <div class="card-hd-title">
        <i class="bi bi-geo-alt-fill" style="color:#ff771d"></i>
        អាសយដ្ឋាន <small style="font-weight:400;color:#aaa">/ Address</small>
      </div>
    </div>
    <div class="card-bd">
      <div class="row g-3">
        <div class="col-6 col-md-3">
          <x-form.field name="province_name" km="ខេត្ត" en="Province"
                        :value="old('province_name', $addr?->province_name)"/>
        </div>
        <div class="col-6 col-md-3">
          <x-form.field name="district_name" km="ស្រុក" en="District"
                        :value="old('district_name', $addr?->district_name)"/>
        </div>
        <div class="col-6 col-md-3">
          <x-form.field name="commune_name" km="ឃុំ" en="Commune"
                        :value="old('commune_name', $addr?->commune_name)"/>
        </div>
        <div class="col-6 col-md-3">
          <x-form.field name="village_name" km="ភូមិ" en="Village"
                        :value="old('village_name', $addr?->village_name)"/>
        </div>
        <div class="col-6">
          <x-form.field name="house_number" km="លេខផ្ទះ" en="House No."
                        :value="old('house_number', $addr?->house_number)"/>
        </div>
        <div class="col-6">
          <x-form.field name="street_number" km="លេខផ្លូវ" en="Street No."
                        :value="old('street_number', $addr?->street_number)"/>
        </div>
      </div>
    </div>
  </div>

</div>{{-- /col-lg-8 --}}

{{-- ── Right: actions + audit ─────────────────────────── --}}
<div class="col-12 col-lg-4">
  <div class="card-emr" style="position:sticky;top:76px">
    <div class="card-hd" style="background:#f6f9ff">
      <div class="card-hd-title">
        <i class="bi bi-save-fill" style="color:#4154f1"></i>
        រក្សាទុក <small style="font-weight:400;color:#aaa">/ Save</small>
      </div>
    </div>
    <div class="card-bd">
      <button type="submit" class="btn btn-primary btn-w100 mb-2">
        <i class="bi bi-check2-circle"></i> រក្សាទុកការផ្លាស់ប្ដូរ / Save Changes
      </button>
      <a href="{{ url('/patients/' . $patient->code) }}" class="btn btn-outline-primary btn-w100 mb-3">
        <i class="bi bi-x-circle"></i> បោះបង់
      </a>

      <div style="border-top:1px solid #f0f2ff;padding-top:12px;font-size:11px;color:#aaa;line-height:1.8">
        <div><i class="bi bi-person" style="width:14px"></i> Code: <strong style="color:#555">{{ $patient->code }}</strong></div>
        @if($patient->created_at)
        <div><i class="bi bi-calendar-plus" style="width:14px"></i> Created: {{ $patient->created_at->format('d/m/Y') }}</div>
        @endif
        @if($patient->updated_at && $patient->updated_at->ne($patient->created_at))
        <div><i class="bi bi-pencil" style="width:14px"></i> Updated: {{ $patient->updated_at->format('d/m/Y H:i') }}</div>
        @endif
        <div style="margin-top:8px">
          <a href="{{ url('/visits?patient_code=' . $patient->code) }}" style="color:#4154f1;font-size:11px">
            <i class="bi bi-clock-history"></i> {{ $patient->visits_count ?? 0 }} visit(s)
          </a>
        </div>
      </div>
    </div>
  </div>
</div>

</div>{{-- /row --}}
</form>

@endsection
