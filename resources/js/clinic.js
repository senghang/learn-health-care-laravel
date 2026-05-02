/**
 * MediFlow EMR — clinic.js
 * 1. Sidebar   — collapse/expand, mobile overlay, tooltip-on-collapsed
 * 2. Toast     — rich: icon / title / progress bar / dismiss
 * 3. Flash     — reads data-flash session divs from DOM
 * 4. Confirm   — emrConfirm() modal + data-confirm interceptors
 * 5. Workflow  — progress ring helper, step scroll, skip confirm
 * 6. Live      — badge refresh every 60 s
 * 7. Dashboard — stat counter animation
 * 8. Forms     — layout helpers
 */

/* ─── 1. SIDEBAR ────────────────────────────────────────────���─────── */

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
    ok  : { cls: '',    bi: 'bi-check-lg'              },
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

/* ─── 4. CONFIRM MODAL ────────────────────────────────────────────── */

(function () {
    var _resolveConfirm = null;

    /**
     * emrConfirm(msg, opts) — show a professional confirm dialog.
     * opts: { type: 'danger'|'warn'|'info', title: '...', okText: '...' }
     * Returns Promise<boolean>
     */
    window.emrConfirm = function (msg, opts) {
        opts = opts || {};
        var type  = opts.type  || 'danger';
        var titles = { danger: 'Confirm Delete', warn: 'Are you sure?', info: 'Confirm Action' };
        var title  = opts.title || titles[type] || 'Confirm';

        var modal   = document.getElementById('emrConfirmModal');
        var msgEl   = document.getElementById('emrConfirmMsg');
        var titleEl = document.getElementById('emrConfirmTitle');
        var iconEl  = document.getElementById('emrConfirmIcon');
        var okBtn   = document.getElementById('emrConfirmOk');

        /* Graceful fallback if modal not in DOM yet */
        if (!modal) return Promise.resolve(window.confirm(msg));

        msgEl.innerHTML = _esc(msg).replace(/\n/g, '<br>');
        titleEl.textContent = title;

        var cfgMap = {
            danger : { icon: 'bi-trash3-fill',              color: '#e74c3c', okCls: 'btn-danger',   label: 'Delete'  },
            warn   : { icon: 'bi-exclamation-triangle-fill', color: '#ff771d', okCls: 'btn-warning',  label: 'Confirm' },
            info   : { icon: 'bi-info-circle-fill',          color: '#4154f1', okCls: 'btn-primary',  label: 'Confirm' },
        };
        var cfg = cfgMap[type] || cfgMap.info;

        iconEl.className = 'bi ' + cfg.icon;
        iconEl.style.color = cfg.color;
        okBtn.textContent = opts.okText || cfg.label;
        okBtn.className = 'btn btn-sm ' + cfg.okCls;

        modal.classList.add('open');
        okBtn.focus();

        return new Promise(function (resolve) { _resolveConfirm = resolve; });
    };

    function _resolveWith(val) {
        var m = document.getElementById('emrConfirmModal');
        if (m) m.classList.remove('open');
        if (_resolveConfirm) { _resolveConfirm(val); _resolveConfirm = null; }
    }

    document.addEventListener('DOMContentLoaded', function () {
        var modal = document.getElementById('emrConfirmModal');
        if (!modal) return;

        document.getElementById('emrConfirmOk')    ?.addEventListener('click', function () { _resolveWith(true);  });
        document.getElementById('emrConfirmNo')    ?.addEventListener('click', function () { _resolveWith(false); });
        document.getElementById('emrConfirmClose') ?.addEventListener('click', function () { _resolveWith(false); });
        modal.addEventListener('click', function (e) { if (e.target === modal) _resolveWith(false); });

        /* ── Intercept forms with data-confirm ────────────────── */
        document.addEventListener('submit', function (e) {
            var form = e.target;
            var msg  = form.dataset.confirm;
            if (!msg) return;
            e.preventDefault();
            emrConfirm(msg, { type: form.dataset.confirmType, title: form.dataset.confirmTitle })
                .then(function (ok) {
                    if (!ok) return;
                    delete form.dataset.confirm;
                    form.submit();
                });
        }, true);

        /* ── Intercept buttons/links with data-confirm ────────── */
        document.addEventListener('click', function (e) {
            var el = e.target.closest('[data-confirm]');
            if (!el || el.tagName === 'FORM') return;
            var msg = el.dataset.confirm;
            if (!msg) return;
            e.preventDefault();
            e.stopImmediatePropagation();
            emrConfirm(msg, { type: el.dataset.confirmType, title: el.dataset.confirmTitle })
                .then(function (ok) {
                    if (!ok) return;
                    if (el.tagName === 'A') { window.location.href = el.href; return; }
                    var frm = el.closest('form');
                    if (frm) { delete el.dataset.confirm; frm.submit(); }
                });
        }, true);
    });

    /* ESC to close */
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && document.getElementById('emrConfirmModal')?.classList.contains('open')) {
            _resolveWith(false);
        }
    });
}());

/* ─── 5. WORKFLOW ─────────────────────────────────────────────────── */

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

/* Skip link confirmation — uses emrConfirm */
document.addEventListener('click', e => {
    const link = e.target.closest('[data-skip-confirm]');
    if (!link) return;
    e.preventDefault();
    emrConfirm(
        `Skip "${link.dataset.skipConfirm || 'this step'}"?\nYou can return to fill it later.\n\nដកចោលមុន? អ្នកអាចវិលត្រឡប់ក្រោយ។`,
        { type: 'info', title: 'Skip Step', okText: 'Skip' }
    ).then(ok => { if (ok) window.location.href = link.href; });
});

/* Mobile step accordion */
function toggleStepList() {
    const list = document.getElementById('stepSelectList');
    const chev = document.getElementById('stepListChevron');
    const open = list?.classList.toggle('open');
    if (chev) chev.style.transform = open ? 'rotate(180deg)' : '';
}

/* ─── 6. LIVE BADGES ──────────────────────────────────────────────── */
setInterval(() => {
    fetch('/patients/search/json?q=_ping_', { credentials: 'same-origin' }).catch(() => {});
}, 60_000);

/* ─── 7. STAT COUNTER ANIMATION ──────────────────────────────────── */
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

/* ─── 8. LAYOUT / MISC ────────────────────────────────────────────── */
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
