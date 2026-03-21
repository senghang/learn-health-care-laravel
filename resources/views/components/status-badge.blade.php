@props([
    'status',              // 'active' | 'done' | 'ipd' | 'opd' | 'skipped' | 'critical'
    'label'  => null,      // override label text; auto-set from status if null
    'icon'   => true,      // show leading icon for active/done
])

@php
    $map = [
        'active'   => ['class' => 'b-active',   'label' => 'Active',   'icon' => 'bi-circle-fill'],
        'done'     => ['class' => 'b-done',     'label' => '✓ Done',   'icon' => null],
        'ipd'      => ['class' => 'b-ipd',      'label' => 'IPD',      'icon' => null],
        'opd'      => ['class' => 'b-opd',      'label' => 'OPD',      'icon' => null],
        'critical' => ['class' => 'b-critical', 'label' => 'Critical', 'icon' => 'bi-exclamation-triangle-fill'],
        'skipped'  => ['class' => '',           'label' => 'Skipped',  'icon' => null],
    ];

    $cfg        = $map[$status] ?? ['class' => '', 'label' => $status, 'icon' => null];
    $badgeClass = $cfg['class'];
    $badgeLabel = $label ?? $cfg['label'];
    $badgeIcon  = ($icon && $cfg['icon']) ? $cfg['icon'] : null;

    // skipped gets its own inline style
    $skippedStyle = $status === 'skipped'
        ? 'background:#fff8ee;color:#c97700;border:1px solid #ffd080;'
        : '';
@endphp

<span class="badge-s {{ $badgeClass }}" style="{{ $skippedStyle }}{{ $attributes->get('style') }}">
    @if($badgeIcon)
        <i class="bi {{ $badgeIcon }}" style="font-size:7px"></i>
    @endif
    {{ $badgeLabel }}
</span>
