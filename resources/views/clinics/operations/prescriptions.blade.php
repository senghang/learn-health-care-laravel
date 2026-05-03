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
                <x-slot:icon><i class="bi bi-plus-lg"></i></x-slot:icon>
                New Prescription
            </x-ui.button>
            <x-ui.button href="{{ route('workflow.create') }}" variant="secondary" size="sm">
                <x-slot:icon><i class="bi bi-diagram-3-fill"></i></x-slot:icon>
                {{ __('app.new_visit') }}
            </x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    {{-- KPI --}}
    <div class="row g-3 mb-3">
        @foreach([
            [$stats['today'],   'bi-capsule-fill',   '#4154f1','#eef0fd', __('app.today'),   'Today'],
            [$stats['total'],   'bi-archive-fill',   '#2eca6a','#e8f8ef', __('app.total'),   'Total'],
            [$stats['doctors'], 'bi-person-badge-fill','#9b59b6','#f0e8ff',__('app.doctors'),'Prescribers'],
        ] as [$val,$ico,$col,$bg,$km,$en])
            <div class="col-4">
                <div class="stat-card">
                    <div class="stat-icon" style="background:{{ $bg }};color:{{ $col }}"><i class="bi {{ $ico }}"></i>
                    </div>
                    <div>
                        <div class="stat-num" style="color:{{ $col }};font-size:20px">{{ $val }}</div>
                        <div class="stat-lbl">{{ $km }}<br><small>{{ $en }}</small></div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Filter --}}
    <x-ui.card class="mb-3">
        <form method="GET" action="{{ route('prescriptions.index') }}">
            <div class="row g-2 align-items-end">
                <div class="col-12 col-sm-5">
                    <input type="text" name="search" class="form-control"
                           placeholder="{{ __('app.search') }}… ({{ __('app.patient.code') }}, code)"
                           value="{{ request('search') }}" autofocus/>
                </div>
                <div class="col-6 col-sm-3">
                    <input type="date" name="date" class="form-control" value="{{ request('date') }}"/>
                </div>
                <div class="col-6 col-sm-2">
                    <input type="text" name="doctor" class="form-control"
                           placeholder="{{ __('app.doctor') }}" value="{{ request('doctor') }}"/>
                </div>
                <div class="col-auto d-flex gap-2">
                    <x-ui.button type="submit" variant="primary">
                        <x-slot:icon><i class="bi bi-funnel-fill"></i></x-slot:icon>
                    </x-ui.button>
                    @if(request()->hasAny(['search','date','doctor']))
                        <x-ui.button href="{{ route('prescriptions.index') }}" variant="secondary">
                            <x-slot:icon><i class="bi bi-x-circle"></i></x-slot:icon>
                        </x-ui.button>
                    @endif
                </div>
            </div>
        </form>
    </x-ui.card>

    {{-- List --}}
    <x-ui.card title="{{ __('app.prescriptions') }}" km="{{ __('app.prescriptions') }}" icon="bi-capsule-fill" icon-color="#e91e8c" :no-padding="true">
        <x-slot:actions>
            <span style="font-size:11px;color:#aaa">{{ $prescriptions->total() }} {{ __('app.records') }}</span>
        </x-slot:actions>
        <div class="table-responsive">
            <table class="tbl">
                <thead>
                <tr>
                    <th>{{ __('app.date') }}</th>
                    <th>{{ __('app.code') }}</th>
                    <th>{{ __('app.patient.name') }}</th>
                    <th>{{ __('app.doctor') }}</th>
                    <th>{{ __('app.medications') }}</th>
                    <th>{{ __('app.visit.title') }}</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                @forelse($prescriptions as $rx)
                    <tr>
                        <td>
                            <div style="font-size:12px;font-weight:600;color:#012970">{{ $rx->prescribed_at?->format('d/m/Y') ?? '—' }}</div>
                            <div style="font-size:10px;color:#aaa">{{ $rx->prescribed_at?->format('H:i') }}</div>
                        </td>
                        <td><code style="font-size:11px;color:#e91e8c">{{ $rx->code }}</code></td>
                        <td>
                            @if($rx->patient)
                                <a href="{{ route('patients.show', $rx->patient->code) }}"
                                   style="font-weight:600;color:#012970;font-size:13px;text-decoration:none">
                                    {{ $rx->patient->surname }}, {{ $rx->patient->name }}
                                </a>
                                <div style="font-size:10.5px;color:#aaa">{{ $rx->patient_code }}</div>
                            @else
                                <span style="color:#bbb">—</span>
                            @endif
                        </td>
                        <td style="font-size:12px;color:#555">{{ $rx->prescribed_by ?? '—' }}</td>
                        <td>
                            <span style="font-size:13px;font-weight:700;color:#e91e8c">{{ $rx->medications->count() }}</span>
                            <span style="font-size:10px;color:#aaa"> {{ __('app.items') }}</span>
                            @if($rx->medications->count() > 0)
                                <div style="font-size:10.5px;color:#aaa;margin-top:1px">
                                    {{ $rx->medications->first()?->medicine_name }}
                                    @if($rx->medications->count() > 1)
                                        + {{ $rx->medications->count() - 1 }} {{ __('app.more') }}
                                    @endif
                                </div>
                            @endif
                        </td>
                        <td>
                            @if($rx->visit_code)
                                <a href="{{ url('/workflow/'.$rx->visit_code) }}"
                                   style="font-size:11px;color:#4154f1">
                                    {{ $rx->visit_code }}
                                </a>
                            @else
                                <span style="color:#ddd">—</span>
                            @endif
                        </td>
                        <td>
                            <div style="display:flex;gap:4px">
                                <x-ui.button href="{{ route('prescriptions.show', $rx->code) }}" variant="secondary" size="sm">
                                    <x-slot:icon><i class="bi bi-eye-fill"></i></x-slot:icon>
                                </x-ui.button>
                                <x-ui.button href="{{ route('print.prescription', $rx->code) }}" variant="secondary" size="sm" onclick="window.open(this.href,'_blank');return false;">
                                    <x-slot:icon><i class="bi bi-printer-fill"></i></x-slot:icon>
                                </x-ui.button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
                            <x-ui.empty-state icon="bi-capsule" title="{{ __('app.no_records') }}" description="Prescriptions are created during patient visits">
                                <x-ui.button href="{{ route('workflow.create') }}" variant="primary" size="sm">
                                    <x-slot:icon><i class="bi bi-plus-lg"></i></x-slot:icon>
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
