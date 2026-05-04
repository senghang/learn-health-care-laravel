@extends('clinics.layout.app')
@section('title', 'អ្នកជំងឺ / Patients')

@section('content')

@php
    $clinicId     = currentClinic()->id;
    $totalPatients = \App\Models\PatientModel::where('clinic_id', $clinicId)->count();
    $todayNew      = \App\Models\PatientModel::where('clinic_id', $clinicId)->whereDate('created_at', today())->count();
    $maleCount     = \App\Models\PatientModel::where('clinic_id', $clinicId)->where('sex', 'M')->count();
    $femaleCount   = \App\Models\PatientModel::where('clinic_id', $clinicId)->where('sex', 'F')->count();
    $colors        = ['#4154f1','#2eca6a','#ff771d','#e74c3c','#9b59b6','#00bcd4','#f39c12','#1abc9c'];
@endphp

<x-ui.page-header km="អ្នកជំងឺ" title="Patients"
    :breadcrumbs="[['label' => 'Dashboard', 'url' => url('/')], ['label' => 'Patients']]">
    <x-slot:actions>
        <x-ui.button href="{{ route('patients.create') }}" variant="primary">
            <x-slot:icon><i class="bi bi-person-plus-fill" aria-hidden="true"></i></x-slot:icon>
            <span class="hidden sm:inline">{{ __('app.patient.new') }}</span>
            <span class="sm:hidden">New</span>
        </x-ui.button>
    </x-slot:actions>
</x-ui.page-header>

{{-- ── KPI STRIP ──────────────────────────────────────────────── --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-5">
    <x-ui.stats-card
        value="{{ number_format($totalPatients) }}"
        label="Total Patients"
        km="អ្នកជំងឺសរុប"
        icon="bi-people-fill"
        color="#4154f1"
        bg="#eef0fd" />
    <x-ui.stats-card
        value="{{ $todayNew }}"
        label="Registered Today"
        km="ថ្មីថ្ងៃនេះ"
        icon="bi-person-plus-fill"
        color="#2eca6a"
        bg="#e8f8ef" />
    <x-ui.stats-card
        value="{{ number_format($maleCount) }}"
        label="Male"
        km="ភេទប្រុស"
        icon="bi-gender-male"
        color="#00bcd4"
        bg="#e8f7f9" />
    <x-ui.stats-card
        value="{{ number_format($femaleCount) }}"
        label="Female"
        km="ភេទស្រី"
        icon="bi-gender-female"
        color="#e91e8c"
        bg="#fde8f2" />
</div>

{{-- ── SEARCH + FILTER ────────────────────────────────────────── --}}
<x-ui.card class="mb-4">
    <form method="GET" action="{{ route('patients.index') }}" role="search" aria-label="Search patients">
        <div class="flex flex-col sm:flex-row gap-2.5">

            {{-- Search --}}
            <div class="flex-1 relative">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none" aria-hidden="true">
                    <i class="bi bi-search text-sm" style="color:#9ca3af"></i>
                </div>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Search by name, code, phone, SPID…"
                    class="w-full text-sm rounded-lg border border-[#e2e8f0] bg-white pl-9 pr-4 py-2 text-[#374151] placeholder-[#9ca3af] transition-colors focus:outline-none focus:border-[#4154f1] focus:ring-2 focus:ring-[#4154f1]/15"
                    aria-label="Search patients" />
            </div>

            {{-- Sex --}}
            <div class="relative sm:w-32">
                <select name="sex" onchange="this.form.submit()"
                    class="w-full text-sm rounded-lg border border-[#e2e8f0] bg-white px-3 py-2 pr-8 text-[#374151] transition-colors focus:outline-none focus:border-[#4154f1] focus:ring-2 focus:ring-[#4154f1]/15 appearance-none">
                    <option value="">All Genders</option>
                    <option value="M" @selected(request('sex') === 'M')>Male</option>
                    <option value="F" @selected(request('sex') === 'F')>Female</option>
                </select>
                <div class="absolute inset-y-0 right-0 flex items-center pr-2.5 pointer-events-none" aria-hidden="true">
                    <i class="bi bi-chevron-down text-xs" style="color:#9ca3af"></i>
                </div>
            </div>

            {{-- Status --}}
            <div class="relative sm:w-32">
                <select name="status" onchange="this.form.submit()"
                    class="w-full text-sm rounded-lg border border-[#e2e8f0] bg-white px-3 py-2 pr-8 text-[#374151] transition-colors focus:outline-none focus:border-[#4154f1] focus:ring-2 focus:ring-[#4154f1]/15 appearance-none">
                    <option value="">All Status</option>
                    <option value="Active"   @selected(request('status') === 'Active')>Active</option>
                    <option value="Inactive" @selected(request('status') === 'Inactive')>Inactive</option>
                </select>
                <div class="absolute inset-y-0 right-0 flex items-center pr-2.5 pointer-events-none" aria-hidden="true">
                    <i class="bi bi-chevron-down text-xs" style="color:#9ca3af"></i>
                </div>
            </div>

            {{-- Submit / Clear --}}
            <div class="flex gap-2 flex-shrink-0">
                <x-ui.button type="submit" variant="primary">
                    <x-slot:icon><i class="bi bi-search" aria-hidden="true"></i></x-slot:icon>
                    Search
                </x-ui.button>
                @if(request()->hasAny(['search','sex','status']))
                    <x-ui.button href="{{ route('patients.index') }}" variant="secondary">
                        <x-slot:icon><i class="bi bi-x-lg" aria-hidden="true"></i></x-slot:icon>
                        Clear
                    </x-ui.button>
                @endif
            </div>

        </div>
    </form>
</x-ui.card>

{{-- ── ACTIVE FILTERS CHIP ROW ────────────────────────────────── --}}
@if(request()->hasAny(['search','sex','status']))
    <div class="flex items-center gap-2 mb-3 flex-wrap">
        <span class="text-xs" style="color:#9ca3af">Filters:</span>
        @if(request('search'))
            <x-ui.badge variant="primary" dot>"{{ Str::limit(request('search'), 25) }}"</x-ui.badge>
        @endif
        @if(request('sex'))
            <x-ui.badge variant="secondary" dot>{{ request('sex') === 'M' ? 'Male' : 'Female' }}</x-ui.badge>
        @endif
        @if(request('status'))
            <x-ui.badge :variant="request('status') === 'Active' ? 'success' : 'secondary'" dot>{{ request('status') }}</x-ui.badge>
        @endif
        <span class="text-xs ml-auto font-medium" style="color:#6b7280">
            {{ number_format($patients->total()) }} result{{ $patients->total() !== 1 ? 's' : '' }}
        </span>
    </div>
@else
    <div class="flex items-center justify-between mb-3">
        <span class="text-xs font-medium" style="color:#9ca3af">
            Showing {{ number_format($patients->count()) }} of {{ number_format($patients->total()) }} patients
        </span>
    </div>
@endif

{{-- ── PATIENT LIST ─────────────────────────────────────────────── --}}
@forelse($patients as $patient)
    @php
        $initials = strtoupper(substr($patient->surname, 0, 1) . substr($patient->name, 0, 1));
        $color    = $colors[abs(crc32($patient->code)) % count($colors)];
        $isNew    = $patient->visits_count === 0;
    @endphp

    <div class="group relative flex items-center gap-3.5 px-4 py-3.5 bg-white rounded-xl mb-2 border border-[#eaecf0]
                cursor-pointer hover:border-[#c7cdfe] hover:shadow-sm transition-all duration-150"
         style="box-shadow:0 1px 2px rgba(17,24,39,.04)"
         onclick="window.location='{{ route('patients.show', $patient->code) }}'"
         role="listitem">

        {{-- Avatar --}}
        <div class="w-10 h-10 rounded-xl flex items-center justify-center text-sm font-black flex-shrink-0 text-white select-none"
             style="background:linear-gradient(135deg,{{ $color }},{{ $color }}dd)" aria-hidden="true">
            {{ $initials ?: '?' }}
        </div>

        {{-- Main info --}}
        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 flex-wrap mb-0.5">
                <span class="text-sm font-bold" style="color:#1a1f36">{{ $patient->surname }}, {{ $patient->name }}</span>
                @if($patient->status === 'Inactive')
                    <x-ui.badge variant="secondary" size="sm">Inactive</x-ui.badge>
                @endif
                @if($isNew)
                    <x-ui.badge variant="success" size="sm" dot>New</x-ui.badge>
                @endif
            </div>
            <div class="flex items-center gap-2 flex-wrap text-xs" style="color:#9ca3af">
                <span class="font-mono font-bold" style="color:#4154f1">{{ $patient->code }}</span>
                @if($patient->birthdate)
                    <span>· {{ $patient->birthdate->age }}y</span>
                @endif
                @if($patient->sex)
                    <span>· {{ $patient->sex === 'M' ? '♂' : '♀' }}</span>
                @endif
                @if($patient->blood_type)
                    <span class="font-bold" style="color:#e74c3c">{{ $patient->blood_type }}</span>
                @endif
                @if($patient->phone)
                    <span class="hidden sm:inline">· {{ $patient->phone }}</span>
                @endif
            </div>
        </div>

        {{-- Desktop: visit count + inline quick actions --}}
        <div class="hidden sm:flex items-center gap-2 flex-shrink-0" onclick="event.stopPropagation()">
            @if(!$isNew)
                <span class="text-xs font-semibold px-2 py-1 rounded-lg" style="background:#f0f0ff;color:#4154f1">
                    {{ $patient->visits_count }} {{ __('app.patient.visits_count') }}
                </span>
            @endif

            {{-- Quick action: New Visit --}}
            <a href="{{ route('workflow.create', ['patient' => $patient->code]) }}"
               class="inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1.5 rounded-lg transition-colors
                      opacity-0 group-hover:opacity-100 focus:opacity-100"
               style="color:#2eca6a;background:#f0fdf4"
               title="New Visit">
                <i class="bi bi-plus-circle-fill" style="font-size:11px"></i>
                <span>Visit</span>
            </a>

            {{-- Quick action: Edit --}}
            <a href="{{ route('patients.edit', $patient->code) }}"
               class="inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1.5 rounded-lg transition-colors
                      opacity-0 group-hover:opacity-100 focus:opacity-100"
               style="color:#6b7280;background:#f9fafb"
               title="Edit Patient">
                <i class="bi bi-pencil-fill" style="font-size:11px"></i>
                <span>Edit</span>
            </a>

            {{-- Delete dropdown item --}}
            <div>
                <x-ui.dropdown align="right" width="w-40">
                    <x-slot:trigger>
                        <button type="button"
                            class="flex items-center justify-center w-7 h-7 rounded-lg transition-colors
                                   opacity-0 group-hover:opacity-100 focus:opacity-100"
                            style="color:#9ca3af"
                            aria-label="More actions">
                            <i class="bi bi-three-dots-vertical" style="font-size:12px"></i>
                        </button>
                    </x-slot:trigger>
                    <x-ui.dropdown-item href="{{ route('patients.show', $patient->code) }}" icon="bi-eye">
                        View Profile
                    </x-ui.dropdown-item>
                    <x-ui.dropdown-divider />
                    <x-ui.dropdown-item icon="bi-trash" variant="danger"
                        onclick="document.getElementById('deletePatient_{{ $patient->id }}').dispatchEvent(new Event('open'))">
                        Delete
                    </x-ui.dropdown-item>
                </x-ui.dropdown>
            </div>
        </div>

        {{-- Mobile: dropdown only --}}
        <div class="sm:hidden flex items-center gap-2 flex-shrink-0" onclick="event.stopPropagation()">
            @if(!$isNew)
                <span class="text-xs font-semibold px-2 py-0.5 rounded" style="background:#f0f0ff;color:#4154f1">
                    {{ $patient->visits_count }}v
                </span>
            @endif
            <x-ui.dropdown align="right" width="w-44">
                <x-slot:trigger>
                    <button type="button"
                        class="flex items-center justify-center w-8 h-8 rounded-lg transition-colors"
                        style="color:#9ca3af"
                        aria-label="Actions">
                        <i class="bi bi-three-dots-vertical" style="font-size:13px"></i>
                    </button>
                </x-slot:trigger>
                <x-ui.dropdown-item href="{{ route('patients.show', $patient->code) }}" icon="bi-eye">View Profile</x-ui.dropdown-item>
                <x-ui.dropdown-item href="{{ route('patients.edit', $patient->code) }}" icon="bi-pencil">Edit</x-ui.dropdown-item>
                <x-ui.dropdown-item href="{{ route('workflow.create', ['patient' => $patient->code]) }}" icon="bi-plus-circle">New Visit</x-ui.dropdown-item>
                <x-ui.dropdown-divider />
                <x-ui.dropdown-item icon="bi-trash" variant="danger"
                    onclick="document.getElementById('deletePatient_{{ $patient->id }}').dispatchEvent(new Event('open'))">
                    Delete
                </x-ui.dropdown-item>
            </x-ui.dropdown>
        </div>

        {{-- Chevron --}}
        <i class="bi bi-chevron-right text-xs hidden sm:block transition-colors group-hover:text-[#4154f1]"
           style="color:#e2e8f0" aria-hidden="true"></i>

    </div>

    {{-- Delete confirm modal --}}
    <x-ui.confirm-modal
        id="deletePatient_{{ $patient->id }}"
        title="Delete Patient"
        message="Are you sure you want to delete {{ $patient->surname }} {{ $patient->name }} ({{ $patient->code }})? All visit records will be permanently removed."
        variant="danger"
        action="{{ route('patients.destroy', $patient->code) }}"
        method="DELETE"
        confirm-label="Yes, Delete" />

@empty
    <x-ui.empty-state
        icon="bi-person-circle"
        :title="request('search') ? 'No patients match your search' : 'No patients registered yet'"
        :description="request('search')
            ? 'Try a different name, code, or phone number.'
            : 'Register the first patient to get started.'">
        @if(request('search'))
            <x-ui.button href="{{ route('patients.index') }}" variant="secondary">
                <x-slot:icon><i class="bi bi-x-circle" aria-hidden="true"></i></x-slot:icon>
                Clear Search
            </x-ui.button>
        @else
            <x-ui.button href="{{ route('patients.create') }}" variant="primary">
                <x-slot:icon><i class="bi bi-person-plus-fill" aria-hidden="true"></i></x-slot:icon>
                {{ __('app.patient.new') }}
            </x-ui.button>
        @endif
    </x-ui.empty-state>
@endforelse

{{-- ── PAGINATION ───────────────────────────────────────────────── --}}
<x-ui.pagination :paginator="$patients" />

@endsection
