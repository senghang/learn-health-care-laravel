@props([
    'formId'        => 'stepForm',
    'skipRoute'     => null,
    'prevStepRoute' => null,
    'nextStepRoute' => null,
    'stepNumber'    => 1,
    'totalSteps'    => 1,
])

<div class="wf-action-bar">

    {{-- ── Left: Save + Skip ────────────────────────────────────────── --}}
    <div class="d-flex gap-2 flex-wrap align-items-center">

        <button type="submit" form="{{ $formId }}" class="wf-save-btn">
            <i class="bi bi-check2-circle"></i>
            <span>រក្សាទុក &amp; បន្ត</span>
            <span class="d-none d-sm-inline" style="font-weight:400;opacity:.8;font-size:12px">/ Save &amp; continue</span>
        </button>

        @if($skipRoute)
        <a href="{{ $skipRoute }}"
           class="wf-skip-btn"
           onclick="return confirm('Skip this step? You can come back to fill it later.\n\nដកចោលមុន? អ្នកអាចវិលត្រឡប់ក្រោយ។')">
            <i class="bi bi-skip-forward-fill"></i>
            <span>Skip</span>
            <span class="d-none d-sm-inline" style="opacity:.7;font-size:11px">/ ដកចោលមុន</span>
        </a>
        @endif

    </div>

    {{-- ── Right: Prev / Counter / Next ─────────────────────────────── --}}
    <div class="d-flex align-items-center gap-2">

        @if($prevStepRoute)
        <a href="{{ $prevStepRoute }}" class="wf-nav-btn" title="Previous step (← Arrow key)">
            <i class="bi bi-chevron-left"></i>
        </a>
        @else
        <button disabled class="wf-nav-btn"><i class="bi bi-chevron-left"></i></button>
        @endif

        <span class="wf-step-counter">{{ $stepNumber }} / {{ $totalSteps }}</span>

        @if($nextStepRoute)
        <a href="{{ $nextStepRoute }}" class="wf-nav-btn" title="Next step (→ Arrow key)">
            <i class="bi bi-chevron-right"></i>
        </a>
        @else
        <button disabled class="wf-nav-btn"><i class="bi bi-chevron-right"></i></button>
        @endif

    </div>

</div>
