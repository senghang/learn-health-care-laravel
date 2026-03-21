@props(['visit', 'step', 'state'])

<a href="{{ route('workflow.step', [$visit->code, $step['id']]) }}"
   class="wf-step
        {{ $state['done'] ? 'done' : '' }}
        {{ $state['active'] ? 'active' : '' }}"
   title="{{ $step['km'] }} / {{ $step['en'] }}{{ $state['skipped'] ? ' [Skipped]' : '' }}">

    <div class="wf-icon
        {{ $state['done'] ? 'done' : '' }}
        {{ $state['skipped'] ? 'skipped' : '' }}">

        @if($state['done'])
            <i class="bi bi-check2"></i>
        @elseif($state['skipped'])
            ⏭
        @else
            {{ $step['icon'] }}
        @endif
    </div>

    <div class="wf-lbl">
        <span class="km">{{ $step['km'] }}</span>
        <span class="en">
            {{ $state['skipped'] ? 'Skipped' : $step['en'] }}
        </span>
    </div>
</a>
