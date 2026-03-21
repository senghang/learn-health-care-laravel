<?php

namespace App\Http\Controllers\Clinics\Workflows\Steps;

use App\Models\VisitModel;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * WorkflowStep — Strategy Interface
 *
 * Every clinical step (Registration, Triage, Labs …) implements this.
 * The WorkflowContext uses this interface to interact with any step
 * without knowing its concrete class.
 */
interface WorkflowStep
{
    /**
     * Unique step identifier used in routes, done_steps[], skipped_steps[].
     * e.g. 'registration', 'triage', 'labs'
     */
    public function id(): string;

    /**
     * Khmer label shown in the step bar and guide panel.
     */
    public function labelKm(): string;

    /**
     * English label.
     */
    public function labelEn(): string;

    /**
     * Icon (emoji or Bootstrap Icon class) for the step bar.
     */
    public function icon(): string;

    /**
     * Short description shown in the guide panel below the step name.
     */
    public function description(): string;

    /**
     * Accent colour (hex) used for step highlights and guide panel dots.
     */
    public function color(): string;

    /**
     * Validate and persist the step's submitted form data.
     * Called by WorkflowController::saveStep().
     *
     * @throws ValidationException
     */
    public function save(VisitModel $visit, Request $request): void;

    /**
     * The Blade view that renders this step's form.
     * Return a view name, e.g. 'clinics.workflow.steps.registration'
     */
    public function view(): string;

    /**
     * Extra data the view needs beyond $visit and $step.
     * Return an associative array merged into view()->with([...]).
     */
    public function viewData(VisitModel $visit): array;

    /**
     * Whether this step can be skipped (all are skippable in MediFlow).
     * Override to return false for mandatory steps (e.g. invoice in some setups).
     */
    public function skippable(): bool;
}

