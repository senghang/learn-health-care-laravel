{{--
    <x-ui.dropdown-item href="/url" icon="bi-eye">View</x-ui.dropdown-item>
    <x-ui.dropdown-item icon="bi-trash" variant="danger">Delete</x-ui.dropdown-item>

    Props:
        href    — link URL (renders as <a>, otherwise <button>)
        icon    — Bootstrap icon class (bi-*)
        variant — default|danger   (danger = red text)
--}}
@props([
    'href'    => null,
    'icon'    => null,
    'variant' => 'default',
])

@php
$textCls = $variant === 'danger'
    ? 'text-[#dc2626] hover:bg-[#fef2f2] hover:text-[#b91c1c]'
    : 'text-[#374151] hover:bg-[#f3f4f6] hover:text-[#1a1f36]';
$cls = "flex items-center gap-2.5 w-full px-4 py-2 text-sm font-medium transition-colors $textCls";
@endphp

@if($href)
<a href="{{ $href }}" {{ $attributes->merge(['class' => $cls]) }} role="menuitem">
    @if($icon)<i class="bi {{ $icon }} w-4 flex-shrink-0" style="font-size:13px" aria-hidden="true"></i>@endif
    {{ $slot }}
</a>
@else
<button type="button" {{ $attributes->merge(['class' => $cls]) }} role="menuitem">
    @if($icon)<i class="bi {{ $icon }} w-4 flex-shrink-0" style="font-size:13px" aria-hidden="true"></i>@endif
    {{ $slot }}
</button>
@endif
