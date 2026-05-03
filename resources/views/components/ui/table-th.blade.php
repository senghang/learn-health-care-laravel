{{--
    <x-ui.table-th>Name</x-ui.table-th>
    <x-ui.table-th sortable sort-key="name" :sort-dir="request('sort')==='name' ? request('dir') : null">
        Patient Name
    </x-ui.table-th>

    Props:
        sortable — bool
        sortKey  — query param key
        sortDir  — asc|desc|null
        align    — left|center|right (default: left)
--}}
@props([
    'sortable' => false,
    'sortKey'  => null,
    'sortDir'  => null,
    'align'    => 'left',
])

@php
$alignCls = ['left'=>'text-left','center'=>'text-center','right'=>'text-right'][$align] ?? 'text-left';
$nextDir = $sortDir === 'asc' ? 'desc' : 'asc';
@endphp

<th {{ $attributes->merge(['class' => "px-4 py-3 text-xs font-semibold uppercase tracking-wider text-[#6b7280] $alignCls whitespace-nowrap"]) }}>
    @if($sortable && $sortKey)
    <a href="{{ request()->fullUrlWithQuery(['sort' => $sortKey, 'dir' => $nextDir]) }}"
       class="inline-flex items-center gap-1 hover:text-[#4154f1] transition-colors">
        {{ $slot }}
        <span class="flex-shrink-0">
            @if($sortDir === 'asc')
                <i class="bi bi-sort-up text-[#4154f1]" aria-label="sorted ascending"></i>
            @elseif($sortDir === 'desc')
                <i class="bi bi-sort-down text-[#4154f1]" aria-label="sorted descending"></i>
            @else
                <i class="bi bi-arrow-down-up opacity-30" aria-hidden="true"></i>
            @endif
        </span>
    </a>
    @else
        {{ $slot }}
    @endif
</th>
