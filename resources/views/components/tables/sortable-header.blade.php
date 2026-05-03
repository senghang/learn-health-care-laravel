{{--
    Sortable table column header — renders <th> with sort arrow link.

    <x-tables.sortable-header col="name" :sort="$sort" :dir="$dir">Name</x-tables.sortable-header>
    <x-tables.sortable-header col="created_at" :sort="$sort" :dir="$dir">Date</x-tables.sortable-header>

    Pass all current query params except sort/dir — the component merges them.

    Props:
        col     — column key for sort= param
        sort    — current sort column (from request)
        dir     — current direction asc|desc (from request)
        params  — array of extra query params to preserve (default: all request params minus sort/dir)
        align   — left | center | right  (default: left)
--}}
@props([
    'col'    => '',
    'sort'   => null,
    'dir'    => 'asc',
    'params' => null,
    'align'  => 'left',
])

@php
$active   = $sort === $col;
$newDir   = ($active && $dir === 'asc') ? 'desc' : 'asc';
$carry    = $params ?? array_filter(request()->except(['sort','dir','page']));
$url      = request()->url() . '?' . http_build_query(array_merge($carry, ['sort' => $col, 'dir' => $newDir]));
$textAlign = match($align) { 'center' => 'text-center', 'right' => 'text-end', default => '' };
@endphp

<th scope="col"
    class="{{ $textAlign }} {{ $attributes->get('class') }}"
    style="white-space:nowrap;font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;
           letter-spacing:.04em;padding:10px 16px {{ $active ? ';color:#4154f1' : '' }}">
    <a href="{{ $url }}"
       class="inline-flex items-center gap-1 no-underline transition-colors hover:text-[#4154f1]"
       style="color:inherit">
        {{ $slot }}
        @if($active)
            <i class="bi {{ $dir === 'asc' ? 'bi-arrow-up' : 'bi-arrow-down' }}"
               style="font-size:10px" aria-hidden="true"></i>
        @else
            <i class="bi bi-arrow-down-up" style="font-size:10px;opacity:.4" aria-hidden="true"></i>
        @endif
    </a>
</th>
