{{-- Settings sub-navigation tabs --}}
<div class="settings-tabs mb-4">
    @foreach([
        ['settings.general',   'bi-gear-fill',         'ការកំណត់ទូទៅ',   'General'],
        ['settings.services',  'bi-clipboard2-check',   'សេវាថ្លៃ',        'Services'],
        ['settings.medicines', 'bi-capsule-fill',       'ឱសថ & ស្តុក',     'Medicines'],
    ] as [$route, $icon, $km, $en])
    @php $active = request()->routeIs($route); @endphp
    <a href="{{ route($route) }}"
       class="settings-tab {{ $active ? 'settings-tab--active' : '' }}">
        <i class="bi {{ $icon }}"></i>
        <span class="settings-tab-km">{{ $km }}</span>
        <span class="settings-tab-en">{{ $en }}</span>
    </a>
    @endforeach
</div>
