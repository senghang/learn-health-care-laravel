{{--
    <x-ui.tabs :tabs="[
        ['id' => 'overview', 'label' => 'Overview', 'km' => 'ទិដ្ឋភាព', 'icon' => 'bi-grid-fill'],
        ['id' => 'visits',   'label' => 'Visits',   'count' => $visits->count()],
    ]" default="overview" variant="line">

        <x-slot:tab_overview>
            <p>Overview content</p>
        </x-slot:tab_overview>

        <x-slot:tab_visits>
            <p>Visits content</p>
        </x-slot:tab_visits>
    </x-ui.tabs>

    Note: slot names use underscore: tab_{id}

    Props:
        tabs    — array of ['id', 'label', 'km'?, 'icon'?, 'count'?, 'disabled'?]
        default — default active tab id
        variant — line|pill (default: line)
--}}
@props([
    'tabs'    => [],
    'default' => null,
    'variant' => 'line',
])

@php
$defaultTab = $default ?? ($tabs[0]['id'] ?? '');
$isPill = $variant === 'pill';
@endphp

<div x-data="{ active: '{{ $defaultTab }}' }" {{ $attributes }}>

    {{-- Tab list --}}
    <div class="{{ $isPill ? 'flex gap-1 p-1 rounded-xl bg-[#f1f5f9]' : 'flex border-b border-[#f0f2ff] overflow-x-auto' }}"
         role="tablist">

        @foreach($tabs as $tab)
        @php $tid = $tab['id'] ?? ''; $isDisabled = $tab['disabled'] ?? false; @endphp

        @if($isPill)
        <button type="button" role="tab"
                :aria-selected="active === '{{ $tid }}'"
                @if(!$isDisabled) @click="active = '{{ $tid }}'" @endif
                @if($isDisabled) disabled aria-disabled="true" @endif
                :class="active === '{{ $tid }}' ? 'bg-white text-[#4154f1] shadow-sm' : 'text-[#64748b] hover:text-[#374151]'"
                class="flex items-center gap-1.5 px-4 py-2 text-sm font-semibold rounded-lg transition-all duration-150 whitespace-nowrap {{ $isDisabled ? 'opacity-40 cursor-not-allowed' : 'cursor-pointer' }}">
        @else
        <button type="button" role="tab"
                :aria-selected="active === '{{ $tid }}'"
                @if(!$isDisabled) @click="active = '{{ $tid }}'" @endif
                @if($isDisabled) disabled aria-disabled="true" @endif
                :class="active === '{{ $tid }}' ? 'border-[#4154f1] text-[#4154f1]' : 'border-transparent text-[#64748b] hover:text-[#374151] hover:border-[#cbd5e1]'"
                class="flex items-center gap-2 px-4 py-3 text-sm font-semibold transition-all duration-150 border-b-2 -mb-px whitespace-nowrap {{ $isDisabled ? 'opacity-40 cursor-not-allowed' : 'cursor-pointer' }}">
        @endif
            @if(!empty($tab['icon']))
            <i class="bi {{ $tab['icon'] }}" style="font-size:13px" aria-hidden="true"></i>
            @endif
            @if(!empty($tab['km']))<span>{{ $tab['km'] }}</span> @if(!empty($tab['label']))<span class="hidden sm:inline text-xs opacity-50">/ {{ $tab['label'] }}</span>@endif
            @elseif(!empty($tab['label']))<span>{{ $tab['label'] }}</span>
            @endif
            @if(isset($tab['count']))
            <span :class="active === '{{ $tid }}' ? 'bg-[#eef0fd] text-[#4154f1]' : 'bg-[#f1f5f9] text-[#94a3b8]'"
                  class="px-1.5 py-0.5 text-xs rounded-full font-bold transition-colors">{{ $tab['count'] }}</span>
            @endif
        </button>
        @endforeach
    </div>

    {{-- Tab panels --}}
    <div class="mt-4">
        @foreach($tabs as $tab)
        @php
            $tid = $tab['id'] ?? '';
            $slotName = 'tab_' . $tid;
        @endphp
        <div x-show="active === '{{ $tid }}'"
             x-transition:enter="transition duration-150 ease-out"
             x-transition:enter-start="opacity-0 translate-y-1"
             x-transition:enter-end="opacity-100 translate-y-0"
             role="tabpanel"
             :aria-hidden="active !== '{{ $tid }}'">
            @if(isset($$slotName)){{ $$slotName }}@endif
        </div>
        @endforeach
    </div>
</div>
