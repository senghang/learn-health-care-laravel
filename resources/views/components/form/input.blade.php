@props([
    'name',
    'label'       => null,
    'km'          => null,
    'type'        => 'text',
    'value'       => null,
    'placeholder' => null,
    'required'    => false,
    'readonly'    => false,
    'hint'        => null,
])

<div class="fld">
    @if($label || $km)
    <label class="flbl" for="field_{{ $name }}">
        @if($km)<span class="km">{{ $km }}</span>@endif
        @if($label)<span class="en">/ {{ $label }}</span>@endif
        @if($required)<span class="req">*</span>@endif
    </label>
    @endif

    <input
        id="field_{{ $name }}"
        type="{{ $type }}"
        name="{{ $name }}"
        value="{{ old($name, $value) }}"
        @if($placeholder) placeholder="{{ $placeholder }}" @endif
        @if($required)  required  @endif
        @if($readonly)  readonly  @endif
        {{ $attributes->merge([
            'class' => 'form-control' . ($errors->has($name) ? ' is-invalid' : '') . ($readonly ? ' ro' : ''),
        ]) }}
    />

    @if($hint)
        <div class="hint">{{ $hint }}</div>
    @endif

    @error($name)
        <div class="text-danger" style="font-size:11px;margin-top:3px">{{ $message }}</div>
    @enderror
</div>
