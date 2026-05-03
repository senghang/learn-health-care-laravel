@extends('clinics.layout.app')
@section('title', 'កែប្រែ ' . $patient->surname . ', ' . $patient->name)

@section('content')

@php $addr = $patient->address; @endphp

<x-ui.page-header
    km="កែប្រែអ្នកជំងឺ"
    title="Edit Patient — {{ $patient->code }}"
    :breadcrumbs="[
        ['label' => 'ដើម',      'url' => url('/')],
        ['label' => 'អ្នកជំងឺ', 'url' => url('/patients')],
        ['label' => $patient->code, 'url' => url('/patients/' . $patient->code)],
        ['label' => 'Edit'],
    ]">
    <x-slot:actions>
        <x-ui.button href="{{ url('/patients/' . $patient->code) }}" variant="secondary" size="sm">
            <x-slot:icon><i class="bi bi-arrow-left"></i></x-slot:icon>
            Cancel
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

<form method="POST" action="{{ url('/patients/' . $patient->code) }}" id="patientForm" novalidate>
@csrf @method('PATCH')

<div class="row g-3">

{{-- ════════════════════════════════════════════════════════════════════════════
     LEFT COLUMN: Patient Info
     ════════════════════════════════════════════════════════════════════════════ --}}
<div class="col-12 col-lg-8">

  {{-- ── Identity ──────────────────────────────────────────────────────────── --}}
  <x-ui.card title="Identity" km="អត្តសញ្ញាណ" icon="bi-person-fill" icon-color="#4154f1" class="mb-3">
    <div class="row g-3">
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
            :value="old('sex', $patient->sex)"/>
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
      <div class="col-6 col-sm-4">
        <x-form.field name="spid" km="លេខ SPID" en="Social Protection ID"
                      placeholder="HEF / NSSF card no."
                      :value="old('spid', $patient->spid)"/>
      </div>
      <div class="col-6 col-sm-4">
        <x-form.select name="blood_type" km="ប្រភេទឈាម" en="Blood Type"
            :options="['' => '—', 'A+' => 'A+', 'A-' => 'A-', 'B+' => 'B+', 'B-' => 'B-', 'AB+' => 'AB+', 'AB-' => 'AB-', 'O+' => 'O+', 'O-' => 'O-']"
            :value="old('blood_type', $patient->blood_type)"/>
      </div>
      <div class="col-6 col-sm-4">
        <x-form.select name="status" km="ស្ថានភាព" en="Status"
            :options="['Active' => 'Active', 'Inactive' => 'Inactive']"
            :value="old('status', $patient->status)"/>
      </div>
    </div>
  </x-ui.card>

  {{-- ── Address ───────────────────────────────────────────────────────────── --}}
  <x-ui.card title="Address" km="អាសយដ្ឋាន" icon="bi-geo-alt-fill" icon-color="#ff771d" class="mb-3">
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
  </x-ui.card>

  {{-- ── Identifications ───────────────────────────────────────────────────── --}}
  <x-ui.card title="ID Cards" km="ប័ណ្ណអត្តសញ្ញាណ" icon="bi-credit-card-fill" icon-color="#9b59b6" class="mb-3">
    <x-slot:actions>
      <x-ui.button type="button" variant="secondary" size="sm" onclick="addIdRow()">
        <x-slot:icon><i class="bi bi-plus-lg"></i></x-slot:icon>
        បន្ថែម
      </x-ui.button>
    </x-slot:actions>
    <div id="idCardContainer">
      @php $existingIds = old('identifications', $patient->identifications->map(fn($id) => ['id' => $id->id, 'card_type' => $id->card_type, 'card_code' => $id->card_code])->toArray()); @endphp
      @foreach($existingIds as $i => $card)
      <div class="row g-2 mb-2 id-row align-items-end">
        @if(!empty($card['id']))
        <input type="hidden" name="identifications[{{ $i }}][id]" value="{{ $card['id'] }}"/>
        @endif
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
      @if(empty($existingIds))
      <div style="font-size:11px;color:#bbb;padding-top:4px" id="idCardEmpty">
        <i class="bi bi-info-circle"></i> គ្មានប័ណ្ណ / No ID cards recorded
      </div>
      @endif
    </div>
  </x-ui.card>

  {{-- ── Contacts ──────────────────────────────────────────────────────────── --}}
  <x-ui.card title="Emergency Contacts" km="ទំនាក់ទំនងបន្ទាន់" icon="bi-people-fill" icon-color="#2eca6a" class="mb-3">
    <x-slot:actions>
      <x-ui.button type="button" variant="secondary" size="sm" onclick="addContactRow()">
        <x-slot:icon><i class="bi bi-plus-lg"></i></x-slot:icon>
        បន្ថែម
      </x-ui.button>
    </x-slot:actions>
    <div id="contactContainer">
      @php $existingContacts = old('contacts', $patient->contacts->map(fn($c) => ['id' => $c->id, 'contact_name' => $c->contact_name, 'contact_phone' => $c->contact_phone, 'relationship' => $c->relationship, 'is_emergency' => $c->is_emergency])->toArray()); @endphp
      @foreach($existingContacts as $i => $c)
      <div class="row g-2 mb-2 contact-row align-items-end">
        @if(!empty($c['id']))
        <input type="hidden" name="contacts[{{ $i }}][id]" value="{{ $c['id'] }}"/>
        @endif
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
      @if(empty($existingContacts))
      <div style="font-size:11px;color:#bbb;padding-top:4px" id="contactEmpty">
        <i class="bi bi-info-circle"></i> គ្មានទំនាក់ទំនង / No contacts recorded
      </div>
      @endif
    </div>
  </x-ui.card>

</div>{{-- /col-lg-8 --}}

{{-- ════════════════════════════════════════════════════════════════════════════
     RIGHT COLUMN: Actions
     ════════════════════════════════════════════════════════════════════════════ --}}
<div class="col-12 col-lg-4">
  <x-ui.card title="Actions" km="សកម្មភាព" icon="bi-save-fill" icon-color="#4154f1" style="position:sticky;top:76px">
    <x-ui.button type="submit" variant="primary" :full-width="true" class="mb-2">
      <x-slot:icon><i class="bi bi-check2-circle"></i></x-slot:icon>
      រក្សាទុកការកែប្រែ / Save Changes
    </x-ui.button>
    <x-ui.button href="{{ url('/patients/' . $patient->code) }}" variant="secondary" :full-width="true">
      <x-slot:icon><i class="bi bi-x-circle"></i></x-slot:icon>
      បោះបង់ / Cancel
    </x-ui.button>

    <div style="border-top:1px solid #f0f2ff;margin-top:14px;padding-top:12px;font-size:11px;color:#aaa;line-height:1.8">
      <div><i class="bi bi-tag" style="width:14px"></i> Code: <strong style="color:#4154f1;font-family:monospace">{{ $patient->code }}</strong></div>
      <div><i class="bi bi-calendar" style="width:14px"></i> Created: {{ $patient->created_at?->format('d/m/Y') }}</div>
      <div><i class="bi bi-pencil" style="width:14px"></i> Updated: {{ $patient->updated_at?->format('d/m/Y H:i') }}</div>
    </div>
  </x-ui.card>
</div>

</div>{{-- /row --}}
</form>

@endsection

@push('scripts')
<script>
// ── Dynamic ID card rows ──────────────────────────────────────────────────────
let idIdx = {{ count($existingIds ?? []) }};

function addIdRow() {
    const c = document.getElementById('idCardContainer');
    const e = document.getElementById('idCardEmpty');
    if (e) e.style.display = 'none';

    c.insertAdjacentHTML('beforeend', `
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
          <input type="text" name="identifications[${idIdx}][card_code]" class="form-control"/>
        </div>
      </div>
      <div class="col-2">
        <button type="button" class="btn btn-sm btn-outline-danger btn-w100" onclick="this.closest('.id-row').remove()">
          <i class="bi bi-trash"></i>
        </button>
      </div>
    </div>`);
    idIdx++;
}

// ── Dynamic contact rows ──────────────────────────────────────────────────────
let cIdx = {{ count($existingContacts ?? []) }};

function addContactRow() {
    const c = document.getElementById('contactContainer');
    const e = document.getElementById('contactEmpty');
    if (e) e.style.display = 'none';

    c.insertAdjacentHTML('beforeend', `
    <div class="row g-2 mb-2 contact-row align-items-end">
      <div class="col-12 col-sm-3">
        <div class="fld"><label class="flbl"><span class="km">ឈ្មោះ</span><span class="en">/ Name</span></label>
          <input type="text" name="contacts[${cIdx}][contact_name]" class="form-control"/></div>
      </div>
      <div class="col-6 col-sm-3">
        <div class="fld"><label class="flbl"><span class="km">ទូរស័ព្ទ</span><span class="en">/ Phone</span></label>
          <input type="tel" name="contacts[${cIdx}][contact_phone]" class="form-control"/></div>
      </div>
      <div class="col-6 col-sm-3">
        <div class="fld"><label class="flbl"><span class="km">ទំនាក់ទំនង</span><span class="en">/ Relation</span></label>
          <select name="contacts[${cIdx}][relationship]" class="form-select">
            <option value="">—</option><option value="Spouse">Spouse</option><option value="Parent">Parent</option>
            <option value="Child">Child</option><option value="Sibling">Sibling</option>
            <option value="Guardian">Guardian</option><option value="Other">Other</option>
          </select></div>
      </div>
      <div class="col-6 col-sm-2">
        <div class="fld"><label class="flbl"><span class="en">Emergency?</span></label>
          <select name="contacts[${cIdx}][is_emergency]" class="form-select">
            <option value="0">No</option><option value="1">Yes</option>
          </select></div>
      </div>
      <div class="col-6 col-sm-1">
        <button type="button" class="btn btn-sm btn-outline-danger btn-w100" onclick="this.closest('.contact-row').remove()">
          <i class="bi bi-trash"></i>
        </button>
      </div>
    </div>`);
    cIdx++;
}
</script>
@endpush
