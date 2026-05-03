@extends('clinics.layout.app')
@section('title', 'អ្នកជំងឺ / Patients')

@section('content')

    <x-ui.page-header
        km="អ្នកជំងឺ"
        title="Patients"
        :breadcrumbs="[['label' => 'Dashboard', 'url' => url('/')], ['label' => 'Patients']]">
        <x-slot:actions>
            <x-ui.button href="{{ route('patients.create') }}" variant="primary">
                <x-slot:icon><i class="bi bi-person-plus-fill" aria-hidden="true"></i></x-slot:icon>
                <span class="hidden sm:inline">{{ __('app.patient.new') }}</span>
                <span class="sm:hidden">New</span>
            </x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    {{-- ── SEARCH + FILTER ─────────────────────────────────────── --}}
    <x-ui.card class="mb-4" :noPadding="false">
        <form method="GET" action="{{ route('patients.index') }}" role="search" aria-label="Search patients">
            <div class="flex flex-col sm:flex-row gap-3">

                {{-- Search input (no label wrapping needed — standalone) --}}
                <div class="flex-1 relative">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none" aria-hidden="true">
                        <i class="bi bi-search text-sm text-[#94a3b8]"></i>
                    </div>
                    <input type="text" name="search" id="patientSearch" value="{{ request('search') }}"
                        placeholder="ស្វែងរក / Search by name, code, phone, SPID…"
                        class="w-full text-sm rounded-lg border border-[#e2e8f0] bg-white pl-9 pr-4 py-2.5 text-[#374151] placeholder-[#94a3b8] transition-colors focus:outline-none focus:border-[#4154f1] focus:ring-2 focus:ring-[#4154f1]/20"
                        aria-label="Search patients" />
                </div>

                {{-- Sex filter --}}
                <div class="relative sm:w-36">
                    <select name="sex"
                        class="w-full text-sm rounded-lg border border-[#e2e8f0] bg-white px-4 py-2.5 text-[#374151] transition-colors focus:outline-none focus:border-[#4154f1] focus:ring-2 focus:ring-[#4154f1]/20 appearance-none"
                        style="padding-right:2.5rem" aria-label="Filter by sex">
                        <option value="">{{ __('app.patient.sex') }} — All</option>
                        <option value="M" @selected(request('sex') === 'M')>{{ __('app.patient.male') }}</option>
                        <option value="F" @selected(request('sex') === 'F')>{{ __('app.patient.female') }}</option>
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none" aria-hidden="true">
                        <i class="bi bi-chevron-down text-xs text-[#94a3b8]"></i>
                    </div>
                </div>

                {{-- Status filter --}}
                <div class="relative sm:w-36">
                    <select name="status"
                        class="w-full text-sm rounded-lg border border-[#e2e8f0] bg-white px-4 py-2.5 text-[#374151] transition-colors focus:outline-none focus:border-[#4154f1] focus:ring-2 focus:ring-[#4154f1]/20 appearance-none"
                        style="padding-right:2.5rem" aria-label="Filter by status">
                        <option value="">Status — All</option>
                        <option value="Active" @selected(request('status') === 'Active')>Active</option>
                        <option value="Inactive" @selected(request('status') === 'Inactive')>Inactive</option>
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none" aria-hidden="true">
                        <i class="bi bi-chevron-down text-xs text-[#94a3b8]"></i>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="flex gap-2">
                    <x-ui.button type="submit" variant="primary">
                        <x-slot:icon><i class="bi bi-funnel-fill" aria-hidden="true"></i></x-slot:icon>
                        {{ __('app.search') }}
                    </x-ui.button>
                    @if (request()->hasAny(['search', 'sex', 'status']))
                        <x-ui.button href="{{ route('patients.index') }}" variant="secondary">
                            <x-slot:icon><i class="bi bi-x-circle" aria-hidden="true"></i></x-slot:icon>
                            Clear
                        </x-ui.button>
                    @endif
                </div>
            </div>
        </form>
    </x-ui.card>

    {{-- ── ACTIVE FILTERS SUMMARY ───────────────────────────────── --}}
    @if (request()->hasAny(['search', 'sex', 'status']))
        <div class="flex items-center gap-2 mb-4 flex-wrap">
            <span class="text-xs text-[#94a3b8]">Active filters:</span>
            @if (request('search'))
                <x-ui.badge variant="primary" dot>
                    "{{ Str::limit(request('search'), 25) }}"
                </x-ui.badge>
            @endif
            @if (request('sex'))
                <x-ui.badge variant="secondary" dot>
                    {{ request('sex') === 'M' ? 'Male' : 'Female' }}
                </x-ui.badge>
            @endif
            @if (request('status'))
                <x-ui.badge :variant="request('status') === 'Active' ? 'success' : 'secondary'" dot>
                    {{ request('status') }}
                </x-ui.badge>
            @endif
            @if ($patients->total() > 0)
                <span class="text-xs text-[#94a3b8] ml-auto">
                    {{ number_format($patients->total()) }} result{{ $patients->total() !== 1 ? 's' : '' }}
                </span>
            @endif
        </div>
    @endif

    {{-- ── PATIENT LIST ─────────────────────────────────────────── --}}
    @php
        $colors = ['#4154f1', '#2eca6a', '#ff771d', '#e74c3c', '#9b59b6', '#00bcd4', '#f39c12', '#1abc9c'];
    @endphp

    @forelse($patients as $patient)
        @php
            $initials = strtoupper(substr($patient->surname, 0, 1) . substr($patient->name, 0, 1));
            $color = $colors[abs(crc32($patient->code)) % count($colors)];
        @endphp
        <div class="group flex items-center gap-4 px-5 py-4 bg-white rounded-2xl mb-2 border border-[#f0f2ff] cursor-pointer
            hover:-translate-y-0.5 hover:shadow-md hover:border-[#e0e4ff] transition-all duration-150"
            style="box-shadow:0 1px 3px rgba(1,41,112,.04)"
            onclick="window.location='{{ route('patients.show', $patient->code) }}'" role="listitem">

            {{-- Avatar --}}
            <div class="w-11 h-11 rounded-xl flex items-center justify-center text-sm font-black flex-shrink-0 text-white"
                style="background:linear-gradient(135deg,{{ $color }},{{ $color }})" aria-hidden="true">
                {{ $initials ?: '?' }}
            </div>

            {{-- Info --}}
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="text-sm font-bold text-[#012970]">{{ $patient->surname }}, {{ $patient->name }}</span>
                    @if ($patient->status === 'Inactive')
                        <x-ui.badge variant="secondary" size="sm">Inactive</x-ui.badge>
                    @endif
                </div>
                <div class="flex items-center gap-2 mt-0.5 flex-wrap">
                    <span class="text-xs font-mono text-[#4154f1] font-bold">{{ $patient->code }}</span>
                    @if ($patient->phone)
                        <span class="text-xs text-[#94a3b8]">· {{ $patient->phone }}</span>
                    @endif
                    @if ($patient->sex)
                        <span class="text-xs text-[#94a3b8]">·
                            {{ $patient->sex === 'M' ? __('app.patient.male') : __('app.patient.female') }}</span>
                    @endif
                    @if ($patient->birthdate)
                        <span class="text-xs text-[#94a3b8]">· {{ $patient->birthdate->age }}y</span>
                    @endif
                    @if ($patient->blood_type)
                        <span class="text-xs font-bold text-[#e74c3c]">{{ $patient->blood_type }}</span>
                    @endif
                </div>
            </div>

            {{-- Right: visits badge + actions --}}
            <div class="flex items-center gap-2 flex-shrink-0">
                @if ($patient->visits_count > 0)
                    <x-ui.badge variant="primary" size="sm">
                        {{ $patient->visits_count }} {{ __('app.patient.visits_count') }}
                    </x-ui.badge>
                @else
                    <x-ui.badge variant="success" size="sm" dot>New</x-ui.badge>
                @endif

                {{-- Actions dropdown --}}
                <div onclick="event.stopPropagation()">
                    <x-ui.dropdown align="right" width="w-44">
                        <x-slot:trigger>
                            <button type="button"
                                class="flex items-center justify-center w-8 h-8 rounded-lg text-[#94a3b8] hover:text-[#4154f1] hover:bg-[#eef0fd] transition-colors opacity-0 group-hover:opacity-100 focus:opacity-100"
                                aria-label="Patient actions for {{ $patient->surname }} {{ $patient->name }}">
                                <i class="bi bi-three-dots-vertical" style="font-size:13px"></i>
                            </button>
                        </x-slot:trigger>

                        <x-ui.dropdown-item href="{{ route('patients.show', $patient->code) }}" icon="bi-eye">
                            View Profile
                        </x-ui.dropdown-item>
                        <x-ui.dropdown-item href="{{ route('patients.edit', $patient->code) }}" icon="bi-pencil">
                            Edit Patient
                        </x-ui.dropdown-item>
                        <x-ui.dropdown-item href="{{ route('workflow.create', ['patient' => $patient->code]) }}"
                            icon="bi-plus-circle">
                            New Visit
                        </x-ui.dropdown-item>
                        <x-ui.dropdown-divider />
                        <x-ui.dropdown-item icon="bi-trash" variant="danger"
                            onclick="document.getElementById('deletePatient_{{ $patient->id }}').dispatchEvent(new Event('open'))">
                            Delete
                        </x-ui.dropdown-item>
                    </x-ui.dropdown>
                </div>

                <i class="bi bi-chevron-right text-[#e2e8f0] group-hover:text-[#4154f1] transition-colors flex-shrink-0"
                    aria-hidden="true"></i>
            </div>
        </div>

        {{-- Delete confirm modal --}}
        <x-ui.confirm-modal id="deletePatient_{{ $patient->id }}" title="Delete Patient"
            message="Are you sure you want to delete {{ $patient->surname }} {{ $patient->name }} ({{ $patient->code }})? All visit records will be permanently removed."
            variant="danger" action="{{ route('patients.destroy', $patient->code) }}" method="DELETE"
            confirm-label="Yes, Delete" />

    @empty
        <x-ui.empty-state icon="bi-person-circle" :title="request('search') ? 'No patients match your search' : 'No patients registered yet'" :description="request('search')
            ? 'Try a different name, code, or phone number.'
            : 'Register the first patient to get started.'">
            @if (request('search'))
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

    {{-- ── PAGINATION ───────────────────────────────────────────── --}}
    <x-ui.pagination :paginator="$patients" />

@endsection
