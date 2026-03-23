@extends('clinics.layout.app')
@section('title', ' អ្នកជំងឺថ្មី / New Patient')

@section('content')

<x-page-header
    title="ចុះឈ្មោះអ្នកជំងឺ"
    subtitle="New Patient"
    :breadcrumbs="[
        ['label' => 'ដើម',      'url' => url('/')],
        ['label' => 'អ្នកជំងឺ', 'url' => url('/patients')],
        ['label' => 'ថ្មី'],
    ]"
>
    <a href="{{ url('/patients') }}" class="btn btn-outline-primary btn-sm">
        <i class="bi bi-arrow-left"></i> ត្រឡប់
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

<form method="POST" action="{{ url('/patients') }}" id="patientForm" novalidate>
@csrf

<div class="row g-3">

{{-- ── Left: core info ─────────────────────────────────── --}}
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
        <div class="col-12 col-sm-4">
          <div class="fld">
            <label class="flbl">
              <span class="km">លេខអ្នកជំងឺ</span>
              <span class="en">/ Patient Code</span>
              <span style="font-size:9px;background:#e8f8ef;color:#1D9E75;padding:1px 6px;border-radius:8px;margin-left:4px">AUTO</span>
            </label>
            <div class="form-control ro" style="color:#4154f1;font-family:monospace;font-weight:700">
              {{ $nextCode }}
            </div>
          </div>
        </div>
        <div class="col-6 col-sm-4">
          <x-form.field name="surname" km="នាមត្រកូល" en="Surname"
                        :value="old('surname')" required/>
        </div>
        <div class="col-6 col-sm-4">
          <x-form.field name="name" km="ឈ្មោះ" en="Given Name"
                        :value="old('name')" required/>
        </div>
        <div class="col-6 col-sm-3">
          <x-form.select name="sex" km="ភេទ" en="Sex" required
              :options="['' => '— ជ្រើស —', 'M' => '♂ ប្រុស / Male', 'F' => '♀ ស្រី / Female']"
              :value="old('sex')"/>
        </div>
        <div class="col-6 col-sm-3">
          <x-form.field name="birthdate" km="ថ្ងៃខែឆ្នាំ" en="Date of Birth"
                        type="date" :value="old('birthdate')"/>
        </div>
        <div class="col-6 col-sm-3">
          <x-form.field name="phone" km="ទូរស័ព្ទ" en="Phone"
                        type="tel" placeholder="012 345 678"
                        :value="old('phone')"/>
        </div>
        <div class="col-6 col-sm-3">
          <x-form.field name="nationality" km="សញ្ជាតិ" en="Nationality"
                        :value="old('nationality', 'ខ្មែរ')"/>
        </div>
        <div class="col-6 col-sm-4">
          <x-form.select name="marital_status" km="ស្ថានភាពអាពាហ៍ពិពាហ៍" en="Marital Status"
              :options="['' => '—', 'Single' => 'នៅលីវ / Single', 'Married' => 'រៀបការ / Married', 'Widowed' => 'មេម៉ាយ / Widowed', 'Divorced' => 'លែងលះ / Divorced']"
              :value="old('marital_status')"/>
        </div>
        <div class="col-6 col-sm-4">
          <x-form.field name="occupation" km="មុខរបរ" en="Occupation"
                        :value="old('occupation')"/>
        </div>
        <div class="col-12 col-sm-4">
          <x-form.field name="spid" km="លេខ SPID" en="Social Protection ID"
                        placeholder="HEF / NSSF card no."
                        :value="old('spid')"/>
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
          <x-form.field name="province_name" km="ខេត្ត" en="Province" :value="old('province_name')"/>
        </div>
        <div class="col-6 col-md-3">
          <x-form.field name="district_name" km="ស្រុក" en="District" :value="old('district_name')"/>
        </div>
        <div class="col-6 col-md-3">
          <x-form.field name="commune_name" km="ឃុំ" en="Commune" :value="old('commune_name')"/>
        </div>
        <div class="col-6 col-md-3">
          <x-form.field name="village_name" km="ភូមិ" en="Village" :value="old('village_name')"/>
        </div>
        <div class="col-6">
          <x-form.field name="house_number" km="លេខផ្ទះ" en="House No." :value="old('house_number')"/>
        </div>
        <div class="col-6">
          <x-form.field name="street_number" km="លេខផ្លូវ" en="Street No." :value="old('street_number')"/>
        </div>
      </div>
    </div>
  </div>

</div>{{-- /col-lg-8 --}}

{{-- ── Right: summary card ─────────────────────────────── --}}
<div class="col-12 col-lg-4">
  <div class="card-emr" style="position:sticky;top:76px">
    <div class="card-hd" style="background:#f6f9ff">
      <div class="card-hd-title">
        <i class="bi bi-info-circle-fill" style="color:#4154f1"></i>
        ព័ត៌មានសំខាន់ <small style="font-weight:400;color:#aaa">/ Quick Guide</small>
      </div>
    </div>
    <div class="card-bd" style="font-size:12.5px">
      <div class="note note-info mb-3" style="font-size:11.5px">
        <i class="bi bi-asterisk" style="font-size:9px;flex-shrink:0;margin-top:3px"></i>
        <span>លេខអ្នកជំងឺ + នាមត្រកូល + ឈ្មោះ + ភេទ ជាតម្រូវការចាំបាច់</span>
      </div>

      <div style="color:#888;line-height:1.8;font-size:11.5px">
        <div><strong style="color:#012970">លេខ SPID</strong> — HEF / NSSF card number</div>
        <div><strong style="color:#012970">លេខផ្ទះ / ផ្លូវ</strong> — optional</div>
        <div><strong style="color:#012970">ខេត្ត-ស្រុក-ឃុំ-ភូមិ</strong> — type in Khmer or English</div>
      </div>

      <div style="margin-top:20px;padding-top:16px;border-top:1px solid #f0f2ff">
        <button type="submit" class="btn btn-primary btn-w100 mb-2">
          <i class="bi bi-person-plus-fill"></i> ចុះឈ្មោះ / Register Patient
        </button>
        <a href="{{ url('/patients') }}" class="btn btn-outline-primary btn-w100">
          <i class="bi bi-x-circle"></i> បោះបង់
        </a>
      </div>

      <div style="margin-top:16px;padding-top:12px;border-top:1px solid #f0f2ff;font-size:11px;color:#aaa">
        <i class="bi bi-shield-check" style="color:#2eca6a"></i>
        ទិន្នន័យចូល {{ currentClinic()?->name ?? '' }} តែប៉ុណ្ណោះ
      </div>
    </div>
  </div>
</div>

</div>{{-- /row --}}
</form>

@endsection
