{{--
    Compact breadcrumb nav — used inside x-ui.page-header.

    <x-ui.breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Patients',  'url' => route('patients.index')],
        ['label' => 'New'],
    ]" />

    Props:
        items — array of ['label', 'url'?]. Last item is current page (no link).
--}}
@props(['items' => []])

@if(count($items))
<nav aria-label="Breadcrumb" {{ $attributes }}>
    <ol class="flex items-center flex-wrap gap-0.5 text-xs" role="list">
        @foreach($items as $i => $item)
            @php $isLast = $i === count($items) - 1; @endphp
            <li class="flex items-center gap-0.5">

                @if(!$isLast && isset($item['url']))
                    <a href="{{ $item['url'] }}"
                       class="flex items-center gap-1 px-1.5 py-0.5 rounded
                              text-[var(--text-muted)] hover:bg-[var(--brand-light)] transition-colors">
                        @if($i === 0)
                            <i class="bi bi-house-door text-[11px]" aria-hidden="true"></i>
                        @endif
                        {{ $item['label'] }}
                    </a>
                @else
                    <span class="px-1.5 py-0.5 font-semibold text-[var(--text-secondary)]"
                          aria-current="{{ $isLast ? 'page' : 'false' }}">
                        {{ $item['label'] }}
                    </span>
                @endif

                @if(!$isLast)
                    <i class="bi bi-chevron-right text-[9px] flex-shrink-0 text-[var(--text-muted)]"
                       aria-hidden="true"></i>
                @endif

            </li>
        @endforeach
    </ol>
</nav>
@endif
