<header id="topbar">
    <button class="topbar-toggle" onclick="toggleSidebar()" aria-label="Toggle sidebar">
        <i class="bi bi-list"></i>
    </button>

    <div class="search-wrap">
        <i class="bi bi-search si"></i>
        <input type="text"
               placeholder="ស្វែងរកអ្នកជំងឺ… / Search…"
               autocomplete="off"
               id="globalSearch"/>
    </div>

    <div class="tb-actions">
        <button class="tb-icon" title="ការជូនដំណឹង / Notifications">
            <i class="bi bi-bell-fill"></i>
            <span class="tb-badge">{{ $notificationCount ?? 0 }}</span>
        </button>
        <button class="tb-icon d-none d-sm-flex" title="ប្រតិទិន / Calendar">
            <i class="bi bi-calendar-event-fill"></i>
        </button>
        <div class="tb-user">
            <div class="tb-avatar">
                {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
            </div>
            <div class="tb-info">
                <div class="name">{{ auth()->user()->name ?? 'Dr. Admin' }}</div>
                <div class="role">{{ auth()->user()->role ?? 'Physician' }}</div>
            </div>
        </div>
    </div>
</header>

<script>
</script>
