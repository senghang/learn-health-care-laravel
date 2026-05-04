@extends('clinics.layout.app')
@section('title', 'Settings')

@section('content')

<x-ui.page-header
    km="ការកំណត់"
    title="Settings"
    :breadcrumbs="[
        ['label' => __('app.home'), 'url' => route('dashboard')],
        ['label' => 'Settings'],
    ]">
</x-ui.page-header>

@include('clinics.settings._subnav')

@if(session('flash'))
    <x-ui.alert type="success" class="mb-4">{{ session('flash') }}</x-ui.alert>
@endif

@if($errors->any())
    <x-ui.alert type="error" class="mb-4">
        <ul class="list-disc pl-4 text-xs space-y-0.5">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </x-ui.alert>
@endif

<form method="POST" action="{{ route('settings.general.update') }}" enctype="multipart/form-data" id="settingsForm">
@csrf @method('PATCH')

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

    {{-- ── LEFT: Main Settings ─────────────────────────────────── --}}
    <div class="lg:col-span-2 space-y-4">

        {{-- Clinic Info --}}
        <x-ui.card>
            <x-slot:header>
                <div class="flex items-center gap-3 px-5 py-4" style="border-bottom:1px solid #e6e9f0;background:#f9fafb">
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0"
                         style="background:#eef0fd;color:#4154f1">
                        <i class="bi bi-hospital-fill" style="font-size:14px" aria-hidden="true"></i>
                    </div>
                    <div>
                        <div class="text-sm font-bold" style="color:#1a1f36">ព័ត៌មានវេជ្ជបណ្ឌិតស្ថាន</div>
                        <div class="text-xs" style="color:#6b7280">Clinic Info</div>
                    </div>
                </div>
            </x-slot:header>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold" style="color:#374151">
                        ឈ្មោះ / Clinic Name <span style="color:#ef4444">*</span>
                    </label>
                    <x-forms.input name="clinic_name" :value="old('clinic_name', $clinic->name)"
                                   required placeholder="Clinic name…" />
                </div>
                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold" style="color:#374151">ឈ្មោះខ្មែរ / Khmer Name</label>
                    <x-forms.input name="clinic_name_kh" :value="old('clinic_name_kh', $clinic->name_kh)"
                                   placeholder="ឈ្មោះខ្មែរ…" />
                </div>
                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold" style="color:#374151">ទូរស័ព្ទ / Phone</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <i class="bi bi-telephone-fill text-xs" style="color:#6b7280" aria-hidden="true"></i>
                        </div>
                        <x-forms.input type="tel" name="clinic_phone" class="pl-8"
                                       :value="old('clinic_phone', $settings->get('clinic_phone')?->value)"
                                       placeholder="012 345 678" />
                    </div>
                </div>
                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold" style="color:#374151">អ៊ីមែល / Email</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <i class="bi bi-envelope-fill text-xs" style="color:#6b7280" aria-hidden="true"></i>
                        </div>
                        <x-forms.input type="email" name="clinic_email" class="pl-8"
                                       :value="old('clinic_email', $settings->get('clinic_email')?->value)"
                                       placeholder="clinic@example.com" />
                    </div>
                </div>
                <div class="sm:col-span-2 space-y-1.5">
                    <label class="block text-xs font-semibold" style="color:#374151">អាសយដ្ឋាន / Address</label>
                    <textarea name="clinic_address" rows="2"
                              placeholder="Street, village, commune, district, province…"
                              class="w-full text-sm rounded-lg border border-[#e2e8f0] bg-white px-3 py-2.5 text-[#374151] placeholder-[#9ca3af] focus:outline-none focus:border-[#4154f1] focus:ring-2 focus:ring-[#4154f1]/20 transition-colors resize-none">{{ old('clinic_address', $settings->get('clinic_address')?->value) }}</textarea>
                </div>
            </div>
        </x-ui.card>

        {{-- Language & Region --}}
        <x-ui.card>
            <x-slot:header>
                <div class="flex items-center gap-3 px-5 py-4" style="border-bottom:1px solid #e6e9f0;background:#f9fafb">
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0"
                         style="background:#fff3e8;color:#ff771d">
                        <i class="bi bi-translate" style="font-size:14px" aria-hidden="true"></i>
                    </div>
                    <div>
                        <div class="text-sm font-bold" style="color:#1a1f36">ភាសា & តំបន់</div>
                        <div class="text-xs" style="color:#6b7280">Language & Region</div>
                    </div>
                </div>
            </x-slot:header>

            <div class="space-y-4">
                {{-- Language toggle --}}
                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold" style="color:#374151">ភាសាលំនាំដើម / Default Language</label>
                    <div class="flex gap-3 flex-wrap">
                        @foreach(['km' => ['ខ្មែរ / Khmer', '🇰🇭', 'Primary language'], 'en' => ['English', '🇺🇸', 'Secondary language']] as $code => [$label, $flag, $hint])
                            @php $isActive = old('default_locale', $clinic->default_locale ?? 'km') === $code; @endphp
                            <label id="lang-label-{{ $code }}" onclick="selectLang('{{ $code }}')"
                                   class="flex items-center gap-3 px-4 py-3 rounded-xl border-2 cursor-pointer flex-1 min-w-40 transition-all"
                                   style="{{ $isActive ? 'border-color:#4154f1;background:#eef0fd' : 'border-color:#e2e8f0;background:#fff' }}">
                                <input type="radio" name="default_locale" value="{{ $code }}"
                                       id="lang_{{ $code }}" class="sr-only"
                                       {{ $isActive ? 'checked' : '' }}>
                                <span class="text-2xl flex-shrink-0">{{ $flag }}</span>
                                <div class="flex-1">
                                    <div class="text-sm font-bold" style="color:#1a1f36">{{ $label }}</div>
                                    <div class="text-xs" style="color:#6b7280">{{ $hint }}</div>
                                </div>
                                <i class="bi bi-check-circle-fill" id="chk_{{ $code }}"
                                   style="color:#4154f1;font-size:16px;{{ $isActive ? '' : 'display:none' }}" aria-hidden="true"></i>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold" style="color:#374151">ល្វែងម៉ោង / Timezone</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <i class="bi bi-clock-fill text-xs" style="color:#6b7280" aria-hidden="true"></i>
                            </div>
                            <x-forms.select name="timezone" class="pl-8">
                                @foreach(['Asia/Phnom_Penh' => 'Asia/Phnom Penh (UTC+7)', 'Asia/Bangkok' => 'Asia/Bangkok (UTC+7)', 'UTC' => 'UTC'] as $val => $lbl)
                                    <option value="{{ $val }}" @selected(old('timezone', $settings->get('timezone')?->value ?? 'Asia/Phnom_Penh') === $val)>
                                        {{ $lbl }}
                                    </option>
                                @endforeach
                            </x-forms.select>
                        </div>
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold" style="color:#374151">រូបិយប័ណ្ណ / Currency</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <i class="bi bi-currency-dollar text-xs" style="color:#6b7280" aria-hidden="true"></i>
                            </div>
                            <x-forms.select name="currency" class="pl-8">
                                @foreach(['KHR' => 'KHR — រៀល', 'USD' => 'USD — Dollar', 'THB' => 'THB — Baht'] as $val => $lbl)
                                    <option value="{{ $val }}" @selected(old('currency', $settings->get('currency')?->value ?? 'KHR') === $val)>
                                        {{ $lbl }}
                                    </option>
                                @endforeach
                            </x-forms.select>
                        </div>
                    </div>
                </div>
            </div>
        </x-ui.card>

        {{-- Branding --}}
        <x-ui.card>
            <x-slot:header>
                <div class="flex items-center gap-3 px-5 py-4" style="border-bottom:1px solid #e6e9f0;background:#f9fafb">
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0"
                         style="background:#f0e8ff;color:#9b59b6">
                        <i class="bi bi-image-fill" style="font-size:14px" aria-hidden="true"></i>
                    </div>
                    <div>
                        <div class="text-sm font-bold" style="color:#1a1f36">ស្លាក / Branding</div>
                        <div class="text-xs" style="color:#6b7280">Logo & visual identity</div>
                    </div>
                </div>
            </x-slot:header>

            <div class="flex items-center gap-4">
                {{-- Logo preview --}}
                <div id="logoPreview" class="flex-shrink-0">
                    @if($clinic->logo)
                        <img src="{{ asset('storage/' . $clinic->logo) }}" id="logoImg"
                             class="w-18 h-18 rounded-xl object-cover"
                             style="width:72px;height:72px;border-radius:12px;object-fit:cover;display:block">
                    @else
                        <div id="logoPlaceholder"
                             class="flex items-center justify-center text-3xl text-white"
                             style="width:72px;height:72px;border-radius:12px;background:linear-gradient(135deg,#4154f1,#717ff5)">
                            ⚕
                        </div>
                    @endif
                </div>

                {{-- Upload control --}}
                <div class="flex-1 space-y-2">
                    <label class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border-2 border-dashed cursor-pointer transition-colors"
                           style="border-color:#c5cbf9;background:#f6f8ff;color:#4154f1;font-size:12.5px;font-weight:600"
                           onmouseenter="this.style.borderColor='#4154f1';this.style.background='#eef0fd'"
                           onmouseleave="this.style.borderColor='#c5cbf9';this.style.background='#f6f8ff'">
                        <i class="bi bi-cloud-arrow-up-fill" aria-hidden="true"></i>
                        Choose Logo
                        <input type="file" name="header_logo" accept="image/png,image/jpeg"
                               onchange="previewLogo(this)" class="sr-only" />
                    </label>
                    <p class="text-xs" style="color:#6b7280">
                        <i class="bi bi-info-circle" aria-hidden="true"></i>
                        PNG or JPG · max 2 MB · used on login and header
                    </p>
                    <div id="logoFileName" class="text-xs items-center gap-1" style="color:#4154f1;display:none">
                        <i class="bi bi-check-circle-fill" style="color:#2eca6a" aria-hidden="true"></i>
                        <span id="logoFileNameText"></span>
                    </div>
                </div>
            </div>
        </x-ui.card>

    </div>{{-- /left --}}

    {{-- ── RIGHT: Save + Meta + Quick Links ────────────────────── --}}
    <div class="space-y-4">

        {{-- Save card --}}
        <x-ui.card class="sticky top-20">
            <x-slot:header>
                <div class="flex items-center gap-2 px-5 py-4" style="border-bottom:1px solid #e6e9f0;background:#f9fafb">
                    <i class="bi bi-save-fill" style="color:#4154f1;font-size:15px" aria-hidden="true"></i>
                    <span class="text-sm font-bold" style="color:#1a1f36">Save Settings</span>
                </div>
            </x-slot:header>

            <x-ui.button type="submit" variant="primary" :fullWidth="true">
                <x-slot:icon><i class="bi bi-check2-circle" aria-hidden="true"></i></x-slot:icon>
                រក្សាទុក / Save
            </x-ui.button>

            {{-- Clinic meta --}}
            <div class="mt-4 space-y-2" style="padding-top:1rem;border-top:1px solid #e6e9f0">
                @foreach([
                    ['bi-globe',          'Subdomain', $clinic->subdomain,                     true],
                    ['bi-calendar-check', 'Since',     $clinic->start_date?->format('d/m/Y') ?? '—', false],
                    ['bi-key-fill',       'Plan',      ucfirst($clinic->plan ?? 'standard'),    false],
                ] as [$icon, $label, $val, $isCode])
                    <div class="flex items-center gap-2 text-xs">
                        <i class="bi {{ $icon }} flex-shrink-0" style="color:#4154f1;width:14px" aria-hidden="true"></i>
                        <span class="flex-1" style="color:#6b7280">{{ $label }}</span>
                        @if($isCode)
                            <code class="text-xs px-1.5 py-0.5 rounded font-mono" style="background:#eef0fd;color:#4154f1">{{ $val }}</code>
                        @else
                            <span class="font-semibold" style="color:#374151">{{ $val }}</span>
                        @endif
                    </div>
                @endforeach
            </div>
        </x-ui.card>

        {{-- Quick Links --}}
        <x-ui.card :noPadding="true">
            <x-slot:header>
                <div class="flex items-center gap-3 px-5 py-4" style="border-bottom:1px solid #e6e9f0;background:#f9fafb">
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0"
                         style="background:#fff3e8;color:#ff771d">
                        <i class="bi bi-lightning-fill" style="font-size:14px" aria-hidden="true"></i>
                    </div>
                    <div>
                        <div class="text-sm font-bold" style="color:#1a1f36">Quick Links</div>
                        <div class="text-xs" style="color:#6b7280">Settings shortcuts</div>
                    </div>
                </div>
            </x-slot:header>
            @foreach([
                [route('settings.services'),     'bi-list-check',      '#2eca6a', '#e8f8ef', 'Services Master',      'Add / edit billable services'],
                [route('settings.medicines'),    'bi-capsule-fill',    '#9b59b6', '#f0e8ff', 'Medicines & Stock',    'Formulary & inventory'],
                [route('beds.index'),            'bi-building-fill',   '#ff771d', '#fff3e8', 'Wards & Beds',         'Room and bed management'],
                [route('settings.roles'),        'bi-shield-lock-fill','#64748b', '#f1f5f9', 'Roles & Permissions',  'User access control'],
            ] as [$url, $icon, $color, $bg, $label, $hint])
                <a href="{{ $url }}"
                   class="flex items-center gap-3 px-5 py-3 transition-colors hover:bg-[#f6f8fa] group"
                   style="border-bottom:1px solid #f5f6ff">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0"
                         style="background:{{ $bg }};color:{{ $color }}">
                        <i class="bi {{ $icon }}" style="font-size:13px" aria-hidden="true"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-semibold" style="color:#374151">{{ $label }}</div>
                        <div class="text-xs" style="color:#6b7280">{{ $hint }}</div>
                    </div>
                    <i class="bi bi-chevron-right text-xs transition-transform group-hover:translate-x-0.5"
                       style="color:#cbd5e1" aria-hidden="true"></i>
                </a>
            @endforeach
        </x-ui.card>

    </div>{{-- /right --}}

</div>{{-- /grid --}}
</form>

@push('scripts')
<script>
function selectLang(code) {
    ['km', 'en'].forEach(function(c) {
        var radio = document.getElementById('lang_' + c);
        var chk   = document.getElementById('chk_' + c);
        var lbl   = document.getElementById('lang-label-' + c);
        if (radio) radio.checked = (c === code);
        if (chk)   chk.style.display = (c === code) ? 'inline' : 'none';
        if (lbl) {
            if (c === code) {
                lbl.style.borderColor = '#4154f1';
                lbl.style.background  = '#eef0fd';
            } else {
                lbl.style.borderColor = '#e2e8f0';
                lbl.style.background  = '#fff';
            }
        }
    });
}

function previewLogo(input) {
    var file = input.files[0];
    if (!file) return;
    var reader = new FileReader();
    reader.onload = function(e) {
        var el = document.getElementById('logoImg') || document.getElementById('logoPlaceholder');
        if (el) {
            var img = document.createElement('img');
            img.src = e.target.result;
            img.style.cssText = 'width:72px;height:72px;border-radius:12px;object-fit:cover;display:block';
            img.id = 'logoImg';
            el.parentNode.replaceChild(img, el);
        }
    };
    reader.readAsDataURL(file);
    var nameEl   = document.getElementById('logoFileName');
    var nameText = document.getElementById('logoFileNameText');
    if (nameEl && nameText) {
        nameText.textContent = file.name;
        nameEl.style.display = 'flex';
        nameEl.style.alignItems = 'center';
        nameEl.style.gap = '4px';
    }
}
</script>
@endpush

@endsection
