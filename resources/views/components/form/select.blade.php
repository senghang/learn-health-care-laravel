@props([
    'name',
    'label'    => null,
    'km'       => null,
    'options'  => [],
    'value'    => null,
    'required' => false,
    'hint'     => null,
])

<div class="fld">
    @if($label || $km)
    <label class="flbl" for="field_{{ $name }}">
        @if($km)<span class="km">{{ $km }}</span>@endif
        @if($label)<span class="en">/ {{ $label }}</span>@endif
        @if($required)<span class="req">*</span>@endif
    </label>
    @endif

    <select
        id="field_{{ $name }}"
        name="{{ $name }}"
        @if($required) required @endif
        {{ $attributes->merge([
            'class' => 'form-select' . ($errors->has($name) ? ' is-invalid' : ''),
        ]) }}
    >
        @foreach($options as $key => $text)
            <option value="{{ $key }}"
                {{ (string) old($name, $value) === (string) $key ? 'selected' : '' }}>
                {{ $text }}
            </option>
        @endforeach
    </select>

    @if($hint)
        <div class="hint">{{ $hint }}</div>
    @endif

    @error($name)
        <div class="text-danger" style="font-size:11px;margin-top:3px">{{ $message }}</div>
    @enderror
</div>
