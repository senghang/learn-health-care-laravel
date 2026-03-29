@php
    // Use MenuService if available, otherwise render static sidebar
    use App\Services\MenuService;$useMenuService = class_exists(MenuService::class);
@endphp

@if($useMenuService)
    {{-- ══ DYNAMIC MENU (from MenuService) ══════════════════════════════════ --}}
    @php
        $menu = app(MenuService::class);
        $groups = $menu->build();
    @endphp

    <nav id="sidebar">
        <div class="sb-brand">
            <div class="sb-logo-icon">
                @if(currentClinic()?->logo)
                    <img src="{{ asset('storage/'.currentClinic()->logo) }}"
                         style="width:34px;height:34px;border-radius:8px;object-fit:cover">
                @else
                    <span style="font-size:19px;line-height:1">⚕</span>
                @endif
            </div>
            <div class="sb-logo-text" id="sbLogoTxt">
                <div class="sb-logo-name">{{ currentClinic()?->name_kh ?? currentClinic()?->name ?? 'MediFlow' }}</div>
                <div class="sb-logo-sub">{{ currentClinic()?->tagline ?? 'EMR System' }}</div>
            </div>
            <button class="sb-pin-btn" id="sbPinBtn" onclick="toggleSidebar()" title="Toggle sidebar">
                <i class="bi bi-layout-sidebar-reverse" id="sbPinIcon"></i>
            </button>
        </div>

        <div class="sb-nav" id="sbNav">
            @foreach($groups as $group)
                @php $vis = collect($group['items'])->where('visible', true); @endphp
                @if($vis->isEmpty())
                    @continue
                @endif

                <div class="sb-group-label" data-group="{{ $group['key'] }}"
                     onclick="toggleGroup('{{ $group['key'] }}')" style="cursor:pointer;user-select:none">
                    <span>{{ $group['emoji'] }} {{ $group['label'] }}</span>
                    <i class="bi bi-chevron-down sb-group-chevron" id="chevron-{{ $group['key'] }}"
                       style="font-size:9px;margin-left:auto;transition:transform .2s"></i>
                </div>

                <div class="sb-group-items" id="group-{{ $group['key'] }}">
                    @foreach($group['items'] as $item)
                        @if(!$item['visible'])
                            @continue
                        @endif
                        <a class="sb-item {{ $item['css'] }} {{ $item['active'] ? 'sb-item--active' : '' }}"
                           href="{{ $item['url'] }}" data-tooltip="{{ $item['en'] }}">
                            <span class="sb-item-icon {{ $item['icon_css'] }}"><i
                                    class="bi {{ $item['icon'] }}"></i></span>
                            <span class="sb-item-label">
                                <span class="sb-km"
                                      @if($item['key']==='new_visit') style="font-weight:700" @endif>{{ $item['km'] }}</span>
                                <span class="sb-en">{{ $item['en'] }}</span>
                            </span>
                            @if($item['badge'])
                                @php $bv = $menu->badge($item['badge']); @endphp
                                @if($bv && $bv !== '0')
                                    <span class="sb-badge">{{ $bv }}</span>
                                @endif
                            @endif
                        </a>
                    @endforeach
                </div>
            @endforeach
        </div>

        @include('clinics.layout._sidebar-footer')
    </nav>

@else
    {{-- ══ STATIC FALLBACK (existing sidebar — no MenuService) ══════════════ --}}
    <nav id="sidebar">
        <div class="sb-brand">
            <div class="sb-logo-icon">
                @if(currentClinic()?->logo)
                    <img src="{{ asset('storage/'.currentClinic()->logo) }}"
                         style="width:34px;height:34px;border-radius:8px;object-fit:cover">
                @else
                    <span style="font-size:19px;line-height:1">⚕</span>
                @endif
            </div>
            <div class="sb-logo-text" id="sbLogoTxt">
                <div class="sb-logo-name">{{ currentClinic()?->name_kh ?? currentClinic()?->name ?? 'MediFlow' }}</div>
                <div class="sb-logo-sub">ប្រព័ន្ធព័ត៌មានសុខភាព</div>
            </div>
            <button class="sb-pin-btn" id="sbPinBtn" onclick="toggleSidebar()" title="Collapse sidebar">
                <i class="bi bi-layout-sidebar-reverse" id="sbPinIcon"></i>
            </button>
        </div>

        <div class="sb-nav" id="sbNav">
            <div class="sb-group-label">🧾 OPERATIONS</div>

            <a class="sb-item {{ request()->routeIs('dashboard') ? 'sb-item--active' : '' }}"
               href="{{ route('dashboard') }}" data-tooltip="Dashboard">
                <span class="sb-item-icon"><i class="bi bi-grid-1x2-fill"></i></span>
                <span class="sb-item-label"><span class="sb-km">ផ្ទាំងគ្រប់គ្រង</span><span
                        class="sb-en">Dashboard</span></span>
            </a>

            <a class="sb-item sb-item--new-visit {{ request()->routeIs('workflow.create') ? 'sb-item--active' : '' }}"
               href="{{ route('workflow.create') }}" data-tooltip="New Visit">
                <span class="sb-item-icon sb-item-icon--accent"><i class="bi bi-plus-circle-fill"></i></span>
                <span class="sb-item-label"><span class="sb-km" style="font-weight:700">ការចូលព្យាបាលថ្មី</span><span
                        class="sb-en">New Visit</span></span>
            </a>

            <a class="sb-item {{ request()->routeIs('patients.*') ? 'sb-item--active' : '' }}"
               href="{{ route('patients.index') }}" data-tooltip="Patients">
                <span class="sb-item-icon"><i class="bi bi-people-fill"></i></span>
                <span class="sb-item-label"><span class="sb-km">អ្នកជំងឺ</span><span
                        class="sb-en">Patients</span></span>
            </a>

            <a class="sb-item {{ request()->routeIs('visits.*') || request()->routeIs('workflow.*') ? 'sb-item--active' : '' }}"
               href="{{ route('visits.index') }}" data-tooltip="Visits">
                <span class="sb-item-icon"><i class="bi bi-hospital-fill"></i></span>
                <span class="sb-item-label"><span class="sb-km">ការចូលព្យាបាល</span><span
                        class="sb-en">Visits</span></span>
            </a>

            <a class="sb-item {{ request()->routeIs('prescriptions.*') ? 'sb-item--active' : '' }}"
               href="{{ route('prescriptions.index') }}" data-tooltip="Prescriptions">
                <span class="sb-item-icon"><i class="bi bi-file-earmark-medical-fill"></i></span>
                <span class="sb-item-label"><span class="sb-km">វេជ្ជបញ្ជា</span><span
                        class="sb-en">Prescriptions</span></span>
            </a>

            <div class="sb-group-label">🏥 IPD</div>

            <a class="sb-item {{ request()->routeIs('beds.*') ? 'sb-item--active' : '' }}"
               href="{{ route('beds.index') }}" data-tooltip="Beds & Wards">
                <span class="sb-item-icon"><i class="bi bi-building-fill"></i></span>
                <span class="sb-item-label"><span class="sb-km">បន្ទប់ & គ្រែ</span><span
                        class="sb-en">Beds & Wards</span></span>
            </a>

            <div class="sb-group-label">💊 PHARMACY & INVENTORY</div>

            <a class="sb-item {{ request()->routeIs('inventory.products') ? 'sb-item--active' : '' }}"
               href="{{ route('inventory.products') }}" data-tooltip="Products">
                <span class="sb-item-icon"><i class="bi bi-box-seam-fill"></i></span>
                <span class="sb-item-label"><span class="sb-km">ថ្នាំ / ផលិតផល</span><span class="sb-en">Products</span></span>
            </a>

            <a class="sb-item {{ request()->routeIs('inventory.stock-in') ? 'sb-item--active' : '' }}"
               href="{{ route('inventory.stock-in') }}" data-tooltip="Stock In">
                <span class="sb-item-icon sb-item-icon--green"><i class="bi bi-box-arrow-in-down"></i></span>
                <span class="sb-item-label"><span class="sb-km">ស្តុកចូល</span><span
                        class="sb-en">Stock In</span></span>
            </a>

            <a class="sb-item {{ request()->routeIs('inventory.stock-out') ? 'sb-item--active' : '' }}"
               href="{{ route('inventory.stock-out') }}" data-tooltip="Stock Out">
                <span class="sb-item-icon sb-item-icon--red"><i class="bi bi-box-arrow-up"></i></span>
                <span class="sb-item-label"><span class="sb-km">ស្តុកចេញ</span><span
                        class="sb-en">Stock Out</span></span>
            </a>

            <div class="sb-group-label">💰 BILLING</div>

            <a class="sb-item {{ request()->routeIs('invoices.*') ? 'sb-item--active' : '' }}"
               href="{{ route('invoices.index') }}" data-tooltip="Invoices">
                <span class="sb-item-icon"><i class="bi bi-receipt-cutoff"></i></span>
                <span class="sb-item-label"><span class="sb-km">វិក្កយបត្រ</span><span
                        class="sb-en">Invoices</span></span>
            </a>

            <div class="sb-group-label">👨‍💼 HR & REPORTS</div>

            <a class="sb-item {{ request()->routeIs('reports.*') ? 'sb-item--active' : '' }}"
               href="{{ route('reports.visits') }}" data-tooltip="Reports">
                <span class="sb-item-icon"><i class="bi bi-bar-chart-line-fill"></i></span>
                <span class="sb-item-label"><span class="sb-km">របាយការណ៍</span><span
                        class="sb-en">Reports</span></span>
            </a>

            <div class="sb-group-label">⚙️ SYSTEM</div>

            <a class="sb-item {{ request()->routeIs('settings.*') ? 'sb-item--active' : '' }}"
               href="{{ route('settings.general') }}" data-tooltip="Settings">
                <span class="sb-item-icon"><i class="bi bi-gear-fill"></i></span>
                <span class="sb-item-label"><span class="sb-km">ការកំណត់</span><span
                        class="sb-en">Settings</span></span>
            </a>

            <a class="sb-item {{ request()->routeIs('settings.roles*') ? 'sb-item--active' : '' }}"
               href="{{ route('settings.roles') }}" data-tooltip="Roles">
                <span class="sb-item-icon"><i class="bi bi-shield-lock-fill"></i></span>
                <span class="sb-item-label"><span class="sb-km">តួនាទី & សិទ្ធិ</span><span class="sb-en">Roles & Permissions</span></span>
            </a>
        </div>

        @include('clinics.layout._sidebar-footer')
    </nav>
@endif

<script>
    const isMobile = () => window.innerWidth < 992;

    function _applyCollapsed(c) {
        ['sidebar', 'topbar', 'main'].forEach(id => document.getElementById(id)?.classList.toggle('sb-collapsed', c));
        const ic = document.getElementById('sbPinIcon');
        if (ic) ic.className = c ? 'bi bi-layout-sidebar' : 'bi bi-layout-sidebar-reverse';
        try {
            localStorage.setItem('sb_col', c ? '1' : '0');
        } catch (_) {
        }
    }

    function toggleSidebar() {
        if (isMobile()) {
            const sb = document.getElementById('sidebar'), ov = document.getElementById('sidebarOverlay');
            const o = sb?.classList.contains('mobile-open');
            sb?.classList.toggle('mobile-open', !o);
            ov?.classList.toggle('show', !o);
        } else {
            _applyCollapsed(!document.getElementById('sidebar')?.classList.contains('sb-collapsed'));
        }
    }

    function closeSidebar() {
        if (!isMobile()) return;
        document.getElementById('sidebar')?.classList.remove('mobile-open');
        document.getElementById('sidebarOverlay')?.classList.remove('show');
    }

    function toggleGroup(key) {
        const el = document.getElementById('group-' + key), ch = document.getElementById('chevron-' + key);
        if (!el) return;
        const h = el.style.display === 'none';
        el.style.display = h ? '' : 'none';
        if (ch) ch.style.transform = h ? '' : 'rotate(-90deg)';
        try {
            const s = JSON.parse(localStorage.getItem('sb_groups') || '{}');
            s[key] = !h;
            localStorage.setItem('sb_groups', JSON.stringify(s));
        } catch (_) {
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        try {
            if (!isMobile() && localStorage.getItem('sb_col') === '1') _applyCollapsed(true);
        } catch (_) {
        }
        try {
            const s = JSON.parse(localStorage.getItem('sb_groups') || '{}');
            Object.entries(s).forEach(([k, c]) => {
                if (c) {
                    const el = document.getElementById('group-' + k), ch = document.getElementById('chevron-' + k);
                    if (el) el.style.display = 'none';
                    if (ch) ch.style.transform = 'rotate(-90deg)';
                }
            });
        } catch (_) {
        }
        document.querySelectorAll('.sb-item[data-tooltip]').forEach(el => {
            el.addEventListener('mouseenter', function () {
                if (!document.getElementById('sidebar')?.classList.contains('sb-collapsed')) return;
                const tip = document.getElementById('sb-tooltip'), rect = el.getBoundingClientRect();
                if (tip) {
                    tip.textContent = el.dataset.tooltip;
                    tip.style.top = (rect.top + rect.height / 2) + 'px';
                    tip.style.display = 'block';
                }
            });
            el.addEventListener('mouseleave', () => {
                const tip = document.getElementById('sb-tooltip');
                if (tip) tip.style.display = 'none';
            });
        });
    });
    window.addEventListener('resize', () => {
        if (!isMobile()) {
            document.getElementById('sidebar')?.classList.remove('mobile-open');
            document.getElementById('sidebarOverlay')?.classList.remove('show');
        }
    });
</script>
<div id="sb-tooltip"
     style="display:none;position:fixed;left:68px;transform:translateY(-50%);background:#1e2d5a;color:#fff;font-size:12px;font-weight:600;padding:5px 12px;border-radius:8px;z-index:9999;pointer-events:none;white-space:nowrap;box-shadow:0 4px 16px rgba(0,0,0,.3)"></div>
