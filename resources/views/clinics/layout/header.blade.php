@php use App\Models\VisitModel; @endphp
<header id="topbar">

    {{-- ── Left: toggle + clinic chip ──────────────────────────────────────── --}}
    <div class="tb-left">
        <button class="tb-toggle" onclick="toggleSidebar()" aria-label="Toggle sidebar">
            <i class="bi bi-list"></i>
        </button>
        <div class="tb-breadcrumb d-none d-md-flex">
            <span class="tb-clinic-chip">
                @if(currentClinic()?->logo)
                    <img src="{{ asset('storage/' . currentClinic()->logo) }}" alt=""
                         style="height:16px;border-radius:3px;object-fit:cover">
                @else
                    <i class="bi bi-hospital" style="font-size:12px"></i>
                @endif
                {{ currentClinic()?->name_kh ?? currentClinic()?->name ?? 'Clinic' }}
            </span>
        </div>
    </div>

    {{-- ── Center: patient search ────────────────────────────────────────────── --}}
    <div class="tb-search-wrap" id="searchWrap" role="combobox" aria-haspopup="listbox" aria-expanded="false">
        <i class="bi bi-search tb-search-icon" id="searchIcon"></i>
        <input id="searchGhost" class="tb-search-ghost" tabindex="-1" readonly aria-hidden="true">
        <input id="globalSearch" class="tb-search-real"
               placeholder="ស្វែងរក / Search patient…"
               autocomplete="off" spellcheck="false"
               aria-label="Search patients"
               aria-autocomplete="list"
               aria-controls="searchDropdown">
        <button class="tb-search-clear" id="searchClear" style="display:none" aria-label="Clear search">
            <i class="bi bi-x"></i>
        </button>
        <div id="searchDropdown" class="tb-search-dd" role="listbox" style="display:none"></div>
    </div>

    {{-- ── Right actions ───────────────────────────────────────────────────── --}}
    <div class="tb-right">

        <div class="tb-clock d-none d-xl-flex">
            <i class="bi bi-clock"></i>
            <span id="tbClock">{{ now()->format('d/m/Y · H:i') }}</span>
        </div>

        <a href="{{ route('workflow.create') }}" class="tb-new-btn" title="New Visit">
            <i class="bi bi-plus-lg"></i>
            <span class="d-none d-lg-inline">New Visit</span>
        </a>

        {{-- Notifications --}}
        <div class="tb-dd-wrap" id="notifWrap">
            <button class="tb-icon-btn" id="notifBtn" onclick="tbToggle('notif')" aria-label="Notifications">
                <i class="bi bi-bell-fill"></i>
                @php $activeCount = VisitModel::whereDate('admitted_at', today())->whereNull('discharged_at')->count(); @endphp
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
                    $activeVisits = VisitModel::whereDate('admitted_at', today())
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

        {{-- Language switcher --}}
        <div class="tb-lang-wrap">
            @php $currentLang = app()->getLocale(); @endphp
            <form method="POST" action="{{ route('lang.switch', $currentLang === 'km' ? 'en' : 'km') }}"
                  style="margin:0">
                @csrf
                <button type="submit" class="tb-lang-btn" title="Switch language">
                    <span class="tb-lang-active">{{ strtoupper($currentLang) }}</span>
                    <span class="tb-lang-divider">|</span>
                    <span class="tb-lang-other">{{ $currentLang === 'km' ? 'EN' : 'KM' }}</span>
                </button>
            </form>
        </div>

        {{-- User menu --}}
        <div class="tb-dd-wrap" id="userWrap">
            <button class="tb-user-btn" id="userBtn" onclick="tbToggle('user')">
                <div class="tb-av">{{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}</div>
                <div class="tb-user-info d-none d-lg-block">
                    <div class="tb-user-name">{{ auth()->user()->name ?? 'User' }}</div>
                    <div class="tb-user-role">{{ auth()->user()->roles->first()?->name ?? 'Staff' }}</div>
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

    </div>
</header>

<script>
    /* ── Clock ────────────────────────────────────────────────────────────── */
    (function tick() {
        var el = document.getElementById('tbClock');
        if (el) {
            var n = new Date(), p = v => String(v).padStart(2, '0');
            el.textContent = p(n.getDate()) + '/' + p(n.getMonth() + 1) + '/' + n.getFullYear() + ' · ' + p(n.getHours()) + ':' + p(n.getMinutes());
        }
        setTimeout(tick, 15000);
    })();

    /* ── Dropdown toggle ──────────────────────────────────────────────────── */
    function tbToggle(which) {
        var panels = {notif: 'notifPanel', user: 'userPanel'};
        Object.entries(panels).forEach(([k, id]) => {
            var el = document.getElementById(id);
            if (!el) return;
            var isOpen = el.style.display !== 'none';
            el.style.display = (k === which) ? (isOpen ? 'none' : 'block') : 'none';
            if (k === 'user') {
                document.getElementById('userChevron')?.classList.toggle('rotated', k === which && !isOpen);
            }
        });
    }

    document.addEventListener('click', function (e) {
        if (!e.target.closest('#notifWrap')) document.getElementById('notifPanel')?.style.setProperty('display', 'none');
        if (!e.target.closest('#userWrap')) {
            document.getElementById('userPanel')?.style.setProperty('display', 'none');
            document.getElementById('userChevron')?.classList.remove('rotated');
        }
    });

    /* ════════════════════════════════════════════════════════════════════════
       PATIENT SEARCH — enhanced autocomplete
       Features:
       • Ghost text (tab/→ accepts)
       • Skeleton loader while fetching
       • Rich result rows: avatar, match highlight, visit pill, last visit
       • Keyboard: ↓↑ navigate, Enter confirm, Esc dismiss, Tab/→ accept ghost
       • Dropdown header (count + keyboard hint)
       • Footer: new patient button + keyboard hint
       • Result actions: open last visit OR new visit
    ════════════════════════════════════════════════════════════════════════ */
    (function () {

        var real = document.getElementById('globalSearch');
        var ghost = document.getElementById('searchGhost');
        var dd = document.getElementById('searchDropdown');
        var clearBtn = document.getElementById('searchClear');
        var wrap = document.getElementById('searchWrap');
        var icon = document.getElementById('searchIcon');

        if (!real) return;

        var patients = [];
        var cache = {};
        var activeIdx = -1;
        var debounce = null;
        var abortCtrl = null;
        var lastQ = '';

        /* ── Utils ─────────────────────────────────────────────── */
        function esc(s) {
            return String(s || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        }

        // Highlight matched substring in display text
        function highlight(str, q) {
            if (!q) return esc(str);
            var idx = str.toLowerCase().indexOf(q.toLowerCase());
            if (idx === -1) return esc(str);
            return esc(str.slice(0, idx)) + '<em>' + esc(str.slice(idx, idx + q.length)) + '</em>' + esc(str.slice(idx + q.length));
        }

        /* ── Fetch ─────────────────────────────────────────────── */
        function fetch_(q) {
            if (cache[q]) {
                render(cache[q], q);
                return;
            }
            showSkeleton();
            if (abortCtrl) abortCtrl.abort();
            abortCtrl = new AbortController();
            fetch('/patients/search/json?q=' + encodeURIComponent(q), {signal: abortCtrl.signal})
                .then(r => r.json())
                .then(data => {
                    cache[q] = data;
                    render(data, q);
                })
                .catch(() => {
                });
        }

        /* ── Skeleton ───────────────────────────────────────────── */
        function showSkeleton() {
            dd.innerHTML =
                '<div class="tb-search-dd-hd"><span class="tb-search-kbd">Searching…</span></div>'
                + [0, 1, 2].map(() =>
                    '<div class="tb-search-skel-row">'
                    + '<div class="tb-skel tb-skel-av"></div>'
                    + '<div style="flex:1"><div class="tb-skel tb-skel-l1"></div><div class="tb-skel tb-skel-l2"></div></div>'
                    + '</div>'
                ).join('');
            openDd();
        }

        /* ── Ghost text ─────────────────────────────────────────── */
        function bestMatch(val, list) {
            if (!val || !list.length) return null;
            var v = val.toLowerCase();
            return list.find(p => {
                var full = (p.surname + ', ' + p.name).toLowerCase();
                var code = (p.code || '').toLowerCase();
                return full.startsWith(v) || code.startsWith(v);
            }) || null;
        }

        function updateGhost(val, list) {
            if (!val) {
                ghost.value = '';
                return;
            }
            var m = bestMatch(val, list);
            if (!m) {
                ghost.value = '';
                return;
            }
            var full = m.surname + ', ' + m.name;
            ghost.value = full.toLowerCase().startsWith(val.toLowerCase())
                ? val + full.slice(val.length)
                : '';
        }

        /* ── Render ─────────────────────────────────────────────── */
        var COLORS = ['#4154f1', '#2eca6a', '#ff771d', '#e74c3c', '#9b59b6', '#00bcd4'];

        function render(list, q) {
            patients = list;
            activeIdx = -1;
            updateGhost(real.value, list);

            var header =
                '<div class="tb-search-dd-hd">'
                + '<span>' + (list.length ? list.length + ' patient' + (list.length !== 1 ? 's' : '') : 'No results') + '</span>'
                + '<span class="tb-search-kbd"><kbd>↑↓</kbd> navigate&nbsp; <kbd>↵</kbd> open&nbsp; <kbd>→</kbd> complete</span>'
                + '</div>';

            var rows = '';
            if (!list.length) {
                rows = '<div class="tb-search-empty">'
                    + '<i class="bi bi-search"></i>'
                    + '<div>No patient found for <strong>' + esc(q) + '</strong></div>'
                    + '<div style="font-size:11px;margin-top:6px;color:#aaa">Check spelling or create a new patient</div>'
                    + '</div>';
            } else {
                rows = list.slice(0, 8).map((p, i) => {
                    var initials = ((p.surname || '').charAt(0) + (p.name || '').charAt(0)).toUpperCase();
                    var color = COLORS[(p.code || '').charCodeAt(2) % COLORS.length] || COLORS[0];
                    var typeBadge = p.last_visit_type
                        ? '<span style="font-size:9px;padding:1px 6px;border-radius:8px;font-weight:700;background:' +
                        (p.last_visit_type === 'IPD' ? '#fff3e8;color:#ff771d' : '#e8f8ef;color:#2eca6a') + '">' + esc(p.last_visit_type) + '</span>'
                        : '';
                    var sexChip = '<span class="sex-chip' + (p.sex === 'F' ? ' f' : '') + '">'
                        + (p.sex === 'M' ? 'ប្រុស' : p.sex === 'F' ? 'ស្រី' : '—') + '</span>';
                    var vcnt = p.visits_count || 0;
                    var vcntHtml = vcnt > 0
                        ? '<span class="tb-search-visits">' + vcnt + ' visit' + (vcnt > 1 ? 's' : '') + '</span>'
                        : '<span class="tb-search-visits" style="background:#f0fdf4;color:#2eca6a">New</span>';

                    return '<div class="tb-search-row" role="option" tabindex="-1" data-idx="' + i + '" onclick="selectPatient(' + i + ')">'
                        + '<div class="tb-search-av" style="background:' + color + '">' + initials + '</div>'
                        + '<div style="flex:1;min-width:0">'
                        + '<div class="tb-search-name">' + highlight(p.surname + ', ' + p.name, q) + '</div>'
                        + '<div class="tb-search-meta">'
                        + '<code style="font-size:10px;color:#64748b">' + esc(p.code) + '</code>'
                        + (p.phone ? '<span>· ' + esc(p.phone) + '</span>' : '')
                        + sexChip
                        + (p.birthdate ? '<span>· ' + esc(p.birthdate) + '</span>' : '')
                        + '</div>'
                        + (p.last_visit_date ? '<div class="tb-search-meta" style="margin-top:2px">'
                            + typeBadge + ' <span>Last: ' + esc(p.last_visit_date) + '</span></div>' : '')
                        + '</div>'
                        + '<div class="tb-search-right">' + vcntHtml + '</div>'
                        + '</div>';
                }).join('');
            }

            var footer =
                '<div class="tb-search-dd-ft">'
                + '<span class="tb-search-kbd" style="color:#bbb;font-size:10px">Press <kbd>Enter</kbd> to open latest visit</span>'
                + '<a href="{{ route("workflow.create") }}" class="tb-search-dd-new"><i class="bi bi-plus-circle-fill"></i> New Visit</a>'
                + '</div>';

            dd.innerHTML = header + rows + footer;
            openDd();
        }

        /* ── Open / close ───────────────────────────────────────── */
        function openDd() {
            dd.style.display = 'block';
            wrap.setAttribute('aria-expanded', 'true');
        }

        function closeDd() {
            dd.style.display = 'none';
            wrap.setAttribute('aria-expanded', 'false');
            ghost.value = '';
            activeIdx = -1;
        }

        /* ── Select ─────────────────────────────────────────────── */
        window.selectPatient = function (idx) {
            var p = patients[idx];
            if (!p) return;
            real.value = p.surname + ', ' + p.name + ' (' + p.code + ')';
            ghost.value = '';
            clearBtn.style.display = 'flex';
            closeDd();

            // Go to latest visit if exists, otherwise new visit
            if (p.last_visit_code) {
                window.location.href = '/workflow/' + p.last_visit_code + '/registration';
            } else {
                window.location.href = '/workflow/create?patient_code=' + encodeURIComponent(p.code);
            }
        };

        /* ── Input event ────────────────────────────────────────── */
        real.addEventListener('input', function () {
            var val = this.value.trim();
            clearBtn.style.display = val ? 'flex' : 'none';
            if (!val) {
                ghost.value = '';
                closeDd();
                lastQ = '';
                return;
            }
            if (val === lastQ) return;
            lastQ = val;
            clearTimeout(debounce);
            debounce = setTimeout(() => fetch_(val), 220);
        });

        /* ── Keyboard ───────────────────────────────────────────── */
        real.addEventListener('keydown', function (e) {
            var rows = dd.querySelectorAll('.tb-search-row');

            // Accept ghost with Tab or ArrowRight
            if ((e.key === 'Tab' || e.key === 'ArrowRight') && ghost.value && ghost.value !== real.value) {
                e.preventDefault();
                var m = bestMatch(real.value, patients);
                if (m) {
                    real.value = m.surname + ', ' + m.name + ' (' + m.code + ')';
                    ghost.value = '';
                    closeDd();
                    clearBtn.style.display = 'flex';
                }
                return;
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
                if (activeIdx >= 0) {
                    selectPatient(activeIdx);
                    return;
                }
                var m = bestMatch(real.value, patients);
                if (m) selectPatient(patients.indexOf(m));
            } else if (e.key === 'Escape') {
                closeDd();
                real.blur();
            }
        });

        function highlightRow(rows) {
            rows.forEach((r, i) => r.classList.toggle('active', i === activeIdx));
            if (activeIdx >= 0 && rows[activeIdx]) {
                var p = patients[activeIdx];
                if (p) {
                    real.value = p.surname + ', ' + p.name;
                    ghost.value = '';
                }
                rows[activeIdx].scrollIntoView({block: 'nearest'});
            }
        }

        /* ── Clear ──────────────────────────────────────────────── */
        clearBtn.addEventListener('click', function () {
            real.value = '';
            ghost.value = '';
            lastQ = '';
            closeDd();
            clearBtn.style.display = 'none';
            cache = {};
            real.focus();
        });

        /* ── Close on outside click ─────────────────────────────── */
        document.addEventListener('click', function (e) {
            if (!e.target.closest('#searchWrap')) closeDd();
        });

        /* ── Re-open on focus ───────────────────────────────────── */
        real.addEventListener('focus', function () {
            if (this.value.trim() && patients.length) openDd();
        });

    })();
</script>
