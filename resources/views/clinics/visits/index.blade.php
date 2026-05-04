@extends('clinics.layout.app')
@section('title', 'ការចូលព្យាបាល / Patient Visits')

@section('content')

<x-ui.page-header km="ការចូលព្យាបាល" title="Patient Visits"
    :breadcrumbs="[['label' => 'ដើម', 'url' => url('/')], ['label' => 'Visits']]">
    <x-slot:actions>
        <x-ui.button href="{{ url('/workflow/create') }}" variant="primary">
            <x-slot:icon><i class="bi bi-plus-lg" aria-hidden="true"></i></x-slot:icon>
            <span class="hidden sm:inline">New Visit</span>
            <span class="sm:hidden">New</span>
        </x-ui.button>
    </x-slot:actions>
</x-ui.page-header>

{{-- ── FILTER ─────────────────────────────────────────────────── --}}
<x-ui.card class="mb-4">
    <form method="GET" action="{{ url('/visits') }}" role="search" aria-label="Filter visits">
        <div class="flex flex-col sm:flex-row gap-3 flex-wrap">

            {{-- Search --}}
            <div class="flex-1 relative min-w-0">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none" aria-hidden="true">
                    <i class="bi bi-search text-sm" style="color:#6b7280"></i>
                </div>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="ឈ្មោះ ឬ លេខ / Name, code or patient…"
                    class="w-full text-sm rounded-lg border border-[#e2e8f0] bg-white pl-9 pr-4 py-2.5 text-[#374151] placeholder-[#9ca3af] focus:outline-none focus:border-[#4154f1] focus:ring-2 focus:ring-[#4154f1]/20 transition-colors"
                    aria-label="Search visits" />
            </div>

            {{-- Type --}}
            <div class="relative sm:w-36">
                <select name="type" aria-label="Filter by visit type"
                    class="w-full text-sm rounded-lg border border-[#e2e8f0] bg-white px-4 py-2.5 text-[#374151] appearance-none focus:outline-none focus:border-[#4154f1] focus:ring-2 focus:ring-[#4154f1]/20 transition-colors"
                    style="padding-right:2.5rem">
                    <option value="">All Types</option>
                    <option value="OPD" @selected(request('type') === 'OPD')>OPD</option>
                    <option value="IPD" @selected(request('type') === 'IPD')>IPD</option>
                </select>
                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none" aria-hidden="true">
                    <i class="bi bi-chevron-down text-xs" style="color:#6b7280"></i>
                </div>
            </div>

            {{-- Status --}}
            <div class="relative sm:w-36">
                <select name="status" aria-label="Filter by status"
                    class="w-full text-sm rounded-lg border border-[#e2e8f0] bg-white px-4 py-2.5 text-[#374151] appearance-none focus:outline-none focus:border-[#4154f1] focus:ring-2 focus:ring-[#4154f1]/20 transition-colors"
                    style="padding-right:2.5rem">
                    <option value="">All Status</option>
                    <option value="active" @selected(request('status') === 'active')>Active</option>
                    <option value="done"   @selected(request('status') === 'done')>Done</option>
                </select>
                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none" aria-hidden="true">
                    <i class="bi bi-chevron-down text-xs" style="color:#6b7280"></i>
                </div>
            </div>

            {{-- Date --}}
            <div class="sm:w-44">
                <input type="date" name="date" value="{{ request('date') }}"
                    class="w-full text-sm rounded-lg border border-[#e2e8f0] bg-white px-4 py-2.5 text-[#374151] focus:outline-none focus:border-[#4154f1] focus:ring-2 focus:ring-[#4154f1]/20 transition-colors"
                    aria-label="Filter by date" />
            </div>

            {{-- Buttons --}}
            <div class="flex gap-2">
                <x-ui.button type="submit" variant="primary">
                    <x-slot:icon><i class="bi bi-funnel-fill" aria-hidden="true"></i></x-slot:icon>
                    Filter
                </x-ui.button>
                @if(request()->hasAny(['search', 'type', 'status', 'date']))
                    <x-ui.button href="{{ url('/visits') }}" variant="secondary">
                        <x-slot:icon><i class="bi bi-x-circle" aria-hidden="true"></i></x-slot:icon>
                        Clear
                    </x-ui.button>
                @endif
            </div>
        </div>
    </form>
</x-ui.card>

{{-- ── RESULTS SUMMARY ─────────────────────────────────────────── --}}
@if($visits->total() > 0)
    <div class="flex items-center justify-between mb-3">
        <span class="text-xs" style="color:#6b7280">
            Showing {{ $visits->firstItem() }}–{{ $visits->lastItem() }} of {{ number_format($visits->total()) }} visits
        </span>
        @if(request()->hasAny(['search', 'type', 'status', 'date']))
            <a href="{{ url('/visits') }}" class="text-xs font-semibold hover:underline" style="color:#e74c3c">
                <i class="bi bi-x-circle" aria-hidden="true"></i> Clear filters
            </a>
        @endif
    </div>
@endif

{{-- ── VISIT LIST ───────────────────────────────────────────────── --}}
@php
    $avatarColors = ['#4154f1','#2eca6a','#ff771d','#e74c3c','#9b59b6','#00bcd4'];
@endphp

@forelse($visits as $visit)
    @php
        $initials = strtoupper(substr($visit->surname ?? '', 0, 1) . substr($visit->name ?? '', 0, 1));
        $color    = $avatarColors[abs(crc32($visit->patient_code ?? '')) % count($avatarColors)];
        $isActive = is_null($visit->discharged_at);
        $done     = $visit->steps_done ?? 0;
        $pct      = round($done / 10 * 100);
    @endphp
    <div class="group flex items-center gap-4 px-5 py-4 bg-white rounded-2xl mb-2 border border-[#e6e9f0] cursor-pointer
        hover:-translate-y-0.5 hover:shadow-md hover:border-[#e0e4ff] transition-all duration-150"
        style="box-shadow:0 1px 3px rgba(17,24,39,.03)"
        onclick="window.location='{{ url('/workflow/'.$visit->code) }}'"
        role="listitem">

        {{-- Avatar --}}
        <div class="w-11 h-11 rounded-xl flex items-center justify-center text-sm font-black text-white flex-shrink-0"
            style="background:linear-gradient(135deg,{{ $color }},{{ $color }}cc)" aria-hidden="true">
            {{ $initials ?: '?' }}
        </div>

        {{-- Info --}}
        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 flex-wrap">
                <span class="text-sm font-bold" style="color:#1a1f36">
                    {{ $visit->surname }}, {{ $visit->name }}
                </span>
                <x-ui.badge :variant="$visit->visit_type === 'IPD' ? 'warning' : 'primary'" size="sm">
                    {{ $visit->visit_type }}
                </x-ui.badge>
                @if($isActive)
                    <x-ui.badge variant="success" size="sm" dot>Active</x-ui.badge>
                @else
                    <x-ui.badge variant="secondary" size="sm">Done</x-ui.badge>
                @endif
            </div>
            <div class="flex items-center gap-2 mt-0.5 flex-wrap">
                <span class="text-xs font-mono font-bold" style="color:#4154f1">{{ $visit->patient_code }}</span>
                <span class="text-xs" style="color:#6b7280">· {{ $visit->code }}</span>
                @if($visit->admitted_at)
                    <span class="text-xs" style="color:#6b7280">· {{ $visit->admitted_at->format('d/m H:i') }}</span>
                @endif
            </div>
            @if($done > 0)
                <div class="flex items-center gap-2 mt-1.5">
                    <div class="h-1.5 rounded-full overflow-hidden" style="width:100px;background:#f1f5f9">
                        <div class="h-full rounded-full transition-all duration-500"
                             style="width:{{ $pct }}%;background:{{ $pct >= 100 ? '#2eca6a' : 'linear-gradient(90deg,#4154f1,#818cf8)' }}"></div>
                    </div>
                    <span class="text-xs font-semibold {{ $pct >= 100 ? 'text-[#2eca6a]' : 'text-[#64748b]' }}">
                        {{ $done }}/10 steps
                    </span>
                </div>
            @endif
        </div>

        {{-- Actions --}}
        <div class="flex items-center gap-2 flex-shrink-0" onclick="event.stopPropagation()">
            <x-ui.button href="{{ url('/workflow/'.$visit->code) }}" variant="primary" size="sm"
                class="hidden sm:inline-flex">
                <x-slot:icon><i class="bi bi-arrow-right-circle-fill" aria-hidden="true"></i></x-slot:icon>
                Continue
            </x-ui.button>
        </div>

        <i class="bi bi-chevron-right text-[#e2e8f0] group-hover:text-[#4154f1] transition-colors flex-shrink-0"
           aria-hidden="true"></i>
    </div>

@empty
    <x-ui.empty-state
        icon="bi-hospital"
        :title="request()->hasAny(['search','type','status','date']) ? 'No visits match your filters' : 'No visits yet'"
        :description="request()->hasAny(['search','type','status','date'])
            ? 'Try adjusting your search or filters.'
            : 'Register a new OPD visit to get started.'">
        @if(request()->hasAny(['search', 'type', 'status', 'date']))
            <x-ui.button href="{{ url('/visits') }}" variant="secondary" size="sm">
                <x-slot:icon><i class="bi bi-x-circle" aria-hidden="true"></i></x-slot:icon>
                Clear Filters
            </x-ui.button>
        @else
            <x-ui.button href="{{ url('/workflow/create') }}" variant="primary" size="sm">
                <x-slot:icon><i class="bi bi-plus-lg" aria-hidden="true"></i></x-slot:icon>
                New Visit
            </x-ui.button>
        @endif
    </x-ui.empty-state>
@endforelse

<x-ui.pagination :paginator="$visits" />

@endsection
