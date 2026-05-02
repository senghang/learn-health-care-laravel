{{--
    <x-card title="Employees" km="បុគ្គលិក" icon="bi-people-fill" :count="$total">
        ... body content ...
    </x-card>

    <x-card title="List" :no-padding="true">
        <x-slot:actions>
            <a href="..." class="btn btn-sm btn-primary">Add</a>
        </x-slot:actions>
        <table>...</table>
    </x-card>

    Props:
      title       — header title text (English)
      km          — Khmer header title (shown before English)
      icon        — Bootstrap icon class e.g. 'bi-people-fill'
      icon-color  — icon color hex, default #4154f1
      count       — optional record count shown in header right
      no-padding  — removes padding from body (for tables/lists)
      header-bg   — custom header background class or style
--}}
@props([
    'title'      => null,
    'km'         => null,
    'icon'       => null,
    'iconColor'  => '#4154f1',
    'count'      => null,
    'noPadding'  => false,
])

<div class="card-emr {{ $attributes->get('class') }}"
     style="{{ $attributes->get('style') }}">

    @if($title || $km || $icon || isset($actions))
    <div class="card-hd">
        <div class="card-hd-title">
            @if($icon)
                <i class="bi {{ $icon }}" style="color:{{ $iconColor }}"></i>
            @endif
            @if($km)
                <span>{{ $km }}</span>
                @if($title)<small style="font-weight:400;color:#aaa;font-size:11px">/ {{ $title }}</small>@endif
            @elseif($title)
                {{ $title }}
            @endif
        </div>
        <div class="d-flex align-items-center gap-2">
            @if($count !== null)
                <span style="font-size:11px;color:#aaa">{{ $count }} records</span>
            @endif
            @if(isset($actions))
                {{ $actions }}
            @endif
        </div>
    </div>
    @endif

    <div class="card-bd" @if($noPadding) style="padding:0" @endif>
        {{ $slot }}
    </div>

</div>
