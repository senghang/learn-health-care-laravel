{{--
    Card footer — action row at the bottom of a card.

    <x-ui.card-footer>
        <x-ui.button variant="secondary" size="sm">Cancel</x-ui.button>
        <x-ui.button variant="primary"   size="sm" type="submit">Save</x-ui.button>
    </x-ui.card-footer>

    Props:
        align — start | end | between | center  (default: end)
--}}
@props(['align' => 'end'])

@php
$alignCls = [
    'start'   => 'justify-start',
    'end'     => 'justify-end',
    'between' => 'justify-between',
    'center'  => 'justify-center',
][$align] ?? 'justify-end';
@endphp

<div class="flex items-center gap-3 px-5 py-4 {{ $alignCls }} {{ $attributes->get('class') }}
            border-t border-[var(--border-subtle)] bg-[var(--bg-app)]">
    {{ $slot }}
</div>
