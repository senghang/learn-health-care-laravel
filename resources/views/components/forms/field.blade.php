{{--
    Form field wrapper — label + input slot + error message.
    Reduces repetition of the label/error pattern across every form.

    <x-forms.field name="name" label="Full Name" km="ឈ្មោះពេញ" required>
        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
               value="{{ old('name') }}">
    </x-forms.field>

    <x-forms.field name="role_id" label="Role" required>
        <select name="role_id" class="form-select @error('role_id') is-invalid @enderror">...</select>
    </x-forms.field>

    Props:
        name     — field name used to look up validation errors
        label    — English label text
        km       — Khmer label text (shown first if provided)
        required — bool: show asterisk
        hint     — small helper text below the input
        col      — Bootstrap col class (e.g. "col-12 col-sm-6") — wraps in that div if provided
--}}
@props([
    'name'     => '',
    'label'    => null,
    'km'       => null,
    'required' => false,
    'hint'     => null,
    'col'      => null,
])

@php $hasError = $errors->has($name); @endphp

@if($col)
<div class="{{ $col }}">
@endif

<div class="mb-0 {{ $attributes->get('class') }}">
    @if($label || $km)
    <label for="{{ $name }}"
           class="form-label d-block"
           style="font-size:12px;font-weight:700;color:#444;margin-bottom:4px">
        @if($km)
            {{ $km }}
            @if($label)<span style="font-weight:400;color:#94a3b8;margin-left:4px">/ {{ $label }}</span>@endif
        @else
            {{ $label }}
        @endif
        @if($required)<span style="color:#e74c3c;margin-left:2px">*</span>@endif
    </label>
    @endif

    {{ $slot }}

    @error($name)
        <div class="invalid-feedback d-block" style="font-size:11px">{{ $message }}</div>
    @enderror

    @if($hint && !$hasError)
        <div style="font-size:11px;color:#94a3b8;margin-top:3px">{{ $hint }}</div>
    @endif
</div>

@if($col)
</div>
@endif
