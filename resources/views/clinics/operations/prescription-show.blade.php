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
        <x-ui.button href="{{ route('print.prescription', $prescription->code) }}" variant="secondary" size="sm"
                     onclick="window.open(this.href,'_blank');return false;">
            <x-slot:icon><i class="bi bi-printer-fill" aria-hidden="true"></i></x-slot:icon>
            {{ __('app.print') }}
        </x-ui.button>
        <x-ui.button href="{{ route('prescriptions.edit', $prescription->code) }}" variant="secondary" size="sm">
            <x-slot:icon><i class="bi bi-pencil-fill" aria-hidden="true"></i></x-slot:icon>
            Edit
        </x-ui.button>
        @if($prescription->visit_code)
            <x-ui.button href="{{ url('/workflow/'.$prescription->visit_code) }}" variant="primary" size="sm">
                <x-slot:icon><i class="bi bi-diagram-3-fill" aria-hidden="true"></i></x-slot:icon>
                {{ __('app.open_visit') }}
            </x-ui.button>
        @endif
    </x-slot:actions>
</x-ui.page-header>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

    {{-- ── Prescription main card ──────────────────────────────────────── --}}
    <div class="lg:col-span-2 space-y-4">
        <x-ui.card>
            <x-slot:header>
                <x-ui.card-header label="{{ __('app.prescription') }}" icon="bi-capsule-fill">
                    <x-slot:actions>
                        <span class="text-xs" style="color:#9ca3af">{{ $prescription->prescribed_at?->format('d/m/Y H:i') }}</span>
                    </x-slot:actions>
                </x-ui.card-header>
            </x-slot:header>

            {{-- Meta --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-5">
                <div>
                    <div class="text-xs font-bold uppercase tracking-wide mb-1" style="color:#9ca3af">{{ __('app.patient.name.name') }}</div>
                    <div class="text-sm font-bold" style="color:#1a1f36">{{ $prescription->patient?->surname }}, {{ $prescription->patient?->name }}</div>
                    <div class="text-xs" style="color:#9ca3af">{{ $prescription->patient_code }}</div>
                </div>
                <div>
                    <div class="text-xs font-bold uppercase tracking-wide mb-1" style="color:#9ca3af">{{ __('app.doctor') }}</div>
                    <div class="text-sm font-semibold" style="color:#374151">{{ $prescription->prescribed_by ?? '—' }}</div>
                </div>
                <div>
                    <div class="text-xs font-bold uppercase tracking-wide mb-1" style="color:#9ca3af">{{ __('app.visit.code') }}</div>
                    <div class="mt-0.5">
                        @if($prescription->visit_code)
                            <a href="{{ url('/workflow/'.$prescription->visit_code) }}"
                               class="text-xs hover:underline" style="color:#4154f1">{{ $prescription->visit_code }}</a>
                        @else
                            <span style="color:#d1d5db">—</span>
                        @endif
                    </div>
                </div>
                <div>
                    <div class="text-xs font-bold uppercase tracking-wide mb-1" style="color:#9ca3af">{{ __('app.medications_count') }}</div>
                    <div class="text-2xl font-extrabold" style="color:#e91e8c">{{ $prescription->medications->count() }}</div>
                </div>
            </div>

            {{-- Medications table --}}
            <div class="text-xs font-extrabold uppercase tracking-wider mb-3" style="color:#e91e8c">
                {{ __('app.medications') }}
            </div>
            <div class="overflow-x-auto rounded-xl border" style="border-color:#e6eaf5">
                <table class="w-full text-sm">
                    <thead>
                        <tr style="background:#f9fafb;border-bottom:1px solid #e6eaf5">
                            <th class="text-left text-xs font-semibold px-3 py-2.5" style="color:#6b7280;width:30px">#</th>
                            <th class="text-left text-xs font-semibold px-3 py-2.5" style="color:#6b7280">{{ __('app.medicine') }}</th>
                            <th class="text-left text-xs font-semibold px-3 py-2.5 hidden sm:table-cell" style="color:#6b7280">{{ __('app.form') }}</th>
                            <th class="text-center text-xs font-semibold px-2 py-2.5" style="color:#6b7280">{{ __('app.morning') }}</th>
                            <th class="text-center text-xs font-semibold px-2 py-2.5 hidden sm:table-cell" style="color:#6b7280">{{ __('app.afternoon') }}</th>
                            <th class="text-center text-xs font-semibold px-2 py-2.5 hidden sm:table-cell" style="color:#6b7280">{{ __('app.evening') }}</th>
                            <th class="text-center text-xs font-semibold px-2 py-2.5 hidden sm:table-cell" style="color:#6b7280">{{ __('app.night') }}</th>
                            <th class="text-center text-xs font-semibold px-2 py-2.5" style="color:#6b7280">{{ __('app.days') }}</th>
                            <th class="text-left text-xs font-semibold px-3 py-2.5" style="color:#6b7280">{{ __('app.total_qty') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($prescription->medications as $i => $med)
                        <tr class="border-b" style="border-color:#f1f5f9">
                            <td class="px-3 py-3 text-xs" style="color:#9ca3af">{{ $i+1 }}</td>
                            <td class="px-3 py-3">
                                <div class="text-sm font-bold" style="color:#1a1f36">{{ $med->medicine_name }}</div>
                                @if($med->strength)
                                    <div class="text-xs" style="color:#9ca3af">{{ $med->strength }}</div>
                                @endif
                                @if($med->note)
                                    <div class="text-xs italic" style="color:#9b59b6">{{ $med->note }}</div>
                                @endif
                            </td>
                            <td class="px-3 py-3 hidden sm:table-cell">
                                @if($med->form)
                                    <span class="text-xs px-2 py-0.5 rounded-md font-semibold" style="background:#e6e9f0;color:#4154f1">{{ $med->form }}</span>
                                @endif
                                @if($med->method)
                                    <span class="text-xs ml-1" style="color:#9ca3af">{{ $med->method }}</span>
                                @endif
                            </td>
                            @foreach(['morning','afternoon','evening','night'] as $slot)
                                <td class="px-2 py-3 text-center text-sm {{ $slot !== 'morning' ? 'hidden sm:table-cell' : '' }}"
                                    style="font-weight:{{ $med->{$slot} > 0 ? '700' : '400' }};color:{{ $med->{$slot} > 0 ? '#1a1f36' : '#d1d5db' }}">
                                    {{ $med->{$slot} > 0 ? $med->{$slot} : '—' }}
                                </td>
                            @endforeach
                            <td class="px-2 py-3 text-center text-sm font-bold" style="color:#4154f1">{{ $med->days ?? '—' }}</td>
                            <td class="px-3 py-3">
                                @php $total = (($med->morning??0)+($med->afternoon??0)+($med->evening??0)+($med->night??0)) * ($med->days??1); @endphp
                                <span class="text-sm font-extrabold" style="color:#e91e8c">{{ $total > 0 ? $total : '—' }}</span>
                                @if($med->unit)
                                    <span class="text-xs" style="color:#9ca3af"> {{ $med->unit }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-6 text-center text-sm" style="color:#d1d5db">{{ __('app.no_medications') }}</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui.card>
    </div>

    {{-- ── Sidebar ──────────────────────────────────────────────────────── --}}
    <div class="space-y-4">

        {{-- Patient info --}}
        @if($prescription->patient)
            @php
                $pt = $prescription->patient;
                $colors = ['#4154f1','#2eca6a','#ff771d','#e74c3c','#9b59b6'];
                $col = $colors[abs(crc32($pt->code)) % count($colors)];
            @endphp
            <x-ui.card class="sticky top-20">
                <x-slot:header>
                    <x-ui.card-header label="{{ __('app.patient.name') }}" icon="bi-person-vcard-fill" />
                </x-slot:header>

                <div class="flex items-center gap-3 mb-4">
                    <div class="w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0 text-white text-sm font-extrabold"
                         style="background:{{ $col }}">
                        {{ strtoupper(substr($pt->surname,0,1).substr($pt->name,0,1)) }}
                    </div>
                    <div>
                        <div class="text-sm font-extrabold" style="color:#1a1f36">{{ $pt->surname }}, {{ $pt->name }}</div>
                        <code class="text-xs" style="color:#4154f1">{{ $pt->code }}</code>
                    </div>
                </div>

                <div class="space-y-2 mb-4">
                    @foreach([
                        [__('app.gender'),      $pt->gender === 'M' ? __('app.male') : __('app.female')],
                        [__('app.birthdate'),   $pt->birthdate?->format('d/m/Y') ?? '—'],
                        [__('app.phone'),       $pt->phone ?? '—'],
                        [__('app.nationality'), $pt->nationality ?? '—'],
                    ] as [$lbl, $val])
                        <div class="flex justify-between items-center text-xs">
                            <span style="color:#9ca3af">{{ $lbl }}</span>
                            <span class="font-semibold" style="color:#374151">{{ $val }}</span>
                        </div>
                    @endforeach
                </div>

                <div class="border-t pt-3" style="border-color:#e6eaf5">
                    <x-ui.button href="{{ route('patients.show', $pt->code) }}" variant="secondary" size="sm" :fullWidth="true">
                        <x-slot:icon><i class="bi bi-person-fill" aria-hidden="true"></i></x-slot:icon>
                        {{ __('app.view_patient') }}
                    </x-ui.button>
                </div>
            </x-ui.card>
        @endif

        {{-- Dispense Panel --}}
        @if($prescription->dispensed_status !== 'dispensed')
            <x-ui.card>
                <x-slot:header>
                    <x-ui.card-header label="Dispense" icon="bi-bag-heart-fill" />
                </x-slot:header>

                @if(session('success'))
                    <x-ui.alert type="success" class="mb-3">{{ session('success') }}</x-ui.alert>
                @endif
                @error('dispense')
                    <x-ui.alert type="error" class="mb-3">{{ $message }}</x-ui.alert>
                @enderror

                {{-- Current status --}}
                <div class="mb-4">
                    @if(!$prescription->dispensed_status)
                        <x-ui.badge variant="warning">Not dispensed</x-ui.badge>
                    @elseif($prescription->dispensed_status === 'partial')
                        <x-ui.badge variant="warning">Partially dispensed</x-ui.badge>
                        @if($prescription->dispensed_by)
                            <div class="text-xs mt-1" style="color:#9ca3af">by {{ $prescription->dispensed_by }}</div>
                        @endif
                    @endif
                </div>

                <form method="POST" action="{{ route('prescriptions.dispense', $prescription->code) }}">
                    @csrf
                    <div class="space-y-3">
                        <div class="space-y-1.5">
                            <label class="block text-xs font-semibold" style="color:#374151">Update Status</label>
                            <x-forms.select name="dispensed_status">
                                <option value="partial">Partially dispensed</option>
                                <option value="dispensed">Fully dispensed (decrements stock)</option>
                            </x-forms.select>
                        </div>
                        <div class="space-y-1.5">
                            <label class="block text-xs font-semibold" style="color:#374151">Dispensed By</label>
                            <x-forms.input type="text" name="dispensed_by"
                                           placeholder="Pharmacist name"
                                           :value="$prescription->dispensed_by ?? auth()->user()?->name ?? ''" />
                        </div>
                        <x-ui.button type="submit" variant="primary" :fullWidth="true">
                            <x-slot:icon><i class="bi bi-bag-check-fill" aria-hidden="true"></i></x-slot:icon>
                            Confirm Dispense
                        </x-ui.button>
                    </div>
                </form>
            </x-ui.card>
        @else
            <x-ui.card>
                <div class="flex flex-col items-center justify-center py-4 text-center">
                    <div class="w-12 h-12 rounded-full flex items-center justify-center mb-3" style="background:#e8f8ef">
                        <i class="bi bi-check-circle-fill text-xl" style="color:#2eca6a"></i>
                    </div>
                    <div class="text-sm font-extrabold mb-1" style="color:#1D9E75">Fully Dispensed</div>
                    @if($prescription->dispensed_by)
                        <div class="text-xs" style="color:#9ca3af">by {{ $prescription->dispensed_by }}</div>
                    @endif
                </div>
            </x-ui.card>
        @endif

    </div>

</div>
@endsection
