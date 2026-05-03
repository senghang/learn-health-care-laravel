{{--
    <x-ui.radio-group name="sex" label="Sex" km="ភេទ" :options="[
        ['value' => 'M', 'label' => 'Male',   'km' => 'ប្រុស'],
        ['value' => 'F', 'label' => 'Female', 'km' => 'ស្រី'],
    ]" required />

    <x-ui.radio-group name="visit_type" label="Visit Type" :options="[...]" layout="inline" />

    Props:
        name    — input name
        label   — group label
        km      — Khmer group label
        options — array of ['value', 'label', 'km'?, 'description'?]
        layout  — stacked|inline (default: stacked)
        value   — currently selected value
        required — bool
        error   — force error message
--}}
@props([
    'name'     => '',
    'label'    => null,
    'km'       => null,
    'options'  => [],
    'layout'   => 'stacked',
    'value'    => null,
    'required' => false,
    'error'    => null,
])

@php
$hasError = $error || $errors->has($name);
$errMsg   = $error ?? $errors->first($name);
$current  = old($name, $value);
@endphp

<fieldset class="mb-4 {{ $attributes->get('class') }}">
    @if($label || $km)
    <legend class="block text-xs font-semibold mb-2 {{ $hasError ? 'text-[#dc2626]' : 'text-[#374151]' }}">
        @if($km)<span>{{ $km }}</span>@endif
        @if($km && $label)<span class="ml-1 font-normal text-[#94a3b8]">/ {{ $label }}</span>
        @elseif($label){{ $label }}@endif
        @if($required)<span class="text-[#ef4444] ml-0.5" aria-hidden="true">*</span>@endif
    </legend>
    @endif

    <div class="{{ $layout === 'inline' ? 'flex flex-wrap gap-x-6 gap-y-2' : 'space-y-2' }}">
        @foreach($options as $opt)
        @php
            $optId = $name . '_' . $opt['value'];
            $isChecked = (string)$current === (string)$opt['value'];
        @endphp
        <label class="flex items-start gap-3 cursor-pointer group">
            <div class="flex-shrink-0 mt-0.5">
                <input
                    type="radio"
                    id="{{ $optId }}"
                    name="{{ $name }}"
                    value="{{ $opt['value'] }}"
                    @if($isChecked) checked @endif
                    @if($required) required @endif
                    class="sr-only peer"
                />
                <div class="w-4 h-4 rounded-full border-2 flex items-center justify-center transition-all duration-150
                            {{ $hasError ? 'border-[#ef4444]' : 'border-[#cbd5e1] group-hover:border-[#4154f1]' }}
                            peer-checked:border-[#4154f1]
                            peer-focus:ring-2 peer-focus:ring-[#4154f1]/30 peer-focus:ring-offset-1">
                    <div class="w-2 h-2 rounded-full bg-[#4154f1] opacity-0 peer-checked:opacity-100 transition-opacity scale-0 peer-checked:scale-100"></div>
                </div>
            </div>
            <div>
                <span class="text-sm font-medium text-[#374151]">
                    @if(!empty($opt['km']))<span>{{ $opt['km'] }}</span> <span class="text-xs text-[#94a3b8]">/ </span>@endif
                    {{ $opt['label'] }}
                </span>
                @if(!empty($opt['description']))
                <span class="block text-xs text-[#94a3b8] mt-0.5">{{ $opt['description'] }}</span>
                @endif
            </div>
        </label>
        @endforeach
    </div>

    @if($hasError)
    <p class="flex items-center gap-1 mt-2 text-xs font-medium text-[#dc2626]" role="alert">
        <i class="bi bi-exclamation-circle-fill" aria-hidden="true"></i>{{ $errMsg }}
    </p>
    @endif
</fieldset>
