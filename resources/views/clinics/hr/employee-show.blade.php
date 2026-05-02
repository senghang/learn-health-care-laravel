@extends('clinics.layout.app')
@section('title', $employee->surname . ' ' . $employee->name)
@section('content')

@php
    $statusMap = [
        'active'     => ['#2eca6a','#e8f8ef','Active'],
        'inactive'   => ['#aaa','#f5f5f5','Inactive'],
        'on_leave'   => ['#ff771d','#fff3e8','On Leave'],
        'terminated' => ['#e74c3c','#fde8e8','Terminated'],
    ];
    [$sc,$sb,$sl] = $statusMap[$employee->status ?? 'inactive'] ?? ['#aaa','#f5f5f5',ucfirst($employee->status)];

    $typeColors = [
        'doctor'     => '#e91e8c',
        'nurse'      => '#2eca6a',
        'pharmacist' => '#9b59b6',
        'lab_tech'   => '#00bcd4',
        'admin'      => '#4154f1',
    ];
    $typeColor = $typeColors[$employee->employee_type] ?? '#aaa';
@endphp

<x-page-header
    :title="$employee->surname . ', ' . $employee->name"
    subtitle="Employee Profile"
    :breadcrumbs="[
        ['label'=>__('app.home'),'url'=>route('dashboard')],
        ['label'=>'Employees','url'=>route('employees.index')],
        ['label'=>$employee->code],
    ]">
    <span style="background:{{ $sb }};color:{{ $sc }};padding:4px 14px;border-radius:20px;font-size:12px;font-weight:700;border:1px solid {{ $sc }}44">{{ $sl }}</span>
    <a href="{{ route('employees.edit', $employee->id) }}" class="btn btn-outline-warning btn-sm">
        <i class="bi bi-pencil-fill"></i> Edit
    </a>
</x-page-header>

@if(session('flash'))
    <div class="note note-success mb-3"><i class="bi bi-check-circle-fill"></i> {{ session('flash') }}</div>
@endif

<div class="row g-3">

{{-- Profile Card --}}
<div class="col-12 col-lg-4">
    <div class="card-emr mb-3">
        <div class="card-hd" style="background:#f6f9ff">
            <div class="card-hd-title"><i class="bi bi-person-circle" style="color:#4154f1"></i> Profile</div>
            <code style="font-size:11px;color:#4154f1;background:#eef0fd;padding:2px 8px;border-radius:6px">{{ $employee->code }}</code>
        </div>
        <div class="card-bd" style="text-align:center;padding-top:20px">
            {{-- Avatar placeholder --}}
            <div style="width:80px;height:80px;border-radius:50%;background:#eef0fd;color:#4154f1;font-size:28px;font-weight:700;display:flex;align-items:center;justify-content:center;margin:0 auto 12px">
                {{ strtoupper(substr($employee->surname,0,1).substr($employee->name,0,1)) }}
            </div>
            <div style="font-size:18px;font-weight:800;color:#012970">{{ $employee->surname }}, {{ $employee->name }}</div>
            @if($employee->name_kh)
                <div style="font-size:13px;color:#aaa">{{ $employee->name_kh }}</div>
            @endif
            <div style="margin-top:8px">
                <span style="font-size:11px;padding:3px 12px;border-radius:12px;font-weight:700;background:{{ $typeColor }}22;color:{{ $typeColor }}">
                    {{ ucfirst(str_replace('_',' ',$employee->employee_type)) }}
                </span>
            </div>
            @if($employee->specialization)
                <div style="font-size:12px;color:#888;margin-top:6px;font-style:italic">{{ $employee->specialization }}</div>
            @endif
        </div>
    </div>

    {{-- Contact --}}
    <div class="card-emr mb-3">
        <div class="card-hd"><div class="card-hd-title"><i class="bi bi-telephone-fill" style="color:#2eca6a"></i> Contact</div></div>
        <div class="card-bd">
            @foreach([
                ['bi-telephone-fill','#2eca6a', $employee->phone ?? '—'],
                ['bi-envelope-fill', '#4154f1', $employee->email ?? '—'],
            ] as [$ico,$col,$val])
            <div style="display:flex;align-items:center;gap:10px;padding:6px 0;border-bottom:1px solid #f5f6ff">
                <i class="bi {{ $ico }}" style="color:{{ $col }};font-size:14px;width:18px;text-align:center"></i>
                <span style="font-size:13px;color:#012970">{{ $val }}</span>
            </div>
            @endforeach
        </div>
    </div>

    {{-- User Account --}}
    <div class="card-emr">
        <div class="card-hd"><div class="card-hd-title"><i class="bi bi-person-lock-fill" style="color:#9b59b6"></i> System Account</div></div>
        <div class="card-bd">
            @if($employee->user)
            <div style="padding:10px;background:#f5eeff;border-radius:8px">
                <div style="font-weight:700;color:#9b59b6;font-size:13px">{{ $employee->user->name }}</div>
                <div style="font-size:11px;color:#aaa">{{ $employee->user->email }}</div>
                <div style="font-size:10.5px;margin-top:6px">
                    <span style="background:{{ $employee->user->is_active?'#e8f8ef':'#fde8e8' }};color:{{ $employee->user->is_active?'#2eca6a':'#e74c3c' }};padding:2px 8px;border-radius:8px;font-weight:700">
                        {{ $employee->user->is_active ? 'Active' : 'Inactive' }}
                    </span>
                    @if($employee->user->role)
                        <span style="background:#eef0fd;color:#4154f1;padding:2px 8px;border-radius:8px;font-weight:700;margin-left:4px">{{ $employee->user->role->name }}</span>
                    @endif
                </div>
            </div>
            @else
            <div style="text-align:center;padding:12px;color:#aaa;font-size:12px">
                <i class="bi bi-person-x" style="font-size:24px;display:block;margin-bottom:6px;opacity:.4"></i>
                No system account linked.
                <div style="margin-top:8px">
                    <a href="{{ route('users.create') }}?employee_id={{ $employee->id }}" class="btn btn-outline-primary btn-sm" style="font-size:11px">
                        <i class="bi bi-person-plus-fill"></i> Create Account
                    </a>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

{{-- Details --}}
<div class="col-12 col-lg-8">

    {{-- Employment Info --}}
    <div class="card-emr mb-3">
        <div class="card-hd" style="background:#f0fcff">
            <div class="card-hd-title"><i class="bi bi-briefcase-fill" style="color:#00bcd4"></i> Employment</div>
        </div>
        <div class="card-bd">
            <div class="row g-3">
                @foreach([
                    ['Employee Code',  $employee->code],
                    ['Type',           ucfirst(str_replace('_',' ',$employee->employee_type ?? '—'))],
                    ['Department',     $employee->department?->name ?? '—'],
                    ['Specialization', $employee->specialization ?? '—'],
                    ['License No.',    $employee->license_number ?? '—'],
                    ['Hire Date',      $employee->hire_date?->format('d/m/Y') ?? '—'],
                    ['End Date',       $employee->end_date?->format('d/m/Y') ?? '—'],
                    ['Status',         $sl],
                ] as [$label, $val])
                <div class="col-6 col-md-3">
                    <div style="font-size:10.5px;color:#aaa;font-weight:700;text-transform:uppercase;letter-spacing:.4px;margin-bottom:3px">{{ $label }}</div>
                    <div style="font-weight:600;color:#012970;font-size:13px">{{ $val }}</div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Personal Info --}}
    <div class="card-emr mb-3">
        <div class="card-hd">
            <div class="card-hd-title"><i class="bi bi-person-vcard-fill" style="color:#4154f1"></i> Personal</div>
        </div>
        <div class="card-bd">
            <div class="row g-3">
                @foreach([
                    ['Gender',    $employee->gender === 'M' ? 'Male' : ($employee->gender === 'F' ? 'Female' : '—')],
                    ['Birthdate', $employee->birthdate?->format('d/m/Y') ?? '—'],
                    ['Phone',     $employee->phone ?? '—'],
                    ['Email',     $employee->email ?? '—'],
                ] as [$label, $val])
                <div class="col-6 col-sm-3">
                    <div style="font-size:10.5px;color:#aaa;font-weight:700;text-transform:uppercase;letter-spacing:.4px;margin-bottom:3px">{{ $label }}</div>
                    <div style="font-weight:600;color:#012970;font-size:13px">{{ $val }}</div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Actions --}}
    <div class="card-emr">
        <div class="card-hd"><div class="card-hd-title"><i class="bi bi-gear-fill" style="color:#555"></i> Actions</div></div>
        <div class="card-bd">
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('employees.edit', $employee->id) }}" class="btn btn-warning btn-sm">
                    <i class="bi bi-pencil-fill"></i> Edit
                </a>
                <a href="{{ route('employees.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left"></i> Back to List
                </a>
                @if(!$employee->user)
                <a href="{{ route('users.create') }}?employee_id={{ $employee->id }}" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-person-plus-fill"></i> Create User Account
                </a>
                @endif
                <form method="POST" action="{{ route('employees.destroy', $employee->id) }}"
                      data-confirm="Permanently delete {{ $employee->surname }}, {{ $employee->name }}? This cannot be undone."
                      data-confirm-type="danger" data-confirm-title="Delete Employee">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger btn-sm">
                        <i class="bi bi-trash3-fill"></i> Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

</div>
@endsection
