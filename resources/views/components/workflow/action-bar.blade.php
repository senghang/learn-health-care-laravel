@props([
    'formId'        => 'stepForm',
    'skipRoute'     => null,
    'prevStepRoute' => null,
    'nextStepRoute' => null,
    'stepNumber'    => 1,
    'totalSteps'    => 1,
])

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 p-3"
     style="border-top:1px solid #f0f2ff">

    {{-- Left: Save + Skip --}}
    <div class="d-flex gap-2 flex-wrap">

        <button type="submit"
                form="{{ $formId }}"
                class="btn btn-primary">
            <i class="bi bi-check2-circle"></i>
            រក្សាទុក &amp; បន្ត / Save &amp; continue
        </button>

        @if($skipRoute)
            {{-- FIXED: route is workflow.skip (GET), confirmed in routes/clinic.php --}}
            <a href="{{ $skipRoute }}"
               class="btn btn-outline-primary"
               onclick="return confirm('Skip this step? You can come back to fill it later.')">
                <i class="bi bi-skip-forward-fill"></i>
                Skip / ដកចោលមុន
            </a>
        @endif

    </div>

    {{-- Right: prev / counter / next --}}
    <div class="d-flex align-items-center gap-2">

        @if($prevStepRoute)
            <a href="{{ $prevStepRoute }}" class="btn btn-sm btn-outline-primary" title="Previous step">
                <i class="bi bi-chevron-left"></i>
            </a>
        @else
            <button disabled class="btn btn-sm btn-outline-primary">
                <i class="bi bi-chevron-left"></i>
            </button>
        @endif

        <span style="font-size:12px;color:#aaa;min-width:44px;text-align:center">
            {{ $stepNumber }} / {{ $totalSteps }}
        </span>

        @if($nextStepRoute)
            <a href="{{ $nextStepRoute }}" class="btn btn-sm btn-outline-primary" title="Next step">
                <i class="bi bi-chevron-right"></i>
            </a>
        @else
            <button disabled class="btn btn-sm btn-outline-primary">
                <i class="bi bi-chevron-right"></i>
            </button>
        @endif

    </div>

</div>
