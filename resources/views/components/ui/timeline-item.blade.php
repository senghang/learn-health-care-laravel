{{--
    Single item inside <x-ui.timeline>.

    Props:
        date    — date string (ISO or pre-formatted)
        title   — English event title
        km      — Khmer label (dominant)
        icon    — Bootstrap icon class (default: bi-circle-fill)
        color   — accent color hex (default: brand)
        by      — actor name (doctor/nurse/system)
        status  — completed | pending | cancelled | null
--}}
@props([
    'date'   => null,
    'title'  => '',
    'km'     => null,
    'icon'   => 'bi-circle-fill',
    'color'  => '#4154f1',
    'by'     => null,
    'status' => null,
])

@php
$displayDate = null;
if ($date) {
    try {
        $displayDate = \Carbon\Carbon::parse($date)
            ->timezone('Asia/Phnom_Penh')
            ->format('d M Y, H:i');
    } catch (\Exception $e) {
        $displayDate = $date;
    }
}

$statusCfg = [
    'completed' => ['label' => 'Completed', 'color' => '#065F46', 'bg' => '#D1FAE5'],
    'pending'   => ['label' => 'Pending',   'color' => '#92400E', 'bg' => '#FEF3C7'],
    'cancelled' => ['label' => 'Cancelled', 'color' => '#991B1B', 'bg' => '#FEE2E2'],
];
$sc = $statusCfg[$status] ?? null;
@endphp

<li class="relative" {{ $attributes }}>

    {{-- Timeline dot --}}
    <span class="absolute flex items-center justify-center w-7 h-7 rounded-full text-white text-xs flex-shrink-0"
          style="left:-2.25rem;top:0;background:{{ $color }};box-shadow:0 0 0 3px white,0 0 0 4px {{ $color }}40"
          aria-hidden="true">
        <i class="bi {{ $icon }}" style="font-size:11px"></i>
    </span>

    {{-- Card --}}
    <div class="bg-white rounded-xl border px-4 py-3"
         style="border-color:var(--border-subtle,#E2E8F0);box-shadow:var(--shadow-sm)">

        {{-- Header row --}}
        <div class="flex items-start justify-between gap-2 flex-wrap">
            <div class="min-w-0">
                @if($km)
                    <span class="text-sm font-bold"
                          style="color:var(--text-primary,#0F172A);font-family:var(--font-khmer,'Noto Sans Khmer',sans-serif)">
                        {{ $km }}
                    </span>
                    @if($title)
                    <span class="text-xs ml-1" style="color:var(--text-muted,#94A3B8)">/ {{ $title }}</span>
                    @endif
                @else
                    <span class="text-sm font-bold" style="color:var(--text-primary,#0F172A)">{{ $title }}</span>
                @endif
            </div>
            <div class="flex items-center gap-2 flex-shrink-0">
                @if($sc)
                    <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded-full"
                          style="color:{{ $sc['color'] }};background:{{ $sc['bg'] }}">
                        {{ $sc['label'] }}
                    </span>
                @endif
                @if($displayDate)
                    <time class="text-[11px] whitespace-nowrap" style="color:var(--text-muted,#94A3B8)">
                        {{ $displayDate }}
                    </time>
                @endif
            </div>
        </div>

        {{-- By --}}
        @if($by)
        <div class="mt-0.5 text-[11px]" style="color:var(--text-muted,#94A3B8)">
            <i class="bi bi-person-fill mr-0.5" aria-hidden="true"></i>{{ $by }}
        </div>
        @endif

        {{-- Body --}}
        @if($slot->isNotEmpty())
        <div class="mt-2 text-xs leading-relaxed" style="color:var(--text-secondary,#475569)">
            {{ $slot }}
        </div>
        @endif

    </div>
</li>
