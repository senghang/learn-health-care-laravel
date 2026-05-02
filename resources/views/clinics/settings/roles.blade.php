@extends('clinics.layout.app')
@section('title', __('app.roles_permissions'))
@section('content')

<x-page-header
    :title="__('app.roles_permissions')"
    subtitle="Roles &amp; Permissions"
    icon="bi-shield-lock-fill"
    :breadcrumbs="[
        ['label'=>__('app.home'),'url'=>route('dashboard')],
        ['label'=>__('app.settings'),'url'=>route('settings.general')],
        ['label'=>__('app.roles_permissions')],
    ]">
    {{-- Sync missing permissions button --}}
    <form method="POST" action="{{ route('settings.roles.seed-permissions') }}" style="display:inline">
        @csrf
        <button type="submit" class="btn btn-outline-secondary btn-sm" title="Add any new default permissions that are missing for this clinic">
            <i class="bi bi-arrow-repeat"></i> Sync Permissions
        </button>
    </form>
</x-page-header>

@if(session('flash'))
<div class="note note-success mb-3"><i class="bi bi-check-circle-fill"></i> {{ session('flash') }}</div>
@endif
@if(session('flash_error'))
<div class="note note-danger mb-3"><i class="bi bi-exclamation-triangle-fill"></i> {{ session('flash_error') }}</div>
@endif

<div class="row g-3">

{{-- Left: Create role + roles list --}}
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
                <div class="mb-3">
                    <label class="form-label" style="font-size:12px;font-weight:700;color:#444">Description <small style="color:#aaa;font-weight:400">optional</small></label>
                    <input type="text" name="description" class="form-control" placeholder="Short description of this role's access…" maxlength="255"/>
                </div>
                <div class="mb-3">
                    <label class="form-label" style="font-size:12px;font-weight:700;color:#444">Level <small style="color:#aaa;font-weight:400">0–100, higher = more authority</small></label>
                    <input type="number" name="level" class="form-control" placeholder="e.g. 10" min="0" max="100" style="width:120px"/>
                </div>
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
                    @if($role->is_system)
                        <i class="bi bi-shield-lock-fill" style="font-size:14px"></i>
                    @else
                        {{ strtoupper(substr($role->name, 0, 1)) }}
                    @endif
                </div>
                <div style="flex:1;min-width:0">
                    <div style="display:flex;align-items:center;gap:5px">
                        <span style="font-weight:700;color:#012970;font-size:13px">{{ $role->name }}</span>
                        @if($role->is_system)
                        <span style="font-size:9px;background:#f0f2ff;color:#4154f1;padding:1px 6px;border-radius:6px;font-weight:700;border:1px solid #d0d5ff">SYSTEM</span>
                        @endif
                        @if($role->level)
                        <span style="font-size:9px;color:#aaa">L{{ $role->level }}</span>
                        @endif
                    </div>
                    <div style="font-size:10.5px;color:#aaa">
                        {{ $role->permissions->count() }} {{ __('app.permissions') }}
                        · {{ $role->users_count }} {{ __('app.users') }}
                    </div>
                    @if($role->description)
                    <div style="font-size:10.5px;color:#888;margin-top:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $role->description }}</div>
                    @endif
                </div>
                <div style="display:flex;gap:4px;flex-shrink:0">
                    @if($role->is_system)
                        <span class="btn btn-sm btn-outline-secondary" style="opacity:.35;pointer-events:none;cursor:not-allowed" title="System roles cannot be modified">
                            <i class="bi bi-lock-fill"></i>
                        </span>
                    @else
                        <button class="btn btn-sm btn-outline-primary"
                                onclick="editRole({{ $role->id }},'{{ addslashes($role->name) }}','{{ addslashes($role->description ?? '') }}',{{ $role->level ?? 'null' }})"
                                title="{{ __('app.edit') }}">
                            <i class="bi bi-pencil"></i>
                        </button>
                        @if($role->users_count == 0)
                        <form method="POST" action="{{ route('settings.roles.destroy', $role->id) }}"
                              data-confirm="Delete role &quot;{{ $role->name }}&quot;? This cannot be undone."
                              data-confirm-type="danger" data-confirm-title="Delete Role">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger" title="{{ __('app.delete') }}">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                        @else
                        <span class="btn btn-sm btn-outline-danger" style="opacity:.3;pointer-events:none" title="{{ $role->users_count }} user(s) assigned">
                            <i class="bi bi-trash"></i>
                        </span>
                        @endif
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

{{-- Right: Permission matrix (per role) --}}
<div class="col-12 col-lg-8">
    @if($roles->count() && $permissions->count())
    @foreach($roles as $role)
    <div class="card-emr mb-3">
        <div class="card-hd" style="background:#f6f9ff">
            <div class="card-hd-title">
                @if($role->is_system)
                    <i class="bi bi-shield-lock-fill" style="color:#4154f1"></i>
                @else
                    <i class="bi bi-shield-fill" style="color:#4154f1"></i>
                @endif
                {{ $role->name }}
                @if($role->is_system)
                    <span style="font-size:9.5px;background:#f0f2ff;color:#4154f1;padding:1px 6px;border-radius:5px;font-weight:700;border:1px solid #d0d5ff;margin-left:4px">SYSTEM</span>
                @endif
                <span style="font-size:10.5px;color:#aaa;font-weight:400">— {{ $role->permissions->count() }}/{{ $permissions->flatten()->count() }} permissions</span>
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
                            <label style="display:flex;align-items:center;gap:8px;cursor:pointer;padding:6px 10px;border-radius:8px;border:1px solid #e6eaf5;background:#fafbff;transition:background .12s{{ $role->is_system ? ';opacity:.6;cursor:not-allowed' : '' }}"
                                   @if(!$role->is_system)
                                   onmouseover="this.style.background='#f0f4ff'" onmouseout="this.style.background='#fafbff'"
                                   @endif>
                                <input type="checkbox" name="permissions[]"
                                       value="{{ $perm->slug }}"
                                       style="width:15px;height:15px;accent-color:#4154f1;flex-shrink:0"
                                       {{ $role->permissions->contains('id', $perm->id) ? 'checked' : '' }}
                                       {{ $role->is_system ? 'disabled' : '' }}>
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
                    @if($role->is_system)
                        <span style="font-size:11px;color:#aaa;display:flex;align-items:center;gap:5px">
                            <i class="bi bi-lock-fill" style="color:#4154f1"></i>
                            System role permissions are managed by the application and cannot be changed here.
                        </span>
                    @else
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="checkAll(this.closest('form'), true)">
                            <i class="bi bi-check-all"></i> {{ __('app.select_all') }}
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="checkAll(this.closest('form'), false)">
                            <i class="bi bi-square"></i> {{ __('app.clear_all') }}
                        </button>
                        <button type="submit" class="btn btn-primary btn-sm ms-auto">
                            <i class="bi bi-save-fill"></i> {{ __('app.save_permissions') }}
                        </button>
                    @endif
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
<x-modal id="editRoleModal" title="{{ __('app.edit_role') }}" icon="bi-shield-fill">
    <form method="POST" id="editRoleForm" novalidate>
        @csrf @method('PATCH')
        <x-form.field name="name" km="{{ __('app.role_name') }}" en="Role Name" required maxlength="80" id="editRoleName"/>
        <x-form.field name="description" en="Description" placeholder="Short description…" maxlength="255" id="editRoleDescription"/>
        <div class="fld" style="max-width:160px">
            <label class="flbl"><span class="en">/ Level</span> <small style="color:#aaa;font-weight:400">0–100</small></label>
            <input type="number" name="level" id="editRoleLevel" class="form-control" min="0" max="100"/>
        </div>
    </form>
    <x-slot:footer>
        <button type="submit" form="editRoleForm" class="btn btn-primary">{{ __('app.update') }}</button>
        <button type="button" class="btn btn-outline-secondary" onclick="closeModal('editRoleModal')">{{ __('app.cancel') }}</button>
    </x-slot:footer>
</x-modal>

@push('scripts')
<script>
function editRole(id, name, description, level) {
    var form = document.getElementById('editRoleForm');
    if (!form) return;
    form.action = '/settings/roles/' + id;
    document.getElementById('editRoleName').value        = name;
    document.getElementById('editRoleDescription').value = description || '';
    document.getElementById('editRoleLevel').value       = level !== null && level !== undefined ? level : '';
    openModal('editRoleModal');
}
function checkAll(form, checked) {
    form.querySelectorAll('input[type="checkbox"]:not([disabled])').forEach(cb => cb.checked = checked);
}
</script>
@endpush

@endsection
