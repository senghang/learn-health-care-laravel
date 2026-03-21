<?php

namespace App\Http\Controllers\Clinics\Workflows;

use App\Http\Controllers\Clinics\Workflows\Steps\WorkflowStep;
use App\Models\VisitModel;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use LogicException;

/**
 * WorkflowContext — Strategy pattern context.
 *
 * Fix applied:
 *   - toViewData() now includes 'stepIdx' (0-based index of current step)
 *     which every step partial uses as $stepIdx.
 */
class WorkflowContext
{
    /** @var Collection<WorkflowStep> */
    private Collection $steps;

    private WorkflowStep $current;
    private VisitModel $visit;

    public function __construct(VisitModel $visit, string $stepId, array $steps)
    {
        $this->visit = $visit;
        $this->steps = collect($steps)->keyBy(fn(WorkflowStep $s) => $s->id());

        if (!$this->steps->has($stepId)) {
            throw new InvalidArgumentException("Unknown workflow step: [{$stepId}]");
        }

        $this->current = $this->steps->get($stepId);
    }

    public static function for(VisitModel $visit, string $stepId, array $steps): self
    {
        return new self($visit, $stepId, $steps);
    }

    // ── Strategy delegation ───────────────────────────────────────────────────

    public function save(Request $request): void
    {
        $this->current->save($this->visit, $request);

        $done    = collect($this->visit->done_steps ?? [])
            ->push($this->current->id())->unique()->values()->all();
        $skipped = collect($this->visit->skipped_steps ?? [])
            ->filter(fn($s) => $s !== $this->current->id())->values()->all();

        $this->visit->update(['done_steps' => $done, 'skipped_steps' => $skipped]);
    }

    public function skip(): void
    {
        if (!$this->current->skippable()) {
            throw new LogicException("Step [{$this->current->id()}] cannot be skipped.");
        }

        $skipped = collect($this->visit->skipped_steps ?? [])
            ->push($this->current->id())->unique()->values()->all();
        $done    = collect($this->visit->done_steps ?? [])
            ->filter(fn($s) => $s !== $this->current->id())->values()->all();

        $this->visit->update(['done_steps' => $done, 'skipped_steps' => $skipped]);
    }

    // ── Navigation ────────────────────────────────────────────────────────────

    public function nextPendingStep(): ?WorkflowStep
    {
        $done          = $this->visit->done_steps ?? [];
        $passedCurrent = false;

        foreach ($this->steps as $step) {
            if ($step->id() === $this->current->id()) {
                $passedCurrent = true;
                continue;
            }
            if ($passedCurrent && !in_array($step->id(), $done)) {
                return $step;
            }
        }

        return null;
    }

    public function nextStep(): ?WorkflowStep
    {
        $ids = $this->steps->keys()->all();
        $idx = array_search($this->current->id(), $ids);
        return isset($ids[$idx + 1]) ? $this->steps->get($ids[$idx + 1]) : null;
    }

    public function prevStep(): ?WorkflowStep
    {
        $ids = $this->steps->keys()->all();
        $idx = array_search($this->current->id(), $ids);
        return $idx > 0 ? $this->steps->get($ids[$idx - 1]) : null;
    }

    public function currentStep(): WorkflowStep
    {
        return $this->current;
    }

    // ── View ──────────────────────────────────────────────────────────────────

    /**
     * Always render the workflow shell (show.blade.php).
     * Step partials are @included inside it.
     */
    public function view(): string
    {
        return 'clinics.workflow.show';
    }

    /**
     * FIXED: now includes 'stepIdx' — the 0-based position of the current step.
     * Every step partial uses {{ $stepIdx + 1 }} nៃ {{ count($steps) }}.
     */
    public function toViewData(): array
    {
        $done    = $this->visit->done_steps ?? [];
        $skipped = $this->visit->skipped_steps ?? [];

        return array_merge(
            [
                'visit'           => $this->visit,
                'steps'           => $this->stepsAsArrays(),
                'currentStep'     => $this->current->id(),
                'stepIdx'         => $this->currentIndex(),     // ← was missing
                'stepNumber'      => $this->currentIndex() + 1,
                'stepTotal'       => $this->steps->count(),
                'prevStep'        => $this->prevStep()?->id(),
                'nextStep'        => $this->nextStep()?->id(),
                'stepMeta'        => $this->allMeta($done, $skipped),
            ],
            $this->current->viewData($this->visit)
        );
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function stepsAsArrays(): array
    {
        return $this->steps->map(fn(WorkflowStep $s) => [
            'id'          => $s->id(),
            'km'          => $s->labelKm(),
            'en'          => $s->labelEn(),
            'icon'        => $s->icon(),
            'color'       => $s->color(),
            'description' => $s->description(),
            'skippable'   => $s->skippable(),
        ])->values()->all();
    }

    private function currentIndex(): int
    {
        $idx = array_search($this->current->id(), $this->steps->keys()->all());
        return $idx !== false ? (int) $idx : 0;
    }

    private function allMeta(array $done, array $skipped): array
    {
        return $this->steps->map(fn(WorkflowStep $step) => [
            'id'          => $step->id(),
            'labelKm'     => $step->labelKm(),
            'labelEn'     => $step->labelEn(),
            'icon'        => $step->icon(),
            'color'       => $step->color(),
            'description' => $step->description(),
            'skippable'   => $step->skippable(),
            'isDone'      => in_array($step->id(), $done),
            'isSkipped'   => in_array($step->id(), $skipped),
            'isCurrent'   => $step->id() === $this->current->id(),
        ])->values()->all();
    }
}
