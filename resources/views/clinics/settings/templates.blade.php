@extends('clinics.layout.app')
@section('title', __('app.settings.templates'))
@section('content')

<div class="pg-header">
  <div>
    <h1 class="pg-title">{{ __('app.settings.templates') }} <small>/ Print Templates</small></h1>
    <div class="breadcrumb-row">
      <a href="{{ route('dashboard') }}">ដើម</a><span>›</span>
      <a href="{{ route('settings.general') }}">{{ __('app.settings.title') }}</a><span>›</span>
      <span>{{ __('app.settings.templates') }}</span>
    </div>
  </div>
  <a href="{{ route('settings.template.create') }}" class="btn btn-primary">
    <i class="bi bi-plus-lg"></i> {{ __('app.settings.new_template') }}
  </a>
</div>

{{-- Sub-nav --}}
@include('clinics.settings._subnav', ['active' => 'settings.templates'])

<div class="card-emr">
  <div class="card-hd">
    <div class="card-hd-title">
      <i class="bi bi-printer-fill"></i> {{ __('app.settings.templates') }}
    </div>
  </div>
  <div class="card-bd" style="padding:0">
    <table class="tbl">
      <thead>
        <tr>
          <th>Code</th>
          <th>{{ __('app.settings.template_type') }}</th>
          <th>Name</th>
          <th>{{ __('app.settings.template_locale') }}</th>
          <th>{{ __('app.settings.paper_size') }}</th>
          <th>{{ __('app.settings.default') }}</th>
          <th>Status</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        @forelse($templates as $tpl)
        <tr>
          <td><code style="color:#4154f1;font-size:11px">{{ $tpl->code }}</code></td>
          <td>
            <span style="background:#eef0fd;color:#4154f1;padding:2px 8px;border-radius:10px;font-size:11px;font-weight:700">
              {{ $tpl->type }}
            </span>
          </td>
          <td style="font-weight:600">{{ $tpl->name }}</td>
          <td>
            <span style="background:#e8f8ef;color:#1D9E75;padding:2px 8px;border-radius:10px;font-size:11px;font-weight:700">
              {{ strtoupper($tpl->locale) }}
            </span>
          </td>
          <td style="font-size:12px;color:#888">{{ $tpl->paper_size }} · {{ $tpl->orientation }}</td>
          <td>
            @if($tpl->is_default)
              <span style="background:#fff3e8;color:#ff771d;padding:2px 8px;border-radius:10px;font-size:10px;font-weight:700">
                DEFAULT
              </span>
            @else
              <span style="color:#ccc;font-size:11px">—</span>
            @endif
          </td>
          <td>
            @if($tpl->is_active)
              <span class="badge-s b-active">Active</span>
            @else
              <span class="badge-s" style="background:#fde8e8;color:#e74c3c">Inactive</span>
            @endif
          </td>
          <td>
            <a href="{{ route('settings.template.edit', $tpl->id) }}" class="btn btn-sm btn-outline-primary">
              <i class="bi bi-pencil"></i>
            </a>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="8" style="text-align:center;padding:32px;color:#bbb">
            <div style="font-size:28px;margin-bottom:8px;opacity:.4">🖨</div>
            <div>No templates yet — <a href="{{ route('settings.template.create') }}">create one</a></div>
          </td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

@endsection
