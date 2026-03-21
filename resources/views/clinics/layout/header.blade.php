<header id="topbar">

    {{-- Sidebar toggle --}}
    <button class="topbar-toggle" onclick="toggleSidebar()" aria-label="Toggle sidebar">
        <i class="bi bi-list"></i>
    </button>

    {{-- Search — inline autocomplete --}}
    <div class="search-wrap" id="searchWrap">
        <i class="bi bi-search si" id="searchIcon"></i>
        {{-- Ghost input shows the inline completion text --}}
        <input type="text" id="searchGhost" class="search-ghost" tabindex="-1" readonly aria-hidden="true"/>
        {{-- Real input sits on top --}}
        <input type="text"
               id="globalSearch"
               class="search-real"
               placeholder="ស្វែងរក / Search patient…"
               autocomplete="off"
               spellcheck="false"
               aria-label="Search patients"
               aria-autocomplete="inline"
               aria-controls="searchDropdown"
               aria-expanded="false"/>
        <button class="search-clear" id="searchClear" style="display:none" aria-label="Clear search">
            <i class="bi bi-x"></i>
        </button>
        {{-- Dropdown still shows for multi-result navigation --}}
        <div id="searchDropdown" class="search-dropdown" role="listbox" style="display:none"></div>
    </div>

    {{-- Right actions --}}
    <div class="tb-actions">

        {{-- Date / time --}}
        <div class="tb-datetime d-none d-md-flex">
            <i class="bi bi-calendar3" style="color:#4154f1;font-size:13px"></i>
            <span id="tbClock">{{ now()->format('d/m/Y · H:i') }}</span>
        </div>

        {{-- Quick new visit --}}
        <a href="{{ route('workflow.create') }}" class="tb-btn-new d-none d-sm-flex">
            <i class="bi bi-plus-lg"></i>
            <span>New Visit</span>
        </a>

        {{-- Notifications --}}
        <div class="tb-notif-wrap">
            <button class="tb-icon" id="notifBtn" onclick="toggleNotif()" aria-label="Notifications">
                <i class="bi bi-bell-fill"></i>
                @php $activeCount = \App\Models\VisitModel::whereDate('admitted_at', today())->whereNull('discharged_at')->count(); @endphp
                @if($activeCount > 0)
                    <span class="tb-badge">{{ min($activeCount, 9) }}{{ $activeCount > 9 ? '+' : '' }}</span>
                @endif
            </button>
            <div class="notif-panel" id="notifPanel" style="display:none">
                <div class="notif-hd">
                    <span>Active Visits Today</span>
                    <a href="{{ route('visits.index', ['status' => 'active']) }}" style="font-size:11px;color:#4154f1">View all</a>
                </div>
                @php
                    $activeVisits = \App\Models\VisitModel::whereDate('admitted_at', today())
                        ->whereNull('discharged_at')->latest('admitted_at')->take(5)->get();
                @endphp
                @forelse($activeVisits as $av)
                <a href="{{ url('/workflow/' . $av->code . '/registration') }}" class="notif-item">
                    <div class="notif-dot" style="background:{{ $av->visit_type === 'IPD' ? '#ff771d' : '#4154f1' }}"></div>
                    <div style="flex:1;min-width:0">
                        <div style="font-size:12px;font-weight:700;color:#012970;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                            {{ $av->surname }}, {{ $av->name }}
                        </div>
                        <div style="font-size:10.5px;color:#aaa">
                            {{ $av->code }} · {{ $av->visit_type }} · {{ $av->admitted_at?->format('H:i') }}
                        </div>
                    </div>
                    <span class="badge-s {{ $av->visit_type === 'IPD' ? 'b-ipd' : 'b-opd' }}" style="font-size:9px;flex-shrink:0">
                        {{ $av->visit_type }}
                    </span>
                </a>
                @empty
                <div class="notif-empty">
                    <i class="bi bi-check-circle" style="font-size:24px;color:#2eca6a;margin-bottom:8px"></i>
                    <div>No active visits</div>
                </div>
                @endforelse
            </div>
        </div>

        {{-- User menu --}}
        <div class="tb-user-wrap">
            <button class="tb-user" id="userMenuBtn" onclick="toggleUserMenu()">
                <div class="tb-avatar">
                    {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                </div>
                <div class="tb-info d-none d-lg-block">
                    <div class="name">{{ auth()->user()->name ?? 'User' }}</div>
                    <div class="role">{{ auth()->user()->role ?? 'Staff' }}</div>
                </div>
                <i class="bi bi-chevron-down" style="font-size:10px;color:#aaa;margin-left:4px" id="userChevron"></i>
            </button>

            <div class="user-menu" id="userMenu" style="display:none">
                <div class="user-menu-hd">
                    <div class="user-menu-avatar">
                        {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                    </div>
                    <div>
                        <div style="font-weight:700;color:#012970;font-size:13px">{{ auth()->user()->name }}</div>
                        <div style="font-size:11px;color:#aaa">{{ auth()->user()->email }}</div>
                        <div style="font-size:10px;background:#eef0fd;color:#4154f1;padding:1px 8px;border-radius:10px;display:inline-block;margin-top:3px;font-weight:700">
                            {{ currentClinic()?->name ?? 'Clinic' }}
                        </div>
                    </div>
                </div>
                <div class="user-menu-body">
                    <a href="#" class="user-menu-item">
                        <i class="bi bi-person-circle"></i> My Profile
                    </a>
                    <a href="#" class="user-menu-item">
                        <i class="bi bi-gear"></i> Settings
                    </a>
                    <a href="{{ route('reports.visits') }}" class="user-menu-item">
                        <i class="bi bi-bar-chart-line"></i> Reports
                    </a>
                    <div style="height:1px;background:#f0f2ff;margin:4px 0"></div>
                    <form method="POST" action="{{ route('logout') }}" style="margin:0">
                        @csrf
                        <button type="submit" class="user-menu-item" style="width:100%;text-align:left;border:none;background:none;cursor:pointer;color:#e74c3c">
                            <i class="bi bi-box-arrow-right"></i> Log Out
                        </button>
                    </form>
                </div>
            </div>
        </div>

    </div>
</header>

<script>
/* ── Clock ── */
(function tickClock() {
    var el = document.getElementById('tbClock');
    if (el) {
        var now = new Date();
        var d = String(now.getDate()).padStart(2,'0');
        var m = String(now.getMonth()+1).padStart(2,'0');
        var y = now.getFullYear();
        var h = String(now.getHours()).padStart(2,'0');
        var min = String(now.getMinutes()).padStart(2,'0');
        el.textContent = d+'/'+m+'/'+y+' · '+h+':'+min;
    }
    setTimeout(tickClock, 10000);
})();

/* ── Notification panel ── */
function toggleNotif() {
    var p = document.getElementById('notifPanel');
    var u = document.getElementById('userMenu');
    if (u) u.style.display = 'none';
    p.style.display = p.style.display === 'none' ? 'block' : 'none';
}

/* ── User menu ── */
function toggleUserMenu() {
    var u = document.getElementById('userMenu');
    var p = document.getElementById('notifPanel');
    var c = document.getElementById('userChevron');
    if (p) p.style.display = 'none';
    var open = u.style.display === 'none';
    u.style.display = open ? 'block' : 'none';
    if (c) c.style.transform = open ? 'rotate(180deg)' : '';
}

/* ── Close dropdowns on outside click ── */
document.addEventListener('click', function(e) {
    if (!e.target.closest('.tb-notif-wrap'))
        document.getElementById('notifPanel').style.display = 'none';
    if (!e.target.closest('.tb-user-wrap')) {
        document.getElementById('userMenu').style.display = 'none';
        var c = document.getElementById('userChevron');
        if (c) c.style.transform = '';
    }
});

/* ═══════════════════════════════════════════════════════════════
   PATIENT SEARCH — inline autocomplete (browser-style)

   How it works:
   - Two stacked inputs: #searchGhost (behind, grey) + #globalSearch (front, transparent bg)
   - As you type, ghost input shows the best-match full name completion
   - Tab / ArrowRight / → accepts the completion (fills input, selects that patient)
   - ArrowDown / ArrowUp navigates the dropdown list
   - Enter confirms the active item or the ghost completion
   - Escape clears ghost and closes dropdown
   - Clicking a dropdown row selects that patient
═══════════════════════════════════════════════════════════════ */
(function () {

    var real     = document.getElementById('globalSearch');
    var ghost    = document.getElementById('searchGhost');
    var dd       = document.getElementById('searchDropdown');
    var clearBtn = document.getElementById('searchClear');
    var icon     = document.getElementById('searchIcon');
    if (!real || !ghost || !dd) return;

    /* ── State ── */
    var timer       = null;
    var results     = [];        // last fetch results
    var activeIdx   = -1;        // highlighted row in dropdown
    var ghostTarget = null;      // the patient object whose name fills the ghost
    var fetching    = false;

    /* ── Colour palette for avatars ── */
    var PALETTE = ['#4154f1','#2eca6a','#ff771d','#e74c3c','#9b59b6','#00bcd4','#f39c12','#1abc9c'];
    function color(code) {
        var n = 0; for (var i = 0; i < (code||'').length; i++) n += code.charCodeAt(i);
        return PALETTE[n % PALETTE.length];
    }

    /* ─────────────────────────────────────────────────────
       GHOST INLINE COMPLETION
    ───────────────────────────────────────────────────── */
    function bestMatch(q) {
        /* Return first result whose full "Surname, Name" starts with q (case-insensitive) */
        var lq = q.toLowerCase();
        for (var i = 0; i < results.length; i++) {
            var p    = results[i];
            var full = (p.surname + ', ' + p.name).toLowerCase();
            var byCode  = p.code.toLowerCase().startsWith(lq);
            var byPhone = (p.phone||'').toLowerCase().startsWith(lq);
            if (full.startsWith(lq) || byCode || byPhone) return { patient: p, idx: i };
        }
        return null;
    }

    function showGhost(q) {
        if (!q) { clearGhost(); return; }
        var match = bestMatch(q);
        if (!match) { clearGhost(); return; }

        ghostTarget = match.patient;
        var p    = match.patient;
        var full = p.surname + ', ' + p.name;

        /* Ghost shows the full name; the real input sits on top
           so the grey "tail" of the completion peeks through */
        ghost.value = full;

        /* Sync scroll offset so ghost lines up with real */
        ghost.style.paddingLeft  = getComputedStyle(real).paddingLeft;
        ghost.style.fontSize     = getComputedStyle(real).fontSize;
        ghost.style.fontFamily   = getComputedStyle(real).fontFamily;
        ghost.style.letterSpacing = getComputedStyle(real).letterSpacing;
    }

    function clearGhost() {
        ghost.value  = '';
        ghostTarget  = null;
    }

    /* ─────────────────────────────────────────────────────
       ACCEPT COMPLETION
    ───────────────────────────────────────────────────── */
    function acceptGhost() {
        if (!ghostTarget) return false;
        var p = ghostTarget;
        real.value = p.surname + ', ' + p.name;
        clearGhost();
        clearBtn.style.display = 'flex';
        closeDD();
        commitPatient(p);
        return true;
    }

    /* Accept the currently highlighted dropdown row */
    function acceptActive() {
        if (activeIdx >= 0 && results[activeIdx]) {
            commitPatient(results[activeIdx]);
            return true;
        }
        return false;
    }

    /* ─────────────────────────────────────────────────────
       COMMIT — navigate to create page with patient data
    ───────────────────────────────────────────────────── */
    function commitPatient(p) {
        real.value = p.surname + ', ' + p.name;
        clearGhost();
        clearBtn.style.display = 'flex';
        closeDD();
        try { sessionStorage.setItem('prefill_patient', JSON.stringify(p)); } catch(e){}
        window.location.href = '/workflow/create';
    }

    /* ─────────────────────────────────────────────────────
       FETCH
    ───────────────────────────────────────────────────── */
    function doFetch(q) {
        fetching = true;
        icon.className = 'bi bi-arrow-repeat si spin';
        fetch('/patients/search?q=' + encodeURIComponent(q))
            .then(function(r) { return r.json(); })
            .then(function(data) {
                fetching = false;
                icon.className = 'bi bi-search si';
                results   = data;
                activeIdx = -1;
                showGhost(real.value.trim());
                renderDD(data, real.value.trim());
            })
            .catch(function() {
                fetching = false;
                icon.className = 'bi bi-search si';
                results = [];
                clearGhost();
                dd.innerHTML = '<div class="sd-empty"><i class="bi bi-exclamation-triangle" style="color:#e74c3c"></i> Search failed — try again</div>';
                openDD();
            });
    }

    /* ─────────────────────────────────────────────────────
       DROPDOWN RENDER
    ───────────────────────────────────────────────────── */
    function renderDD(patients, q) {
        if (!patients.length) {
            dd.innerHTML = '<div class="sd-empty">'
                + '<i class="bi bi-search" style="font-size:22px;color:#ddd;display:block;margin-bottom:8px"></i>'
                + '<div style="font-size:12.5px;font-weight:600;color:#bbb;margin-bottom:4px">No patients found</div>'
                + '<div style="font-size:11px;color:#ccc;margin-bottom:12px">Try a different name, code, or phone</div>'
                + '<a href="/workflow/create" class="sd-new-btn"><i class="bi bi-person-plus-fill"></i> Register new patient</a>'
                + '</div>';
            openDD(); return;
        }

        var html = '<div class="sd-section-label"><i class="bi bi-people-fill"></i> '
                 + patients.length + ' patient' + (patients.length !== 1 ? 's' : '') + ' found — '
                 + '<span style="color:#aaa;font-weight:400">Tab or → to complete</span></div>';

        html += patients.map(function(p, i) {
            var initials = ((p.surname||'')[0]||'').toUpperCase() + ((p.name||'')[0]||'').toUpperCase();
            var c        = color(p.code);
            var age = '';
            if (p.birthdate) {
                var pts = p.birthdate.split('/');
                if (pts.length === 3) age = Math.floor((Date.now() - new Date(pts[2],pts[1]-1,pts[0])) / 3.15576e10) + 'y';
            }
            var meta  = [p.code, p.phone||null, p.sex==='M'?'♂':p.sex==='F'?'♀':null, age||null].filter(Boolean).join(' · ');
            var lastV = p.last_visit_date
                ? '<span class="sd-visit-pill '+(p.last_visit_type==='IPD'?'ipd':'opd')+'">'+p.last_visit_type+'</span>'
                  + ' <span style="color:#bbb;font-size:10px">'+p.last_visit_date+'</span>'
                : '';
            var vcnt = p.visits_count > 0
                ? '<span class="sd-vcnt">'+p.visits_count+' visit'+(p.visits_count!==1?'s':'')+'</span>'
                : '<span class="sd-vcnt new">New</span>';

            /* Highlight the matched portion in the name */
            var display = esc(p.surname) + ', ' + esc(p.name);
            var lq = q.toLowerCase();
            if (display.toLowerCase().startsWith(lq)) {
                display = '<strong>' + esc(q) + '</strong>' + esc(display.slice(q.length));
            }

            return '<div class="sd-item" data-idx="'+i+'" role="option" tabindex="-1"'
                + ' onmousedown="selectRow('+i+')" onmouseenter="hoverRow('+i+')">'
                + '<div class="sd-avatar" style="background:'+c+'">'+initials+'</div>'
                + '<div class="sd-info">'
                + '<div class="sd-name">'+display+'</div>'
                + '<div class="sd-meta">'+esc(meta)+'</div>'
                + (lastV ? '<div class="sd-last">'+lastV+'</div>' : '')
                + '</div>'
                + '<div class="sd-right">'+vcnt+'</div>'
                + '</div>';
        }).join('');

        html += '<div class="sd-footer"><a href="/workflow/create" class="sd-new-btn"><i class="bi bi-person-plus-fill"></i> Register new patient</a></div>';
        dd.innerHTML = html;
        openDD();
    }

    /* ─────────────────────────────────────────────────────
       DROPDOWN OPEN/CLOSE
    ───────────────────────────────────────────────────── */
    function openDD()  { dd.style.display='block';  real.setAttribute('aria-expanded','true'); }
    function closeDD() { dd.style.display='none';   real.setAttribute('aria-expanded','false'); activeIdx=-1; }

    /* ─────────────────────────────────────────────────────
       ROW HIGHLIGHT
    ───────────────────────────────────────────────────── */
    function hoverRow(i) { setActive(i); }

    window.selectRow = function(i) {
        /* mousedown fires before blur — use this to commit */
        if (results[i]) commitPatient(results[i]);
    };

    function setActive(i) {
        activeIdx = i;
        dd.querySelectorAll('.sd-item').forEach(function(el, j) {
            el.classList.toggle('active', j === i);
        });
        if (results[i]) showGhost(real.value.trim());  /* ghost still shows */
        var active = dd.querySelector('.sd-item.active');
        if (active) active.scrollIntoView({ block: 'nearest' });
    }

    /* ─────────────────────────────────────────────────────
       INPUT EVENTS
    ───────────────────────────────────────────────────── */
    real.addEventListener('input', function() {
        var q = real.value;
        clearBtn.style.display = q ? 'flex' : 'none';
        clearGhost();
        clearTimeout(timer);
        if (!q.trim() || q.trim().length < 2) { closeDD(); results=[]; return; }
        timer = setTimeout(function() { doFetch(q.trim()); }, 220);
    });

    real.addEventListener('focus', function() {
        var q = real.value.trim();
        if (q.length >= 2 && results.length) {
            showGhost(q);
            openDD();
        } else if (q.length >= 2 && !fetching) {
            doFetch(q);
        }
    });

    real.addEventListener('keydown', function(e) {
        var items = dd.querySelectorAll('.sd-item');

        if (e.key === 'Tab' || e.key === 'ArrowRight') {
            /* Accept ghost completion */
            if (ghostTarget && real.selectionStart === real.value.length) {
                e.preventDefault();
                acceptGhost();
            }
        } else if (e.key === 'ArrowDown') {
            e.preventDefault();
            if (dd.style.display === 'none' && results.length) openDD();
            var next = activeIdx < items.length - 1 ? activeIdx + 1 : 0;
            setActive(next);
            /* Ghost shows the highlighted row's name */
            if (results[next]) ghost.value = results[next].surname + ', ' + results[next].name;
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            var prev = activeIdx > 0 ? activeIdx - 1 : items.length - 1;
            setActive(prev);
            if (results[prev]) ghost.value = results[prev].surname + ', ' + results[prev].name;
        } else if (e.key === 'Enter') {
            e.preventDefault();
            if (!acceptActive()) acceptGhost();
        } else if (e.key === 'Escape') {
            clearGhost();
            closeDD();
        }
    });

    /* Prevent blur from firing before mousedown on dropdown rows */
    dd.addEventListener('mousedown', function(e) { e.preventDefault(); });

    real.addEventListener('blur', function() {
        /* Small delay so mousedown on dd rows fires first */
        setTimeout(function() {
            clearGhost();
            closeDD();
        }, 150);
    });

    /* ─────────────────────────────────────────────────────
       CLEAR BUTTON & OUTSIDE CLICK
    ───────────────────────────────────────────────────── */
    clearBtn.addEventListener('click', function() {
        real.value = '';
        clearGhost();
        closeDD();
        results = [];
        clearBtn.style.display = 'none';
        real.focus();
    });

    document.addEventListener('click', function(e) {
        if (!e.target.closest('#searchWrap')) { clearGhost(); closeDD(); }
    });

    /* ─────────────────────────────────────────────────────
       UTIL
    ───────────────────────────────────────────────────── */
    function esc(s) {
        return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

})();
</script>
