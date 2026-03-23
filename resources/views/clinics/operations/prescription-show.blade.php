@extends('clinics.layout.app')
@section('title', 'Rx ' . $prescription->code)
@section('content')

<x-page-header
    title="{{ $prescription->code }}"
    subtitle="Prescription Detail"
    :breadcrumbs="[
        ['label'=>__('app.home'),'url'=>route('dashboard')],
        ['label'=>__('app.prescriptions'),'url'=>route('prescriptions.index')],
        ['label'=>$prescription->code],
    ]">
    <a href="{{ route('print.prescription', $prescription->code) }}" class="btn btn-outline-primary btn-sm" target="_blank">
        <i class="bi bi-printer-fill"></i> {{ __('app.print') }}
    </a>
    @if($prescription->visit_code)
    <a href="{{ url('/workflow/'.$prescription->visit_code) }}" class="btn btn-primary btn-sm">
        <i class="bi bi-diagram-3-fill"></i> {{ __('app.open_visit') }}
    </a>
    @endif
</x-page-header>

<div class="row g-3">

{{-- Prescription header card --}}
<div class="col-12 col-lg-8">
    <div class="card-emr mb-3">
        <div class="card-hd" style="background:#fdf0f8">
            <div class="card-hd-title">
                <i class="bi bi-capsule-fill" style="color:#e91e8c"></i>
                {{ __('app.prescription') }}
                <code style="font-size:12px;color:#e91e8c;background:#fce4f4;padding:2px 8px;border-radius:6px">{{ $prescription->code }}</code>
            </div>
            <span style="font-size:11px;color:#aaa">{{ $prescription->prescribed_at?->format('d/m/Y H:i') }}</span>
        </div>
        <div class="card-bd">
            <div class="row g-3 mb-4">
                <div class="col-6 col-sm-3">
                    <div style="font-size:10.5px;color:#aaa;font-weight:700;text-transform:uppercase;letter-spacing:.4px">{{ __('app.patient') }}</div>
                    <div style="font-weight:700;color:#012970;margin-top:3px">{{ $prescription->patient?->surname }}, {{ $prescription->patient?->name }}</div>
                    <div style="font-size:10.5px;color:#aaa">{{ $prescription->patient_code }}</div>
                </div>
                <div class="col-6 col-sm-3">
                    <div style="font-size:10.5px;color:#aaa;font-weight:700;text-transform:uppercase;letter-spacing:.4px">{{ __('app.doctor') }}</div>
                    <div style="font-weight:600;color:#555;margin-top:3px">{{ $prescription->prescribed_by ?? '—' }}</div>
                </div>
                <div class="col-6 col-sm-3">
                    <div style="font-size:10.5px;color:#aaa;font-weight:700;text-transform:uppercase;letter-spacing:.4px">{{ __('app.visit') }}</div>
                    <div style="margin-top:3px">
                        @if($prescription->visit_code)
                        <a href="{{ url('/workflow/'.$prescription->visit_code) }}" style="font-size:12px;color:#4154f1">{{ $prescription->visit_code }}</a>
                        @else <span style="color:#ddd">—</span> @endif
                    </div>
                </div>
                <div class="col-6 col-sm-3">
                    <div style="font-size:10.5px;color:#aaa;font-weight:700;text-transform:uppercase;letter-spacing:.4px">{{ __('app.medications_count') }}</div>
                    <div style="font-size:22px;font-weight:800;color:#e91e8c;margin-top:2px">{{ $prescription->medications->count() }}</div>
                </div>
            </div>

            {{-- Medications table --}}
            <div style="font-size:10.5px;font-weight:800;text-transform:uppercase;letter-spacing:.5px;color:#e91e8c;margin-bottom:10px">
                {{ __('app.medications') }}
            </div>
            <div class="table-responsive">
                <table class="tbl">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ __('app.medicine') }}</th>
                            <th>{{ __('app.form') }}</th>
                            <th style="text-align:center">{{ __('app.morning') }}</th>
                            <th style="text-align:center">{{ __('app.afternoon') }}</th>
                            <th style="text-align:center">{{ __('app.evening') }}</th>
                            <th style="text-align:center">{{ __('app.night') }}</th>
                            <th style="text-align:center">{{ __('app.days') }}</th>
                            <th>{{ __('app.total_qty') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($prescription->medications as $i => $med)
                    <tr>
                        <td style="color:#aaa;font-size:11px">{{ $i+1 }}</td>
                        <td>
                            <div style="font-weight:700;color:#012970">{{ $med->medicine_name }}</div>
                            @if($med->strength)<div style="font-size:10.5px;color:#aaa">{{ $med->strength }}</div>@endif
                            @if($med->note)<div style="font-size:10.5px;color:#9b59b6;font-style:italic">{{ $med->note }}</div>@endif
                        </td>
                        <td>
                            @if($med->form)<span style="font-size:11px;background:#f0f2ff;color:#4154f1;padding:2px 8px;border-radius:6px">{{ $med->form }}</span>@endif
                            @if($med->method)<span style="font-size:10.5px;color:#aaa;margin-left:4px">{{ $med->method }}</span>@endif
                        </td>
                        @foreach(['morning','afternoon','evening','night'] as $slot)
                        <td style="text-align:center;font-weight:{{ $med->{$slot} > 0 ? '700' : '400' }};color:{{ $med->{$slot} > 0 ? '#012970' : '#ddd' }}">
                            {{ $med->{$slot} > 0 ? $med->{$slot} : '—' }}
                        </td>
                        @endforeach
                        <td style="text-align:center;font-weight:700;color:#4154f1">{{ $med->days ?? '—' }}</td>
                        <td>
                            @php $total = (($med->morning??0)+($med->afternoon??0)+($med->evening??0)+($med->night??0)) * ($med->days??1); @endphp
                            <span style="font-weight:800;color:#e91e8c">{{ $total > 0 ? $total : '—' }}</span>
                            @if($med->unit) <span style="font-size:10px;color:#aaa">{{ $med->unit }}</span>@endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="9" style="text-align:center;color:#bbb;padding:20px">{{ __('app.no_medications') }}</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Patient sidebar --}}
<div class="col-12 col-lg-4">
    @if($prescription->patient)
    <div class="card-emr" style="position:sticky;top:76px">
        <div class="card-hd" style="background:#f6f9ff">
            <div class="card-hd-title"><i class="bi bi-person-vcard-fill" style="color:#4154f1"></i> {{ __('app.patient') }}</div>
        </div>
        <div class="card-bd">
            @php
                $pt = $prescription->patient;
                $colors = ['#4154f1','#2eca6a','#ff771d','#e74c3c','#9b59b6'];
                $col = $colors[abs(crc32($pt->code)) % count($colors)];
            @endphp
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:14px">
                <div style="width:44px;height:44px;border-radius:12px;background:{{ $col }};color:#fff;display:flex;align-items:center;justify-content:center;font-size:16px;font-weight:800;flex-shrink:0">
                    {{ strtoupper(substr($pt->surname,0,1).substr($pt->name,0,1)) }}
                </div>
                <div>
                    <div style="font-weight:800;font-size:14px;color:#012970">{{ $pt->surname }}, {{ $pt->name }}</div>
                    <code style="font-size:11px;color:#4154f1">{{ $pt->code }}</code>
                </div>
            </div>
            @foreach([
                [__('app.gender'),      $pt->gender === 'M' ? __('app.male') : __('app.female')],
                [__('app.birthdate'),   $pt->birthdate?->format('d/m/Y') ?? '—'],
                [__('app.phone'),       $pt->phone ?? '—'],
                [__('app.nationality'), $pt->nationality ?? '—'],
            ] as [$lbl, $val])
            <div style="display:flex;justify-content:space-between;margin-bottom:6px;font-size:12px">
                <span style="color:#aaa">{{ $lbl }}</span>
                <span style="font-weight:600;color:#333">{{ $val }}</span>
            </div>
            @endforeach
            <div style="margin-top:12px;padding-top:12px;border-top:1px solid #f0f2ff">
                <a href="{{ route('patients.show', $pt->code) }}" class="btn btn-outline-primary btn-sm btn-w100">
                    <i class="bi bi-person-fill"></i> {{ __('app.view_patient') }}
                </a>
            </div>
        </div>
    </div>
    @endif
</div>

</div>
@endsection
