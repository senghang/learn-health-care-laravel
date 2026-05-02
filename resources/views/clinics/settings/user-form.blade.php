@extends('clinics.layout.app')
@section('title', $user ? 'Edit User' : 'New User')
@section('content')

<x-page-header
    :title="$user ? 'កែប្រែអ្នកប្រើ' : 'បន្ថែមអ្នកប្រើ'"
    :subtitle="$user ? 'Edit User Account' : 'New User Account'"
    :breadcrumbs="[
        ['label'=>__('app.home'),'url'=>route('dashboard')],
        ['label'=>__('app.settings'),'url'=>route('settings.general')],
        ['label'=>'Users','url'=>route('users.index')],
        ['label'=>$user ? 'Edit '.$user->email : 'New'],
    ]">
    <a href="{{ route('users.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Back
    </a>
</x-page-header>

@if($errors->any())
<div class="note note-danger mb-3">
    <i class="bi bi-exclamation-triangle-fill"></i>
    <ul style="margin:0;padding-left:16px;font-size:12px">
        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
    </ul>
</div>
@endif

<form method="POST"
      action="{{ $user ? route('users.update', $user->id) : route('users.store') }}"
      novalidate>
    @csrf
    @if($user) @method('PATCH') @endif

    <div class="row g-3">

        {{-- Left column: account details --}}
        <div class="col-12 col-lg-7">
            <div class="card-emr mb-3">
                <div class="card-hd" style="background:#f6f9ff">
                    <div class="card-hd-title">
                        <i class="bi bi-person-fill" style="color:#4154f1"></i>
                        Account Details
                    </div>
                </div>
                <div class="card-bd">

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

                </div>
            </div>

            {{-- Password --}}
            <div class="card-emr mb-3">
                <div class="card-hd" style="background:#f6f9ff">
                    <div class="card-hd-title">
                        <i class="bi bi-key-fill" style="color:#ff771d"></i>
                        Password
                    </div>
                </div>
                <div class="card-bd">
                    @if($user)
                        <p style="font-size:12px;color:#888;margin-bottom:12px">
                            Leave blank to keep the current password.
                        </p>
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

                    <div id="pwd-strength" style="margin-top:8px;font-size:11px;color:#aaa;display:none">
                        <i class="bi bi-info-circle"></i>
                        Password must be at least 8 characters with uppercase, lowercase, and a number.
                    </div>
                </div>
            </div>
        </div>

        {{-- Right column: role & status --}}
        <div class="col-12 col-lg-5">
            <div class="card-emr mb-3">
                <div class="card-hd" style="background:#f6f9ff">
                    <div class="card-hd-title">
                        <i class="bi bi-shield-fill" style="color:#4154f1"></i>
                        Role & Status
                    </div>
                </div>
                <div class="card-bd">

                    <div class="mb-3">
                        <label class="form-label" style="font-size:12px;font-weight:700;color:#444">
                            Role <span style="color:#e74c3c">*</span>
                        </label>
                        <select name="role_id" class="form-select @error('role_id') is-invalid @enderror" required>
                            <option value="">— Select Role —</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}"
                                    {{ old('role_id', $user?->role_id) == $role->id ? 'selected' : '' }}>
                                    {{ $role->name }}
                                    @if($role->is_system) (system) @endif
                                </option>
                            @endforeach
                        </select>
                        @error('role_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label style="display:flex;align-items:center;gap:10px;cursor:pointer;padding:12px 14px;border-radius:10px;border:1px solid #e6eaf5;background:#fafbff">
                            <input type="hidden" name="is_active" value="0"/>
                            <input type="checkbox" name="is_active" value="1"
                                   style="width:16px;height:16px;accent-color:#4154f1;flex-shrink:0"
                                   {{ old('is_active', $user === null || $user->is_active) ? 'checked' : '' }}/>
                            <div>
                                <div style="font-size:13px;font-weight:700;color:#012970">Active Account</div>
                                <div style="font-size:11px;color:#aaa">User can log in to the system</div>
                            </div>
                        </label>
                    </div>

                </div>
            </div>

            {{-- Employee linkage --}}
            <div class="card-emr mb-3">
                <div class="card-hd" style="background:#f6f9ff">
                    <div class="card-hd-title">
                        <i class="bi bi-person-badge-fill" style="color:#2eca6a"></i>
                        Linked Employee
                        <span style="font-size:10.5px;color:#aaa;font-weight:400;margin-left:4px">optional</span>
                    </div>
                </div>
                <div class="card-bd">
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
                    <div style="font-size:11px;color:#aaa;margin-top:6px">
                        Links this user account to an employee record. Only active, unlinked employees are shown.
                    </div>
                </div>
            </div>

            {{-- Submit --}}
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save-fill"></i>
                    {{ $user ? 'Update User' : 'Create User' }}
                </button>
                <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-x-circle"></i> Cancel
                </a>
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
