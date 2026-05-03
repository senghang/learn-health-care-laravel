{{--
    Delete confirmation with form submission:
    <x-ui.confirm-modal
        id="deletePatient_{{ $patient->id }}"
        title="Delete Patient"
        message="Are you sure you want to delete {{ $patient->name }}? This cannot be undone."
        variant="danger"
        action="{{ route('patients.destroy', $patient->code) }}"
        method="DELETE"
        confirm-label="Yes, Delete"
    >
        <x-slot:trigger>
            <x-ui.button variant="danger" size="sm">Delete</x-ui.button>
        </x-slot:trigger>
    </x-ui.confirm-modal>

    Props:
        id            — unique id
        title         — modal title
        message       — confirmation message
        variant       — danger|warning|info (default: danger)
        action        — form action URL (required for form submission)
        method        — HTTP method (default: DELETE)
        confirmLabel  — confirm button text (default: Confirm)
        cancelLabel   — cancel button text (default: Cancel)
--}}
@props([
    'id'           => null,
    'title'        => 'Confirm Action',
    'message'      => 'Are you sure you want to proceed?',
    'variant'      => 'danger',
    'action'       => null,
    'method'       => 'DELETE',
    'confirmLabel' => 'Confirm',
    'cancelLabel'  => 'Cancel',
])

@php
$cfg = [
    'danger'  => ['icon'=>'bi-exclamation-triangle-fill','icolor'=>'#ef4444','ibg'=>'#fef2f2','btnVariant'=>'danger'],
    'warning' => ['icon'=>'bi-exclamation-circle-fill',  'icolor'=>'#ff771d','ibg'=>'#fff7ed','btnVariant'=>'warning'],
    'info'    => ['icon'=>'bi-info-circle-fill',          'icolor'=>'#3b82f6','ibg'=>'#eff6ff','btnVariant'=>'primary'],
];
$c = $cfg[$variant] ?? $cfg['danger'];
$modalId = $id ?? 'confirm_' . uniqid();
@endphp

<div x-data="{ open: false }" @if($id) id="{{ $id }}" @endif x-on:open="open = true">
    @isset($trigger)
    <div @click="open = true">{{ $trigger }}</div>
    @endisset

    <div
        x-show="open"
        x-transition:enter="transition duration-150 ease-out"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition duration-100 ease-in"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        style="background:rgba(1,41,112,.55);backdrop-filter:blur(3px)"
        @keydown.escape.window="open = false"
        role="alertdialog"
        aria-modal="true"
        aria-labelledby="{{ $modalId }}-title"
        aria-describedby="{{ $modalId }}-msg"
        x-cloak
    >
        <div
            class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm"
            x-transition:enter="transition duration-150 ease-out"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            @click.stop
        >
            {{-- Icon header --}}
            <div class="flex flex-col items-center text-center px-6 pt-8 pb-4">
                <div class="w-14 h-14 rounded-2xl flex items-center justify-center mb-4"
                     style="background:{{ $c['ibg'] }}">
                    <i class="bi {{ $c['icon'] }} text-2xl" style="color:{{ $c['icolor'] }}" aria-hidden="true"></i>
                </div>
                <h3 id="{{ $modalId }}-title" class="text-base font-bold text-[#012970] mb-2">{{ $title }}</h3>
                <p id="{{ $modalId }}-msg" class="text-sm leading-relaxed text-[#64748b]">{{ $message }}</p>
            </div>

            {{-- Actions --}}
            <div class="flex gap-3 px-6 pb-6 pt-2">
                <button @click="open = false" type="button"
                        class="flex-1 inline-flex items-center justify-center px-4 py-2.5 text-sm font-semibold rounded-xl border border-[#e2e8f0] text-[#475569] bg-white hover:bg-[#f8faff] transition-colors">
                    {{ $cancelLabel }}
                </button>

                @if($action)
                <form action="{{ $action }}" method="POST" class="flex-1">
                    @csrf
                    @if(!in_array(strtoupper($method), ['POST', 'GET']))
                    @method($method)
                    @endif
                    <button type="submit"
                            class="w-full inline-flex items-center justify-center px-4 py-2.5 text-sm font-semibold rounded-xl text-white transition-colors
                            {{ $c['btnVariant'] === 'danger' ? 'bg-[#ef4444] hover:bg-[#dc2626] border border-[#ef4444]' : '' }}
                            {{ $c['btnVariant'] === 'warning' ? 'bg-[#ff771d] hover:bg-[#e56515] border border-[#ff771d]' : '' }}
                            {{ $c['btnVariant'] === 'primary' ? 'bg-[#4154f1] hover:bg-[#3344d0] border border-[#4154f1]' : '' }}">
                        {{ $confirmLabel }}
                    </button>
                </form>
                @else
                <button @click="$dispatch('confirmed'); open = false" type="button"
                        class="flex-1 inline-flex items-center justify-center px-4 py-2.5 text-sm font-semibold rounded-xl text-white transition-colors bg-[#ef4444] hover:bg-[#dc2626]">
                    {{ $confirmLabel }}
                </button>
                @endif
            </div>
        </div>
    </div>
</div>
