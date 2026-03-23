/**
 * MediFlow EMR — clinic.js  (enhanced)
 * 1. Sidebar   — collapse/expand, mobile overlay, tooltip-on-collapsed
 * 2. Toast     — rich: icon / title / progress bar / dismiss
 * 3. Flash     — reads data-flash and session divs from DOM
 * 4. Workflow  — progress ring helper, step scroll, skip confirm
 * 5. Live      — badge refresh every 60 s
 * 6. Dashboard — stat counter animation
 * 7. Forms     — layout helpers
 */

/* ─── 1. SIDEBAR ──────────────────────────────────────────────────── */

const isMobile = () => window.innerWidth < 992;

function _applyCollapsed(collapsed) {
    const sb = document.getElementById('sidebar');
    const tb = document.getElementById('topbar');
    const mn = document.getElementById('main');
    const ic = document.getElementById('sbPinIcon');
    [sb, tb, mn].forEach(el => el?.classList.toggle('sb-collapsed', collapsed));
    if (ic) ic.className = collapsed ? 'bi bi-layout-sidebar' : 'bi bi-layout-sidebar-reverse';
    try { localStorage.setItem('sb_col', collapsed ? '1' : '0'); } catch (_) {}
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

(function () {
    try { if (!isMobile() && localStorage.getItem('sb_col') === '1') _applyCollapsed(true); } catch (_) {}
})();

window.addEventListener('resize', () => {
    if (!isMobile()) {
        document.getElementById('sidebar')?.classList.remove('mobile-open');
        document.getElementById('sidebarOverlay')?.classList.remove('show');
    }
});

/* ─── 2. TOAST ────────────────────────────────────────────────────── */

const _TOAST_CFG = {
    ok  : { cls: '',    bi: 'bi-check-lg'             },
    wrn : { cls: 'wrn', bi: 'bi-exclamation-triangle'  },
    err : { cls: 'err', bi: 'bi-x-circle'              },
    inf : { cls: 'inf', bi: 'bi-info-circle'           },
};

function toast(msg, type = 'ok', title = '', duration = 4500) {
    const wrap = document.getElementById('toastWrap');
    if (!wrap || !msg) return null;
    const cfg = _TOAST_CFG[type] ?? _TOAST_CFG.ok;
    const el  = document.createElement('div');
    el.className = 'toast-item' + (cfg.cls ? ' ' + cfg.cls : '');
    el.innerHTML =
        `<div class="toast-icon"><i class="bi ${cfg.bi}"></i></div>` +
        `<div class="toast-body">` +
            (title ? `<div class="toast-title">${_esc(title)}</div>` : '') +
            `<div class="toast-msg">${_esc(msg)}</div>` +
        `</div>` +
        `<button class="toast-close" onclick="_toastDismiss(this.parentElement)" aria-label="Close">` +
            `<i class="bi bi-x"></i>` +
        `</button>`;
    wrap.appendChild(el);
    if (duration > 0) setTimeout(() => _toastDismiss(el), duration);
    return el;
}

function _toastDismiss(el) {
    if (!el || el.classList.contains('removing')) return;
    el.classList.add('removing');
    setTimeout(() => el?.remove(), 240);
}

function _esc(s) {
    return String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

/* ─── 3. FLASH READER ────────────────────────────────────────────── */

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-flash]').forEach(el => {
        const msg = el.dataset.flash, type = el.dataset.type || 'ok', title = el.dataset.title || '';
        if (msg) toast(msg, type, title);
    });
    document.querySelectorAll('[data-session-flash]').forEach(el => {
        if (el.dataset.msg) toast(el.dataset.msg, el.dataset.sessionFlash === 'success' ? 'ok' : 'err');
    });
});

/* ─── 4. WORKFLOW ─────────────────────────────────────────────────── */

function drawProgressRing(id, pct) {
    const svg = document.getElementById(id);
    if (!svg) return;
    const c = svg.querySelector('.wf-progress-ring-circle');
    if (!c) return;
    const r = parseFloat(c.getAttribute('r') || 18);
    const circ = 2 * Math.PI * r;
    c.style.strokeDasharray  = circ;
    c.style.strokeDashoffset = circ - (pct / 100) * circ;
}

document.addEventListener('DOMContentLoaded', () => {
    /* Auto-scroll active step pill into view */
    const track  = document.querySelector('.wf-track');
    const active = track?.querySelector('.wf-pill--active');
    if (active && track) {
        setTimeout(() => {
            const tR = track.getBoundingClientRect(), aR = active.getBoundingClientRect();
            track.scrollBy({ left: aR.left - tR.left - tR.width / 2 + aR.width / 2, behavior: 'smooth' });
        }, 120);
    }

    /* Init any progress rings already on page */
    document.querySelectorAll('[data-ring-pct]').forEach(svg => {
        drawProgressRing(svg.id, parseFloat(svg.dataset.ringPct));
    });
});

/* Skip link confirmation */
document.addEventListener('click', e => {
    const link = e.target.closest('[data-skip-confirm]');
    if (!link) return;
    if (!confirm(`Skip "${link.dataset.skipConfirm || 'this step'}"? You can return later.`)) e.preventDefault();
});

/* Mobile step accordion */
function toggleStepList() {
    const list = document.getElementById('stepSelectList');
    const chev = document.getElementById('stepListChevron');
    const open = list?.classList.toggle('open');
    if (chev) chev.style.transform = open ? 'rotate(180deg)' : '';
}

/* ─── 5. LIVE BADGES ──────────────────────────────────────────────── */
setInterval(() => {
    /* Silently probe — badges update naturally on next navigation */
    fetch('/patients/search/json?q=_ping_', { credentials: 'same-origin' }).catch(() => {});
}, 60_000);

/* ─── 6. STAT COUNTER ANIMATION ──────────────────────────────────── */
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.stat-num[data-target]').forEach(el => {
        const target = parseFloat(el.dataset.target);
        const suffix = el.dataset.suffix || '';
        if (!isFinite(target) || target === 0) return;
        let cur = 0;
        const step = target / (600 / 16);
        const tick = () => {
            cur = Math.min(cur + step, target);
            el.textContent = (Number.isInteger(target) ? Math.round(cur) : cur.toFixed(1)) + suffix;
            if (cur < target) requestAnimationFrame(tick);
        };
        requestAnimationFrame(tick);
    });
});

/* ─── 7. LAYOUT / MISC ────────────────────────────────────────────── */
function _updateVssLayout() {
    const col = document.getElementById('vssCol');
    if (col) col.style.display = window.innerWidth >= 992 ? '' : 'none';
}
window.addEventListener('resize', _updateVssLayout);
document.addEventListener('DOMContentLoaded', _updateVssLayout);

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.row--just-saved').forEach(el => {
        setTimeout(() => el.classList.remove('row--just-saved'), 2200);
    });
});
