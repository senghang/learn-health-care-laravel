@extends('clinics.layout.app')
@section('title', __('app.prescriptions'))
@section('content')

<x-ui.page-header
    :km="__('app.prescriptions')"
    title="Prescriptions"
    :breadcrumbs="[
        ['label' => __('app.home'), 'url' => route('dashboard')],
        ['label' => __('app.prescriptions')],
    ]">
    <x-slot:actions>
        <x-ui.button href="{{ route('prescriptions.create') }}" variant="primary" size="sm">
            <x-slot:icon><i class="bi bi-plus-lg" aria-hidden="true"></i></x-slot:icon>
            New Prescription
        </x-ui.button>
        <x-ui.button href="{{ route('workflow.create') }}" variant="secondary" size="sm">
            <x-slot:icon><i class="bi bi-diagram-3-fill" aria-hidden="true"></i></x-slot:icon>
            {{ __('app.new_visit') }}
        </x-ui.button>
    </x-slot:actions>
</x-ui.page-header>

{{-- KPI --}}
<div class="grid grid-cols-3 gap-4 mb-5">
    @foreach([
        [$stats['today'],   'bi-capsule-fill',      '#4154f1','#eef0fd', __('app.today'),   'Today'],
        [$stats['total'],   'bi-archive-fill',       '#2eca6a','#e8f8ef', __('app.total'),   'Total'],
        [$stats['doctors'], 'bi-person-badge-fill',  '#9b59b6','#f0e8ff', __('app.doctors'), 'Prescribers'],
    ] as [$val,$ico,$col,$bg,$km,$en])
        <x-ui.stats-card :value="$val" :km="$km" :label="$en" :icon="$ico" :color="$col" :bg="$bg" />
    @endforeach
</div>

{{-- Filter --}}
<x-ui.card class="mb-4">
    <form method="GET" action="{{ route('prescriptions.index') }}">
        <div class="flex flex-wrap gap-2 items-end">
            <div class="flex-1 min-w-40">
                <x-forms.input type="text" name="search"
                               :placeholder="__('app.search') . '… (' . __('app.patient.code') . ', code)'"
                               :value="request('search')" autofocus />
            </div>
            <div class="w-36">
                <x-forms.input type="date" name="date" :value="request('date')" />
            </div>
            <div class="w-36">
                <x-forms.input type="text" name="doctor"
                               :placeholder="__('app.doctor')"
                               :value="request('doctor')" />
            </div>
            <div class="flex gap-2">
                <x-ui.button type="submit" variant="primary">
                    <x-slot:icon><i class="bi bi-funnel-fill" aria-hidden="true"></i></x-slot:icon>
                </x-ui.button>
                @if(request()->hasAny(['search','date','doctor']))
                    <x-ui.button href="{{ route('prescriptions.index') }}" variant="secondary">
                        <x-slot:icon><i class="bi bi-x-circle" aria-hidden="true"></i></x-slot:icon>
                    </x-ui.button>
                @endif
            </div>
        </div>
    </form>
</x-ui.card>

{{-- List --}}
<x-ui.card noPadding>
    <x-slot:header>
        <x-ui.card-header label="{{ __('app.prescriptions') }}" icon="bi-capsule-fill">
            <x-slot:actions>
                <span class="text-xs" style="color:#9ca3af">{{ $prescriptions->total() }} {{ __('app.records') }}</span>
            </x-slot:actions>
        </x-ui.card-header>
    </x-slot:header>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr style="background:#f9fafb;border-bottom:1px solid #e6eaf5">
                    <th class="text-left text-xs font-semibold px-4 py-2.5" style="color:#6b7280">{{ __('app.date') }}</th>
                    <th class="text-left text-xs font-semibold px-3 py-2.5" style="color:#6b7280">{{ __('app.code') }}</th>
                    <th class="text-left text-xs font-semibold px-3 py-2.5" style="color:#6b7280">{{ __('app.patient.name') }}</th>
                    <th class="text-left text-xs font-semibold px-3 py-2.5 hidden md:table-cell" style="color:#6b7280">{{ __('app.doctor') }}</th>
                    <th class="text-left text-xs font-semibold px-3 py-2.5" style="color:#6b7280">{{ __('app.medications') }}</th>
                    <th class="text-left text-xs font-semibold px-3 py-2.5 hidden lg:table-cell" style="color:#6b7280">{{ __('app.visit.title') }}</th>
                    <th style="width:80px"></th>
                </tr>
            </thead>
            <tbody>
            @forelse($prescriptions as $rx)
                <tr class="border-b hover:bg-[#f9fafb] transition-colors" style="border-color:#f1f5f9">
                    <td class="px-4 py-3">
                        <div class="text-xs font-semibold" style="color:#1a1f36">{{ $rx->prescribed_at?->format('d/m/Y') ?? '—' }}</div>
                        <div class="text-xs" style="color:#9ca3af">{{ $rx->prescribed_at?->format('H:i') }}</div>
                    </td>
                    <td class="px-3 py-3">
                        <code class="text-xs font-bold" style="color:#e91e8c">{{ $rx->code }}</code>
                    </td>
                    <td class="px-3 py-3">
                        @if($rx->patient)
                            <a href="{{ route('patients.show', $rx->patient->code) }}"
                               class="text-sm font-semibold hover:underline" style="color:#1a1f36">
                                {{ $rx->patient->surname }}, {{ $rx->patient->name }}
                            </a>
                            <div class="text-xs" style="color:#9ca3af">{{ $rx->patient_code }}</div>
                        @else
                            <span style="color:#d1d5db">—</span>
                        @endif
                    </td>
                    <td class="px-3 py-3 text-xs hidden md:table-cell" style="color:#374151">{{ $rx->prescribed_by ?? '—' }}</td>
                    <td class="px-3 py-3">
                        <span class="text-sm font-bold" style="color:#e91e8c">{{ $rx->medications->count() }}</span>
                        <span class="text-xs" style="color:#9ca3af"> {{ __('app.items') }}</span>
                        @if($rx->medications->count() > 0)
                            <div class="text-xs mt-0.5" style="color:#9ca3af">
                                {{ $rx->medications->first()?->medicine_name }}
                                @if($rx->medications->count() > 1)
                                    + {{ $rx->medications->count() - 1 }} {{ __('app.more') }}
                                @endif
                            </div>
                        @endif
                    </td>
                    <td class="px-3 py-3 hidden lg:table-cell">
                        @if($rx->visit_code)
                            <a href="{{ url('/workflow/'.$rx->visit_code) }}"
                               class="text-xs hover:underline" style="color:#4154f1">
                                {{ $rx->visit_code }}
                            </a>
                        @else
                            <span style="color:#d1d5db">—</span>
                        @endif
                    </td>
                    <td class="px-3 py-3">
                        <div class="flex gap-1">
                            <x-ui.button href="{{ route('prescriptions.show', $rx->code) }}" variant="secondary" size="sm">
                                <x-slot:icon><i class="bi bi-eye-fill" aria-hidden="true"></i></x-slot:icon>
                            </x-ui.button>
                            <x-ui.button href="{{ route('print.prescription', $rx->code) }}" variant="secondary" size="sm"
                                         onclick="window.open(this.href,'_blank');return false;">
                                <x-slot:icon><i class="bi bi-printer-fill" aria-hidden="true"></i></x-slot:icon>
                            </x-ui.button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-4 py-8">
                        <x-ui.empty-state icon="bi-capsule" title="{{ __('app.no_records') }}"
                                          description="Prescriptions are created during patient visits">
                            <x-ui.button href="{{ route('workflow.create') }}" variant="primary" size="sm">
                                <x-slot:icon><i class="bi bi-plus-lg" aria-hidden="true"></i></x-slot:icon>
                                {{ __('app.new_visit') }}
                            </x-ui.button>
                        </x-ui.empty-state>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</x-ui.card>

<x-ui.pagination :paginator="$prescriptions" />

@endsection
