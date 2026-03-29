@extends('clinics.layout.app')
@section('title', __('app.imagery'))

@section('content')
<x-page-header :title="__('app.imagery') ?? 'Imaging'" subtitle="Imaging Orders"
    :breadcrumbs="[['label'=>__('app.nav.dashboard'),'url'=>route('dashboard')],['label'=>'Imaging']]">
</x-page-header>

{{-- KPI Stats --}}
<div class="row g-3 mb-3">
    @foreach([
        ['label'=>'Today','value'=>$stats['today_orders'],'icon'=>'bi-camera-fill','color'=>'#4154f1','bg'=>'#eef0fd'],
        ['label'=>'Pending','value'=>$stats['pending'],'icon'=>'bi-hourglass-split','color'=>'#ff771d','bg'=>'#fff3e8'],
        ['label'=>'Completed Today','value'=>$stats['completed_today'],'icon'=>'bi-check-circle-fill','color'=>'#2eca6a','bg'=>'#e8f8ef'],
    ] as $s)
    <div class="col-6 col-xl-4">
        <div class="stat-card">
            <div class="stat-icon" style="background:{{ $s['bg'] }};color:{{ $s['color'] }}"><i class="bi {{ $s['icon'] }}"></i></div>
            <div><div class="stat-num" style="color:{{ $s['color'] }}">{{ $s['value'] }}</div><div class="stat-lbl">{{ $s['label'] }}</div></div>
        </div>
    </div>
    @endforeach
</div>

{{-- Filter --}}
<div class="card-emr mb-3">
    <div class="card-bd">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-12 col-sm-3">
                <input type="text" name="search" class="form-control" placeholder="{{ __('app.search') }}…" value="{{ request('search') }}"/>
            </div>
            <div class="col-6 col-sm-2">
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    @foreach(['requested','completed'] as $st)
                        <option value="{{ $st }}" {{ request('status')===$st?'selected':'' }}>{{ ucfirst($st) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-sm-2">
                <select name="category" class="form-select">
                    <option value="">All Types</option>
                    @foreach(['xray'=>'X-Ray','ultrasound'=>'Ultrasound','ct_scan'=>'CT Scan','mri'=>'MRI','ecg'=>'ECG'] as $k=>$v)
                        <option value="{{ $k }}" {{ request('category')===$k?'selected':'' }}>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-sm-2">
                <input type="date" name="date" class="form-control" value="{{ request('date') }}"/>
            </div>
            <div class="col-6 col-sm-3 d-flex gap-2">
                <button class="btn btn-primary btn-sm flex-fill"><i class="bi bi-search"></i></button>
                <a href="{{ route('imagery.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-x-lg"></i></a>
            </div>
        </form>
    </div>
</div>

{{-- Table --}}
<div class="card-emr">
    <div class="card-bd" style="padding:0">
        <div class="table-responsive">
            <table class="tbl">
                <thead>
                    <tr>
                        <th>Code</th><th>Patient</th><th>Category</th><th>Urgency</th><th>Status</th><th>Requested</th><th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($imageries as $img)
                    @php
                        $sc = ['requested'=>'#ff771d','completed'=>'#2eca6a'][$img->status] ?? '#aaa';
                        $catIcons = ['xray'=>'📷','ultrasound'=>'🔊','ct_scan'=>'🔬','mri'=>'🧲','ecg'=>'💓'];
                    @endphp
                    <tr>
                        <td><code style="font-size:11px;color:#4154f1">{{ $img->code }}</code></td>
                        <td>
                            <div style="font-weight:700;color:#012970;font-size:12.5px">{{ $img->patient?->surname }}, {{ $img->patient?->name }}</div>
                            <div style="font-size:10px;color:#aaa">{{ $img->patient_code }}</div>
                        </td>
                        <td>
                            <span style="font-size:12px">{{ $catIcons[$img->category] ?? '📋' }} {{ ucfirst(str_replace('_', ' ', $img->category ?? '—')) }}</span>
                        </td>
                        <td style="font-size:10px;font-weight:700;color:{{ ['normal'=>'#2eca6a','urgent'=>'#ff771d','stat'=>'#e74c3c'][$img->urgency] ?? '#aaa' }}">
                            {{ strtoupper($img->urgency ?? 'normal') }}
                        </td>
                        <td><span style="background:{{ $sc }}22;color:{{ $sc }};font-size:10px;font-weight:700;padding:2px 8px;border-radius:20px">{{ ucfirst($img->status) }}</span></td>
                        <td style="font-size:11px;color:#888">{{ $img->requested_at?->format('d/m H:i') }}</td>
                        <td><a href="{{ route('imagery.show', $img->code) }}" class="btn btn-sm btn-outline-primary" style="font-size:11px;padding:2px 8px"><i class="bi bi-eye"></i></a></td>
                    </tr>
                    @empty
                    <tr><td colspan="7" style="text-align:center;padding:40px;color:#bbb">{{ __('app.no_records') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{ $imageries->links() }}
@endsection
