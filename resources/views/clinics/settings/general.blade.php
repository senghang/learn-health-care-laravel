@extends('clinics.layout.app')
@section('title', 'Settings')
@section('content')

<x-page-header title="ការកំណត់" subtitle="Settings"
    icon="bi-gear-fill"
    :breadcrumbs="[['label'=>'ដើម','url'=>url('/')],['label'=>'Settings']]"/>

@include('clinics.settings._subnav')

@if(session('flash'))
<div class="note note-success mb-3">
    <i class="bi bi-check-circle-fill"></i>
    <strong>{{ session('flash') }}</strong>
</div>
@endif

@if($errors->any())
<div class="note note-danger mb-3">
    <i class="bi bi-exclamation-triangle-fill"></i>
    <ul style="margin:0;padding-left:16px">
        @foreach($errors->all() as $e)<li style="font-size:12px">{{ $e }}</li>@endforeach
    </ul>
</div>
@endif

<form method="POST" action="{{ route('settings.general.update') }}" enctype="multipart/form-data" id="settingsForm">
@csrf @method('PATCH')

<div class="row g-3">

{{-- ── LEFT COLUMN ─────────────────────────────────────────────────── --}}
<div class="col-12 col-lg-8">

    {{-- Clinic Info --}}
    <div class="settings-card">
        <div class="settings-card-hd">
            <span class="settings-card-icon" style="background:#eef0fd;color:#4154f1">
                <i class="bi bi-hospital-fill"></i>
            </span>
            <div>
                <div class="settings-card-title">ព័ត៌មានវេជ្ជបណ្ឌិតស្ថាន</div>
                <div class="settings-card-sub">Clinic Info</div>
            </div>
        </div>
        <div class="settings-card-bd">
            <div class="row g-3">
                <div class="col-12 col-sm-6">
                    <div class="fld">
                        <label class="flbl">
                            <span class="km">ឈ្មោះ</span><span class="en">/ Clinic Name</span>
                            <span class="req">*</span>
                        </label>
                        <input name="clinic_name" class="form-control"
                               value="{{ old('clinic_name', $clinic->name) }}" required
                               placeholder="Clinic name…"/>
                    </div>
                </div>
                <div class="col-12 col-sm-6">
                    <div class="fld">
                        <label class="flbl">
                            <span class="km">ឈ្មោះខ្មែរ</span><span class="en">/ Khmer Name</span>
                        </label>
                        <input name="clinic_name_kh" class="form-control"
                               value="{{ old('clinic_name_kh', $clinic->name_kh) }}"
                               placeholder="ឈ្មោះខ្មែរ…"/>
                    </div>
                </div>
                <div class="col-12 col-sm-6">
                    <div class="fld">
                        <label class="flbl">
                            <span class="km">ទូរស័ព្ទ</span><span class="en">/ Phone</span>
                        </label>
                        <div class="input-icon-wrap">
                            <i class="bi bi-telephone-fill input-icon"></i>
                            <input name="clinic_phone" type="tel" class="form-control"
                                   value="{{ old('clinic_phone', $settings->get('clinic_phone')?->value) }}"
                                   placeholder="012 345 678"/>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6">
                    <div class="fld">
                        <label class="flbl">
                            <span class="km">អ៊ីមែល</span><span class="en">/ Email</span>
                        </label>
                        <div class="input-icon-wrap">
                            <i class="bi bi-envelope-fill input-icon"></i>
                            <input name="clinic_email" type="email" class="form-control"
                                   value="{{ old('clinic_email', $settings->get('clinic_email')?->value) }}"
                                   placeholder="clinic@example.com"/>
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="fld">
                        <label class="flbl">
                            <span class="km">អាសយដ្ឋាន</span><span class="en">/ Address</span>
                        </label>
                        <textarea name="clinic_address" class="form-control" rows="2"
                                  placeholder="Street, village, commune, district, province…">{{ old('clinic_address', $settings->get('clinic_address')?->value) }}</textarea>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Language & Region --}}
    <div class="settings-card">
        <div class="settings-card-hd">
            <span class="settings-card-icon" style="background:#fff3e8;color:#ff771d">
                <i class="bi bi-translate"></i>
            </span>
            <div>
                <div class="settings-card-title">ភាសា & តំបន់</div>
                <div class="settings-card-sub">Language & Region</div>
            </div>
        </div>
        <div class="settings-card-bd">
            <div class="row g-3">

                {{-- Language toggle --}}
                <div class="col-12">
                    <div class="fld">
                        <label class="flbl">
                            <span class="km">ភាសាលំនាំដើម</span>
                            <span class="en">/ Default Language</span>
                        </label>
                        <div class="lang-toggle-wrap">
                            @foreach(['km'=>['ខ្មែរ / Khmer','🇰🇭','Primary language'],'en'=>['English','🇺🇸','Secondary language']] as $code=>[$label,$flag,$hint])
                            <label class="lang-toggle {{ old('default_locale', $clinic->default_locale ?? 'km') === $code ? 'active' : '' }}"
                                   id="lang-label-{{ $code }}"
                                   onclick="selectLang('{{ $code }}')">
                                <input type="radio" name="default_locale" value="{{ $code }}"
                                       id="lang_{{ $code }}" style="display:none"
                                       {{ old('default_locale', $clinic->default_locale ?? 'km') === $code ? 'checked' : '' }}>
                                <span class="lang-flag">{{ $flag }}</span>
                                <div class="lang-info">
                                    <span class="lang-name">{{ $label }}</span>
                                    <span class="lang-hint">{{ $hint }}</span>
                                </div>
                                <i class="bi bi-check-circle-fill lang-check" id="chk_{{ $code }}"
                                   style="display:{{ old('default_locale', $clinic->default_locale ?? 'km') === $code ? 'block' : 'none' }}"></i>
                            </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6">
                    <div class="fld">
                        <label class="flbl">
                            <span class="km">ល្វែងម៉ោង</span><span class="en">/ Timezone</span>
                        </label>
                        <div class="input-icon-wrap">
                            <i class="bi bi-clock-fill input-icon"></i>
                            <select name="timezone" class="form-select">
                                @foreach(['Asia/Phnom_Penh'=>'Asia/Phnom Penh (UTC+7)','Asia/Bangkok'=>'Asia/Bangkok (UTC+7)','UTC'=>'UTC'] as $val=>$label)
                                <option value="{{ $val }}" {{ old('timezone', $settings->get('timezone')?->value ?? 'Asia/Phnom_Penh') === $val ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6">
                    <div class="fld">
                        <label class="flbl">
                            <span class="km">រូបិយប័ណ្ណ</span><span class="en">/ Currency</span>
                        </label>
                        <div class="input-icon-wrap">
                            <i class="bi bi-currency-dollar input-icon"></i>
                            <select name="currency" class="form-select">
                                @foreach(['KHR'=>'KHR — រៀល','USD'=>'USD — Dollar','THB'=>'THB — Baht'] as $val=>$label)
                                <option value="{{ $val }}" {{ old('currency', $settings->get('currency')?->value ?? 'KHR') === $val ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Branding --}}
    <div class="settings-card">
        <div class="settings-card-hd">
            <span class="settings-card-icon" style="background:#f0e8ff;color:#9b59b6">
                <i class="bi bi-image-fill"></i>
            </span>
            <div>
                <div class="settings-card-title">ស្លាក</div>
                <div class="settings-card-sub">Branding</div>
            </div>
        </div>
        <div class="settings-card-bd">
            <div class="row g-3 align-items-center">
                <div class="col-auto">
                    <div class="logo-preview" id="logoPreview">
                        @if($clinic->logo)
                            <img src="{{ asset('storage/'.$clinic->logo) }}" id="logoImg"
                                 style="width:72px;height:72px;border-radius:12px;object-fit:cover;display:block">
                        @else
                            <div id="logoPlaceholder" style="width:72px;height:72px;border-radius:12px;background:linear-gradient(135deg,#4154f1,#717ff5);display:flex;align-items:center;justify-content:center;font-size:28px;color:#fff">⚕</div>
                        @endif
                    </div>
                </div>
                <div class="col">
                    <div class="fld" style="margin:0">
                        <label class="flbl">
                            <span class="km">រូបតំណាង</span><span class="en">/ Logo</span>
                        </label>
                        <label class="upload-btn">
                            <i class="bi bi-cloud-arrow-up-fill"></i>
                            Choose file
                            <input type="file" name="header_logo" accept="image/png,image/jpeg"
                                   onchange="previewLogo(this)" style="display:none"/>
                        </label>
                        <div style="font-size:10.5px;color:#94a3b8;margin-top:5px">
                            <i class="bi bi-info-circle"></i>
                            PNG or JPG · max 2 MB · used on login and header branding
                        </div>
                        <div id="logoFileName" style="font-size:11px;color:#4154f1;margin-top:3px;display:none">
                            <i class="bi bi-check-circle-fill" style="color:#2eca6a"></i>
                            <span id="logoFileNameText"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>{{-- /col-lg-8 --}}

{{-- ── RIGHT COLUMN: Save + Clinic meta + Quick links ─────────────── --}}
<div class="col-12 col-lg-4">

    {{-- Save card --}}
    <div class="settings-save-card">
        <div class="settings-save-hd">
            <i class="bi bi-save-fill" style="color:#4154f1;font-size:16px"></i>
            <span>Save Settings</span>
        </div>

        <button type="submit" class="settings-save-btn">
            <i class="bi bi-check2-circle"></i>
            រក្សាទុក / Save Settings
        </button>

        {{-- Clinic meta chips --}}
        <div class="settings-meta-list">
            <div class="settings-meta-row">
                <span class="settings-meta-icon"><i class="bi bi-globe"></i></span>
                <span class="settings-meta-label">Subdomain</span>
                <code class="settings-meta-val">{{ $clinic->subdomain }}</code>
            </div>
            <div class="settings-meta-row">
                <span class="settings-meta-icon"><i class="bi bi-calendar-check"></i></span>
                <span class="settings-meta-label">Since</span>
                <span class="settings-meta-val">{{ $clinic->start_date?->format('d/m/Y') ?? '—' }}</span>
            </div>
            <div class="settings-meta-row">
                <span class="settings-meta-icon"><i class="bi bi-key-fill"></i></span>
                <span class="settings-meta-label">Plan</span>
                <span class="settings-meta-val" style="text-transform:capitalize">{{ $clinic->plan ?? 'standard' }}</span>
            </div>
        </div>
    </div>

    {{-- Quick Links --}}
    <div class="settings-card settings-quick-links">
        <div class="settings-card-hd">
            <span class="settings-card-icon" style="background:#fff3e8;color:#ff771d">
                <i class="bi bi-lightning-fill"></i>
            </span>
            <div>
                <div class="settings-card-title">Quick Links</div>
                <div class="settings-card-sub">Settings shortcuts</div>
            </div>
        </div>
        <div class="settings-card-bd" style="padding:0">
            @foreach([
                [route('settings.services'),  'bi-list-check',    '#2eca6a','#e8f8ef', 'Services Master',  'Add / edit billable services'],
                [route('settings.medicines'), 'bi-capsule-fill',  '#9b59b6','#f0e8ff', 'Medicines & Stock','Formulary & inventory'],
                [route('beds.index'),         'bi-building-fill', '#ff771d','#fff3e8', 'Wards & Beds',     'Room and bed management'],
                [route('settings.roles'),     'bi-shield-lock-fill','#64748b','#f1f5f9','Roles & Permissions','User access control'],
            ] as [$url,$icon,$color,$bg,$label,$hint])
            <a href="{{ $url }}" class="quick-link-row">
                <span class="quick-link-icon" style="background:{{ $bg }};color:{{ $color }}">
                    <i class="bi {{ $icon }}"></i>
                </span>
                <div class="quick-link-text">
                    <span class="quick-link-label">{{ $label }}</span>
                    <span class="quick-link-hint">{{ $hint }}</span>
                </div>
                <i class="bi bi-chevron-right quick-link-arrow"></i>
            </a>
            @endforeach
        </div>
    </div>

</div>{{-- /col-lg-4 --}}

</div>{{-- /row --}}
</form>

<style>
/* Settings page styles */
.settings-card {
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 2px 16px rgba(1,41,112,.07);
    margin-bottom: 16px;
    overflow: hidden;
    border: 1px solid #f0f2ff;
    transition: box-shadow .2s;
}
.settings-card:hover { box-shadow: 0 4px 24px rgba(1,41,112,.10); }
.settings-card-hd {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 16px 20px;
    border-bottom: 1px solid #f0f2ff;
    background: #fafbff;
}
.settings-card-icon {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    flex-shrink: 0;
}
.settings-card-title {
    font-size: 14px;
    font-weight: 800;
    color: #012970;
    line-height: 1.2;
}
.settings-card-sub {
    font-size: 10.5px;
    color: #94a3b8;
    margin-top: 1px;
}
.settings-card-bd { padding: 20px; }

/* Language toggle */
.lang-toggle-wrap {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    margin-top: 4px;
}
.lang-toggle {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 16px;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    cursor: pointer;
    background: #fff;
    transition: border-color .15s, background .15s, transform .1s;
    flex: 1;
    min-width: 160px;
}
.lang-toggle:hover { border-color: #c5cbf9; background: #f8f9ff; transform: translateY(-1px); }
.lang-toggle.active { border-color: #4154f1; background: #eef0fd; }
.lang-flag { font-size: 24px; flex-shrink: 0; }
.lang-info { flex: 1; }
.lang-name { display: block; font-size: 13px; font-weight: 700; color: #012970; }
.lang-hint { display: block; font-size: 10px; color: #94a3b8; margin-top: 1px; }
.lang-check { color: #4154f1; font-size: 16px; flex-shrink: 0; }

/* Upload button */
.upload-btn {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 8px 16px;
    border-radius: 8px;
    border: 1.5px dashed #c5cbf9;
    background: #f0f2ff;
    color: #4154f1;
    font-size: 12.5px;
    font-weight: 600;
    cursor: pointer;
    transition: border-color .15s, background .15s;
}
.upload-btn:hover { border-color: #4154f1; background: #e6e9fd; }

/* Save card */
.settings-save-card {
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 2px 16px rgba(1,41,112,.07);
    border: 1px solid #f0f2ff;
    padding: 0;
    margin-bottom: 16px;
    position: sticky;
    top: 76px;
    overflow: hidden;
}
.settings-save-hd {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 14px 18px;
    border-bottom: 1px solid #f0f2ff;
    font-size: 13.5px;
    font-weight: 700;
    color: #012970;
    background: #fafbff;
}
.settings-save-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    width: calc(100% - 32px);
    margin: 16px;
    padding: 12px 20px;
    background: linear-gradient(135deg, #4154f1, #6366f1);
    color: #fff;
    border: none;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    transition: opacity .15s, transform .1s, box-shadow .15s;
    box-shadow: 0 4px 16px rgba(65,84,241,.35);
    font-family: var(--font, inherit);
}
.settings-save-btn:hover { opacity: .92; transform: translateY(-1px); box-shadow: 0 6px 22px rgba(65,84,241,.42); }
.settings-save-btn:active { transform: translateY(0); }

/* Meta list */
.settings-meta-list {
    padding: 0 18px 16px;
    display: flex;
    flex-direction: column;
    gap: 8px;
}
.settings-meta-row {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 12px;
    color: #64748b;
}
.settings-meta-icon {
    width: 20px;
    text-align: center;
    color: #4154f1;
    font-size: 12px;
    flex-shrink: 0;
}
.settings-meta-label { flex: 1; color: #94a3b8; }
.settings-meta-val { font-weight: 700; color: #374151; }
.settings-meta-val code { font-family: monospace; color: #4154f1; background: #eef0fd; padding: 1px 6px; border-radius: 4px; font-size: 11px; }

/* Quick links */
.settings-quick-links .settings-card-bd { padding: 0; }
.quick-link-row {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 11px 18px;
    border-bottom: 1px solid #f5f6ff;
    text-decoration: none;
    transition: background .12s;
}
.quick-link-row:last-child { border-bottom: none; }
.quick-link-row:hover { background: #f6f9ff; }
.quick-link-icon {
    width: 34px;
    height: 34px;
    border-radius: 9px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    flex-shrink: 0;
}
.quick-link-text { flex: 1; min-width: 0; }
.quick-link-label { display: block; font-size: 13px; font-weight: 600; color: #374151; }
.quick-link-hint  { display: block; font-size: 10.5px; color: #94a3b8; margin-top: 1px; }
.quick-link-arrow { color: #cbd5e1; font-size: 11px; flex-shrink: 0; transition: transform .15s; }
.quick-link-row:hover .quick-link-arrow { color: #4154f1; transform: translateX(3px); }
</style>

<script>
function selectLang(code) {
    ['km','en'].forEach(c => {
        document.getElementById('lang_'+c).checked = (c === code);
        document.getElementById('chk_'+c).style.display = (c === code) ? 'block' : 'none';
        var lbl = document.getElementById('lang-label-'+c);
        if (lbl) lbl.classList.toggle('active', c === code);
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
    var nameEl = document.getElementById('logoFileName');
    var nameText = document.getElementById('logoFileNameText');
    if (nameEl && nameText) { nameText.textContent = file.name; nameEl.style.display = 'flex'; nameEl.style.alignItems = 'center'; nameEl.style.gap = '4px'; }
}
</script>

@endsection
