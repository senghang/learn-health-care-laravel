{{-- resources/views/clinics/layout/_sidebar-footer.blade.php --}}
<div class="sb-user-footer" id="sbUser">
    <div class="sb-user-av">{{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}</div>
    <div class="sb-user-meta" id="sbUserTxt">
        <div class="sb-user-name-text">{{ auth()->user()->name ?? 'User' }}</div>
        <div class="sb-user-clinic">
            {{ auth()->user()->role?->name ?? 'Staff' }} · {{ currentClinic()?->name ?? 'Clinic' }}
        </div>
    </div>
    <form method="POST" action="{{ route('logout') }}" class="sb-logout">
        @csrf
        <button type="submit" class="sb-logout-btn" title="Log out"><i class="bi bi-box-arrow-right"></i></button>
    </form>
</div>
