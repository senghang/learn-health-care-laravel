@extends('clinics.layout.app')
@section('title', 'Users')
@section('content')

<x-ui.page-header
    km="អ្នកប្រើប្រាស់"
    title="Users"
    :breadcrumbs="[
        ['label'=>__('app.home'),'url'=>route('dashboard')],
        ['label'=>__('app.settings'),'url'=>route('settings.general')],
        ['label'=>'Users'],
    ]">
    <x-slot:actions>
        <x-ui.button href="{{ route('users.create') }}" variant="primary">
            <x-slot:icon><i class="bi bi-person-plus-fill" aria-hidden="true"></i></x-slot:icon>
            <span class="hidden sm:inline">New User</span>
            <span class="sm:hidden">New</span>
        </x-ui.button>
    </x-slot:actions>
</x-ui.page-header>

@if(session('flash'))
    <x-ui.alert type="success" class="mb-4">{{ session('flash') }}</x-ui.alert>
@endif
@if(session('flash_error'))
    <x-ui.alert type="error" class="mb-4">{{ session('flash_error') }}</x-ui.alert>
@endif

{{-- KPI --}}
@php
    $totalCount  = $users->total();
    $activeCount = $users->getCollection()->where('is_active', true)->count();
@endphp
<div class="grid grid-cols-2 xl:grid-cols-4 gap-3 mb-4">
    <x-ui.stats-card km="អ្នកប្រើប្រាស់រួម" label="Total Users" :value="$totalCount" icon="bi-people-fill" color="#4154f1" bg="#eef0fd"/>
    <x-ui.stats-card km="សកម្ម" label="Active" :value="$activeCount" icon="bi-person-check-fill" color="#2eca6a" bg="#e8f8ef"/>
    <x-ui.stats-card km="អសកម្ម" label="Inactive" :value="$users->getCollection()->where('is_active',false)->count()" icon="bi-person-dash-fill" color="#e74c3c" bg="#fde8e8"/>
    <x-ui.stats-card km="តួនាទី" label="Roles" :value="$roles->count()" icon="bi-shield-fill" color="#ff771d" bg="#fff3e8"/>
</div>

{{-- Filters --}}
<x-ui.card class="mb-4" :noPadding="false">
    <form method="GET" action="{{ route('users.index') }}">
        <div class="flex flex-col sm:flex-row gap-3 flex-wrap">
            <div class="flex-1 min-w-0">
                <input type="text" name="search"
                       class="w-full text-sm rounded-lg border border-[#e2e8f0] bg-white px-3 py-2.5 text-[#374151] placeholder-[#6b7280] focus:outline-none focus:border-[#4154f1] focus:ring-2 focus:ring-[#4154f1]/20"
                       value="{{ request('search') }}" placeholder="Name, email, phone…" autofocus/>
            </div>
            <div class="sm:w-44">
                <select name="role_id" class="w-full text-sm rounded-lg border border-[#e2e8f0] bg-white px-3 py-2.5 text-[#374151] focus:outline-none focus:border-[#4154f1] focus:ring-2 focus:ring-[#4154f1]/20 appearance-none">
                    <option value="">All Roles</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->id }}" {{ request('role_id') == $role->id ? 'selected' : '' }}>{{ $role->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="sm:w-36">
                <select name="status" class="w-full text-sm rounded-lg border border-[#e2e8f0] bg-white px-3 py-2.5 text-[#374151] focus:outline-none focus:border-[#4154f1] focus:ring-2 focus:ring-[#4154f1]/20 appearance-none">
                    <option value="">All Status</option>
                    <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div class="flex gap-2">
                <x-ui.button type="submit" variant="primary">
                    <x-slot:icon><i class="bi bi-funnel-fill"></i></x-slot:icon>
                    Search
                </x-ui.button>
                @if(request()->hasAny(['search','role_id','status']))
                    <x-ui.button href="{{ route('users.index') }}" variant="secondary">
                        <x-slot:icon><i class="bi bi-x-circle"></i></x-slot:icon>
                        Clear
                    </x-ui.button>
                @endif
            </div>
        </div>
    </form>
</x-ui.card>

{{-- Table --}}
<x-ui.card>
    <x-slot:header>
        <x-ui.card-header km="គណនីអ្នកប្រើ" label="User Accounts" icon="bi-people-fill" :count="$users->total()"/>
    </x-slot:header>

    <x-ui.table>
        <x-slot:head>
            <tr>
                <x-ui.table-th>User</x-ui.table-th>
                <x-ui.table-th>Role</x-ui.table-th>
                <x-ui.table-th>Employee</x-ui.table-th>
                <x-ui.table-th>Last Login</x-ui.table-th>
                <x-ui.table-th>Status</x-ui.table-th>
                <x-ui.table-th></x-ui.table-th>
            </tr>
        </x-slot:head>
        <x-slot:body>
        @forelse($users as $user)
        @php
            $colors = ['#4154f1','#2eca6a','#ff771d','#e74c3c','#9b59b6','#00bcd4'];
            $primaryRole = $user->roles->first();
            $col = $colors[($primaryRole?->id ?? 0) % count($colors)];
        @endphp
        <tr class="hover:bg-[#f9fafb] transition-colors">
            <x-ui.table-td>
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-full flex items-center justify-center font-black text-sm flex-shrink-0"
                         style="background:{{ $col }}22;color:{{ $col }}">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                    <div>
                        <div class="font-bold text-[#1a1f36] text-sm">{{ $user->name }}</div>
                        <div class="text-[11px] text-[#6b7280]">{{ $user->email }}</div>
                        @if($user->phone)
                            <div class="text-[10.5px] text-[#6b7280]">{{ $user->phone }}</div>
                        @endif
                    </div>
                </div>
            </x-ui.table-td>
            <x-ui.table-td>
                @if($primaryRole)
                    <span class="text-[11px] px-2.5 py-0.5 rounded-lg font-bold" style="background:{{ $col }}18;color:{{ $col }}">
                        {{ $primaryRole->name }}
                    </span>
                    @if($primaryRole->is_system)
                        <div class="text-[9px] text-[#6b7280] mt-0.5">system</div>
                    @endif
                @else
                    <span class="text-[11px] text-[#cbd5e1]">— no role</span>
                @endif
            </x-ui.table-td>
            <x-ui.table-td>
                @if($user->employee)
                    <div class="text-xs text-[#555]">{{ $user->employee->surname }}, {{ $user->employee->name }}</div>
                    <div class="text-[10.5px] text-[#6b7280]">{{ ucfirst(str_replace('_',' ', $user->employee->employee_type)) }}</div>
                @else
                    <span class="text-[#ddd] text-[11px]">—</span>
                @endif
            </x-ui.table-td>
            <x-ui.table-td>
                @if($user->last_login_at)
                    <div class="text-xs text-[#555]">{{ $user->last_login_at->format('d/m/Y') }}</div>
                    <div class="text-[10.5px] text-[#6b7280]">{{ $user->last_login_at->format('H:i') }}</div>
                @else
                    <span class="text-[11px] text-[#ddd]">Never</span>
                @endif
            </x-ui.table-td>
            <x-ui.table-td>
                <x-ui.badge :variant="$user->is_active ? 'success' : 'danger'" size="sm">
                    {{ $user->is_active ? 'Active' : 'Inactive' }}
                </x-ui.badge>
            </x-ui.table-td>
            <x-ui.table-td align="right">
                <div class="flex gap-1 justify-end">
                    <x-ui.button href="{{ route('users.edit', $user->id) }}" variant="ghost" size="sm">
                        <x-slot:icon><i class="bi bi-pencil"></i></x-slot:icon>
                    </x-ui.button>
                    @if($user->id !== auth()->id())
                    <form method="POST" action="{{ route('users.destroy', $user->id) }}"
                          data-confirm="Delete user &quot;{{ $user->email }}&quot;? Their account and access will be permanently removed."
                          data-confirm-type="danger" data-confirm-title="Delete User">
                        @csrf @method('DELETE')
                        <button type="submit" class="w-7 h-7 flex items-center justify-center rounded-md text-[#e74c3c] hover:bg-[#fde8e8] transition-colors text-xs">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                    @else
                    <span class="w-7 h-7 flex items-center justify-center rounded-md text-[#e74c3c] opacity-25 text-xs cursor-not-allowed" title="Cannot delete yourself">
                        <i class="bi bi-trash"></i>
                    </span>
                    @endif
                </div>
            </x-ui.table-td>
        </tr>
        @empty
        <tr>
            <td colspan="6">
                <x-ui.empty-state icon="bi-person-circle" title="No users found" description="Create the first user account." compact>
                    <x-ui.button href="{{ route('users.create') }}" variant="primary" size="sm">
                        <x-slot:icon><i class="bi bi-person-plus-fill"></i></x-slot:icon>
                        Add User
                    </x-ui.button>
                </x-ui.empty-state>
            </td>
        </tr>
        @endforelse
        </x-slot:body>
    </x-ui.table>
</x-ui.card>

<x-ui.pagination :paginator="$users" class="mt-4"/>

@endsection
