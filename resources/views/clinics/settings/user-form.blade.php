@extends('clinics.layout.app')
@section('title', $user ? 'Edit User' : 'New User')
@section('content')

<x-ui.page-header
    :km="$user ? 'កែប្រែអ្នកប្រើ' : 'បន្ថែមអ្នកប្រើ'"
    :title="$user ? 'Edit User Account' : 'New User Account'"
    :breadcrumbs="[
        ['label'=>__('app.home'),'url'=>route('dashboard')],
        ['label'=>__('app.settings'),'url'=>route('settings.general')],
        ['label'=>'Users','url'=>route('users.index')],
        ['label'=>$user ? 'Edit '.$user->email : 'New'],
    ]">
    <x-slot:actions>
        <x-ui.button href="{{ route('users.index') }}" variant="secondary">
            <x-slot:icon><i class="bi bi-arrow-left"></i></x-slot:icon>
            Back
        </x-ui.button>
    </x-slot:actions>
</x-ui.page-header>

@if($errors->any())
    <x-ui.alert type="error" class="mb-4">
        <ul class="list-disc list-inside text-xs space-y-0.5">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </x-ui.alert>
@endif

<form method="POST"
      action="{{ $user ? route('users.update', $user->id) : route('users.store') }}"
      novalidate>
    @csrf
    @if($user) @method('PATCH') @endif

    <div class="row g-3">

        {{-- Left column: account details --}}
        <div class="col-12 col-lg-7">
            <x-ui.card class="mb-3">
                <x-slot:header>
                    <x-ui.card-header label="Account Details" icon="bi-person-fill"/>
                </x-slot:header>
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label" style="font-size:12px;font-weight:700;color:#444">
                            Full Name <span style="color:#e74c3c">*</span>
                        </label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', $user?->name) }}"
                               placeholder="e.g. Sok Chan" required maxlength="100"/>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12 col-sm-7">
                        <label class="form-label" style="font-size:12px;font-weight:700;color:#444">
                            Email <span style="color:#e74c3c">*</span>
                        </label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                               value="{{ old('email', $user?->email) }}"
                               placeholder="user@hospital.com" required maxlength="150"
                               autocomplete="off"/>
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12 col-sm-5">
                        <label class="form-label" style="font-size:12px;font-weight:700;color:#444">Phone</label>
                        <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror"
                               value="{{ old('phone', $user?->phone) }}"
                               placeholder="+855 12 345 678" maxlength="30"/>
                        @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </x-ui.card>

            {{-- Password --}}
            <x-ui.card class="mb-3">
                <x-slot:header>
                    <x-ui.card-header label="Password" icon="bi-key-fill"/>
                </x-slot:header>
                @if($user)
                    <p class="text-xs text-[#888] mb-3">Leave blank to keep the current password.</p>
                @endif
                <div class="row g-3">
                    <div class="col-12 col-sm-6">
                        <label class="form-label" style="font-size:12px;font-weight:700;color:#444">
                            Password {{ !$user ? '<span style="color:#e74c3c">*</span>' : '' }}
                        </label>
                        <div style="position:relative">
                            <input type="password" name="password" id="password"
                                   class="form-control @error('password') is-invalid @enderror"
                                   placeholder="{{ $user ? 'New password (optional)' : 'Min 8 chars, upper+lower+digit' }}"
                                   autocomplete="new-password"
                                   {{ !$user ? 'required' : '' }}/>
                            <button type="button" onclick="togglePwd('password')"
                                    style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;color:#aaa;cursor:pointer;padding:0">
                                <i class="bi bi-eye" id="pwd-eye"></i>
                            </button>
                        </div>
                        @error('password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12 col-sm-6">
                        <label class="form-label" style="font-size:12px;font-weight:700;color:#444">
                            Confirm Password {{ !$user ? '<span style="color:#e74c3c">*</span>' : '' }}
                        </label>
                        <input type="password" name="password_confirmation" id="password_confirmation"
                               class="form-control"
                               placeholder="Repeat password"
                               autocomplete="new-password"
                               {{ !$user ? 'required' : '' }}/>
                    </div>
                </div>

                <div id="pwd-strength" class="mt-2 text-[11px] text-[#94a3b8]" style="display:none">
                    <i class="bi bi-info-circle"></i>
                    Password must be at least 8 characters with uppercase, lowercase, and a number.
                </div>
            </x-ui.card>
        </div>

        {{-- Right column: role & status --}}
        <div class="col-12 col-lg-5">
            <x-ui.card class="mb-3">
                <x-slot:header>
                    <x-ui.card-header label="Role & Status" icon="bi-shield-fill"/>
                </x-slot:header>

                <div class="mb-3">
                    <label class="form-label" style="font-size:12px;font-weight:700;color:#444">
                        Role <span style="color:#e74c3c">*</span>
                    </label>
                    <select name="role_id" class="form-select @error('role_id') is-invalid @enderror" required>
                        <option value="">— Select Role —</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}"
                                {{ old('role_id', $user?->roles->first()?->id) == $role->id ? 'selected' : '' }}>
                                {{ $role->name }}
                                @if($role->is_system) (system) @endif
                            </option>
                        @endforeach
                    </select>
                    @error('role_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label class="flex items-center gap-2.5 cursor-pointer p-3 rounded-lg border border-[#e6eaf5] bg-[#fafbff] hover:bg-[#f0f4ff] transition-colors">
                        <input type="hidden" name="is_active" value="0"/>
                        <input type="checkbox" name="is_active" value="1"
                               class="w-4 h-4 accent-[#4154f1] flex-shrink-0"
                               {{ old('is_active', $user === null || $user->is_active) ? 'checked' : '' }}/>
                        <div>
                            <div class="text-sm font-bold text-[#012970]">Active Account</div>
                            <div class="text-[11px] text-[#94a3b8]">User can log in to the system</div>
                        </div>
                    </label>
                </div>
            </x-ui.card>

            {{-- Employee linkage --}}
            <x-ui.card class="mb-3">
                <x-slot:header>
                    <x-ui.card-header label="Linked Employee" icon="bi-person-badge-fill">
                        <x-slot:actions>
                            <span class="text-[10.5px] text-[#94a3b8]">optional</span>
                        </x-slot:actions>
                    </x-ui.card-header>
                </x-slot:header>
                <select name="employee_id" class="form-select">
                    <option value="">— No employee link —</option>
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}"
                            {{ old('employee_id', $user?->employee_id) == $emp->id ? 'selected' : '' }}>
                            {{ $emp->surname }}, {{ $emp->name }}
                            ({{ ucfirst(str_replace('_',' ',$emp->employee_type)) }})
                        </option>
                    @endforeach
                </select>
                <div class="text-[11px] text-[#94a3b8] mt-1.5">
                    Links this user account to an employee record. Only active, unlinked employees are shown.
                </div>
            </x-ui.card>

            {{-- Submit --}}
            <div class="flex flex-col gap-2">
                <x-ui.button type="submit" variant="primary" :fullWidth="true">
                    <x-slot:icon><i class="bi bi-save-fill"></i></x-slot:icon>
                    {{ $user ? 'Update User' : 'Create User' }}
                </x-ui.button>
                <x-ui.button href="{{ route('users.index') }}" variant="secondary" :fullWidth="true">
                    <x-slot:icon><i class="bi bi-x-circle"></i></x-slot:icon>
                    Cancel
                </x-ui.button>
            </div>
        </div>

    </div>{{-- /row --}}
</form>

@push('scripts')
<script>
function togglePwd(id) {
    const inp = document.getElementById(id);
    const eye = document.getElementById('pwd-eye');
    if (!inp) return;
    inp.type = inp.type === 'password' ? 'text' : 'password';
    if (eye) eye.className = inp.type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash';
}

document.getElementById('password')?.addEventListener('input', function() {
    const hint = document.getElementById('pwd-strength');
    if (hint) hint.style.display = this.value.length > 0 ? 'block' : 'none';
});
</script>
@endpush

@endsection
