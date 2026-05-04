@extends('clinics.layout.app')
@section('title', $employee->surname . ' ' . $employee->name)
@section('content')

@php
    $typeColors = ['doctor'=>'#e91e8c','nurse'=>'#2eca6a','pharmacist'=>'#9b59b6','lab_tech'=>'#00bcd4','admin'=>'#4154f1'];
    $typeColor = $typeColors[$employee->employee_type] ?? '#aaa';
    $statusVariant = match($employee->status ?? 'inactive') {
        'active'     => 'success',
        'on_leave'   => 'warning',
        'terminated' => 'danger',
        default      => 'secondary',
    };
    $statusLabel = match($employee->status ?? 'inactive') {
        'active'     => 'Active',
        'on_leave'   => 'On Leave',
        'terminated' => 'Terminated',
        default      => 'Inactive',
    };
@endphp

<x-ui.page-header
    :km="$employee->surname.', '.$employee->name"
    title="Employee Profile"
    :breadcrumbs="[
        ['label'=>__('app.home'),'url'=>route('dashboard')],
        ['label'=>'Employees','url'=>route('employees.index')],
        ['label'=>$employee->code],
    ]">
    <x-slot:actions>
        <x-ui.button href="{{ route('employees.edit', $employee->id) }}" variant="warning">
            <x-slot:icon><i class="bi bi-pencil-fill"></i></x-slot:icon>
            Edit
        </x-ui.button>
    </x-slot:actions>
</x-ui.page-header>

@if(session('flash'))
    <x-ui.alert type="success" class="mb-4">{{ session('flash') }}</x-ui.alert>
@endif

<div class="row g-3">

{{-- Profile Card --}}
<div class="col-12 col-lg-4">
    <x-ui.card class="mb-3">
        <x-slot:header>
            <x-ui.card-header label="Profile" icon="bi-person-circle">
                <x-slot:actions>
                    <code class="text-[11px] text-[#4154f1] bg-[#eef0fd] px-2 py-0.5 rounded">{{ $employee->code }}</code>
                </x-slot:actions>
            </x-ui.card-header>
        </x-slot:header>
        <div class="text-center pt-2">
            <div class="w-20 h-20 rounded-full bg-[#eef0fd] text-[#4154f1] text-3xl font-bold flex items-center justify-center mx-auto mb-3">
                {{ strtoupper(substr($employee->surname,0,1).substr($employee->name,0,1)) }}
            </div>
            <div class="text-lg font-black text-[#1a1f36]">{{ $employee->surname }}, {{ $employee->name }}</div>
            @if($employee->name_kh)
                <div class="text-sm text-[#6b7280] mt-0.5">{{ $employee->name_kh }}</div>
            @endif
            <div class="mt-2">
                <span class="text-[11px] px-3 py-1 rounded-xl font-bold" style="background:{{ $typeColor }}22;color:{{ $typeColor }}">
                    {{ ucfirst(str_replace('_',' ',$employee->employee_type)) }}
                </span>
            </div>
            @if($employee->specialization)
                <div class="text-xs text-[#888] mt-1.5 italic">{{ $employee->specialization }}</div>
            @endif
        </div>
    </x-ui.card>

    {{-- Contact --}}
    <x-ui.card class="mb-3">
        <x-slot:header>
            <x-ui.card-header label="Contact" icon="bi-telephone-fill"/>
        </x-slot:header>
        @foreach([['bi-telephone-fill','#2eca6a',$employee->phone ?? '—'],['bi-envelope-fill','#4154f1',$employee->email ?? '—']] as [$ico,$col,$val])
        <div class="flex items-center gap-2.5 py-2 border-b border-[#f5f6ff]">
            <i class="bi {{ $ico }} w-5 text-center" style="color:{{ $col }};font-size:14px"></i>
            <span class="text-sm text-[#1a1f36]">{{ $val }}</span>
        </div>
        @endforeach
    </x-ui.card>

    {{-- User Account --}}
    <x-ui.card>
        <x-slot:header>
            <x-ui.card-header label="System Account" icon="bi-person-lock-fill"/>
        </x-slot:header>
        @if($employee->user)
        <div class="p-2.5 bg-[#f5eeff] rounded-lg">
            <div class="font-bold text-[#9b59b6] text-sm">{{ $employee->user->name }}</div>
            <div class="text-[11px] text-[#6b7280]">{{ $employee->user->email }}</div>
            <div class="flex gap-1.5 mt-1.5">
                <x-ui.badge :variant="$employee->user->is_active ? 'success' : 'danger'" size="sm">
                    {{ $employee->user->is_active ? 'Active' : 'Inactive' }}
                </x-ui.badge>
                @if($employee->user->roles->first())
                    <x-ui.badge variant="primary" size="sm">{{ $employee->user->roles->first()->name }}</x-ui.badge>
                @endif
            </div>
        </div>
        @else
        <div class="text-center py-3 text-[#6b7280]">
            <i class="bi bi-person-x text-2xl block mb-1.5 opacity-40"></i>
            <div class="text-xs mb-2">No system account linked.</div>
            <x-ui.button href="{{ route('users.create') }}?employee_id={{ $employee->id }}" variant="ghost" size="sm">
                <x-slot:icon><i class="bi bi-person-plus-fill"></i></x-slot:icon>
                Create Account
            </x-ui.button>
        </div>
        @endif
    </x-ui.card>
</div>

{{-- Details --}}
<div class="col-12 col-lg-8">

    {{-- Employment Info --}}
    <x-ui.card class="mb-3">
        <x-slot:header>
            <x-ui.card-header label="Employment" icon="bi-briefcase-fill"/>
        </x-slot:header>
        <div class="row g-3">
            @foreach([
                ['Employee Code',  $employee->code],
                ['Type',           ucfirst(str_replace('_',' ',$employee->employee_type ?? '—'))],
                ['Department',     $employee->department?->name ?? '—'],
                ['Specialization', $employee->specialization ?? '—'],
                ['License No.',    $employee->license_number ?? '—'],
                ['Hire Date',      $employee->hire_date?->format('d/m/Y') ?? '—'],
                ['End Date',       $employee->end_date?->format('d/m/Y') ?? '—'],
                ['Status',         $statusLabel],
            ] as [$label, $val])
            <div class="col-6 col-md-3">
                <div class="text-[10.5px] text-[#6b7280] font-bold uppercase tracking-wide mb-0.5">{{ $label }}</div>
                <div class="font-semibold text-[#1a1f36] text-sm">{{ $val }}</div>
            </div>
            @endforeach
        </div>
    </x-ui.card>

    {{-- Personal Info --}}
    <x-ui.card class="mb-3">
        <x-slot:header>
            <x-ui.card-header label="Personal" icon="bi-person-vcard-fill"/>
        </x-slot:header>
        <div class="row g-3">
            @foreach([
                ['Gender',    $employee->gender === 'M' ? 'Male' : ($employee->gender === 'F' ? 'Female' : '—')],
                ['Birthdate', $employee->birthdate?->format('d/m/Y') ?? '—'],
                ['Phone',     $employee->phone ?? '—'],
                ['Email',     $employee->email ?? '—'],
            ] as [$label, $val])
            <div class="col-6 col-sm-3">
                <div class="text-[10.5px] text-[#6b7280] font-bold uppercase tracking-wide mb-0.5">{{ $label }}</div>
                <div class="font-semibold text-[#1a1f36] text-sm">{{ $val }}</div>
            </div>
            @endforeach
        </div>
    </x-ui.card>

    {{-- Actions --}}
    <x-ui.card>
        <x-slot:header>
            <x-ui.card-header label="Actions" icon="bi-gear-fill"/>
        </x-slot:header>
        <div class="flex gap-2 flex-wrap">
            <x-ui.button href="{{ route('employees.edit', $employee->id) }}" variant="warning">
                <x-slot:icon><i class="bi bi-pencil-fill"></i></x-slot:icon>
                Edit
            </x-ui.button>
            <x-ui.button href="{{ route('employees.index') }}" variant="secondary">
                <x-slot:icon><i class="bi bi-arrow-left"></i></x-slot:icon>
                Back to List
            </x-ui.button>
            @if(!$employee->user)
            <x-ui.button href="{{ route('users.create') }}?employee_id={{ $employee->id }}" variant="ghost">
                <x-slot:icon><i class="bi bi-person-plus-fill"></i></x-slot:icon>
                Create User Account
            </x-ui.button>
            @endif
            <form method="POST" action="{{ route('employees.destroy', $employee->id) }}"
                  data-confirm="Permanently delete {{ $employee->surname }}, {{ $employee->name }}? This cannot be undone."
                  data-confirm-type="danger" data-confirm-title="Delete Employee">
                @csrf @method('DELETE')
                <x-ui.button type="submit" variant="danger">
                    <x-slot:icon><i class="bi bi-trash3-fill"></i></x-slot:icon>
                    Delete
                </x-ui.button>
            </form>
        </div>
    </x-ui.card>
</div>

</div>
@endsection
