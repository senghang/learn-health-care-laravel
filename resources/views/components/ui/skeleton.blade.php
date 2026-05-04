{{--
    Skeleton loader — animated placeholders for loading states.

    <x-ui.skeleton class="h-4 w-48 mb-2" />          — single line
    <x-ui.skeleton type="card" />                      — full card placeholder
    <x-ui.skeleton type="table" :rows="5" />           — table rows
    <x-ui.skeleton type="stats" :count="4" />          — stat cards row
    <x-ui.skeleton type="avatar" size="md" />          — avatar circle
    <x-ui.skeleton type="text" />                      — 3 text lines

    Props:
        type    — line | card | table | stats | avatar | text  (default: line)
        rows    — row count for type=table (default: 5)
        count   — card count for type=stats (default: 4)
        size    — avatar size xs|sm|md|lg|xl  (default: md)
        rounded — bool rounded-full instead of rounded-md
--}}
@props([
    'type'    => 'line',
    'rows'    => 5,
    'count'   => 4,
    'size'    => 'md',
    'rounded' => false,
])

@php
/* Use the CSS .skeleton class from design-tokens.css which has the shimmer animation */
$sk   = 'skeleton';
$base = $rounded ? 'rounded-full' : 'rounded-md';
$avatarMap = ['xs'=>'w-6 h-6','sm'=>'w-8 h-8','md'=>'w-10 h-10','lg'=>'w-12 h-12','xl'=>'w-16 h-16'];
$avatarWh  = $avatarMap[$size] ?? $avatarMap['md'];
@endphp

@if($type === 'line')
    <div class="{{ $sk }} {{ $base }} {{ $attributes->get('class','h-4 w-full') }}"
         aria-hidden="true" role="presentation"></div>

@elseif($type === 'avatar')
    <div class="{{ $sk }} rounded-full {{ $avatarWh }}" aria-hidden="true" role="presentation"></div>

@elseif($type === 'text')
    <div class="space-y-2" aria-hidden="true" role="presentation" {{ $attributes }}>
        <div class="{{ $sk }} rounded h-3 w-full"></div>
        <div class="{{ $sk }} rounded h-3 w-5/6"></div>
        <div class="{{ $sk }} rounded h-3 w-4/6"></div>
    </div>

@elseif($type === 'stats')
    <div class="grid grid-cols-2 lg:grid-cols-{{ min($count,4) }} gap-4 mb-5"
         aria-hidden="true" role="presentation" {{ $attributes }}>
        @for($i = 0; $i < $count; $i++)
        <div class="bg-white rounded-xl p-5 border" style="border-color:var(--border-subtle,#E2E8F0)">
            <div class="flex items-start justify-between mb-4">
                <div class="{{ $sk }} rounded-xl w-11 h-11"></div>
                <div class="{{ $sk }} rounded h-5 w-14"></div>
            </div>
            <div class="{{ $sk }} rounded h-9 w-16 mb-1"></div>
            <div class="{{ $sk }} rounded h-3 w-28"></div>
        </div>
        @endfor
    </div>

@elseif($type === 'card')
    <div class="bg-white rounded-xl border overflow-hidden"
         style="border-color:var(--border-subtle,#E2E8F0)"
         aria-hidden="true" role="presentation" {{ $attributes }}>
        <div class="px-5 py-4 flex items-center gap-3" style="border-bottom:1px solid var(--border-subtle,#E2E8F0)">
            <div class="{{ $sk }} rounded-md w-7 h-7"></div>
            <div class="{{ $sk }} rounded h-4 w-32"></div>
        </div>
        <div class="p-5 space-y-3">
            <div class="{{ $sk }} rounded h-3 w-full"></div>
            <div class="{{ $sk }} rounded h-3 w-5/6"></div>
            <div class="{{ $sk }} rounded h-3 w-4/6"></div>
            <div class="{{ $sk }} rounded h-3 w-full"></div>
            <div class="{{ $sk }} rounded h-3 w-3/4"></div>
        </div>
    </div>

@elseif($type === 'table')
    <div class="bg-white rounded-xl border overflow-hidden"
         style="border-color:var(--border-subtle,#E2E8F0)"
         aria-hidden="true" role="presentation" {{ $attributes }}>
        <div class="px-5 py-4 flex items-center gap-3" style="border-bottom:1px solid var(--border-subtle,#E2E8F0)">
            <div class="{{ $sk }} rounded-md w-7 h-7"></div>
            <div class="{{ $sk }} rounded h-4 w-40"></div>
        </div>
        @for($i = 0; $i < $rows; $i++)
        <div class="px-5 py-3.5 flex items-center gap-4 {{ $i < $rows-1 ? 'border-b' : '' }}"
             style="{{ $i < $rows-1 ? 'border-color:var(--border-subtle,#E2E8F0)' : '' }}">
            <div class="{{ $sk }} rounded-full w-9 h-9 flex-shrink-0"></div>
            <div class="flex-1 space-y-1.5">
                <div class="{{ $sk }} rounded h-3 w-1/3"></div>
                <div class="{{ $sk }} rounded h-2.5 w-1/4"></div>
            </div>
            <div class="{{ $sk }} rounded h-5 w-16"></div>
            <div class="{{ $sk }} rounded h-5 w-20"></div>
        </div>
        @endfor
    </div>

@endif
