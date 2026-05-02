{{--
    Standard page header with title, breadcrumbs and optional action slot.

    Usage:
      <x-page-header title="ការកំណត់" subtitle="Settings"
          icon="bi-gear-fill" icon-color="#4154f1"
          :breadcrumbs="[
              ['label'=>'Home','url'=>route('dashboard')],
              ['label'=>'Settings'],
          ]">
          <a href="..." class="btn btn-primary btn-sm">Add</a>
      </x-page-header>

    Props:
      title       — main title (Khmer or English)
      subtitle    — secondary label shown after /
      icon        — Bootstrap icon class (optional)
      icon-color  — icon bg/color preset or hex
      breadcrumbs — array of ['label','url'(optional)]
--}}
@props([
    'title',
    'subtitle' => null,
    'icon' => null,
    'iconColor' => '#4154f1',
    'iconBg' => null,
    'breadcrumbs' => [],
])

@php
    $iconBg = $iconBg ?? $iconColor . '18';
@endphp

<div class="pg-header">
    <div class="d-flex align-items-center gap-3">
        @if ($icon)
            <div
                style="width:42px;height:42px;border-radius:12px;background:{{ $iconBg }};color:{{ $iconColor }};display:flex;align-items:center;justify-content:center;font-size:19px;flex-shrink:0">
                <i class="bi {{ $icon }}"></i>
            </div>
        @endif
        <div>
            <h1 class="pg-title">
                {{ $title }}
                @if ($subtitle)
                    <small>/ {{ $subtitle }}</small>
                @endif
            </h1>
            @if (count($breadcrumbs))
                <div class="breadcrumb-row">
                    @foreach ($breadcrumbs as $crumb)
                        @php
                            // Avoid printing arrays; show [array] if mistaken translation key was given.
                            $label = is_array($crumb['label'] ?? null) ? '[array]' : $crumb['label'] ?? '';
                        @endphp
                        @if (isset($crumb['url']))
                            <a href="{{ $crumb['url'] }}">{{ $label }}</a>
                        @else
                            <span>{{ $label }}</span>
                        @endif
                        @if (!$loop->last)
                            <span>›</span>
                        @endif
                    @endforeach
                </div>
            @endif

        </div>
    </div>

    @if ($slot->isNotEmpty())
        <div class="d-flex gap-2 align-items-center flex-wrap">
            {{ $slot }}
        </div>
    @endif
</div>
