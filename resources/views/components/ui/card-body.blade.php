{{--
    <x-ui.card-body>content</x-ui.card-body>
    <x-ui.card-body :padding="false">table goes here</x-ui.card-body>

    Props:
        padding — bool add padding (default: true)
        compact — smaller padding
--}}
@props([
    'padding' => true,
    'compact' => false,
])

<div {{ $attributes->merge(['class' => $padding ? ($compact ? 'p-4' : 'p-5') : '']) }}>
    {{ $slot }}
</div>
