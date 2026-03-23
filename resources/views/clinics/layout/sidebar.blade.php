<nav id="sidebar">

    {{-- ── Brand ──────────────────────────────────────────────────────────── --}}
    <div class="sb-brand">
        <div class="sb-logo-icon">
            @if(currentClinic()?->logo)
                <img src="{{ asset('storage/'.currentClinic()->logo) }}"
                     style="width:34px;height:34px;border-radius:8px;object-fit:cover;display:block">
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

        {{-- ── Dashboard ─────────────────────────────────────────────────── --}}
        <a class="sb-item {{ request()->routeIs('dashboard') ? 'sb-item--active' : '' }}"
           href="{{ route('dashboard') }}" data-tooltip="Dashboard">
            <span class="sb-item-icon"><i class="bi bi-grid-1x2-fill"></i></span>
            <span class="sb-item-label">
                <span class="sb-km">ផ្ទាំងគ្រប់គ្រង</span>
                <span class="sb-en">Dashboard</span>
            </span>
        </a>

        {{-- ── OPERATIONS (primary — top position) ───────────────────────── --}}
        <div class="sb-group-label">OPERATIONS</div>

        {{-- New Visit — hero action --}}
        <a class="sb-item sb-item--new-visit {{ request()->routeIs('workflow.create') ? 'sb-item--active' : '' }}"
           href="{{ route('workflow.create') }}" data-tooltip="New Visit">
            <span class="sb-item-icon sb-item-icon--accent">
                <i class="bi bi-plus-circle-fill"></i>
            </span>
            <span class="sb-item-label">
                <span class="sb-km" style="font-weight:700">ការចូលព្យាបាលថ្មី</span>
                <span class="sb-en">New Visit</span>
            </span>
        </a>

        <a class="sb-item {{ request()->routeIs('patients.*') ? 'sb-item--active' : '' }}"
           href="{{ route('patients.index') }}" data-tooltip="Registration">
            <span class="sb-item-icon"><i class="bi bi-person-plus-fill"></i></span>
            <span class="sb-item-label">
                <span class="sb-km">ចុះឈ្មោះ</span>
                <span class="sb-en">Registration</span>
            </span>
        </a>

        <a class="sb-item {{ request()->routeIs('visits.*') || request()->routeIs('workflow.*') ? 'sb-item--active' : '' }}"
           href="{{ route('visits.index') }}" data-tooltip="Consultation">
            <span class="sb-item-icon"><i class="bi bi-stethoscope"></i></span>
            <span class="sb-item-label">
                <span class="sb-km">ការព្យាបាល</span>
                <span class="sb-en">Consultation</span>
            </span>
            @php $activeVisits = \App\Models\VisitModel::whereHas('patient', fn($q)=>$q->where('clinic_id',currentClinic()->id))->whereNull('discharged_at')->count(); @endphp
            @if($activeVisits > 0)
                <span class="sb-badge">{{ $activeVisits > 99 ? '99+' : $activeVisits }}</span>
            @endif
        </a>

        <a class="sb-item {{ request()->routeIs('prescriptions.*') ? 'sb-item--active' : '' }}"
           href="{{ route('prescriptions.index') }}" data-tooltip="Prescription">
            <span class="sb-item-icon"><i class="bi bi-capsule-fill"></i></span>
            <span class="sb-item-label">
                <span class="sb-km">វេជ្ជបញ្ជា</span>
                <span class="sb-en">Prescription</span>
            </span>
        </a>

        <a class="sb-item {{ request()->routeIs('invoices.*') ? 'sb-item--active' : '' }}"
           href="{{ route('invoices.index') }}" data-tooltip="Invoice">
            <span class="sb-item-icon"><i class="bi bi-receipt-cutoff"></i></span>
            <span class="sb-item-label">
                <span class="sb-km">វិក្កយបត្រ</span>
                <span class="sb-en">Invoice</span>
            </span>
            @php $pendingInv = \App\Models\InvoiceModel::whereHas('patient', fn($q) => $q->where('clinic_id', currentClinic()->id))->whereIn('status', ['pending','partial'])->count(); @endphp
            @if($pendingInv > 0)
                <span class="sb-badge" style="background:#ff771d">{{ $pendingInv > 99 ? '99+' : $pendingInv }}</span>
            @endif
        </a>

        <a class="sb-item {{ request()->routeIs('invoices.index') && request('status') === 'pending' ? 'sb-item--active' : '' }}"
           href="{{ route('invoices.index', ['status' => 'pending']) }}" data-tooltip="Payment">
            <span class="sb-item-icon"><i class="bi bi-cash-stack"></i></span>
            <span class="sb-item-label">
                <span class="sb-km">ការទូទាត់</span>
                <span class="sb-en">Payment</span>
            </span>
        </a>

        {{-- ── INVENTORY ──────────────────────────────────────────────────── --}}
        <div class="sb-group-label">INVENTORY</div>

        <a class="sb-item {{ request()->routeIs('inventory.products') || request()->routeIs('inventory.product.*') ? 'sb-item--active' : '' }}"
           href="{{ route('inventory.products') }}" data-tooltip="Products">
            <span class="sb-item-icon"><i class="bi bi-box-seam-fill"></i></span>
            <span class="sb-item-label">
                <span class="sb-km">ផលិតផល</span>
                <span class="sb-en">Products</span>
            </span>
        </a>

        <a class="sb-item {{ request()->routeIs('inventory.stock-in') ? 'sb-item--active' : '' }}"
           href="{{ route('inventory.stock-in') }}" data-tooltip="Stock In">
            <span class="sb-item-icon sb-item-icon--green"><i class="bi bi-box-arrow-in-down-right"></i></span>
            <span class="sb-item-label">
                <span class="sb-km">ស្តុកចូល</span>
                <span class="sb-en">Stock In</span>
            </span>
        </a>

        <a class="sb-item {{ request()->routeIs('inventory.stock-out') ? 'sb-item--active' : '' }}"
           href="{{ route('inventory.stock-out') }}" data-tooltip="Stock Out">
            <span class="sb-item-icon sb-item-icon--red"><i class="bi bi-box-arrow-up-right"></i></span>
            <span class="sb-item-label">
                <span class="sb-km">ស្តុកចេញ</span>
                <span class="sb-en">Stock Out</span>
            </span>
        </a>

        <a class="sb-item {{ request()->routeIs('inventory.report') ? 'sb-item--active' : '' }}"
           href="{{ route('inventory.report') }}" data-tooltip="Inventory Report">
            <span class="sb-item-icon"><i class="bi bi-clipboard2-data-fill"></i></span>
            <span class="sb-item-label">
                <span class="sb-km">របាយការណ៍ស្តុក</span>
                <span class="sb-en">Inv. Report</span>
            </span>
            @php
                $outStock = \App\Models\MedicineModel::where('clinic_id', currentClinic()->id)->where('stock', '<=', 0)->count();
                $lowStock = \App\Models\MedicineModel::where('clinic_id', currentClinic()->id)->whereColumn('stock', '<=', 'stock_alert')->where('stock', '>', 0)->count();
            @endphp
            @if($outStock > 0)
                <span class="sb-badge" style="background:#dc2626">{{ $outStock }}</span>
            @elseif($lowStock > 0)
                <span class="sb-badge" style="background:#d97706">{{ $lowStock }}</span>
            @endif
        </a>

        {{-- ── REPORTS ────────────────────────────────────────────────────── --}}
        <div class="sb-group-label">REPORTS</div>

        <a class="sb-item {{ request()->routeIs('reports.revenue') ? 'sb-item--active' : '' }}"
           href="{{ route('reports.revenue') }}" data-tooltip="Revenue">
            <span class="sb-item-icon"><i class="bi bi-currency-dollar"></i></span>
            <span class="sb-item-label">
                <span class="sb-km">ប្រាក់ចំណូល</span>
                <span class="sb-en">Revenue</span>
            </span>
        </a>

        <a class="sb-item {{ request()->routeIs('reports.visits') || request()->routeIs('reports.daily') ? 'sb-item--active' : '' }}"
           href="{{ route('reports.visits') }}" data-tooltip="Visit Reports">
            <span class="sb-item-icon"><i class="bi bi-bar-chart-fill"></i></span>
            <span class="sb-item-label">
                <span class="sb-km">របាយការណ៍ចូល</span>
                <span class="sb-en">Visit Reports</span>
            </span>
        </a>

        <a class="sb-item {{ request()->routeIs('reports.doctor-performance') ? 'sb-item--active' : '' }}"
           href="{{ route('reports.doctor-performance') }}" data-tooltip="Doctor Performance">
            <span class="sb-item-icon"><i class="bi bi-person-badge-fill"></i></span>
            <span class="sb-item-label">
                <span class="sb-km">សមត្ថភាពវេជ្ជ</span>
                <span class="sb-en">Doctor Performance</span>
            </span>
        </a>

        {{-- ── SYSTEM SETUP (bottom — rarely used) ───────────────────────── --}}
        <div class="sb-group-label">SYSTEM SETUP</div>

        <a class="sb-item {{ request()->routeIs('settings.*') ? 'sb-item--active' : '' }}"
           href="{{ route('settings.general') }}" data-tooltip="Settings">
            <span class="sb-item-icon"><i class="bi bi-gear-fill"></i></span>
            <span class="sb-item-label">
                <span class="sb-km">ការកំណត់</span>
                <span class="sb-en">Settings</span>
            </span>
        </a>

        <a class="sb-item {{ request()->routeIs('beds.*') ? 'sb-item--active' : '' }}"
           href="{{ route('beds.index') }}" data-tooltip="Rooms &amp; Beds">
            <span class="sb-item-icon"><i class="bi bi-building-fill"></i></span>
            <span class="sb-item-label">
                <span class="sb-km">បន្ទប់ & គ្រែ</span>
                <span class="sb-en">Rooms &amp; Beds</span>
            </span>
        </a>

    </div>{{-- /sb-nav --}}

    {{-- ── User footer ─────────────────────────────────────────────────────── --}}
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
const isMobile = () => window.innerWidth < 992;

function _applyCollapsed(collapsed) {
    const sb = document.getElementById('sidebar');
    const tb = document.getElementById('topbar');
    const mn = document.getElementById('main');
    const ic = document.getElementById('sbPinIcon');
    [sb, tb, mn].forEach(el => el?.classList.toggle('sb-collapsed', collapsed));
    if (ic) ic.className = collapsed ? 'bi bi-layout-sidebar' : 'bi bi-layout-sidebar-reverse';
    try { localStorage.setItem('sb_col', collapsed ? '1' : '0'); } catch(_) {}
}

function toggleSidebar() {
    if (isMobile()) {
        const sb = document.getElementById('sidebar');
        const ov = document.getElementById('sidebarOverlay');
        const open = sb?.classList.contains('mobile-open');
        sb?.classList.toggle('mobile-open', !open);
        ov?.classList.toggle('show', !open);
    } else {
        _applyCollapsed(!document.getElementById('sidebar')?.classList.contains('sb-collapsed'));
    }
}

function closeSidebar() {
    if (!isMobile()) return;
    document.getElementById('sidebar')?.classList.remove('mobile-open');
    document.getElementById('sidebarOverlay')?.classList.remove('show');
}

document.addEventListener('DOMContentLoaded', () => {
    try { if (!isMobile() && localStorage.getItem('sb_col') === '1') _applyCollapsed(true); } catch(_) {}

    // Tooltip for collapsed sidebar
    document.querySelectorAll('.sb-item[data-tooltip]').forEach(el => {
        el.addEventListener('mouseenter', function() {
            const sb = document.getElementById('sidebar');
            if (!sb?.classList.contains('sb-collapsed')) return;
            const tip = document.getElementById('sb-tooltip');
            if (!tip) return;
            const rect = el.getBoundingClientRect();
            tip.textContent = el.dataset.tooltip;
            tip.style.top = (rect.top + rect.height/2) + 'px';
            tip.style.display = 'block';
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

{{-- Tooltip element for collapsed state --}}
<div id="sb-tooltip" style="display:none;position:fixed;left:68px;transform:translateY(-50%);background:#1e2d5a;color:#fff;font-size:12px;font-weight:600;padding:5px 12px;border-radius:8px;z-index:9999;pointer-events:none;white-space:nowrap;box-shadow:0 4px 16px rgba(0,0,0,.3)"></div>
