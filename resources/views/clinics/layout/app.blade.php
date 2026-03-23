<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'MediFlow EMR') — {{ currentClinic()?->name ?? 'Clinic' }}</title>

    {{-- Bootstrap + Icons --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"/>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"/>

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet"
          href="https://fonts.googleapis.com/css2?family=Hanuman:wght@300;400;700&family=Nunito:wght@400;500;600;700;800&display=swap"/>

    {{-- EMR CSS --}}
    @vite(['resources/css/clinic.css'])
    <link rel="stylesheet" href="{{ asset('css/ux-enhancements.css') }}">

    {{-- Sidebar + Header CSS --}}
    @if(file_exists(public_path('css/sidebar-header.css')))
    <link rel="stylesheet" href="{{ asset('css/sidebar-header.css') }}">
    @endif

    {{-- Visual enhancement layer: operations-first, live indicators, toasts --}}
    @if(file_exists(public_path('css/emr-enhance.css')))
    <link rel="stylesheet" href="{{ asset('css/emr-enhance.css') }}">
    @endif

    {{-- Sidebar / Header / Reports improvements --}}
    <style>

/* ═══════════════════════════════════════════════════════════════
   SIDEBAR IMPROVEMENTS
═══════════════════════════════════════════════════════════════ */

/* Logo strip */
.sb-logo {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 16px 14px 14px;
    border-bottom: 1px solid rgba(255,255,255,.08);
    flex-shrink: 0;
    position: relative;
}
.sb-logo-icon {
    width: 36px;
    height: 36px;
    background: var(--primary);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 20px;
    flex-shrink: 0;
    overflow: hidden;
}
.sb-logo-text { flex: 1; overflow: hidden; }
.sb-logo-title {
    font-size: 13.5px;
    font-weight: 800;
    color: #fff;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    letter-spacing: -.2px;
}
.sb-logo-sub { font-size: 9.5px; color: #5a7aaa; margin-top: 1px; }
.sb-collapse-btn {
    width: 26px; height: 26px;
    background: rgba(255,255,255,.07);
    border: none;
    border-radius: 6px;
    color: #7096c8;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
    flex-shrink: 0;
    transition: background .15s, color .15s;
}
.sb-collapse-btn:hover { background: rgba(255,255,255,.14); color: #fff; }

/* Nav links — override old .s-link */
.s-link {
    display: flex;
    align-items: center;
    gap: 0;
    padding: 0;
    color: var(--sidebar-txt);
    font-size: 13px;
    transition: background .15s, border-left-color .15s, color .15s;
    border-left: 3px solid transparent;
    cursor: pointer;
    text-decoration: none;
    user-select: none;
    border-radius: 0;
    margin: 0 8px;
    border-radius: 8px;
    border-left: none;
    overflow: hidden;
}
.s-link:hover { background: rgba(255,255,255,.07); color: #fff; }
.s-link.active {
    background: rgba(65,84,241,.28);
    color: #fff;
    box-shadow: inset 3px 0 0 var(--primary);
}
.s-icon {
    width: 40px;
    height: 38px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    flex-shrink: 0;
}
.s-text { flex: 1; padding: 9px 0; overflow: hidden; }
.s-km { display: block; font-size: 12.5px; font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.s-en { display: block; font-size: 9.5px; color: #5a7aaa; margin-top: 1px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.s-link.active .s-en { color: #a0b0e8; }
.s-badge {
    margin-right: 10px;
    background: var(--primary);
    color: #fff;
    font-size: 9.5px;
    padding: 2px 7px;
    border-radius: 20px;
    font-weight: 700;
    flex-shrink: 0;
}
.s-pill {
    margin-right: 10px;
    font-size: 8.5px;
    padding: 1px 7px;
    border-radius: 20px;
    font-weight: 800;
    flex-shrink: 0;
    text-transform: uppercase;
    letter-spacing: .4px;
}
.s-pill.new { background: #2eca6a22; color: #2eca6a; border: 1px solid #2eca6a44; }

/* Collapsed state */
#sidebar.collapsed .sb-logo-text,
#sidebar.collapsed .s-text,
#sidebar.collapsed .s-badge,
#sidebar.collapsed .s-pill,
#sidebar.collapsed .nav-sec,
#sidebar.collapsed .sb-user-info,
#sidebar.collapsed .sb-collapse-btn { display: none; }
#sidebar.collapsed { width: 60px; }
#sidebar.collapsed .s-link { margin: 0 4px; justify-content: center; border-radius: 8px; }
#sidebar.collapsed .s-icon { width: 52px; height: 40px; }

/* User strip */
.sb-user {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 14px;
    border-top: 1px solid rgba(255,255,255,.07);
    flex-shrink: 0;
    background: rgba(0,0,0,.12);
}
.sb-user-avatar {
    width: 32px; height: 32px;
    border-radius: 50%;
    background: linear-gradient(135deg,#4154f1,#717ff5);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
    font-weight: 800;
    flex-shrink: 0;
}
.sb-user-info { flex: 1; overflow: hidden; }
.sb-user-name { font-size: 12px; font-weight: 700; color: #e0e8f8; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.sb-user-role { font-size: 10px; color: #5a7aaa; margin-top: 1px; }
.sb-logout-btn {
    width: 28px; height: 28px;
    background: rgba(255,255,255,.07);
    border: none;
    border-radius: 7px;
    color: #7096c8;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    flex-shrink: 0;
    transition: background .15s, color .15s;
}
.sb-logout-btn:hover { background: rgba(231,76,60,.25); color: #ff7070; }
#sidebar.collapsed .sb-user { justify-content: center; padding: 12px 4px; }
#sidebar.collapsed .sb-logout-btn { width: 40px; height: 36px; }

/* Nav section labels */
.nav-sec {
    padding: 14px 14px 4px;
    font-size: 9px;
    text-transform: uppercase;
    letter-spacing: 1.2px;
    color: rgba(255,255,255,.22);
    font-weight: 700;
    white-space: nowrap;
}

/* ═══════════════════════════════════════════════════════════════
   HEADER IMPROVEMENTS
═══════════════════════════════════════════════════════════════ */

.tb-datetime {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 11.5px;
    color: #888;
    background: #f6f9ff;
    border: 1px solid #e6eaf5;
    border-radius: 20px;
    padding: 5px 12px;
    white-space: nowrap;
    flex-shrink: 0;
}
.tb-btn-new {
    display: flex;
    align-items: center;
    gap: 6px;
    background: var(--primary);
    color: #fff;
    border: none;
    border-radius: 8px;
    padding: 7px 14px;
    font-size: 12.5px;
    font-weight: 700;
    cursor: pointer;
    text-decoration: none;
    transition: background .15s, transform .15s;
    white-space: nowrap;
    flex-shrink: 0;
    font-family: var(--font);
}
.tb-btn-new:hover { background: var(--primary-d); color: #fff; transform: translateY(-1px); }

/* Notification panel */
.tb-notif-wrap { position: relative; flex-shrink: 0; }
.notif-panel {
    position: absolute;
    top: calc(100% + 10px);
    right: 0;
    width: 300px;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 8px 40px rgba(1,41,112,.14);
    border: 1px solid #e6eaf5;
    z-index: 9000;
    overflow: hidden;
}
.notif-hd {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 16px;
    font-size: 12px;
    font-weight: 700;
    color: #012970;
    border-bottom: 1px solid #f0f2ff;
    background: #fafbff;
}
.notif-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 16px;
    border-bottom: 1px solid #f5f6ff;
    transition: background .12s;
    text-decoration: none;
}
.notif-item:hover { background: #f6f9ff; }
.notif-dot {
    width: 8px; height: 8px;
    border-radius: 50%;
    flex-shrink: 0;
}
.notif-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 28px 16px;
    color: #bbb;
    font-size: 12px;
}

/* User menu */
.tb-user-wrap { position: relative; flex-shrink: 0; }
.tb-user {
    display: flex;
    align-items: center;
    gap: 8px;
    background: none;
    border: none;
    cursor: pointer;
    padding: 4px 8px;
    border-radius: 10px;
    transition: background .15s;
}
.tb-user:hover { background: #f0f2ff; }
.user-menu {
    position: absolute;
    top: calc(100% + 10px);
    right: 0;
    width: 240px;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 8px 40px rgba(1,41,112,.14);
    border: 1px solid #e6eaf5;
    z-index: 9000;
    overflow: hidden;
}
.user-menu-hd {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 16px;
    background: linear-gradient(135deg,#012970,#1a3a7c);
}
.user-menu-avatar {
    width: 38px; height: 38px;
    border-radius: 50%;
    background: rgba(255,255,255,.2);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 15px;
    font-weight: 800;
    flex-shrink: 0;
    border: 2px solid rgba(255,255,255,.3);
}
.user-menu-body { padding: 6px; }
.user-menu-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 9px 10px;
    border-radius: 8px;
    font-size: 12.5px;
    color: #333;
    text-decoration: none;
    transition: background .12s;
    font-family: var(--font);
}
.user-menu-item:hover { background: #f0f2ff; color: var(--primary); }

/* Search dropdown */
.search-dropdown {
    position: absolute;
    top: calc(100% + 6px);
    left: 0;
    right: 0;
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 8px 30px rgba(1,41,112,.12);
    border: 1px solid #e6eaf5;
    z-index: 9000;
    overflow: hidden;
    max-height: 280px;
    overflow-y: auto;
}
.sd-item {
    display: block;
    padding: 10px 14px;
    border-bottom: 1px solid #f5f6ff;
    text-decoration: none;
    transition: background .12s;
}
.sd-item:hover { background: #f6f9ff; }
.sd-item:last-child { border-bottom: none; }
.search-wrap { position: relative; }

/* ═══════════════════════════════════════════════════════════════
   REPORTS PAGE
═══════════════════════════════════════════════════════════════ */

.report-filter-bar {
    background: #fff;
    border-radius: var(--radius);
    box-shadow: var(--card-shadow);
    padding: 16px 20px;
    margin-bottom: 20px;
}
.report-filter-bar form { display: flex; flex-wrap: wrap; gap: 12px; align-items: flex-end; }
.filter-group { display: flex; flex-direction: column; gap: 4px; min-width: 140px; }
.filter-label {
    font-size: 10.5px;
    font-weight: 700;
    color: #777;
    text-transform: uppercase;
    letter-spacing: .5px;
}
.filter-group .form-control,
.filter-group .form-select { font-size: 12.5px; padding: 7px 10px; }
.filter-actions { display: flex; gap: 8px; align-items: flex-end; flex-wrap: wrap; }

.report-stat {
    background: #fff;
    border-radius: var(--radius);
    box-shadow: var(--card-shadow);
    padding: 16px 18px;
    display: flex;
    flex-direction: column;
    gap: 4px;
}
.report-stat-num {
    font-size: 28px;
    font-weight: 800;
    line-height: 1;
    font-family: 'Nunito', sans-serif;
}
.report-stat-lbl { font-size: 11px; color: #999; }
.report-stat-sub { font-size: 10.5px; color: #bbb; margin-top: 2px; }

/* Export buttons */
.btn-export {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 7px 14px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 700;
    cursor: pointer;
    border: 1.5px solid;
    transition: all .15s;
    text-decoration: none;
    font-family: var(--font);
    white-space: nowrap;
}
.btn-export-csv  { color: #2eca6a; border-color: #2eca6a; background: #e8f8ef; }
.btn-export-csv:hover  { background: #2eca6a; color: #fff; }
.btn-export-print { color: #4154f1; border-color: #4154f1; background: #eef0fd; }
.btn-export-print:hover { background: #4154f1; color: #fff; }

/* Report table */
.report-tbl { width: 100%; border-collapse: collapse; }
.report-tbl thead th {
    background: #f6f9ff;
    color: #012970;
    font-size: 10.5px;
    font-weight: 700;
    padding: 10px 14px;
    border-bottom: 2px solid #e6eaf5;
    text-transform: uppercase;
    letter-spacing: .4px;
    white-space: nowrap;
}
.report-tbl tbody td {
    padding: 10px 14px;
    border-bottom: 1px solid #f0f2ff;
    font-size: 12.5px;
    vertical-align: middle;
}
.report-tbl tbody tr:hover { background: #fafbff; }
.report-tbl tbody tr:last-child td { border-bottom: none; }
.report-tbl .no-data td {
    text-align: center;
    padding: 40px;
    color: #bbb;
    font-size: 13px;
}

/* Print styles */
@media print {
    #sidebar, #topbar, #bottomNav,
    .report-filter-bar, .filter-actions,
    .btn-export, .pg-header .btn,
    .toast-wrap { display: none !important; }
    #main { margin-left: 0 !important; padding-top: 0 !important; }
    .main-body { padding: 0 !important; }
    .report-tbl tbody tr:hover { background: transparent !important; }
    body { font-size: 11pt; }
}

/* ═══════════════════════════════════════════════════════════════
   SEARCH — Inline autocomplete
═══════════════════════════════════════════════════════════════ */

/* Wrap */
.search-wrap {
    position: relative;
    flex: 1;
    max-width: 340px;
}

/* Ghost layer — sits behind input, shows completion text */
.search-ghost {
    position: absolute;
    left: 36px;
    right: 32px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 13px;
    font-family: var(--font);
    color: #aaa;
    pointer-events: none;
    white-space: nowrap;
    overflow: hidden;
    z-index: 1;
    letter-spacing: 0;
}

/* Real input — sits above ghost, background transparent so ghost shows through */
.search-wrap input#globalSearch {
    position: relative;
    z-index: 2;
    border: 1.5px solid #e0e6f5;
    background: transparent;
    border-radius: 10px;
    padding: 8px 34px 8px 36px;
    font-size: 13px;
    width: 100%;
    font-family: var(--font);
    color: #333;
    outline: none;
    transition: border-color .2s, box-shadow .2s;
    caret-color: var(--primary);
}
/* White bg on the wrap itself so ghost text has a background */
.search-wrap {
    background: #f6f9ff;
    border-radius: 10px;
}
.search-wrap:focus-within {
    background: #fff;
    box-shadow: 0 0 0 3px rgba(65,84,241,.1);
}
.search-wrap input#globalSearch:focus {
    border-color: var(--primary);
}

/* Search icon */
.search-wrap .si {
    position: absolute;
    left: 11px;
    top: 50%;
    transform: translateY(-50%);
    color: #aaa;
    font-size: 14px;
    pointer-events: none;
    z-index: 3;
    transition: color .2s;
}
.search-wrap:focus-within .si { color: var(--primary); }
@keyframes spin { to { transform: translateY(-50%) rotate(360deg); } }
.si.spin { animation: spin .7s linear infinite !important; }

/* Clear ×  button */
.search-clear {
    position: absolute;
    right: 8px;
    top: 50%;
    transform: translateY(-50%);
    width: 20px;
    height: 20px;
    background: #e0e4f5;
    border: none;
    border-radius: 50%;
    color: #777;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 11px;
    padding: 0;
    z-index: 3;
    transition: background .15s, color .15s;
}
.search-clear:hover { background: var(--danger); color: #fff; }

/* ── Dropdown panel ── */
.search-dropdown {
    position: absolute;
    top: calc(100% + 8px);
    left: 0;
    right: 0;
    min-width: 380px;
    max-width: 500px;
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 16px 56px rgba(1,41,112,.18), 0 2px 8px rgba(1,41,112,.08);
    border: 1px solid #e6eaf5;
    z-index: 9100;
    overflow: hidden;
    max-height: 440px;
    overflow-y: auto;
    animation: sdSlide .14s ease;
}
@keyframes sdSlide {
    from { opacity:0; transform: translateY(-8px) scale(.98); }
    to   { opacity:1; transform: translateY(0)   scale(1);    }
}

/* Header row */
.sd-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 8px 14px 6px;
    border-bottom: 1px solid #f0f2ff;
    background: #fafbff;
}
.sd-label {
    font-size: 9.5px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .7px;
    color: #bbb;
    display: flex;
    align-items: center;
    gap: 5px;
}
.sd-hint {
    font-size: 9.5px;
    color: #ccc;
    font-style: italic;
}

/* Result items */
.sd-item {
    display: flex;
    align-items: center;
    gap: 11px;
    padding: 10px 14px;
    cursor: pointer;
    border-bottom: 1px solid #f5f6ff;
    transition: background .1s;
    outline: none;
}
.sd-item:last-of-type { border-bottom: none; }
.sd-item:hover,
.sd-item.active {
    background: #f0f4ff;
}
.sd-item.active {
    box-shadow: inset 3px 0 0 var(--primary);
}

/* Avatar */
.sd-avatar {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 13.5px;
    font-weight: 800;
    flex-shrink: 0;
    letter-spacing: -.5px;
}

/* Patient info */
.sd-info { flex: 1; min-width: 0; }
.sd-name {
    font-size: 13px;
    font-weight: 700;
    color: #012970;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.sd-meta {
    font-size: 10.5px;
    color: #aaa;
    margin-top: 1px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.sd-last {
    display: flex;
    align-items: center;
    gap: 5px;
    margin-top: 2px;
}
.sd-date { font-size: 10px; color: #bbb; }

/* Match highlight */
.sd-hl {
    background: #fff3a3;
    color: #7a6000;
    border-radius: 2px;
    font-style: normal;
    padding: 0 1px;
}

/* Visit type pills */
.sd-pill {
    font-size: 9px;
    font-weight: 800;
    padding: 1px 7px;
    border-radius: 8px;
}
.sd-pill.opd { background: #e8f8ef; color: #2eca6a; }
.sd-pill.ipd { background: #fff3e8; color: #ff771d; }

/* Visit count badge */
.sd-right { flex-shrink: 0; }
.sd-vcnt {
    font-size: 10px;
    font-weight: 700;
    background: #eef0fd;
    color: #4154f1;
    padding: 3px 9px;
    border-radius: 10px;
    white-space: nowrap;
}
.sd-vcnt.new { background: #e8f8ef; color: #2eca6a; }

/* Footer */
.sd-footer {
    padding: 10px 14px;
    border-top: 1px solid #f0f2ff;
    background: #fafbff;
}
.sd-new-btn {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    font-size: 12px;
    font-weight: 700;
    color: var(--primary);
    text-decoration: none;
    padding: 7px 14px;
    border-radius: 8px;
    border: 1.5px solid #c5cbf9;
    background: #eef0fd;
    transition: background .15s, color .15s, transform .1s;
}
.sd-new-btn:hover { background: var(--primary); color: #fff; transform: translateY(-1px); }

/* Empty state */
.sd-empty {
    padding: 28px 20px;
    text-align: center;
    color: #bbb;
}

/* Skeleton */
.sd-loading { padding: 4px 0; }
.sd-skeleton {
    display: flex;
    align-items: center;
    gap: 11px;
    padding: 10px 14px;
    border-bottom: 1px solid #f5f6ff;
}
.sk-avatar {
    width: 40px; height: 40px;
    border-radius: 10px;
    flex-shrink: 0;
}
.sk-lines { flex: 1; display: flex; flex-direction: column; gap: 7px; }
.sk-line {
    height: 10px;
    border-radius: 6px;
}
.sk-l1 { width: 58%; }
.sk-l2 { width: 35%; }
.sk-avatar, .sk-line {
    background: linear-gradient(90deg, #f0f2ff 25%, #e6eaf5 50%, #f0f2ff 75%);
    background-size: 200% 100%;
    animation: shimmer 1.2s infinite;
}
@keyframes shimmer {
    0%   { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}

/* Mobile */
@media (max-width: 480px) {
    .search-dropdown { min-width: 0; left: -8px; right: -8px; border-radius: 12px; }
    .search-wrap { max-width: 160px; }
    .sd-hint { display: none; }
}

/* ═══════════════════════════════════════════════════════════════
   SEARCH — inline autocomplete (ghost + real input overlay)
═══════════════════════════════════════════════════════════════ */

/* Search wrap */
.search-wrap {
    position: relative;
    flex: 1;
    max-width: 340px;
}

/* Shared styles for both ghost and real inputs */
.search-real,
.search-ghost {
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    width: 100%;
    height: 38px;
    font-size: 13px;
    font-family: var(--font);
    letter-spacing: 0;
    border-radius: 10px;
    padding: 8px 34px 8px 36px;
    box-sizing: border-box;
    line-height: 1.4;
    white-space: nowrap;
    overflow: hidden;
}

/* Ghost — sits behind, shows completion in grey */
.search-ghost {
    position: absolute;
    z-index: 1;
    background: #f6f9ff;
    border: 1.5px solid #e0e6f5;
    color: #aaa;               /* greyed-out completion text */
    pointer-events: none;
    user-select: none;
    outline: none;
    cursor: default;
}

/* Real input — transparent background, sits on top */
.search-real {
    position: relative;       /* on top in stacking context */
    z-index: 2;
    background: transparent;
    border: 1.5px solid transparent;  /* invisible until focus */
    color: #444;
    outline: none;
    transition: border-color .2s, box-shadow .2s;
    caret-color: var(--primary);
}

/* When real input has content, show its border on ghost */
.search-wrap:focus-within .search-ghost {
    border-color: var(--primary);
    background: #fff;
    box-shadow: 0 0 0 3px rgba(65,84,241,.1);
}

/* Make search-wrap itself sized so both positioned inputs fit */
.search-wrap {
    height: 38px;
}

/* Search icon */
.search-wrap .si {
    position: absolute;
    left: 11px;
    top: 50%;
    transform: translateY(-50%);
    color: #aaa;
    font-size: 14px;
    pointer-events: none;
    z-index: 3;
    transition: color .2s;
}
.search-wrap:focus-within .si { color: var(--primary); }
@keyframes spin { to { transform: translateY(-50%) rotate(360deg); } }
.si.spin { animation: spin .7s linear infinite; color: var(--primary) !important; }

/* Clear button */
.search-clear {
    position: absolute;
    right: 8px;
    top: 50%;
    transform: translateY(-50%);
    width: 20px; height: 20px;
    background: #e0e4f5;
    border: none;
    border-radius: 50%;
    color: #888;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 11px;
    padding: 0;
    z-index: 4;
    transition: background .15s, color .15s;
}
.search-clear:hover { background: var(--primary); color: #fff; }

/* ── Dropdown panel ── */
.search-dropdown {
    position: absolute;
    top: calc(100% + 8px);
    left: 0;
    right: 0;
    min-width: 360px;
    max-width: 480px;
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 12px 48px rgba(1,41,112,.16), 0 2px 8px rgba(1,41,112,.08);
    border: 1px solid #e6eaf5;
    z-index: 9100;
    overflow: hidden;
    animation: sdIn .12s ease;
    max-height: 420px;
    overflow-y: auto;
}
@keyframes sdIn {
    from { opacity: 0; transform: translateY(-6px); }
    to   { opacity: 1; transform: translateY(0); }
}

/* Section label */
.sd-section-label {
    padding: 8px 14px 4px;
    font-size: 9.5px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .7px;
    color: #bbb;
    display: flex;
    align-items: center;
    gap: 5px;
    position: sticky;
    top: 0;
    background: #fff;
    border-bottom: 1px solid #f5f6ff;
    z-index: 1;
}

/* Patient result row */
.sd-item {
    display: flex;
    align-items: center;
    gap: 11px;
    padding: 10px 14px;
    cursor: pointer;
    transition: background .08s;
    border-bottom: 1px solid #f5f6ff;
    outline: none;
}
.sd-item:last-of-type { border-bottom: none; }
.sd-item:hover, .sd-item.active { background: #f0f4ff; }
.sd-item.active {
    background: #eef0fd;
    border-left: 3px solid var(--primary);
    padding-left: 11px;
}

/* Avatar */
.sd-avatar {
    width: 38px; height: 38px;
    border-radius: 10px;
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
    font-weight: 800;
    flex-shrink: 0;
    letter-spacing: -.5px;
}

/* Info */
.sd-info { flex: 1; min-width: 0; }
.sd-name {
    font-size: 13px;
    font-weight: 600;
    color: #012970;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.sd-name strong { color: var(--primary); font-weight: 800; }
.sd-meta {
    font-size: 10.5px; color: #aaa; margin-top: 1px;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.sd-last {
    font-size: 10.5px; color: #aaa; margin-top: 2px;
    display: flex; align-items: center; gap: 4px;
}

/* Visit pills */
.sd-visit-pill {
    font-size: 9px; font-weight: 800;
    padding: 1px 6px; border-radius: 8px;
}
.sd-visit-pill.opd { background: #e8f8ef; color: #2eca6a; }
.sd-visit-pill.ipd { background: #fff3e8; color: #ff771d; }

/* Visit count */
.sd-right { flex-shrink: 0; }
.sd-vcnt {
    font-size: 10px; font-weight: 700;
    background: #eef0fd; color: #4154f1;
    padding: 2px 8px; border-radius: 10px; white-space: nowrap;
}
.sd-vcnt.new { background: #e8f8ef; color: #2eca6a; }

/* Footer */
.sd-footer {
    padding: 10px 14px;
    border-top: 1px solid #f0f2ff;
    background: #fafbff;
    position: sticky;
    bottom: 0;
}
.sd-new-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    font-weight: 700;
    color: var(--primary);
    text-decoration: none;
    padding: 6px 12px;
    border-radius: 8px;
    border: 1.5px solid #c5cbf9;
    background: #eef0fd;
    transition: background .15s, transform .1s;
    font-family: var(--font);
}
.sd-new-btn:hover { background: var(--primary); color: #fff; transform: translateY(-1px); }

/* Empty state */
.sd-empty {
    padding: 28px 20px;
    text-align: center;
    color: #bbb;
}

/* Skeleton loading */
.sd-skeleton {
    display: flex;
    align-items: center;
    gap: 11px;
    padding: 10px 14px;
    border-bottom: 1px solid #f5f6ff;
}
.sk-avatar {
    width: 38px; height: 38px; border-radius: 10px; flex-shrink: 0;
    background: linear-gradient(90deg,#f0f2ff 25%,#e6eaf5 50%,#f0f2ff 75%);
    background-size: 200% 100%;
    animation: shimmer 1.2s infinite;
}
.sk-lines { flex: 1; display: flex; flex-direction: column; gap: 6px; }
.sk-line {
    height: 10px; border-radius: 6px;
    background: linear-gradient(90deg,#f0f2ff 25%,#e6eaf5 50%,#f0f2ff 75%);
    background-size: 200% 100%;
    animation: shimmer 1.2s infinite;
}
.sk-l1 { width: 60%; } .sk-l2 { width: 40%; }
@keyframes shimmer { 0% { background-position: 200% 0; } 100% { background-position: -200% 0; } }

/* Responsive */
@media (max-width: 576px) {
    .search-dropdown { min-width: 0; left: -8px; right: -8px; border-radius: 10px; }
    .search-wrap { max-width: 180px; }
}
@media (max-width: 380px) {
    .search-wrap { max-width: 140px; }
}

    </style>

    @yield('style')
    @stack('styles')
</head>
<body>

{{-- Sidebar overlay (mobile) --}}
<div id="sidebarOverlay" onclick="closeSidebar()"></div>

{{-- Sidebar --}}
@include('clinics.layout.sidebar')

{{-- Topbar --}}
@include('clinics.layout.header')

{{-- Main content --}}
<main id="main">
    <div class="main-body">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @yield('content')
    </div>
</main>

{{-- Mobile Bottom Nav --}}
@include('clinics.layout.bottom-nav')

{{-- Toast container --}}
<div class="toast-wrap" id="toastWrap"></div>

{{-- Flash toast (from session) --}}
<x-flash/>

{{-- Scripts --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
@vite(['resources/js/clinic.js'])

@stack('scripts')
</body>
</html>
