@php
    $menu   = app(\App\Services\MenuService::class);
    $groups = $menu->build();
@endphp

<nav id="sidebar">

    {{-- Brand --}}
    <div class="sb-brand">
        <div class="sb-logo-icon">
            @if(currentClinic()?->logo)
                <img src="{{ asset('storage/'.currentClinic()->logo) }}"
                     style="width:36px;height:36px;object-fit:cover">
            @else
                <i class="bi bi-hospital" style="font-size:17px"></i>
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

    {{-- Navigation --}}
    <div class="sb-nav" id="sbNav">
        @foreach($groups as $group)
            @php $vis = collect($group['items'])->where('visible', true); @endphp
            @if($vis->isEmpty()) @continue @endif

            <div class="sb-group-label"
                 data-group="{{ $group['key'] }}"
                 onclick="toggleGroup('{{ $group['key'] }}')"
                 aria-expanded="true">
                <span>{{ $group['label'] }}</span>
                <i class="bi bi-chevron-down sb-group-chevron" id="chevron-{{ $group['key'] }}"></i>
            </div>

            <div class="sb-group-items" id="group-{{ $group['key'] }}">
                @foreach($group['items'] as $item)
                    @if(!$item['visible']) @continue @endif

                    @if(!empty($item['children']))
                        {{-- Parent item (collapsible submenu) --}}
                        @php $subOpen = $item['active']; @endphp
                        <a class="sb-item sb-item--parent {{ $item['css'] }} {{ $subOpen ? 'sb-item--active sb-open' : '' }}"
                           href="#"
                           data-tooltip="{{ $item['en'] }}"
                           onclick="toggleSubmenu('{{ $item['key'] }}'); return false;"
                           id="parent-{{ $item['key'] }}"
                           aria-expanded="{{ $subOpen ? 'true' : 'false' }}">
                            <span class="sb-item-icon {{ $item['icon_css'] }}">
                                <i class="bi {{ $item['icon'] }}"></i>
                            </span>
                            <span class="sb-item-label">
                                <span class="sb-km"
                                      @if(($item['key'] ?? '') === 'new_visit') style="font-weight:800" @endif>{{ $item['km'] }}</span>
                                <span class="sb-en">{{ $item['en'] }}</span>
                            </span>
                            @if($item['badge'])
                                @php $bv = $menu->badge($item['badge']); @endphp
                                @if($bv && $bv !== '0')
                                    <span class="sb-badge">{{ $bv }}</span>
                                @endif
                            @endif
                            <i class="bi bi-chevron-down sb-sub-chevron" id="sub-chevron-{{ $item['key'] }}"
                               style="{{ $subOpen ? 'transform:rotate(-180deg)' : '' }}"></i>
                        </a>

                        <div class="sb-submenu {{ $subOpen ? 'sb-submenu--open' : '' }}"
                             id="submenu-{{ $item['key'] }}">
                            @foreach($item['children'] as $child)
                                @if(!$child['visible']) @continue @endif
                                <a class="sb-item sb-item--child {{ $child['active'] ? 'sb-item--active' : '' }}"
                                   href="{{ $child['url'] }}">
                                    <span class="sb-item-label">
                                        <span class="sb-km">{{ $child['km'] }}</span>
                                        <span class="sb-en">{{ $child['en'] }}</span>
                                    </span>
                                    @if(!empty($child['badge']))
                                        @php $cbv = $menu->badge($child['badge']); @endphp
                                        @if($cbv && $cbv !== '0')
                                            <span class="sb-badge">{{ $cbv }}</span>
                                        @endif
                                    @endif
                                </a>
                            @endforeach
                        </div>

                    @else
                        {{-- Regular flat item --}}
                        <a class="sb-item {{ $item['css'] }} {{ $item['active'] ? 'sb-item--active' : '' }}"
                           href="{{ $item['url'] }}"
                           data-tooltip="{{ $item['en'] }}">
                            <span class="sb-item-icon {{ $item['icon_css'] }}">
                                <i class="bi {{ $item['icon'] }}"></i>
                            </span>
                            <span class="sb-item-label">
                                <span class="sb-km"
                                      @if(($item['key'] ?? '') === 'new_visit') style="font-weight:800" @endif>{{ $item['km'] }}</span>
                                <span class="sb-en">{{ $item['en'] }}</span>
                            </span>
                            @if($item['badge'])
                                @php $bv = $menu->badge($item['badge']); @endphp
                                @if($bv && $bv !== '0')
                                    <span class="sb-badge">{{ $bv }}</span>
                                @endif
                            @endif
                        </a>
                    @endif
                @endforeach
            </div>
        @endforeach
    </div>

    @include('clinics.layout._sidebar-footer')
</nav>

<div id="sb-tooltip" style="display:none"></div>

<script>
(function () {
    const isMobile = () => window.innerWidth < 992;

    /* ── Submenu (class-based for CSS transition) ──────────────── */
    window.toggleSubmenu = function (key) {
        const el  = document.getElementById('submenu-' + key);
        const ch  = document.getElementById('sub-chevron-' + key);
        const par = document.getElementById('parent-' + key);
        if (!el) return;

        const open = el.classList.contains('sb-submenu--open');
        el.classList.toggle('sb-submenu--open', !open);
        if (ch)  ch.style.transform = !open ? 'rotate(-180deg)' : '';
        if (par) { par.classList.toggle('sb-open', !open); par.setAttribute('aria-expanded', String(!open)); }

        try {
            const s = JSON.parse(localStorage.getItem('sb_sub') || '{}');
            s[key] = !open;
            localStorage.setItem('sb_sub', JSON.stringify(s));
        } catch (_) {}
    };

    /* ── Collapse / expand sidebar ─────────────────────────────── */
    function applyCollapsed(c) {
        ['sidebar', 'topbar', 'main'].forEach(id =>
            document.getElementById(id)?.classList.toggle('sb-collapsed', c)
        );
        const ic = document.getElementById('sbPinIcon');
        if (ic) ic.className = c ? 'bi bi-layout-sidebar' : 'bi bi-layout-sidebar-reverse';
        try { localStorage.setItem('sb_col', c ? '1' : '0'); } catch (_) {}
    }

    window.toggleSidebar = function () {
        if (isMobile()) {
            const sb = document.getElementById('sidebar');
            const ov = document.getElementById('sidebarOverlay');
            const o  = sb?.classList.contains('mobile-open');
            sb?.classList.toggle('mobile-open', !o);
            ov?.classList.toggle('show', !o);
        } else {
            applyCollapsed(!document.getElementById('sidebar')?.classList.contains('sb-collapsed'));
        }
    };

    window.closeSidebar = function () {
        if (!isMobile()) return;
        document.getElementById('sidebar')?.classList.remove('mobile-open');
        document.getElementById('sidebarOverlay')?.classList.remove('show');
    };

    /* ── Group toggle ───────────────────────────────────────────── */
    window.toggleGroup = function (key) {
        const el = document.getElementById('group-' + key);
        const ch = document.getElementById('chevron-' + key);
        if (!el) return;
        const hidden = el.style.display === 'none';
        el.style.display = hidden ? '' : 'none';
        if (ch) ch.style.transform = hidden ? '' : 'rotate(-90deg)';
        try {
            const s = JSON.parse(localStorage.getItem('sb_grp') || '{}');
            s[key] = !hidden;
            localStorage.setItem('sb_grp', JSON.stringify(s));
        } catch (_) {}
    };

    /* ── Tooltip (collapsed mode) ───────────────────────────────── */
    function initTooltips() {
        const tip = document.getElementById('sb-tooltip');
        if (!tip) return;
        document.querySelectorAll('.sb-item[data-tooltip]').forEach(el => {
            el.addEventListener('mouseenter', function () {
                if (!document.getElementById('sidebar')?.classList.contains('sb-collapsed')) return;
                const rect = el.getBoundingClientRect();
                tip.textContent = el.dataset.tooltip;
                tip.style.top  = (rect.top + rect.height / 2) + 'px';
                tip.style.display = 'block';
            });
            el.addEventListener('mouseleave', () => { tip.style.display = 'none'; });
        });
    }

    /* ── Restore state on load ──────────────────────────────────── */
    document.addEventListener('DOMContentLoaded', function () {
        // Collapsed
        try {
            if (!isMobile() && localStorage.getItem('sb_col') === '1') applyCollapsed(true);
        } catch (_) {}

        // Group visibility
        try {
            const s = JSON.parse(localStorage.getItem('sb_grp') || '{}');
            Object.entries(s).forEach(([k, vis]) => {
                if (!vis) {
                    const el = document.getElementById('group-' + k);
                    const ch = document.getElementById('chevron-' + k);
                    if (el) el.style.display = 'none';
                    if (ch) ch.style.transform = 'rotate(-90deg)';
                }
            });
        } catch (_) {}

        // Submenu open/closed
        try {
            const ss = JSON.parse(localStorage.getItem('sb_sub') || '{}');
            Object.entries(ss).forEach(([k, open]) => {
                const el  = document.getElementById('submenu-' + k);
                const ch  = document.getElementById('sub-chevron-' + k);
                const par = document.getElementById('parent-' + k);
                if (!el) return;
                // Never force-close an active route's submenu
                const hasActive = el.querySelector('.sb-item--active');
                if (hasActive) return;
                if (open && !el.classList.contains('sb-submenu--open')) {
                    el.classList.add('sb-submenu--open');
                    if (ch)  ch.style.transform = 'rotate(-180deg)';
                    if (par) par.classList.add('sb-open');
                } else if (!open && el.classList.contains('sb-submenu--open')) {
                    el.classList.remove('sb-submenu--open');
                    if (ch)  ch.style.transform = '';
                    if (par) par.classList.remove('sb-open');
                }
            });
        } catch (_) {}

        initTooltips();
    });

    window.addEventListener('resize', function () {
        if (!isMobile()) {
            document.getElementById('sidebar')?.classList.remove('mobile-open');
            document.getElementById('sidebarOverlay')?.classList.remove('show');
        }
    });
}());
</script>
