@extends('clinics.layout.app')
@section('title', 'Visit ' . $visit->code)

@section('content')

@php
    $s        = $summary;
    $patient  = $s['patient'];
    $v        = $s['visit'];
    $triage   = $s['triage'];
    $vitals   = $s['vitals'] ?? [];
    $soap     = $s['soap'] ?? null;
    $dx       = $s['diagnoses'] ?? [];
    $rxList   = $s['prescriptions'] ?? [];
    $labs     = $s['laboratories'] ?? [];
    $imgs     = $s['imageries'] ?? [];
    $invList  = $s['invoices'] ?? [];
    $typeColor = $v['type'] === 'IPD' ? '#ff771d' : '#4154f1';
@endphp

<x-ui.page-header
    km="ការចូលទស្សន"
    :title="'Visit: ' . $visit->code"
    :breadcrumbs="[
        ['label' => 'ដើម',    'url' => route('dashboard')],
        ['label' => 'Visits', 'url' => route('visits.index')],
        ['label' => $visit->code],
    ]">
    <x-slot:actions>
        @if($patient)
            <x-ui.button href="{{ route('patients.show', $patient->code) }}" variant="secondary" size="sm">
                <x-slot:icon><i class="bi bi-person-fill" aria-hidden="true"></i></x-slot:icon>
                Patient Profile
            </x-ui.button>
        @endif
        <x-ui.button href="{{ route('print.prescription', $visit->code) }}" variant="secondary" size="sm"
                     onclick="window.open(this.href,'_blank');return false;">
            <x-slot:icon><i class="bi bi-printer-fill" aria-hidden="true"></i></x-slot:icon>
            Print
        </x-ui.button>
    </x-slot:actions>
</x-ui.page-header>

{{-- Visit Header Banner --}}
<x-ui.card class="mb-4">
    <div class="flex flex-wrap gap-4 items-center">
        <div class="w-14 h-14 rounded-2xl flex items-center justify-center flex-shrink-0 text-white text-2xl"
             style="background:linear-gradient(135deg,{{ $typeColor }},{{ $typeColor }}cc)">
            <i class="bi bi-{{ $v['type'] === 'IPD' ? 'bed-fill' : 'person-fill' }}" aria-hidden="true"></i>
        </div>
        <div class="flex-1 min-w-48">
            <div class="flex items-center gap-2 flex-wrap mb-1">
                <span class="text-lg font-extrabold font-mono" style="color:#1a1f36">{{ $visit->code }}</span>
                <x-ui.badge :variant="$v['type'] === 'IPD' ? 'warning' : 'primary'">{{ $v['type'] }}</x-ui.badge>
                <x-ui.badge variant="success" dot>Completed</x-ui.badge>
                @if($v['priority'] === 'emergency')
                    <x-ui.badge variant="danger">🚨 Emergency</x-ui.badge>
                @endif
            </div>
            <div class="flex gap-4 flex-wrap text-xs" style="color:#6b7280">
                <span><i class="bi bi-calendar3" aria-hidden="true"></i> {{ $v['admitted_at']?->format('d/m/Y H:i') ?? '—' }}</span>
                @if($v['discharged_at'])
                    <span><i class="bi bi-door-open" aria-hidden="true"></i> {{ $v['discharged_at']->format('d/m/Y H:i') }}</span>
                @endif
                @if($v['admission_type'])
                    <span><i class="bi bi-tag-fill" aria-hidden="true"></i> {{ $v['admission_type'] }}</span>
                @endif
                <span><i class="bi bi-check2-all" aria-hidden="true"></i> {{ count($v['steps_done']) }}/10 steps</span>
            </div>
        </div>
        @if($patient)
            <div class="text-right flex-shrink-0">
                <div class="text-sm font-bold" style="color:#1a1f36">{{ $patient->surname }}, {{ $patient->name }}</div>
                <div class="text-xs font-mono" style="color:#6b7280">{{ $patient->code }}</div>
                @if($patient->birthdate)
                    <div class="text-xs" style="color:#6b7280">{{ $patient->birthdate->age }}y · {{ $patient->sex === 'M' ? '♂' : '♀' }}</div>
                @endif
            </div>
        @endif
    </div>
</x-ui.card>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

    {{-- ── LEFT COLUMN ──────────────────────────────────────────────────────── --}}
    <div class="space-y-4">

        {{-- Triage --}}
        @if($triage)
            <x-ui.card noPadding>
                <x-slot:header>
                    <x-ui.card-header label="ការត្រួតពិនិត្យ / Triage" icon="bi-clipboard2-pulse-fill" />
                </x-slot:header>
                @foreach([
                    ['bi-chat-text-fill',  'Chief Complaint', $triage['chief_complaint'] ?? '—'],
                    ['bi-speedometer2',    'Triage Level',    $triage['triage_level'] ?? '—'],
                    ['bi-arrows-vertical', 'Height',          ($triage['height'] ?? null) ? $triage['height'].' cm' : '—'],
                    ['bi-life-preserver',  'Weight',          ($triage['weight'] ?? null) ? $triage['weight'].' kg' : '—'],
                    ['bi-calculator',      'BMI',             ($triage['bmi'] ?? null) ? number_format($triage['bmi'],1) : '—'],
                ] as [$ico, $lbl, $val])
                    <div class="flex items-center gap-3 px-4 py-2.5 border-b" style="border-color:#f8f9ff">
                        <i class="bi {{ $ico }} text-xs flex-shrink-0" style="width:14px;color:#9ca3af"></i>
                        <div>
                            <div class="text-xs" style="color:#d1d5db">{{ $lbl }}</div>
                            <div class="text-xs {{ $val === '—' ? '' : 'font-semibold' }}"
                                 style="color:{{ $val === '—' ? '#d1d5db' : '#1a1f36' }}">{{ $val }}</div>
                        </div>
                    </div>
                @endforeach
            </x-ui.card>
        @endif

        {{-- Vitals --}}
        @if(!empty($vitals))
            <x-ui.card noPadding>
                <x-slot:header>
                    <x-ui.card-header label="សញ្ញាជីវិត / Vital Signs" icon="bi-heart-pulse-fill" />
                </x-slot:header>
                @foreach($vitals as $key => $val)
                    @if($val !== null)
                        <div class="flex items-center gap-3 px-4 py-2.5 border-b" style="border-color:#f8f9ff">
                            <div>
                                <div class="text-xs capitalize" style="color:#d1d5db">{{ str_replace('_',' ',$key) }}</div>
                                <div class="text-xs font-semibold" style="color:#1a1f36">{{ $val }}</div>
                            </div>
                        </div>
                    @endif
                @endforeach
            </x-ui.card>
        @endif

    </div>

    {{-- ── RIGHT COLUMN ─────────────────────────────────────────────────────── --}}
    <div class="lg:col-span-2 space-y-4">

        {{-- SOAP --}}
        @if($soap)
            <x-ui.card>
                <x-slot:header>
                    <x-ui.card-header label="ការពិគ្រោះ / SOAP Note" icon="bi-file-earmark-medical-fill" />
                </x-slot:header>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @foreach([['S','Subjective',$soap['subjective']??null],['O','Objective',$soap['objective']??null],['A','Assessment',$soap['assessment']??null],['P','Plan',$soap['plan']??null]] as [$letter,$label,$val])
                        @if($val)
                            <div>
                                <div class="text-xs font-bold mb-1" style="color:#4154f1">{{ $letter }} — {{ $label }}</div>
                                <div class="text-xs leading-relaxed whitespace-pre-line" style="color:#374151">{{ $val }}</div>
                            </div>
                        @endif
                    @endforeach
                </div>
            </x-ui.card>
        @endif

        {{-- Diagnoses --}}
        @if(!empty($dx))
            <x-ui.card noPadding>
                <x-slot:header>
                    <x-ui.card-header label="រោគវិនិច្ឆ័យ / Diagnoses" icon="bi-patch-check-fill" />
                </x-slot:header>
                @foreach($dx as $d)
                    <div class="flex items-center gap-3 px-4 py-3 border-b" style="border-color:#f8f9ff">
                        <div class="w-7 h-7 rounded-lg flex items-center justify-center flex-shrink-0"
                             style="background:#f0e8ff;color:#9b59b6">
                            <i class="bi bi-check2 text-xs" aria-hidden="true"></i>
                        </div>
                        <div class="flex-1">
                            <div class="text-xs font-semibold" style="color:#1a1f36">{{ $d['name'] ?? $d->diagnosis_name ?? '—' }}</div>
                            @if(!empty($d['icd_code'] ?? $d->icd_code ?? null))
                                <div class="text-xs font-mono" style="color:#9ca3af">{{ $d['icd_code'] ?? $d->icd_code }}</div>
                            @endif
                        </div>
                        @if(!empty($d['type'] ?? null))
                            <span class="text-xs px-2 py-0.5 rounded-md font-semibold"
                                  style="background:#f0e8ff;color:#9b59b6">{{ $d['type'] }}</span>
                        @endif
                    </div>
                @endforeach
            </x-ui.card>
        @endif

        {{-- Prescriptions --}}
        @if(!empty($rxList))
            <x-ui.card noPadding>
                <x-slot:header>
                    <x-ui.card-header label="វេជ្ជបញ្ជា / Prescriptions" icon="bi-capsule-fill" />
                </x-slot:header>
                @foreach($rxList as $rx)
                    @php
                        $ds = $rx->dispensed_status;
                        $dsVariant = $ds === 'dispensed' ? 'success' : ($ds === 'partial' ? 'warning' : 'primary');
                    @endphp
                    <a href="{{ route('prescriptions.show', $rx->code) }}"
                       class="flex items-center gap-3 px-4 py-3 border-b hover:bg-[#f9fafb] transition-colors"
                       style="border-color:#f8f9ff;text-decoration:none">
                        <div class="flex-1">
                            <div class="text-xs font-bold font-mono" style="color:#e91e8c">{{ $rx->code }}</div>
                            <div class="text-xs" style="color:#6b7280">{{ $rx->medications->count() }} medications · {{ $rx->created_at?->format('d/m/Y') }}</div>
                        </div>
                        <x-ui.badge :variant="$dsVariant" size="sm">{{ ucfirst($ds ?? 'pending') }}</x-ui.badge>
                        <i class="bi bi-chevron-right text-xs flex-shrink-0" style="color:#d1d5db"></i>
                    </a>
                @endforeach
            </x-ui.card>
        @endif

        {{-- Lab + Imaging --}}
        @if(!empty($labs) || !empty($imgs))
            <x-ui.card noPadding>
                <x-slot:header>
                    <x-ui.card-header label="Lab / Imaging" icon="bi-eyedropper" />
                </x-slot:header>
                @foreach($labs as $lab)
                    @php
                        $labVariant = match($lab->status) {
                            'pending'=>'secondary','collected'=>'warning','resulted'=>'success','verified'=>'primary',default=>'secondary'
                        };
                    @endphp
                    <a href="{{ route('laboratory.show', $lab->code) }}"
                       class="flex items-center gap-3 px-4 py-3 border-b hover:bg-[#f9fafb] transition-colors"
                       style="border-color:#f8f9ff;text-decoration:none">
                        <div class="w-7 h-7 rounded-lg flex items-center justify-center flex-shrink-0"
                             style="background:#e8f8ef;color:#2eca6a">
                            <i class="bi bi-droplet-fill text-xs" aria-hidden="true"></i>
                        </div>
                        <div class="flex-1">
                            <div class="text-xs font-semibold font-mono" style="color:#1a1f36">{{ $lab->code }}</div>
                            <div class="text-xs" style="color:#6b7280">{{ $lab->category ?? 'Lab' }} · {{ $lab->created_at?->format('d/m/Y') }}</div>
                        </div>
                        <x-ui.badge :variant="$labVariant" size="sm">{{ ucfirst($lab->status) }}</x-ui.badge>
                        <i class="bi bi-chevron-right text-xs flex-shrink-0" style="color:#d1d5db"></i>
                    </a>
                @endforeach
                @foreach($imgs as $img)
                    <a href="{{ route('imagery.show', $img->code) }}"
                       class="flex items-center gap-3 px-4 py-3 border-b hover:bg-[#f9fafb] transition-colors"
                       style="border-color:#f8f9ff;text-decoration:none">
                        <div class="w-7 h-7 rounded-lg flex items-center justify-center flex-shrink-0"
                             style="background:#fff3e8;color:#ff771d">
                            <i class="bi bi-camera-fill text-xs" aria-hidden="true"></i>
                        </div>
                        <div class="flex-1">
                            <div class="text-xs font-semibold font-mono" style="color:#1a1f36">{{ $img->code }}</div>
                            <div class="text-xs" style="color:#6b7280">{{ $img->category ?? 'Imaging' }} · {{ $img->created_at?->format('d/m/Y') }}</div>
                        </div>
                        <i class="bi bi-chevron-right text-xs flex-shrink-0" style="color:#d1d5db"></i>
                    </a>
                @endforeach
            </x-ui.card>
        @endif

        {{-- Invoices --}}
        @if(!empty($invList))
            <x-ui.card noPadding>
                <x-slot:header>
                    <x-ui.card-header label="វិក្កយបត្រ / Invoices" icon="bi-receipt-cutoff" />
                </x-slot:header>
                @foreach($invList as $inv)
                    @php
                        $invVariant = match($inv->status) {
                            'paid'=>'success','partial'=>'warning','pending'=>'danger','void'=>'secondary',default=>'secondary'
                        };
                    @endphp
                    <a href="{{ route('invoices.show', $inv->code) }}"
                       class="flex items-center gap-3 px-4 py-3 border-b hover:bg-[#f9fafb] transition-colors"
                       style="border-color:#f8f9ff;text-decoration:none">
                        <div class="flex-1">
                            <div class="text-xs font-bold font-mono" style="color:#9b59b6">{{ $inv->code }}</div>
                            <div class="text-xs" style="color:#6b7280">{{ $inv->created_at?->format('d/m/Y') }}</div>
                        </div>
                        <x-ui.badge :variant="$invVariant" size="sm">{{ ucfirst($inv->status) }}</x-ui.badge>
                        <span class="text-sm font-bold" style="color:#1a1f36">{{ number_format($inv->grand_total) }} KHR</span>
                        <i class="bi bi-chevron-right text-xs flex-shrink-0" style="color:#d1d5db"></i>
                    </a>
                @endforeach
            </x-ui.card>
        @endif

        @if(empty($soap) && empty($dx) && empty($rxList) && empty($labs) && empty($imgs) && empty($invList))
            <x-ui.card>
                <x-ui.empty-state icon="bi-hospital" title="No clinical data recorded" description="No clinical data recorded for this visit." />
            </x-ui.card>
        @endif

    </div>

</div>

@endsection
