{{--
    Filter toolbar — wraps a form with consistent card styling.
    All filter inputs go in the default slot; active filter count shown as badge.

    <x-tables.filter-toolbar action="{{ route('patients.index') }}">
        <x-forms.search-input name="search" :value="request('search')" />
        <select name="status" class="form-select" style="font-size:13px;max-width:160px">
            <option value="">All Status</option>
            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
        </select>
        <x-ui.button type="submit" variant="primary" size="sm">Filter</x-ui.button>
    </x-tables.filter-toolbar>

    Props:
        action  — form action URL (default: current URL)
        method  — GET | POST (default: GET)
        count   — number of active filters (shows badge)
        label   — toolbar label (default: hidden)
--}}
@props([
    'action' => null,
    'method' => 'GET',
    'count'  => null,
    'label'  => null,
])

<div class="bg-white rounded-2xl border border-[#f0f2ff] px-4 py-3 mb-4"
     style="box-shadow:0 1px 3px rgba(1,41,112,.05)" {{ $attributes }}>

    @if($label)
    <div class="text-xs font-semibold text-[#94a3b8] mb-2 flex items-center gap-1.5">
        <i class="bi bi-funnel-fill" aria-hidden="true"></i>
        {{ $label }}
        @if($count)
            <span class="inline-flex items-center justify-center w-4 h-4 text-[9px] font-bold
                         bg-[#4154f1] text-white rounded-full">{{ $count }}</span>
        @endif
    </div>
    @endif

    <form method="{{ strtoupper($method) === 'GET' ? 'GET' : 'POST' }}"
          action="{{ $action ?? request()->url() }}"
          class="flex flex-wrap items-center gap-2">
        @if(strtoupper($method) !== 'GET') @csrf @endif

        {{ $slot }}

        {{-- Reset link if filters active --}}
        @if($count)
            <a href="{{ $action ?? request()->url() }}"
               class="text-xs text-[#94a3b8] hover:text-[#4154f1] transition-colors whitespace-nowrap ml-1">
                <i class="bi bi-x-circle me-0.5"></i>Clear
            </a>
        @endif
    </form>
</div>
