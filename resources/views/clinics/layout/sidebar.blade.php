<nav id="sidebar">
    <div class="sidebar-logo">
        <div class="logo-icon">⚕</div>
        <div class="logo-text" id="sbLogoTxt">
            <div class="title">MediFlow EMR</div>
            <div class="sub">ប្រព័ន្ធព័ត៌មានសុខភាព</div>
        </div>
    </div>

    <div class="sidebar-nav">
        <div class="nav-sec">ទំព័រដើម / Home</div>
        <a class="s-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
           href="{{ route('dashboard') }}">
            <i class="bi bi-grid-1x2-fill"></i>
            <span class="s-text">
                        <span class="km-t">ផ្ទាំងគ្រប់គ្រង</span>
                        <span class="en-t">Dashboard</span>
                    </span>
        </a>

        <div class="nav-sec">ព្យាបាល / Clinical</div>
        <a class="s-link {{ request()->routeIs('visits.*') ? 'active' : '' }}"
           href="{{ route('visits.index') }}">
            <i class="bi bi-hospital"></i>
            <span class="s-text">
                <span class="km-t">ការចូលព្យាបាល</span>
                <span class="en-t">Patient Visits</span>
            </span>
            <span class="s-badge" id="visitCountBadge">{{ 0 }}</span>
        </a>

        <a class="s-link {{ request()->routeIs('workflow.*') ? 'active' : '' }}"
           href="">
            <i class="bi bi-diagram-3-fill"></i>
            <span class="s-text">
                                <span class="km-t">ប្រតិបត្តិការព្យាបាល</span>
                                <span class="en-t">Clinical Workflow</span>
                            </span>
        </a>

        <div class="nav-sec">ទិន្នន័យ / Data</div>
        <a class="s-link"
           href="">
            <i class="bi bi-flask2-fill"></i>
            <span class="s-text">
                        <span class="km-t">មន្ទីរពិសោធន៍</span>
                        <span class="en-t">Laboratory</span>
                    </span>
        </a>
        <a class="s-link {{ request()->routeIs('pharmacy.*') ? 'active' : '' }}"
           href="">
            <i class="bi bi-capsule-pill"></i>
            <span class="s-text">
                        <span class="km-t">បញ្ជាថ្នាំ</span>
                        <span class="en-t">Prescriptions</span>
                    </span>
        </a>
        <a class="s-link {{ request()->routeIs('billing.*') ? 'active' : '' }}"
           href="">
            <i class="bi bi-receipt-cutoff"></i>
            <span class="s-text">
                        <span class="km-t">វិក្កយបត្រ</span>
                        <span class="en-t">Billing</span>
                    </span>
        </a>

        <div class="nav-sec">ការគ្រប់គ្រង / Admin</div>
        <a class="s-link {{ request()->routeIs('users.*') ? 'active' : '' }}"
           href="">
            <i class="bi bi-people-fill"></i>
            <span class="s-text">
                        <span class="km-t">អ្នកប្រើប្រាស់</span>
                        <span class="en-t">Users</span>
                    </span>
        </a>
        <a class="s-link {{ request()->routeIs('reports.*') ? 'active' : '' }}"
           href="">
            <i class="bi bi-bar-chart-fill"></i>
            <span class="s-text">
                        <span class="km-t">របាយការណ៍</span>
                        <span class="en-t">Reports</span>
                    </span>
        </a>
        <a class="s-link {{ request()->routeIs('settings.*') ? 'active' : '' }}"
           href="">
            <i class="bi bi-gear-fill"></i>
            <span class="s-text">
                        <span class="km-t">ការកំណត់</span>
                        <span class="en-t">Settings</span>
                    </span>
        </a>
    </div>
</nav>

<script>
    /* ══ SIDEBAR ══ */
    const isMobile = () => window.innerWidth < 992;

    function toggleSidebar() {
        const sb = document.getElementById('sidebar');
        const ov = document.getElementById('sidebarOverlay');
        const tb = document.getElementById('topbar');
        const mn = document.getElementById('main');
        if (isMobile()) {
            const open = sb.classList.contains('mobile-open');
            sb.classList.toggle('mobile-open', !open);
            ov.classList.toggle('show', !open);
        } else {
            const col = sb.classList.contains('collapsed');
            sb.classList.toggle('collapsed', !col);
            tb.classList.toggle('sb-collapsed', !col);
            mn.classList.toggle('sb-collapsed', !col);
            const logoTxt = document.getElementById('sbLogoTxt');
            if (logoTxt) logoTxt.style.display = !col ? 'none' : '';
        }
    }

    function closeSidebar() {
        if (!isMobile()) return;
        document.getElementById('sidebar').classList.remove('mobile-open');
        document.getElementById('sidebarOverlay').classList.remove('show');
    }
</script>
