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
    <div class="d-flex gap-2 flex-wrap align-items-center" style="min-width:0">

        {{-- Auto-save status --}}
        <span id="wfAutoSaveIndicator"
              style="display:none;align-items:center;gap:4px;font-size:10.5px;color:#bbb;
                     font-family:inherit;flex-shrink:0">
        </span>

        <button type="submit" form="{{ $formId }}" class="wf-save-btn">
            <i class="bi bi-check2-circle"></i>
            <span>រក្សាទុក &amp; បន្ត</span>
            <span class="d-none d-sm-inline" style="font-weight:400;opacity:.8;font-size:12px">/ Save &amp; continue</span>
        </button>

        @if($skipRoute)
        <a href="{{ $skipRoute }}"
           class="wf-skip-btn"
           data-confirm="Skip this step? You can return to fill it later.&#10;&#10;ដកចោលមុន? អ្នកអាចវិលត្រឡប់ក្រោយ។"
           data-confirm-type="info" data-confirm-title="Skip Step">
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
