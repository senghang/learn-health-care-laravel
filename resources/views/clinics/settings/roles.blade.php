@extends('clinics.layout.app')
@section('title', __('app.roles_permissions'))

@section('content')

<x-ui.page-header
    :km="__('app.roles_permissions')"
    title="Roles & Permissions"
    :breadcrumbs="[
        ['label' => __('app.home'), 'url' => route('dashboard')],
        ['label' => __('app.settings'), 'url' => route('settings.general')],
        ['label' => __('app.roles_permissions')],
    ]">
    <x-slot:actions>
        <form method="POST" action="{{ route('settings.roles.seed-permissions') }}">
            @csrf
            <x-ui.button type="submit" variant="secondary">
                <x-slot:icon><i class="bi bi-arrow-repeat" aria-hidden="true"></i></x-slot:icon>
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

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

    {{-- ── LEFT: Create + Roles List ────────────────────────── --}}
    <div class="space-y-4">

        {{-- Create role form --}}
        <x-ui.card>
            <x-slot:header>
                <div class="flex items-center gap-2 px-5 py-4" style="border-bottom:1px solid #e6e9f0">
                    <i class="bi bi-shield-plus" style="color:#4154f1;font-size:15px" aria-hidden="true"></i>
                    <span class="text-sm font-bold" style="color:#1a1f36">{{ __('app.new_role') }}</span>
                </div>
            </x-slot:header>
            <form method="POST" action="{{ route('settings.roles.store') }}" novalidate class="space-y-3">
                @csrf
                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold" style="color:#374151">
                        {{ __('app.role_name') }} <span style="color:#ef4444">*</span>
                    </label>
                    <x-forms.input name="name" placeholder="Doctor, Nurse, Receptionist…" required maxlength="80" />
                    @error('name')
                        <p class="text-xs flex items-center gap-1" style="color:#ef4444">
                            <i class="bi bi-exclamation-circle-fill"></i> {{ $message }}
                        </p>
                    @enderror
                </div>

                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold" style="color:#374151">
                        Description <span class="font-normal" style="color:#9ca3af">optional</span>
                    </label>
                    <x-forms.input name="description" placeholder="Short description of this role's access…" maxlength="255" />
                </div>

                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold" style="color:#374151">
                        Level <span class="font-normal" style="color:#9ca3af">0–100, higher = more authority</span>
                    </label>
                    <x-forms.input type="number" name="level" placeholder="e.g. 10" min="0" max="100" class="w-28" />
                </div>

                <x-ui.button type="submit" variant="primary" :fullWidth="true">
                    <x-slot:icon><i class="bi bi-plus-circle-fill" aria-hidden="true"></i></x-slot:icon>
                    {{ __('app.create_role') }}
                </x-ui.button>
            </form>
        </x-ui.card>

        {{-- Roles list --}}
        <x-ui.card :noPadding="true">
            <x-slot:header>
                <div class="flex items-center gap-2 px-5 py-4" style="border-bottom:1px solid #e6e9f0">
                    <i class="bi bi-shield-fill" style="color:#4154f1;font-size:15px" aria-hidden="true"></i>
                    <span class="text-sm font-bold" style="color:#1a1f36">{{ __('app.roles') }}</span>
                    <span class="text-xs font-semibold px-2 py-0.5 rounded-full" style="background:#f3f4f6;color:#6b7280">
                        {{ $roles->count() }}
                    </span>
                </div>
            </x-slot:header>
            @php $colors = ['#4154f1','#2eca6a','#ff771d','#e74c3c','#9b59b6','#00bcd4']; @endphp
            @forelse($roles as $role)
                @php $col = $colors[$loop->index % count($colors)]; @endphp
                <div class="flex items-center gap-2.5 px-4 py-3" style="border-bottom:1px solid #f8f9fb">
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center text-sm font-black flex-shrink-0"
                         style="background:{{ $col }}22;color:{{ $col }}">
                        @if($role->is_system)
                            <i class="bi bi-shield-lock-fill" style="font-size:14px" aria-hidden="true"></i>
                        @else
                            {{ strtoupper(substr($role->name, 0, 1)) }}
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <span class="text-sm font-bold" style="color:#1a1f36">{{ $role->name }}</span>
                            @if($role->is_system)
                                <x-ui.badge variant="primary" size="sm">SYSTEM</x-ui.badge>
                            @endif
                            @if($role->level)
                                <span class="text-xs" style="color:#6b7280">L{{ $role->level }}</span>
                            @endif
                        </div>
                        <div class="text-xs" style="color:#6b7280">
                            {{ $role->permissions->count() }} {{ __('app.permissions') }}
                            · {{ $role->users_count }} {{ __('app.users') }}
                        </div>
                        @if($role->description)
                            <div class="text-xs truncate mt-0.5" style="color:#9ca3af">{{ $role->description }}</div>
                        @endif
                    </div>
                    <div class="flex gap-1 flex-shrink-0">
                        @if($role->is_system)
                            <span class="w-7 h-7 flex items-center justify-center rounded-md text-xs opacity-30 cursor-not-allowed"
                                  style="color:#6b7280" title="System roles cannot be modified">
                                <i class="bi bi-lock-fill" aria-hidden="true"></i>
                            </span>
                        @else
                            <button type="button"
                                    class="w-7 h-7 flex items-center justify-center rounded-md text-xs transition-colors hover:bg-[#eef0fd]"
                                    style="color:#4154f1"
                                    onclick="editRole({{ $role->id }},'{{ addslashes($role->name) }}','{{ addslashes($role->description ?? '') }}',{{ $role->level ?? 'null' }})"
                                    title="{{ __('app.edit') }}">
                                <i class="bi bi-pencil" aria-hidden="true"></i>
                            </button>
                            @if($role->users_count == 0)
                                <form method="POST" action="{{ route('settings.roles.destroy', $role->id) }}"
                                      data-confirm="Delete role &quot;{{ $role->name }}&quot;? This cannot be undone."
                                      data-confirm-type="danger" data-confirm-title="Delete Role">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                            class="w-7 h-7 flex items-center justify-center rounded-md text-xs transition-colors hover:bg-[#fde8e8]"
                                            style="color:#e74c3c" title="{{ __('app.delete') }}">
                                        <i class="bi bi-trash" aria-hidden="true"></i>
                                    </button>
                                </form>
                            @else
                                <span class="w-7 h-7 flex items-center justify-center rounded-md text-xs opacity-30 cursor-not-allowed"
                                      style="color:#e74c3c" title="{{ $role->users_count }} user(s) assigned">
                                    <i class="bi bi-trash" aria-hidden="true"></i>
                                </span>
                            @endif
                        @endif
                    </div>
                </div>
            @empty
                <div class="px-5 py-10">
                    <x-ui.empty-state icon="bi-shield" :title="__('app.no_roles_yet')" />
                </div>
            @endforelse
        </x-ui.card>

    </div>{{-- /left --}}

    {{-- ── RIGHT: Permission Matrix ───────────────────────────── --}}
    <div class="lg:col-span-2 space-y-4">
        @if($roles->count() && $permissions->count())
            @foreach($roles as $role)
                <x-ui.card>
                    <x-slot:header>
                        <div class="flex items-center justify-between px-5 py-4" style="border-bottom:1px solid #e6e9f0">
                            <div class="flex items-center gap-2">
                                <i class="bi bi-{{ $role->is_system ? 'shield-lock-fill' : 'shield-fill' }}"
                                   style="color:#4154f1;font-size:15px" aria-hidden="true"></i>
                                <span class="text-sm font-bold" style="color:#1a1f36">{{ $role->name }}</span>
                                @if($role->is_system)
                                    <x-ui.badge variant="primary" size="sm">SYSTEM</x-ui.badge>
                                @endif
                            </div>
                            <span class="text-xs" style="color:#6b7280">
                                {{ $role->permissions->count() }}/{{ $permissions->flatten()->count() }} permissions
                            </span>
                        </div>
                    </x-slot:header>
                    <form method="POST" action="{{ route('settings.roles.permissions', $role->id) }}" novalidate>
                        @csrf
                        <div class="space-y-4">
                            @foreach($permissions as $group => $perms)
                                <div>
                                    <div class="text-xs font-black uppercase tracking-wide mb-2 pb-1"
                                         style="color:#4154f1;border-bottom:1px solid #e6e9f0">
                                        {{ ucfirst($group) }}
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                        @foreach($perms as $perm)
                                            <label class="flex items-center gap-2 cursor-pointer px-2.5 py-1.5 rounded-lg border transition-colors
                                                          {{ $role->is_system ? 'opacity-60 cursor-not-allowed border-[#e6eaf5] bg-[#f9fafb]' : 'border-[#e6eaf5] bg-[#f9fafb] hover:bg-[#f0f4ff]' }}">
                                                <input type="checkbox"
                                                       name="permissions[]"
                                                       value="{{ $perm->slug }}"
                                                       class="w-3.5 h-3.5 flex-shrink-0"
                                                       style="accent-color:#4154f1"
                                                       {{ $role->permissions->contains('id', $perm->id) ? 'checked' : '' }}
                                                       {{ $role->is_system ? 'disabled' : '' }}>
                                                <div>
                                                    <div class="text-xs font-semibold" style="color:#1a1f36">{{ $perm->name }}</div>
                                                    <div class="text-xs font-mono" style="color:#6b7280">{{ $perm->slug }}</div>
                                                </div>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="flex items-center gap-2 mt-4 pt-4" style="border-top:1px solid #e6e9f0">
                            @if($role->is_system)
                                <span class="text-xs flex items-center gap-1.5" style="color:#6b7280">
                                    <i class="bi bi-lock-fill" style="color:#4154f1" aria-hidden="true"></i>
                                    System role permissions are managed by the application.
                                </span>
                            @else
                                <x-ui.button type="button" variant="ghost" size="sm"
                                             onclick="checkAll(this.closest('form'), true)">
                                    <x-slot:icon><i class="bi bi-check-all" aria-hidden="true"></i></x-slot:icon>
                                    {{ __('app.select_all') }}
                                </x-ui.button>
                                <x-ui.button type="button" variant="ghost" size="sm"
                                             onclick="checkAll(this.closest('form'), false)">
                                    <x-slot:icon><i class="bi bi-square" aria-hidden="true"></i></x-slot:icon>
                                    {{ __('app.clear_all') }}
                                </x-ui.button>
                                <div class="ml-auto">
                                    <x-ui.button type="submit" variant="primary" size="sm">
                                        <x-slot:icon><i class="bi bi-save-fill" aria-hidden="true"></i></x-slot:icon>
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
                <x-ui.empty-state icon="bi-shield" :title="__('app.create_role_first')" :description="__('app.create_role_hint')" />
            </x-ui.card>
        @endif
    </div>

</div>{{-- /grid --}}

{{-- ── Edit Role Modal ───────────────────────────────────────── --}}
<x-ui.modal id="editRoleModal" title="Edit Role" km="{{ __('app.edit_role') }}">
    <form method="POST" id="editRoleForm" novalidate class="space-y-3">
        @csrf @method('PATCH')

        <div class="space-y-1.5">
            <label for="editRoleName" class="block text-xs font-semibold" style="color:#374151">
                {{ __('app.role_name') }} <span style="color:#ef4444">*</span>
            </label>
            <input type="text" id="editRoleName" name="name" required maxlength="80"
                   class="w-full text-sm rounded-lg border border-[#e2e8f0] bg-white px-3 py-2.5 text-[#374151] placeholder-[#9ca3af] focus:outline-none focus:border-[#4154f1] focus:ring-2 focus:ring-[#4154f1]/20 transition-colors" />
        </div>

        <div class="space-y-1.5">
            <label for="editRoleDescription" class="block text-xs font-semibold" style="color:#374151">
                Description <span class="font-normal" style="color:#9ca3af">optional</span>
            </label>
            <input type="text" id="editRoleDescription" name="description" maxlength="255"
                   placeholder="Short description…"
                   class="w-full text-sm rounded-lg border border-[#e2e8f0] bg-white px-3 py-2.5 text-[#374151] placeholder-[#9ca3af] focus:outline-none focus:border-[#4154f1] focus:ring-2 focus:ring-[#4154f1]/20 transition-colors" />
        </div>

        <div class="space-y-1.5">
            <label for="editRoleLevel" class="block text-xs font-semibold" style="color:#374151">
                Level <span class="font-normal" style="color:#9ca3af">0–100</span>
            </label>
            <input type="number" id="editRoleLevel" name="level" min="0" max="100"
                   class="w-28 text-sm rounded-lg border border-[#e2e8f0] bg-white px-3 py-2.5 text-[#374151] focus:outline-none focus:border-[#4154f1] focus:ring-2 focus:ring-[#4154f1]/20 transition-colors" />
        </div>
    </form>
    <x-slot:footer>
        <x-ui.button type="submit" form="editRoleForm" variant="primary">
            {{ __('app.update') }}
        </x-ui.button>
        <x-ui.button type="button" variant="secondary"
                     onclick="document.getElementById('editRoleModal').dispatchEvent(new Event('close-modal'))">
            {{ __('app.cancel') }}
        </x-ui.button>
    </x-slot:footer>
</x-ui.modal>

@push('scripts')
<script>
function editRole(id, name, description, level) {
    var form = document.getElementById('editRoleForm');
    if (!form) return;
    form.action = '/settings/roles/' + id;
    document.getElementById('editRoleName').value        = name;
    document.getElementById('editRoleDescription').value = description || '';
    document.getElementById('editRoleLevel').value       = (level !== null && level !== undefined) ? level : '';
    document.getElementById('editRoleModal').dispatchEvent(new Event('open'));
}
function checkAll(form, checked) {
    form.querySelectorAll('input[type="checkbox"]:not([disabled])').forEach(function(cb) {
        cb.checked = checked;
    });
}
</script>
@endpush

@endsection
