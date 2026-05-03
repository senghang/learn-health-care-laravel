@extends('clinics.layout.app')
@section('title', 'Pharmacy — Dispensing Queue')

@section('content')

<x-ui.page-header
    km="ឱសថស្ថាន"
    title="Pharmacy — Dispensing Queue"
    :breadcrumbs="[
        ['label' => __('app.home'), 'url' => route('dashboard')],
        ['label' => 'Pharmacy'],
    ]">
</x-ui.page-header>

@if(session('success'))
    <x-ui.alert type="success" class="mb-3">{{ session('success') }}</x-ui.alert>
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
<x-ui.card class="mb-3">
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
                <x-ui.button type="submit" variant="primary" size="sm">
                    <x-slot:icon><i class="bi bi-funnel-fill"></i></x-slot:icon>
                </x-ui.button>
                @if(request()->hasAny(['search','date']))
                    <x-ui.button href="{{ route('pharmacy.index', ['status' => $status]) }}" variant="secondary" size="sm">
                        <x-slot:icon><i class="bi bi-x-circle"></i></x-slot:icon>
                    </x-ui.button>
                @endif
            </div>
        </div>
    </form>
</x-ui.card>

{{-- Dispensing Queue Table --}}
<x-ui.card title="Dispensing Queue" icon="bi-bag-heart-fill" icon-color="#7c3aed" :no-padding="true">
    <x-slot:actions>
        <span style="font-size:11px;color:#aaa">{{ $prescriptions->total() }} {{ __('app.records') }}</span>
    </x-slot:actions>
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
                    $statusVariant = match($rx->dispensed_status) {
                        'dispensed' => 'success',
                        'partial'   => 'warning',
                        default     => 'secondary',
                    };
                    $statusLabel = match($rx->dispensed_status) {
                        'dispensed' => '✅ Dispensed',
                        'partial'   => '⏳ Partial',
                        default     => '⏸ Pending',
                    };
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
                        <x-ui.badge :variant="$statusVariant">{{ $statusLabel }}</x-ui.badge>
                        @if($rx->dispensed_by && $rx->dispensed_status === 'dispensed')
                            <div style="font-size:10px;color:#aaa;margin-top:2px">
                                by {{ $rx->dispensed_by }}
                            </div>
                        @endif
                    </td>
                    <td>
                        @if($rx->dispensed_status !== 'dispensed')
                            <x-ui.button href="{{ route('pharmacy.show', $rx->code) }}" variant="primary" size="sm">
                                <x-slot:icon><i class="bi bi-bag-fill"></i></x-slot:icon>
                                Dispense
                            </x-ui.button>
                        @else
                            <x-ui.button href="{{ route('pharmacy.show', $rx->code) }}" variant="secondary" size="sm">
                                <x-slot:icon><i class="bi bi-eye-fill"></i></x-slot:icon>
                                View
                            </x-ui.button>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">
                        <x-ui.empty-state icon="bi-capsule" title="No prescriptions in the {{ $status }} queue" description="Prescriptions appear here after being ordered during a visit" />
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</x-ui.card>

<x-ui.pagination :paginator="$prescriptions" />

@endsection
