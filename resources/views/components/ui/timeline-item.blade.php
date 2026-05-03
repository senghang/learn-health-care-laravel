{{--
    Single item inside <x-ui.timeline>. See timeline.blade.php for full usage.

    Props:
        date    — date string (displayed as-is if already formatted, parsed if ISO)
        title   — English event title
        km      — Khmer label
        icon    — Bootstrap icon class (default: bi-circle-fill)
        color   — accent color hex (default: #4154f1)
        by      — actor (doctor/nurse/system)
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
// Format date
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
    'completed' => ['cls' => 'text-[#16a34a]', 'bg' => '#e8f8ef', 'label' => 'Completed'],
    'pending'   => ['cls' => 'text-[#f59e0b]', 'bg' => '#fef3c7', 'label' => 'Pending'],
    'cancelled' => ['cls' => 'text-[#dc2626]', 'bg' => '#fef2f2', 'label' => 'Cancelled'],
];
$sc = $statusCfg[$status] ?? null;
@endphp

<li class="relative" {{ $attributes }}>
    {{-- Dot / icon --}}
    <span class="absolute flex items-center justify-center w-7 h-7 rounded-full text-white text-xs"
          style="left:-2.25rem;top:0;background:{{ $color }};box-shadow:0 0 0 3px #fff">
        <i class="bi {{ $icon }}" aria-hidden="true"></i>
    </span>

    <div class="bg-white rounded-xl border border-[#f0f2ff] px-4 py-3"
         style="box-shadow:0 1px 3px rgba(1,41,112,.05)">
        {{-- Header row --}}
        <div class="flex items-start justify-between gap-2 flex-wrap">
            <div class="min-w-0">
                @if($km)
                    <span class="text-sm font-bold text-[#012970]">{{ $km }}</span>
                    <span class="text-xs text-[#94a3b8] ml-1">/ {{ $title }}</span>
                @else
                    <span class="text-sm font-bold text-[#012970]">{{ $title }}</span>
                @endif
            </div>
            <div class="flex items-center gap-2 flex-shrink-0">
                @if($sc)
                    <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded-full {{ $sc['cls'] }}"
                          style="background:{{ $sc['bg'] }}">
                        {{ $sc['label'] }}
                    </span>
                @endif
                @if($displayDate)
                    <time class="text-[11px] text-[#94a3b8] whitespace-nowrap">{{ $displayDate }}</time>
                @endif
            </div>
        </div>

        {{-- By --}}
        @if($by)
            <div class="text-[11px] text-[#94a3b8] mt-0.5">
                <i class="bi bi-person-fill mr-0.5" aria-hidden="true"></i>{{ $by }}
            </div>
        @endif

        {{-- Body --}}
        @if($slot->isNotEmpty())
            <div class="mt-2 text-xs text-[#475569] leading-relaxed">
                {{ $slot }}
            </div>
        @endif
    </div>
</li>
