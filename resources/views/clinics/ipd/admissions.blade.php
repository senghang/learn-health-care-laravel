@extends('clinics.layout.app')
@section('title', 'IPD — Admissions')
@section('content')

<x-ui.page-header
    km="អ្នកជំងឺសម្រាក"
    title="Inpatient Admissions"
    :breadcrumbs="[['label'=>'ដើម','url'=>route('dashboard')],['label'=>'IPD'],['label'=>'Admissions']]">
</x-ui.page-header>

{{-- Flow Banner --}}
<div class="flow-panel mb-3">
    <div class="flow-panel-title">
        <i class="bi bi-diagram-3-fill me-1"></i> Admission Workflow
    </div>
    <div class="flow-steps">
        <span class="flow-step"><i class="bi bi-person-plus-fill me-1"></i>1. Admit patient</span>
        <span class="flow-step"><i class="bi bi-hospital me-1"></i>2. Assign bed</span>
        <span class="flow-step"><i class="bi bi-clipboard2-heart me-1"></i>3. Treatments &amp; medications</span>
        <span class="flow-step"><i class="bi bi-calendar2-week me-1"></i>4. Monitor LOS</span>
        <span class="flow-step"><i class="bi bi-box-arrow-right me-1"></i>5. Discharge</span>
    </div>
</div>

@if(session('flash'))
    <x-ui.alert type="success" class="mb-4">{{ session('flash') }}</x-ui.alert>
@endif
@if(session('flash_error'))
    <x-ui.alert type="error" class="mb-4">{{ session('flash_error') }}</x-ui.alert>
@endif

{{-- KPI Cards --}}
<div class="grid grid-cols-2 xl:grid-cols-4 gap-3 mb-4">
    <x-ui.stats-card km="សម្រាកនៅ" label="Active Admissions" :value="$stats['active_admissions']" icon="bi-hospital-fill" color="#4154f1" bg="#eef0fd"/>
    <x-ui.stats-card km="ថ្ងៃនេះចូល" label="Admitted Today" :value="$stats['today_admissions']" icon="bi-box-arrow-in-right" color="#2eca6a" bg="#e8f8ef"/>
    <x-ui.stats-card km="ថ្ងៃនេះចេញ" label="Discharged Today" :value="$stats['today_discharges']" icon="bi-box-arrow-right" color="#ff771d" bg="#fff3e8"/>
    <x-ui.stats-card km="ថ្ងៃ​ជា​មធ្យម" :label="'Avg Stay: '.$stats['avg_length_of_stay'].'d'" :value="$stats['avg_length_of_stay'].'d'" icon="bi-calendar2-week" color="#9b59b6" bg="#f5eeff"/>
</div>

{{-- Filter --}}
<x-ui.card class="mb-4" :noPadding="false">
    <div class="flex items-center justify-between mb-3">
        <span class="text-xs font-bold text-[#1a1f36] flex items-center gap-1.5">
            <i class="bi bi-funnel-fill text-[#4154f1]"></i> Filter
        </span>
        @if(request()->hasAny(['search','status','ward_id','date']))
            <x-ui.button href="{{ route('admissions.index') }}" variant="secondary" size="sm">
                <x-slot:icon><i class="bi bi-x-circle"></i></x-slot:icon>
                Clear
            </x-ui.button>
        @endif
    </div>
    <form method="GET" action="{{ route('admissions.index') }}">
        <div class="flex flex-col sm:flex-row gap-3 flex-wrap">
            <div class="flex-1 min-w-0">
                <input type="text" name="search"
                       class="w-full text-sm rounded-lg border border-[#e2e8f0] bg-white px-3 py-2.5 text-[#374151] placeholder-[#6b7280] focus:outline-none focus:border-[#4154f1] focus:ring-2 focus:ring-[#4154f1]/20"
                       placeholder="Search code, patient name…" value="{{ request('search') }}" autofocus/>
            </div>
            <div class="sm:w-40">
                <select name="status" class="w-full text-sm rounded-lg border border-[#e2e8f0] bg-white px-3 py-2.5 text-[#374151] focus:outline-none focus:border-[#4154f1] focus:ring-2 focus:ring-[#4154f1]/20 appearance-none">
                    <option value="">All Status</option>
                    <option value="admitted"    {{ request('status')==='admitted'   ?'selected':'' }}>🟢 Admitted</option>
                    <option value="discharged"  {{ request('status')==='discharged' ?'selected':'' }}>🔵 Discharged</option>
                    <option value="transferred" {{ request('status')==='transferred'?'selected':'' }}>🟠 Transferred</option>
                    <option value="deceased"    {{ request('status')==='deceased'   ?'selected':'' }}>⚫ Deceased</option>
                    <option value="cancelled"   {{ request('status')==='cancelled'  ?'selected':'' }}>🔴 Cancelled</option>
                </select>
            </div>
            <div class="sm:w-44">
                <select name="ward_id" class="w-full text-sm rounded-lg border border-[#e2e8f0] bg-white px-3 py-2.5 text-[#374151] focus:outline-none focus:border-[#4154f1] focus:ring-2 focus:ring-[#4154f1]/20 appearance-none">
                    <option value="">All Wards</option>
                    @foreach($wards as $ward)
                    <option value="{{ $ward->id }}" {{ request('ward_id') == $ward->id ? 'selected':'' }}>{{ $ward->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="sm:w-40">
                <input type="date" name="date"
                       class="w-full text-sm rounded-lg border border-[#e2e8f0] bg-white px-3 py-2.5 text-[#374151] focus:outline-none focus:border-[#4154f1] focus:ring-2 focus:ring-[#4154f1]/20"
                       value="{{ request('date') }}" title="Filter by admission date"/>
            </div>
            <x-ui.button type="submit" variant="primary">
                <x-slot:icon><i class="bi bi-search"></i></x-slot:icon>
                Search
            </x-ui.button>
        </div>
    </form>
</x-ui.card>

{{-- Table --}}
<x-ui.card>
    <x-slot:header>
        <x-ui.card-header km="បញ្ជីចូលសម្រាក" label="Admission List" icon="bi-hospital" :count="$admissions->total()">
            <x-slot:actions>
                <span class="text-[11px] text-[#6b7280]">Click a row to open details</span>
            </x-slot:actions>
        </x-ui.card-header>
    </x-slot:header>

    <x-ui.table>
        <x-slot:head>
            <tr>
                <x-ui.table-th>Code</x-ui.table-th>
                <x-ui.table-th>Patient</x-ui.table-th>
                <x-ui.table-th>Ward / Bed</x-ui.table-th>
                <x-ui.table-th>Type</x-ui.table-th>
                <x-ui.table-th>Attending</x-ui.table-th>
                <x-ui.table-th>Admitted</x-ui.table-th>
                <x-ui.table-th>LOS</x-ui.table-th>
                <x-ui.table-th>Status</x-ui.table-th>
                <x-ui.table-th align="right">Action</x-ui.table-th>
            </tr>
        </x-slot:head>
        <x-slot:body>
        @forelse($admissions as $adm)
        @php
            $isAdmitted = $adm->status === 'admitted';
            $statusMeta = [
                'admitted'    => ['success',    'Admitted'],
                'discharged'  => ['primary',    'Discharged'],
                'transferred' => ['warning',    'Transferred'],
                'deceased'    => ['secondary',  'Deceased'],
                'cancelled'   => ['danger',     'Cancelled'],
            ];
            [$sVariant, $sLabel] = $statusMeta[$adm->status] ?? ['secondary', 'Unknown'];
            $los = $adm->length_of_stay ?? 0;
            $overdueStyle = $isAdmitted && $adm->expected_discharge_at?->isPast() ? 'color:#e74c3c;font-weight:700' : '';
        @endphp
        <tr class="cursor-pointer hover:bg-[#f9fafb] transition-colors" onclick="location.href='{{ route('admissions.show', $adm->code) }}'">
            <x-ui.table-td>
                <code class="text-[#4154f1] text-[11px]">{{ $adm->code }}</code>
                @if($isAdmitted && $adm->expected_discharge_at?->isPast())
                    <span title="Expected discharge date passed" class="text-[#e74c3c] ml-1 text-[10px]">
                        <i class="bi bi-alarm-fill"></i>
                    </span>
                @endif
            </x-ui.table-td>
            <x-ui.table-td>
                <div class="font-bold text-[#1a1f36] text-sm">
                    {{ $adm->patient?->surname }} {{ $adm->patient?->name }}
                </div>
                <div class="text-[10.5px] text-[#6b7280]">{{ $adm->patient_code }}</div>
            </x-ui.table-td>
            <x-ui.table-td>
                <div class="text-xs text-[#444] font-semibold">{{ $adm->ward?->name ?? '—' }}</div>
                @if($adm->bed)
                    <div class="text-[10.5px] text-[#6b7280]">
                        <i class="bi bi-hospital text-[9px]"></i> {{ $adm->bed->name }}
                    </div>
                @endif
            </x-ui.table-td>
            <x-ui.table-td>
                @if($adm->admission_type)
                    <x-ui.badge variant="primary" size="sm">{{ $adm->admission_type }}</x-ui.badge>
                @else
                    <span class="text-[#bbb]">—</span>
                @endif
            </x-ui.table-td>
            <x-ui.table-td>
                <span class="text-xs text-[#555]">{{ $adm->attending_doctor ?? '—' }}</span>
            </x-ui.table-td>
            <x-ui.table-td>
                <div class="text-xs font-semibold">{{ $adm->admitted_at?->format('d M Y') }}</div>
                <div class="text-[10px] text-[#6b7280]">{{ $adm->admitted_at?->format('H:i') }}</div>
            </x-ui.table-td>
            <x-ui.table-td>
                <span class="font-black text-sm" style="{{ $overdueStyle ?: 'color:'.($isAdmitted ? '#2eca6a':'#888') }}">
                    {{ $los }}d
                </span>
            </x-ui.table-td>
            <x-ui.table-td>
                <x-ui.badge :variant="$sVariant" size="sm">{{ $sLabel }}</x-ui.badge>
            </x-ui.table-td>
            <x-ui.table-td align="right">
                <div onclick="event.stopPropagation()">
                    <x-ui.button href="{{ route('admissions.show', $adm->code) }}" variant="ghost" size="sm">
                        <x-slot:icon><i class="bi bi-eye"></i></x-slot:icon>
                    </x-ui.button>
                </div>
            </x-ui.table-td>
        </tr>
        @empty
        <tr>
            <td colspan="9">
                <x-ui.empty-state icon="bi-hospital" title="No admissions found"
                    :description="request()->hasAny(['search','status','ward_id','date']) ? 'Try adjusting your filters.' : 'No admissions have been recorded yet.'"
                    compact>
                    @if(request()->hasAny(['search','status','ward_id','date']))
                        <x-ui.button href="{{ route('admissions.index') }}" variant="secondary" size="sm">
                            <x-slot:icon><i class="bi bi-x-circle"></i></x-slot:icon>
                            Clear filters
                        </x-ui.button>
                    @endif
                </x-ui.empty-state>
            </td>
        </tr>
        @endforelse
        </x-slot:body>
    </x-ui.table>
</x-ui.card>

<x-ui.pagination :paginator="$admissions" class="mt-4"/>

@endsection
