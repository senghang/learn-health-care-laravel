@extends('clinics.layout.app')
@section('title', __('app.laboratory'))

@section('content')
<x-page-header :title="__('app.laboratory')" subtitle="Laboratory Orders"
    :breadcrumbs="[['label'=>__('app.nav.dashboard'),'url'=>route('dashboard')],['label'=>__('app.laboratory')]]">
    <a href="#" onclick="document.getElementById('newLabModal').classList.remove('hidden')" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg"></i> {{ __('app.lab_request') ?? 'New Lab Order' }}
    </a>
</x-page-header>

{{-- KPI Stats --}}
<div class="row g-3 mb-3">
    @foreach([
        ['label'=>'Today','value'=>$stats['today_orders'],'icon'=>'bi-droplet-fill','color'=>'#4154f1','bg'=>'#eef0fd'],
        ['label'=>'Pending','value'=>$stats['pending'],'icon'=>'bi-hourglass-split','color'=>'#ff771d','bg'=>'#fff3e8'],
        ['label'=>'In Progress','value'=>$stats['in_progress'],'icon'=>'bi-arrow-repeat','color'=>'#9b59b6','bg'=>'#f0e8ff'],
        ['label'=>'Completed Today','value'=>$stats['completed_today'],'icon'=>'bi-check-circle-fill','color'=>'#2eca6a','bg'=>'#e8f8ef'],
    ] as $s)
    <div class="col-6 col-xl-3">
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
            <div class="col-12 col-sm-4">
                <input type="text" name="search" class="form-control" placeholder="{{ __('app.search') }}…" value="{{ request('search') }}"/>
            </div>
            <div class="col-6 col-sm-2">
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    @foreach(['requested','collected','processing','completed'] as $st)
                        <option value="{{ $st }}" {{ request('status')===$st?'selected':'' }}>{{ ucfirst($st) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-sm-2">
                <select name="urgency" class="form-select">
                    <option value="">All Urgency</option>
                    @foreach(['normal','urgent','stat'] as $u)
                        <option value="{{ $u }}" {{ request('urgency')===$u?'selected':'' }}>{{ strtoupper($u) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-sm-2">
                <input type="date" name="date" class="form-control" value="{{ request('date') }}"/>
            </div>
            <div class="col-6 col-sm-2 d-flex gap-2">
                <button class="btn btn-primary btn-sm flex-fill"><i class="bi bi-search"></i></button>
                <a href="{{ route('laboratory.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-x-lg"></i></a>
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
                        <th>Code</th>
                        <th>Patient</th>
                        <th>Title</th>
                        <th>Urgency</th>
                        <th>Status</th>
                        <th>Requested</th>
                        <th>Tests</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($labs as $lab)
                    @php
                        $statusColors = [
                            'requested'=>['#ff771d','#fff3e8'], 'collected'=>['#4154f1','#eef0fd'],
                            'processing'=>['#9b59b6','#f0e8ff'], 'completed'=>['#2eca6a','#e8f8ef'],
                        ];
                        [$sc,$sbg] = $statusColors[$lab->status] ?? ['#aaa','#f5f5f5'];
                        $urgColors = ['normal'=>'#2eca6a','urgent'=>'#ff771d','stat'=>'#e74c3c'];
                    @endphp
                    <tr>
                        <td><code style="font-size:11px;color:#4154f1">{{ $lab->code }}</code></td>
                        <td>
                            <div style="font-weight:700;color:#012970;font-size:12.5px">{{ $lab->patient?->surname }}, {{ $lab->patient?->name }}</div>
                            <div style="font-size:10px;color:#aaa">{{ $lab->patient_code }}</div>
                        </td>
                        <td style="font-size:12.5px">{{ $lab->title ?? '—' }}</td>
                        <td>
                            <span style="font-size:10px;font-weight:700;color:{{ $urgColors[$lab->urgency] ?? '#aaa' }}">
                                {{ strtoupper($lab->urgency ?? 'normal') }}
                            </span>
                        </td>
                        <td>
                            <span style="background:{{ $sbg }};color:{{ $sc }};font-size:10px;font-weight:700;padding:2px 8px;border-radius:20px">
                                {{ ucfirst($lab->status) }}
                            </span>
                        </td>
                        <td style="font-size:11px;color:#888">{{ $lab->requested_at?->format('d/m H:i') }}</td>
                        <td style="font-size:11px;color:#666">{{ $lab->results_count ?? $lab->results->count() }}</td>
                        <td>
                            <a href="{{ route('laboratory.show', $lab->code) }}" class="btn btn-sm btn-outline-primary" style="font-size:11px;padding:2px 8px">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" style="text-align:center;padding:40px;color:#bbb">{{ __('app.no_records') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{ $labs->links() }}
@endsection
