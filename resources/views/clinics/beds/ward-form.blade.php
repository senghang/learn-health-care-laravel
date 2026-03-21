@extends('clinics.layout.app')
@section('title', $ward ? 'Edit Ward' : 'New Ward')
@section('content')

<div class="pg-header">
  <div>
    <h1 class="pg-title">
      {{ $ward ? 'Edit Ward' : 'New Ward' }}
      <small>/ {{ $ward ? 'Edit' : 'Create' }}</small>
    </h1>
    <div class="breadcrumb-row">
      <a href="{{ route('dashboard') }}">ដើម</a><span>›</span>
      <a href="{{ route('beds.index') }}">{{ __('app.bed.title') }}</a><span>›</span>
      <span>{{ $ward ? 'Edit' : 'New' }}</span>
    </div>
  </div>
  <a href="{{ route('beds.index') }}" class="btn btn-outline-primary btn-sm">
    <i class="bi bi-arrow-left"></i> {{ __('app.back') }}
  </a>
</div>

<div class="row">
  <div class="col-12 col-md-6">
    <div class="card-emr">
      <div class="card-hd">
        <div class="card-hd-title">
          <i class="bi bi-building" style="color:#BA7517"></i>
          {{ $ward ? 'Edit Ward' : 'New Ward' }}
        </div>
      </div>
      <div class="card-bd">
        <form method="POST"
              action="{{ $ward ? route('beds.ward.update', $ward->id) : route('beds.ward.store') }}">
          @csrf
          @if($ward) @method('PATCH') @endif

          <div class="fld">
            <label class="flbl">
              <span class="km">{{ __('app.code') }}</span>
              <span class="req">*</span>
              @if(!$ward)<span style="font-size:9px;background:#e8f8ef;color:#1D9E75;padding:1px 6px;border-radius:8px">AUTO</span>@endif
            </label>
            <input name="code" class="form-control"
                   value="{{ old('code', $ward?->code) }}"
                   placeholder="WD-1-001"
                   {{ $ward ? 'readonly' : '' }}/>
          </div>

          <div class="fld">
            <label class="flbl"><span class="km">{{ __('app.name') }}</span><span class="req">*</span></label>
            <input name="name" class="form-control"
                   value="{{ old('name', $ward?->name) }}"
                   placeholder="Paediatrics Ward"/>
          </div>

          <div class="row g-2">
            <div class="col-6">
              <div class="fld">
                <label class="flbl"><span class="km">ឈ្មោះ (KH)</span></label>
                <input name="name_kh" class="form-control"
                       value="{{ old('name_kh', $ward?->name_kh) }}"
                       placeholder="សេវាកុមារ"/>
              </div>
            </div>
            <div class="col-6">
              <div class="fld">
                <label class="flbl"><span class="km">Name (EN)</span></label>
                <input name="name_en" class="form-control"
                       value="{{ old('name_en', $ward?->name_en) }}"
                       placeholder="Paediatrics"/>
              </div>
            </div>
          </div>

          <div class="row g-2">
            <div class="col-6">
              <div class="fld">
                <label class="flbl"><span class="km">{{ __('app.type') }}</span><span class="req">*</span></label>
                <select name="type" class="form-select">
                  @foreach(['IPD','OPD','ICU','Emergency','Theatre','Outpatient'] as $t)
                  <option value="{{ $t }}" {{ old('type', $ward?->type ?? 'IPD') === $t ? 'selected' : '' }}>
                    {{ $t }}
                  </option>
                  @endforeach
                </select>
              </div>
            </div>
            <div class="col-6">
              <div class="fld">
                <label class="flbl">
                  <span class="km">{{ __('app.bed.capacity') }}</span>
                  <span class="req">*</span>
                </label>
                <input name="capacity" type="number" min="0" class="form-control"
                       value="{{ old('capacity', $ward?->capacity ?? 0) }}"/>
              </div>
            </div>
          </div>

          @if($ward)
          <div class="fld d-flex align-items-center gap-3">
            <label style="display:flex;align-items:center;gap:8px;font-size:13px;cursor:pointer">
              <input type="checkbox" name="is_active" value="1"
                     {{ old('is_active', $ward->is_active) ? 'checked' : '' }}
                     style="width:16px;height:16px">
              Active
            </label>
          </div>
          @endif

          <div class="d-flex gap-2 mt-3 pt-3" style="border-top:1px solid #f0f2ff">
            <button type="submit" class="btn btn-primary">
              <i class="bi bi-check2-circle"></i>
              {{ $ward ? __('app.update') : __('app.save') }}
            </button>
            <a href="{{ route('beds.index') }}" class="btn btn-outline-primary">
              {{ __('app.cancel') }}
            </a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

@endsection
