{{--
    User / patient avatar — initials circle with optional image and status dot.

    <x-ui.avatar name="Sok Chan" size="md" />
    <x-ui.avatar name="John Doe" src="{{ $user->avatar_url }}" size="lg" status="online" />
    <x-ui.avatar name="Admin" color="#4154f1" size="sm" :square="true" />

    Props:
        name    — full name; first letter of first+last word used as initials
        src     — image URL (overrides initials)
        size    — xs | sm | md | lg | xl  (default: md)
        color   — accent hex color used for bg (default: auto from name hash)
        square  — rounded-lg instead of full circle
        status  — online | away | busy | offline | null  (shows dot)
        badge   — number to show as badge (notification-style, top-right)
        alt     — img alt text (defaults to name)
--}}
@props([
    'name'   => '?',
    'src'    => null,
    'size'   => 'md',
    'color'  => null,
    'square' => false,
    'status' => null,
    'badge'  => null,
    'alt'    => null,
])

@php
// Size map: [outer wh, text size, border width]
$sizeMap = [
    'xs' => ['w-6 h-6',   'text-[9px]',  1],
    'sm' => ['w-8 h-8',   'text-[11px]', 1],
    'md' => ['w-10 h-10', 'text-[13px]', 2],
    'lg' => ['w-12 h-12', 'text-[15px]', 2],
    'xl' => ['w-16 h-16', 'text-[20px]', 2],
];
[$wh, $textSize, $border] = $sizeMap[$size] ?? $sizeMap['md'];

// Status dot sizes
$dotSize = match($size) {
    'xs','sm' => 'w-2 h-2',
    'lg','xl' => 'w-3.5 h-3.5',
    default   => 'w-2.5 h-2.5',
};

// Status colors
$statusColor = match($status) {
    'online'  => '#2eca6a',
    'away'    => '#ff9f43',
    'busy'    => '#e74c3c',
    'offline' => '#94a3b8',
    default   => null,
};

// Initials (up to 2 chars)
$parts    = array_filter(explode(' ', trim($name)));
$initials = strtoupper(mb_substr($parts[0] ?? '?', 0, 1));
if (count($parts) > 1) {
    $initials .= strtoupper(mb_substr(end($parts), 0, 1));
}

// Auto-color from name if not provided
if (!$color) {
    $palette = ['#4154f1','#2eca6a','#ff771d','#e74c3c','#8b5cf6','#06b6d4','#f59e0b','#ec4899'];
    $color = $palette[abs(crc32($name)) % count($palette)];
}

$shape = $square ? 'rounded-lg' : 'rounded-full';
$alt   = $alt ?? $name;
@endphp

<span class="relative inline-flex flex-shrink-0 {{ $wh }}" {{ $attributes }}>

    {{-- Image or initials --}}
    @if($src)
        <img src="{{ $src }}" alt="{{ $alt }}"
             class="{{ $wh }} {{ $shape }} object-cover ring-{{ $border }} ring-white">
    @else
        <span class="inline-flex items-center justify-center {{ $wh }} {{ $shape }} {{ $textSize }} font-bold text-white select-none"
              style="background:{{ $color }};ring:{{ $border }}px solid white"
              title="{{ $name }}"
              aria-label="{{ $name }}">
            {{ $initials }}
        </span>
    @endif

    {{-- Status dot --}}
    @if($statusColor)
        <span class="absolute bottom-0 right-0 {{ $dotSize }} rounded-full ring-2 ring-white"
              style="background:{{ $statusColor }}"
              aria-label="{{ $status }}"></span>
    @endif

    {{-- Notification badge --}}
    @if($badge !== null)
        <span class="absolute -top-1 -right-1 min-w-[16px] h-4 px-0.5 text-[9px] font-bold
                     bg-[#e74c3c] text-white rounded-full flex items-center justify-center leading-none">
            {{ $badge > 99 ? '99+' : $badge }}
        </span>
    @endif

</span>
