<nav id="bottomNav">
    <div class="bn-items">
        <button class="bn-item {{ request()->routeIs('dashboard') ? 'active' : '' }}"
                onclick="window.location='{{ url('/') }}'">
            <i class="bi bi-grid-1x2-fill"></i>
            <span class="bn-lbl">ដើម<br><small>Home</small></span>
        </button>

        <button class="bn-item {{ request()->routeIs('patients.*') ? 'active' : '' }}"
                onclick="window.location='{{ url('/patients') }}'">
            <i class="bi bi-people-fill"></i>
            <span class="bn-lbl">អ្នកជំងឺ<br><small>Patients</small></span>
        </button>

        <button class="bn-item bn-item--new"
                onclick="window.location='{{ url('/workflow/create') }}'">
            <i class="bi bi-plus-circle-fill" style="font-size:26px;color:#4154f1"></i>
            <span class="bn-lbl" style="color:#4154f1">ថ្មី<br><small>New</small></span>
        </button>

        <button class="bn-item {{ request()->routeIs('visits.*') ? 'active' : '' }}"
                onclick="window.location='{{ url('/visits') }}'">
            <i class="bi bi-hospital-fill"></i>
            <span class="bn-lbl">ចូល<br><small>Visits</small></span>
        </button>

        <button class="bn-item {{ request()->routeIs('reports.*') ? 'active' : '' }}"
                onclick="window.location='{{ url('/reports/visits') }}'">
            <i class="bi bi-bar-chart-fill"></i>
            <span class="bn-lbl">របាយ<br><small>Reports</small></span>
        </button>
    </div>
</nav>
