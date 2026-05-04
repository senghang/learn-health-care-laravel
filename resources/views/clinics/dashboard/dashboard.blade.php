@extends('clinics.layout.app')
@section('title', 'Dashboard')

@section('content')
@php
    $clinic      = currentClinic();
    $hour        = now()->hour;
    $greeting    = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
    $greetKm     = $hour < 12 ? 'អរុណសួស្ដី' : ($hour < 17 ? 'ទិវាសួស្ដី' : 'សាយណ្ហសួស្ដី');
    $maxBar      = max(1, collect($weeklyStats)->max(fn($d) => $d['opd'] + $d['ipd']));
    $total7      = collect($weeklyStats)->sum(fn($d) => $d['opd'] + $d['ipd']);
    $pendingInv  = $stats['pending_invoices']  ?? 0;
    $activeVisits= $stats['active_total']       ?? 0;
    $activeOpd   = $stats['active_opd']         ?? 0;
    $todayVisits = $stats['today_visits']        ?? 0;
    $inpatients  = $stats['inpatients']          ?? 0;
    $lowStock    = $stats['low_stock']            ?? 0;
@endphp

{{-- ── HERO ─────────────────────────────────────────────────── --}}
<div class="rounded-2xl p-6 mb-5 relative overflow-hidden"
     style="background:linear-gradient(135deg,#1a1f36 0%,#1e3a8a 50%,#4154f1 100%)">

    {{-- Decorative blobs --}}
    <div class="absolute top-0 right-0 w-64 h-64 rounded-full pointer-events-none"
         style="background:radial-gradient(circle,rgba(255,255,255,.12),transparent);transform:translate(30%,-30%)"></div>
    <div class="absolute bottom-0 left-1/2 w-40 h-40 rounded-full pointer-events-none"
         style="background:radial-gradient(circle,rgba(165,180,252,.08),transparent);transform:translate(-50%,50%)"></div>

    <div class="relative flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

        {{-- Greeting --}}
        <div>
            <div class="flex items-center gap-3 mb-1">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center text-lg font-bold flex-shrink-0"
                     style="background:rgba(255,255,255,.15);color:#fff" aria-hidden="true">🏥</div>
                <div>
                    <div class="text-xs font-semibold tracking-wide" style="color:rgba(255,255,255,.6)">
                        {{ $clinic?->name_kh ?? $clinic?->name ?? 'MediFlow' }}
                    </div>
                    <h1 class="text-xl font-black text-white leading-tight">
                        {{ $greetKm }} / {{ $greeting }}
                    </h1>
                </div>
            </div>
            <div class="flex items-center gap-3 flex-wrap mt-2">
                <div class="flex items-center gap-1.5 text-xs font-medium" style="color:rgba(255,255,255,.7)">
                    <i class="bi bi-calendar3" aria-hidden="true"></i>
                    <time id="heroClock">{{ now()->format('l, d F Y · H:i') }}</time>
                </div>
                @if($activeVisits > 0)
                <x-ui.badge variant="success" dot>{{ $activeVisits }} active</x-ui.badge>
                @endif
            </div>
        </div>

        {{-- CTA buttons --}}
        <div class="flex items-center gap-2 flex-shrink-0">
            <x-ui.button href="{{ route('workflow.create') }}"
                         class="!bg-white !text-[#4154f1] !border-white hover:!bg-[#f8f9fb] !shadow-lg hover:scale-105">
                <x-slot:icon><i class="bi bi-plus-circle-fill" aria-hidden="true"></i></x-slot:icon>
                <span class="hidden sm:inline">New Visit</span>
            </x-ui.button>
            @if($pendingInv > 0)
            <x-ui.button href="{{ route('invoices.index', ['status'=>'pending']) }}"
                         class="!bg-transparent !text-[#fed7aa] !border-[rgba(255,119,29,.4)] hover:!bg-[rgba(255,119,29,.2)]">
                <x-slot:icon><i class="bi bi-receipt-cutoff" aria-hidden="true"></i></x-slot:icon>
                {{ $pendingInv }} pending
            </x-ui.button>
            @endif
        </div>
    </div>
</div>

{{-- ── KPI GRID ─────────────────────────────────────────────── --}}
<div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-5">

    <x-ui.stats-card
        title="Total Patients Today"
        km="អ្នកជំងឺថ្ងៃនេះ"
        :value="$todayVisits"
        icon="bi-people-fill"
        color="#4154f1"
        href="{{ route('visits.index') }}"
    />

    <x-ui.stats-card
        title="Active OPD Visits"
        km="ការចូល OPD"
        :value="$activeOpd"
        icon="bi-activity"
        color="#2eca6a"
        :live="$activeOpd > 0"
        href="{{ route('visits.index', ['visit_type'=>'OPD']) }}"
    />

    <x-ui.stats-card
        title="IPD Admissions"
        km="អ្នកចូលសម្រាក"
        :value="$inpatients"
        icon="bi-bed-fill"
        color="#ff771d"
        href="{{ route('admissions.index') }}"
    />

    <x-ui.stats-card
        title="Pending Bills"
        km="វិក្កយបត្រ"
        :value="$pendingInv"
        icon="bi-receipt-cutoff"
        color="#9b59b6"
        href="{{ route('invoices.index', ['status'=>'pending']) }}"
    />

    <x-ui.stats-card
        title="Stock Alerts"
        km="ស្តុកទាប"
        :value="$lowStock"
        icon="bi-exclamation-triangle-fill"
        color="#ef4444"
        href="{{ route('inventory.products') }}"
    />

</div>

{{-- ── QUICK ACTIONS ────────────────────────────────────────── --}}
@php
$quickActions = [
    ['route'=>route('patients.create'),      'icon'=>'bi-person-plus-fill',  'bg'=>'#4154f1','light'=>'#eef0fd','km'=>'ចុះឈ្មោះអ្នកជំងឺ',  'en'=>'Register Patient'],
    ['route'=>route('workflow.create'),      'icon'=>'bi-plus-circle-fill',  'bg'=>'#2eca6a','light'=>'#e8f8ef','km'=>'ការចូល OPD ថ្មី',    'en'=>'New OPD Visit'],
    ['route'=>route('invoices.create'),      'icon'=>'bi-receipt-cutoff',    'bg'=>'#ff771d','light'=>'#fff3e8','km'=>'បង្កើតវិក្កយបត្រ',   'en'=>'Create Invoice'],
    ['route'=>route('prescriptions.create'),'icon'=>'bi-capsule-fill',       'bg'=>'#9b59b6','light'=>'#f0e8ff','km'=>'បន្ថែមវេជ្ជបញ្ជា',   'en'=>'Add Prescription'],
];
@endphp
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-5" role="list" aria-label="Quick Actions">
    @foreach($quickActions as $qa)
    <a href="{{ $qa['route'] }}" role="listitem"
       class="flex items-center gap-3 p-4 rounded-2xl border transition-all duration-150 hover:-translate-y-0.5 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-[#4154f1]/30"
       style="background:{{ $qa['light'] }};border-color:{{ $qa['bg'] }}22">
        <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white text-base flex-shrink-0"
             style="background:{{ $qa['bg'] }}" aria-hidden="true">
            <i class="bi {{ $qa['icon'] }}"></i>
        </div>
        <div class="min-w-0">
            <div class="text-xs font-bold truncate" style="color:#1a1f36">{{ $qa['en'] }}</div>
            <div class="text-xs truncate" style="color:#6b7280">{{ $qa['km'] }}</div>
        </div>
    </a>
    @endforeach
</div>

{{-- ── CHART + LIVE QUEUE ───────────────────────────────────── --}}
<div class="grid grid-cols-1 lg:grid-cols-5 gap-4 mb-5">

    {{-- 7-Day Bar Chart --}}
    <x-ui.card class="lg:col-span-3">
        <x-slot:header>
            <x-ui.card-header title="7-Day Visits" km="ការចូលព្យាបាល ៧ ថ្ងៃ" icon="bi-bar-chart-fill">
                <div class="flex items-center gap-3 text-xs font-semibold text-[#64748b]">
                    <span class="flex items-center gap-1">
                        <span class="inline-block w-2.5 h-2.5 rounded-sm bg-[#4154f1]" aria-hidden="true"></span>OPD
                    </span>
                    <span class="flex items-center gap-1">
                        <span class="inline-block w-2.5 h-2.5 rounded-sm bg-[#ff771d]" aria-hidden="true"></span>IPD
                    </span>
                </div>
            </x-ui.card-header>
        </x-slot:header>

        {{-- Bars --}}
        <div style="height:120px;display:flex;align-items:flex-end;gap:6px" role="img" aria-label="7-day visit bar chart">
            @foreach($weeklyStats as $d)
            @php
                $t       = $d['opd'] + $d['ipd'];
                $isToday = ($d['date'] ?? '') === today()->format('d/m');
                $opdH    = $maxBar > 0 ? round($d['opd'] / $maxBar * 100) : 0;
                $ipdH    = $maxBar > 0 ? round($d['ipd'] / $maxBar * 100) : 0;
            @endphp
            <div class="flex-1 flex flex-col items-center gap-1 h-full">
                <div class="text-xs font-bold" style="color:{{ $t > 0 ? '#475569' : 'transparent' }}" aria-hidden="true">
                    {{ $t ?: '' }}
                </div>
                <div class="flex-1 w-full flex flex-col justify-end gap-px relative">
                    @if($isToday)
                    <div class="absolute inset-0 rounded-lg" style="background:rgba(65,84,241,.05);border:1px dashed rgba(65,84,241,.2)" aria-hidden="true"></div>
                    @endif
                    @if($d['opd'] > 0)
                    <div class="w-full rounded-t-sm" style="height:{{ $opdH }}%;background:linear-gradient(180deg,#717ff5 0%,#4154f1 100%);min-height:3px;position:relative;z-index:1;transition:height .6s cubic-bezier(.4,0,.2,1)"></div>
                    @endif
                    @if($d['ipd'] > 0)
                    <div class="w-full" style="height:{{ $ipdH }}%;background:linear-gradient(180deg,#ffaa6b 0%,#ff771d 100%);border-radius:{{ $d['opd'] > 0 ? '0' : '4px 4px' }} 0 0;min-height:3px;position:relative;z-index:1;transition:height .6s cubic-bezier(.4,0,.2,1)"></div>
                    @endif
                    @if($t === 0)
                    <div class="w-full" style="height:2px;background:#f1f5f9;border-radius:1px"></div>
                    @endif
                </div>
                <div class="text-xs font-semibold whitespace-nowrap"
                     style="color:{{ $isToday ? '#4154f1' : '#6b7280' }};font-weight:{{ $isToday ? '800' : '500' }}">
                    {{ $d['day'] }}
                </div>
            </div>
            @endforeach
        </div>

        {{-- Summary row --}}
        <div class="grid grid-cols-4 gap-2 mt-4 pt-4" style="border-top:1px solid #e6e9f0">
            @foreach([
                ['7-Day','Total',$total7,'#1a1f36'],
                ['OPD','OPD',collect($weeklyStats)->sum('opd'),'#4154f1'],
                ['IPD','IPD',collect($weeklyStats)->sum('ipd'),'#ff771d'],
                ['Rate','OPD %',($total7>0?round(collect($weeklyStats)->sum('opd')/$total7*100).'%':'—'),'#2eca6a'],
            ] as [$km,$en,$val,$col])
            <div class="text-center">
                <div class="text-lg font-black" style="color:{{ $col }}">{{ $val }}</div>
                <div class="text-xs text-[#6b7280]">{{ $en }}</div>
            </div>
            @endforeach
        </div>
    </x-ui.card>

    {{-- Live Patient Queue --}}
    <div class="bg-white rounded-2xl border border-[#e6e9f0] flex flex-col lg:col-span-2"
         style="box-shadow:0 1px 3px rgba(17,24,39,.04),0 4px 16px rgba(17,24,39,.03);max-height:360px">

        <x-ui.card-header icon="bi-broadcast" title="Today's patients" km="Live Queue" :compact="true">
            <a href="{{ route('visits.index') }}" class="text-xs font-semibold text-[#4154f1] hover:underline">
                View all <i class="bi bi-arrow-right" aria-hidden="true"></i>
            </a>
        </x-ui.card-header>

        <div class="flex-1 overflow-y-auto">
            @php $avatarColors = ['#4154f1','#2eca6a','#ff771d','#e74c3c','#9b59b6','#00bcd4']; @endphp
            @forelse($recentVisits->take(8) as $v)
            @php
                $col = $avatarColors[abs(crc32($v->patient_code ?? '')) % 6];
                $nm  = trim(($v->patient?->surname ?? $v->surname ?? '') . ', ' . ($v->patient?->name ?? $v->name ?? ''));
                $act = is_null($v->discharged_at);
            @endphp
            <a href="{{ url('/workflow/'.$v->code) }}"
               class="flex items-center gap-3 px-5 py-3 hover:bg-[#f8f9fb] transition-colors"
               style="border-bottom:1px solid #f8f9fb">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center text-xs font-black flex-shrink-0"
                     style="background:{{ $col }}18;color:{{ $col }};border:1.5px solid {{ $col }}33" aria-hidden="true">
                    {{ strtoupper(substr($v->patient?->surname ?? $v->surname ?? 'U',0,1)) }}{{ strtoupper(substr($v->patient?->name ?? $v->name ?? '',0,1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <div class="text-xs font-bold truncate" style="color:#1a1f36">{{ $nm ?: $v->patient_code }}</div>
                    <div class="text-xs text-[#6b7280]">{{ $v->code }} · {{ $v->visit_type }}</div>
                </div>
                <div class="flex-shrink-0 text-right">
                    @if($act)
                    <span class="w-2 h-2 rounded-full bg-[#2eca6a] inline-block" style="animation:livePulse 2s ease-in-out infinite" aria-label="Active"></span>
                    @else
                    <i class="bi bi-check2-circle text-sm text-[#2eca6a]" aria-label="Complete"></i>
                    @endif
                    <div class="text-xs text-[#cbd5e1]">{{ $v->admitted_at?->format('H:i') }}</div>
                </div>
            </a>
            @empty
            <x-ui.empty-state
                icon="bi-clipboard-pulse"
                title="No visits today"
                :compact="true"
            >
                <x-ui.button href="{{ route('workflow.create') }}" variant="ghost" size="sm">
                    <x-slot:icon><i class="bi bi-plus-lg" aria-hidden="true"></i></x-slot:icon>
                    Register first visit
                </x-ui.button>
            </x-ui.empty-state>
            @endforelse
        </div>
    </div>

</div>

{{-- ── RECENT VISITS TABLE ──────────────────────────────────── --}}
<x-ui.card :noPadding="true">
    <x-slot:header>
        <x-ui.card-header title="Recent Visits" km="ការចូលព្យាបាលថ្មីៗ" icon="bi-clock-history">
            <x-ui.button href="{{ route('visits.index') }}" variant="ghost" size="sm">
                View all <i class="bi bi-arrow-right" aria-hidden="true"></i>
            </x-ui.button>
        </x-ui.card-header>
    </x-slot:header>

    <x-ui.table empty-title="No visits yet today" empty-icon="bi-clipboard-pulse"
                :emptyDesc="'Register the first patient to start the queue.'">
        <x-slot:head>
            <x-ui.table-th>Code</x-ui.table-th>
            <x-ui.table-th>Patient</x-ui.table-th>
            <x-ui.table-th class="hidden sm:table-cell">Type</x-ui.table-th>
            <x-ui.table-th>Status</x-ui.table-th>
            <x-ui.table-th class="hidden md:table-cell">Progress</x-ui.table-th>
            <x-ui.table-th></x-ui.table-th>
        </x-slot:head>
        <x-slot:body>
            @forelse($recentVisits as $visit)
            @php
                $act       = is_null($visit->discharged_at);
                $done      = count($visit->done_steps ?? []);
                $total     = 10;
                $pct       = round($done / $total * 100);
                $nm        = trim(($visit->patient?->surname ?? $visit->surname ?? '') . ', ' . ($visit->patient?->name ?? $visit->name ?? ''));
                $typeColor = $visit->visit_type === 'IPD' ? '#ff771d' : '#4154f1';
            @endphp
            <tr class="hover:bg-[#f8f9fb] transition-colors cursor-pointer"
                style="border-bottom:1px solid #f8f9fb"
                onclick="location.href='{{ url('/workflow/'.$visit->code) }}'">

                <x-ui.table-td>
                    <div class="text-xs font-black font-mono" style="color:#4154f1">{{ $visit->code }}</div>
                    <div class="text-xs text-[#cbd5e1]">{{ $visit->admitted_at?->format('d/m H:i') }}</div>
                </x-ui.table-td>

                <x-ui.table-td>
                    <div class="text-sm font-bold" style="color:#1a1f36">{{ $nm ?: '—' }}</div>
                    <div class="text-xs text-[#6b7280]">{{ $visit->patient_code }}</div>
                </x-ui.table-td>

                <x-ui.table-td class="hidden sm:table-cell">
                    <x-ui.badge :variant="$visit->visit_type === 'IPD' ? 'warning' : 'primary'">
                        {{ $visit->visit_type }}
                    </x-ui.badge>
                </x-ui.table-td>

                <x-ui.table-td>
                    @if($act)
                    <x-ui.badge variant="success" dot>Active</x-ui.badge>
                    @else
                    <x-ui.badge variant="secondary">
                        <i class="bi bi-check2-circle text-[#2eca6a]" aria-hidden="true"></i> Done
                    </x-ui.badge>
                    @endif
                </x-ui.table-td>

                <x-ui.table-td class="hidden md:table-cell">
                    <div class="flex items-center gap-2">
                        <div class="flex-1 h-1.5 rounded-full bg-[#f1f5f9] overflow-hidden" style="width:80px">
                            <div class="h-full rounded-full transition-all"
                                 style="width:{{ $pct }}%;background:{{ $pct>=100?'#2eca6a':'linear-gradient(90deg,#4154f1,#818cf8)' }}"></div>
                        </div>
                        <span class="text-xs font-bold {{ $pct>=100?'text-[#2eca6a]':'text-[#64748b]' }}">{{ $done }}/{{ $total }}</span>
                    </div>
                </x-ui.table-td>

                <x-ui.table-td align="right" onclick="event.stopPropagation()">
                    <x-ui.button href="{{ url('/workflow/'.$visit->code) }}" size="sm" variant="primary"
                                 class="hover:scale-105">
                        <x-slot:icon><i class="bi bi-arrow-right-circle-fill" aria-hidden="true"></i></x-slot:icon>
                        <span class="hidden sm:inline">Continue</span>
                    </x-ui.button>
                </x-ui.table-td>
            </tr>
            @empty
            {{-- Empty handled by x-ui.table's body slot detection --}}
            @endforelse
        </x-slot:body>
    </x-ui.table>

    @if($recentVisits->isEmpty())
    <div class="p-4">
        <x-ui.empty-state icon="bi-clipboard-pulse" title="No visits yet today"
                          description="Register the first patient to start the queue.">
            <x-ui.button href="{{ route('workflow.create') }}" variant="primary" size="sm">
                <x-slot:icon><i class="bi bi-plus-lg" aria-hidden="true"></i></x-slot:icon>
                Register First Visit
            </x-ui.button>
        </x-ui.empty-state>
    </div>
    @endif
</x-ui.card>

@endsection

@push('scripts')
<script>
(function tick() {
    const el = document.getElementById('heroClock');
    if (el) {
        const n = new Date();
        const p = v => String(v).padStart(2,'0');
        const days   = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
        const months = ['January','February','March','April','May','June','July','August','September','October','November','December'];
        el.textContent = `${days[n.getDay()]}, ${p(n.getDate())} ${months[n.getMonth()]} ${n.getFullYear()} · ${p(n.getHours())}:${p(n.getMinutes())}`;
        el.setAttribute('datetime', n.toISOString());
    }
    setTimeout(tick, 60000);
})();
</script>
@endpush
