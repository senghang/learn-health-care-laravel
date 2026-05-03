{{--
    <x-ui.empty-state
        icon="bi-person-circle"
        title="No patients found"
        description="Register the first patient to get started."
    >
        <x-ui.button href="{{ route('patients.create') }}" variant="primary">
            <x-slot:icon><i class="bi bi-person-plus-fill"></i></x-slot:icon>
            Register Patient
        </x-ui.button>
    </x-ui.empty-state>

    Props:
        icon        — Bootstrap icon class (bi-*)
        title       — heading
        description — sub-text
        compact     — bool smaller version (default: false)
--}}
@props([
    'icon'        => 'bi-inbox',
    'title'       => 'Nothing here yet',
    'description' => null,
    'compact'     => false,
])

<div class="flex flex-col items-center justify-center text-center {{ $compact ? 'py-8 px-4' : 'py-16 px-6' }} {{ $attributes->get('class') }}">
    <div class="flex items-center justify-center {{ $compact ? 'w-12 h-12' : 'w-16 h-16' }} rounded-2xl mb-4"
         style="background:#eef0fd">
        <i class="bi {{ $icon }} {{ $compact ? 'text-2xl' : 'text-3xl' }}" style="color:#4154f1" aria-hidden="true"></i>
    </div>

    <h3 class="{{ $compact ? 'text-sm' : 'text-base' }} font-bold mb-1" style="color:#012970">
        {{ $title }}
    </h3>

    @if($description)
    <p class="{{ $compact ? 'text-xs' : 'text-sm' }} mb-4 max-w-xs" style="color:#94a3b8">
        {{ $description }}
    </p>
    @endif

    @if($slot->isNotEmpty())
    <div class="mt-2">{{ $slot }}</div>
    @endif
</div>
