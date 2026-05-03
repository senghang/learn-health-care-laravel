{{--
    Skeleton loader — animated placeholder blocks for loading states.

    <x-ui.skeleton class="h-4 w-48 mb-2" />          <!-- single line -->
    <x-ui.skeleton type="card" />                      <!-- full card skeleton -->
    <x-ui.skeleton type="table" :rows="5" />           <!-- table skeleton -->
    <x-ui.skeleton type="stats" :count="4" />          <!-- stat cards row -->
    <x-ui.skeleton type="avatar" size="md" />          <!-- avatar circle -->

    Props:
        type    — line | card | table | stats | avatar | text  (default: line)
        rows    — number of rows for type=table (default: 5)
        count   — number of cards for type=stats (default: 4)
        size    — avatar size xs|sm|md|lg|xl  (default: md)
        rounded — rounded-full instead of rounded-lg (for avatar/line)
--}}
@props([
    'type'    => 'line',
    'rows'    => 5,
    'count'   => 4,
    'size'    => 'md',
    'rounded' => false,
])

@php
$pulse = 'animate-pulse bg-[#e8ecff]';
$base  = $rounded ? 'rounded-full' : 'rounded-lg';
$avatarMap = ['xs'=>'w-6 h-6','sm'=>'w-8 h-8','md'=>'w-10 h-10','lg'=>'w-12 h-12','xl'=>'w-16 h-16'];
$avatarWh  = $avatarMap[$size] ?? $avatarMap['md'];
@endphp

@if($type === 'line')
    {{-- Single skeleton line --}}
    <div class="{{ $pulse }} {{ $base }} {{ $attributes->get('class','h-4 w-full') }}"
         aria-hidden="true" role="presentation"></div>

@elseif($type === 'avatar')
    <div class="{{ $pulse }} rounded-full {{ $avatarWh }}" aria-hidden="true" role="presentation"></div>

@elseif($type === 'text')
    {{-- 3 lines of text --}}
    <div class="space-y-2" aria-hidden="true" role="presentation" {{ $attributes }}>
        <div class="{{ $pulse }} rounded h-3 w-full"></div>
        <div class="{{ $pulse }} rounded h-3 w-5/6"></div>
        <div class="{{ $pulse }} rounded h-3 w-4/6"></div>
    </div>

@elseif($type === 'stats')
    <div class="grid grid-cols-2 lg:grid-cols-{{ min($count,4) }} gap-4 mb-5"
         aria-hidden="true" role="presentation" {{ $attributes }}>
        @for($i = 0; $i < $count; $i++)
        <div class="bg-white rounded-2xl p-5 border border-[#f0f2ff]">
            <div class="flex items-start justify-between mb-4">
                <div class="{{ $pulse }} rounded-xl w-11 h-11"></div>
                <div class="{{ $pulse }} rounded h-5 w-14"></div>
            </div>
            <div class="{{ $pulse }} rounded h-9 w-16 mb-1"></div>
            <div class="{{ $pulse }} rounded h-3 w-28"></div>
        </div>
        @endfor
    </div>

@elseif($type === 'card')
    <div class="bg-white rounded-2xl border border-[#f0f2ff] overflow-hidden"
         aria-hidden="true" role="presentation" {{ $attributes }}>
        {{-- Card header --}}
        <div class="px-5 py-4 border-b border-[#f0f2ff] flex items-center gap-2">
            <div class="{{ $pulse }} rounded w-4 h-4"></div>
            <div class="{{ $pulse }} rounded h-4 w-32"></div>
        </div>
        {{-- Card body --}}
        <div class="p-5 space-y-3">
            <div class="{{ $pulse }} rounded h-3 w-full"></div>
            <div class="{{ $pulse }} rounded h-3 w-5/6"></div>
            <div class="{{ $pulse }} rounded h-3 w-4/6"></div>
            <div class="{{ $pulse }} rounded h-3 w-full"></div>
            <div class="{{ $pulse }} rounded h-3 w-3/4"></div>
        </div>
    </div>

@elseif($type === 'table')
    <div class="bg-white rounded-2xl border border-[#f0f2ff] overflow-hidden"
         aria-hidden="true" role="presentation" {{ $attributes }}>
        {{-- Table header --}}
        <div class="px-5 py-4 border-b border-[#f0f2ff] flex items-center gap-2">
            <div class="{{ $pulse }} rounded w-4 h-4"></div>
            <div class="{{ $pulse }} rounded h-4 w-40"></div>
        </div>
        {{-- Rows --}}
        @for($i = 0; $i < $rows; $i++)
        <div class="px-5 py-3.5 flex items-center gap-4 {{ $i < $rows-1 ? 'border-b border-[#f8f9ff]' : '' }}">
            <div class="{{ $pulse }} rounded-full w-9 h-9 flex-shrink-0"></div>
            <div class="flex-1 space-y-1.5">
                <div class="{{ $pulse }} rounded h-3 w-1/3"></div>
                <div class="{{ $pulse }} rounded h-2.5 w-1/4"></div>
            </div>
            <div class="{{ $pulse }} rounded h-5 w-16"></div>
            <div class="{{ $pulse }} rounded h-5 w-20"></div>
            <div class="{{ $pulse }} rounded w-7 h-7"></div>
        </div>
        @endfor
    </div>

@endif
