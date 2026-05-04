@extends('clinics.layout.app')
@section('title', 'Doctor Performance')
@section('content')

<x-page-header title="សមត្ថភាពវេជ្ជបណ្ឌិត" subtitle="Doctor Performance"
    :breadcrumbs="[['label'=>'ដើម','url'=>url('/')],['label'=>'Reports'],['label'=>'Doctor Performance']]">
</x-page-header>

{{-- Date filter --}}
<div class="card-emr mb-3">
    <div class="card-bd">
        <form method="GET" action="{{ route('reports.doctor-performance') }}" class="row g-2 align-items-end">
            <div class="col-6 col-sm-3">
                <label class="flbl"><span class="km">ពី</span><span class="en">/ From</span></label>
                <input type="date" name="date_from" class="form-control"
                       value="{{ request('date_from', $dateFrom->format('Y-m-d')) }}"/>
            </div>
            <div class="col-6 col-sm-3">
                <label class="flbl"><span class="km">ដល់</span><span class="en">/ To</span></label>
                <input type="date" name="date_to" class="form-control"
                       value="{{ request('date_to', $dateTo->format('Y-m-d')) }}"/>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary"><i class="bi bi-funnel-fill"></i> Filter</button>
            </div>
        </form>
    </div>
</div>

{{-- Summary --}}
@if($performance->count() > 0)
<div class="row g-3 mb-3">
    @foreach([
        [$performance->count(), 'bi-person-badge-fill', '#4154f1','#eef0fd', 'វេជ្ជបណ្ឌិត','Doctors'],
        [$performance->sum('visits'), 'bi-hospital-fill', '#2eca6a','#e8f8ef', 'ការចូលព្យាបាល','Visits'],
        [$performance->sum('diagnoses'), 'bi-bullseye', '#ff771d','#fff3e8', 'រោគវិនិច្ឆ័យ','Diagnoses'],
        [$performance->sum('prescriptions'), 'bi-capsule-fill', '#9b59b6','#f0e8ff', 'វេជ្ជបញ្ជា','Prescriptions'],
    ] as [$val,$icon,$col,$bg,$km,$en])
    <div class="col-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:{{ $bg }};color:{{ $col }}"><i class="bi {{ $icon }}"></i></div>
            <div><div class="stat-num" style="color:{{ $col }}">{{ $val }}</div>
                <div class="stat-lbl">{{ $km }}<br><small>{{ $en }}</small></div></div>
        </div>
    </div>
    @endforeach
</div>
@endif

{{-- Performance table --}}
<div class="card-emr">
    <div class="card-hd">
        <div class="card-hd-title">
            <i class="bi bi-person-badge-fill" style="color:#4154f1"></i>
            លទ្ធផល / Results
            <small style="font-weight:400;color:#aaa">{{ $dateFrom->format('d/m/Y') }} — {{ $dateTo->format('d/m/Y') }}</small>
        </div>
    </div>
    <div class="card-bd" style="padding:0">
        <table class="tbl">
            <thead>
                <tr>
                    <th>#</th>
                    <th>វេជ្ជបណ្ឌិត / Doctor</th>
                    <th>ការចូលព្យាបាល</th>
                    <th>រោគវិនិច្ឆ័យ</th>
                    <th>វេជ្ជបញ្ជា</th>
                    <th>Dx / Visit</th>
                </tr>
            </thead>
            <tbody>
            @forelse($performance as $i => $row)
            @php
                $colors = ['#4154f1','#2eca6a','#ff771d','#e74c3c','#9b59b6','#00bcd4'];
                $col    = $colors[$i % count($colors)];
                $dxRate = $row['visits'] > 0 ? round($row['diagnoses'] / $row['visits'], 1) : 0;
            @endphp
            <tr>
                <td style="color:#bbb;font-size:12px">{{ $i + 1 }}</td>
                <td>
                    <div style="display:flex;align-items:center;gap:10px">
                        <div style="width:34px;height:34px;border-radius:9px;background:{{ $col }}22;color:{{ $col }};display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:800;flex-shrink:0">
                            {{ strtoupper(substr($row['doctor'], 0, 1)) }}
                        </div>
                        <div>
                            <div style="font-weight:700;color:#1a1f36;font-size:13px">{{ $row['doctor'] }}</div>
                        </div>
                    </div>
                </td>
                <td>
                    <span style="font-size:18px;font-weight:800;color:#4154f1">{{ $row['visits'] }}</span>
                    <div style="height:4px;background:#eef0fd;border-radius:2px;margin-top:3px;width:80px">
                        @php $maxVisits = $performance->max('visits') ?: 1; @endphp
                        <div style="height:100%;width:{{ round($row['visits']/$maxVisits*100) }}%;background:#4154f1;border-radius:2px"></div>
                    </div>
                </td>
                <td style="font-size:16px;font-weight:700;color:#ff771d">{{ $row['diagnoses'] }}</td>
                <td style="font-size:16px;font-weight:700;color:#9b59b6">{{ $row['prescriptions'] }}</td>
                <td>
                    <span style="font-size:13px;font-weight:700;background:#e6e9f0;color:#4154f1;padding:3px 10px;border-radius:20px">
                        {{ $dxRate }}
                    </span>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" style="text-align:center;padding:40px;color:#bbb">
                <div style="font-size:36px;margin-bottom:10px;opacity:.3">👨‍⚕️</div>
                <div style="font-size:13px;font-weight:600;margin-bottom:6px">No data found for this period</div>
                <div style="font-size:11px">Diagnoses must have a <strong>diagnosed_by</strong> value to appear here.</div>
            </td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($performance->isEmpty())
<div class="note note-info mt-3">
    <i class="bi bi-info-circle-fill"></i>
    <div>
        ទិន្នន័យនឹងបង្ហាញ នៅពេលដែលវេជ្ជបណ្ឌិតបំពេញ <strong>Diagnosed By</strong> នៅក្នុងជំហានរោគវិនិច្ឆ័យ។<br>
        <span style="font-size:11px;color:#6979de">Data appears when the <strong>Diagnosed By</strong> field is filled in the Diagnosis workflow step.</span>
    </div>
</div>
@endif

@endsection
