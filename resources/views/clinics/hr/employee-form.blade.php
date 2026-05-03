@extends('clinics.layout.app')
@section('title', $employee ? 'Edit Employee' : 'New Employee')
@section('content')

<x-ui.page-header
    :km="$employee ? 'កែប្រែបុគ្គលិក' : 'បន្ថែមបុគ្គលិក'"
    :title="$employee ? 'Edit Employee' : 'New Employee'"
    :breadcrumbs="[
        ['label'=>__('app.home'),'url'=>route('dashboard')],
        ['label'=>'Employees','url'=>route('employees.index')],
        ['label'=>$employee ? 'Edit '.$employee->code : 'New'],
    ]">
    @if($employee)
        <x-slot:actions>
            <x-ui.button href="{{ route('employees.show', $employee->id) }}" variant="secondary">
                <x-slot:icon><i class="bi bi-arrow-left"></i></x-slot:icon>
                Back
            </x-ui.button>
        </x-slot:actions>
    @endif
</x-ui.page-header>

@if($errors->any())
    <x-ui.alert type="error" class="mb-4">
        <ul class="list-disc list-inside text-xs space-y-0.5">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </x-ui.alert>
@endif

<form method="POST"
      action="{{ $employee ? route('employees.update', $employee->id) : route('employees.store') }}"
      novalidate>
@csrf
@if($employee) @method('PATCH') @endif

<div class="row g-3">

{{-- Main --}}
<div class="col-12 col-lg-8">

    {{-- Personal Info --}}
    <x-ui.card class="mb-3">
        <x-slot:header>
            <x-ui.card-header label="Personal Information" icon="bi-person-fill"/>
        </x-slot:header>
        <div class="row g-3">
            <div class="col-6 col-md-4">
                <x-form.field name="surname" km="នាមត្រកូល" en="Surname" required
                              :value="old('surname', $employee?->surname)"/>
            </div>
            <div class="col-6 col-md-4">
                <x-form.field name="name" km="ឈ្មោះ" en="First Name" required
                              :value="old('name', $employee?->name)"/>
            </div>
            <div class="col-6 col-md-4">
                <x-form.field name="name_kh" km="ឈ្មោះខ្មែរ" en="Khmer Name"
                              :value="old('name_kh', $employee?->name_kh)"/>
            </div>
            <div class="col-6 col-md-3">
                <x-form.select name="gender" km="ភេទ" en="Gender"
                               :options="[''=>'—','M'=>'Male','F'=>'Female']"
                               :value="old('gender', $employee?->gender)"/>
            </div>
            <div class="col-6 col-md-3">
                <x-form.field name="birthdate" km="ថ្ងៃកំណើត" en="Birthdate" type="date"
                              :value="old('birthdate', $employee?->birthdate?->toDateString())"/>
            </div>
            <div class="col-6 col-md-3">
                <x-form.field name="phone" km="ទូរស័ព្ទ" en="Phone"
                              :value="old('phone', $employee?->phone)"/>
            </div>
            <div class="col-6 col-md-3">
                <x-form.field name="email" km="អ៊ីម៉ែល" en="Email" type="email"
                              :value="old('email', $employee?->email)"/>
            </div>
        </div>
    </x-ui.card>

    {{-- Professional --}}
    <x-ui.card class="mb-3">
        <x-slot:header>
            <x-ui.card-header label="Professional Details" icon="bi-briefcase-fill"/>
        </x-slot:header>
        <div class="row g-3">
            <div class="col-6 col-md-4">
                <div class="fld">
                    <label class="flbl"><span class="km">ប្រភេទ</span><span class="en">/ Employee Type</span><span class="req">*</span></label>
                    <select name="employee_type" class="form-select" required>
                        <option value="">— ជ្រើស —</option>
                        @foreach(['doctor'=>'Doctor','nurse'=>'Nurse','pharmacist'=>'Pharmacist','lab_tech'=>'Lab Technician','admin'=>'Admin / Cashier','other'=>'Other'] as $v=>$l)
                            <option value="{{ $v }}" {{ old('employee_type', $employee?->employee_type)===$v?'selected':'' }}>{{ $l }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-6 col-md-4">
                <div class="fld">
                    <label class="flbl"><span class="km">នាយកដ្ឋាន</span><span class="en">/ Department</span></label>
                    <select name="department_id" class="form-select">
                        <option value="">— None —</option>
                        @foreach($departments as $dep)
                            <option value="{{ $dep->id }}" {{ old('department_id',$employee?->department_id)==$dep->id?'selected':'' }}>
                                {{ $dep->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-6 col-md-4">
                <x-form.field name="specialization" km="ជំនាញ" en="Specialization"
                              placeholder="Cardiology, General…"
                              :value="old('specialization', $employee?->specialization)"/>
            </div>
            <div class="col-6 col-md-4">
                <x-form.field name="license_number" km="លេខអាជ្ញាប័ណ្ណ" en="License No."
                              :value="old('license_number', $employee?->license_number)"/>
            </div>
            <div class="col-6 col-md-4">
                <x-form.field name="hire_date" km="ថ្ងៃចូលបម្រើ" en="Hire Date" type="date"
                              :value="old('hire_date', $employee?->hire_date?->toDateString())"/>
            </div>
            <div class="col-6 col-md-4">
                <x-form.select name="status" km="ស្ថានភាព" en="Status"
                               :options="['active'=>'Active','inactive'=>'Inactive','on_leave'=>'On Leave','terminated'=>'Terminated']"
                               :value="old('status', $employee?->status ?? 'active')"/>
            </div>
            @if($employee)
            <div class="col-6 col-md-4">
                <x-form.field name="end_date" km="ថ្ងៃចប់" en="End Date" type="date"
                              :value="old('end_date', $employee?->end_date?->toDateString())"/>
            </div>
            @endif
        </div>
    </x-ui.card>

</div>

{{-- Sidebar --}}
<div class="col-12 col-lg-4">
    <x-ui.card style="position:sticky;top:76px">
        <x-slot:header>
            <x-ui.card-header label="Save" icon="bi-save-fill"/>
        </x-slot:header>
        <x-ui.button type="submit" variant="primary" :fullWidth="true" class="mb-2">
            <x-slot:icon><i class="bi bi-check2-circle"></i></x-slot:icon>
            {{ $employee ? 'Update Employee' : 'Create Employee' }}
        </x-ui.button>
        <x-ui.button href="{{ $employee ? route('employees.show', $employee->id) : route('employees.index') }}" variant="secondary" :fullWidth="true">
            <x-slot:icon><i class="bi bi-x-circle"></i></x-slot:icon>
            Cancel
        </x-ui.button>

        @if($employee)
        <div class="mt-4 pt-3 border-t border-[#f0f2ff]">
            <div class="text-[11px] text-[#94a3b8] mb-2 font-bold uppercase tracking-wide">Info</div>
            @foreach([
                ['Code',    $employee->code],
                ['Created', $employee->created_at?->format('d/m/Y')],
                ['Updated', $employee->updated_at?->format('d/m/Y')],
            ] as [$l,$v])
            <div class="flex justify-between text-xs mb-1">
                <span class="text-[#94a3b8]">{{ $l }}</span>
                <span class="font-semibold text-[#012970]">{{ $v }}</span>
            </div>
            @endforeach

            @if($employee->user)
            <div class="mt-2.5 p-2 bg-[#e8f8ef] rounded-lg">
                <div class="text-[11px] font-bold text-[#2eca6a]">Linked User Account</div>
                <div class="text-xs text-[#555]">{{ $employee->user->name }}</div>
                <div class="text-[10.5px] text-[#94a3b8]">{{ $employee->user->email }}</div>
            </div>
            @endif
        </div>

        <form method="POST" action="{{ route('employees.destroy', $employee->id) }}"
              class="mt-3"
              data-confirm="Permanently delete this employee record? This cannot be undone."
              data-confirm-type="danger" data-confirm-title="Delete Employee">
            @csrf @method('DELETE')
            <x-ui.button type="submit" variant="danger" :fullWidth="true" size="sm">
                <x-slot:icon><i class="bi bi-trash3-fill"></i></x-slot:icon>
                Delete Employee
            </x-ui.button>
        </form>
        @endif

    </x-ui.card>
</div>

</div>
</form>
@endsection
