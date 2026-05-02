@extends('clinics.layout.app')
@section('title', $employee ? 'Edit Employee' : 'New Employee')
@section('content')

<x-page-header
    :title="$employee ? 'កែប្រែបុគ្គលិក' : 'បន្ថែមបុគ្គលិក'"
    :subtitle="$employee ? 'Edit Employee' : 'New Employee'"
    :breadcrumbs="[
        ['label'=>__('app.home'),'url'=>route('dashboard')],
        ['label'=>'Employees','url'=>route('employees.index')],
        ['label'=>$employee ? 'Edit '.$employee->code : 'New'],
    ]">
    @if($employee)
        <a href="{{ route('employees.show', $employee->id) }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    @endif
</x-page-header>

@if($errors->any())
<div class="note note-danger mb-3">
    <i class="bi bi-exclamation-triangle-fill"></i>
    <ul style="margin:0;padding-left:16px;font-size:12px">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
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
    <div class="card-emr mb-3">
        <div class="card-hd" style="background:#f6f9ff">
            <div class="card-hd-title"><i class="bi bi-person-fill" style="color:#4154f1"></i> Personal Information</div>
        </div>
        <div class="card-bd">
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
        </div>
    </div>

    {{-- Professional --}}
    <div class="card-emr mb-3">
        <div class="card-hd" style="background:#f0fcff">
            <div class="card-hd-title"><i class="bi bi-briefcase-fill" style="color:#00bcd4"></i> Professional Details</div>
        </div>
        <div class="card-bd">
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
        </div>
    </div>

</div>

{{-- Sidebar --}}
<div class="col-12 col-lg-4">
    <div class="card-emr" style="position:sticky;top:76px">
        <div class="card-hd" style="background:#f6f9ff">
            <div class="card-hd-title"><i class="bi bi-save-fill" style="color:#4154f1"></i> Save</div>
        </div>
        <div class="card-bd">
            <button type="submit" class="btn btn-primary btn-w100 mb-2">
                <i class="bi bi-check2-circle"></i>
                {{ $employee ? 'Update Employee' : 'Create Employee' }}
            </button>
            <a href="{{ $employee ? route('employees.show', $employee->id) : route('employees.index') }}"
               class="btn btn-outline-secondary btn-w100">
                <i class="bi bi-x-circle"></i> Cancel
            </a>

            @if($employee)
            <div style="margin-top:16px;padding-top:14px;border-top:1px solid #f0f2ff">
                <div style="font-size:11px;color:#aaa;margin-bottom:8px;font-weight:700;text-transform:uppercase">Info</div>
                @foreach([
                    ['Code',    $employee->code],
                    ['Created', $employee->created_at?->format('d/m/Y')],
                    ['Updated', $employee->updated_at?->format('d/m/Y')],
                ] as [$l,$v])
                <div style="display:flex;justify-content:space-between;font-size:11.5px;margin-bottom:4px">
                    <span style="color:#aaa">{{ $l }}</span>
                    <span style="font-weight:600;color:#012970">{{ $v }}</span>
                </div>
                @endforeach

                @if($employee->user)
                <div style="margin-top:10px;padding:8px;background:#e8f8ef;border-radius:8px;font-size:11px">
                    <div style="font-weight:700;color:#2eca6a">Linked User Account</div>
                    <div style="color:#555">{{ $employee->user->name }}</div>
                    <div style="color:#aaa">{{ $employee->user->email }}</div>
                </div>
                @endif
            </div>

            <form method="POST" action="{{ route('employees.destroy', $employee->id) }}"
                  class="mt-3"
                  data-confirm="Permanently delete this employee record? This cannot be undone."
                  data-confirm-type="danger" data-confirm-title="Delete Employee">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-outline-danger btn-w100 btn-sm">
                    <i class="bi bi-trash3-fill"></i> Delete Employee
                </button>
            </form>
            @endif

        </div>
    </div>
</div>

</div>
</form>
@endsection
