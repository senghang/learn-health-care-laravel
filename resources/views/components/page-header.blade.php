@props([
    'title',               // required — main title (Khmer)
    'subtitle'  => null,   // optional — English subtitle shown after /
    'breadcrumbs' => [],   // array of ['label' => '...', 'url' => '...'] or just ['label']
])

<div class="pg-header">
    <div>
        <h1 class="pg-title">
            {{ $title }}
            @if($subtitle)
                <small>/ {{ $subtitle }}</small>
            @endif
        </h1>
        @if(count($breadcrumbs))
            <div class="breadcrumb-row">
                @foreach($breadcrumbs as $i => $crumb)
                    @if(isset($crumb['url']))
                        <a href="{{ $crumb['url'] }}">
                            {{ is_string($crumb['label']) ? $crumb['label'] : '' }}
                        </a>
                    @else
                        <span>{{ $crumb['label'] }}</span>
                    @endif
                    @if(!$loop->last)
                        <span>›</span>
                    @endif
                @endforeach
            </div>
        @endif
    </div>

    @if($slot->isNotEmpty())
        <div class="d-flex gap-2 align-items-center flex-wrap">
            {{ $slot }}
        </div>
    @endif
</div>
