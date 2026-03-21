{{--
    <x-form.select name="title" km="តួរបស់" en="Title"
        :options="['Doctor' => 'Doctor', 'Nurse' => 'Nurse']"
        :value="$title" />

    Props:
      name        — select name
      km          — Khmer label
      en          — English label
      options     — associative array [value => label]
      value       — currently selected value
      required    — boolean
      placeholder — optional empty first option label
      errorMsg    — custom error message text
--}}
@props([
    'name',
    'km'          => '',
    'en'          => '',
    'options'     => [],
    'value'       => null,
    'required'    => false,
    'placeholder' => null,
    'errorMsg'    => null,
])

<div class="fld">
    <label class="flbl">
        @if($km)<span class="km">{{ $km }}</span>@endif
        @if($en)<span class="en">/ {{ $en }}</span>@endif
        @if($required)<span class="req">*</span>@endif
    </label>

    <select
        name="{{ $name }}"
        class="form-select {{ $errors->has($name) ? 'is-invalid' : '' }}"
        {{ $required ? 'required' : '' }}
        @if($required) data-error-msg="{{ $errorMsg ?? ($en ?: $km) }}" @endif
        {{ $attributes->except(['name','km','en','options','value','required','placeholder','errorMsg']) }}
    >
        @if($placeholder !== null)
            <option value="">{{ $placeholder }}</option>
        @endif

        @foreach($options as $optVal => $optLabel)
            <option value="{{ $optVal }}" {{ old($name, $value) == $optVal ? 'selected' : '' }}>
                {{ $optLabel }}
            </option>
        @endforeach
    </select>

    @error($name)
        <div class="field-error">{{ $message }}</div>
    @enderror
</div>
