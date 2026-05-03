@props([
    'title' => '',
    'subtitle' => null,
    'breadcrumbs' => [],
])

<div class="pg-header">
    <div>
        <h1 class="pg-title">
            {{ $title }}

            @if ($subtitle)
                <small>/ {{ $subtitle }}</small>
            @endif
        </h1>

        @if (count($breadcrumbs))
            <div class="breadcrumb-row flex items-center flex-wrap gap-1 text-sm text-slate-500">
                @foreach ($breadcrumbs as $index => $item)
                    @php
                        $isLast = $index === count($breadcrumbs) - 1;
                    @endphp

                    {{-- Link item --}}
                    @if (!$isLast && isset($item['url']))
                        <a href="{{ $item['url'] }}"
                            class="inline-flex items-center gap-1 px-1.5 py-1 rounded-md hover:bg-slate-100 hover:text-slate-900 transition">

                            @if ($index === 0)
                                <i class="bi bi-house text-[12px]"></i>
                            @endif

                            <span>{{ $item['label'] }}</span>
                        </a>

                        {{-- Current page --}}
                    @else
                        <span class="px-1.5 py-1 font-medium text-slate-900">
                            {{ $item['label'] }}
                        </span>
                    @endif

                    {{-- Separator --}}
                    @if (!$isLast)
                        <span class="text-slate-300">/</span>
                    @endif
                @endforeach
            </div>
        @endif
    </div>
</div>
