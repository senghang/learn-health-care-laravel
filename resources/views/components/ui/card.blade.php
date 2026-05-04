{{--
    Card container.

    Full (with named header slot):
    <x-ui.card>
        <x-slot:header><x-ui.card-header km="អ្នកជំងឺ" icon="bi-people-fill" /></x-slot:header>
        body content
    </x-ui.card>

    Shorthand (auto-creates header row):
    <x-ui.card km="វេជ្ជស្ថាន" title="Lab Queue" icon="bi-flask-fill">
        body content
    </x-ui.card>

    Props:
        title|km|icon|iconColor  — shorthand header
        count      — record count badge in shorthand header
        noPadding  — remove body padding (for full-bleed tables)
        compact    — smaller padding
--}}
@props([
    'title'     => null,
    'km'        => null,
    'icon'      => null,
    'iconColor' => '#4154f1',
    'count'     => null,
    'noPadding' => false,
    'compact'   => false,
])

<div {{ $attributes->merge(['class' => 'bg-white rounded-xl border border-[var(--border-subtle)] overflow-hidden shadow-sm']) }}>

    {{-- Shorthand header (only when using title/km/icon props) --}}
    @if($title || $km || $icon)
        <div class="flex items-center justify-between px-5 {{ $compact ? 'py-3' : 'py-4' }}
                    border-b border-[var(--border-subtle)]">
            <div class="flex items-center gap-2 min-w-0">
                @if($icon)
                    <i class="bi {{ $icon }} text-sm flex-shrink-0"
                       style="color:{{ $iconColor }}" aria-hidden="true"></i>
                @endif
                <div class="min-w-0">
                    @if($km)
                        <span class="text-sm font-bold text-[var(--text-primary)] font-khmer">{{ $km }}</span>
                        @if($title)
                            <span class="text-xs ml-1.5 text-[var(--text-muted)]">/ {{ $title }}</span>
                        @endif
                    @else
                        <span class="text-sm font-bold text-[var(--text-primary)]">{{ $title }}</span>
                    @endif
                </div>
                @if($count !== null)
                    <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-slate-100 text-slate-500 flex-shrink-0">
                        {{ number_format($count) }}
                    </span>
                @endif
            </div>
            @isset($actions)
                <div class="flex items-center gap-2 flex-shrink-0">{{ $actions }}</div>
            @endisset
        </div>
    @endif

    {{-- Named header slot (full custom header component) --}}
    @isset($header)
        {{ $header }}
    @endisset

    {{-- Body --}}
    <div class="{{ $noPadding ? '' : ($compact ? 'p-4' : 'p-5') }}">
        {{ $slot }}
    </div>

    {{-- Footer --}}
    @isset($footer)
        <div class="border-t border-[var(--border-subtle)]">{{ $footer }}</div>
    @endisset

</div>
