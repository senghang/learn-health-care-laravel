{{--
    <x-ui.table :loading="$loading" empty-title="No patients found" empty-icon="bi-person-circle">
        <x-slot:head>
            <x-ui.table-th>Code</x-ui.table-th>
            <x-ui.table-th sortable sort-key="name" :sort-dir="request('sort') === 'name' ? request('dir') : null">
                Patient
            </x-ui.table-th>
            <x-ui.table-th class="hidden sm:table-cell">Status</x-ui.table-th>
            <x-ui.table-th align="right">Actions</x-ui.table-th>
        </x-slot:head>
        <x-slot:body>
            @foreach($patients as $p)
            <tr class="hover:bg-[#f8faff] transition-colors cursor-pointer border-b border-[#f8faff]"
                onclick="location.href='{{ route('patients.show', $p->code) }}'">
                <x-ui.table-td>{{ $p->code }}</x-ui.table-td>
                <x-ui.table-td>{{ $p->name }}</x-ui.table-td>
                <x-ui.table-td class="hidden sm:table-cell"><x-ui.badge>Active</x-ui.badge></x-ui.table-td>
                <x-ui.table-td align="right" @click.stop>…actions…</x-ui.table-td>
            </tr>
            @endforeach
        </x-slot:body>
    </x-ui.table>

    Props:
        loading     — bool show skeleton loader
        emptyTitle  — empty state title
        emptyIcon   — Bootstrap icon class for empty state
        emptyDesc   — empty state description
        sticky      — bool sticky header (default: true)
        striped     — bool alternate row shading
--}}
@props([
    'loading'    => false,
    'emptyTitle' => 'No records found',
    'emptyIcon'  => 'bi-inbox',
    'emptyDesc'  => null,
    'sticky'     => true,
    'striped'    => false,
])

<div class="overflow-x-auto rounded-b-2xl {{ $attributes->get('class') }}">
    <table class="w-full border-collapse text-sm" role="grid">

        {{-- Head --}}
        @isset($head)
        <thead>
            <tr class="{{ $sticky ? 'sticky top-0 z-10' : '' }}"
                style="background:#f8f9fb;border-bottom:1px solid #e6e9f0">
                {{ $head }}
            </tr>
        </thead>
        @endisset

        {{-- Body --}}
        <tbody>
            @if($loading)
                @for($i = 0; $i < 5; $i++)
                <tr style="border-bottom:1px solid #f0f2f5">
                    @for($j = 0; $j < 5; $j++)
                    <td class="px-4 py-3.5">
                        <div class="h-3 rounded-full animate-pulse" style="background:#e2e8f0;width:{{ rand(40,90) }}%"></div>
                    </td>
                    @endfor
                </tr>
                @endfor
            @elseif(isset($body) && $body->isNotEmpty())
                {{ $body }}
            @else
                <tr>
                    <td colspan="99" class="px-4 py-0">
                        <x-ui.empty-state
                            :icon="$emptyIcon"
                            :title="$emptyTitle"
                            :description="$emptyDesc"
                        >
                            @isset($emptyAction){{ $emptyAction }}@endisset
                        </x-ui.empty-state>
                    </td>
                </tr>
            @endif
        </tbody>
    </table>
</div>
