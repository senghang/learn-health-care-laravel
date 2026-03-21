@props([
    'name',
    'label',
    'km' => null,
    'options' => [],
    'value' => null
])

<x-form.group>
    <label class="flbl">
        <span class="km">{{ $km }}</span>
        <span class="en">/ {{ $label }}</span>
    </label>

    <select
        name="{{ $name }}"
        {{ $attributes->merge([
            'class' => 'form-select ' . ($errors->has($name) ? 'is-invalid' : '')
        ]) }}
    >
        @foreach($options as $key => $text)
            <option value="{{ $key }}"
                {{ old($name, $value) == $key ? 'selected' : '' }}>
                {{ $text }}
            </option>
        @endforeach
    </select>

    <x-form.error :name="$name"/>
</x-form.group>
