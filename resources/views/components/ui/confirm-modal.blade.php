{{--
    Confirmation dialog — icon-centered layout for destructive/important actions.

    <x-ui.confirm-modal
        id="deletePatient_{{ $patient->id }}"
        title="Delete Patient"
        message="Are you sure? This cannot be undone."
        variant="danger"
        action="{{ route('patients.destroy', $patient->code) }}"
        method="DELETE"
        confirm-label="Yes, Delete"
    />

    Open programmatically:
        document.getElementById('deletePatient_1').dispatchEvent(new Event('open'))

    Props:
        id           — unique id for programmatic open
        title        — dialog title
        message      — confirmation message
        variant      — danger | warning | info  (default: danger)
        action       — form POST action URL
        method       — HTTP method override (default: DELETE)
        confirmLabel — confirm button text
        cancelLabel  — cancel button text
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
    'danger'  => ['icon' => 'bi-exclamation-triangle-fill', 'ibg' => 'bg-red-100',    'iCls' => 'text-red-500',    'btnCls' => 'bg-[#EF4444] hover:bg-[#DC2626] border-[#EF4444] focus-visible:ring-red-300'],
    'warning' => ['icon' => 'bi-exclamation-circle-fill',   'ibg' => 'bg-amber-100',  'iCls' => 'text-amber-500',  'btnCls' => 'bg-[#F59E0B] hover:bg-[#D97706] border-[#F59E0B] focus-visible:ring-amber-300'],
    'info'    => ['icon' => 'bi-info-circle-fill',           'ibg' => 'bg-indigo-100', 'iCls' => 'text-indigo-500', 'btnCls' => 'bg-[#4154f1] hover:bg-[#3344D0] border-[#4154f1] focus-visible:ring-indigo-300'],
];
$c       = $cfg[$variant] ?? $cfg['danger'];
$modalId = $id ?? 'confirm_' . uniqid();
@endphp

<div x-data="{ open: false }"
     @if($id) id="{{ $id }}" @endif
     x-on:open="open = true">

    {{-- Trigger slot --}}
    @isset($trigger)
    <div @click="open = true">{{ $trigger }}</div>
    @endisset

    {{-- Overlay --}}
    <div x-show="open"
         x-transition:enter="transition duration-150 ease-out"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition duration-100 ease-in"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-[var(--bg-overlay)] backdrop-blur"
         @keydown.escape.window="open = false"
         role="alertdialog" aria-modal="true"
         aria-labelledby="{{ $modalId }}-title"
         aria-describedby="{{ $modalId }}-msg"
         x-cloak>

        {{-- Panel --}}
        <div x-transition:enter="transition duration-150 ease-out"
             x-transition:enter-start="opacity-0 scale-95 translate-y-1"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition duration-100 ease-in"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="relative w-full max-w-sm bg-white rounded-2xl overflow-hidden shadow-xl"
             @click.stop>

            {{-- Icon + text --}}
            <div class="flex flex-col items-center text-center px-6 pt-8 pb-5">
                <div class="w-14 h-14 rounded-2xl flex items-center justify-center mb-4 {{ $c['ibg'] }}">
                    <i class="bi {{ $c['icon'] }} text-2xl {{ $c['iCls'] }}" aria-hidden="true"></i>
                </div>
                <h3 id="{{ $modalId }}-title"
                    class="text-base font-bold mb-2 text-[var(--text-primary)]">{{ $title }}</h3>
                <p id="{{ $modalId }}-msg"
                   class="text-sm leading-relaxed text-[var(--text-secondary)]">{{ $message }}</p>
            </div>

            {{-- Buttons --}}
            <div class="flex gap-3 px-6 pb-6">
                <button @click="open = false" type="button"
                        class="flex-1 inline-flex items-center justify-center px-4 py-2.5
                               text-sm font-semibold rounded-lg border bg-white
                               border-[var(--border-default)] text-[var(--text-secondary)]
                               transition-colors duration-150 hover:bg-slate-50
                               focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-200">
                    {{ $cancelLabel }}
                </button>

                @if($action)
                <form action="{{ $action }}" method="POST" class="flex-1">
                    @csrf
                    @if(!in_array(strtoupper($method), ['POST','GET'])) @method($method) @endif
                    <button type="submit"
                            class="w-full inline-flex items-center justify-center px-4 py-2.5
                                   text-sm font-semibold rounded-lg border text-white
                                   transition-colors duration-150 active:scale-[.98]
                                   focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-1
                                   {{ $c['btnCls'] }}">
                        {{ $confirmLabel }}
                    </button>
                </form>
                @else
                <button @click="$dispatch('confirmed'); open = false" type="button"
                        class="flex-1 inline-flex items-center justify-center px-4 py-2.5
                               text-sm font-semibold rounded-lg border text-white
                               transition-colors duration-150 {{ $c['btnCls'] }}">
                    {{ $confirmLabel }}
                </button>
                @endif
            </div>

        </div>
    </div>
</div>
