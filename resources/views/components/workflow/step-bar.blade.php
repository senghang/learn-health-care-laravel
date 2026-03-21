{{-- Desktop --}}
<div class="wf-bar d-none d-lg-block">
    <div class="wf-inner">
        @foreach($steps as $step)

            @php
                $isDone    = in_array($step['id'], $visit->done_steps ?? []);
                $isSkipped = in_array($step['id'], $visit->skipped_steps ?? []);
                $isActive  = $step['id'] === $currentStep;
            @endphp

            <x-workflow.step-item
                :visit="$visit"
                :step="$step"
                :state="[
                    'done' => $isDone,
                    'skipped' => $isSkipped,
                    'active' => $isActive
                ]"
            />
        @endforeach
    </div>
</div>

{{-- Mobile --}}
<div class="step-select-bar card-emr d-lg-none">
    <div class="step-select-hd" onclick="toggleStepList()">
        <div>
            <div class="text-muted small">
                ជំហានបច្ចុប្បន្ន / Current Step
            </div>

            @php
                $current = collect($steps)->firstWhere('id', $currentStep);
            @endphp

            <div class="cur-step">
                {{ $current['icon'] ?? '📋' }}
                {{ $current['km'] ?? '' }}
                <small>{{ $current['en'] ?? '' }}</small>
            </div>
        </div>

        <i class="bi bi-chevron-down" id="stepListChevron"></i>
    </div>

    <div class="step-select-list" id="stepSelectList">
        @foreach($steps as $step)

            @php
                $isDone    = in_array($step['id'], $visit->done_steps ?? []);
                $isSkipped = in_array($step['id'], $visit->skipped_steps ?? []);
                $isActive  = $step['id'] === $currentStep;
            @endphp

            <x-workflow.step-mobile-item
                :visit="$visit"
                :step="$step"
                :state="[
                    'done' => $isDone,
                    'skipped' => $isSkipped,
                    'active' => $isActive
                ]"
            />
        @endforeach
    </div>
</div>
