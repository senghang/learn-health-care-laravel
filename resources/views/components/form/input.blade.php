@props([
    'name',
    'label',
    'km' => null,
    'type' => 'text',
    'value' => null,
    'placeholder' => null,
    'required' => false, // ✅ new prop
])

<x-form.group>
    <label class="flbl">
        <span class="km">
            {{ $km }}
        </span>
        <span class="en">
            / {{ $label }}
            @if($required)
                <span style="color:red;">*</span>
            @endif
        </span>
    </label>

    <input
        type="{{ $type }}"
        name="{{ $name }}"
        value="{{ old($name, $value) }}"
        placeholder="{{ $placeholder }}"
        @if($required) required @endif
        {{ $attributes->merge([
            'class' => 'form-control ' . ($errors->has($name) ? 'is-invalid' : '')
        ]) }}
    />

    <x-form.error :name="$name"/>
</x-form.group>
