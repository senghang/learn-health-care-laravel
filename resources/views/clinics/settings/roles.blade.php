@extends('clinics.layout.app')
@section('title', __('app.roles_permissions'))
@section('content')

<x-page-header
    :title="__('app.roles_permissions')"
    subtitle="Roles &amp; Permissions"
    :breadcrumbs="[
        ['label'=>__('app.home'),'url'=>route('dashboard')],
        ['label'=>__('app.settings'),'url'=>route('settings.general')],
        ['label'=>__('app.roles_permissions')],
    ]">
</x-page-header>

@if(session('flash'))
<div class="note note-success mb-3"><i class="bi bi-check-circle-fill"></i> {{ session('flash') }}</div>
@endif
@if(session('flash_error'))
<div class="note note-danger mb-3"><i class="bi bi-exclamation-triangle-fill"></i> {{ session('flash_error') }}</div>
@endif

<div class="row g-3">

{{-- Create role + roles list --}}
<div class="col-12 col-lg-4">

    {{-- Create role form --}}
    <div class="card-emr mb-3">
        <div class="card-hd" style="background:#f6f9ff">
            <div class="card-hd-title"><i class="bi bi-shield-plus" style="color:#4154f1"></i> {{ __('app.new_role') }}</div>
        </div>
        <div class="card-bd">
            <form method="POST" action="{{ route('settings.roles.store') }}" novalidate>
                @csrf
                <x-form.field name="name" :km="__('app.role_name')" en="Role Name"
                              placeholder="Doctor, Nurse, Receptionist…" required/>
                <button type="submit" class="btn btn-primary btn-w100">
                    <i class="bi bi-plus-circle-fill"></i> {{ __('app.create_role') }}
                </button>
            </form>
        </div>
    </div>

    {{-- Roles list --}}
    <div class="card-emr">
        <div class="card-hd">
            <div class="card-hd-title"><i class="bi bi-shield-fill" style="color:#4154f1"></i> {{ __('app.roles') }}</div>
            <span style="font-size:11px;color:#aaa">{{ $roles->count() }} {{ __('app.roles') }}</span>
        </div>
        <div class="card-bd" style="padding:0">
            @forelse($roles as $role)
            @php
                $colors = ['#4154f1','#2eca6a','#ff771d','#e74c3c','#9b59b6','#00bcd4'];
                $col    = $colors[$loop->index % count($colors)];
            @endphp
            <div style="display:flex;align-items:center;gap:10px;padding:12px 16px;border-bottom:1px solid #f5f6ff">
                <div style="width:34px;height:34px;border-radius:9px;background:{{ $col }}22;color:{{ $col }};display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:800;flex-shrink:0">
                    {{ strtoupper(substr($role->name, 0, 1)) }}
                </div>
                <div style="flex:1;min-width:0">
                    <div style="font-weight:700;color:#012970;font-size:13px">{{ $role->name }}</div>
                    <div style="font-size:10.5px;color:#aaa">
                        {{ $role->permissions->count() }} {{ __('app.permissions') }}
                        · {{ $role->users_count }} {{ __('app.users') }}
                    </div>
                </div>
                <div style="display:flex;gap:4px">
                    <button class="btn btn-sm btn-outline-primary" onclick="editRole({{ $role->id }},'{{ addslashes($role->name) }}')" title="{{ __('app.edit') }}">
                        <i class="bi bi-pencil"></i>
                    </button>
                    @if($role->users_count == 0)
                    <form method="POST" action="{{ route('settings.roles.destroy', $role->id) }}" onsubmit="return confirm('{{ __('app.confirm_delete') }}')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger" title="{{ __('app.delete') }}">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                    @endif
                </div>
            </div>
            @empty
            <div style="text-align:center;padding:28px;color:#bbb">
                <div style="font-size:28px;margin-bottom:6px;opacity:.3">🛡</div>
                {{ __('app.no_roles_yet') }}
            </div>
            @endforelse
        </div>
    </div>

</div>

{{-- Permission matrix --}}
<div class="col-12 col-lg-8">
    @if($roles->count() && $permissions->count())
    @foreach($roles as $role)
    <div class="card-emr mb-3">
        <div class="card-hd" style="background:#f6f9ff">
            <div class="card-hd-title">
                <i class="bi bi-shield-fill" style="color:#4154f1"></i>
                {{ $role->name }}
                <span style="font-size:10.5px;color:#aaa;font-weight:400">— {{ __('app.permissions') }}</span>
            </div>
        </div>
        <div class="card-bd">
            <form method="POST" action="{{ route('settings.roles.permissions', $role->id) }}" novalidate>
                @csrf
                @foreach($permissions as $group => $perms)
                <div style="margin-bottom:14px">
                    <div style="font-size:10.5px;font-weight:800;text-transform:uppercase;letter-spacing:.5px;color:#4154f1;margin-bottom:8px;padding-bottom:4px;border-bottom:1px solid #f0f2ff">
                        {{ ucfirst($group) }}
                    </div>
                    <div class="row g-2">
                        @foreach($perms as $perm)
                        <div class="col-12 col-sm-6">
                            <label style="display:flex;align-items:center;gap:8px;cursor:pointer;padding:6px 10px;border-radius:8px;border:1px solid #e6eaf5;background:#fafbff;transition:background .12s"
                                   onmouseover="this.style.background='#f0f4ff'" onmouseout="this.style.background='#fafbff'">
                                <input type="checkbox" name="permissions[]"
                                       value="{{ $perm->slug }}"
                                       style="width:15px;height:15px;accent-color:#4154f1;flex-shrink:0"
                                       {{ $role->permissions->contains('id', $perm->id) ? 'checked' : '' }}>
                                <div>
                                    <div style="font-size:12px;font-weight:600;color:#012970">{{ $perm->name }}</div>
                                    <div style="font-size:10px;color:#aaa;font-family:monospace">{{ $perm->slug }}</div>
                                </div>
                            </label>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endforeach
                <div style="display:flex;gap:8px;margin-top:12px;padding-top:12px;border-top:1px solid #f0f2ff">
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="checkAll(this.closest('form'), true)">
                        <i class="bi bi-check-all"></i> {{ __('app.select_all') }}
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="checkAll(this.closest('form'), false)">
                        <i class="bi bi-square"></i> {{ __('app.clear_all') }}
                    </button>
                    <button type="submit" class="btn btn-primary btn-sm ms-auto">
                        <i class="bi bi-save-fill"></i> {{ __('app.save_permissions') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endforeach
    @else
    <div class="card-emr">
        <div class="card-bd" style="text-align:center;padding:40px;color:#bbb">
            <div style="font-size:40px;margin-bottom:10px;opacity:.3">🛡</div>
            <div style="font-size:13px;font-weight:600;margin-bottom:6px">{{ __('app.create_role_first') }}</div>
            <div style="font-size:11px">{{ __('app.create_role_hint') }}</div>
        </div>
    </div>
    @endif
</div>

</div>{{-- /row --}}

{{-- Edit role modal --}}
<div id="editRoleModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:9000;align-items:center;justify-content:center">
    <div style="background:#fff;border-radius:14px;padding:24px;width:380px;max-width:90vw;box-shadow:0 20px 60px rgba(0,0,0,.2)">
        <h3 style="font-size:15px;font-weight:800;color:#012970;margin-bottom:16px">{{ __('app.edit_role') }}</h3>
        <form method="POST" id="editRoleForm" novalidate>
            @csrf @method('PATCH')
            <x-form.field name="name" :km="__('app.role_name')" en="Role Name" id="editRoleName" required/>
            <div style="display:flex;gap:8px;margin-top:12px">
                <button type="submit" class="btn btn-primary">{{ __('app.update') }}</button>
                <button type="button" class="btn btn-outline-secondary" onclick="closeEditModal()">{{ __('app.cancel') }}</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function editRole(id, name) {
    var modal = document.getElementById('editRoleModal');
    var form  = document.getElementById('editRoleForm');
    var input = document.getElementById('editRoleName');
    if (!modal || !form || !input) return;
    form.action = '/settings/roles/' + id;
    input.value = name;
    modal.style.display = 'flex';
}
function closeEditModal() {
    document.getElementById('editRoleModal').style.display = 'none';
}
document.getElementById('editRoleModal').addEventListener('click', function(e) {
    if (e.target === this) closeEditModal();
});
function checkAll(form, checked) {
    form.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = checked);
}
</script>
@endpush

@endsection
