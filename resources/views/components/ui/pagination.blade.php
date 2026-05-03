{{--
    <x-ui.pagination :paginator="$patients" />

    Props:
        paginator — Laravel paginator instance ($patients->links() replacement)
        simple    — bool use simple prev/next only
--}}
@props([
    'paginator' => null,
    'simple'    => false,
])

@if($paginator && $paginator->hasPages())
@php
    $current    = $paginator->currentPage();
    $last       = $paginator->lastPage();
    $from       = $paginator->firstItem();
    $to         = $paginator->lastItem();
    $total      = $paginator->total();
    $prevUrl    = $paginator->previousPageUrl();
    $nextUrl    = $paginator->nextPageUrl();

    // Smart window: always show first, last, current ±2, with ellipsis
    $window = [];
    for ($i = 1; $i <= $last; $i++) {
        if ($i === 1 || $i === $last || abs($i - $current) <= 2) {
            $window[] = $i;
        }
    }
    // Deduplicate and sort
    $window = array_unique($window);
    sort($window);
@endphp

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mt-4 {{ $attributes->get('class') }}">

    {{-- Count info --}}
    <p class="text-xs text-[#94a3b8]">
        @if($from && $to)
        Showing <span class="font-semibold text-[#374151]">{{ number_format($from) }}</span>–<span class="font-semibold text-[#374151]">{{ number_format($to) }}</span>
        of <span class="font-semibold text-[#374151]">{{ number_format($total) }}</span> results
        @endif
    </p>

    {{-- Page buttons --}}
    <nav aria-label="Pagination">
        <ul class="flex items-center gap-1 list-none p-0 m-0">

            {{-- Prev --}}
            <li>
                @if($prevUrl)
                <a href="{{ $prevUrl }}"
                   class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-sm font-medium text-[#475569] bg-white border border-[#e2e8f0] hover:bg-[#f8faff] hover:border-[#4154f1] hover:text-[#4154f1] transition-colors"
                   aria-label="Previous page">
                    <i class="bi bi-chevron-left" style="font-size:11px"></i>
                </a>
                @else
                <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-sm font-medium text-[#cbd5e1] bg-[#f8faff] border border-[#f0f2ff] cursor-not-allowed"
                      aria-disabled="true" aria-label="Previous page">
                    <i class="bi bi-chevron-left" style="font-size:11px"></i>
                </span>
                @endif
            </li>

            @if(!$simple)
            @php $prevPage = null; @endphp
            @foreach($window as $page)
                @if($prevPage !== null && $page - $prevPage > 1)
                <li><span class="inline-flex items-center justify-center w-8 h-8 text-sm text-[#94a3b8]">…</span></li>
                @endif

                <li>
                    @if($page === $current)
                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-sm font-bold text-white bg-[#4154f1]"
                          aria-current="page" aria-label="Page {{ $page }}">
                        {{ $page }}
                    </span>
                    @else
                    <a href="{{ $paginator->url($page) }}"
                       class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-sm font-medium text-[#475569] bg-white border border-[#e2e8f0] hover:bg-[#f8faff] hover:border-[#4154f1] hover:text-[#4154f1] transition-colors"
                       aria-label="Page {{ $page }}">
                        {{ $page }}
                    </a>
                    @endif
                </li>
                @php $prevPage = $page; @endphp
            @endforeach
            @endif

            {{-- Next --}}
            <li>
                @if($nextUrl)
                <a href="{{ $nextUrl }}"
                   class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-sm font-medium text-[#475569] bg-white border border-[#e2e8f0] hover:bg-[#f8faff] hover:border-[#4154f1] hover:text-[#4154f1] transition-colors"
                   aria-label="Next page">
                    <i class="bi bi-chevron-right" style="font-size:11px"></i>
                </a>
                @else
                <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-sm font-medium text-[#cbd5e1] bg-[#f8faff] border border-[#f0f2ff] cursor-not-allowed"
                      aria-disabled="true" aria-label="Next page">
                    <i class="bi bi-chevron-right" style="font-size:11px"></i>
                </span>
                @endif
            </li>

        </ul>
    </nav>
</div>
@endif
