@extends('clinics.layout.app')
@section('title', __('app.roles_permissions'))
@section('content')

<x-ui.page-header
    :km="__('app.roles_permissions')"
    title="Roles & Permissions"
    :breadcrumbs="[
        ['label'=>__('app.home'),'url'=>route('dashboard')],
        ['label'=>__('app.settings'),'url'=>route('settings.general')],
        ['label'=>__('app.roles_permissions')],
    ]">
    <x-slot:actions>
        <form method="POST" action="{{ route('settings.roles.seed-permissions') }}">
            @csrf
            <x-ui.button type="submit" variant="secondary">
                <x-slot:icon><i class="bi bi-arrow-repeat"></i></x-slot:icon>
                Sync Permissions
            </x-ui.button>
        </form>
    </x-slot:actions>
</x-ui.page-header>

@if(session('flash'))
    <x-ui.alert type="success" class="mb-4">{{ session('flash') }}</x-ui.alert>
@endif
@if(session('flash_error'))
    <x-ui.alert type="error" class="mb-4">{{ session('flash_error') }}</x-ui.alert>
@endif

<div class="row g-3">

{{-- Left: Create role + roles list --}}
<div class="col-12 col-lg-4">

    {{-- Create role form --}}
    <x-ui.card class="mb-3">
        <x-slot:header>
            <x-ui.card-header :label="__('app.new_role')" icon="bi-shield-plus"/>
        </x-slot:header>
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
            <x-ui.button type="submit" variant="primary" :fullWidth="true">
                <x-slot:icon><i class="bi bi-plus-circle-fill"></i></x-slot:icon>
                {{ __('app.create_role') }}
            </x-ui.button>
        </form>
    </x-ui.card>

    {{-- Roles list --}}
    <x-ui.card>
        <x-slot:header>
            <x-ui.card-header :label="__('app.roles')" icon="bi-shield-fill" :count="$roles->count()"/>
        </x-slot:header>
        <div style="padding:0">
            @forelse($roles as $role)
            @php
                $colors = ['#4154f1','#2eca6a','#ff771d','#e74c3c','#9b59b6','#00bcd4'];
                $col    = $colors[$loop->index % count($colors)];
            @endphp
            <div class="flex items-center gap-2.5 px-4 py-3 border-b border-[#f5f6ff]">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center text-sm font-black flex-shrink-0"
                     style="background:{{ $col }}22;color:{{ $col }}">
                    @if($role->is_system)
                        <i class="bi bi-shield-lock-fill" style="font-size:14px"></i>
                    @else
                        {{ strtoupper(substr($role->name, 0, 1)) }}
                    @endif
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-1.5">
                        <span class="font-bold text-[#012970] text-sm">{{ $role->name }}</span>
                        @if($role->is_system)
                            <x-ui.badge variant="primary" size="sm">SYSTEM</x-ui.badge>
                        @endif
                        @if($role->level)
                            <span class="text-[9px] text-[#94a3b8]">L{{ $role->level }}</span>
                        @endif
                    </div>
                    <div class="text-[10.5px] text-[#94a3b8]">
                        {{ $role->permissions->count() }} {{ __('app.permissions') }}
                        · {{ $role->users_count }} {{ __('app.users') }}
                    </div>
                    @if($role->description)
                    <div class="text-[10.5px] text-[#888] mt-0.5 truncate">{{ $role->description }}</div>
                    @endif
                </div>
                <div class="flex gap-1 flex-shrink-0">
                    @if($role->is_system)
                        <span class="w-7 h-7 flex items-center justify-center rounded-md text-[#94a3b8] opacity-35 cursor-not-allowed text-xs" title="System roles cannot be modified">
                            <i class="bi bi-lock-fill"></i>
                        </span>
                    @else
                        <button class="w-7 h-7 flex items-center justify-center rounded-md text-[#4154f1] hover:bg-[#eef0fd] transition-colors text-xs"
                                onclick="editRole({{ $role->id }},'{{ addslashes($role->name) }}','{{ addslashes($role->description ?? '') }}',{{ $role->level ?? 'null' }})"
                                title="{{ __('app.edit') }}">
                            <i class="bi bi-pencil"></i>
                        </button>
                        @if($role->users_count == 0)
                        <form method="POST" action="{{ route('settings.roles.destroy', $role->id) }}"
                              data-confirm="Delete role &quot;{{ $role->name }}&quot;? This cannot be undone."
                              data-confirm-type="danger" data-confirm-title="Delete Role">
                            @csrf @method('DELETE')
                            <button type="submit" class="w-7 h-7 flex items-center justify-center rounded-md text-[#e74c3c] hover:bg-[#fde8e8] transition-colors text-xs" title="{{ __('app.delete') }}">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                        @else
                        <span class="w-7 h-7 flex items-center justify-center rounded-md text-[#e74c3c] opacity-30 cursor-not-allowed text-xs" title="{{ $role->users_count }} user(s) assigned">
                            <i class="bi bi-trash"></i>
                        </span>
                        @endif
                    @endif
                </div>
            </div>
            @empty
            <x-ui.empty-state icon="bi-shield" :title="__('app.no_roles_yet')" compact/>
            @endforelse
        </div>
    </x-ui.card>

</div>

{{-- Right: Permission matrix (per role) --}}
<div class="col-12 col-lg-8">
    @if($roles->count() && $permissions->count())
    @foreach($roles as $role)
    <x-ui.card class="mb-3">
        <x-slot:header>
            <x-ui.card-header :label="$role->name" :icon="$role->is_system ? 'bi-shield-lock-fill' : 'bi-shield-fill'">
                <x-slot:actions>
                    @if($role->is_system)
                        <x-ui.badge variant="primary" size="sm">SYSTEM</x-ui.badge>
                    @endif
                    <span class="text-[10.5px] text-[#94a3b8]">{{ $role->permissions->count() }}/{{ $permissions->flatten()->count() }} permissions</span>
                </x-slot:actions>
            </x-ui.card-header>
        </x-slot:header>
        <form method="POST" action="{{ route('settings.roles.permissions', $role->id) }}" novalidate>
            @csrf
            @foreach($permissions as $group => $perms)
            <div class="mb-3.5">
                <div class="text-[10.5px] font-black uppercase tracking-wide text-[#4154f1] mb-2 pb-1 border-b border-[#f0f2ff]">
                    {{ ucfirst($group) }}
                </div>
                <div class="row g-2">
                    @foreach($perms as $perm)
                    <div class="col-12 col-sm-6">
                        <label class="flex items-center gap-2 cursor-pointer px-2.5 py-1.5 rounded-lg border border-[#e6eaf5] bg-[#fafbff] transition-colors{{ $role->is_system ? ' opacity-60 cursor-not-allowed' : ' hover:bg-[#f0f4ff]' }}">
                            <input type="checkbox" name="permissions[]"
                                   value="{{ $perm->slug }}"
                                   class="w-3.5 h-3.5 flex-shrink-0 accent-[#4154f1]"
                                   {{ $role->permissions->contains('id', $perm->id) ? 'checked' : '' }}
                                   {{ $role->is_system ? 'disabled' : '' }}>
                            <div>
                                <div class="text-xs font-semibold text-[#012970]">{{ $perm->name }}</div>
                                <div class="text-[10px] text-[#94a3b8] font-mono">{{ $perm->slug }}</div>
                            </div>
                        </label>
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach
            <div class="flex gap-2 mt-3 pt-3 border-t border-[#f0f2ff]">
                @if($role->is_system)
                    <span class="text-[11px] text-[#94a3b8] flex items-center gap-1.5">
                        <i class="bi bi-lock-fill text-[#4154f1]"></i>
                        System role permissions are managed by the application and cannot be changed here.
                    </span>
                @else
                    <x-ui.button type="button" variant="ghost" size="sm" onclick="checkAll(this.closest('form'), true)">
                        <x-slot:icon><i class="bi bi-check-all"></i></x-slot:icon>
                        {{ __('app.select_all') }}
                    </x-ui.button>
                    <x-ui.button type="button" variant="ghost" size="sm" onclick="checkAll(this.closest('form'), false)">
                        <x-slot:icon><i class="bi bi-square"></i></x-slot:icon>
                        {{ __('app.clear_all') }}
                    </x-ui.button>
                    <div class="ms-auto">
                        <x-ui.button type="submit" variant="primary" size="sm">
                            <x-slot:icon><i class="bi bi-save-fill"></i></x-slot:icon>
                            {{ __('app.save_permissions') }}
                        </x-ui.button>
                    </div>
                @endif
            </div>
        </form>
    </x-ui.card>
    @endforeach
    @else
    <x-ui.card>
        <x-ui.empty-state icon="bi-shield" :title="__('app.create_role_first')" :description="__('app.create_role_hint')"/>
    </x-ui.card>
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
