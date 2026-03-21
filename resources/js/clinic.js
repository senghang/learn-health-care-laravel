/**
 * MediFlow EMR — emr.js
 * Frontend: sidebar toggle, toast, step accordion, layout
 */

window.addEventListener('resize', () => {
    if (!isMobile()) {
        document.getElementById('sidebar').classList.remove('mobile-open');
        document.getElementById('sidebarOverlay').classList.remove('show');
    }
});

/* ══ TOAST ══ */
function toast(msg, type = 'ok') {
    const w = document.getElementById('toastWrap');
    if (!w) return;
    const d = document.createElement('div');
    d.className = 'toast-item' + (type === 'err' ? ' err' : type === 'wrn' ? ' wrn' : '');
    d.innerHTML = `<span style="font-size:15px">${type === 'err' ? '❌' : type === 'wrn' ? '⚠️' : '✅'}</span><span>${msg}</span>`;
    w.appendChild(d);
    setTimeout(() => d.remove(), 3200);
}

/* Show flash message from Laravel session if present */
document.addEventListener('DOMContentLoaded', () => {
    const flash = document.querySelector('[data-flash]');
    if (flash) toast(flash.dataset.flash, flash.dataset.type || 'ok');
});

/* ══ STEP ACCORDION (mobile) ══ */
let stepListOpen = false;

function toggleStepList() {
    stepListOpen = !stepListOpen;
    const list = document.getElementById('stepSelectList');
    const chev = document.getElementById('stepListChevron');
    if (list) list.classList.toggle('open', stepListOpen);
    if (chev) chev.style.transform = stepListOpen ? 'rotate(180deg)' : '';
}

/* ══ LAYOUT ══ */
function updateLayout() {
    const col = document.getElementById('vssCol');
    if (!col) return;
    col.style.display = window.innerWidth >= 992 ? 'block' : 'none';
}

window.addEventListener('resize', updateLayout);

document.addEventListener('DOMContentLoaded', updateLayout);
