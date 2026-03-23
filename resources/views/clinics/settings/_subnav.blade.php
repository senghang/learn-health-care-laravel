{{-- Settings sub-navigation tabs --}}
<div class="settings-tabs mb-4">
    @foreach([
        ['settings.general',   'bi-gear-fill',         'ការកំណត់ទូទៅ',   'General'],
        ['settings.templates', 'bi-printer-fill',       'ពុម្ពព្រីន',      'Print Templates'],
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

<style>
.settings-tabs {
    display: flex;
    gap: 4px;
    flex-wrap: wrap;
    background: #f8fafc;
    padding: 6px;
    border-radius: 14px;
    border: 1px solid #e2e8f0;
}
.settings-tab {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 8px 16px;
    border-radius: 10px;
    font-size: 12.5px;
    font-weight: 600;
    text-decoration: none;
    color: #64748b;
    transition: all .15s;
    white-space: nowrap;
}
.settings-tab:hover {
    background: #fff;
    color: #374151;
    box-shadow: 0 1px 6px rgba(1,41,112,.08);
}
.settings-tab--active {
    background: #fff;
    color: #4154f1;
    box-shadow: 0 2px 10px rgba(1,41,112,.10);
    font-weight: 700;
}
.settings-tab--active i { color: #4154f1; }
.settings-tab-km { font-weight: inherit; }
.settings-tab-en {
    font-size: 10px;
    color: #94a3b8;
    font-weight: 400;
}
.settings-tab--active .settings-tab-en { color: #6979de; }
@media(max-width:640px) {
    .settings-tab-en { display: none; }
    .settings-tab { padding: 8px 12px; font-size: 12px; }
}
</style>
