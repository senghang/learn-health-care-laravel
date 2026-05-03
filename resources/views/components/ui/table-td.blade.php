{{--
    <x-ui.table-td>content</x-ui.table-td>
    <x-ui.table-td align="right" @click.stop>actions</x-ui.table-td>

    Props:
        align — left|center|right (default: left)
--}}
@props(['align' => 'left'])

@php
$alignCls = ['left'=>'text-left','center'=>'text-center','right'=>'text-right'][$align] ?? 'text-left';
@endphp

<td {{ $attributes->merge(['class' => "px-4 py-3.5 $alignCls"]) }}>
    {{ $slot }}
</td>
