@extends('clinics.layout.app')
@section('title', 'Dashboard')

@push('styles')
<style>
/* ── Dashboard-scoped overrides ──────────────────────────────────────── */
.dash-hero          { background: linear-gradient(135deg, #012970 0%, #1e3a8a 50%, #4154f1 100%); }
.dash-card          { background:#fff; border-radius:16px; border:1px solid #f0f2ff; box-shadow:0 1px 3px rgba(1,41,112,.06),0 4px 16px rgba(1,41,112,.04); }
.dash-card-hover    { transition:transform .15s, box-shadow .15s; }
.dash-card-hover:hover { transform:translateY(-2px); box-shadow:0 8px 32px rgba(1,41,112,.12); }
.kpi-num            { font-size:2.25rem; font-weight:900; line-height:1; letter-spacing:-1px; }
.live-pulse         { animation:livePulse 2s ease-in-out infinite; }
@keyframes livePulse { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:.5;transform:scale(1.4)} }
.bar-segment        { border-radius:4px 4px 0 0; transition:height .6s cubic-bezier(.4,0,.2,1); }
.step-badge         { font-size:10px; font-weight:800; letter-spacing:.5px; padding:2px 8px; border-radius:6px; }
.q-row              { transition:background .12s; }
.q-row:hover        { background:#f8faff; }
.quick-tile         { border-radius:14px; padding:16px; cursor:pointer; transition:transform .15s,box-shadow .15s,background .12s; border:1.5px solid transparent; text-decoration:none; display:flex; align-items:center; gap:12px; }
.quick-tile:hover   { transform:translateY(-2px); box-shadow:0 6px 24px rgba(0,0,0,.1); }
</style>
@endpush

@section('content')
@php
    $clinic   = currentClinic();
    $hour     = now()->hour;
    $greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
    $greetKm  = $hour < 12 ? 'អរុណសួស្ដី' : ($hour < 17 ? 'ទិវាសួស្ដី' : 'សាយណ្ហសួស្ដី');

    $maxBar   = max(1, collect($weeklyStats)->max(fn($d) => $d['opd'] + $d['ipd']));
    $total7   = collect($weeklyStats)->sum(fn($d) => $d['opd'] + $d['ipd']);
    $todayOpd = collect($weeklyStats)->last()['opd'] ?? 0;
    $todayIpd = collect($weeklyStats)->last()['ipd'] ?? 0;

    $pendingInv  = $stats['pending_invoices'] ?? 0;
    $activeVisits= $stats['active_total'] ?? 0;
    $todayVisits = $stats['today_visits'] ?? 0;
    $inpatients  = $stats['inpatients'] ?? 0;
    $thisMonth   = $stats['this_month'] ?? 0;
@endphp

{{-- ══════════════════════════════════════════════════════════════
     HERO BANNER
════════════════════════════════════════════════════════════════ --}}
<div class="dash-hero rounded-2xl p-6 mb-5 relative overflow-hidden">

    {{-- Decorative circles --}}
    <div class="absolute top-0 right-0 w-64 h-64 rounded-full opacity-10"
         style="background:radial-gradient(circle,#fff,transparent);transform:translate(30%,-30%)"></div>
    <div class="absolute bottom-0 left-1/2 w-40 h-40 rounded-full opacity-5"
         style="background:radial-gradient(circle,#a5b4fc,transparent);transform:translate(-50%,50%)"></div>

    <div class="relative flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

        {{-- Greeting --}}
        <div>
            <div class="flex items-center gap-3 mb-1">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center text-lg font-bold"
                     style="background:rgba(255,255,255,.15);color:#fff">
                    🏥
                </div>
                <div>
                    <div class="text-xs font-semibold tracking-wide" style="color:rgba(255,255,255,.6)">
                        {{ $clinic?->name_kh ?? $clinic?->name ?? 'MediFlow' }}
                    </div>
                    <div class="text-xl font-black text-white leading-tight">
                        {{ $greetKm }} / {{ $greeting }}
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-3 flex-wrap mt-2">
                <div class="flex items-center gap-1.5 text-xs font-medium" style="color:rgba(255,255,255,.7)">
                    <i class="bi bi-calendar3"></i>
                    <span id="heroClock">{{ now()->format('l, d F Y · H:i') }}</span>
                </div>
                @if($activeVisits > 0)
                <span class="flex items-center gap-1 text-xs font-bold px-2.5 py-1 rounded-full"
                      style="background:rgba(46,202,106,.2);color:#6ee7b7;border:1px solid rgba(46,202,106,.3)">
                    <span class="w-1.5 h-1.5 rounded-full bg-green-400 live-pulse inline-block"></span>
                    {{ $activeVisits }} active
                </span>
                @endif
            </div>
        </div>

        {{-- Action buttons --}}
        <div class="flex items-center gap-2 flex-shrink-0">
            <a href="{{ route('workflow.create') }}"
               class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-bold transition-all duration-150 hover:scale-105"
               style="background:#fff;color:#4154f1;box-shadow:0 4px 14px rgba(0,0,0,.2)">
                <i class="bi bi-plus-circle-fill"></i>
                <span class="hidden sm:inline">New Visit</span>
            </a>
            @if($pendingInv > 0)
            <a href="{{ route('invoices.index', ['status'=>'pending']) }}"
               class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-bold"
               style="background:rgba(255,119,29,.25);color:#fed7aa;border:1px solid rgba(255,119,29,.4)">
                <i class="bi bi-receipt-cutoff"></i>
                {{ $pendingInv }} pending
            </a>
            @endif
        </div>

    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════
     KPI STAT CARDS
════════════════════════════════════════════════════════════════ --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-5">

    {{-- Active Visits (Hero) --}}
    <div class="dash-card dash-card-hover col-span-1 p-5 cursor-pointer"
         onclick="location.href='{{ route('visits.index') }}'">
        <div class="flex items-start justify-between mb-3">
            <div class="w-11 h-11 rounded-xl flex items-center justify-center text-white text-lg"
                 style="background:linear-gradient(135deg,#4154f1,#6366f1)">
                <i class="bi bi-activity"></i>
            </div>
            @if($activeVisits > 0)
            <span class="flex items-center gap-1 text-xs font-bold px-2 py-0.5 rounded-full"
                  style="background:#e8f8ef;color:#2eca6a">
                <span class="w-1.5 h-1.5 rounded-full bg-green-400 live-pulse inline-block"></span>
                Live
            </span>
            @endif
        </div>
        <div class="kpi-num" style="color:#4154f1">{{ $activeVisits }}</div>
        <div class="text-xs font-semibold mt-1" style="color:#94a3b8">Active Visits</div>
        <div class="text-xs mt-0.5" style="color:#cbd5e1">ការចូលសកម្ម</div>
    </div>

    {{-- Today --}}
    <div class="dash-card dash-card-hover p-5 cursor-pointer"
         onclick="location.href='{{ route('visits.index') }}'">
        <div class="w-11 h-11 rounded-xl flex items-center justify-center text-white text-lg mb-3"
             style="background:linear-gradient(135deg,#2eca6a,#16a34a)">
            <i class="bi bi-calendar-check-fill"></i>
        </div>
        <div class="kpi-num" style="color:#2eca6a">{{ $todayVisits }}</div>
        <div class="text-xs font-semibold mt-1" style="color:#94a3b8">Today's Visits</div>
        <div class="text-xs mt-0.5" style="color:#cbd5e1">ថ្ងៃនេះ</div>
    </div>

    {{-- IPD --}}
    <div class="dash-card dash-card-hover p-5 cursor-pointer"
         onclick="location.href='{{ route('admissions.index') }}'">
        <div class="w-11 h-11 rounded-xl flex items-center justify-center text-white text-lg mb-3"
             style="background:linear-gradient(135deg,#ff771d,#ea580c)">
            <i class="bi bi-bed-fill"></i>
        </div>
        <div class="kpi-num" style="color:#ff771d">{{ $inpatients }}</div>
        <div class="text-xs font-semibold mt-1" style="color:#94a3b8">Inpatients (IPD)</div>
        <div class="text-xs mt-0.5" style="color:#cbd5e1">អ្នកជំងឺចូលសម្រាក</div>
    </div>

    {{-- Month --}}
    <div class="dash-card dash-card-hover p-5">
        <div class="w-11 h-11 rounded-xl flex items-center justify-center text-white text-lg mb-3"
             style="background:linear-gradient(135deg,#9b59b6,#7c3aed)">
            <i class="bi bi-graph-up-arrow"></i>
        </div>
        <div class="kpi-num" style="color:#9b59b6">{{ $thisMonth }}</div>
        <div class="text-xs font-semibold mt-1" style="color:#94a3b8">This Month</div>
        <div class="text-xs mt-0.5" style="color:#cbd5e1">ខែនេះ</div>
    </div>

</div>

{{-- ══════════════════════════════════════════════════════════════
     QUICK ACTIONS
════════════════════════════════════════════════════════════════ --}}
@php
$quickActions = [
    ['route'=>route('workflow.create'),      'icon'=>'bi-plus-circle-fill',    'bg'=>'#4154f1','light'=>'#eef0fd', 'km'=>'ការចូលព្យាបាលថ្មី', 'en'=>'New Visit',       'badge'=>null],
    ['route'=>route('prescriptions.index'),  'icon'=>'bi-capsule-fill',        'bg'=>'#e91e8c','light'=>'#fde8f5', 'km'=>'វេជ្ជបញ្ជា',        'en'=>'Prescriptions',   'badge'=>null],
    ['route'=>route('invoices.index'),       'icon'=>'bi-receipt-cutoff',      'bg'=>'#00bcd4','light'=>'#e0f7fa', 'km'=>'វិក្កយបត្រ',         'en'=>'Invoices',        'badge'=>$pendingInv ?: null],
    ['route'=>route('pharmacy.index'),       'icon'=>'bi-droplet-fill',        'bg'=>'#2eca6a','light'=>'#e8f8ef', 'km'=>'ឱសថស្ថាន',          'en'=>'Pharmacy',        'badge'=>null],
    ['route'=>route('laboratory.index'),     'icon'=>'bi-eyedropper',          'bg'=>'#ff771d','light'=>'#fff3e8', 'km'=>'មន្ទីរពិសោធន៍',     'en'=>'Laboratory',      'badge'=>null],
    ['route'=>route('patients.index'),       'icon'=>'bi-people-fill',         'bg'=>'#9b59b6','light'=>'#f0e8ff', 'km'=>'អ្នកជំងឺ',           'en'=>'Patients',        'badge'=>null],
];
@endphp
<div class="grid grid-cols-3 lg:grid-cols-6 gap-3 mb-5">
    @foreach($quickActions as $qa)
    <a href="{{ $qa['route'] }}" class="quick-tile" style="background:{{ $qa['light'] }};border-color:{{ $qa['bg'] }}22">
        <div class="w-9 h-9 rounded-xl flex items-center justify-center text-white text-sm flex-shrink-0"
             style="background:{{ $qa['bg'] }}">
            <i class="bi {{ $qa['icon'] }}"></i>
        </div>
        <div class="min-w-0 hidden sm:block">
            <div class="text-xs font-bold truncate" style="color:#012970">{{ $qa['en'] }}</div>
            <div class="text-xs truncate" style="color:#94a3b8">{{ $qa['km'] }}</div>
        </div>
        @if($qa['badge'])
        <span class="text-xs font-black px-1.5 py-0.5 rounded-lg ml-auto text-white flex-shrink-0"
              style="background:{{ $qa['bg'] }}">{{ $qa['badge'] }}</span>
        @endif
    </a>
    @endforeach
</div>

{{-- ══════════════════════════════════════════════════════════════
     CHART + LIVE QUEUE (two-column)
════════════════════════════════════════════════════════════════ --}}
<div class="grid grid-cols-1 lg:grid-cols-5 gap-4 mb-5">

    {{-- 7-Day Chart --}}
    <div class="dash-card p-5 lg:col-span-3">
        <div class="flex items-center justify-between mb-4">
            <div>
                <div class="text-sm font-bold" style="color:#012970">
                    <i class="bi bi-bar-chart-fill me-1" style="color:#4154f1"></i>
                    7-Day Visits
                </div>
                <div class="text-xs mt-0.5" style="color:#94a3b8">ការចូលព្យាបាល ៧ ថ្ងៃចុងក្រោយ</div>
            </div>
            <div class="flex items-center gap-3 text-xs font-semibold" style="color:#64748b">
                <span class="flex items-center gap-1">
                    <span class="inline-block w-2.5 h-2.5 rounded-sm" style="background:#4154f1"></span>OPD
                </span>
                <span class="flex items-center gap-1">
                    <span class="inline-block w-2.5 h-2.5 rounded-sm" style="background:#ff771d"></span>IPD
                </span>
            </div>
        </div>

        {{-- Bars --}}
        <div style="height:120px;display:flex;align-items:flex-end;gap:6px">
            @foreach($weeklyStats as $d)
            @php
                $t = $d['opd'] + $d['ipd'];
                $isToday = ($d['date'] ?? '') === today()->format('d/m');
                $opdH = $maxBar > 0 ? round($d['opd'] / $maxBar * 100) : 0;
                $ipdH = $maxBar > 0 ? round($d['ipd'] / $maxBar * 100) : 0;
            @endphp
            <div class="flex-1 flex flex-col items-center gap-1 h-full">
                {{-- Value label --}}
                <div class="text-xs font-bold" style="color:{{ $t > 0 ? '#475569' : 'transparent' }}">{{ $t ?: '' }}</div>
                {{-- Bar container --}}
                <div class="flex-1 w-full flex flex-col justify-end gap-px relative">
                    @if($isToday)
                    <div class="absolute inset-0 rounded-lg" style="background:rgba(65,84,241,.05);border:1px dashed rgba(65,84,241,.2)"></div>
                    @endif
                    @if($d['opd'] > 0)
                    <div class="bar-segment w-full" style="height:{{ $opdH }}%;background:linear-gradient(180deg,#717ff5 0%,#4154f1 100%);min-height:3px;position:relative;z-index:1"></div>
                    @endif
                    @if($d['ipd'] > 0)
                    <div class="bar-segment w-full" style="height:{{ $ipdH }}%;background:linear-gradient(180deg,#ffaa6b 0%,#ff771d 100%);border-radius:{{ $d['opd'] > 0 ? '0' : '4px 4px' }} 0 0;min-height:3px;position:relative;z-index:1"></div>
                    @endif
                    @if($t === 0)
                    <div class="w-full" style="height:2px;background:#f1f5f9;border-radius:1px"></div>
                    @endif
                </div>
                {{-- Day label --}}
                <div class="text-xs font-semibold whitespace-nowrap"
                     style="color:{{ $isToday ? '#4154f1' : '#94a3b8' }};font-weight:{{ $isToday ? '800' : '500' }}">
                    {{ $d['day'] }}
                </div>
            </div>
            @endforeach
        </div>

        {{-- Summary row --}}
        <div class="grid grid-cols-4 gap-2 mt-4 pt-4" style="border-top:1px solid #f0f2ff">
            @foreach([
                ['7-Day','Total',$total7,'#012970'],
                ['OPD','OPD',collect($weeklyStats)->sum('opd'),'#4154f1'],
                ['IPD','IPD',collect($weeklyStats)->sum('ipd'),'#ff771d'],
                ['Rate','Rate',($total7>0?round(collect($weeklyStats)->sum('opd')/$total7*100).'%':'—'),'#2eca6a'],
            ] as [$km,$en,$val,$col])
            <div class="text-center">
                <div class="text-lg font-black" style="color:{{ $col }}">{{ $val }}</div>
                <div class="text-xs" style="color:#94a3b8">{{ $en }}</div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Live Patient Queue --}}
    <div class="dash-card lg:col-span-2 flex flex-col" style="max-height:360px">
        <div class="flex items-center justify-between p-5 pb-3 flex-shrink-0">
            <div>
                <div class="text-sm font-bold flex items-center gap-2" style="color:#012970">
                    <span class="w-2 h-2 rounded-full bg-green-400 live-pulse inline-block"></span>
                    Live Queue
                </div>
                <div class="text-xs" style="color:#94a3b8">Today's patients</div>
            </div>
            <a href="{{ route('visits.index') }}" class="text-xs font-semibold" style="color:#4154f1">
                View all <i class="bi bi-arrow-right"></i>
            </a>
        </div>

        <div class="flex-1 overflow-y-auto">
            @php $colors = ['#4154f1','#2eca6a','#ff771d','#e74c3c','#9b59b6','#00bcd4']; @endphp
            @forelse($recentVisits->take(8) as $v)
            @php
                $col = $colors[abs(crc32($v->patient_code ?? '')) % 6];
                $nm  = trim(($v->patient?->surname ?? $v->surname ?? '') . ', ' . ($v->patient?->name ?? $v->name ?? ''));
                $act = is_null($v->discharged_at);
            @endphp
            <a href="{{ url('/workflow/'.$v->code) }}"
               class="q-row flex items-center gap-3 px-5 py-3"
               style="border-bottom:1px solid #f8faff;text-decoration:none">
                {{-- Avatar --}}
                <div class="w-8 h-8 rounded-lg flex items-center justify-center text-xs font-black flex-shrink-0"
                     style="background:{{ $col }}18;color:{{ $col }};border:1.5px solid {{ $col }}33">
                    {{ strtoupper(substr($v->patient?->surname ?? $v->surname ?? 'U', 0, 1)) }}{{ strtoupper(substr($v->patient?->name ?? $v->name ?? '', 0, 1)) }}
                </div>
                {{-- Name --}}
                <div class="flex-1 min-w-0">
                    <div class="text-xs font-bold truncate" style="color:#012970">{{ $nm ?: $v->patient_code }}</div>
                    <div class="text-xs" style="color:#94a3b8">{{ $v->code }} · {{ $v->visit_type }}</div>
                </div>
                {{-- Status --}}
                <div class="flex-shrink-0 text-right">
                    @if($act)
                    <span class="w-2 h-2 rounded-full bg-green-400 live-pulse inline-block"></span>
                    @else
                    <i class="bi bi-check2-circle text-sm" style="color:#2eca6a"></i>
                    @endif
                    <div class="text-xs" style="color:#cbd5e1">{{ $v->admitted_at?->format('H:i') }}</div>
                </div>
            </a>
            @empty
            <div class="flex flex-col items-center justify-center py-10" style="color:#cbd5e1">
                <div class="text-4xl mb-2 opacity-30">📋</div>
                <div class="text-xs font-semibold">No visits today</div>
                <a href="{{ route('workflow.create') }}" class="text-xs mt-2 font-bold" style="color:#4154f1">
                    + Register first visit
                </a>
            </div>
            @endforelse
        </div>
    </div>

</div>

{{-- ══════════════════════════════════════════════════════════════
     RECENT VISITS TABLE
════════════════════════════════════════════════════════════════ --}}
<div class="dash-card overflow-hidden">

    {{-- Header --}}
    <div class="flex items-center justify-between px-5 py-4" style="border-bottom:1px solid #f0f2ff">
        <div>
            <div class="text-sm font-bold" style="color:#012970">
                <i class="bi bi-clock-history me-1" style="color:#4154f1"></i>
                Recent Visits
            </div>
            <div class="text-xs" style="color:#94a3b8">ការចូលព្យាបាលថ្មីៗ</div>
        </div>
        <a href="{{ route('visits.index') }}" class="text-xs font-semibold px-3 py-1.5 rounded-lg"
           style="background:#eef0fd;color:#4154f1">
            View all <i class="bi bi-arrow-right ms-1"></i>
        </a>
    </div>

    {{-- Table --}}
    <div style="overflow-x:auto">
        <table style="width:100%;border-collapse:collapse">
            <thead>
                <tr style="background:#f8faff">
                    <th class="text-left text-xs font-bold px-5 py-3" style="color:#64748b">Code</th>
                    <th class="text-left text-xs font-bold px-4 py-3" style="color:#64748b">Patient</th>
                    <th class="text-left text-xs font-bold px-4 py-3 hidden sm:table-cell" style="color:#64748b">Type</th>
                    <th class="text-left text-xs font-bold px-4 py-3" style="color:#64748b">Status</th>
                    <th class="text-left text-xs font-bold px-4 py-3 hidden md:table-cell" style="color:#64748b">Progress</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
            @forelse($recentVisits as $visit)
            @php
                $act      = is_null($visit->discharged_at);
                $done     = count($visit->done_steps ?? []);
                $total    = 10;
                $pct      = round($done / $total * 100);
                $skip     = count($visit->skipped_steps ?? []);
                $nm       = trim(($visit->patient?->surname ?? $visit->surname ?? '') . ', ' . ($visit->patient?->name ?? $visit->name ?? ''));
                $typeColor= $visit->visit_type === 'IPD' ? '#ff771d' : '#4154f1';
            @endphp
            <tr class="q-row" style="border-bottom:1px solid #f8faff;cursor:pointer"
                onclick="location.href='{{ url('/workflow/'.$visit->code) }}'">

                <td class="px-5 py-3.5">
                    <div class="text-xs font-black" style="color:#4154f1;font-family:monospace">{{ $visit->code }}</div>
                    <div class="text-xs mt-0.5" style="color:#cbd5e1">{{ $visit->admitted_at?->format('d/m H:i') }}</div>
                </td>

                <td class="px-4 py-3.5">
                    <div class="text-sm font-bold" style="color:#012970">{{ $nm ?: '—' }}</div>
                    <div class="text-xs" style="color:#94a3b8">{{ $visit->patient_code }}</div>
                </td>

                <td class="px-4 py-3.5 hidden sm:table-cell">
                    <span class="step-badge text-white"
                          style="background:{{ $typeColor }}">{{ $visit->visit_type }}</span>
                </td>

                <td class="px-4 py-3.5">
                    @if($act)
                    <span class="flex items-center gap-1.5 text-xs font-bold" style="color:#2eca6a">
                        <span class="w-1.5 h-1.5 rounded-full bg-green-400 live-pulse inline-block"></span>
                        Active
                    </span>
                    @else
                    <span class="flex items-center gap-1 text-xs font-semibold" style="color:#94a3b8">
                        <i class="bi bi-check2-circle" style="color:#2eca6a"></i>
                        Done
                    </span>
                    @endif
                </td>

                <td class="px-4 py-3.5 hidden md:table-cell">
                    <div class="flex items-center gap-2">
                        <div class="flex-1" style="background:#f1f5f9;border-radius:4px;height:5px;width:80px;overflow:hidden">
                            <div style="height:100%;width:{{ $pct }}%;background:{{ $pct>=100?'#2eca6a':'linear-gradient(90deg,#4154f1,#818cf8)' }};border-radius:4px;transition:width .4s"></div>
                        </div>
                        <span class="text-xs font-bold" style="color:{{ $pct>=100?'#2eca6a':'#64748b' }}">{{ $done }}/{{ $total }}</span>
                    </div>
                    @if($skip > 0)
                    <div class="text-xs mt-0.5" style="color:#f59e0b">{{ $skip }} skipped</div>
                    @endif
                </td>

                <td class="px-4 py-3.5" onclick="event.stopPropagation()">
                    <a href="{{ url('/workflow/'.$visit->code) }}"
                       class="flex items-center gap-1 text-xs font-bold px-3 py-1.5 rounded-lg transition-all hover:scale-105"
                       style="background:#4154f1;color:#fff">
                        <i class="bi bi-arrow-right-circle-fill"></i>
                        <span class="hidden sm:inline">Continue</span>
                    </a>
                </td>

            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center py-16" style="color:#cbd5e1">
                    <div class="text-5xl mb-3 opacity-20">🏥</div>
                    <div class="text-sm font-semibold mb-3">No visits yet today</div>
                    <a href="{{ route('workflow.create') }}"
                       class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-bold text-white"
                       style="background:#4154f1">
                        <i class="bi bi-plus-lg"></i> Register First Visit
                    </a>
                </td>
            </tr>
            @endforelse
            </tbody>
        </table>
    </div>

</div>

@endsection

@push('scripts')
<script>
(function tick() {
    const el = document.getElementById('heroClock');
    if (el) {
        const n = new Date();
        const p = v => String(v).padStart(2,'0');
        const days = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
        const months = ['January','February','March','April','May','June','July','August','September','October','November','December'];
        el.textContent = `${days[n.getDay()]}, ${p(n.getDate())} ${months[n.getMonth()]} ${n.getFullYear()} · ${p(n.getHours())}:${p(n.getMinutes())}`;
    }
    setTimeout(tick, 60000);
})();
</script>
@endpush
