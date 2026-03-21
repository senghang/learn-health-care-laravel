<div class="wf-action-bar d-flex justify-content-between align-items-center flex-wrap gap-2 p-3 border-top bg-light">
    {{-- Left actions: Save & Skip --}}
    <div class="d-flex gap-2 flex-wrap">
        <button type="submit"
                form="{{ $formId ?? 'stepForm' }}"
                name="_action"
                value="save"
                class="btn btn-primary">
            <i class="bi bi-check2-circle"></i>
            រក្សាទុក & បន្ត / Save to continue
        </button>

        @if(isset($skipRoute))
            <a href="{{ $skipRoute }}"
               class="btn btn-warning"
               onclick="return confirm('Skip this step?')">
                <i class="bi bi-skip-forward-fill"></i>
                Skip can fill later
            </a>
        @endif
    </div>

    {{-- Right navigation: Prev / Step counter / Next --}}
    <div class="d-flex align-items-center gap-2">
        @if(isset($prevStepRoute))
            <a href="{{ $prevStepRoute }}"
               class="btn btn-sm btn-outline-primary">
                <i class="bi bi-chevron-left"></i>
            </a>
        @else
            <button disabled class="btn btn-sm btn-outline-primary">
                <i class="bi bi-chevron-left"></i>
            </button>
        @endif

        <span class="text-muted small">
            {{ $stepNumber ?? 1 }} / {{ $totalSteps ?? 1 }}
        </span>

        @if(isset($nextStepRoute))
            <a href="{{ $nextStepRoute }}"
               class="btn btn-sm btn-outline-primary">
                <i class="bi bi-chevron-right"></i>
            </a>
        @else
            <button disabled class="btn btn-sm btn-outline-primary">
                <i class="bi bi-chevron-right"></i>
            </button>
        @endif
    </div>
</div>
