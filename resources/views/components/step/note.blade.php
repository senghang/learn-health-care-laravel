{{--
    <x-step.note type="info|success|warning|danger" icon="bi-...">
        Message text
    </x-step.note>
--}}
@props([
    'type' => 'info',
    'icon' => null,
])

@php
    $map = [
        'info'    => ['class' => 'note-info',    'icon' => 'bi-info-circle-fill'],
        'success' => ['class' => 'note-success', 'icon' => 'bi-check-circle-fill'],
        'warning' => ['class' => 'note-warn',    'icon' => 'bi-exclamation-triangle-fill'],
        'danger'  => ['class' => 'note-danger',  'icon' => 'bi-exclamation-triangle-fill'],
    ];
    $cfg     = $map[$type] ?? $map['info'];
    $iconCls = $icon ?? $cfg['icon'];
@endphp

<div class="note {{ $cfg['class'] }} mb-3">
    <i class="bi {{ $iconCls }}"></i>
    <div>{{ $slot }}</div>
</div>
