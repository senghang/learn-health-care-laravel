@extends('clinics.layout.app')
@section('title', 'Employees')
@section('content')

<x-page-header
    title="បុគ្គលិក"
    subtitle="Employees"
    icon="bi-people-fill"
    :breadcrumbs="[['label'=>__('app.home'),'url'=>route('dashboard')],['label'=>'Employees']]">
    <a href="{{ route('employees.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-person-plus-fill"></i> New Employee
    </a>
</x-page-header>

@if(session('flash'))
    <div class="note note-success mb-3"><i class="bi bi-check-circle-fill"></i> {{ session('flash') }}</div>
@endif

{{-- KPI --}}
@php
    $total  = $employees->total();
    $page   = $employees->getCollection();
@endphp
<div class="row g-3 mb-3">
    <div class="col-6 col-xl-3">
        <x-stat-card :value="$total" icon="bi-people-fill" color="#4154f1" bg="#eef0fd" km="បុគ្គលិករួម" en="Total"/>
    </div>
    <div class="col-6 col-xl-3">
        <x-stat-card :value="$page->where('employee_type','doctor')->count()" icon="bi-heart-pulse-fill" color="#e91e8c" bg="#fde8f5" km="វេជ្ជបណ្ឌិត" en="Doctors"/>
    </div>
    <div class="col-6 col-xl-3">
        <x-stat-card :value="$page->where('employee_type','nurse')->count()" icon="bi-bandaid-fill" color="#2eca6a" bg="#e8f8ef" km="គិលានុបដ្ថាក" en="Nurses"/>
    </div>
    <div class="col-6 col-xl-3">
        <x-stat-card :value="$page->where('status','active')->count()" icon="bi-circle-fill" color="#2eca6a" bg="#e8f8ef" km="សកម្ម" en="Active"/>
    </div>
</div>

{{-- Filters --}}
<div class="card-emr mb-3">
    <div class="card-bd">
        <form method="GET" action="{{ route('employees.index') }}">
            <div class="row g-2 align-items-end">
                <div class="col-12 col-sm-4">
                    <input type="text" name="search" class="form-control"
                           value="{{ request('search') }}"
                           placeholder="Name, code, phone…" autofocus/>
                </div>
                <div class="col-6 col-sm-2">
                    <select name="type" class="form-select">
                        <option value="">All Types</option>
                        @foreach(['doctor'=>'Doctor','nurse'=>'Nurse','admin'=>'Admin','pharmacist'=>'Pharmacist','lab_tech'=>'Lab Tech','other'=>'Other'] as $v=>$l)
                            <option value="{{ $v }}" {{ request('type')===$v?'selected':'' }}>{{ $l }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-sm-2">
                    <select name="department" class="form-select">
                        <option value="">All Departments</option>
                        @foreach($departments as $dep)
                            <option value="{{ $dep->id }}" {{ request('department')==$dep->id?'selected':'' }}>{{ $dep->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-sm-2">
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        @foreach(['active'=>'Active','inactive'=>'Inactive','on_leave'=>'On Leave','terminated'=>'Terminated'] as $v=>$l)
                            <option value="{{ $v }}" {{ request('status')===$v?'selected':'' }}>{{ $l }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-funnel-fill"></i></button>
                    @if(request()->hasAny(['search','type','department','status']))
                        <a href="{{ route('employees.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-x-circle"></i></a>
                    @endif
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Table --}}
<div class="card-emr">
    <div class="card-hd">
        <div class="card-hd-title"><i class="bi bi-people-fill" style="color:#4154f1"></i> Employee List</div>
        <span style="font-size:11px;color:#aaa">{{ $employees->total() }} records</span>
    </div>
    <div class="card-bd" style="padding:0">
        <div class="table-responsive">
            <table class="tbl">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Type / Dept</th>
                        <th>Contact</th>
                        <th>Hire Date</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                @forelse($employees as $emp)
                @php
                    $statusMap = [
                        'active'     => ['#2eca6a','#e8f8ef','Active'],
                        'inactive'   => ['#aaa','#f5f5f5','Inactive'],
                        'on_leave'   => ['#ff771d','#fff3e8','On Leave'],
                        'terminated' => ['#e74c3c','#fde8e8','Terminated'],
                    ];
                    [$sc,$sb,$sl] = $statusMap[$emp->status ?? 'inactive'] ?? ['#aaa','#f5f5f5',$emp->status];

                    $typeColors = [
                        'doctor'     => '#e91e8c',
                        'nurse'      => '#2eca6a',
                        'pharmacist' => '#9b59b6',
                        'lab_tech'   => '#00bcd4',
                        'admin'      => '#4154f1',
                    ];
                    $typeColor = $typeColors[$emp->employee_type] ?? '#aaa';
                @endphp
                <tr onclick="location.href='{{ route('employees.show', $emp->id) }}'" style="cursor:pointer">
                    <td><code style="color:#4154f1;font-size:11px">{{ $emp->code }}</code></td>
                    <td>
                        <div style="font-weight:700;color:#012970">{{ $emp->surname }}, {{ $emp->name }}</div>
                        @if($emp->name_kh)<div style="font-size:10.5px;color:#aaa">{{ $emp->name_kh }}</div>@endif
                        @if($emp->specialization)<div style="font-size:10.5px;color:#888;font-style:italic">{{ $emp->specialization }}</div>@endif
                    </td>
                    <td>
                        <span style="font-size:10.5px;padding:2px 8px;border-radius:8px;font-weight:700;background:{{ $typeColor }}22;color:{{ $typeColor }}">
                            {{ ucfirst(str_replace('_',' ',$emp->employee_type)) }}
                        </span>
                        @if($emp->department)
                            <div style="font-size:10.5px;color:#aaa;margin-top:3px">{{ $emp->department->name }}</div>
                        @endif
                    </td>
                    <td>
                        @if($emp->phone)<div style="font-size:12px;color:#555"><i class="bi bi-telephone-fill" style="font-size:10px;color:#aaa"></i> {{ $emp->phone }}</div>@endif
                        @if($emp->email)<div style="font-size:11px;color:#aaa">{{ $emp->email }}</div>@endif
                    </td>
                    <td style="font-size:12px;color:#555">{{ $emp->hire_date?->format('d/m/Y') ?? '—' }}</td>
                    <td>
                        <span style="font-size:11px;padding:3px 10px;border-radius:8px;font-weight:700;background:{{ $sb }};color:{{ $sc }}">
                            {{ $sl }}
                        </span>
                    </td>
                    <td onclick="event.stopPropagation()">
                        <div class="d-flex gap-1">
                            <a href="{{ route('employees.show', $emp->id) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a>
                            <a href="{{ route('employees.edit', $emp->id) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" style="text-align:center;padding:36px;color:#bbb">
                    <div style="font-size:36px;margin-bottom:10px;opacity:.3">👥</div>
                    No employees found
                    <div><a href="{{ route('employees.create') }}" class="btn btn-primary btn-sm mt-2"><i class="bi bi-person-plus-fill"></i> Add Employee</a></div>
                </td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@if($employees->hasPages())
    <div class="mt-3">{{ $employees->links() }}</div>
@endif

@endsection
