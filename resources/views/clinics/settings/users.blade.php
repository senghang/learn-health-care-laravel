@extends('clinics.layout.app')
@section('title', 'Users')
@section('content')

<x-page-header
    title="អ្នកប្រើប្រាស់ / Users"
    subtitle="System — User Accounts"
    :breadcrumbs="[
        ['label'=>__('app.home'),'url'=>route('dashboard')],
        ['label'=>__('app.settings'),'url'=>route('settings.general')],
        ['label'=>'Users'],
    ]">
    <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-person-plus-fill"></i> New User
    </a>
</x-page-header>

@if(session('flash'))
    <div class="note note-success mb-3"><i class="bi bi-check-circle-fill"></i> {{ session('flash') }}</div>
@endif
@if(session('flash_error'))
    <div class="note note-danger mb-3"><i class="bi bi-exclamation-triangle-fill"></i> {{ session('flash_error') }}</div>
@endif

{{-- KPI --}}
@php
    $totalCount  = $users->total();
    $activeCount = $users->getCollection()->where('is_active', true)->count();
@endphp
<div class="row g-3 mb-3">
    @foreach([
        [$totalCount,  'bi-people-fill',      '#4154f1','#eef0fd', 'អ្នកប្រើប្រាស់រួម','Total Users'],
        [$activeCount, 'bi-person-check-fill', '#2eca6a','#e8f8ef', 'សកម្ម',             'Active'],
        [$users->getCollection()->where('is_active',false)->count(), 'bi-person-dash-fill','#e74c3c','#fde8e8','អសកម្ម','Inactive'],
        [$roles->count(), 'bi-shield-fill',   '#ff771d','#fff3e8', 'តួនាទី',            'Roles'],
    ] as [$val,$ico,$col,$bg,$km,$en])
    <div class="col-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:{{ $bg }};color:{{ $col }}"><i class="bi {{ $ico }}"></i></div>
            <div>
                <div class="stat-num" style="color:{{ $col }};font-size:18px">{{ $val }}</div>
                <div class="stat-lbl">{{ $km }}<br><small>{{ $en }}</small></div>
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- Filters --}}
<div class="card-emr mb-3">
    <div class="card-bd">
        <form method="GET" action="{{ route('users.index') }}">
            <div class="row g-2 align-items-end">
                <div class="col-12 col-sm-4">
                    <input type="text" name="search" class="form-control"
                           value="{{ request('search') }}"
                           placeholder="Name, email, phone…" autofocus/>
                </div>
                <div class="col-6 col-sm-3">
                    <select name="role_id" class="form-select">
                        <option value="">All Roles</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}" {{ request('role_id') == $role->id ? 'selected' : '' }}>
                                {{ $role->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-sm-2">
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-auto d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-funnel-fill"></i></button>
                    @if(request()->hasAny(['search','role_id','status']))
                        <a href="{{ route('users.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-x-circle"></i>
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Table --}}
<div class="card-emr">
    <div class="card-hd">
        <div class="card-hd-title"><i class="bi bi-people-fill" style="color:#4154f1"></i> User Accounts</div>
        <span style="font-size:11px;color:#aaa">{{ $users->total() }} records</span>
    </div>
    <div class="card-bd" style="padding:0">
        <div class="table-responsive">
            <table class="tbl">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Role</th>
                        <th>Employee</th>
                        <th>Last Login</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                @forelse($users as $user)
                @php
                    $colors = ['#4154f1','#2eca6a','#ff771d','#e74c3c','#9b59b6','#00bcd4'];
                    $col = $colors[($user->role_id ?? 0) % count($colors)];
                @endphp
                <tr>
                    <td>
                        <div style="display:flex;align-items:center;gap:10px">
                            <div style="width:34px;height:34px;border-radius:50%;background:{{ $col }}22;color:{{ $col }};display:flex;align-items:center;justify-content:center;font-weight:800;font-size:13px;flex-shrink:0">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                            <div>
                                <div style="font-weight:700;color:#012970;font-size:13px">{{ $user->name }}</div>
                                <div style="font-size:11px;color:#aaa">{{ $user->email }}</div>
                                @if($user->phone)
                                    <div style="font-size:10.5px;color:#bbb">{{ $user->phone }}</div>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td>
                        @if($user->role)
                            <span style="font-size:11px;padding:3px 10px;border-radius:8px;font-weight:700;background:{{ $col }}18;color:{{ $col }}">
                                {{ $user->role->name }}
                            </span>
                            @if($user->role->is_system)
                                <span style="font-size:9px;color:#aaa;display:block;margin-top:2px">system</span>
                            @endif
                        @else
                            <span style="color:#ccc;font-size:11px">— no role</span>
                        @endif
                    </td>
                    <td>
                        @if($user->employee)
                            <div style="font-size:12px;color:#555">
                                {{ $user->employee->surname }}, {{ $user->employee->name }}
                            </div>
                            <div style="font-size:10.5px;color:#aaa">
                                {{ ucfirst(str_replace('_',' ', $user->employee->employee_type)) }}
                            </div>
                        @else
                            <span style="color:#ddd;font-size:11px">—</span>
                        @endif
                    </td>
                    <td>
                        @if($user->last_login_at)
                            <div style="font-size:12px;color:#555">{{ $user->last_login_at->format('d/m/Y') }}</div>
                            <div style="font-size:10.5px;color:#aaa">{{ $user->last_login_at->format('H:i') }}</div>
                        @else
                            <span style="color:#ddd;font-size:11px">Never</span>
                        @endif
                    </td>
                    <td>
                        @if($user->is_active)
                            <span style="font-size:11px;padding:3px 10px;border-radius:8px;font-weight:700;background:#e8f8ef;color:#2eca6a">Active</span>
                        @else
                            <span style="font-size:11px;padding:3px 10px;border-radius:8px;font-weight:700;background:#fde8e8;color:#e74c3c">Inactive</span>
                        @endif
                    </td>
                    <td onclick="event.stopPropagation()">
                        <div class="d-flex gap-1">
                            <a href="{{ route('users.edit', $user->id) }}"
                               class="btn btn-sm btn-outline-secondary"
                               title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            @if($user->id !== auth()->id())
                            <form method="POST" action="{{ route('users.destroy', $user->id) }}"
                                  data-confirm="Delete user &quot;{{ $user->email }}&quot;? Their account and access will be permanently removed."
                                  data-confirm-type="danger" data-confirm-title="Delete User">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                            @else
                            <span class="btn btn-sm btn-outline-danger"
                                  style="opacity:.25;pointer-events:none" title="Cannot delete yourself">
                                <i class="bi bi-trash"></i>
                            </span>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align:center;padding:40px;color:#bbb">
                        <div style="font-size:36px;margin-bottom:10px;opacity:.3">👤</div>
                        No users found
                        <div>
                            <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm mt-2">
                                <i class="bi bi-person-plus-fill"></i> Add User
                            </a>
                        </div>
                    </td>
                </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@if($users->hasPages())
    <div class="mt-3">{{ $users->links() }}</div>
@endif

@endsection
