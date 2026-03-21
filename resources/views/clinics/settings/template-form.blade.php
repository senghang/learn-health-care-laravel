@extends('clinics.layout.app')
@section('title', $template ? 'Edit Template' : 'New Template')
@section('content')

<div class="pg-header">
  <div>
    <h1 class="pg-title">
      {{ $template ? 'Edit Template' : __('app.settings.new_template') }}
    </h1>
    <div class="breadcrumb-row">
      <a href="{{ route('dashboard') }}">ដើម</a><span>›</span>
      <a href="{{ route('settings.templates') }}">{{ __('app.settings.templates') }}</a><span>›</span>
      <span>{{ $template ? 'Edit' : 'New' }}</span>
    </div>
  </div>
  <a href="{{ route('settings.templates') }}" class="btn btn-outline-primary btn-sm">
    <i class="bi bi-arrow-left"></i> {{ __('app.back') }}
  </a>
</div>

<div class="row g-3">
  {{-- Form --}}
  <div class="col-12 col-lg-5">
    <div class="card-emr">
      <div class="card-hd">
        <div class="card-hd-title">
          <i class="bi bi-printer-fill"></i> Template Details
        </div>
      </div>
      <div class="card-bd">
        <form id="tplForm"
              method="POST"
              action="{{ $template ? route('settings.template.update', $template->id) : route('settings.template.store') }}">
          @csrf
          @if($template) @method('PATCH') @endif

          <div class="fld">
            <label class="flbl"><span class="km">Code</span><span class="req">*</span></label>
            <input name="code" class="form-control {{ $errors->has('code') ? 'is-invalid' : '' }}"
                   value="{{ old('code', $template?->code) }}"
                   {{ $template ? 'readonly' : '' }}
                   placeholder="TPL-1-RX-KH"/>
            @error('code')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
          </div>

          <div class="row g-2">
            <div class="col-6">
              <div class="fld">
                <label class="flbl"><span class="km">{{ __('app.settings.template_type') }}</span><span class="req">*</span></label>
                <select name="type" class="form-select">
                  @foreach($types as $t)
                  <option value="{{ $t }}" {{ old('type', $template?->type) === $t ? 'selected' : '' }}>
                    {{ $t }}
                  </option>
                  @endforeach
                </select>
              </div>
            </div>
            <div class="col-6">
              <div class="fld">
                <label class="flbl"><span class="km">{{ __('app.settings.template_locale') }}</span></label>
                <select name="locale" class="form-select">
                  @foreach(['km' => 'ខ្មែរ (km)', 'en' => 'English (en)', 'all' => 'All'] as $v => $l)
                  <option value="{{ $v }}" {{ old('locale', $template?->locale ?? 'km') === $v ? 'selected' : '' }}>
                    {{ $l }}
                  </option>
                  @endforeach
                </select>
              </div>
            </div>
          </div>

          <div class="fld">
            <label class="flbl"><span class="km">Name</span><span class="req">*</span></label>
            <input name="name" class="form-control"
                   value="{{ old('name', $template?->name) }}"
                   placeholder="វេជ្ជបញ្ជា (ភាសាខ្មែរ)"/>
          </div>

          <div class="row g-2">
            <div class="col-6">
              <div class="fld">
                <label class="flbl"><span class="km">{{ __('app.settings.paper_size') }}</span></label>
                <select name="paper_size" class="form-select">
                  @foreach(['A5','A4','Letter'] as $p)
                  <option value="{{ $p }}" {{ old('paper_size', $template?->paper_size ?? 'A5') === $p ? 'selected' : '' }}>{{ $p }}</option>
                  @endforeach
                </select>
              </div>
            </div>
            <div class="col-6">
              <div class="fld">
                <label class="flbl"><span class="km">{{ __('app.settings.orientation') }}</span></label>
                <select name="orientation" class="form-select">
                  <option value="portrait"  {{ old('orientation', $template?->orientation ?? 'portrait')  === 'portrait'  ? 'selected' : '' }}>{{ __('app.settings.portrait') }}</option>
                  <option value="landscape" {{ old('orientation', $template?->orientation ?? 'portrait') === 'landscape' ? 'selected' : '' }}>{{ __('app.settings.landscape') }}</option>
                </select>
              </div>
            </div>
          </div>

          <div class="fld d-flex align-items-center gap-3">
            <label style="display:flex;align-items:center;gap:8px;font-size:13px;cursor:pointer">
              <input type="checkbox" name="is_default" value="1"
                     {{ old('is_default', $template?->is_default) ? 'checked' : '' }}
                     style="width:16px;height:16px">
              {{ __('app.settings.default') }}
            </label>
            @if($template)
            <label style="display:flex;align-items:center;gap:8px;font-size:13px;cursor:pointer">
              <input type="checkbox" name="is_active" value="1"
                     {{ old('is_active', $template?->is_active ?? true) ? 'checked' : '' }}
                     style="width:16px;height:16px">
              Active
            </label>
            @endif
          </div>

          <div class="fld">
            <label class="flbl">
              <span class="km">Template Content</span>
              <span class="en">/ Blade HTML</span>
              <span class="req">*</span>
            </label>
            <div style="font-size:10px;color:#aaa;margin-bottom:4px">
              Variables: <code>$rx</code> <code>$invoice</code> <code>$visit</code> <code>$patient</code>
              · Helpers: <code>df_d()</code> <code>khr()</code> <code>currentClinic()</code>
            </div>
            <textarea name="content" id="tplContent" class="form-control"
                      rows="18"
                      style="font-family:var(--font-mono,'Courier New',monospace);font-size:11px;resize:vertical"
                      oninput="updatePreview()"
                      placeholder="<div>{{ $patient?->surname }}</div>">{{ old('content', $template?->content) }}</textarea>
          </div>

          <div class="d-flex gap-2 mt-3 pt-3" style="border-top:1px solid #f0f2ff">
            <button type="submit" class="btn btn-primary">
              <i class="bi bi-check2-circle"></i>
              {{ $template ? __('app.update') : __('app.save') }}
            </button>
            <a href="{{ route('settings.templates') }}" class="btn btn-outline-primary">
              {{ __('app.cancel') }}
            </a>
          </div>
        </form>
      </div>
    </div>
  </div>

  {{-- Live preview --}}
  <div class="col-12 col-lg-7">
    <div class="card-emr" style="position:sticky;top:76px">
      <div class="card-hd">
        <div class="card-hd-title"><i class="bi bi-eye-fill"></i> Preview</div>
        <span style="font-size:11px;color:#aaa">Approximate render (no Blade variables)</span>
      </div>
      <div class="card-bd" style="padding:0">
        <iframe id="previewFrame"
                style="width:100%;height:500px;border:none;background:#fff;border-radius:0 0 10px 10px"
                sandbox="allow-same-origin"></iframe>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
function updatePreview() {
  const content = document.getElementById('tplContent').value;
  // Simple variable substitution for preview — not real Blade rendering
  const preview = content
    .replace(/\{\{[^}]+\}\}/g, '<span style="background:#fff3e8;color:#ff771d;padding:1px 3px;border-radius:3px;font-size:10px">var</span>')
    .replace(/@\w+[^>]*/g, '')
    .replace(/<\?php[^?]*\?>/g, '');

  const doc = `<!DOCTYPE html><html><head>
    <meta charset="UTF-8">
    <style>
      body{font-family:'Noto Sans Khmer','Nunito',sans-serif;font-size:11pt;padding:16px;color:#000}
      *{box-sizing:border-box}table{width:100%;border-collapse:collapse}
      th,td{padding:3px 6px}
    </style></head><body>${preview}</body></html>`;

  const frame = document.getElementById('previewFrame');
  const blob = new Blob([doc], {type:'text/html'});
  frame.src = URL.createObjectURL(blob);
}
document.addEventListener('DOMContentLoaded', updatePreview);
</script>
@endpush
@endsection
