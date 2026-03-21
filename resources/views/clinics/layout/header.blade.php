<header id="topbar">

    {{-- ── Left: toggle + breadcrumb ──────────────────────────────────── --}}
    <div class="tb-left">
        <button class="tb-toggle" onclick="toggleSidebar()" aria-label="Toggle sidebar">
            <i class="bi bi-list"></i>
        </button>

        {{-- Breadcrumb / page title — filled by each page via a shared view composer --}}
        <div class="tb-breadcrumb d-none d-md-flex">
            <span class="tb-clinic-chip">
                @if(currentClinic()?->logo)
                    <img src="{{ asset('storage/' . currentClinic()->logo) }}" alt="" style="height:16px;border-radius:3px;object-fit:cover">
                @else
                    <i class="bi bi-hospital" style="font-size:12px"></i>
                @endif
                {{ currentClinic()?->name_kh ?? currentClinic()?->name ?? 'Clinic' }}
            </span>
        </div>
    </div>

    {{-- ── Center: search ──────────────────────────────────────────────── --}}
    <div class="tb-search-wrap" id="searchWrap">
        <i class="bi bi-search tb-search-icon"></i>
        <input id="searchGhost"  class="tb-search-ghost"  tabindex="-1" readonly aria-hidden="true">
        <input id="globalSearch" class="tb-search-real"
               placeholder="ស្វែងរក / Search patient…"
               autocomplete="off" spellcheck="false"
               aria-label="Search patients">
        <button class="tb-search-clear" id="searchClear" style="display:none" aria-label="Clear">
            <i class="bi bi-x"></i>
        </button>
        <div id="searchDropdown" class="tb-search-dd" role="listbox" style="display:none"></div>
    </div>

    {{-- ── Right actions ────────────────────────────────────────────────── --}}
    <div class="tb-right">

        {{-- Clock --}}
        <div class="tb-clock d-none d-xl-flex">
            <i class="bi bi-clock"></i>
            <span id="tbClock">{{ now()->format('d/m/Y · H:i') }}</span>
        </div>

        {{-- Quick new visit --}}
        <a href="{{ route('workflow.create') }}" class="tb-new-btn" title="New Visit">
            <i class="bi bi-plus-lg"></i>
            <span class="d-none d-lg-inline">New Visit</span>
        </a>

        {{-- Notifications --}}
        <div class="tb-dd-wrap" id="notifWrap">
            <button class="tb-icon-btn" id="notifBtn" onclick="tbToggle('notif')" aria-label="Notifications">
                <i class="bi bi-bell-fill"></i>
                @php $activeCount = \App\Models\VisitModel::whereDate('admitted_at', today())->whereNull('discharged_at')->count(); @endphp
                @if($activeCount > 0)
                    <span class="tb-dot">{{ min($activeCount, 9) }}{{ $activeCount > 9 ? '+' : '' }}</span>
                @endif
            </button>

            <div class="tb-dropdown tb-notif-panel" id="notifPanel" style="display:none">
                <div class="tb-dd-hd">
                    <span><i class="bi bi-circle-fill" style="font-size:7px;color:#2eca6a"></i> Active Today</span>
                    <a href="{{ route('visits.index', ['status' => 'active']) }}" class="tb-dd-hd-link">View all →</a>
                </div>
                @php
                    $activeVisits = \App\Models\VisitModel::whereDate('admitted_at', today())
                        ->whereNull('discharged_at')->latest('admitted_at')->take(6)->get();
                @endphp
                @forelse($activeVisits as $av)
                <a href="{{ url('/workflow/' . $av->code . '/registration') }}" class="tb-notif-row">
                    <span class="tb-notif-dot {{ $av->visit_type === 'IPD' ? 'ipd' : 'opd' }}"></span>
                    <div style="flex:1;min-width:0">
                        <div class="tb-notif-name">{{ $av->surname }}, {{ $av->name }}</div>
                        <div class="tb-notif-meta">{{ $av->code }} · {{ $av->visit_type }}</div>
                    </div>
                    <span class="tb-notif-time">{{ $av->admitted_at?->format('H:i') }}</span>
                </a>
                @empty
                <div class="tb-dd-empty">
                    <i class="bi bi-check-circle-fill" style="color:#2eca6a;font-size:22px;margin-bottom:6px"></i>
                    <div>No active visits</div>
                </div>
                @endforelse
                @if($activeCount > 6)
                <div class="tb-dd-footer">+{{ $activeCount - 6 }} more active visits</div>
                @endif
            </div>
        </div>

        {{-- User menu --}}
        <div class="tb-dd-wrap" id="userWrap">
            <button class="tb-user-btn" id="userBtn" onclick="tbToggle('user')">
                <div class="tb-av">{{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}</div>
                <div class="tb-user-info d-none d-lg-block">
                    <div class="tb-user-name">{{ auth()->user()->name ?? 'User' }}</div>
                    <div class="tb-user-role">{{ auth()->user()->role ?? 'Staff' }}</div>
                </div>
                <i class="bi bi-chevron-down tb-user-chevron" id="userChevron"></i>
            </button>

            <div class="tb-dropdown tb-user-panel" id="userPanel" style="display:none">
                <div class="tb-user-hd">
                    <div class="tb-user-hd-av">{{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}</div>
                    <div>
                        <div style="font-weight:700;color:#012970;font-size:13px">{{ auth()->user()->name }}</div>
                        <div style="font-size:11px;color:#aaa;margin-top:1px">{{ auth()->user()->email }}</div>
                        <div class="tb-user-clinic-chip">
                            <i class="bi bi-hospital" style="font-size:9px"></i>
                            {{ currentClinic()?->name ?? 'Clinic' }}
                        </div>
                    </div>
                </div>
                <div class="tb-user-menu">
                    <a href="#" class="tb-menu-item">
                        <i class="bi bi-person-circle"></i> My Profile
                    </a>
                    <a href="{{ route('settings.general') }}" class="tb-menu-item">
                        <i class="bi bi-gear"></i> Settings
                    </a>
                    <a href="{{ route('reports.visits') }}" class="tb-menu-item">
                        <i class="bi bi-bar-chart-line"></i> Reports
                    </a>
                    <div class="tb-menu-sep"></div>
                    <form method="POST" action="{{ route('logout') }}" style="margin:0">
                        @csrf
                        <button type="submit" class="tb-menu-item tb-menu-item--danger">
                            <i class="bi bi-box-arrow-right"></i> Log Out
                        </button>
                    </form>
                </div>
            </div>
        </div>

    </div>{{-- /tb-right --}}
</header>

<script>
/* ── Clock ── */
(function tick() {
    var el = document.getElementById('tbClock');
    if (el) {
        var n = new Date();
        var pad = v => String(v).padStart(2,'0');
        el.textContent = pad(n.getDate())+'/'+pad(n.getMonth()+1)+'/'+n.getFullYear()+' · '+pad(n.getHours())+':'+pad(n.getMinutes());
    }
    setTimeout(tick, 15000);
})();

/* ── Dropdown toggle ── */
function tbToggle(which) {
    var panels = {notif: 'notifPanel', user: 'userPanel'};
    Object.entries(panels).forEach(([k, id]) => {
        var el = document.getElementById(id);
        if (!el) return;
        if (k === which) {
            var isOpen = el.style.display !== 'none';
            el.style.display = isOpen ? 'none' : 'block';
            if (k === 'user') {
                var c = document.getElementById('userChevron');
                if (c) c.classList.toggle('rotated', !isOpen);
            }
        } else {
            el.style.display = 'none';
        }
    });
}

/* ── Close on outside click ── */
document.addEventListener('click', function(e) {
    if (!e.target.closest('#notifWrap')) {
        var p = document.getElementById('notifPanel');
        if (p) p.style.display = 'none';
    }
    if (!e.target.closest('#userWrap')) {
        var u = document.getElementById('userPanel');
        if (u) u.style.display = 'none';
        var c = document.getElementById('userChevron');
        if (c) c.classList.remove('rotated');
    }
});

/* ═══════════════════════════════════════════════════════════
   PATIENT SEARCH — inline autocomplete
   Two stacked inputs: ghost (completion hint) + real (user types)
   Tab / → accepts ghost; ↓/↑ navigates dropdown; Enter confirms; Esc closes
═══════════════════════════════════════════════════════════ */
(function () {
    var real  = document.getElementById('globalSearch');
    var ghost = document.getElementById('searchGhost');
    var dd    = document.getElementById('searchDropdown');
    var clear = document.getElementById('searchClear');

    if (!real) return;

    var patients  = [];
    var cache     = {};
    var activeIdx = -1;
    var debounce  = null;
    var controller = null;

    /* ── fetch ── */
    function fetchPatients(q) {
        if (cache[q]) { renderResults(cache[q], q); return; }
        if (controller) controller.abort();
        controller = new AbortController();
        fetch('/patients/search?q=' + encodeURIComponent(q), { signal: controller.signal })
            .then(r => r.json())
            .then(data => { cache[q] = data; renderResults(data, q); })
            .catch(() => {});
    }

    /* ── best prefix match for ghost ── */
    function bestMatch(val, list) {
        if (!val || !list.length) return null;
        var v = val.toLowerCase();
        return list.find(p => {
            var full = (p.surname + ', ' + p.name).toLowerCase();
            var code = (p.code || '').toLowerCase();
            return full.startsWith(v) || code.startsWith(v);
        }) || null;
    }

    /* ── render ghost ── */
    function updateGhost(val, list) {
        if (!val) { ghost.value = ''; return; }
        var m = bestMatch(val, list);
        if (!m) { ghost.value = ''; return; }
        var full = m.surname + ', ' + m.name;
        if (full.toLowerCase().startsWith(val.toLowerCase())) {
            ghost.value = val + full.slice(val.length);
        } else {
            ghost.value = '';
        }
    }

    /* ── render dropdown ── */
    function renderResults(list, q) {
        patients = list;
        activeIdx = -1;

        updateGhost(real.value, list);

        if (!list.length) {
            dd.innerHTML = '<div class="tb-search-empty">No patients found for <strong>' + escHtml(q) + '</strong></div>';
            dd.style.display = 'block';
            return;
        }

        dd.innerHTML = list.slice(0, 8).map((p, i) => {
            var initials = ((p.surname||'').charAt(0) + (p.name||'').charAt(0)).toUpperCase();
            var colors   = ['#4154f1','#2eca6a','#ff771d','#e74c3c','#9b59b6','#00bcd4'];
            var color    = colors[(p.code || '').charCodeAt(2) % colors.length || 0];
            var visits   = p.visits_count || 0;
            return '<div class="tb-search-row" role="option" data-idx="'+i+'" onclick="selectPatient('+i+')">'
                 + '<div class="tb-search-av" style="background:'+color+'">'+initials+'</div>'
                 + '<div style="flex:1;min-width:0">'
                 + '<div class="tb-search-name">'+escHtml(p.surname)+', '+escHtml(p.name)+'</div>'
                 + '<div class="tb-search-meta">'+escHtml(p.code)+(p.phone?' · '+escHtml(p.phone):'')+' · '+(p.sex==='M'?'ប្រុស':'ស្រី')+'</div>'
                 + '</div>'
                 + '<div class="tb-search-visits">'+visits+' visit'+(visits!==1?'s':'')+'</div>'
                 + '</div>';
        }).join('');

        dd.style.display = 'block';
        real.setAttribute('aria-expanded', 'true');
    }

    /* ── select patient ── */
    window.selectPatient = function(idx) {
        var p = patients[idx];
        if (!p) return;
        real.value = p.surname + ', ' + p.name + ' (' + p.code + ')';
        ghost.value = '';
        dd.style.display = 'none';
        clear.style.display = 'flex';
        window.location.href = '/patients/' + p.code;
    };

    /* ── input handler ── */
    real.addEventListener('input', function() {
        var val = this.value.trim();
        clear.style.display = val ? 'flex' : 'none';
        if (!val) { ghost.value=''; dd.style.display='none'; return; }
        clearTimeout(debounce);
        debounce = setTimeout(() => fetchPatients(val), 240);
    });

    /* ── keyboard ── */
    real.addEventListener('keydown', function(e) {
        var rows = dd.querySelectorAll('.tb-search-row');

        if (e.key === 'Tab' || e.key === 'ArrowRight') {
            if (ghost.value && ghost.value !== real.value) {
                e.preventDefault();
                var m = bestMatch(real.value, patients);
                if (m) { real.value = m.surname + ', ' + m.name + ' (' + m.code + ')'; ghost.value=''; dd.style.display='none'; clear.style.display='flex'; }
                return;
            }
        }
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            activeIdx = Math.min(activeIdx + 1, rows.length - 1);
            highlightRow(rows);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            activeIdx = Math.max(activeIdx - 1, -1);
            highlightRow(rows);
        } else if (e.key === 'Enter') {
            e.preventDefault();
            if (activeIdx >= 0) selectPatient(activeIdx);
            else if (ghost.value) {
                var m = bestMatch(real.value, patients);
                if (m) selectPatient(patients.indexOf(m));
            }
        } else if (e.key === 'Escape') {
            ghost.value = ''; dd.style.display = 'none'; real.blur();
        }
    });

    function highlightRow(rows) {
        rows.forEach((r, i) => r.classList.toggle('active', i === activeIdx));
        if (activeIdx >= 0 && rows[activeIdx]) {
            var p = patients[activeIdx];
            real.value = p.surname + ', ' + p.name;
            ghost.value = '';
            rows[activeIdx].scrollIntoView({block:'nearest'});
        }
    }

    /* ── clear btn ── */
    clear.addEventListener('click', function() {
        real.value=''; ghost.value=''; dd.style.display='none'; clear.style.display='none'; real.focus();
    });

    /* ── close on outside ── */
    document.addEventListener('click', function(e) {
        if (!e.target.closest('#searchWrap')) {
            dd.style.display = 'none';
            ghost.value = '';
        }
    });

    /* ── re-open on focus ── */
    real.addEventListener('focus', function() {
        if (this.value.trim() && patients.length) {
            dd.style.display = 'block';
        }
    });

    function escHtml(s) {
        return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }
})();
</script>
