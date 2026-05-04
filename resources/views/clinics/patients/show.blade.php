@extends('clinics.layout.app')
@section('title', $patient->surname . ', ' . $patient->name)

@section('content')

@php
    $ageStr   = $patient->birthdate
        ? $patient->birthdate->age . 'y (' . $patient->birthdate->format('d/m/Y') . ')'
        : '—';
    $colors   = ['#4154f1', '#2eca6a', '#ff771d', '#e74c3c', '#9b59b6', '#00bcd4', '#f39c12', '#1abc9c'];
    $color    = $colors[abs(crc32($patient->code)) % count($colors)];
    $initials = strtoupper(substr($patient->surname, 0, 1) . substr($patient->name, 0, 1));
    $lastVisit = $patient->visits->first();
@endphp

<x-ui.page-header :km="$patient->surname . ', ' . $patient->name" title="Patient Profile" :breadcrumbs="[
    ['label' => 'ដើម', 'url' => url('/')],
    ['label' => 'អ្នកជំងឺ', 'url' => url('/patients')],
    ['label' => $patient->code],
]">
    <x-slot:actions>
        <x-ui.button href="{{ url('/patients/' . $patient->code . '/edit') }}" variant="secondary" size="sm">
            <x-slot:icon><i class="bi bi-pencil-fill" aria-hidden="true"></i></x-slot:icon>
            កែប្រែ
        </x-ui.button>
        <x-ui.button href="{{ url('/workflow/create') }}?patient_code={{ $patient->code }}" variant="primary" size="sm">
            <x-slot:icon><i class="bi bi-plus-lg" aria-hidden="true"></i></x-slot:icon>
            ការចូលថ្មី
        </x-ui.button>
    </x-slot:actions>
</x-ui.page-header>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

    {{-- ══ LEFT: Profile ═══════════════════════════════════════════════════════ --}}
    <div class="space-y-4">

        {{-- Avatar + Core Info --}}
        <x-ui.card>
            <div class="text-center pt-2 pb-4">
                <div class="w-18 h-18 rounded-2xl flex items-center justify-center mx-auto mb-3 text-white text-2xl font-extrabold"
                     style="width:72px;height:72px;background:linear-gradient(135deg,{{ $color }},{{ $color }}cc)">
                    {{ $initials }}
                </div>
                <div class="text-lg font-extrabold" style="color:#1a1f36">{{ $patient->surname }}, {{ $patient->name }}</div>
                <div class="text-xs font-mono mt-1" style="color:#9ca3af">{{ $patient->code }}</div>
                <div class="flex justify-center gap-1.5 mt-3 flex-wrap">
                    <x-ui.badge :variant="$patient->sex === 'M' ? 'primary' : 'warning'">
                        {{ $patient->sex === 'M' ? '♂ ប្រុស' : '♀ ស្រី' }}
                    </x-ui.badge>
                    @if(($patient->visits_count ?? $patient->visits->count()) > 0)
                        <x-ui.badge variant="primary">{{ $patient->visits_count ?? $patient->visits->count() }} Visits</x-ui.badge>
                    @else
                        <x-ui.badge variant="success">New Patient</x-ui.badge>
                    @endif
                    @if($patient->spid)
                        <x-ui.badge variant="warning">SPID</x-ui.badge>
                    @endif
                    @if($patient->blood_type)
                        <x-ui.badge variant="danger">{{ $patient->blood_type }}</x-ui.badge>
                    @endif
                    @if($patient->status === 'Active')
                        <x-ui.badge variant="success" dot>{{ $patient->status }}</x-ui.badge>
                    @else
                        <x-ui.badge variant="secondary">{{ $patient->status }}</x-ui.badge>
                    @endif
                </div>
            </div>

            <div class="border-t" style="border-color:#e6eaf5">
                @foreach([
                    ['bi-calendar3',       'ថ្ងៃខែឆ្នាំ / DOB',      $ageStr],
                    ['bi-telephone-fill',  'ទូរស័ព្ទ / Phone',        $patient->phone ?? '—'],
                    ['bi-flag-fill',       'សញ្ជាតិ / Nationality',   $patient->nationality ?? '—'],
                    ['bi-briefcase-fill',  'មុខរបរ / Occupation',     $patient->occupation ?? '—'],
                    ['bi-heart-fill',      'អាពាហ៍ / Marital',        $patient->marital_status ?? '—'],
                    ['bi-shield-fill',     'SPID',                     $patient->spid ?? '—'],
                ] as [$icon, $label, $val])
                    <div class="flex items-center gap-3 px-4 py-2.5 border-b" style="border-color:#f8f9ff">
                        <i class="bi {{ $icon }} text-xs flex-shrink-0" style="width:14px;text-align:center;color:#9ca3af"></i>
                        <div class="flex-1 min-w-0">
                            <div class="text-xs" style="color:#d1d5db">{{ $label }}</div>
                            <div class="text-xs {{ $val === '—' ? '' : 'font-semibold' }}"
                                 style="color:{{ $val === '—' ? '#d1d5db' : '#1a1f36' }}">{{ $val }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </x-ui.card>

        {{-- Address --}}
        @if($patient->address)
            @php $addr = $patient->address; @endphp
            <x-ui.card>
                <x-slot:header>
                    <x-ui.card-header label="Address / អាសយដ្ឋាន" icon="bi-geo-alt-fill" />
                </x-slot:header>
                <div class="space-y-1.5 text-xs leading-relaxed" style="color:#374151">
                    @if($addr->house_number || $addr->street_number)
                        <div><i class="bi bi-house-fill me-1" style="color:#d1d5db"></i>
                            ផ្ទះ{{ $addr->house_number ?? '' }} ផ្លូវ{{ $addr->street_number ?? '' }}
                        </div>
                    @endif
                    @if($addr->village_name)
                        <div><i class="bi bi-map me-1" style="color:#d1d5db"></i> ភូមិ{{ $addr->village_name }}</div>
                    @endif
                    @if($addr->commune_name)
                        <div><i class="bi bi-signpost me-1" style="color:#d1d5db"></i> ឃុំ{{ $addr->commune_name }}</div>
                    @endif
                    @if($addr->district_name)
                        <div><i class="bi bi-building me-1" style="color:#d1d5db"></i> ស្រុក{{ $addr->district_name }}</div>
                    @endif
                    @if($addr->province_name)
                        <div><i class="bi bi-geo me-1" style="color:#d1d5db"></i> ខេត្ត{{ $addr->province_name }}</div>
                    @endif
                    @if(!$addr->province_name && !$addr->district_name && !$addr->village_name)
                        <div style="color:#d1d5db">— No address recorded —</div>
                    @endif
                </div>
            </x-ui.card>
        @endif

        {{-- ID Cards --}}
        @if($patient->identifications->isNotEmpty())
            <x-ui.card noPadding>
                <x-slot:header>
                    <x-ui.card-header label="ID Cards / ប័ណ្ណ" icon="bi-credit-card-fill" />
                </x-slot:header>
                @foreach($patient->identifications as $idCard)
                    <div class="flex items-center gap-3 px-4 py-3 border-b" style="border-color:#f8f9ff">
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0 text-white"
                             style="background:linear-gradient(135deg,#9b59b6,#7c3aed)">
                            <i class="bi bi-credit-card text-sm" aria-hidden="true"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="text-xs font-bold" style="color:#1a1f36">{{ $idCard->card_code }}</div>
                            <div class="text-xs" style="color:#9ca3af">{{ $idCard->card_type }}</div>
                        </div>
                    </div>
                @endforeach
            </x-ui.card>
        @endif

        {{-- Emergency Contacts --}}
        @if($patient->contacts->isNotEmpty())
            <x-ui.card noPadding>
                <x-slot:header>
                    <x-ui.card-header label="Contacts / ទំនាក់ទំនង" icon="bi-people-fill" />
                </x-slot:header>
                @foreach($patient->contacts as $contact)
                    <div class="flex items-center gap-3 px-4 py-3 border-b" style="border-color:#f8f9ff">
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0 text-white"
                             style="background:linear-gradient(135deg,{{ $contact->is_emergency ? '#e74c3c' : '#2eca6a' }},{{ $contact->is_emergency ? '#c0392b' : '#27ae60' }})">
                            <i class="bi bi-{{ $contact->is_emergency ? 'exclamation-triangle-fill' : 'person-fill' }} text-sm" aria-hidden="true"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="text-xs font-bold" style="color:#1a1f36">
                                {{ $contact->contact_name }}
                                @if($contact->is_emergency)
                                    <span class="ml-1 text-xs px-1.5 py-0.5 rounded-md font-bold" style="background:#fce4ec;color:#e74c3c">EMERGENCY</span>
                                @endif
                            </div>
                            <div class="text-xs" style="color:#9ca3af">
                                {{ $contact->relationship ?? '' }}
                                @if($contact->contact_phone) · {{ $contact->contact_phone }} @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </x-ui.card>
        @endif

    </div>{{-- /left --}}

    {{-- ══ RIGHT: Tabbed History ════════════════════════════════════════════════ --}}
    <div class="lg:col-span-2">

        @php
            $opdVisits = $patient->visits->where('visit_type', 'OPD');
            $ipdVisits = $patient->visits->where('visit_type', 'IPD');
        @endphp

        <x-ui.card noPadding>
            {{-- Tab Nav --}}
            <div class="flex items-center justify-between border-b overflow-x-auto" style="border-color:#e6eaf5">
                <div class="flex">
                    @php
                        $tabs = [
                            ['id' => 'opd',     'icon' => 'bi-person-fill',     'label' => 'OPD',          'count' => $opdVisits->count(),                 'color' => '#4154f1'],
                            ['id' => 'ipd',     'icon' => 'bi-bed-fill',        'label' => 'IPD',          'count' => $ipdVisits->count(),                 'color' => '#ff771d'],
                            ['id' => 'rx',      'icon' => 'bi-capsule-fill',    'label' => 'Prescriptions','count' => $patient->prescriptions->count(),    'color' => '#e91e8c'],
                            ['id' => 'lab',     'icon' => 'bi-eyedropper',      'label' => 'Lab Results',  'count' => $patient->laboratories->count(),     'color' => '#2eca6a'],
                            ['id' => 'billing', 'icon' => 'bi-receipt-cutoff',  'label' => 'Billing',      'count' => $patient->invoices->count(),         'color' => '#9b59b6'],
                        ];
                    @endphp
                    @foreach($tabs as $tab)
                        <button onclick="switchTab('{{ $tab['id'] }}')" id="tab-btn-{{ $tab['id'] }}"
                                class="patient-tab-btn flex items-center gap-1.5 px-4 py-3 text-xs font-bold border-none bg-transparent cursor-pointer whitespace-nowrap transition-all"
                                style="color:#6b7280;border-bottom:2px solid transparent;margin-bottom:-1px">
                            <i class="bi {{ $tab['icon'] }}" aria-hidden="true"></i>
                            {{ $tab['label'] }}
                            @if($tab['count'] > 0)
                                <span class="text-xs font-extrabold px-1.5 py-0.5 rounded-full"
                                      style="background:{{ $tab['color'] }}18;color:{{ $tab['color'] }}">{{ $tab['count'] }}</span>
                            @endif
                        </button>
                    @endforeach
                </div>
                <div class="px-3 flex-shrink-0">
                    <x-ui.button href="{{ url('/workflow/create') }}?patient_code={{ $patient->code }}" variant="primary" size="sm">
                        <x-slot:icon><i class="bi bi-plus-lg" aria-hidden="true"></i></x-slot:icon>
                        New Visit
                    </x-ui.button>
                </div>
            </div>

            {{-- ── OPD Tab ──────────────────────────────────────────────────────── --}}
            <div id="tab-opd" class="patient-tab-pane">
                @forelse($opdVisits as $visit)
                    @php
                        $isActive  = is_null($visit->discharged_at);
                        $doneCount = count($visit->done_steps ?? []);
                    @endphp
                    <a href="{{ url('/workflow/' . $visit->code) }}"
                       class="flex items-center gap-3 px-4 py-3.5 border-b hover:bg-[#f9fafb] transition-colors"
                       style="border-color:#f1f5f9;text-decoration:none">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 text-white"
                             style="background:linear-gradient(135deg,#4154f1,#717ff5)">
                            <i class="bi bi-person-fill" aria-hidden="true"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap mb-0.5">
                                <code class="text-xs font-bold" style="color:#4154f1">{{ $visit->code }}</code>
                                <x-ui.badge variant="primary" size="sm">OPD</x-ui.badge>
                                @if($isActive)
                                    <x-ui.badge variant="success" dot size="sm">Active</x-ui.badge>
                                @else
                                    <x-ui.badge variant="secondary" size="sm">Done</x-ui.badge>
                                @endif
                            </div>
                            <div class="text-xs" style="color:#9ca3af">
                                <i class="bi bi-calendar3" aria-hidden="true"></i>
                                {{ $visit->admitted_at?->format('d/m/Y H:i') ?? '—' }}
                                @if($visit->admission_type) · {{ $visit->admission_type }} @endif
                                @if($doneCount > 0) · <span style="color:#2eca6a">{{ $doneCount }} steps done</span> @endif
                            </div>
                        </div>
                        <i class="bi bi-chevron-right text-sm flex-shrink-0" style="color:#d1d5db"></i>
                    </a>
                @empty
                    <div class="text-center py-12">
                        <div class="text-4xl mb-3 opacity-30">🏥</div>
                        <div class="text-sm font-semibold mb-3" style="color:#9ca3af">No OPD visits yet</div>
                        <x-ui.button href="{{ url('/workflow/create') }}?patient_code={{ $patient->code }}" variant="primary" size="sm">
                            <x-slot:icon><i class="bi bi-plus-lg" aria-hidden="true"></i></x-slot:icon>
                            New OPD Visit
                        </x-ui.button>
                    </div>
                @endforelse
            </div>

            {{-- ── IPD Tab ──────────────────────────────────────────────────────── --}}
            <div id="tab-ipd" class="patient-tab-pane" style="display:none">
                @forelse($ipdVisits as $visit)
                    @php $isActive = is_null($visit->discharged_at); @endphp
                    <a href="{{ url('/workflow/' . $visit->code) }}"
                       class="flex items-center gap-3 px-4 py-3.5 border-b hover:bg-[#f9fafb] transition-colors"
                       style="border-color:#f1f5f9;text-decoration:none">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 text-white"
                             style="background:linear-gradient(135deg,#ff771d,#e65a00)">
                            <i class="bi bi-bed-fill" aria-hidden="true"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap mb-0.5">
                                <code class="text-xs font-bold" style="color:#ff771d">{{ $visit->code }}</code>
                                <x-ui.badge variant="warning" size="sm">IPD</x-ui.badge>
                                @if($isActive)
                                    <x-ui.badge variant="success" dot size="sm">Active</x-ui.badge>
                                @else
                                    <x-ui.badge variant="secondary" size="sm">Done</x-ui.badge>
                                @endif
                            </div>
                            <div class="text-xs" style="color:#9ca3af">
                                <i class="bi bi-calendar3" aria-hidden="true"></i>
                                {{ $visit->admitted_at?->format('d/m/Y H:i') ?? '—' }}
                                @if($visit->discharged_at) · Discharged {{ $visit->discharged_at->format('d/m/Y') }} @endif
                            </div>
                        </div>
                        <i class="bi bi-chevron-right text-sm flex-shrink-0" style="color:#d1d5db"></i>
                    </a>
                @empty
                    <div class="text-center py-12">
                        <div class="text-4xl mb-3 opacity-30">🛏️</div>
                        <div class="text-sm font-semibold" style="color:#9ca3af">No IPD admissions</div>
                    </div>
                @endforelse
            </div>

            {{-- ── Prescriptions Tab ────────────────────────────────────────────── --}}
            <div id="tab-rx" class="patient-tab-pane" style="display:none">
                @forelse($patient->prescriptions as $rx)
                    <a href="{{ route('prescriptions.show', $rx->code) }}"
                       class="flex items-center gap-3 px-4 py-3.5 border-b hover:bg-[#f9fafb] transition-colors"
                       style="border-color:#f1f5f9;text-decoration:none">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 text-white"
                             style="background:linear-gradient(135deg,#e91e8c,#c2185b)">
                            <i class="bi bi-capsule-fill" aria-hidden="true"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap mb-0.5">
                                <code class="text-xs font-bold" style="color:#e91e8c">{{ $rx->code }}</code>
                                @if($rx->dispensed_status === 'dispensed')
                                    <x-ui.badge variant="success" size="sm">Dispensed</x-ui.badge>
                                @elseif($rx->dispensed_status === 'partial')
                                    <x-ui.badge variant="warning" size="sm">Partial</x-ui.badge>
                                @else
                                    <x-ui.badge variant="primary" size="sm">Pending</x-ui.badge>
                                @endif
                            </div>
                            <div class="text-xs" style="color:#9ca3af">
                                <i class="bi bi-calendar3" aria-hidden="true"></i>
                                {{ $rx->created_at?->format('d/m/Y H:i') ?? '—' }}
                                @if($rx->visit_code) · {{ $rx->visit_code }} @endif
                            </div>
                        </div>
                        <i class="bi bi-chevron-right text-sm flex-shrink-0" style="color:#d1d5db"></i>
                    </a>
                @empty
                    <div class="text-center py-12">
                        <div class="text-4xl mb-3 opacity-30">💊</div>
                        <div class="text-sm font-semibold" style="color:#9ca3af">No prescriptions found</div>
                    </div>
                @endforelse
            </div>

            {{-- ── Lab Results Tab ──────────────────────────────────────────────── --}}
            <div id="tab-lab" class="patient-tab-pane" style="display:none">
                @forelse($patient->laboratories as $lab)
                    @php
                        $labVariant = match($lab->status) {
                            'pending'   => 'secondary',
                            'collected' => 'warning',
                            'resulted'  => 'success',
                            'verified'  => 'primary',
                            default     => 'secondary',
                        };
                    @endphp
                    <a href="{{ route('laboratory.show', $lab->code) }}"
                       class="flex items-center gap-3 px-4 py-3.5 border-b hover:bg-[#f9fafb] transition-colors"
                       style="border-color:#f1f5f9;text-decoration:none">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 text-white"
                             style="background:linear-gradient(135deg,#2eca6a,#16a34a)">
                            <i class="bi bi-eyedropper" aria-hidden="true"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap mb-0.5">
                                <code class="text-xs font-bold" style="color:#2eca6a">{{ $lab->code }}</code>
                                <x-ui.badge :variant="$labVariant" size="sm">{{ ucfirst($lab->status) }}</x-ui.badge>
                            </div>
                            <div class="text-xs" style="color:#9ca3af">
                                <i class="bi bi-calendar3" aria-hidden="true"></i>
                                {{ $lab->created_at?->format('d/m/Y H:i') ?? '—' }}
                                @if($lab->category) · {{ $lab->category }} @endif
                            </div>
                        </div>
                        <i class="bi bi-chevron-right text-sm flex-shrink-0" style="color:#d1d5db"></i>
                    </a>
                @empty
                    <div class="text-center py-12">
                        <div class="text-4xl mb-3 opacity-30">🧪</div>
                        <div class="text-sm font-semibold" style="color:#9ca3af">No lab results found</div>
                    </div>
                @endforelse
            </div>

            {{-- ── Billing Tab ───────────────────────────────────────────────────── --}}
            <div id="tab-billing" class="patient-tab-pane" style="display:none">
                @forelse($patient->invoices as $inv)
                    @php
                        $invVariant = match($inv->status) {
                            'paid'   => 'success',
                            'partial'=> 'warning',
                            'pending'=> 'danger',
                            'void'   => 'secondary',
                            default  => 'secondary',
                        };
                    @endphp
                    <a href="{{ route('invoices.show', $inv->code) }}"
                       class="flex items-center gap-3 px-4 py-3.5 border-b hover:bg-[#f9fafb] transition-colors"
                       style="border-color:#f1f5f9;text-decoration:none">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 text-white"
                             style="background:linear-gradient(135deg,#9b59b6,#7c3aed)">
                            <i class="bi bi-receipt-cutoff" aria-hidden="true"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap mb-0.5">
                                <code class="text-xs font-bold" style="color:#9b59b6">{{ $inv->code }}</code>
                                <x-ui.badge :variant="$invVariant" size="sm">{{ ucfirst($inv->status) }}</x-ui.badge>
                                <span class="text-xs font-bold" style="color:#1a1f36">{{ number_format($inv->grand_total) }} KHR</span>
                            </div>
                            <div class="text-xs" style="color:#9ca3af">
                                <i class="bi bi-calendar3" aria-hidden="true"></i>
                                {{ $inv->created_at?->format('d/m/Y H:i') ?? '—' }}
                                @if($inv->visit_code) · {{ $inv->visit_code }} @endif
                            </div>
                        </div>
                        <i class="bi bi-chevron-right text-sm flex-shrink-0" style="color:#d1d5db"></i>
                    </a>
                @empty
                    <div class="text-center py-12">
                        <div class="text-4xl mb-3 opacity-30">🧾</div>
                        <div class="text-sm font-semibold" style="color:#9ca3af">No billing records found</div>
                    </div>
                @endforelse
            </div>

        </x-ui.card>
    </div>{{-- /right --}}

</div>

@endsection

@push('scripts')
<script>
(function() {
    const ACTIVE_COLOR = {
        opd: '#4154f1', ipd: '#ff771d', rx: '#e91e8c', lab: '#2eca6a', billing: '#9b59b6'
    };
    function switchTab(id) {
        document.querySelectorAll('.patient-tab-pane').forEach(p => p.style.display = 'none');
        document.querySelectorAll('.patient-tab-btn').forEach(b => {
            b.style.color = '#6b7280';
            b.style.borderBottomColor = 'transparent';
        });
        const pane = document.getElementById('tab-' + id);
        const btn  = document.getElementById('tab-btn-' + id);
        if (pane) pane.style.display = 'block';
        if (btn) {
            btn.style.color = ACTIVE_COLOR[id] || '#4154f1';
            btn.style.borderBottomColor = ACTIVE_COLOR[id] || '#4154f1';
        }
        sessionStorage.setItem('patientTab', id);
    }
    window.switchTab = switchTab;
    const saved = sessionStorage.getItem('patientTab') || 'opd';
    switchTab(saved);
})();
</script>
@endpush
