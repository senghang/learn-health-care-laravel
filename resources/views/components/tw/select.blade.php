{{-- ══════════════════════════════════════════════════════════════════════════════
     FILE: resources/views/components/tw/select.blade.php
     
     Tailwind replacement for x-form.select
     
     Usage:
       <x-tw.select name="gender" label-en="Gender" label-km="ភេទ"
           :options="StoreSettingModel::selectOptions('gender')" required />
     ══════════════════════════════════════════════════════════════════════════════ --}}
@props([
    'name',
    'labelEn' => '',
    'labelKm' => '',
    'options' => [],
    'value' => null,
    'required' => false,
    'placeholder' => '— Select —',
])

<div>
    <label for="{{ $name }}" class="block text-xs font-semibold text-slate-500 mb-1">
        <span>{{ $labelKm }}</span>
        @if($labelEn)<span class="text-slate-400 font-normal"> / {{ $labelEn }}</span>@endif
        @if($required)<span class="text-red-500">*</span>@endif
    </label>
    <select name="{{ $name }}" id="{{ $name }}"
            {{ $required ? 'required' : '' }}
            class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm
                   focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                   @error($name) border-red-400 ring-1 ring-red-400 @enderror">
        @if($placeholder)
            <option value="">{{ $placeholder }}</option>
        @endif
        @foreach($options as $key => $label)
            <option value="{{ $key }}" {{ old($name, $value) == $key ? 'selected' : '' }}>{{ $label }}</option>
        @endforeach
    </select>
    @error($name)
        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
    @enderror
</div>
