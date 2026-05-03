{{--
    Full card:
    <x-ui.card>
        <x-slot:header>
            <x-ui.card-header title="Patients" km="អ្នកជំងឺ" icon="bi-people-fill" :count="100">
                <x-slot:actions>
                    <x-ui.button size="sm">Add</x-ui.button>
                </x-slot:actions>
            </x-ui.card-header>
        </x-slot:header>
        <x-ui.card-body>…content…</x-ui.card-body>
        <x-slot:footer>
            <x-ui.card-footer>…</x-ui.card-footer>
        </x-slot:footer>
    </x-ui.card>

    Shorthand (title auto-creates header):
    <x-ui.card title="Lab Queue" km="វេជ្ជស្ថាន" icon="bi-flask-fill">
        <p>content</p>
    </x-ui.card>

    Props:
        title      — header title (shorthand — creates header automatically)
        km         — Khmer title
        icon       — Bootstrap icon class
        iconColor  — icon color (default: #4154f1)
        count      — record count in header
        noPadding  — remove body padding (for tables)
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

<div {{ $attributes->merge(['class' => 'bg-white rounded-xl border border-[#e6e9f0] overflow-hidden']) }}
     style="box-shadow:0 1px 2px rgba(17,24,39,.05),0 4px 16px rgba(17,24,39,.04)">

    {{-- Shorthand header --}}
    @if($title || $km || $icon)
    <div class="flex items-center justify-between px-5 {{ $compact ? 'py-3' : 'py-4' }}"
         style="border-bottom:1px solid #e6e9f0">
        <div class="flex items-center gap-2">
            @if($icon)
            <i class="bi {{ $icon }}" style="color:{{ $iconColor }};font-size:15px" aria-hidden="true"></i>
            @endif
            <div>
                @if($km)
                <span class="text-sm font-bold" style="color:#1a1f36">{{ $km }}</span>
                @if($title)<span class="text-xs ml-1.5" style="color:#6b7280">/ {{ $title }}</span>@endif
                @else
                <span class="text-sm font-bold" style="color:#1a1f36">{{ $title }}</span>
                @endif
            </div>
            @if($count !== null)
            <span class="text-xs font-semibold px-2 py-0.5 rounded-full"
                  style="background:#f3f4f6;color:#6b7280">{{ number_format($count) }}</span>
            @endif
        </div>
        @isset($actions)
        <div class="flex items-center gap-2">{{ $actions }}</div>
        @endisset
    </div>
    @endif

    {{-- Named header slot (full custom header) --}}
    @isset($header)
    {{ $header }}
    @endisset

    {{-- Body --}}
    <div @if($noPadding) style="padding:0" @else class="{{ $compact ? 'p-4' : 'p-5' }}" @endif>
        {{ $slot }}
    </div>

    {{-- Footer --}}
    @isset($footer)
    <div style="border-top:1px solid #e6e9f0">{{ $footer }}</div>
    @endisset
</div>
