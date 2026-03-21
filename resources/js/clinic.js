/**
 * MediFlow EMR — clinic.js
 */

/* ── Sidebar ─────────────────────────────────────────────────────────────── */
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
        tb?.classList.toggle('sb-collapsed', !col);
        mn?.classList.toggle('sb-collapsed', !col);
        const logoTxt = document.getElementById('sbLogoTxt');
        if (logoTxt) logoTxt.style.display = !col ? 'none' : '';
        // Persist sidebar state across page loads
        try { localStorage.setItem('sb_collapsed', !col ? '1' : '0'); } catch {}
    }
}

function closeSidebar() {
    if (!isMobile()) return;
    document.getElementById('sidebar')?.classList.remove('mobile-open');
    document.getElementById('sidebarOverlay')?.classList.remove('show');
}

// Restore sidebar collapsed state on load
(function restoreSidebar() {
    try {
        if (!isMobile() && localStorage.getItem('sb_collapsed') === '1') {
            document.getElementById('sidebar')?.classList.add('collapsed');
            document.getElementById('topbar')?.classList.add('sb-collapsed');
            document.getElementById('main')?.classList.add('sb-collapsed');
            const logoTxt = document.getElementById('sbLogoTxt');
            if (logoTxt) logoTxt.style.display = 'none';
        }
    } catch {}
})();

window.addEventListener('resize', () => {
    if (!isMobile()) {
        document.getElementById('sidebar')?.classList.remove('mobile-open');
        document.getElementById('sidebarOverlay')?.classList.remove('show');
    }
});

/* ── Toast ───────────────────────────────────────────────────────────────── */
function toast(msg, type = 'ok') {
    const w = document.getElementById('toastWrap');
    if (!w || !msg) return;

    const d = document.createElement('div');
    d.className = 'toast-item' + (type === 'err' ? ' err' : type === 'wrn' ? ' wrn' : '');

    const icon = type === 'err' ? '❌' : type === 'wrn' ? '⚠️' : '✅';
    d.innerHTML = `<span style="font-size:15px">${icon}</span><span>${msg}</span>`;

    w.appendChild(d);
    setTimeout(() => d.remove(), 3500);
}

/**
 * FIXED: read ALL flash divs, not just the first one.
 * The updated flash.blade.php can emit multiple divs
 * (one for 'flash', one for 'success', one for 'error' etc.)
 */
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-flash]').forEach(el => {
        const msg = el.dataset.flash;
        const type = el.dataset.type || 'ok';
        if (msg) toast(msg, type);
    });
});

/* ── Step accordion (mobile) ─────────────────────────────────────────────── */
let stepListOpen = false;

function toggleStepList() {
    stepListOpen = !stepListOpen;
    document.getElementById('stepSelectList')?.classList.toggle('open', stepListOpen);
    const chev = document.getElementById('stepListChevron');
    if (chev) chev.style.transform = stepListOpen ? 'rotate(180deg)' : '';
}

/* ── Layout: hide/show VSS column ───────────────────────────────────────── */
function updateLayout() {
    const col = document.getElementById('vssCol');
    if (!col) return;
    col.style.display = window.innerWidth >= 992 ? 'block' : 'none';
}

window.addEventListener('resize', updateLayout);
document.addEventListener('DOMContentLoaded', updateLayout);
