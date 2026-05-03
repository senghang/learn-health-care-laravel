@extends('clinics.layout.app')
@section('title', 'អ្នកជំងឺថ្មី / New Patient')

@section('content')

<x-ui.page-header
    km="ចុះឈ្មោះអ្នកជំងឺ"
    title="New Patient"
    :breadcrumbs="[
        ['label' => 'ដើម',      'url' => url('/')],
        ['label' => 'អ្នកជំងឺ', 'url' => url('/patients')],
        ['label' => 'ថ្មី'],
    ]">
    <x-slot:actions>
        <x-ui.button href="{{ url('/patients') }}" variant="secondary" size="sm">
            <x-slot:icon><i class="bi bi-arrow-left"></i></x-slot:icon>
            ត្រឡប់
        </x-ui.button>
    </x-slot:actions>
</x-ui.page-header>

@if($errors->any())
<x-ui.alert type="error" class="mb-3">
    <i class="bi bi-exclamation-triangle-fill"></i>
    <ul style="margin:0;padding-left:16px;font-size:12px">
        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
    </ul>
</x-ui.alert>
@endif

<form method="POST" action="{{ url('/patients') }}" id="patientForm" novalidate>
@csrf

<div class="row g-3">

{{-- ════════════════════════════════════════════════════════════════════════════
     LEFT COLUMN: Patient Info
     ════════════════════════════════════════════════════════════════════════════ --}}
<div class="col-12 col-lg-8">

  {{-- ── Identity Card ─────────────────────────────────────────────────────── --}}
  <x-ui.card title="Identity" km="អត្តសញ្ញាណ" icon="bi-person-fill" icon-color="#4154f1" class="mb-3">
    <div class="row g-3">
      {{-- Patient code (auto-generated, read-only) --}}
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
      <div class="col-6 col-sm-4">
        <x-form.field name="spid" km="លេខ SPID" en="Social Protection ID"
                      placeholder="HEF / NSSF card no."
                      :value="old('spid')"/>
      </div>
      <div class="col-6 col-sm-4">
        <x-form.select name="blood_type" km="ប្រភេទឈាម" en="Blood Type"
            :options="['' => '—', 'A+' => 'A+', 'A-' => 'A-', 'B+' => 'B+', 'B-' => 'B-', 'AB+' => 'AB+', 'AB-' => 'AB-', 'O+' => 'O+', 'O-' => 'O-']"
            :value="old('blood_type')"/>
      </div>
    </div>
  </x-ui.card>

  {{-- ── Address Card ──────────────────────────────────────────────────────── --}}
  <x-ui.card title="Address" km="អាសយដ្ឋាន" icon="bi-geo-alt-fill" icon-color="#ff771d" class="mb-3">
    <div class="row g-3">
      <div class="col-6 col-md-3">
        <x-form.field name="province_name" km="ខេត្ត" en="Province"
                      :value="old('province_name')"/>
      </div>
      <div class="col-6 col-md-3">
        <x-form.field name="district_name" km="ស្រុក" en="District"
                      :value="old('district_name')"/>
      </div>
      <div class="col-6 col-md-3">
        <x-form.field name="commune_name" km="ឃុំ" en="Commune"
                      :value="old('commune_name')"/>
      </div>
      <div class="col-6 col-md-3">
        <x-form.field name="village_name" km="ភូមិ" en="Village"
                      :value="old('village_name')"/>
      </div>
      <div class="col-6">
        <x-form.field name="house_number" km="លេខផ្ទះ" en="House No."
                      :value="old('house_number')"/>
      </div>
      <div class="col-6">
        <x-form.field name="street_number" km="លេខផ្លូវ" en="Street No."
                      :value="old('street_number')"/>
      </div>
    </div>
  </x-ui.card>

  {{-- ── Identifications Card ──────────────────────────────────────────────── --}}
  <x-ui.card title="ID Cards" km="ប័ណ្ណអត្តសញ្ញាណ" icon="bi-credit-card-fill" icon-color="#9b59b6" class="mb-3">
    <x-slot:actions>
      <x-ui.button type="button" variant="secondary" size="sm" onclick="addIdRow()">
        <x-slot:icon><i class="bi bi-plus-lg"></i></x-slot:icon>
        បន្ថែម
      </x-ui.button>
    </x-slot:actions>
    <div id="idCardContainer">
      @if(old('identifications'))
        @foreach(old('identifications') as $i => $card)
        <div class="row g-2 mb-2 id-row align-items-end">
          <div class="col-5">
            <x-form.select name="identifications[{{ $i }}][card_type]" km="ប្រភេទប័ណ្ណ" en="Card Type"
                :options="['' => '—', 'NID' => 'អត្តសញ្ញាណប័ណ្ណ / NID', 'Passport' => 'លិខិតឆ្លងដែន / Passport', 'HEF' => 'បណ្ណសមធម៌ / HEF Card', 'NSSF' => 'បណ្ណ NSSF', 'Other' => 'ផ្សេង / Other']"
                :value="$card['card_type'] ?? ''"/>
          </div>
          <div class="col-5">
            <x-form.field name="identifications[{{ $i }}][card_code]" km="លេខប័ណ្ណ" en="Card Number"
                          :value="$card['card_code'] ?? ''"/>
          </div>
          <div class="col-2">
            <x-ui.button type="button" variant="danger" size="sm" :full-width="true" onclick="this.closest('.id-row').remove()">
              <x-slot:icon><i class="bi bi-trash"></i></x-slot:icon>
            </x-ui.button>
          </div>
        </div>
        @endforeach
      @endif
      <div style="font-size:11px;color:#bbb;padding-top:4px" id="idCardEmpty">
        @if(!old('identifications'))
          <i class="bi bi-info-circle"></i> ចុច "បន្ថែម" ដើម្បីបញ្ចូលប័ណ្ណអត្តសញ្ញាណ / Click "Add" to enter ID cards
        @endif
      </div>
    </div>
  </x-ui.card>

  {{-- ── Contacts Card ─────────────────────────────────────────────────────── --}}
  <x-ui.card title="Emergency Contacts" km="ទំនាក់ទំនងបន្ទាន់" icon="bi-people-fill" icon-color="#2eca6a" class="mb-3">
    <x-slot:actions>
      <x-ui.button type="button" variant="secondary" size="sm" onclick="addContactRow()">
        <x-slot:icon><i class="bi bi-plus-lg"></i></x-slot:icon>
        បន្ថែម
      </x-ui.button>
    </x-slot:actions>
    <div id="contactContainer">
      @if(old('contacts'))
        @foreach(old('contacts') as $i => $c)
        <div class="row g-2 mb-2 contact-row align-items-end">
          <div class="col-12 col-sm-3">
            <x-form.field name="contacts[{{ $i }}][contact_name]" km="ឈ្មោះ" en="Name"
                          :value="$c['contact_name'] ?? ''"/>
          </div>
          <div class="col-6 col-sm-3">
            <x-form.field name="contacts[{{ $i }}][contact_phone]" km="ទូរស័ព្ទ" en="Phone"
                          :value="$c['contact_phone'] ?? ''"/>
          </div>
          <div class="col-6 col-sm-3">
            <x-form.select name="contacts[{{ $i }}][relationship]" km="ទំនាក់ទំនង" en="Relation"
                :options="['' => '—', 'Spouse' => 'ប្រពន្ធ/ប្តី / Spouse', 'Parent' => 'មាតា/បិតា / Parent', 'Child' => 'កូន / Child', 'Sibling' => 'បងប្អូន / Sibling', 'Guardian' => 'អាណាព្យាបាល / Guardian', 'Other' => 'ផ្សេង / Other']"
                :value="$c['relationship'] ?? ''"/>
          </div>
          <div class="col-6 col-sm-2">
            <div class="fld">
              <label class="flbl"><span class="en">Emergency?</span></label>
              <select name="contacts[{{ $i }}][is_emergency]" class="form-select">
                <option value="0">No</option>
                <option value="1" {{ ($c['is_emergency'] ?? 0) ? 'selected' : '' }}>Yes</option>
              </select>
            </div>
          </div>
          <div class="col-6 col-sm-1">
            <x-ui.button type="button" variant="danger" size="sm" :full-width="true" onclick="this.closest('.contact-row').remove()">
              <x-slot:icon><i class="bi bi-trash"></i></x-slot:icon>
            </x-ui.button>
          </div>
        </div>
        @endforeach
      @endif
      <div style="font-size:11px;color:#bbb;padding-top:4px" id="contactEmpty">
        @if(!old('contacts'))
          <i class="bi bi-info-circle"></i> ចុច "បន្ថែម" ដើម្បីបញ្ចូលទំនាក់ទំនង / Click "Add" to enter contacts
        @endif
      </div>
    </div>
  </x-ui.card>

</div>{{-- /col-lg-8 --}}

{{-- ════════════════════════════════════════════════════════════════════════════
     RIGHT COLUMN: Actions sidebar
     ════════════════════════════════════════════════════════════════════════════ --}}
<div class="col-12 col-lg-4">
  <x-ui.card title="Actions" km="សកម្មភាព" icon="bi-save-fill" icon-color="#4154f1" style="position:sticky;top:76px">
    <x-ui.button type="submit" variant="primary" :full-width="true" class="mb-2">
      <x-slot:icon><i class="bi bi-check2-circle"></i></x-slot:icon>
      រក្សាទុក / Save Patient
    </x-ui.button>
    <x-ui.button href="{{ url('/patients') }}" variant="secondary" :full-width="true">
      <x-slot:icon><i class="bi bi-x-circle"></i></x-slot:icon>
      បោះបង់ / Cancel
    </x-ui.button>

    <div style="border-top:1px solid #f0f2ff;margin-top:14px;padding-top:12px;font-size:11px;color:#aaa;line-height:1.8">
      <div><i class="bi bi-tag" style="width:14px"></i> Code: <strong style="color:#4154f1;font-family:monospace">{{ $nextCode }}</strong></div>
      <div><i class="bi bi-calendar" style="width:14px"></i> Date: {{ now()->format('d/m/Y') }}</div>
      <div><i class="bi bi-person" style="width:14px"></i> By: {{ auth()->user()->name ?? 'Staff' }}</div>
    </div>

    {{-- Quick tips --}}
    <div style="border-top:1px solid #f0f2ff;margin-top:12px;padding-top:10px">
      <div style="font-size:10px;font-weight:700;color:#bbb;text-transform:uppercase;margin-bottom:6px">Required Fields</div>
      <div style="font-size:11px;color:#888;line-height:1.7">
        <i class="bi bi-asterisk" style="color:#e74c3c;font-size:8px"></i> Surname<br>
        <i class="bi bi-asterisk" style="color:#e74c3c;font-size:8px"></i> Given Name<br>
        <i class="bi bi-asterisk" style="color:#e74c3c;font-size:8px"></i> Sex
      </div>
    </div>
  </x-ui.card>
</div>{{-- /col-lg-4 --}}

</div>{{-- /row --}}
</form>

@endsection

@push('scripts')
<script>
// ── Dynamic ID card rows ──────────────────────────────────────────────────────
let idIdx = {{ count(old('identifications', [])) }};

function addIdRow() {
    const container = document.getElementById('idCardContainer');
    const empty = document.getElementById('idCardEmpty');
    if (empty) empty.style.display = 'none';

    const html = `
    <div class="row g-2 mb-2 id-row align-items-end">
      <div class="col-5">
        <div class="fld">
          <label class="flbl"><span class="km">ប្រភេទប័ណ្ណ</span><span class="en">/ Card Type</span></label>
          <select name="identifications[${idIdx}][card_type]" class="form-select">
            <option value="">—</option>
            <option value="NID">អត្តសញ្ញាណប័ណ្ណ / NID</option>
            <option value="Passport">លិខិតឆ្លងដែន / Passport</option>
            <option value="HEF">បណ្ណសមធម៌ / HEF Card</option>
            <option value="NSSF">បណ្ណ NSSF</option>
            <option value="Other">ផ្សេង / Other</option>
          </select>
        </div>
      </div>
      <div class="col-5">
        <div class="fld">
          <label class="flbl"><span class="km">លេខប័ណ្ណ</span><span class="en">/ Card Number</span></label>
          <input type="text" name="identifications[${idIdx}][card_code]" class="form-control" placeholder="Enter card number"/>
        </div>
      </div>
      <div class="col-2">
        <button type="button" class="btn btn-sm btn-outline-danger btn-w100" onclick="this.closest('.id-row').remove()">
          <i class="bi bi-trash"></i>
        </button>
      </div>
    </div>`;

    container.insertAdjacentHTML('beforeend', html);
    idIdx++;
}

// ── Dynamic contact rows ──────────────────────────────────────────────────────
let cIdx = {{ count(old('contacts', [])) }};

function addContactRow() {
    const container = document.getElementById('contactContainer');
    const empty = document.getElementById('contactEmpty');
    if (empty) empty.style.display = 'none';

    const html = `
    <div class="row g-2 mb-2 contact-row align-items-end">
      <div class="col-12 col-sm-3">
        <div class="fld">
          <label class="flbl"><span class="km">ឈ្មោះ</span><span class="en">/ Name</span></label>
          <input type="text" name="contacts[${cIdx}][contact_name]" class="form-control"/>
        </div>
      </div>
      <div class="col-6 col-sm-3">
        <div class="fld">
          <label class="flbl"><span class="km">ទូរស័ព្ទ</span><span class="en">/ Phone</span></label>
          <input type="tel" name="contacts[${cIdx}][contact_phone]" class="form-control"/>
        </div>
      </div>
      <div class="col-6 col-sm-3">
        <div class="fld">
          <label class="flbl"><span class="km">ទំនាក់ទំនង</span><span class="en">/ Relation</span></label>
          <select name="contacts[${cIdx}][relationship]" class="form-select">
            <option value="">—</option>
            <option value="Spouse">ប្រពន្ធ/ប្តី / Spouse</option>
            <option value="Parent">មាតា/បិតា / Parent</option>
            <option value="Child">កូន / Child</option>
            <option value="Sibling">បងប្អូន / Sibling</option>
            <option value="Guardian">អាណាព្យាបាល / Guardian</option>
            <option value="Other">ផ្សេង / Other</option>
          </select>
        </div>
      </div>
      <div class="col-6 col-sm-2">
        <div class="fld">
          <label class="flbl"><span class="en">Emergency?</span></label>
          <select name="contacts[${cIdx}][is_emergency]" class="form-select">
            <option value="0">No</option>
            <option value="1">Yes</option>
          </select>
        </div>
      </div>
      <div class="col-6 col-sm-1">
        <button type="button" class="btn btn-sm btn-outline-danger btn-w100" onclick="this.closest('.contact-row').remove()">
          <i class="bi bi-trash"></i>
        </button>
      </div>
    </div>`;

    container.insertAdjacentHTML('beforeend', html);
    cIdx++;
}
</script>
@endpush
