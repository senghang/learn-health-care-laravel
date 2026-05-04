{{--
    Data table wrapper — card shell with optional header, filter slot, footer/pagination.
    The actual <table> rows go in the default slot.

    <x-ui.data-table
        title="Patients"
        km="អ្នកជំងឺ"
        icon="bi-people-fill"
        :count="$patients->total()">

        <x-slot:filters>
            <x-tables.filter-toolbar action="{{ route('patients.index') }}">
                <x-forms.search-input name="search" :value="request('search')" />
            </x-tables.filter-toolbar>
        </x-slot:filters>

        <x-slot:actions>
            <x-ui.button href="{{ route('patients.create') }}" variant="primary" size="sm">
                <x-slot:icon><i class="bi bi-plus-lg"></i></x-slot:icon>
                New Patient
            </x-ui.button>
        </x-slot:actions>

        <x-slot:thead>
            <tr>
                <x-ui.table-th>Name</x-ui.table-th>
                <x-ui.table-th>Status</x-ui.table-th>
            </tr>
        </x-slot:thead>

        {{-- tbody rows --}}
        @foreach($patients as $p)
        <tr class="hover:bg-[#fafbff] transition-colors">
            <x-ui.table-td>{{ $p->name }}</x-ui.table-td>
        </tr>
        @endforeach

        <x-slot:footer>
            <x-ui.pagination :paginator="$patients" />
        </x-slot:footer>
    </x-ui.data-table>

    Props:
        title   — English title for card header
        km      — Khmer title
        icon    — Bootstrap icon class
        count   — row count badge
        loading — bool: show skeleton overlay
--}}
@props([
    'title'   => null,
    'km'      => null,
    'icon'    => null,
    'count'   => null,
    'loading' => false,
])

<div class="relative" {{ $attributes }}>
    {{-- Filter slot (outside card) --}}
    @isset($filters)
        {{ $filters }}
    @endisset

    <x-ui.card :noPadding="true">
        {{-- Card header --}}
        @if($title || $km || isset($actions))
        <x-slot:header>
            <x-ui.card-header
                :title="$title"
                :km="$km"
                :icon="$icon"
                :count="$count">
                @isset($actions)
                <x-slot:actions>{{ $actions }}</x-slot:actions>
                @endisset
            </x-ui.card-header>
        </x-slot:header>
        @endif

        {{-- Table --}}
        <x-ui.table>
            @isset($thead)
            <x-slot:thead>{{ $thead }}</x-slot:thead>
            @endisset

            {{ $slot }}
        </x-ui.table>

        {{-- Footer / pagination --}}
        @isset($footer)
        <div class="px-5 py-3" style="border-top:1px solid var(--border-subtle,#E2E8F0)">
            {{ $footer }}
        </div>
        @endisset
    </x-ui.card>

    {{-- Loading overlay --}}
    @if($loading)
    <div class="absolute inset-0 bg-white/70 rounded-2xl flex items-center justify-center z-10"
         aria-busy="true" aria-label="Loading…">
        <div class="w-8 h-8 rounded-full border-2 border-[#4154f1]/20 border-t-[#4154f1] animate-spin"></div>
    </div>
    @endif
</div>
