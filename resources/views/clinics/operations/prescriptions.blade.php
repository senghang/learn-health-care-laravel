@extends('clinics.layout.app')
@section('title', __('app.prescriptions'))
@section('content')

    <x-page-header
        :title="__('app.prescriptions')"
        subtitle="Prescriptions"
        :breadcrumbs="[['label'=>__('app.home'),'url'=>route('dashboard')],['label'=>__('app.prescriptions')]]">
        <a href="{{ route('prescriptions.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg"></i> New Prescription
        </a>
        <a href="{{ route('workflow.create') }}" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-diagram-3-fill"></i> {{ __('app.new_visit') }}
        </a>
    </x-page-header>

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
    <div class="card-emr mb-3">
        <div class="card-bd">
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
                        <button type="submit" class="btn btn-primary"><i class="bi bi-funnel-fill"></i></button>
                        @if(request()->hasAny(['search','date','doctor']))
                            <a href="{{ route('prescriptions.index') }}" class="btn btn-outline-secondary"><i
                                    class="bi bi-x-circle"></i></a>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- List --}}
    <div class="card-emr">
        <div class="card-hd">
            <div class="card-hd-title"><i class="bi bi-capsule-fill" style="color:#e91e8c"></i>
                {{ __('app.prescriptions') }}
            </div>
            <span style="font-size:11px;color:#aaa">{{ $prescriptions->total() }} {{ __('app.records') }}</span>
        </div>
        <div class="card-bd" style="padding:0">
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
                                <div
                                    style="font-size:12px;font-weight:600;color:#012970">{{ $rx->prescribed_at?->format('d/m/Y') ?? '—' }}</div>
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
                                <span
                                    style="font-size:13px;font-weight:700;color:#e91e8c">{{ $rx->medications->count() }}</span>
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
                                    <a href="{{ route('prescriptions.show', $rx->code) }}"
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye-fill"></i>
                                    </a>
                                    <a href="{{ route('print.prescription', $rx->code) }}"
                                       class="btn btn-sm btn-outline-secondary" target="_blank">
                                        <i class="bi bi-printer-fill"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align:center;padding:40px;color:#bbb">
                                <div style="font-size:36px;margin-bottom:10px;opacity:.3">💊</div>
                                <div
                                    style="font-size:13px;font-weight:600;margin-bottom:6px">{{ __('app.no_records') }}</div>
                                <a href="{{ route('workflow.create') }}" class="btn btn-primary btn-sm">
                                    <i class="bi bi-plus-lg"></i> {{ __('app.new_visit') }}
                                </a>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if($prescriptions->hasPages())
        <div class="mt-3">{{ $prescriptions->links() }}</div>
    @endif

@endsection
