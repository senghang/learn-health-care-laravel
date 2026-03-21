@extends('clinics.layout.app')
@section('title', __('app.settings.services'))
@section('content')

<div class="pg-header">
  <div>
    <h1 class="pg-title">{{ __('app.settings.services') }} <small>/ Service Master</small></h1>
    <div class="breadcrumb-row">
      <a href="{{ route('dashboard') }}">ដើម</a><span>›</span>
      <a href="{{ route('settings.general') }}">{{ __('app.settings.title') }}</a><span>›</span>
      <span>{{ __('app.settings.services') }}</span>
    </div>
  </div>
</div>

@include('clinics.settings._subnav', ['active' => 'settings.services'])

<div class="row g-3">
  {{-- Add form --}}
  <div class="col-12 col-lg-4">
    <div class="card-emr" style="position:sticky;top:76px">
      <div class="card-hd">
        <div class="card-hd-title"><i class="bi bi-plus-circle-fill" style="color:#2eca6a"></i> Add Service</div>
      </div>
      <div class="card-bd">
        <form method="POST" action="{{ route('settings.service.store') }}">
          @csrf
          <div class="fld">
            <label class="flbl"><span class="km">Code</span><span class="req">*</span></label>
            <input name="code" class="form-control" value="{{ old('code') }}" placeholder="SRV001"/>
          </div>
          <div class="fld">
            <label class="flbl"><span class="km">{{ __('app.name') }}</span><span class="req">*</span></label>
            <input name="name" class="form-control" value="{{ old('name') }}" placeholder="OPD Consultation"/>
          </div>
          <div class="fld">
            <label class="flbl"><span class="km">ឈ្មោះ (KH)</span></label>
            <input name="name_kh" class="form-control" value="{{ old('name_kh') }}" placeholder="ការពិនិត្យ OPD"/>
          </div>
          <div class="fld">
            <label class="flbl"><span class="km">ប្រភេទ</span><span class="en">/ Category</span></label>
            <select name="category" class="form-select">
              <option value="">— Select —</option>
              @foreach(['Consultation','Laboratory','Imaging','Procedure','Pharmacy','Other'] as $cat)
              <option value="{{ $cat }}" {{ old('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
              @endforeach
            </select>
          </div>
          <div class="fld">
            <label class="flbl"><span class="km">{{ __('app.billing.grand_total') }} (KHR)</span><span class="req">*</span></label>
            <input name="price" type="number" class="form-control" value="{{ old('price', 0) }}" min="0"/>
          </div>
          <button type="submit" class="btn btn-primary btn-w100">
            <i class="bi bi-plus-circle"></i> {{ __('app.add') }}
          </button>
        </form>
      </div>
    </div>
  </div>

  {{-- List --}}
  <div class="col-12 col-lg-8">
    <div class="card-emr">
      <div class="card-hd">
        <div class="card-hd-title"><i class="bi bi-list-check"></i> {{ __('app.settings.services') }}</div>
        <span style="font-size:11px;color:#aaa">{{ $services->total() }} services</span>
      </div>
      <div class="card-bd" style="padding:0">
        <table class="tbl">
          <thead>
            <tr>
              <th>Code</th>
              <th>Name</th>
              <th>KH Name</th>
              <th>Category</th>
              <th class="text-right">Price (KHR)</th>
              <th>Status</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            @forelse($services as $svc)
            <tr>
              <td><code style="color:#4154f1;font-size:11px">{{ $svc->code }}</code></td>
              <td style="font-weight:600">{{ $svc->name }}</td>
              <td style="color:#888">{{ $svc->name_kh ?? '—' }}</td>
              <td>
                @if($svc->category)
                <span style="background:#eef0fd;color:#4154f1;padding:2px 8px;border-radius:10px;font-size:10px">
                  {{ $svc->category }}
                </span>
                @endif
              </td>
              <td style="text-align:right;font-weight:700">{{ khr_fmt($svc->price) }}</td>
              <td>
                <span class="badge-s {{ $svc->is_active ? 'b-active' : '' }}"
                      style="{{ !$svc->is_active ? 'background:#fde8e8;color:#e74c3c' : '' }}">
                  {{ $svc->is_active ? 'Active' : 'Inactive' }}
                </span>
              </td>
              <td>
                <form method="POST" action="{{ route('settings.service.update', $svc->id) }}" class="d-inline">
                  @csrf @method('PATCH')
                  <input type="hidden" name="name"      value="{{ $svc->name }}">
                  <input type="hidden" name="price"     value="{{ $svc->price }}">
                  <input type="hidden" name="is_active" value="{{ $svc->is_active ? '0' : '1' }}">
                  <button type="submit" class="btn btn-sm btn-outline-secondary"
                          title="{{ $svc->is_active ? 'Deactivate' : 'Activate' }}">
                    <i class="bi bi-{{ $svc->is_active ? 'pause-fill' : 'play-fill' }}"></i>
                  </button>
                </form>
              </td>
            </tr>
            @empty
            <tr><td colspan="7" style="text-align:center;padding:24px;color:#bbb">No services yet</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
      @if($services->hasPages())
      <div class="card-bd pt-0">{{ $services->links() }}</div>
      @endif
    </div>
  </div>
</div>

@endsection
