@extends('clinics.layout.app')
@section('title', 'Employees')
@section('content')

<x-ui.page-header
    km="បុគ្គលិក"
    title="Employees"
    :breadcrumbs="[['label'=>__('app.home'),'url'=>route('dashboard')],['label'=>'Employees']]">
    <x-slot:actions>
        <x-ui.button href="{{ route('employees.create') }}" variant="primary">
            <x-slot:icon><i class="bi bi-person-plus-fill" aria-hidden="true"></i></x-slot:icon>
            <span class="hidden sm:inline">New Employee</span>
            <span class="sm:hidden">New</span>
        </x-ui.button>
    </x-slot:actions>
</x-ui.page-header>

@if(session('flash'))
    <x-ui.alert type="success" class="mb-4">{{ session('flash') }}</x-ui.alert>
@endif

{{-- KPI --}}
@php
    $total = $employees->total();
    $page  = $employees->getCollection();
@endphp
<div class="grid grid-cols-2 xl:grid-cols-4 gap-3 mb-4">
    <x-ui.stats-card km="បុគ្គលិករួម" label="Total" :value="$total" icon="bi-people-fill" color="#4154f1" bg="#eef0fd"/>
    <x-ui.stats-card km="វេជ្ជបណ្ឌិត" label="Doctors" :value="$page->where('employee_type','doctor')->count()" icon="bi-heart-pulse-fill" color="#e91e8c" bg="#fde8f5"/>
    <x-ui.stats-card km="គិលានុបដ្ថាក" label="Nurses" :value="$page->where('employee_type','nurse')->count()" icon="bi-bandaid-fill" color="#2eca6a" bg="#e8f8ef"/>
    <x-ui.stats-card km="សកម្ម" label="Active" :value="$page->where('status','active')->count()" icon="bi-circle-fill" color="#2eca6a" bg="#e8f8ef"/>
</div>

{{-- Filters --}}
<x-ui.card class="mb-4" :noPadding="false">
    <form method="GET" action="{{ route('employees.index') }}">
        <div class="flex flex-col sm:flex-row gap-3 flex-wrap">
            <div class="flex-1 min-w-0">
                <input type="text" name="search" class="w-full text-sm rounded-lg border border-[#e2e8f0] bg-white px-3 py-2.5 text-[#374151] placeholder-[#94a3b8] focus:outline-none focus:border-[#4154f1] focus:ring-2 focus:ring-[#4154f1]/20"
                       value="{{ request('search') }}" placeholder="Name, code, phone…" autofocus/>
            </div>
            <div class="sm:w-40">
                <select name="type" class="w-full text-sm rounded-lg border border-[#e2e8f0] bg-white px-3 py-2.5 text-[#374151] focus:outline-none focus:border-[#4154f1] focus:ring-2 focus:ring-[#4154f1]/20 appearance-none">
                    <option value="">All Types</option>
                    @foreach(['doctor'=>'Doctor','nurse'=>'Nurse','admin'=>'Admin','pharmacist'=>'Pharmacist','lab_tech'=>'Lab Tech','other'=>'Other'] as $v=>$l)
                        <option value="{{ $v }}" {{ request('type')===$v?'selected':'' }}>{{ $l }}</option>
                    @endforeach
                </select>
            </div>
            <div class="sm:w-44">
                <select name="department" class="w-full text-sm rounded-lg border border-[#e2e8f0] bg-white px-3 py-2.5 text-[#374151] focus:outline-none focus:border-[#4154f1] focus:ring-2 focus:ring-[#4154f1]/20 appearance-none">
                    <option value="">All Departments</option>
                    @foreach($departments as $dep)
                        <option value="{{ $dep->id }}" {{ request('department')==$dep->id?'selected':'' }}>{{ $dep->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="sm:w-36">
                <select name="status" class="w-full text-sm rounded-lg border border-[#e2e8f0] bg-white px-3 py-2.5 text-[#374151] focus:outline-none focus:border-[#4154f1] focus:ring-2 focus:ring-[#4154f1]/20 appearance-none">
                    <option value="">All Status</option>
                    @foreach(['active'=>'Active','inactive'=>'Inactive','on_leave'=>'On Leave','terminated'=>'Terminated'] as $v=>$l)
                        <option value="{{ $v }}" {{ request('status')===$v?'selected':'' }}>{{ $l }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2">
                <x-ui.button type="submit" variant="primary">
                    <x-slot:icon><i class="bi bi-funnel-fill"></i></x-slot:icon>
                    Search
                </x-ui.button>
                @if(request()->hasAny(['search','type','department','status']))
                    <x-ui.button href="{{ route('employees.index') }}" variant="secondary">
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
        <x-ui.card-header km="បញ្ជីបុគ្គលិក" label="Employee List" icon="bi-people-fill" :count="$employees->total()"/>
    </x-slot:header>

    <x-ui.table>
        <x-slot:head>
            <tr>
                <x-ui.table-th>Code</x-ui.table-th>
                <x-ui.table-th>Name</x-ui.table-th>
                <x-ui.table-th>Type / Dept</x-ui.table-th>
                <x-ui.table-th>Contact</x-ui.table-th>
                <x-ui.table-th>Hire Date</x-ui.table-th>
                <x-ui.table-th>Status</x-ui.table-th>
                <x-ui.table-th></x-ui.table-th>
            </tr>
        </x-slot:head>
        <x-slot:body>
        @forelse($employees as $emp)
        @php
            $typeColors = ['doctor'=>'#e91e8c','nurse'=>'#2eca6a','pharmacist'=>'#9b59b6','lab_tech'=>'#00bcd4','admin'=>'#4154f1'];
            $typeColor = $typeColors[$emp->employee_type] ?? '#aaa';
        @endphp
        <tr onclick="location.href='{{ route('employees.show', $emp->id) }}'" class="cursor-pointer hover:bg-[#fafbff] transition-colors">
            <x-ui.table-td>
                <code class="text-[#4154f1] text-[11px]">{{ $emp->code }}</code>
            </x-ui.table-td>
            <x-ui.table-td>
                <div class="font-bold text-[#012970]">{{ $emp->surname }}, {{ $emp->name }}</div>
                @if($emp->name_kh)<div class="text-[10.5px] text-[#94a3b8]">{{ $emp->name_kh }}</div>@endif
                @if($emp->specialization)<div class="text-[10.5px] text-[#888] italic">{{ $emp->specialization }}</div>@endif
            </x-ui.table-td>
            <x-ui.table-td>
                <span class="text-[10.5px] px-2 py-0.5 rounded-lg font-bold" style="background:{{ $typeColor }}22;color:{{ $typeColor }}">
                    {{ ucfirst(str_replace('_',' ',$emp->employee_type)) }}
                </span>
                @if($emp->department)
                    <div class="text-[10.5px] text-[#94a3b8] mt-0.5">{{ $emp->department->name }}</div>
                @endif
            </x-ui.table-td>
            <x-ui.table-td>
                @if($emp->phone)<div class="text-xs text-[#555]"><i class="bi bi-telephone-fill text-[10px] text-[#94a3b8]"></i> {{ $emp->phone }}</div>@endif
                @if($emp->email)<div class="text-[11px] text-[#94a3b8]">{{ $emp->email }}</div>@endif
            </x-ui.table-td>
            <x-ui.table-td>
                <span class="text-xs text-[#555]">{{ $emp->hire_date?->format('d/m/Y') ?? '—' }}</span>
            </x-ui.table-td>
            <x-ui.table-td>
                @php
                    $statusVariant = match($emp->status ?? 'inactive') {
                        'active'     => 'success',
                        'on_leave'   => 'warning',
                        'terminated' => 'danger',
                        default      => 'secondary',
                    };
                    $statusLabel = match($emp->status ?? 'inactive') {
                        'active'     => 'Active',
                        'on_leave'   => 'On Leave',
                        'terminated' => 'Terminated',
                        default      => 'Inactive',
                    };
                @endphp
                <x-ui.badge :variant="$statusVariant" size="sm">{{ $statusLabel }}</x-ui.badge>
            </x-ui.table-td>
            <x-ui.table-td align="right">
                <div class="flex gap-1 justify-end" onclick="event.stopPropagation()">
                    <x-ui.button href="{{ route('employees.show', $emp->id) }}" variant="ghost" size="sm">
                        <x-slot:icon><i class="bi bi-eye"></i></x-slot:icon>
                    </x-ui.button>
                    <x-ui.button href="{{ route('employees.edit', $emp->id) }}" variant="ghost" size="sm">
                        <x-slot:icon><i class="bi bi-pencil"></i></x-slot:icon>
                    </x-ui.button>
                </div>
            </x-ui.table-td>
        </tr>
        @empty
        <tr>
            <td colspan="7">
                <x-ui.empty-state icon="bi-people" title="No employees found" description="Add your first employee to get started." compact>
                    <x-ui.button href="{{ route('employees.create') }}" variant="primary" size="sm">
                        <x-slot:icon><i class="bi bi-person-plus-fill"></i></x-slot:icon>
                        Add Employee
                    </x-ui.button>
                </x-ui.empty-state>
            </td>
        </tr>
        @endforelse
        </x-slot:body>
    </x-ui.table>
</x-ui.card>

<x-ui.pagination :paginator="$employees" class="mt-4"/>

@endsection
