@extends('clinics.layout.app')
@section('title', 'New Patient')

@section('content')

<x-ui.page-header
    km="ចុះឈ្មោះអ្នកជំងឺ"
    title="New Patient"
    :breadcrumbs="[
        ['label' => __('app.home'), 'url' => route('dashboard')],
        ['label' => 'Patients',    'url' => url('/patients')],
        ['label' => 'New'],
    ]">
    <x-slot:actions>
        <x-ui.button href="{{ url('/patients') }}" variant="secondary" size="sm">
            <x-slot:icon><i class="bi bi-arrow-left" aria-hidden="true"></i></x-slot:icon>
            Back
        </x-ui.button>
    </x-slot:actions>
</x-ui.page-header>

@if($errors->any())
    <x-ui.alert type="error" class="mb-4" title="Please fix the following errors">
        <ul class="list-disc pl-4 text-xs space-y-0.5 mt-1">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </x-ui.alert>
@endif

<form method="POST" action="{{ url('/patients') }}" id="patientForm" novalidate>
@csrf

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

    {{-- ── LEFT COLUMN — Patient Data ────────────────────────────── --}}
    <div class="lg:col-span-2 space-y-4">

        {{-- ─ Identity ─────────────────────────────────────────── --}}
        <x-ui.card>
            <x-slot:header>
                <x-ui.card-header km="អត្តសញ្ញាណ" label="Identity" icon="bi-person-fill" iconColor="#4154f1" />
            </x-slot:header>

            <div class="grid grid-cols-2 sm:grid-cols-3 gap-x-4 gap-y-3">

                {{-- Auto code — readonly display field --}}
                <x-forms.field name="" km="លេខអ្នកជំងឺ" label="Patient Code" badge="auto">
                    <div class="flex items-center w-full text-sm rounded-md border px-3"
                         style="height:40px;color:#4154f1;font-family:var(--font-mono,monospace);font-weight:700;
                                background:var(--bg-app,#F8FAFC);border-color:var(--border-subtle,#E2E8F0)">
                        {{ $nextCode }}
                    </div>
                </x-forms.field>

                {{-- Pair: Surname + Given Name ──────────────────────────────── --}}
                {{-- col-span-2 tinted box communicates "these two fields belong together" --}}
                <div class="sm:col-span-2 field-pair grid grid-cols-2 gap-3">

                    <x-forms.field name="surname" km="នាមត្រកូល" label="Surname" required>
                        <x-forms.input id="surname" name="surname" :value="old('surname')"
                                       required data-req="surname" autocomplete="family-name" />
                    </x-forms.field>

                    <x-forms.field name="name" km="ឈ្មោះ" label="Given Name" required>
                        <x-forms.input id="name" name="name" :value="old('name')"
                                       required data-req="name" autocomplete="given-name" />
                    </x-forms.field>

                </div>

                {{-- Sex --}}
                <x-forms.field name="sex" km="ភេទ" label="Sex" required>
                    <x-forms.select id="sex" name="sex" required data-req="sex">
                        <option value="" @if(!old('sex')) selected @endif disabled>ជ្រើស / Choose…</option>
                        <option value="M" @selected(old('sex') === 'M')>♂ ប្រុស / Male</option>
                        <option value="F" @selected(old('sex') === 'F')>♀ ស្រី / Female</option>
                    </x-forms.select>
                </x-forms.field>

                {{-- Date of birth --}}
                <x-forms.field name="birthdate" km="ថ្ងៃខែឆ្នាំ" label="Date of Birth">
                    <x-forms.input id="birthdate" type="date" name="birthdate"
                                   :value="old('birthdate')" />
                </x-forms.field>

                {{-- Phone --}}
                <x-forms.field name="phone" km="ទូរស័ព្ទ" label="Phone">
                    <x-forms.input id="phone" type="tel" name="phone"
                                   placeholder="012 345 678" :value="old('phone')" autocomplete="tel" />
                </x-forms.field>

                {{-- Nationality --}}
                <x-forms.field name="nationality" km="សញ្ជាតិ" label="Nationality">
                    <x-forms.input id="nationality" name="nationality"
                                   :value="old('nationality', 'ខ្មែរ')" />
                </x-forms.field>

                {{-- Marital status --}}
                <x-forms.field name="marital_status" km="ស្ថានភាព" label="Marital Status">
                    <x-forms.select id="marital_status" name="marital_status">
                        <option value="">— គ្មាន / None</option>
                        <option value="Single"   @selected(old('marital_status') === 'Single')>នៅលីវ / Single</option>
                        <option value="Married"  @selected(old('marital_status') === 'Married')>រៀបការ / Married</option>
                        <option value="Widowed"  @selected(old('marital_status') === 'Widowed')>មេម៉ាយ / Widowed</option>
                        <option value="Divorced" @selected(old('marital_status') === 'Divorced')>លែងលះ / Divorced</option>
                    </x-forms.select>
                </x-forms.field>

                {{-- Occupation --}}
                <x-forms.field name="occupation" km="មុខរបរ" label="Occupation">
                    <x-forms.input id="occupation" name="occupation" :value="old('occupation')" />
                </x-forms.field>

                {{-- SPID --}}
                <x-forms.field name="spid" km="លេខ SPID" label="SPID No."
                               hint="HEF / NSSF card number">
                    <x-forms.input id="spid" name="spid"
                                   placeholder="Card number" :value="old('spid')" />
                </x-forms.field>

                {{-- Blood type --}}
                <x-forms.field name="blood_type" km="ប្រភេទឈាម" label="Blood Type">
                    <x-forms.select id="blood_type" name="blood_type">
                        <option value="">— គ្មាន / None</option>
                        @foreach(['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $bt)
                            <option value="{{ $bt }}" @selected(old('blood_type') === $bt)>{{ $bt }}</option>
                        @endforeach
                    </x-forms.select>
                </x-forms.field>

            </div>
        </x-ui.card>

        {{-- ─ Address ────────────────────────────────────────── --}}
        <x-ui.card>
            <x-slot:header>
                <x-ui.card-header km="អាសយដ្ឋាន" label="Address" icon="bi-geo-alt-fill" iconColor="#F59E0B" />
            </x-slot:header>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-x-4 gap-y-3">

                {{-- Province / District / Commune / Village — full 4-col row --}}
                @foreach([
                    ['province_name', 'ខេត្ត',  'Province'],
                    ['district_name', 'ស្រុក',  'District'],
                    ['commune_name',  'ឃុំ',    'Commune'],
                    ['village_name',  'ភូមិ',   'Village'],
                ] as [$field, $km, $en])
                    <x-forms.field :name="$field" :km="$km" :label="$en">
                        <x-forms.input :name="$field" :id="$field" :value="old($field)" />
                    </x-forms.field>
                @endforeach

                {{-- Pair: House No + Street No ─────────────────────────────── --}}
                <div class="col-span-2 field-pair grid grid-cols-2 gap-3">
                    <x-forms.field name="house_number" km="លេខផ្ទះ" label="House No.">
                        <x-forms.input id="house_number" name="house_number" :value="old('house_number')" />
                    </x-forms.field>
                    <x-forms.field name="street_number" km="លេខផ្លូវ" label="Street No.">
                        <x-forms.input id="street_number" name="street_number" :value="old('street_number')" />
                    </x-forms.field>
                </div>

            </div>
        </x-ui.card>

        {{-- ─ ID Cards ───────────────────────────────────────── --}}
        <x-ui.card>
            <x-slot:header>
                <x-ui.card-header km="ប័ណ្ណអត្តសញ្ញាណ" label="ID Cards" icon="bi-credit-card-fill" iconColor="#9B59B6">
                    <x-slot:actions>
                        <x-ui.button type="button" variant="secondary" size="sm" onclick="addIdRow()">
                            <x-slot:icon><i class="bi bi-plus-lg" aria-hidden="true"></i></x-slot:icon>
                            Add
                        </x-ui.button>
                    </x-slot:actions>
                </x-ui.card-header>
            </x-slot:header>
            <div id="idCardContainer" class="space-y-2">
                @if(old('identifications'))
                    @foreach(old('identifications') as $i => $card)
                        <div class="id-row flex items-end gap-2">
                            <div class="flex-1 space-y-1">
                                <label class="flex items-center gap-1 text-[13px] font-semibold"
                                       style="color:var(--text-primary,#0F172A);font-family:var(--font-khmer,'Noto Sans Khmer',sans-serif)">ប្រភេទប័ណ្ណ</label>
                                <x-forms.select name="identifications[{{ $i }}][card_type]">
                                    <option value="" disabled>ជ្រើស / Choose…</option>
                                    <option value="NID"      @selected(($card['card_type'] ?? '') === 'NID')>NID</option>
                                    <option value="Passport" @selected(($card['card_type'] ?? '') === 'Passport')>Passport</option>
                                    <option value="HEF"      @selected(($card['card_type'] ?? '') === 'HEF')>HEF Card</option>
                                    <option value="NSSF"     @selected(($card['card_type'] ?? '') === 'NSSF')>NSSF</option>
                                    <option value="Other"    @selected(($card['card_type'] ?? '') === 'Other')>Other</option>
                                </x-forms.select>
                            </div>
                            <div class="flex-1 space-y-1">
                                <label class="flex items-center gap-1 text-[13px] font-semibold"
                                       style="color:var(--text-primary,#0F172A);font-family:var(--font-khmer,'Noto Sans Khmer',sans-serif)">លេខប័ណ្ណ</label>
                                <x-forms.input name="identifications[{{ $i }}][card_code]" :value="$card['card_code'] ?? ''" />
                            </div>
                            <button type="button" onclick="this.closest('.id-row').remove()"
                                    class="flex-shrink-0 flex items-center justify-center rounded-md border border-[#FCA5A5] hover:bg-[#FEE2E2] transition-colors"
                                    style="width:40px;height:40px;color:#EF4444"
                                    aria-label="Remove ID card">
                                <i class="bi bi-trash" style="font-size:13px" aria-hidden="true"></i>
                            </button>
                        </div>
                    @endforeach
                @endif
                <p id="idCardEmpty" class="text-xs py-1" style="color:var(--text-muted,#94A3B8)">
                    @if(!old('identifications'))
                        <i class="bi bi-info-circle" aria-hidden="true"></i>
                        Click "Add" to attach ID cards (NID, Passport, HEF, NSSF)
                    @endif
                </p>
            </div>
        </x-ui.card>

        {{-- ─ Emergency Contacts ─────────────────────────────── --}}
        <x-ui.card>
            <x-slot:header>
                <x-ui.card-header km="ទំនាក់ទំនងបន្ទាន់" label="Emergency Contacts"
                                  icon="bi-people-fill" iconColor="#10B981">
                    <x-slot:actions>
                        <x-ui.button type="button" variant="secondary" size="sm" onclick="addContactRow()">
                            <x-slot:icon><i class="bi bi-plus-lg" aria-hidden="true"></i></x-slot:icon>
                            Add
                        </x-ui.button>
                    </x-slot:actions>
                </x-ui.card-header>
            </x-slot:header>
            <div id="contactContainer" class="space-y-2">
                @if(old('contacts'))
                    @foreach(old('contacts') as $i => $c)
                        <div class="contact-row grid gap-2 items-end"
                             style="grid-template-columns:1fr 1fr 1fr auto">
                            <div class="space-y-1">
                                <label class="flex items-center text-[13px] font-semibold"
                                       style="color:var(--text-primary,#0F172A)">Name</label>
                                <x-forms.input name="contacts[{{ $i }}][contact_name]" :value="$c['contact_name'] ?? ''" />
                            </div>
                            <div class="space-y-1">
                                <label class="flex items-center text-[13px] font-semibold"
                                       style="color:var(--text-primary,#0F172A)">Phone</label>
                                <x-forms.input name="contacts[{{ $i }}][contact_phone]" :value="$c['contact_phone'] ?? ''" />
                            </div>
                            <div class="space-y-1">
                                <label class="flex items-center text-[13px] font-semibold"
                                       style="color:var(--text-primary,#0F172A)">Relation</label>
                                <x-forms.select name="contacts[{{ $i }}][relationship]">
                                    <option value="" disabled>ជ្រើស / Choose…</option>
                                    <option value="Spouse"   @selected(($c['relationship'] ?? '') === 'Spouse')>Spouse</option>
                                    <option value="Parent"   @selected(($c['relationship'] ?? '') === 'Parent')>Parent</option>
                                    <option value="Child"    @selected(($c['relationship'] ?? '') === 'Child')>Child</option>
                                    <option value="Sibling"  @selected(($c['relationship'] ?? '') === 'Sibling')>Sibling</option>
                                    <option value="Guardian" @selected(($c['relationship'] ?? '') === 'Guardian')>Guardian</option>
                                    <option value="Other"    @selected(($c['relationship'] ?? '') === 'Other')>Other</option>
                                </x-forms.select>
                            </div>
                            <button type="button" onclick="this.closest('.contact-row').remove()"
                                    class="flex-shrink-0 flex items-center justify-center rounded-md border border-[#FCA5A5] hover:bg-[#FEE2E2] transition-colors"
                                    style="width:40px;height:40px;color:#EF4444"
                                    aria-label="Remove contact">
                                <i class="bi bi-trash" style="font-size:13px" aria-hidden="true"></i>
                            </button>
                        </div>
                    @endforeach
                @endif
                <p id="contactEmpty" class="text-xs py-1" style="color:var(--text-muted,#94A3B8)">
                    @if(!old('contacts'))
                        <i class="bi bi-info-circle" aria-hidden="true"></i>
                        Click "Add" to enter emergency contacts
                    @endif
                </p>
            </div>
        </x-ui.card>

    </div>{{-- /left --}}

    {{-- ── RIGHT COLUMN — Save Panel ─────────────────────────────── --}}
    <div>
        <x-ui.card class="sticky top-20">
            <x-slot:header>
                <x-ui.card-header km="រក្សាទុក" label="Save Patient" icon="bi-floppy-fill" iconColor="#4154f1" />
            </x-slot:header>

            {{-- Primary actions --}}
            <div class="space-y-2">
                <x-ui.button type="submit" variant="primary" :fullWidth="true">
                    <x-slot:icon><i class="bi bi-check2-circle" aria-hidden="true"></i></x-slot:icon>
                    រក្សាទុក / Save Patient
                </x-ui.button>
                <x-ui.button href="{{ url('/patients') }}" variant="secondary" :fullWidth="true">
                    <x-slot:icon><i class="bi bi-x-circle" aria-hidden="true"></i></x-slot:icon>
                    Cancel
                </x-ui.button>
            </div>

            {{-- Metadata --}}
            <dl class="mt-4 pt-4 space-y-2.5 text-xs" style="border-top:1px solid var(--border-subtle,#E2E8F0)">
                <div class="flex items-center justify-between">
                    <dt style="color:var(--text-muted,#94A3B8)">Patient Code</dt>
                    <dd>
                        <code class="font-mono font-bold px-1.5 py-0.5 rounded"
                              style="background:#EEF0FD;color:#4154f1">{{ $nextCode }}</code>
                    </dd>
                </div>
                <div class="flex items-center justify-between">
                    <dt style="color:var(--text-muted,#94A3B8)">Date</dt>
                    <dd style="color:var(--text-secondary,#475569)">{{ now()->format('d/m/Y') }}</dd>
                </div>
                <div class="flex items-center justify-between">
                    <dt style="color:var(--text-muted,#94A3B8)">By</dt>
                    <dd style="color:var(--text-secondary,#475569)">{{ auth()->user()->name ?? 'Staff' }}</dd>
                </div>
            </dl>

            {{-- Live required-fields checklist --}}
            <div class="mt-4 pt-4" style="border-top:1px solid var(--border-subtle,#E2E8F0)">
                <div class="text-[10px] font-bold uppercase tracking-widest mb-3"
                     style="color:var(--text-muted,#94A3B8);letter-spacing:.08em">
                    Required Fields
                </div>

                {{-- Square-checkbox tracker (not radio circles) --}}
                <ul class="space-y-2.5" role="list" aria-label="Required fields status">

                    <li id="req_surname" class="flex items-center gap-2.5 text-xs transition-colors duration-200"
                        style="color:var(--text-muted,#94A3B8)" data-field="surname">
                        <span class="req-check" id="chk_surname"></span>
                        <span class="req-label">នាមត្រកូល / Surname</span>
                    </li>

                    <li id="req_name" class="flex items-center gap-2.5 text-xs transition-colors duration-200"
                        style="color:var(--text-muted,#94A3B8)" data-field="name">
                        <span class="req-check" id="chk_name"></span>
                        <span class="req-label">ឈ្មោះ / Given Name</span>
                    </li>

                    <li id="req_sex" class="flex items-center gap-2.5 text-xs transition-colors duration-200"
                        style="color:var(--text-muted,#94A3B8)" data-field="sex">
                        <span class="req-check" id="chk_sex"></span>
                        <span class="req-label">ភេទ / Sex</span>
                    </li>

                </ul>

                {{-- Progress bar --}}
                <div class="mt-3 h-1.5 rounded-full overflow-hidden" style="background:#E2E8F0">
                    <div id="reqProgress" class="h-full rounded-full transition-all duration-300"
                         style="width:0%;background:var(--success,#10B981)"></div>
                </div>
                <p id="reqProgressLabel" class="text-[10px] mt-1.5" style="color:var(--text-muted,#94A3B8)">
                    0 of 3 required fields filled
                </p>
            </div>

        </x-ui.card>
    </div>

</div>{{-- /grid --}}
</form>

@push('scripts')
<script>
/* ══════════════════════════════════════════════════════════════════════
   CSS class strings for JS-generated rows
   Keep in sync with forms/input.blade.php and forms/select.blade.php
   ══════════════════════════════════════════════════════════════════════ */
var inputCls = [
    'w-full text-sm border border-[#CBD5E1] rounded-md bg-white text-[#0F172A]',
    'placeholder-[#94A3B8] px-3 py-[9px]',
    'transition-colors duration-150 focus:outline-none',
    'focus-visible:ring-2 focus-visible:ring-[#4154f1]/20 focus-visible:border-[#4154f1]',
].join(' ');

var selectCls = inputCls + ' appearance-none';

var trashBtnCls = [
    'flex-shrink-0 flex items-center justify-center rounded-md',
    'border border-[#FCA5A5] hover:bg-[#FEE2E2] transition-colors',
].join(' ');
var trashBtnStyle = 'width:40px;height:40px;color:#EF4444;cursor:pointer;background:none';

var labelCls  = 'flex items-center text-[13px] font-semibold';
var labelStyle = 'color:var(--text-primary,#0F172A);font-family:var(--font-khmer,"Noto Sans Khmer",sans-serif)';

/* ── Dynamic ID card rows ──────────────────────────────────────────── */
var idIdx = {{ count(old('identifications', [])) }};

function addIdRow() {
    var container = document.getElementById('idCardContainer');
    var empty     = document.getElementById('idCardEmpty');
    if (empty) empty.style.display = 'none';

    var div = document.createElement('div');
    div.className = 'id-row flex items-end gap-2';
    div.innerHTML =
        '<div class="flex-1 space-y-1">'
        + '<label class="' + labelCls + '" style="' + labelStyle + '">ប្រភេទប័ណ្ណ</label>'
        + '<div class="relative">'
        + '<select name="identifications[' + idIdx + '][card_type]" class="' + selectCls + '" style="padding-right:2.5rem;color:var(--text-muted,#94A3B8)"'
        + ' onchange="this.style.color=this.value===\'\'?\'var(--text-muted,#94A3B8)\':\' var(--text-primary,#0F172A)\'">'
        + '<option value="" disabled selected>ជ្រើស / Choose…</option>'
        + '<option value="NID">NID</option>'
        + '<option value="Passport">Passport</option>'
        + '<option value="HEF">HEF Card</option>'
        + '<option value="NSSF">NSSF</option>'
        + '<option value="Other">Other</option>'
        + '</select>'
        + '<div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none" aria-hidden="true">'
        + '<i class="bi bi-chevron-down" style="font-size:11px;color:#64748B"></i></div>'
        + '</div></div>'
        + '<div class="flex-1 space-y-1">'
        + '<label class="' + labelCls + '" style="' + labelStyle + '">លេខប័ណ្ណ</label>'
        + '<input type="text" name="identifications[' + idIdx + '][card_code]"'
        + ' class="' + inputCls + '" placeholder="Card number" />'
        + '</div>'
        + '<button type="button" onclick="this.closest(\'.id-row\').remove()"'
        + ' class="' + trashBtnCls + '" style="' + trashBtnStyle + '" aria-label="Remove ID card">'
        + '<i class="bi bi-trash" style="font-size:13px" aria-hidden="true"></i></button>';

    container.insertBefore(div, document.getElementById('idCardEmpty'));
    idIdx++;
}

/* ── Dynamic contact rows ──────────────────────────────────────────── */
var cIdx = {{ count(old('contacts', [])) }};

function addContactRow() {
    var container = document.getElementById('contactContainer');
    var empty     = document.getElementById('contactEmpty');
    if (empty) empty.style.display = 'none';

    var div = document.createElement('div');
    div.className = 'contact-row grid gap-2 items-end';
    div.style.cssText = 'grid-template-columns:1fr 1fr 1fr auto';
    div.innerHTML =
        '<div class="space-y-1">'
        + '<label class="' + labelCls + '" style="color:var(--text-primary,#0F172A)">Name</label>'
        + '<input type="text" name="contacts[' + cIdx + '][contact_name]" class="' + inputCls + '" />'
        + '</div>'
        + '<div class="space-y-1">'
        + '<label class="' + labelCls + '" style="color:var(--text-primary,#0F172A)">Phone</label>'
        + '<input type="tel" name="contacts[' + cIdx + '][contact_phone]" class="' + inputCls + '" />'
        + '</div>'
        + '<div class="space-y-1">'
        + '<label class="' + labelCls + '" style="color:var(--text-primary,#0F172A)">Relation</label>'
        + '<div class="relative">'
        + '<select name="contacts[' + cIdx + '][relationship]" class="' + selectCls + '" style="padding-right:2.5rem;color:var(--text-muted,#94A3B8)"'
        + ' onchange="this.style.color=this.value===\'\'?\'var(--text-muted,#94A3B8)\':\' var(--text-primary,#0F172A)\'">'
        + '<option value="" disabled selected>ជ្រើស / Choose…</option>'
        + '<option value="Spouse">Spouse</option>'
        + '<option value="Parent">Parent</option>'
        + '<option value="Child">Child</option>'
        + '<option value="Sibling">Sibling</option>'
        + '<option value="Guardian">Guardian</option>'
        + '<option value="Other">Other</option>'
        + '</select>'
        + '<div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none" aria-hidden="true">'
        + '<i class="bi bi-chevron-down" style="font-size:11px;color:#64748B"></i></div>'
        + '</div></div>'
        + '<button type="button" onclick="this.closest(\'.contact-row\').remove()"'
        + ' class="' + trashBtnCls + '" style="' + trashBtnStyle + '" aria-label="Remove contact">'
        + '<i class="bi bi-trash" style="font-size:13px" aria-hidden="true"></i></button>';

    container.insertBefore(div, document.getElementById('contactEmpty'));
    cIdx++;
}

/* ══════════════════════════════════════════════════════════════════════
   Live required-fields checklist
   ══════════════════════════════════════════════════════════════════════ */
(function () {
    var FIELDS = ['surname', 'name', 'sex'];

    function getInput(field) {
        return document.querySelector('[data-req="' + field + '"]');
    }

    function markField(field, filled) {
        var li  = document.getElementById('req_' + field);
        var chk = document.getElementById('chk_' + field);
        var lbl = li ? li.querySelector('.req-label') : null;
        if (!li || !chk) return;

        if (filled) {
            li.style.color = 'var(--success-text,#065F46)';
            chk.innerHTML  = '<i class="bi bi-check2" style="font-size:10px;color:#fff"></i>';
            chk.classList.add('is-done');
        } else {
            li.style.color = 'var(--text-muted,#94A3B8)';
            chk.innerHTML  = '';
            chk.classList.remove('is-done');
        }
    }

    function updateProgress() {
        var filled = 0;
        FIELDS.forEach(function (f) {
            var el = getInput(f);
            if (el && el.value.trim() !== '') filled++;
        });

        var pct = Math.round((filled / FIELDS.length) * 100);
        var bar = document.getElementById('reqProgress');
        var lbl = document.getElementById('reqProgressLabel');
        if (bar) bar.style.width = pct + '%';
        if (lbl) lbl.textContent = filled + ' of ' + FIELDS.length + ' required fields filled';
    }

    function onFieldChange(field) {
        var el     = getInput(field);
        var filled = el && el.value.trim() !== '';
        markField(field, filled);
        updateProgress();
    }

    function init() {
        FIELDS.forEach(function (field) {
            var el = getInput(field);
            if (!el) return;
            el.addEventListener('input',  function () { onFieldChange(field); });
            el.addEventListener('change', function () { onFieldChange(field); });
            onFieldChange(field); // set initial state (handles old() repopulation)
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
</script>
@endpush

@endsection
