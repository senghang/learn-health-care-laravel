<?php

namespace App\Http\Controllers\Clinics\Workflows;

use App\Http\Controllers\Clinics\Workflows\Steps\WorkflowStep;
use App\Models\VisitModel;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use LogicException;

/**
 * WorkflowContext
 *
 * The Context in the Strategy pattern.
 * Holds the ordered list of steps, tracks which is active,
 * and delegates save/skip operations to the current strategy.
 *
 * Usage in controller:
 *
 *   $ctx = WorkflowContext::for($visit, $stepId, $steps);
 *   $ctx->save($request);          // delegates to current step
 *   $ctx->advance();               // move to next pending step
 *   $ctx->toViewData();            // returns array for blade
 */
class WorkflowContext
{
    /** @var Collection<WorkflowStep> */
    private Collection $steps;

    private WorkflowStep $current;
    private VisitModel $visit;

    /**
     * @param VisitModel $visit
     * @param string $stepId — active step id
     * @param WorkflowStep[] $steps — ordered array of all steps
     */
    public function __construct(VisitModel $visit, string $stepId, array $steps)
    {
        $this->visit = $visit;
        $this->steps = collect($steps)->keyBy(fn(WorkflowStep $s) => $s->id());

        if (!$this->steps->has($stepId)) {
            throw new InvalidArgumentException("Unknown step: {$stepId}");
        }

        $this->current = $this->steps->get($stepId);
    }

    /** Factory shortcut */
    public static function for(VisitModel $visit, string $stepId, array $steps): self
    {
        return new self($visit, $stepId, $steps);
    }

    // ── Strategy delegation ───────────────────────────────────────────────────

    /** Delegate save to the current step strategy */
    public function save(Request $request): void
    {

        $this->current->save($this->visit, $request);
        $done = collect($this->visit->done_steps ?? [])->push($this->current->id())->unique()->values()->all();
        $skipped = collect($this->visit->skipped_steps ?? [])->filter(fn($s) => $s !== $this->current->id())->values()->all();
        $this->visit->update(['done_steps' => $done, 'skipped_steps' => $skipped]);
    }

    /** Mark current step as skipped */
    public function skip(): void
    {
        if (!$this->current->skippable()) {
            throw new LogicException("Step [{$this->current->id()}] cannot be skipped.");
        }

        $skipped = collect($this->visit->skipped_steps ?? [])->push($this->current->id())->unique()->values()->all();
        $done = collect($this->visit->done_steps ?? [])->filter(fn($s) => $s !== $this->current->id())->values()->all();

        $this->visit->update(['done_steps' => $done, 'skipped_steps' => $skipped]);
    }

    // ── Navigation ────────────────────────────────────────────────────────────

    /** Next step that is not yet done (skipping done steps) */
    public function nextPendingStep(): ?WorkflowStep
    {
        $done = $this->visit->done_steps ?? [];
        $passedCurrent = false;

        foreach ($this->steps as $step) {
            if ($step->id() === $this->current->id()) {
                $passedCurrent = true;
                continue;
            }
            if ($passedCurrent && !in_array($step->id(), $done)) return $step;
        }

        return null;
    }

    /** All data needed by workflow Blade views */
    public function toViewData(): array
    {
        $done = $this->visit->done_steps ?? [];
        $skipped = $this->visit->skipped_steps ?? [];

        return array_merge(
            [
                'visit' => $this->visit,
                'steps' => $this->stepsAsArrays(),   // blade reads $step['id'] etc.
                'currentStep' => $this->current->id(),
                'currentStepView' => $this->current->view(),   // show.blade.php uses this to @include
                'stepNumber' => $this->currentIndex() + 1,
                'stepTotal' => $this->steps->count(),
                'prevStep' => $this->prevStep()?->id(),
                'nextStep' => $this->nextStep()?->id(),
                'stepMeta' => $this->allMeta($done, $skipped),
            ],
            $this->current->viewData($this->visit)
        );
    }

    /**
     * Steps as plain associative arrays — matches show.blade.php's $step['id'] etc.
     */
    private function stepsAsArrays(): array
    {
        return $this->steps->map(fn(WorkflowStep $s) => [
            'id' => $s->id(),
            'km' => $s->labelKm(),
            'en' => $s->labelEn(),
            'icon' => $s->icon(),
            'color' => $s->color(),
            'description' => $s->description(),
            'skippable' => $s->skippable(),
        ])->values()->all();
    }

    // ── View helpers ──────────────────────────────────────────────────────────

    /**
     * Always render through the workflow shell (show.blade.php).
     * The shell @includes the step partial internally.
     * Individual step views are partials, never rendered directly.
     */
    public function view(): string
    {
        return 'clinics.workflow.show';
    }

    private function currentIndex(): int
    {
        return array_search($this->current->id(), $this->steps->keys()->all());
    }

    /** Previous step */
    public function prevStep(): ?WorkflowStep
    {
        $ids = $this->steps->keys()->all();
        $idx = array_search($this->current->id(), $ids);
        return $idx > 0 ? $this->steps->get($ids[$idx - 1]) : null;
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    /** Immediately next step regardless of done status */
    public function nextStep(): ?WorkflowStep
    {
        $ids = $this->steps->keys()->all();
        $idx = array_search($this->current->id(), $ids);
        return isset($ids[$idx + 1]) ? $this->steps->get($ids[$idx + 1]) : null;
    }

    /** Build the full meta array for the step bar + guide panel */
    private function allMeta(array $done, array $skipped): array
    {
        return $this->steps->map(function (WorkflowStep $step) use ($done, $skipped) {
            return [
                'id' => $step->id(),
                'labelKm' => $step->labelKm(),
                'labelEn' => $step->labelEn(),
                'icon' => $step->icon(),
                'color' => $step->color(),
                'description' => $step->description(),
                'skippable' => $step->skippable(),
                'isDone' => in_array($step->id(), $done),
                'isSkipped' => in_array($step->id(), $skipped),
                'isCurrent' => $step->id() === $this->current->id(),
            ];
        })->values()->all();
    }

    /** Current step object */
    public function currentStep(): WorkflowStep
    {
        return $this->current;
    }
}
