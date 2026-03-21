@extends('clinics.layout.app')
@section('title', __('app.settings.general'))
@section('content')

<div class="pg-header">
  <div>
    <h1 class="pg-title">{{ __('app.settings.title') }} <small>/ Settings</small></h1>
    <div class="breadcrumb-row">
      <a href="{{ route('dashboard') }}">{{ __('app.nav.dashboard') }}</a>
      <span>›</span><span>{{ __('app.settings.general') }}</span>
    </div>
  </div>
</div>

{{-- Settings sub-nav --}}
<div class="tabs mb-3" style="display:flex;gap:4px;flex-wrap:wrap">
  @foreach([
    ['settings.general',   'bi-gear-fill',        __('app.settings.general')],
    ['settings.templates', 'bi-printer-fill',      __('app.settings.templates')],
    ['settings.services',  'bi-list-check',        __('app.settings.services')],
    ['settings.medicines', 'bi-capsule-fill',      __('app.settings.medicines')],
  ] as [$route, $icon, $label])
  <a href="{{ route($route) }}"
     class="tab {{ request()->routeIs($route) ? 'on' : '' }}"
     style="display:inline-flex;align-items:center;gap:6px;padding:7px 16px;border-radius:20px;
            font-size:13px;text-decoration:none;border:1px solid var(--color-border-tertiary);
            background:{{ request()->routeIs($route) ? 'var(--color-text-primary)' : 'var(--color-background-secondary)' }};
            color:{{ request()->routeIs($route) ? 'var(--color-background-primary)' : 'var(--color-text-secondary)' }}">
    <i class="bi {{ $icon }}"></i> {{ $label }}
  </a>
  @endforeach
</div>

<div class="card-emr">
  <div class="card-hd">
    <div class="card-hd-title">
      <i class="bi bi-gear-fill"></i> {{ __('app.settings.general') }}
    </div>
  </div>
  <div class="card-bd">
    <form method="POST" action="{{ route('settings.general.update') }}" enctype="multipart/form-data">
      @csrf @method('PATCH')

      <div class="row g-3">
        <div class="col-12 col-md-4">
          <div class="fld">
            <label class="flbl">
              <span class="km">{{ __('app.settings.locale') }}</span>
              <span class="en">/ Default Language</span>
            </label>
            <select name="default_locale" class="form-select">
              <option value="km" {{ ($clinic->default_locale ?? 'km') === 'km' ? 'selected' : '' }}>
                ភាសាខ្មែរ (Khmer)
              </option>
              <option value="en" {{ ($clinic->default_locale ?? 'km') === 'en' ? 'selected' : '' }}>
                English
              </option>
            </select>
          </div>
        </div>

        <div class="col-12 col-md-4">
          <div class="fld">
            <label class="flbl">
              <span class="km">{{ __('app.settings.timezone') }}</span>
              <span class="en">/ Timezone</span>
            </label>
            <select name="timezone" class="form-select">
              @foreach(['Asia/Phnom_Penh','Asia/Bangkok','UTC'] as $tz)
              <option value="{{ $tz }}" {{ ($settings['timezone']?->value ?? 'Asia/Phnom_Penh') === $tz ? 'selected' : '' }}>
                {{ $tz }}
              </option>
              @endforeach
            </select>
          </div>
        </div>

        <div class="col-12 col-md-4">
          <div class="fld">
            <label class="flbl">
              <span class="km">រូបតំណាង</span>
              <span class="en">/ Header Logo</span>
            </label>
            <input type="file" name="header_logo" class="form-control" accept="image/png,image/jpeg">
            @if($clinic->logo)
            <div style="margin-top:8px">
              <img src="{{ asset('storage/'.$clinic->logo) }}" style="height:40px;border-radius:6px">
            </div>
            @endif
          </div>
        </div>
      </div>

      <div class="mt-3 pt-3" style="border-top:1px solid #f0f2ff">
        <button type="submit" class="btn btn-primary">
          <i class="bi bi-check2-circle"></i> {{ __('app.save') }}
        </button>
      </div>
    </form>
  </div>
</div>

@endsection
