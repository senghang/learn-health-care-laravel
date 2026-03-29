{{-- ══════════════════════════════════════════════════════════════════════════════
     FILE: resources/views/components/tw/field.blade.php
     
     Tailwind replacement for x-form.field
     
     Usage:
       <x-tw.field name="surname" label-en="Surname" label-km="នាមត្រកូល" required />
       <x-tw.field name="notes" label-en="Notes" textarea rows="3" />
     ══════════════════════════════════════════════════════════════════════════════ --}}
@props([
    'name',
    'labelEn' => '',
    'labelKm' => '',
    'type' => 'text',
    'value' => null,
    'placeholder' => '',
    'required' => false,
    'readonly' => false,
    'textarea' => false,
    'rows' => 3,
])

<div>
    <label for="{{ $name }}" class="block text-xs font-semibold text-slate-500 mb-1">
        <span>{{ $labelKm }}</span>
        @if($labelEn)<span class="text-slate-400 font-normal"> / {{ $labelEn }}</span>@endif
        @if($required)<span class="text-red-500">*</span>@endif
    </label>
    @if($textarea)
        <textarea
            name="{{ $name }}" id="{{ $name }}"
            rows="{{ $rows }}"
            placeholder="{{ $placeholder }}"
            {{ $required ? 'required' : '' }}
            {{ $readonly ? 'readonly' : '' }}
            class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm
                   focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                   {{ $readonly ? 'bg-slate-50 text-slate-500 cursor-not-allowed' : '' }}
                   @error($name) border-red-400 ring-1 ring-red-400 @enderror"
        >{{ old($name, $value) }}</textarea>
    @else
        <input
            type="{{ $type }}" name="{{ $name }}" id="{{ $name }}"
            value="{{ old($name, $value) }}"
            placeholder="{{ $placeholder }}"
            {{ $required ? 'required' : '' }}
            {{ $readonly ? 'readonly' : '' }}
            class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm
                   focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                   {{ $readonly ? 'bg-slate-50 text-slate-500 cursor-not-allowed' : '' }}
                   @error($name) border-red-400 ring-1 ring-red-400 @enderror"
        />
    @endif
    @error($name)
        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
    @enderror
</div>
