@extends('clinics.layout.app')
@section('title', __('app.settings.medicines'))
@section('content')

<div class="pg-header">
  <div>
    <h1 class="pg-title">{{ __('app.settings.medicines') }} <small>/ Medicine Master</small></h1>
    <div class="breadcrumb-row">
      <a href="{{ route('dashboard') }}">ដើម</a><span>›</span>
      <a href="{{ route('settings.general') }}">{{ __('app.settings.title') }}</a><span>›</span>
      <span>{{ __('app.settings.medicines') }}</span>
    </div>
  </div>
</div>

@include('clinics.settings._subnav', ['active' => 'settings.medicines'])

<div class="row g-3">
  {{-- Add form --}}
  <div class="col-12 col-lg-4">
    <div class="card-emr" style="position:sticky;top:76px">
      <div class="card-hd">
        <div class="card-hd-title"><i class="bi bi-plus-circle-fill" style="color:#e91e8c"></i> Add Medicine</div>
      </div>
      <div class="card-bd">
        <form method="POST" action="{{ route('settings.medicine.store') }}">
          @csrf
          <div class="row g-2">
            <div class="col-6">
              <div class="fld">
                <label class="flbl"><span>Code</span><span class="req">*</span></label>
                <input name="code" class="form-control" value="{{ old('code') }}" placeholder="MED001"/>
              </div>
            </div>
            <div class="col-6">
              <div class="fld">
                <label class="flbl"><span>Form</span></label>
                <select name="form" class="form-select">
                  <option value="">—</option>
                  @foreach(['Tablet','Capsule','Syrup','Injection','Cream','Drops','Inhaler'] as $f)
                  <option value="{{ $f }}" {{ old('form') === $f ? 'selected' : '' }}>{{ $f }}</option>
                  @endforeach
                </select>
              </div>
            </div>
          </div>
          <div class="fld">
            <label class="flbl"><span>Name</span><span class="req">*</span></label>
            <input name="name" class="form-control" value="{{ old('name') }}" placeholder="Amoxicillin"/>
          </div>
          <div class="fld">
            <label class="flbl"><span>ឈ្មោះ (KH)</span></label>
            <input name="name_kh" class="form-control" value="{{ old('name_kh') }}"/>
          </div>
          <div class="fld">
            <label class="flbl"><span>Generic Name</span></label>
            <input name="generic_name" class="form-control" value="{{ old('generic_name') }}"/>
          </div>
          <div class="row g-2">
            <div class="col-6">
              <div class="fld">
                <label class="flbl"><span>Strength</span></label>
                <input name="strength" class="form-control" value="{{ old('strength') }}" placeholder="500mg"/>
              </div>
            </div>
            <div class="col-6">
              <div class="fld">
                <label class="flbl"><span>Unit</span></label>
                <input name="unit" class="form-control" value="{{ old('unit') }}" placeholder="tablet"/>
              </div>
            </div>
          </div>
          <div class="row g-2">
            <div class="col-4">
              <div class="fld">
                <label class="flbl"><span>Price</span><span class="req">*</span></label>
                <input name="price" type="number" class="form-control" value="{{ old('price', 0) }}" min="0"/>
              </div>
            </div>
            <div class="col-4">
              <div class="fld">
                <label class="flbl"><span>Stock</span><span class="req">*</span></label>
                <input name="stock" type="number" class="form-control" value="{{ old('stock', 0) }}" min="0"/>
              </div>
            </div>
            <div class="col-4">
              <div class="fld">
                <label class="flbl"><span>Alert ≤</span></label>
                <input name="stock_alert" type="number" class="form-control" value="{{ old('stock_alert', 10) }}" min="0"/>
              </div>
            </div>
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
        <div class="card-hd-title"><i class="bi bi-capsule-fill"></i> {{ __('app.settings.medicines') }}</div>
        <span style="font-size:11px;color:#aaa">{{ $medicines->total() }} medicines</span>
      </div>
      <div class="card-bd" style="padding:0">
        <table class="tbl">
          <thead>
            <tr>
              <th>Code</th>
              <th>Name</th>
              <th>Form</th>
              <th>Strength</th>
              <th class="text-right">Price</th>
              <th class="text-right">Stock</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            @forelse($medicines as $med)
            @php $lowStock = $med->stock <= $med->stock_alert; @endphp
            <tr style="{{ $lowStock ? 'background:#fff8ee' : '' }}">
              <td><code style="color:#e91e8c;font-size:11px">{{ $med->code }}</code></td>
              <td>
                <div style="font-weight:600">{{ $med->name }}</div>
                @if($med->name_kh)<div style="font-size:10px;color:#888">{{ $med->name_kh }}</div>@endif
                @if($med->generic_name)<div style="font-size:10px;color:#aaa">{{ $med->generic_name }}</div>@endif
              </td>
              <td>
                @if($med->form)
                <span style="background:#fbeaf0;color:#D4537E;padding:2px 6px;border-radius:8px;font-size:10px">
                  {{ $med->form }}
                </span>
                @endif
              </td>
              <td style="font-size:11px;color:#888">{{ $med->strength }}</td>
              <td style="text-align:right;font-size:12px">{{ khr_fmt($med->price) }}</td>
              <td style="text-align:right">
                <span style="font-weight:700;color:{{ $lowStock ? '#e74c3c' : '#2eca6a' }}">
                  {{ $med->stock }}
                </span>
                @if($lowStock)
                <span style="font-size:9px;background:#fde8e8;color:#e74c3c;padding:1px 5px;border-radius:5px;margin-left:3px">
                  LOW
                </span>
                @endif
              </td>
              <td>
                <form method="POST" action="{{ route('settings.medicine.update', $med->id) }}" class="d-inline">
                  @csrf @method('PATCH')
                  <input type="hidden" name="name"        value="{{ $med->name }}">
                  <input type="hidden" name="generic_name"value="{{ $med->generic_name }}">
                  <input type="hidden" name="form"        value="{{ $med->form }}">
                  <input type="hidden" name="strength"    value="{{ $med->strength }}">
                  <input type="hidden" name="price"       value="{{ $med->price }}">
                  <input type="hidden" name="stock"       value="{{ $med->stock }}">
                  <input type="hidden" name="stock_alert" value="{{ $med->stock_alert }}">
                  <input type="hidden" name="is_active"   value="{{ $med->is_active ? '0' : '1' }}">
                  <button type="submit" class="btn btn-sm btn-outline-secondary"
                          title="{{ $med->is_active ? 'Deactivate' : 'Activate' }}">
                    <i class="bi bi-{{ $med->is_active ? 'pause-fill' : 'play-fill' }}"></i>
                  </button>
                </form>
              </td>
            </tr>
            @empty
            <tr><td colspan="7" style="text-align:center;padding:24px;color:#bbb">No medicines yet</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
      @if($medicines->hasPages())
      <div class="card-bd pt-0">{{ $medicines->links() }}</div>
      @endif
    </div>
  </div>
</div>

@endsection
