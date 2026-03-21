{{--
    <x-form.field name="height" km="កម្ពស់" en="Height" type="number"
                  placeholder="170" :value="$height" />

    Renders the standard .fld wrapper with bilingual label + any input type.
    For textarea, use: <x-form.field ... textarea rows="3" />
    For select, use <x-form.select instead.

    Props:
      name        — input name attribute (required)
      km          — Khmer label text
      en          — English label text
      type        — input type (default: text)
      value       — current value
      placeholder — placeholder text
      required    — boolean
      readonly    — boolean
      textarea    — boolean, renders <textarea> instead of <input>
      rows        — textarea rows (default 3)
      class       — extra classes on the input
      col         — Bootstrap col class for wrapping (optional, wraps in div if set)
--}}
@props([
    'name',
    'km'          => '',
    'en'          => '',
    'type'        => 'text',
    'value'       => null,
    'placeholder' => null,
    'required'    => false,
    'readonly'    => false,
    'textarea'    => false,
    'rows'        => 3,
    'col'         => null,
    'errorMsg'    => null,
])

@php
    $inputClass = 'form-control' . ($readonly ? ' ro' : '') . ($errors->has($name) ? ' is-invalid' : '');
    $reqAttr    = $required ? 'required' : '';
    $roAttr     = $readonly ? 'readonly' : '';
    $errAttr    = ($required && $errorMsg) ? 'data-error-msg="' . e($errorMsg) . '"' : ($required ? 'data-error-msg="' . e($en ?: $km) . '"' : '');
@endphp

@php $inner = <<<BLADE
<div class="fld">
BLADE; @endphp

<div class="fld" @if($col) @endif>
    <label class="flbl">
        @if($km)<span class="km">{{ $km }}</span>@endif
        @if($en)<span class="en">/ {{ $en }}</span>@endif
        @if($required)<span class="req">*</span>@endif
    </label>

    @if($textarea)
        <textarea
            name="{{ $name }}"
            class="{{ $inputClass }}"
            rows="{{ $rows }}"
            placeholder="{{ $placeholder }}"
            {{ $required ? 'required' : '' }}
            {{ $readonly ? 'readonly' : '' }}
            @if($required) data-error-msg="{{ $errorMsg ?? ($en ?: $km) }}" @endif
            {{ $attributes->except(['name','km','en','type','value','placeholder','required','readonly','textarea','rows','col','errorMsg']) }}
        >{{ old($name, $value) }}</textarea>
    @else
        <input
            type="{{ $type }}"
            name="{{ $name }}"
            value="{{ old($name, $value) }}"
            placeholder="{{ $placeholder }}"
            class="{{ $inputClass }}"
            {{ $required ? 'required' : '' }}
            {{ $readonly ? 'readonly' : '' }}
            @if($required) data-error-msg="{{ $errorMsg ?? ($en ?: $km) }}" @endif
            {{ $attributes->except(['name','km','en','type','value','placeholder','required','readonly','textarea','rows','col','errorMsg']) }}
        />
    @endif

    @error($name)
        <div class="field-error">{{ $message }}</div>
    @enderror
</div>
