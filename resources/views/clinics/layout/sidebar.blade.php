<nav id="sidebar">

    {{-- ── Brand ──────────────────────────────────────────────────────── --}}
    <div class="sb-brand">
        <div class="sb-logo-icon">
            @if(currentClinic()?->logo)
                <img src="{{ asset('storage/' . currentClinic()->logo) }}" alt=""
                     style="width:34px;height:34px;border-radius:8px;object-fit:cover;display:block">
            @else
                <span style="font-size:19px;line-height:1">⚕</span>
            @endif
        </div>
        <div class="sb-logo-text" id="sbLogoTxt">
            <div class="sb-logo-name">{{ currentClinic()?->name_kh ?? currentClinic()?->name ?? 'MediFlow' }}</div>
            <div class="sb-logo-sub">ប្រព័ន្ធព័ត៌មានសុខភាព</div>
        </div>
        <button class="sb-pin-btn" id="sbPinBtn" onclick="toggleSidebar()" title="Toggle sidebar" aria-label="Toggle sidebar">
            <i class="bi bi-layout-sidebar-reverse" id="sbPinIcon"></i>
        </button>
    </div>

    {{-- ── Navigation ─────────────────────────────────────────────────── --}}
    <div class="sb-nav" id="sbNav">

        {{-- MAIN --}}
        <div class="sb-group-label">MAIN</div>

        @php
            function sbLink(string $label, string $icon, string $route = '', bool $active = false, ?string $badge = null, bool $soon = false, string $title = ''): string {
                // helper not used in Blade — we write directly below
                return '';
            }
        @endphp

        <a class="sb-item {{ request()->routeIs('dashboard') ? 'sb-item--active' : '' }}"
           href="{{ route('dashboard') }}" title="Dashboard">
            <span class="sb-item-icon"><i class="bi bi-grid-1x2-fill"></i></span>
            <span class="sb-item-label">
                <span class="sb-km">ផ្ទាំងគ្រប់គ្រង</span>
                <span class="sb-en">Dashboard</span>
            </span>
        </a>

        {{-- CLINICAL --}}
        <div class="sb-group-label">CLINICAL</div>

        <a class="sb-item {{ request()->routeIs('patients.*') ? 'sb-item--active' : '' }}"
           href="{{ route('patients.index') }}" title="Patients">
            <span class="sb-item-icon"><i class="bi bi-people-fill"></i></span>
            <span class="sb-item-label">
                <span class="sb-km">អ្នកជំងឺ</span>
                <span class="sb-en">Patients</span>
            </span>
        </a>

        <a class="sb-item {{ request()->routeIs('visits.*') ? 'sb-item--active' : '' }}"
           href="{{ route('visits.index') }}" title="Patient Visits">
            <span class="sb-item-icon"><i class="bi bi-hospital-fill"></i></span>
            <span class="sb-item-label">
                <span class="sb-km">ការចូលព្យាបាល</span>
                <span class="sb-en">Patient Visits</span>
            </span>
            @php $todayActive = \App\Models\VisitModel::whereDate('admitted_at', today())->whereNull('discharged_at')->count(); @endphp
            @if($todayActive > 0)
                <span class="sb-badge">{{ $todayActive > 99 ? '99+' : $todayActive }}</span>
            @endif
        </a>

        <a class="sb-item sb-item--new {{ request()->routeIs('workflow.create') ? 'sb-item--active' : '' }}"
           href="{{ route('workflow.create') }}" title="New Visit">
            <span class="sb-item-icon"><i class="bi bi-plus-circle-fill"></i></span>
            <span class="sb-item-label">
                <span class="sb-km">ការចូលថ្មី</span>
                <span class="sb-en">New Visit</span>
            </span>
        </a>

        <a class="sb-item {{ request()->routeIs('beds.*') ? 'sb-item--active' : '' }}"
           href="{{ route('beds.index') }}" title="Wards & Beds">
            <span class="sb-item-icon"><i class="bi bi-building-fill"></i></span>
            <span class="sb-item-label">
                <span class="sb-km">ផ្នែក & គ្រែ</span>
                <span class="sb-en">Wards & Beds</span>
            </span>
        </a>

        {{-- DATA --}}
        <div class="sb-group-label">DATA</div>

        <a class="sb-item sb-item--muted" href="#" onclick="return false" title="Laboratory">
            <span class="sb-item-icon"><i class="bi bi-flask2-fill"></i></span>
            <span class="sb-item-label">
                <span class="sb-km">ពិសោធន៍</span>
                <span class="sb-en">Laboratory</span>
            </span>
            <span class="sb-soon-chip">Soon</span>
        </a>

        <a class="sb-item sb-item--muted" href="#" onclick="return false" title="Prescriptions">
            <span class="sb-item-icon"><i class="bi bi-capsule-fill"></i></span>
            <span class="sb-item-label">
                <span class="sb-km">វេជ្ជបញ្ជា</span>
                <span class="sb-en">Prescriptions</span>
            </span>
            <span class="sb-soon-chip">Soon</span>
        </a>

        <a class="sb-item sb-item--muted" href="#" onclick="return false" title="Billing">
            <span class="sb-item-icon"><i class="bi bi-receipt-cutoff"></i></span>
            <span class="sb-item-label">
                <span class="sb-km">វិក្កយបត្រ</span>
                <span class="sb-en">Billing</span>
            </span>
            <span class="sb-soon-chip">Soon</span>
        </a>

        {{-- ADMIN --}}
        <div class="sb-group-label">ADMIN</div>

        <a class="sb-item {{ request()->routeIs('reports.*') ? 'sb-item--active' : '' }}"
           href="{{ route('reports.visits') }}" title="Reports">
            <span class="sb-item-icon"><i class="bi bi-bar-chart-fill"></i></span>
            <span class="sb-item-label">
                <span class="sb-km">របាយការណ៍</span>
                <span class="sb-en">Reports</span>
            </span>
        </a>

        <a class="sb-item {{ request()->routeIs('settings.*') ? 'sb-item--active' : '' }}"
           href="{{ route('settings.general') }}" title="Settings">
            <span class="sb-item-icon"><i class="bi bi-gear-fill"></i></span>
            <span class="sb-item-label">
                <span class="sb-km">ការកំណត់</span>
                <span class="sb-en">Settings</span>
            </span>
        </a>

    </div>{{-- /sb-nav --}}

    {{-- ── User footer ─────────────────────────────────────────────────── --}}
    <div class="sb-user-footer" id="sbUser">
        <div class="sb-user-av">{{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}</div>
        <div class="sb-user-meta" id="sbUserTxt">
            <div class="sb-user-name-text">{{ auth()->user()->name ?? 'User' }}</div>
            <div class="sb-user-clinic">{{ currentClinic()?->name ?? 'Clinic' }}</div>
        </div>
        <form method="POST" action="{{ route('logout') }}" class="sb-logout">
            @csrf
            <button type="submit" class="sb-logout-btn" title="Log out">
                <i class="bi bi-box-arrow-right"></i>
            </button>
        </form>
    </div>

</nav>

<script>
/* ── Sidebar toggle ─────────────────────────────────────────── */
const isMobile = () => window.innerWidth < 992;

function _applyCollapsed(collapsed) {
    const sb = document.getElementById('sidebar');
    const tb = document.getElementById('topbar');
    const mn = document.getElementById('main');
    const ic = document.getElementById('sbPinIcon');

    [sb, tb, mn].forEach(el => el?.classList.toggle('sb-collapsed', collapsed));
    if (ic) ic.className = collapsed ? 'bi bi-layout-sidebar' : 'bi bi-layout-sidebar-reverse';
    try { localStorage.setItem('sb_col', collapsed ? '1' : '0'); } catch(e) {}
}

function toggleSidebar() {
    if (isMobile()) {
        const sb = document.getElementById('sidebar');
        const ov = document.getElementById('sidebarOverlay');
        const open = sb.classList.contains('mobile-open');
        sb.classList.toggle('mobile-open', !open);
        ov.classList.toggle('show', !open);
    } else {
        _applyCollapsed(!document.getElementById('sidebar').classList.contains('sb-collapsed'));
    }
}

function closeSidebar() {
    if (!isMobile()) return;
    document.getElementById('sidebar')?.classList.remove('mobile-open');
    document.getElementById('sidebarOverlay')?.classList.remove('show');
}

document.addEventListener('DOMContentLoaded', () => {
    try {
        if (!isMobile() && localStorage.getItem('sb_col') === '1') _applyCollapsed(true);
    } catch(e) {}
});

window.addEventListener('resize', () => {
    if (!isMobile()) {
        document.getElementById('sidebar')?.classList.remove('mobile-open');
        document.getElementById('sidebarOverlay')?.classList.remove('show');
    }
});
</script>
