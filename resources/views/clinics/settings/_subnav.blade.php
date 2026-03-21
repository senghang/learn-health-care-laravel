{{-- _subnav.blade.php — include in each settings view --}}
<div style="display:flex;gap:4px;flex-wrap:wrap;margin-bottom:16px">
  @foreach([
    ['settings.general',   'bi-gear-fill',   __('app.settings.general')],
    ['settings.templates', 'bi-printer-fill', __('app.settings.templates')],
    ['settings.services',  'bi-list-check',   __('app.settings.services')],
    ['settings.medicines', 'bi-capsule-fill', __('app.settings.medicines')],
  ] as [$r, $i, $l])
  <a href="{{ route($r) }}"
     style="display:inline-flex;align-items:center;gap:6px;padding:6px 14px;border-radius:20px;
            font-size:12px;text-decoration:none;font-weight:500;
            border:1px solid {{ request()->routeIs($r) ? '#4154f1' : '#e6eaf5' }};
            background:{{ request()->routeIs($r) ? '#4154f1' : '#f6f9ff' }};
            color:{{ request()->routeIs($r) ? '#fff' : '#888' }}">
    <i class="bi {{ $i }}"></i> {{ $l }}
  </a>
  @endforeach
</div>
