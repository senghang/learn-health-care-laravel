@props(['visit', 'step', 'state'])

<a href="{{ route('workflow.step', [$visit->code, $step['id']]) }}"
   class="step-sel-item
        {{ $state['done'] ? 'done' : '' }}
        {{ $state['active'] ? 'active-s' : '' }}">

    <div class="sel-icon">
        {{ $state['done'] ? '✓' : ($state['skipped'] ? '⏭' : $step['icon']) }}
    </div>

    <div class="sel-lbl">
        {{ $step['km'] }}
        <span class="small text-muted">
            / {{ $step['en'] }}
        </span>
    </div>
</a>
