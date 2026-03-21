<nav id="sidebar">

    {{-- Logo --}}
    <div class="sb-logo">
        <div class="sb-logo-icon">
            @if(currentClinic()?->logo)
                <img src="{{ asset('storage/' . currentClinic()->logo) }}" alt="logo"
                     style="width:36px;height:36px;object-fit:contain;border-radius:8px">
            @else
                <span style="font-size:20px">⚕</span>
            @endif
        </div>
        <div class="sb-logo-text" id="sbLogoTxt">
            <div class="sb-logo-title">{{ currentClinic()?->name ?? 'MediFlow' }}</div>
            <div class="sb-logo-sub">EMR System</div>
        </div>
        <button class="sb-collapse-btn" onclick="toggleSidebar()" title="Collapse sidebar">
            <i class="bi bi-layout-sidebar-reverse"></i>
        </button>
    </div>

    {{-- Nav --}}
    <div class="sidebar-nav">

        {{-- Dashboard --}}
        <div class="nav-sec">ទំព័រដើម</div>
        <a class="s-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
           href="{{ route('dashboard') }}">
            <span class="s-icon"><i class="bi bi-grid-1x2-fill"></i></span>
            <span class="s-text">
                <span class="s-km">ផ្ទាំងគ្រប់គ្រង</span>
                <span class="s-en">Dashboard</span>
            </span>
        </a>

        {{-- Clinical --}}
        <div class="nav-sec">ព្យាបាល / Clinical</div>

        <a class="s-link {{ request()->routeIs('visits.*') ? 'active' : '' }}"
           href="{{ route('visits.index') }}">
            <span class="s-icon"><i class="bi bi-hospital-fill"></i></span>
            <span class="s-text">
                <span class="s-km">ការចូលព្យាបាល</span>
                <span class="s-en">Patient Visits</span>
            </span>
            @php $todayCount = \App\Models\VisitModel::whereDate('admitted_at', today())->whereNull('discharged_at')->count(); @endphp
            @if($todayCount > 0)
                <span class="s-badge">{{ $todayCount }}</span>
            @endif
        </a>

        <a class="s-link {{ request()->routeIs('workflow.*') ? 'active' : '' }}"
           href="{{ route('workflow.create') }}">
            <span class="s-icon"><i class="bi bi-diagram-3-fill"></i></span>
            <span class="s-text">
                <span class="s-km">ការព្យាបាលថ្មី</span>
                <span class="s-en">New Visit</span>
            </span>
            <span class="s-pill new">New</span>
        </a>

        {{-- Data --}}
        <div class="nav-sec">ទិន្នន័យ / Data</div>

        <a class="s-link {{ request()->routeIs('patients.*') ? 'active' : '' }}"
           href="#">
            <span class="s-icon"><i class="bi bi-people-fill"></i></span>
            <span class="s-text">
                <span class="s-km">អ្នកជំងឺ</span>
                <span class="s-en">Patients</span>
            </span>
        </a>

        <a class="s-link" href="#">
            <span class="s-icon"><i class="bi bi-flask2-fill"></i></span>
            <span class="s-text">
                <span class="s-km">មន្ទីរពិសោធន៍</span>
                <span class="s-en">Laboratory</span>
            </span>
        </a>

        <a class="s-link" href="#">
            <span class="s-icon"><i class="bi bi-capsule-pill"></i></span>
            <span class="s-text">
                <span class="s-km">បញ្ជាថ្នាំ</span>
                <span class="s-en">Prescriptions</span>
            </span>
        </a>

        <a class="s-link" href="#">
            <span class="s-icon"><i class="bi bi-receipt-cutoff"></i></span>
            <span class="s-text">
                <span class="s-km">វិក្កយបត្រ</span>
                <span class="s-en">Billing</span>
            </span>
        </a>

        {{-- Reports --}}
        <div class="nav-sec">របាយការណ៍ / Reports</div>

        <a class="s-link {{ request()->routeIs('reports.*') ? 'active' : '' }}"
           href="{{ route('reports.visits') }}">
            <span class="s-icon"><i class="bi bi-bar-chart-line-fill"></i></span>
            <span class="s-text">
                <span class="s-km">របាយការណ៍ការចូល</span>
                <span class="s-en">Visit Reports</span>
            </span>
        </a>

        <a class="s-link {{ request()->routeIs('reports.daily*') ? 'active' : '' }}"
           href="{{ route('reports.daily') }}">
            <span class="s-icon"><i class="bi bi-calendar-check-fill"></i></span>
            <span class="s-text">
                <span class="s-km">របាយការណ៍ប្រចាំថ្ងៃ</span>
                <span class="s-en">Daily Summary</span>
            </span>
        </a>

        {{-- Admin --}}
        <div class="nav-sec">ការគ្រប់គ្រង / Admin</div>

        <a class="s-link" href="#">
            <span class="s-icon"><i class="bi bi-gear-fill"></i></span>
            <span class="s-text">
                <span class="s-km">ការកំណត់</span>
                <span class="s-en">Settings</span>
            </span>
        </a>

    </div>

    {{-- User profile strip at bottom --}}
    <div class="sb-user">
        <div class="sb-user-avatar">
            {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
        </div>
        <div class="sb-user-info" id="sbUserInfo">
            <div class="sb-user-name">{{ auth()->user()->name ?? 'User' }}</div>
            <div class="sb-user-role">{{ auth()->user()->role ?? 'Staff' }}</div>
        </div>
        <form method="POST" action="{{ route('logout') }}" id="sbLogoutForm" style="margin:0">
            @csrf
            <button type="submit" class="sb-logout-btn" title="Log out">
                <i class="bi bi-box-arrow-right"></i>
            </button>
        </form>
    </div>

</nav>

<script>
const isMobile = () => window.innerWidth < 992;

function toggleSidebar() {
    const sb  = document.getElementById('sidebar');
    const ov  = document.getElementById('sidebarOverlay');
    const tb  = document.getElementById('topbar');
    const mn  = document.getElementById('main');

    if (isMobile()) {
        const open = sb.classList.toggle('mobile-open');
        ov.classList.toggle('show', open);
    } else {
        const collapsed = sb.classList.toggle('collapsed');
        tb.classList.toggle('sb-collapsed', collapsed);
        mn.classList.toggle('sb-collapsed', collapsed);
        // persist state
        try { localStorage.setItem('sb_collapsed', collapsed ? '1' : '0'); } catch(e){}
    }
}

function closeSidebar() {
    if (!isMobile()) return;
    document.getElementById('sidebar').classList.remove('mobile-open');
    document.getElementById('sidebarOverlay').classList.remove('show');
}

// Restore collapsed state on load
(function() {
    try {
        if (!isMobile() && localStorage.getItem('sb_collapsed') === '1') {
            document.getElementById('sidebar').classList.add('collapsed');
            document.getElementById('main')?.classList.add('sb-collapsed');
            document.getElementById('topbar')?.classList.add('sb-collapsed');
        }
    } catch(e) {}
})();
</script>
