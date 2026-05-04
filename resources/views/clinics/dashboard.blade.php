@extends('clinics.layout.app')
@section('title', 'ផ្ទាំងគ្រប់គ្រង')
@section('content')

    {{-- ── Page header ──────────────────────────────────────────────────── --}}
    <div class="pg-header">
        <div>
            <h1 class="pg-title">ផ្ទាំងគ្រប់គ្រង <small>/ Dashboard</small></h1>
            <div class="breadcrumb-row">
                <span style="color:#4154f1;font-weight:600;font-size:11px">
                    <i class="bi bi-hospital" style="font-size:10px"></i>
                    {{ currentClinic()?->name_kh ?? (currentClinic()?->name ?? 'Clinic') }}
                </span>
                <span>·</span>
                <span id="liveDate" style="font-size:11px;color:#aaa">{{ now()->format('d/m/Y H:i') }}</span>
                <span class="live-dot ms-1" style="width:6px;height:6px;vertical-align:middle" title="Live"></span>
            </div>
        </div>
        <a href="{{ url('/workflow/create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> ការចូលព្យាបាលថ្មី
        </a>
    </div>

    {{-- ── KPI row ──────────────────────────────────────────────────────── --}}
    <div class="row g-3 mb-3">

        {{-- HERO: Active visits --}}
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="op-hero-card" onclick="window.location='{{ url('/visits?status=active') }}'">
                <div class="op-hero-num" data-target="{{ $stats['active_total'] }}">{{ $stats['active_total'] }}</div>
                <div class="op-hero-label">ការចូលសកម្ម / Active Visits</div>
                <div class="op-hero-sub" style="display:flex;align-items:center;gap:6px;margin-top:4px">
                    @if ($stats['active_total'] > 0)
                        <span class="live-dot" style="width:6px;height:6px;flex-shrink:0"></span> Live now
                    @else
                        <i class="bi bi-check2-circle"></i> All clear
                    @endif
                </div>
                <div class="op-hero-icon"><i class="bi bi-activity"></i></div>
            </div>
        </div>

        {{-- Today --}}
        <div class="col-6 col-sm-6 col-lg-3">
            <div class="stat-card" style="cursor:pointer" onclick="window.location='{{ url('/visits') }}'">
                <div class="stat-icon" style="background:#eef0fd;color:#4154f1"><i class="bi bi-calendar-check-fill"></i>
                </div>
                <div style="flex:1">
                    <div class="stat-num" data-target="{{ $stats['today_visits'] }}" style="color:#4154f1">
                        {{ $stats['today_visits'] }}</div>
                    <div class="stat-lbl">ថ្ងៃនេះ<br><small>Today</small></div>
                </div>
            </div>
        </div>

        {{-- IPD --}}
        <div class="col-6 col-sm-6 col-lg-3">
            <div class="stat-card" style="cursor:pointer"
                onclick="window.location='{{ url('/visits?type=IPD&status=active') }}'">
                <div class="stat-icon" style="background:#fff3e8;color:#ff771d"><i class="bi bi-bed-fill"></i></div>
                <div style="flex:1">
                    <div class="stat-num" data-target="{{ $stats['inpatients'] }}" style="color:#ff771d">
                        {{ $stats['inpatients'] }}</div>
                    <div class="stat-lbl">IPD<br><small>Inpatients</small></div>
                    @if ($stats['inpatients'] > 0)
                        <div style="margin-top:4px">
                            <span class="status-pill status-pill--active" style="font-size:9px;padding:1px 7px">
                                <span class="live-dot" style="width:5px;height:5px"></span> Admitted
                            </span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Month --}}
        <div class="col-6 col-sm-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#f0e8ff;color:#9b59b6"><i class="bi bi-graph-up"></i></div>
                <div style="flex:1">
                    <div class="stat-num" data-target="{{ $stats['this_month'] }}" style="color:#9b59b6">
                        {{ $stats['this_month'] }}</div>
                    <div class="stat-lbl">ខែនេះ<br><small>This Month</small></div>
                </div>
            </div>
        </div>

    </div>

    {{-- ── Operations quick tiles ────────────────────────────────────────── --}}
    <div class="row g-2 mb-3">
        @php
            $ops = [
                [
                    'url' => url('/workflow/create'),
                    'icon' => 'bi-plus-circle-fill',
                    'color' => '#4154f1',
                    'class' => 'qa-tile--consult',
                    'km' => 'ការចូលព្យាបាលថ្មី',
                    'en' => 'New Visit',
                    'badge' => null,
                ],
                [
                    'url' => url('/visits?status=active'),
                    'icon' => 'bi-stethoscope',
                    'color' => '#2eca6a',
                    'class' => 'qa-tile--payment',
                    'km' => 'ការចូលសកម្ម',
                    'en' => 'Active Visits',
                    'badge' => $stats['active_total'] > 0 ? $stats['active_total'] : null,
                ],
                [
                    'url' => url('/prescriptions'),
                    'icon' => 'bi-capsule-fill',
                    'color' => '#e91e8c',
                    'class' => 'qa-tile--rx',
                    'km' => 'វេជ្ជបញ្ជា',
                    'en' => 'Prescriptions',
                    'badge' => null,
                ],
                [
                    'url' => url('/invoices?status=pending'),
                    'icon' => 'bi-receipt-cutoff',
                    'color' => '#00bcd4',
                    'class' => 'qa-tile--invoice',
                    'km' => 'វិក្កយបត្ររងចាំ',
                    'en' => 'Pending Invoices',
                    'badge' => ($stats['pending_invoices'] ?? 0) > 0 ? $stats['pending_invoices'] : null,
                ],
            ];
        @endphp
        @foreach ($ops as $op)
            <div class="col-6 col-lg-3">
                <a href="{{ $op['url'] }}" class="qa-tile {{ $op['class'] }}">
                    <div class="qa-tile-icon" style="background:{{ $op['color'] }}"><i
                            class="bi {{ $op['icon'] }}"></i></div>
                    <div style="flex:1;min-width:0">
                        <div class="qa-tile-label">{{ $op['km'] }}</div>
                        <div class="qa-tile-sub">{{ $op['en'] }}</div>
                    </div>
                    @if ($op['badge'])
                        <span
                            style="background:{{ $op['color'] }};color:#fff;font-size:10px;padding:2px 9px;border-radius:10px;font-weight:800;flex-shrink:0">{{ $op['badge'] }}</span>
                    @else
                        <i class="bi bi-chevron-right" style="color:#cbd5e1;font-size:11px;flex-shrink:0"></i>
                    @endif
                </a>
            </div>
        @endforeach
    </div>

    {{-- ── Chart + Live feed ────────────────────────────────────────────── --}}
    <div class="row g-3 mb-3">

        {{-- 7-day bar chart --}}
        <div class="col-12 col-lg-8">
            <div class="card-emr" style="height:100%">
                <div class="card-hd">
                    <div class="card-hd-title"><i class="bi bi-bar-chart-fill" style="color:#4154f1"></i>
                        ការចូលព្យាបាល ៧ ថ្ងៃ <small style="font-weight:400;color:#aaa">/ Last 7 Days</small>
                    </div>
                    <div style="display:flex;gap:12px">
                        <span style="font-size:10px;color:#4154f1;display:flex;align-items:center;gap:4px"><span
                                style="width:8px;height:8px;border-radius:2px;background:#4154f1;display:inline-block"></span>OPD</span>
                        <span style="font-size:10px;color:#ff771d;display:flex;align-items:center;gap:4px"><span
                                style="width:8px;height:8px;border-radius:2px;background:#ff771d;display:inline-block"></span>IPD</span>
                    </div>
                </div>
                <div class="card-bd" style="padding-top:8px">
                    @php
                        $maxVal = max(1, collect($weeklyStats)->max(fn($d) => $d['opd'] + $d['ipd']));
                        $totalOpd = collect($weeklyStats)->sum('opd');
                        $totalIpd = collect($weeklyStats)->sum('ipd');
                        $total7 = $totalOpd + $totalIpd;
                    @endphp
                    <div style="height:160px;display:flex;align-items:flex-end;gap:5px;padding:0 4px">
                        @foreach ($weeklyStats as $d)
                            @php
                                $t = $d['opd'] + $d['ipd'];
                                $isToday = ($d['date'] ?? '') === today()->format('d/m');
                            @endphp
                            <div style="flex:1;display:flex;flex-direction:column;align-items:center;gap:3px;height:100%">
                                <div style="font-size:10px;color:{{ $t > 0 ? '#475569' : 'transparent' }};font-weight:700">
                                    {{ $t }}</div>
                                <div
                                    style="flex:1;width:100%;display:flex;flex-direction:column;justify-content:flex-end;gap:1px;position:relative">
                                    @if ($isToday)
                                        <div
                                            style="position:absolute;inset:0;background:rgba(65,84,241,.04);border-radius:4px;border:1px dashed rgba(65,84,241,.2)">
                                        </div>
                                    @endif
                                    @if ($d['opd'] > 0)
                                        <div
                                            style="height:{{ round(($d['opd'] / $maxVal) * 130) }}px;background:linear-gradient(180deg,#717ff5,#4154f1);border-radius:4px 4px 0 0;min-height:3px;position:relative;z-index:1">
                                        </div>
                                    @endif
                                    @if ($d['ipd'] > 0)
                                        <div
                                            style="height:{{ round(($d['ipd'] / $maxVal) * 130) }}px;background:linear-gradient(180deg,#ffaa6b,#ff771d);border-radius:{{ $d['opd'] > 0 ? '0' : '4px 4px' }} 0 0;min-height:3px;position:relative;z-index:1">
                                        </div>
                                    @endif
                                    @if ($t === 0)
                                        <div style="height:2px;background:#e2e8f0;border-radius:1px"></div>
                                    @endif
                                </div>
                                <div
                                    style="font-size:9.5px;color:{{ $isToday ? '#4154f1' : '#6b7280' }};font-weight:{{ $isToday ? '700' : '400' }};white-space:nowrap">
                                    {{ $d['day'] }}</div>
                            </div>
                        @endforeach
                    </div>
                    <div style="display:flex;margin-top:12px;padding-top:12px;border-top:1px solid #e6e9f0">
                        @foreach ([['7 ថ្ងៃ', '7-Day', $total7, '#1a1f36'], ['OPD', 'OPD', $totalOpd, '#4154f1'], ['IPD', 'IPD', $totalIpd, '#ff771d'], ['Rate', 'OPD%', $total7 > 0 ? round(($totalOpd / $total7) * 100) . '%' : '0%', '#2eca6a']] as [$km, $en, $val, $col])
                            <div style="flex:1;text-align:center;padding:0 4px;border-right:1px solid #e6e9f0">
                                <div style="font-size:18px;font-weight:800;color:{{ $col }}">{{ $val }}
                                </div>
                                <div style="font-size:9.5px;color:#6b7280">{{ $km }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Live activity feed --}}
        <div class="col-12 col-lg-4">
            <div class="card-emr" style="height:100%">
                <div class="card-hd">
                    <div class="card-hd-title"><i class="bi bi-broadcast" style="color:#22c55e"></i> Live</div>
                    <span class="status-pill status-pill--active" style="font-size:9.5px;padding:2px 10px">
                        <span class="live-dot" style="width:5px;height:5px"></span> Live
                    </span>
                </div>
                <div class="card-bd" style="padding:4px 0;overflow-y:auto;max-height:280px">
                    @forelse($recentVisits->take(8) as $v)
                        @php
                            $cols = ['#4154f1', '#2eca6a', '#ff771d', '#e74c3c', '#9b59b6', '#00bcd4'];
                            $col = $cols[abs(crc32($v->patient_code ?? '')) % 6];
                            $nm =
                                ($v->patient?->surname ?? ($v->surname ?? '')) .
                                ', ' .
                                ($v->patient?->name ?? ($v->name ?? ''));
                            $act = is_null($v->discharged_at);
                        @endphp
                        <a href="{{ url('/workflow/' . $v->code) }}"
                            style="display:flex;align-items:center;gap:10px;padding:9px 16px;border-bottom:1px solid #f1f5f9;text-decoration:none;transition:background .12s"
                            onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background=''">
                            <div
                                style="width:32px;height:32px;border-radius:9px;background:{{ $col }}18;color:{{ $col }};display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:800;flex-shrink:0;border:1.5px solid {{ $col }}33">
                                {{ strtoupper(substr($v->patient?->surname ?? ($v->surname ?? 'U'), 0, 1) . substr($v->patient?->name ?? ($v->name ?? ''), 0, 1)) }}
                            </div>
                            <div style="flex:1;min-width:0">
                                <div
                                    style="font-size:12.5px;font-weight:600;color:#1a1f36;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                                    {{ $nm }}</div>
                                <div style="font-size:10px;color:#6b7280;margin-top:1px">{{ $v->code }} ·
                                    {{ $v->visit_type }}</div>
                            </div>
                            <div style="display:flex;flex-direction:column;align-items:flex-end;gap:3px;flex-shrink:0">
                                @if ($act)
                                    <span class="live-dot" style="width:7px;height:7px"></span>
                                @else<i class="bi bi-check2-circle" style="color:#2eca6a;font-size:13px"></i>
                                @endif
                                <span
                                    style="font-size:9.5px;color:#cbd5e1">{{ $v->admitted_at?->format('H:i') ?? '—' }}</span>
                            </div>
                        </a>
                    @empty
                        <div style="text-align:center;padding:32px;color:#6b7280">
                            <div style="font-size:28px;opacity:.3;margin-bottom:6px">📋</div>
                            <div style="font-size:12px">No visits today</div>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- ── Recent visits table ─────────────────────────────────────────── --}}
    <div class="card-emr">
        <div class="card-hd">
            <div class="card-hd-title"><i class="bi bi-clock-history"></i>
                ការចូលព្យាបាលថ្មីៗ <small style="font-weight:400;color:#aaa">/ Recent Visits</small>
            </div>
            <a href="{{ url('/visits') }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-arrow-right"></i>
                មើលទាំងអស់</a>
        </div>
        <div class="card-bd" style="padding:0">
            <div class="table-responsive">
                <table class="tbl tbl-responsive-cards">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>អ្នកជំងឺ</th>
                            <th>ប្រភេទ</th>
                            <th>ស្ថានភាព</th>
                            <th>វឌ្ឍនភាព</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentVisits as $visit)
                            @php
                                $act = is_null($visit->discharged_at);
                                $done = count($visit->done_steps ?? []);
                                $pct = round(($done / 10) * 100);
                                $skip = count($visit->skipped_steps ?? []);
                                $nm =
                                    ($visit->patient?->surname ?? ($visit->surname ?? '')) .
                                    ', ' .
                                    ($visit->patient?->name ?? ($visit->name ?? ''));
                            @endphp
                            <tr style="cursor:pointer"
                                onclick="window.location='{{ url('/workflow/' . $visit->code) }}'">
                                <td data-label="Code">
                                    <code
                                        style="color:#4154f1;font-size:11.5px;font-weight:700">{{ $visit->code }}</code>
                                    <div style="font-size:10px;color:#cbd5e1;margin-top:1px">
                                        {{ $visit->admitted_at?->format('d/m H:i') }}</div>
                                </td>
                                <td data-label="Patient">
                                    <div style="font-weight:700;color:#1a1f36">{{ $nm }}</div>
                                    <div style="font-size:10.5px;color:#6b7280">{{ $visit->patient_code }}</div>
                                </td>
                                <td><x-status-badge :status="strtolower($visit->visit_type)" :label="$visit->visit_type" /></td>
                                <td>
                                    @if ($act)
                                        <span class="status-pill status-pill--active"><span class="live-dot"
                                                style="width:5px;height:5px"></span> Active</span>
                                    @else
                                        <span class="status-pill status-pill--done"><i class="bi bi-check2"
                                                style="font-size:9px"></i> Done</span>
                                    @endif
                                </td>
                                <td>
                                    <div style="font-size:10px;color:#64748b;margin-bottom:3px;font-weight:600">
                                        {{ $done }}/10
                                        @if ($skip > 0)
                                            <span style="color:#f59e0b;margin-left:3px">{{ $skip }}⏭</span>
                                        @endif
                                    </div>
                                    <div
                                        style="height:5px;background:#e2e8f0;border-radius:3px;width:80px;overflow:hidden">
                                        <div
                                            style="height:100%;width:{{ $pct }}%;background:{{ $pct >= 100 ? '#2eca6a' : 'linear-gradient(90deg,#4154f1,#6366f1)' }};border-radius:3px;transition:width .4s">
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <a href="{{ url('/workflow/' . $visit->code) }}" class="btn btn-sm btn-primary"
                                        onclick="event.stopPropagation()">
                                        <i class="bi bi-arrow-right-circle-fill"></i> បន្ត
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" style="text-align:center;padding:48px;color:#6b7280">
                                    <div style="font-size:40px;opacity:.2;margin-bottom:12px">🏥</div>
                                    <div style="font-size:14px;font-weight:600;color:#cbd5e1;margin-bottom:10px">
                                        មិនទាន់មានការចូលព្យាបាល</div>
                                    <a href="{{ url('/workflow/create') }}" class="btn btn-primary btn-sm"><i
                                            class="bi bi-plus-lg"></i> ថ្មី / New Visit</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        (function tick() {
            var el = document.getElementById('liveDate');
            if (el) {
                var n = new Date(),
                    p = v => String(v).padStart(2, '0');
                el.textContent = p(n.getDate()) + '/' + p(n.getMonth() + 1) + '/' + n.getFullYear() + ' ' + p(n
                    .getHours()) + ':' + p(n.getMinutes());
            }
            setTimeout(tick, 30000);
        })();
    </script>
@endpush
