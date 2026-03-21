<nav id="bottomNav">
    <div class="bn-items">
        <button class="bn-item {{ request()->routeIs('dashboard') ? 'active' : '' }}"
                onclick="window.location='{{ route('dashboard') }}'">
            <i class="bi bi-grid-1x2-fill"></i>
            <span class="bn-lbl">ដើម<br><small>Home</small></span>
        </button>
        <button class="bn-item {{ request()->routeIs('visits.*') ? 'active' : '' }}"
                onclick="window.location='{{ route('visits.index') }}'">
            <i class="bi bi-hospital"></i>
            <span class="bn-lbl">ចូលព្យាបាល<br><small>Visits</small></span>
        </button>
        <button class="bn-item {{ request()->routeIs('workflow.create') ? 'active' : '' }}"
                onclick="window.location='{{ route('workflow.create') }}'">
            <i class="bi bi-plus-circle-fill" style="font-size:28px;color:#4154f1"></i>
            <span class="bn-lbl" style="color:#4154f1">ថ្មី<br><small>New</small></span>
        </button>
        {{--        <button class="bn-item {{ request()->routeIs('labs.*') ? 'active' : '' }}"--}}
        {{--                onclick="window.location='{{ route('labs.index') }}'">--}}
        {{--            <i class="bi bi-flask2-fill"></i>--}}
        {{--            <span class="bn-lbl">ពិសោធ<br><small>Labs</small></span>--}}
        {{--        </button>--}}
        {{--        <button class="bn-item {{ request()->routeIs('billing.*') ? 'active' : '' }}"--}}
        {{--                onclick="window.location='{{ route('billing.index') }}'">--}}
        {{--            <i class="bi bi-receipt-cutoff"></i>--}}
        {{--            <span class="bn-lbl">វិក្ក<br><small>Bill</small></span>--}}
        {{--        </button>--}}
    </div>
</nav>
