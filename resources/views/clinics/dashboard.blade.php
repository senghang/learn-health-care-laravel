@extends('clinics.layout.app')

@section('title', 'ផ្ទាំងគ្រប់គ្រង')

@section('content')

    {{-- ── Page Header ─────────────────────────────────────────────────────── --}}
    <div class="pg-header">
        <div>
            <h1 class="pg-title">ផ្ទាំងគ្រប់គ្រង <small>/ Dashboard</small></h1>
            <div class="breadcrumb-row">
                <a href="{{ route('dashboard') }}">ដើម</a>
                <span>›</span><span>Dashboard</span>
            </div>
        </div>
        <div class="d-flex gap-2 align-items-center flex-wrap">
        <span
            style="font-size:11px;color:#aaa;background:#f6f9ff;padding:5px 12px;border-radius:20px;border:1px solid #e6eaf5">
            <i class="bi bi-calendar3"></i> {{ now()->format('d/m/Y · H:i') }}
        </span>
            <a href="{{ route('workflow.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> ការចូលព្យាបាលថ្មី
            </a>
        </div>
    </div>

    {{-- ── KPI Cards ───────────────────────────────────────────────────────── --}}
    <div class="row g-3 mb-3">
        <div class="col-6 col-xl-3">
            <div class="stat-card" style="cursor:pointer" onclick="window.location='{{ route('visits.index') }}'">
                <div class="stat-icon" style="background:#eef0fd;color:#4154f1"><i class="bi bi-hospital-fill"></i>
                </div>
                <div style="flex:1">
                    <div class="stat-num" style="color:#4154f1">{{ $stats['today_visits'] }}</div>
                    <div class="stat-lbl">ការចូលថ្ងៃនេះ<br><small>Today's Visits</small></div>
                    <div class="stat-trend text-success"><i class="bi bi-arrow-up-short"></i>+3 ពីម្សិលមិញ</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="stat-card" style="cursor:pointer"
                 onclick="window.location='{{ route('visits.index', ['type'=>'IPD']) }}'">
                <div class="stat-icon" style="background:#fff3e8;color:#ff771d"><i class="bi bi-bed-fill"></i></div>
                <div style="flex:1">
                    <div class="stat-num" style="color:#ff771d">{{ $stats['inpatients'] }}</div>
                    <div class="stat-lbl">អ្នកជំងឺ IPD<br><small>Inpatients</small></div>
                    <div class="stat-trend text-muted"><i class="bi bi-dash"></i>គ្មានផ្លាស់ប្ដូរ</div>
                </div>
            </div>
        </div>
        {{--        <div class="col-6 col-xl-3">--}}
        {{--            <div class="stat-card" style="cursor:pointer" onclick="window.location='{{ route('labs.index') }}'">--}}
        {{--                <div class="stat-icon" style="background:#e8f8ef;color:#2eca6a"><i class="bi bi-flask2-fill"></i></div>--}}
        {{--                <div style="flex:1">--}}
        {{--                    <div class="stat-num" style="color:#2eca6a">{{ $stats['lab_results'] }}</div>--}}
        {{--                    <div class="stat-lbl">លទ្ធផលពិសោធ<br><small>Lab Results</small></div>--}}
        {{--                    <div class="stat-trend text-danger"><i class="bi bi-exclamation-triangle-fill"></i>2 ក្សសោ</div>--}}
        {{--                </div>--}}
        {{--            </div>--}}
        {{--        </div>--}}
        {{--        <div class="col-6 col-xl-3">--}}
        {{--            <div class="stat-card" style="cursor:pointer" onclick="window.location='{{ route('billing.index') }}'">--}}
        {{--                <div class="stat-icon" style="background:#fde8e8;color:#e74c3c"><i class="bi bi-cash-stack"></i></div>--}}
        {{--                <div style="flex:1">--}}
        {{--                    <div class="stat-num" style="color:#e74c3c">{{ $stats['revenue'] }}</div>--}}
        {{--                    <div class="stat-lbl">ប្រាក់ចំណូល KHR<br><small>Today Revenue</small></div>--}}
        {{--                    <div class="stat-trend text-success"><i class="bi bi-arrow-up-short"></i>+18%</div>--}}
        {{--                </div>--}}
        {{--            </div>--}}
        {{--        </div>--}}
    </div>

    {{-- ── Chart + Quick Actions ───────────────────────────────────────────── --}}
    <div class="row g-3 mb-3">

        {{-- Bar Chart --}}
        <div class="col-12 col-lg-8">
            <div class="card-emr" style="height:100%">
                <div class="card-hd">
                    <div class="card-hd-title">
                        <i class="bi bi-bar-chart-fill"></i>
                        ការចូលព្យាបាល ៧ ថ្ងៃ <small style="font-weight:400;color:#aaa">/ Visits This Week</small>
                    </div>
                    <div style="display:flex;gap:10px">
                    <span style="font-size:10px;color:#4154f1;display:flex;align-items:center;gap:4px">
                        <span
                            style="width:10px;height:10px;border-radius:2px;background:#4154f1;display:inline-block"></span>OPD
                    </span>
                        <span style="font-size:10px;color:#ff771d;display:flex;align-items:center;gap:4px">
                        <span
                            style="width:10px;height:10px;border-radius:2px;background:#ff771d;display:inline-block"></span>IPD
                    </span>
                    </div>
                </div>
                <div class="card-bd" style="padding-top:8px">
                    @php
                        $chartData = $weeklyStats;
                        $maxVal    = collect($chartData)->max(fn($d) => $d['opd'] + $d['ipd']);
                        $maxVal    = max($maxVal, 1); // avoid division by zero
                    @endphp
                    <div style="height:160px;display:flex;align-items:flex-end;gap:6px;padding:0 4px">
                        @foreach($chartData as $d)
                            @php $total = $d['opd'] + $d['ipd']; @endphp
                            <div
                                style="flex:1;display:flex;flex-direction:column;align-items:center;gap:4px;height:100%">
                                <div style="font-size:10px;color:#aaa;font-weight:600">{{ $total }}</div>
                                <div
                                    style="flex:1;width:100%;display:flex;flex-direction:column;justify-content:flex-end;gap:1px">
                                    <div
                                        style="height:{{ $maxVal>0 ? round($d['opd']/$maxVal*130) : 0 }}px;background:linear-gradient(180deg,#717ff5,#4154f1);border-radius:3px 3px 0 0;min-height:{{ $d['opd']>0?2:0 }}px"></div>
                                    <div
                                        style="height:{{ $maxVal>0 ? round($d['ipd']/$maxVal*130) : 0 }}px;background:linear-gradient(180deg,#ffaa6b,#ff771d);border-radius:{{ $d['opd']>0?'0':'3px 3px' }} 0 0;min-height:{{ $d['ipd']>0?2:0 }}px"></div>
                                </div>
                                <div style="font-size:10px;color:#aaa;white-space:nowrap">{{ $d['day'] }}</div>
                            </div>
                        @endforeach
                    </div>
                    @php $totalOpd=collect($chartData)->sum('opd'); $totalIpd=collect($chartData)->sum('ipd'); $total7=$totalOpd+$totalIpd; @endphp
                    <div style="display:flex;gap:0;margin-top:12px;padding-top:12px;border-top:1px solid #f0f2ff">
                        @foreach([['ក្នុង 7 ថ្ងៃ','Total',$total7,'#012970'],['OPD','OPD',$totalOpd,'#4154f1'],['IPD','IPD',$totalIpd,'#ff771d'],['OPD Rate','Rate',($total7>0?round($totalOpd/$total7*100):0).'%','#2eca6a']] as [$lkm,$len,$val,$col])
                            <div
                                style="flex:1;text-align:center;padding:0 4px;border-right:1px solid #f0f2ff;last:border:none">
                                <div style="font-size:18px;font-weight:800;color:{{ $col }}">{{ $val }}</div>
                                <div style="font-size:10px;color:#aaa">{{ $lkm }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Quick Actions --}}
        <div class="col-12 col-lg-4">
            <div class="card-emr" style="height:100%">
                <div class="card-hd">
                    <div class="card-hd-title">
                        <i class="bi bi-lightning-fill" style="color:#ff771d"></i>
                        សកម្មភាពរហ័ស <small style="font-weight:400;color:#aaa">/ Quick Actions</small>
                    </div>
                </div>
                <div class="card-bd" style="padding:8px 12px;display:flex;flex-direction:column;gap:7px">
                    @foreach([
                        [route('workflow.create'),             'bi-plus-circle-fill', '#4154f1','#eef0fd','#e0e4fb', 'ការចូលព្យាបាលថ្មី',  'New Visit',        null],
                        [route('visits.index',['status'=>'active']),'bi-activity','#ff771d','#fff3e8','#ffe8d0', 'ការចូលសកម្ម',      'Active Visits',    $stats['today_visits']],
                        // [route('labs.index'),                  'bi-flask2-fill',      '#2eca6a','#e8f8ef','#d0f0dd', 'លទ្ធផលពិសោធ',      'Lab Results',      $stats['critical_labs']],
                        // [route('billing.index'),               'bi-receipt',          '#e74c3c','#fde8e8','#f9d0d0', 'វិក្កយបត្រ',         'Billing',          null],
                        // [route('pharmacy.index'),              'bi-capsule-fill',     '#9b59b6','#f0e8ff','#e4d8f8', 'ឱសថស្ថាន',          'Pharmacy',         null],
                    ] as [$url,$ico,$col,$bg,$hover,$km,$en,$badge])
                        <a href="{{ $url }}"
                           style="display:flex;align-items:center;gap:12px;padding:11px 14px;border-radius:10px;background:{{ $bg }};text-decoration:none;transition:background .15s"
                           onmouseover="this.style.background='{{ $hover }}'"
                           onmouseout="this.style.background='{{ $bg }}'">
                            <div
                                style="width:34px;height:34px;border-radius:9px;background:{{ $col }};color:#fff;display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0">
                                <i class="bi {{ $ico }}"></i>
                            </div>
                            <div style="flex:1;min-width:0">
                                <div style="font-weight:700;font-size:13px;color:#012970">{{ $km }}</div>
                                <div style="font-size:11px;color:#999">{{ $en }}</div>
                            </div>
                            @if($badge !== null)
                                <span
                                    style="background:{{ $col }};color:#fff;font-size:10px;padding:2px 8px;border-radius:10px;font-weight:700">{{ $badge }}</span>
                            @else
                                <i class="bi bi-chevron-right" style="color:#ddd;font-size:11px"></i>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- ── Recent Visits + Activity Feed ─────────────────────────────────── --}}
    <div class="row g-3">

        <div class="col-12 col-lg-8">
            <div class="card-emr">
                <div class="card-hd">
                    <div class="card-hd-title">
                        <i class="bi bi-clock-history"></i>
                        ការចូលព្យាបាលថ្មីៗ <small style="font-weight:400;color:#aaa">/ Recent Visits</small>
                    </div>
                    <a href="{{ route('visits.index') }}" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-arrow-right"></i> មើលទាំងអស់
                    </a>
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
                                <th>ដំណើរការ</th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($recentVisits as $visit)
                                <tr style="cursor:pointer"
                                    onclick="window.location='{{ route('workflow.show', $visit->code) }}'">
                                    <td data-label="Code">
                                        <code style="color:#4154f1;font-size:12px">{{ $visit->code }}</code>
                                        <div
                                            style="font-size:10px;color:#ccc">{{ $visit->admitted_at?->format('d/m H:i') }}</div>
                                    </td>
                                    <td data-label="អ្នកជំងឺ">
                                        <div
                                            style="font-weight:700">{{ $visit->patient->full_name ?? ($visit->surname.', '.$visit->given_name) }}</div>
                                        <div style="font-size:10.5px;color:#aaa">{{ $visit->patient_code }}</div>
                                    </td>
                                    <td><span
                                            class="badge-s {{ $visit->visit_type==='IPD'?'b-ipd':'b-opd' }}">{{ $visit->visit_type }}</span>
                                    </td>
                                    <td>
                                        @if(is_null($visit->discharged_at))
                                            <span class="badge-s b-active"><i class="bi bi-circle-fill"
                                                                              style="font-size:7px"></i>Active</span>
                                        @else
                                            <span class="badge-s b-done">✓ Done</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div style="font-size:10px;color:#aaa;margin-bottom:3px;font-weight:600">
                                            {{ $visit->steps_done ?? 0 }}/10
                                            @if(count($visit->skipped_steps ?? []) > 0)
                                                <span
                                                    style="color:#c97700"> · {{ count($visit->skipped_steps) }}⏭</span>
                                            @endif
                                        </div>
                                        <div class="prog-bar" style="width:90px">
                                            <div class="prog-fill"
                                                 style="width:{{ $visit->progress_percent ?? 0 }}%"></div>
                                        </div>
                                    </td>
                                    <td>
                                        <a href="{{ route('workflow.show', $visit->code) }}"
                                           class="btn btn-sm btn-primary" onclick="event.stopPropagation()">
                                            <i class="bi bi-pencil-fill"></i>បន្ត
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" style="text-align:center;padding:32px;color:#bbb">
                                        <div style="font-size:28px;margin-bottom:8px;opacity:.4">🏥</div>
                                        <div style="font-size:13px;font-weight:600;margin-bottom:6px">
                                            មិនទាន់មានការចូលព្យាបាលថ្ងៃនេះ
                                        </div>
                                        <div style="font-size:11px;margin-bottom:12px">No visits recorded today</div>
                                        <a href="{{ route('workflow.create') }}" class="btn btn-sm btn-primary">
                                            <i class="bi bi-plus-lg"></i> ចាប់ផ្ដើម / New Visit
                                        </a>
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Activity Feed --}}
        <div class="col-12 col-lg-4">
            <div class="card-emr">
                <div class="card-hd">
                    <div class="card-hd-title">
                        <i class="bi bi-bell-fill" style="color:#9b59b6"></i>
                        សកម្មភាពថ្មីៗ <small style="font-weight:400;color:#aaa">/ Activity</small>
                    </div>
                    <span
                        style="font-size:10px;color:#2eca6a;background:#e8f8ef;padding:2px 10px;border-radius:20px;border:1px solid #a8e6c2;font-weight:700">
                    <i class="bi bi-circle-fill" style="font-size:6px"></i> Live
                </span>
                </div>
                <div class="card-bd" style="padding:4px 0">
                    @forelse($recentVisits->take(7) as $v)
                        @php
                            $vColors = ['#4154f1','#2eca6a','#ff771d','#e74c3c','#9b59b6','#00bcd4'];
                            $vColor  = $vColors[abs(crc32($v->patient_code)) % count($vColors)];
                            $isNew   = $v->created_at->diffInMinutes(now()) < 60;
                        @endphp
                        <a href="{{ route('workflow.show', $v->code) }}"
                           style="display:flex;align-items:flex-start;gap:10px;padding:9px 16px;border-bottom:1px solid #f5f6ff;transition:background .12s;text-decoration:none"
                           onmouseover="this.style.background='#fafbff'" onmouseout="this.style.background=''">
                            <div
                                style="width:30px;height:30px;border-radius:8px;background:{{ $vColor }}22;color:{{ $vColor }};display:flex;align-items:center;justify-content:center;font-size:13px;flex-shrink:0;margin-top:1px;font-weight:800">
                                {{ strtoupper(substr($v->surname,0,1).substr($v->given_name,0,1)) }}
                            </div>
                            <div style="flex:1;min-width:0">
                                <div
                                    style="font-size:12.5px;font-weight:600;color:#012970;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                                    {{ $v->surname }}, {{ $v->given_name }}
                                </div>
                                <div style="font-size:10.5px;color:#aaa;margin-top:1px">
                                    {{ $v->code }} ·
                                    <span class="badge-s {{ $v->visit_type==='IPD'?'b-ipd':'b-opd' }}"
                                          style="font-size:9px;padding:1px 6px">{{ $v->visit_type }}</span>
                                    @if(is_null($v->discharged_at))
                                        · <span style="color:#2eca6a;font-weight:600">Active</span>
                                    @else
                                        · <span style="color:#aaa">Done</span>
                                    @endif
                                </div>
                            </div>
                            <div style="font-size:10px;color:#ccc;flex-shrink:0;margin-top:2px;white-space:nowrap">
                                {{ $v->admitted_at?->diffForHumans(null, true) ?? '—' }}
                            </div>
                        </a>
                    @empty
                        <div style="text-align:center;padding:32px 16px;color:#bbb">
                            <div style="font-size:28px;margin-bottom:8px;opacity:.3">📋</div>
                            <div style="font-size:12px">មិនទាន់មានការចូលព្យាបាល<br>No visits yet</div>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

    </div>

@endsection

@push('scripts')
    <script>
        /* Animate stat numbers counting up on load */
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.stat-num').forEach(el => {
                const raw = el.textContent.trim();
                const num = parseFloat(raw.replace(/[^\d.]/g, ''));
                if (!isFinite(num) || num === 0) return;
                const suffix = raw.replace(/[\d.]/g, '');
                let cur = 0;
                const step = num / (700 / 16);
                const tick = () => {
                    cur = Math.min(cur + step, num);
                    el.textContent = (Number.isInteger(num) ? Math.round(cur) : cur.toFixed(1)) + suffix;
                    if (cur < num) requestAnimationFrame(tick);
                };
                requestAnimationFrame(tick);
            });
        });
    </script>
@endpush
