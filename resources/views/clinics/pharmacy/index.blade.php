@extends('clinics.layout.app')
@section('title', 'Pharmacy — Dispensing Queue')

@section('content')

<x-page-header
    title="ឱសថស្ថាន / Pharmacy"
    subtitle="Dispensing Queue"
    :breadcrumbs="[['label' => __('app.home'), 'url' => route('dashboard')], ['label' => 'Pharmacy']]">
</x-page-header>

@if(session('success'))
    <div class="note note-success mb-3">
        <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
    </div>
@endif

{{-- KPI Strip --}}
<div class="row g-3 mb-3">
    @foreach([
        [$stats['pending'],   'bi-hourglass-split',    '#ff771d','#fff4ec', 'Pending',   'ត្រូវចែកចាយ'],
        [$stats['partial'],   'bi-pie-chart-fill',     '#9b59b6','#f5eeff', 'Partial',   'ចែកផ្នែក'],
        [$stats['today'],     'bi-bag-check-fill',     '#2eca6a','#e8f8ef', 'Done Today','ថ្ងៃនេះ'],
        [$stats['low_stock'], 'bi-exclamation-triangle-fill','#e74c3c','#fde8e8','Low Stock','ស្តុកទាប'],
    ] as [$val, $ico, $col, $bg, $en, $km])
        <div class="col-6 col-sm-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:{{ $bg }};color:{{ $col }}">
                    <i class="bi {{ $ico }}"></i>
                </div>
                <div>
                    <div class="stat-num" style="color:{{ $col }};font-size:20px">{{ $val }}</div>
                    <div class="stat-lbl">{{ $km }}<br><small>{{ $en }}</small></div>
                </div>
            </div>
        </div>
    @endforeach
</div>

{{-- Filters --}}
<div class="card-emr mb-3">
    <div class="card-bd">
        <form method="GET" action="{{ route('pharmacy.index') }}">
            <div class="row g-2 align-items-end">

                {{-- Status tabs --}}
                <div class="col-12 col-sm-auto">
                    <div class="btn-group btn-group-sm" role="group">
                        @foreach(['pending' => ['⏸ Pending', '#ff771d'], 'partial' => ['⏳ Partial', '#9b59b6'], 'dispensed' => ['✅ Done', '#2eca6a'], 'all' => ['All', '#aaa']] as $s => [$lbl, $col])
                            <a href="{{ route('pharmacy.index', array_merge(request()->query(), ['status' => $s])) }}"
                               class="btn {{ $status === $s ? 'btn-dark' : 'btn-outline-secondary' }}"
                               style="{{ $status === $s ? "background:{$col};border-color:{$col}" : '' }}">
                                {{ $lbl }}
                            </a>
                        @endforeach
                    </div>
                </div>

                <div class="col-12 col-sm-4">
                    <input type="hidden" name="status" value="{{ $status }}"/>
                    <input type="text" name="search" class="form-control form-control-sm"
                           placeholder="Patient name or Rx code…"
                           value="{{ request('search') }}" autofocus/>
                </div>
                <div class="col-6 col-sm-2">
                    <input type="date" name="date" class="form-control form-control-sm"
                           value="{{ request('date') }}"/>
                </div>
                <div class="col-auto d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="bi bi-funnel-fill"></i>
                    </button>
                    @if(request()->hasAny(['search','date']))
                        <a href="{{ route('pharmacy.index', ['status' => $status]) }}"
                           class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-x-circle"></i>
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Dispensing Queue Table --}}
<div class="card-emr">
    <div class="card-hd">
        <div class="card-hd-title">
            <i class="bi bi-bag-heart-fill" style="color:#7c3aed"></i>
            Dispensing Queue
        </div>
        <span style="font-size:11px;color:#aaa">{{ $prescriptions->total() }} {{ __('app.records') }}</span>
    </div>
    <div class="card-bd" style="padding:0">
        <div class="table-responsive">
            <table class="tbl">
                <thead>
                    <tr>
                        <th>Date / Time</th>
                        <th>Rx Code</th>
                        <th>Patient</th>
                        <th>Doctor</th>
                        <th>Items</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                @forelse($prescriptions as $rx)
                    @php
                        $hasCatalogMeds = $rx->medications->where('medication_code', '!=', null)->count();
                        $statusCfg = match($rx->dispensed_status) {
                            'dispensed' => ['✅ Dispensed', '#2eca6a', '#e8f8ef'],
                            'partial'   => ['⏳ Partial',   '#9b59b6', '#f5eeff'],
                            default     => ['⏸ Pending',   '#ff771d', '#fff4ec'],
                        };
                        [$statusLabel, $statusColor, $statusBg] = $statusCfg;
                    @endphp
                    <tr>
                        <td>
                            <div style="font-size:12px;font-weight:600;color:#012970">
                                {{ $rx->prescribed_at?->format('d/m/Y') ?? '—' }}
                            </div>
                            <div style="font-size:10px;color:#aaa">{{ $rx->prescribed_at?->format('H:i') }}</div>
                        </td>
                        <td>
                            <code style="font-size:11px;color:#e91e8c">{{ $rx->code }}</code>
                        </td>
                        <td>
                            @if($rx->patient)
                                <div style="font-weight:600;color:#012970;font-size:13px">
                                    {{ $rx->patient->surname }}, {{ $rx->patient->name }}
                                </div>
                                <div style="font-size:10.5px;color:#aaa">{{ $rx->patient_code }}</div>
                            @else
                                <span style="color:#bbb">—</span>
                            @endif
                        </td>
                        <td style="font-size:12px;color:#555">{{ $rx->prescribed_by ?? '—' }}</td>
                        <td>
                            <span style="font-size:13px;font-weight:700;color:#e91e8c">
                                {{ $rx->medications->count() }}
                            </span>
                            <span style="font-size:10px;color:#aaa"> items</span>
                            @if($hasCatalogMeds < $rx->medications->count())
                                <div style="font-size:10px;color:#e74c3c;margin-top:2px">
                                    <i class="bi bi-exclamation-triangle-fill"></i>
                                    {{ $rx->medications->count() - $hasCatalogMeds }} without catalogue code
                                </div>
                            @endif
                        </td>
                        <td>
                            <span style="font-size:11px;padding:3px 10px;border-radius:8px;font-weight:700;background:{{ $statusBg }};color:{{ $statusColor }}">
                                {{ $statusLabel }}
                            </span>
                            @if($rx->dispensed_by && $rx->dispensed_status === 'dispensed')
                                <div style="font-size:10px;color:#aaa;margin-top:2px">
                                    by {{ $rx->dispensed_by }}
                                </div>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('pharmacy.show', $rx->code) }}"
                               class="btn btn-sm {{ $rx->dispensed_status !== 'dispensed' ? 'btn-primary' : 'btn-outline-secondary' }}">
                                @if($rx->dispensed_status !== 'dispensed')
                                    <i class="bi bi-bag-fill"></i> Dispense
                                @else
                                    <i class="bi bi-eye-fill"></i> View
                                @endif
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align:center;padding:50px;color:#bbb">
                            <div style="font-size:40px;margin-bottom:12px;opacity:.3">💊</div>
                            <div style="font-size:13px;font-weight:600;margin-bottom:6px">
                                No prescriptions in the {{ $status }} queue
                            </div>
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
