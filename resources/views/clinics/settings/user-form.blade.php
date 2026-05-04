@extends('clinics.layout.app')
@section('title', $user ? 'Edit User' : 'New User')

@section('content')

<x-ui.page-header
    :km="$user ? 'កែប្រែអ្នកប្រើ' : 'បន្ថែមអ្នកប្រើ'"
    :title="$user ? 'Edit User Account' : 'New User Account'"
    :breadcrumbs="[
        ['label' => __('app.home'), 'url' => route('dashboard')],
        ['label' => __('app.settings'), 'url' => route('settings.general')],
        ['label' => 'Users', 'url' => route('users.index')],
        ['label' => $user ? 'Edit ' . $user->email : 'New'],
    ]">
    <x-slot:actions>
        <x-ui.button href="{{ route('users.index') }}" variant="secondary">
            <x-slot:icon><i class="bi bi-arrow-left" aria-hidden="true"></i></x-slot:icon>
            Back
        </x-ui.button>
    </x-slot:actions>
</x-ui.page-header>

@if($errors->any())
    <x-ui.alert type="error" class="mb-4">
        <ul class="list-disc pl-4 text-xs space-y-0.5">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </x-ui.alert>
@endif

<form method="POST"
      action="{{ $user ? route('users.update', $user->id) : route('users.store') }}"
      novalidate>
    @csrf
    @if($user) @method('PATCH') @endif

    <div class="grid grid-cols-1 lg:grid-cols-5 gap-5">

        {{-- ── Left: Account Details + Password ──────────────────── --}}
        <div class="lg:col-span-3 space-y-4">

            {{-- Account Details --}}
            <x-ui.card>
                <x-slot:header>
                    <x-ui.card-header label="Account Details" icon="bi-person-fill" />
                </x-slot:header>
                <div class="space-y-4">

                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold" style="color:#374151">
                            Full Name <span style="color:#ef4444">*</span>
                        </label>
                        <x-forms.input type="text" name="name" :value="old('name', $user?->name)"
                                       placeholder="e.g. Sok Chan" required maxlength="100" />
                        @error('name')
                            <p class="text-xs flex items-center gap-1" style="color:#ef4444">
                                <i class="bi bi-exclamation-circle-fill"></i> {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-5 gap-4">
                        <div class="sm:col-span-3 space-y-1.5">
                            <label class="block text-xs font-semibold" style="color:#374151">
                                Email <span style="color:#ef4444">*</span>
                            </label>
                            <x-forms.input type="email" name="email" :value="old('email', $user?->email)"
                                           placeholder="user@hospital.com" required maxlength="150"
                                           autocomplete="off" />
                            @error('email')
                                <p class="text-xs flex items-center gap-1" style="color:#ef4444">
                                    <i class="bi bi-exclamation-circle-fill"></i> {{ $message }}
                                </p>
                            @enderror
                        </div>
                        <div class="sm:col-span-2 space-y-1.5">
                            <label class="block text-xs font-semibold" style="color:#374151">Phone</label>
                            <x-forms.input type="text" name="phone" :value="old('phone', $user?->phone)"
                                           placeholder="+855 12 345 678" maxlength="30" />
                            @error('phone')
                                <p class="text-xs flex items-center gap-1" style="color:#ef4444">
                                    <i class="bi bi-exclamation-circle-fill"></i> {{ $message }}
                                </p>
                            @enderror
                        </div>
                    </div>
                </div>
            </x-ui.card>

            {{-- Password --}}
            <x-ui.card>
                <x-slot:header>
                    <x-ui.card-header label="Password" icon="bi-key-fill" />
                </x-slot:header>
                @if($user)
                    <p class="text-xs mb-3" style="color:#6b7280">Leave blank to keep the current password.</p>
                @endif
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold" style="color:#374151">
                            Password @if(!$user)<span style="color:#ef4444">*</span>@endif
                        </label>
                        <div class="relative">
                            <x-forms.input type="password" name="password" id="password"
                                           :placeholder="$user ? 'New password (optional)' : 'Min 8 chars, upper+lower+digit'"
                                           autocomplete="new-password"
                                           :required="!$user" />
                            <button type="button" onclick="togglePwd('password')"
                                    class="absolute inset-y-0 right-0 flex items-center pr-3 text-xs"
                                    style="background:none;border:none;color:#9ca3af;cursor:pointer">
                                <i class="bi bi-eye" id="pwd-eye" aria-hidden="true"></i>
                            </button>
                        </div>
                        @error('password')
                            <p class="text-xs flex items-center gap-1" style="color:#ef4444">
                                <i class="bi bi-exclamation-circle-fill"></i> {{ $message }}
                            </p>
                        @enderror
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold" style="color:#374151">
                            Confirm Password @if(!$user)<span style="color:#ef4444">*</span>@endif
                        </label>
                        <x-forms.input type="password" name="password_confirmation"
                                       placeholder="Repeat password"
                                       autocomplete="new-password"
                                       :required="!$user" />
                    </div>
                </div>
                <p id="pwd-strength" class="text-xs mt-2 flex items-center gap-1.5" style="color:#6b7280;display:none">
                    <i class="bi bi-info-circle" aria-hidden="true"></i>
                    Password must be at least 8 characters with uppercase, lowercase, and a number.
                </p>
            </x-ui.card>

        </div>

        {{-- ── Right: Role, Status, Employee, Submit ──────────────── --}}
        <div class="lg:col-span-2 space-y-4">

            {{-- Role & Status --}}
            <x-ui.card>
                <x-slot:header>
                    <x-ui.card-header label="Role & Status" icon="bi-shield-fill" />
                </x-slot:header>
                <div class="space-y-3">
                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold" style="color:#374151">
                            Role <span style="color:#ef4444">*</span>
                        </label>
                        <x-forms.select name="role_id" required>
                            <option value="">— Select Role —</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}"
                                        {{ old('role_id', $user?->roles->first()?->id) == $role->id ? 'selected' : '' }}>
                                    {{ $role->name }}
                                    @if($role->is_system) (system) @endif
                                </option>
                            @endforeach
                        </x-forms.select>
                        @error('role_id')
                            <p class="text-xs flex items-center gap-1" style="color:#ef4444">
                                <i class="bi bi-exclamation-circle-fill"></i> {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <label class="flex items-center gap-2.5 cursor-pointer p-3 rounded-lg border border-[#e6eaf5] bg-[#f9fafb] hover:bg-[#f0f4ff] transition-colors">
                        <input type="hidden" name="is_active" value="0" />
                        <input type="checkbox" name="is_active" value="1"
                               class="w-4 h-4 flex-shrink-0" style="accent-color:#4154f1"
                               {{ old('is_active', $user === null || $user->is_active) ? 'checked' : '' }} />
                        <div>
                            <div class="text-sm font-bold" style="color:#1a1f36">Active Account</div>
                            <div class="text-xs" style="color:#6b7280">User can log in to the system</div>
                        </div>
                    </label>
                </div>
            </x-ui.card>

            {{-- Employee Linkage --}}
            <x-ui.card>
                <x-slot:header>
                    <x-ui.card-header label="Linked Employee" icon="bi-person-badge-fill">
                        <x-slot:actions>
                            <span class="text-xs" style="color:#6b7280">optional</span>
                        </x-slot:actions>
                    </x-ui.card-header>
                </x-slot:header>
                <div class="space-y-2">
                    <x-forms.select name="employee_id">
                        <option value="">— No employee link —</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}"
                                    {{ old('employee_id', $user?->employee_id) == $emp->id ? 'selected' : '' }}>
                                {{ $emp->surname }}, {{ $emp->name }}
                                ({{ ucfirst(str_replace('_', ' ', $emp->employee_type)) }})
                            </option>
                        @endforeach
                    </x-forms.select>
                    <p class="text-xs" style="color:#6b7280">
                        Links this user account to an employee record. Only active, unlinked employees are shown.
                    </p>
                </div>
            </x-ui.card>

            {{-- Submit --}}
            <div class="space-y-2">
                <x-ui.button type="submit" variant="primary" :fullWidth="true">
                    <x-slot:icon><i class="bi bi-save-fill" aria-hidden="true"></i></x-slot:icon>
                    {{ $user ? 'Update User' : 'Create User' }}
                </x-ui.button>
                <x-ui.button href="{{ route('users.index') }}" variant="secondary" :fullWidth="true">
                    <x-slot:icon><i class="bi bi-x-circle" aria-hidden="true"></i></x-slot:icon>
                    Cancel
                </x-ui.button>
            </div>

        </div>

    </div>{{-- /grid --}}
</form>

@push('scripts')
<script>
function togglePwd(id) {
    var inp = document.getElementById(id);
    var eye = document.getElementById('pwd-eye');
    if (!inp) return;
    inp.type = inp.type === 'password' ? 'text' : 'password';
    if (eye) eye.className = inp.type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash';
}
document.getElementById('password')?.addEventListener('input', function() {
    var hint = document.getElementById('pwd-strength');
    if (hint) hint.style.display = this.value.length > 0 ? 'flex' : 'none';
});
</script>
@endpush

@endsection
