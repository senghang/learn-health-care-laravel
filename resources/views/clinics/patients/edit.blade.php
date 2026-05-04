@extends('clinics.layout.app')
@section('title', 'Edit — ' . $patient->surname . ', ' . $patient->name)

@section('content')

@php $addr = $patient->address; @endphp

<x-ui.page-header
    km="កែប្រែអ្នកជំងឺ"
    title="Edit Patient — {{ $patient->code }}"
    :breadcrumbs="[
        ['label' => __('app.home'), 'url' => route('dashboard')],
        ['label' => 'Patients', 'url' => url('/patients')],
        ['label' => $patient->code, 'url' => url('/patients/' . $patient->code)],
        ['label' => 'Edit'],
    ]">
    <x-slot:actions>
        <x-ui.button href="{{ url('/patients/' . $patient->code) }}" variant="secondary" size="sm">
            <x-slot:icon><i class="bi bi-arrow-left" aria-hidden="true"></i></x-slot:icon>
            Cancel
        </x-ui.button>
    </x-slot:actions>
</x-ui.page-header>

@if($errors->any())
    <x-ui.alert type="error" class="mb-4">
        <ul class="list-disc pl-4 text-xs space-y-0.5">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </x-ui.alert>
@endif

<form method="POST" action="{{ url('/patients/' . $patient->code) }}" id="patientForm" novalidate>
@csrf @method('PATCH')

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

    {{-- ── LEFT: Patient Info ─────────────────────────────────── --}}
    <div class="lg:col-span-2 space-y-4">

        {{-- Identity --}}
        <x-ui.card>
            <x-slot:header>
                <div class="flex items-center gap-2 px-5 py-4" style="border-bottom:1px solid #e6e9f0">
                    <i class="bi bi-person-fill" style="color:#4154f1;font-size:15px" aria-hidden="true"></i>
                    <span class="text-sm font-bold" style="color:#1a1f36">អត្តសញ្ញាណ / Identity</span>
                </div>
            </x-slot:header>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">

                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold" style="color:#374151">លេខអ្នកជំងឺ</label>
                    <div class="w-full text-sm rounded-lg border border-[#e2e8f0] bg-[#f9fafb] px-3 py-2.5 font-mono font-bold"
                         style="color:#4154f1">{{ $patient->code }}</div>
                </div>

                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold" style="color:#374151">
                        នាមត្រកូល / Surname <span style="color:#ef4444">*</span>
                    </label>
                    <x-forms.input name="surname" :value="old('surname', $patient->surname)" required />
                    @error('surname')<p class="text-xs" style="color:#ef4444">{{ $message }}</p>@enderror
                </div>

                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold" style="color:#374151">
                        ឈ្មោះ / Given Name <span style="color:#ef4444">*</span>
                    </label>
                    <x-forms.input name="name" :value="old('name', $patient->name)" required />
                    @error('name')<p class="text-xs" style="color:#ef4444">{{ $message }}</p>@enderror
                </div>

                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold" style="color:#374151">
                        ភេទ / Sex <span style="color:#ef4444">*</span>
                    </label>
                    <x-forms.select name="sex" required>
                        <option value="">— ជ្រើស —</option>
                        <option value="M" @selected(old('sex', $patient->sex) === 'M')>♂ ប្រុស / Male</option>
                        <option value="F" @selected(old('sex', $patient->sex) === 'F')>♀ ស្រី / Female</option>
                    </x-forms.select>
                </div>

                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold" style="color:#374151">ថ្ងៃខែឆ្នាំ / Date of Birth</label>
                    <x-forms.input type="date" name="birthdate"
                                   :value="old('birthdate', $patient->birthdate?->format('Y-m-d'))" />
                </div>

                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold" style="color:#374151">ទូរស័ព្ទ / Phone</label>
                    <x-forms.input type="tel" name="phone" placeholder="012 345 678"
                                   :value="old('phone', $patient->phone)" />
                </div>

                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold" style="color:#374151">សញ្ជាតិ / Nationality</label>
                    <x-forms.input name="nationality" :value="old('nationality', $patient->nationality ?? 'ខ្មែរ')" />
                </div>

                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold" style="color:#374151">ស្ថានភាព / Marital Status</label>
                    <x-forms.select name="marital_status">
                        <option value="">—</option>
                        <option value="Single"   @selected(old('marital_status', $patient->marital_status) === 'Single')>Single</option>
                        <option value="Married"  @selected(old('marital_status', $patient->marital_status) === 'Married')>Married</option>
                        <option value="Widowed"  @selected(old('marital_status', $patient->marital_status) === 'Widowed')>Widowed</option>
                        <option value="Divorced" @selected(old('marital_status', $patient->marital_status) === 'Divorced')>Divorced</option>
                    </x-forms.select>
                </div>

                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold" style="color:#374151">មុខរបរ / Occupation</label>
                    <x-forms.input name="occupation" :value="old('occupation', $patient->occupation)" />
                </div>

                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold" style="color:#374151">លេខ SPID</label>
                    <x-forms.input name="spid" placeholder="HEF / NSSF card no." :value="old('spid', $patient->spid)" />
                </div>

                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold" style="color:#374151">ប្រភេទឈាម / Blood Type</label>
                    <x-forms.select name="blood_type">
                        <option value="">—</option>
                        @foreach(['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $bt)
                            <option value="{{ $bt }}" @selected(old('blood_type', $patient->blood_type) === $bt)>{{ $bt }}</option>
                        @endforeach
                    </x-forms.select>
                </div>

                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold" style="color:#374151">ស្ថានភាព / Status</label>
                    <x-forms.select name="status">
                        <option value="Active"   @selected(old('status', $patient->status) === 'Active')>Active</option>
                        <option value="Inactive" @selected(old('status', $patient->status) === 'Inactive')>Inactive</option>
                    </x-forms.select>
                </div>
            </div>
        </x-ui.card>

        {{-- Address --}}
        <x-ui.card>
            <x-slot:header>
                <div class="flex items-center gap-2 px-5 py-4" style="border-bottom:1px solid #e6e9f0">
                    <i class="bi bi-geo-alt-fill" style="color:#ff771d;font-size:15px" aria-hidden="true"></i>
                    <span class="text-sm font-bold" style="color:#1a1f36">អាសយដ្ឋាន / Address</span>
                </div>
            </x-slot:header>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                @foreach([
                    ['province_name', 'ខេត្ត / Province',     $addr?->province_name],
                    ['district_name', 'ស្រុក / District',     $addr?->district_name],
                    ['commune_name',  'ឃុំ / Commune',        $addr?->commune_name],
                    ['village_name',  'ភូមិ / Village',       $addr?->village_name],
                    ['house_number',  'លេខផ្ទះ / House No.',  $addr?->house_number],
                    ['street_number', 'លេខផ្លូវ / Street No.',$addr?->street_number],
                ] as [$field, $label, $current])
                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold" style="color:#374151">{{ $label }}</label>
                        <x-forms.input name="{{ $field }}" :value="old($field, $current)" />
                    </div>
                @endforeach
            </div>
        </x-ui.card>

        {{-- ID Cards --}}
        @php
            $existingIds = old('identifications', $patient->identifications->map(fn($id) => [
                'id' => $id->id, 'card_type' => $id->card_type, 'card_code' => $id->card_code
            ])->toArray());
        @endphp
        <x-ui.card>
            <x-slot:header>
                <div class="flex items-center justify-between px-5 py-4" style="border-bottom:1px solid #e6e9f0">
                    <div class="flex items-center gap-2">
                        <i class="bi bi-credit-card-fill" style="color:#9b59b6;font-size:15px" aria-hidden="true"></i>
                        <span class="text-sm font-bold" style="color:#1a1f36">ប័ណ្ណអត្តសញ្ញាណ / ID Cards</span>
                    </div>
                    <x-ui.button type="button" variant="secondary" size="sm" onclick="addIdRow()">
                        <x-slot:icon><i class="bi bi-plus-lg" aria-hidden="true"></i></x-slot:icon>
                        Add
                    </x-ui.button>
                </div>
            </x-slot:header>
            <div id="idCardContainer" class="space-y-2">
                @foreach($existingIds as $i => $card)
                    <div class="id-row flex items-end gap-2">
                        @if(!empty($card['id']))
                            <input type="hidden" name="identifications[{{ $i }}][id]" value="{{ $card['id'] }}" />
                        @endif
                        <div class="flex-1 space-y-1.5">
                            <label class="block text-xs font-semibold" style="color:#374151">ប្រភេទប័ណ្ណ</label>
                            <x-forms.select name="identifications[{{ $i }}][card_type]">
                                <option value="">—</option>
                                @foreach(['NID' => 'NID', 'Passport' => 'Passport', 'HEF' => 'HEF Card', 'NSSF' => 'NSSF', 'Other' => 'Other'] as $v => $l)
                                    <option value="{{ $v }}" @selected(($card['card_type'] ?? '') === $v)>{{ $l }}</option>
                                @endforeach
                            </x-forms.select>
                        </div>
                        <div class="flex-1 space-y-1.5">
                            <label class="block text-xs font-semibold" style="color:#374151">លេខប័ណ្ណ</label>
                            <x-forms.input name="identifications[{{ $i }}][card_code]" :value="$card['card_code'] ?? ''" />
                        </div>
                        <button type="button" onclick="this.closest('.id-row').remove()"
                                class="flex-shrink-0 w-9 h-9 flex items-center justify-center rounded-lg border border-[#fca5a5] hover:bg-[#fde8e8] transition-colors text-xs"
                                style="color:#e74c3c">
                            <i class="bi bi-trash" aria-hidden="true"></i>
                        </button>
                    </div>
                @endforeach
                @if(empty($existingIds))
                    <div id="idCardEmpty" class="text-xs py-1" style="color:#9ca3af">
                        <i class="bi bi-info-circle" aria-hidden="true"></i> No ID cards recorded
                    </div>
                @endif
            </div>
        </x-ui.card>

        {{-- Emergency Contacts --}}
        @php
            $existingContacts = old('contacts', $patient->contacts->map(fn($c) => [
                'id' => $c->id, 'contact_name' => $c->contact_name, 'contact_phone' => $c->contact_phone,
                'relationship' => $c->relationship, 'is_emergency' => $c->is_emergency,
            ])->toArray());
        @endphp
        <x-ui.card>
            <x-slot:header>
                <div class="flex items-center justify-between px-5 py-4" style="border-bottom:1px solid #e6e9f0">
                    <div class="flex items-center gap-2">
                        <i class="bi bi-people-fill" style="color:#2eca6a;font-size:15px" aria-hidden="true"></i>
                        <span class="text-sm font-bold" style="color:#1a1f36">ទំនាក់ទំនងបន្ទាន់ / Contacts</span>
                    </div>
                    <x-ui.button type="button" variant="secondary" size="sm" onclick="addContactRow()">
                        <x-slot:icon><i class="bi bi-plus-lg" aria-hidden="true"></i></x-slot:icon>
                        Add
                    </x-ui.button>
                </div>
            </x-slot:header>
            <div id="contactContainer" class="space-y-2">
                @foreach($existingContacts as $i => $c)
                    <div class="contact-row grid gap-2 items-end"
                         style="grid-template-columns: 1fr 1fr 1fr auto">
                        @if(!empty($c['id']))
                            <input type="hidden" name="contacts[{{ $i }}][id]" value="{{ $c['id'] }}" />
                        @endif
                        <div class="space-y-1.5">
                            <label class="block text-xs font-semibold" style="color:#374151">Name</label>
                            <x-forms.input name="contacts[{{ $i }}][contact_name]" :value="$c['contact_name'] ?? ''" />
                        </div>
                        <div class="space-y-1.5">
                            <label class="block text-xs font-semibold" style="color:#374151">Phone</label>
                            <x-forms.input name="contacts[{{ $i }}][contact_phone]" :value="$c['contact_phone'] ?? ''" />
                        </div>
                        <div class="space-y-1.5">
                            <label class="block text-xs font-semibold" style="color:#374151">Relation</label>
                            <x-forms.select name="contacts[{{ $i }}][relationship]">
                                <option value="">—</option>
                                @foreach(['Spouse','Parent','Child','Sibling','Guardian','Other'] as $rel)
                                    <option value="{{ $rel }}" @selected(($c['relationship'] ?? '') === $rel)>{{ $rel }}</option>
                                @endforeach
                            </x-forms.select>
                        </div>
                        <button type="button" onclick="this.closest('.contact-row').remove()"
                                class="flex-shrink-0 w-9 h-9 flex items-center justify-center rounded-lg border border-[#fca5a5] hover:bg-[#fde8e8] transition-colors text-xs mb-0.5"
                                style="color:#e74c3c">
                            <i class="bi bi-trash" aria-hidden="true"></i>
                        </button>
                    </div>
                @endforeach
                @if(empty($existingContacts))
                    <div id="contactEmpty" class="text-xs py-1" style="color:#9ca3af">
                        <i class="bi bi-info-circle" aria-hidden="true"></i> No contacts recorded
                    </div>
                @endif
            </div>
        </x-ui.card>

    </div>{{-- /left --}}

    {{-- ── RIGHT: Actions Sidebar ───────────────────────────────── --}}
    <div>
        <x-ui.card class="sticky top-20">
            <x-slot:header>
                <div class="flex items-center gap-2 px-5 py-4" style="border-bottom:1px solid #e6e9f0">
                    <i class="bi bi-save-fill" style="color:#4154f1;font-size:15px" aria-hidden="true"></i>
                    <span class="text-sm font-bold" style="color:#1a1f36">Save Changes</span>
                </div>
            </x-slot:header>

            <div class="space-y-2">
                <x-ui.button type="submit" variant="primary" :fullWidth="true">
                    <x-slot:icon><i class="bi bi-check2-circle" aria-hidden="true"></i></x-slot:icon>
                    Save Changes
                </x-ui.button>
                <x-ui.button href="{{ url('/patients/' . $patient->code) }}" variant="secondary" :fullWidth="true">
                    <x-slot:icon><i class="bi bi-x-circle" aria-hidden="true"></i></x-slot:icon>
                    Cancel
                </x-ui.button>
            </div>

            <div class="mt-4 pt-4 space-y-2" style="border-top:1px solid #e6e9f0">
                <div class="flex items-center justify-between text-xs">
                    <span style="color:#6b7280">Code</span>
                    <code class="font-mono font-bold px-1.5 py-0.5 rounded" style="background:#eef0fd;color:#4154f1">{{ $patient->code }}</code>
                </div>
                <div class="flex items-center justify-between text-xs">
                    <span style="color:#6b7280">Created</span>
                    <span style="color:#374151">{{ $patient->created_at?->format('d/m/Y') }}</span>
                </div>
                <div class="flex items-center justify-between text-xs">
                    <span style="color:#6b7280">Updated</span>
                    <span style="color:#374151">{{ $patient->updated_at?->format('d/m/Y H:i') }}</span>
                </div>
            </div>
        </x-ui.card>
    </div>

</div>{{-- /grid --}}
</form>

@push('scripts')
<script>
var inputCls = 'w-full text-sm rounded-lg border border-[#e2e8f0] bg-white px-3 py-2.5 text-[#374151] placeholder-[#9ca3af] focus:outline-none transition-colors';
var selectCls = inputCls + ' appearance-none';
var trashBtnCls = 'flex-shrink-0 w-9 h-9 flex items-center justify-center rounded-lg border hover:bg-[#fde8e8] transition-colors text-xs';

var idIdx = {{ count($existingIds ?? []) }};

function addIdRow() {
    var container = document.getElementById('idCardContainer');
    var empty = document.getElementById('idCardEmpty');
    if (empty) empty.style.display = 'none';
    var div = document.createElement('div');
    div.className = 'id-row flex items-end gap-2';
    div.innerHTML =
        '<div class="flex-1 space-y-1">'
        + '<label class="block text-xs font-semibold" style="color:#374151">ប្រភេទប័ណ្ណ</label>'
        + '<div class="relative"><select name="identifications[' + idIdx + '][card_type]" class="' + selectCls + '" style="padding-right:2.5rem">'
        + '<option value="">—</option><option value="NID">NID</option><option value="Passport">Passport</option>'
        + '<option value="HEF">HEF Card</option><option value="NSSF">NSSF</option><option value="Other">Other</option>'
        + '</select><div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none"><i class="bi bi-chevron-down text-xs" style="color:#6b7280"></i></div></div></div>'
        + '<div class="flex-1 space-y-1"><label class="block text-xs font-semibold" style="color:#374151">លេខប័ណ្ណ</label>'
        + '<input type="text" name="identifications[' + idIdx + '][card_code]" class="' + inputCls + '"/></div>'
        + '<button type="button" onclick="this.closest(\'.id-row\').remove()" class="' + trashBtnCls + '" style="color:#e74c3c;border-color:#fca5a5"><i class="bi bi-trash"></i></button>';
    container.appendChild(div);
    idIdx++;
}

var cIdx = {{ count($existingContacts ?? []) }};

function addContactRow() {
    var container = document.getElementById('contactContainer');
    var empty = document.getElementById('contactEmpty');
    if (empty) empty.style.display = 'none';
    var div = document.createElement('div');
    div.className = 'contact-row grid gap-2 items-end';
    div.style.cssText = 'grid-template-columns: 1fr 1fr 1fr auto';
    div.innerHTML =
        '<div class="space-y-1"><label class="block text-xs font-semibold" style="color:#374151">Name</label>'
        + '<input type="text" name="contacts[' + cIdx + '][contact_name]" class="' + inputCls + '"/></div>'
        + '<div class="space-y-1"><label class="block text-xs font-semibold" style="color:#374151">Phone</label>'
        + '<input type="tel" name="contacts[' + cIdx + '][contact_phone]" class="' + inputCls + '"/></div>'
        + '<div class="space-y-1"><label class="block text-xs font-semibold" style="color:#374151">Relation</label>'
        + '<div class="relative"><select name="contacts[' + cIdx + '][relationship]" class="' + selectCls + '" style="padding-right:2.5rem">'
        + '<option value="">—</option><option value="Spouse">Spouse</option><option value="Parent">Parent</option>'
        + '<option value="Child">Child</option><option value="Sibling">Sibling</option>'
        + '<option value="Guardian">Guardian</option><option value="Other">Other</option>'
        + '</select><div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none"><i class="bi bi-chevron-down text-xs" style="color:#6b7280"></i></div></div></div>'
        + '<button type="button" onclick="this.closest(\'.contact-row\').remove()" class="' + trashBtnCls + ' mb-0.5" style="color:#e74c3c;border-color:#fca5a5"><i class="bi bi-trash"></i></button>';
    container.appendChild(div);
    cIdx++;
}
</script>
@endpush

@endsection
