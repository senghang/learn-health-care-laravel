@extends('clinics.layout.app')
@section('title', 'Rx ' . $prescription->code)
@section('content')

    <x-ui.page-header
        :km="$prescription->code"
        title="Prescription Detail"
        :breadcrumbs="[
            ['label' => __('app.home'), 'url' => route('dashboard')],
            ['label' => __('app.prescriptions'), 'url' => route('prescriptions.index')],
            ['label' => $prescription->code],
        ]">
        <x-slot:actions>
            <x-ui.button href="{{ route('print.prescription', $prescription->code) }}" variant="secondary" size="sm" onclick="window.open(this.href,'_blank');return false;">
                <x-slot:icon><i class="bi bi-printer-fill"></i></x-slot:icon>
                {{ __('app.print') }}
            </x-ui.button>
            <x-ui.button href="{{ route('prescriptions.edit', $prescription->code) }}" variant="secondary" size="sm">
                <x-slot:icon><i class="bi bi-pencil-fill"></i></x-slot:icon>
                Edit
            </x-ui.button>
            @if($prescription->visit_code)
                <x-ui.button href="{{ url('/workflow/'.$prescription->visit_code) }}" variant="primary" size="sm">
                    <x-slot:icon><i class="bi bi-diagram-3-fill"></i></x-slot:icon>
                    {{ __('app.open_visit') }}
                </x-ui.button>
            @endif
        </x-slot:actions>
    </x-ui.page-header>

    <div class="row g-3">

        {{-- Prescription header card --}}
        <div class="col-12 col-lg-8">
            <x-ui.card title="{{ __('app.prescription') }}" km="{{ __('app.prescription') }}" icon="bi-capsule-fill" icon-color="#e91e8c" class="mb-3">
                <x-slot:actions>
                    <span style="font-size:11px;color:#aaa">{{ $prescription->prescribed_at?->format('d/m/Y H:i') }}</span>
                </x-slot:actions>
                <div class="row g-3 mb-4">
                    <div class="col-6 col-sm-3">
                        <div style="font-size:10.5px;color:#aaa;font-weight:700;text-transform:uppercase;letter-spacing:.4px">{{ __('app.patient.name.name') }}</div>
                        <div style="font-weight:700;color:#012970;margin-top:3px">{{ $prescription->patient?->surname }}, {{ $prescription->patient?->name }}</div>
                        <div style="font-size:10.5px;color:#aaa">{{ $prescription->patient_code }}</div>
                    </div>
                    <div class="col-6 col-sm-3">
                        <div style="font-size:10.5px;color:#aaa;font-weight:700;text-transform:uppercase;letter-spacing:.4px">{{ __('app.doctor') }}</div>
                        <div style="font-weight:600;color:#555;margin-top:3px">{{ $prescription->prescribed_by ?? '—' }}</div>
                    </div>
                    <div class="col-6 col-sm-3">
                        <div style="font-size:10.5px;color:#aaa;font-weight:700;text-transform:uppercase;letter-spacing:.4px">{{ __('app.visit.code') }}</div>
                        <div style="margin-top:3px">
                            @if($prescription->visit_code)
                                <a href="{{ url('/workflow/'.$prescription->visit_code) }}"
                                   style="font-size:12px;color:#4154f1">{{ $prescription->visit_code }}</a>
                            @else
                                <span style="color:#ddd">—</span>
                            @endif
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
                                    @if($med->strength)
                                        <div style="font-size:10.5px;color:#aaa">{{ $med->strength }}</div>
                                    @endif
                                    @if($med->note)
                                        <div style="font-size:10.5px;color:#9b59b6;font-style:italic">{{ $med->note }}</div>
                                    @endif
                                </td>
                                <td>
                                    @if($med->form)
                                        <span style="font-size:11px;background:#f0f2ff;color:#4154f1;padding:2px 8px;border-radius:6px">{{ $med->form }}</span>
                                    @endif
                                    @if($med->method)
                                        <span style="font-size:10.5px;color:#aaa;margin-left:4px">{{ $med->method }}</span>
                                    @endif
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
                                    @if($med->unit)
                                        <span style="font-size:10px;color:#aaa">{{ $med->unit }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" style="text-align:center;color:#bbb;padding:20px">{{ __('app.no_medications') }}</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </x-ui.card>
        </div>

        {{-- Patient sidebar --}}
        <div class="col-12 col-lg-4">
            @if($prescription->patient)
                <x-ui.card title="{{ __('app.patient.name') }}" km="{{ __('app.patient.name') }}" icon="bi-person-vcard-fill" icon-color="#4154f1" style="position:sticky;top:76px">
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
                        <x-ui.button href="{{ route('patients.show', $pt->code) }}" variant="secondary" size="sm" :full-width="true">
                            <x-slot:icon><i class="bi bi-person-fill"></i></x-slot:icon>
                            {{ __('app.view_patient') }}
                        </x-ui.button>
                    </div>
                </x-ui.card>
            @endif

            {{-- Dispense Panel --}}
            @if($prescription->dispensed_status !== 'dispensed')
            <x-ui.card title="Dispense" km="Dispense" icon="bi-bag-heart-fill" icon-color="#7c3aed" class="mt-3">
                @if(session('success'))
                    <x-ui.alert type="success" class="mb-3">{{ session('success') }}</x-ui.alert>
                @endif
                @error('dispense')
                    <x-ui.alert type="error" class="mb-3">{{ $message }}</x-ui.alert>
                @enderror

                {{-- Current status badge --}}
                <div style="margin-bottom:12px">
                    @if(!$prescription->dispensed_status)
                        <span style="background:#fff3cd;color:#856404;border:1px solid #ffc107;font-size:11px;padding:3px 10px;border-radius:8px;font-weight:700">
                            ⏸ Not dispensed
                        </span>
                    @elseif($prescription->dispensed_status === 'partial')
                        <span style="background:#fff8e1;color:#b45309;border:1px solid #fde68a;font-size:11px;padding:3px 10px;border-radius:8px;font-weight:700">
                            ⏳ Partially dispensed
                        </span>
                        @if($prescription->dispensed_by)
                            <div style="font-size:10.5px;color:#aaa;margin-top:4px">by {{ $prescription->dispensed_by }}</div>
                        @endif
                    @endif
                </div>

                <form method="POST" action="{{ route('prescriptions.dispense', $prescription->code) }}">
                    @csrf
                    <div class="fld mb-2">
                        <label class="flbl" style="color:#7c3aed;font-size:11px;font-weight:800">Update Status</label>
                        <select name="dispensed_status" class="form-select form-select-sm" style="border-color:#c4b5fd">
                            <option value="partial">⏳ Partially dispensed</option>
                            <option value="dispensed">✅ Fully dispensed (decrements stock)</option>
                        </select>
                    </div>
                    <div class="fld mb-3">
                        <label class="flbl" style="color:#7c3aed;font-size:11px;font-weight:800">Dispensed By</label>
                        <input type="text" name="dispensed_by" class="form-control form-control-sm"
                               style="border-color:#c4b5fd"
                               placeholder="Pharmacist name"
                               value="{{ $prescription->dispensed_by ?? auth()->user()?->name ?? '' }}"/>
                    </div>
                    <button type="submit" class="btn btn-sm btn-w100"
                            style="background:#7c3aed;color:#fff;font-weight:700;padding:9px 0">
                        <i class="bi bi-bag-check-fill"></i> Confirm Dispense
                    </button>
                </form>
            </x-ui.card>
            @else
            <x-ui.card class="mt-3">
                <div style="text-align:center;padding:16px">
                    <div style="font-size:28px;margin-bottom:6px">✅</div>
                    <div style="font-weight:800;color:#1D9E75;font-size:13px">Fully Dispensed</div>
                    @if($prescription->dispensed_by)
                        <div style="font-size:11px;color:#aaa;margin-top:4px">by {{ $prescription->dispensed_by }}</div>
                    @endif
                </div>
            </x-ui.card>
            @endif
        </div>

    </div>
@endsection
