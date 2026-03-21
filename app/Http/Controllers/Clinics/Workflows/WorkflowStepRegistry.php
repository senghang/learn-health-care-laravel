<?php

namespace App\Http\Controllers\Clinics\Workflows;

use App\Http\Controllers\Clinics\Workflows\Steps\DiagnosisStep;
use App\Http\Controllers\Clinics\Workflows\Steps\HistoryStep;
use App\Http\Controllers\Clinics\Workflows\Steps\InvoiceStep;
use App\Http\Controllers\Clinics\Workflows\Steps\LabsStep;
use App\Http\Controllers\Clinics\Workflows\Steps\PrescriptionStep;
use App\Http\Controllers\Clinics\Workflows\Steps\ReferralStep;
use App\Http\Controllers\Clinics\Workflows\Steps\RegistrationStep;
use App\Http\Controllers\Clinics\Workflows\Steps\SoapStep;
use App\Http\Controllers\Clinics\Workflows\Steps\TriageStep;
use App\Http\Controllers\Clinics\Workflows\Steps\VitalsStep;
use App\Http\Controllers\Clinics\Workflows\Steps\WorkflowStep;
use InvalidArgumentException;

/**
 * WorkflowStepRegistry
 *
 * Single place to register all steps and their order.
 * Inject this via Laravel's service container anywhere you need steps.
 *
 * Bind in AppServiceProvider:
 *   $this->app->singleton(WorkflowStepRegistry::class);
 */
class WorkflowStepRegistry
{
    /** @var WorkflowStep[] Ordered list of all steps */
    private array $steps;

    public function __construct()
    {
        $this->steps = [
            new RegistrationStep(),
            new TriageStep(),
            new VitalsStep(),
            new HistoryStep(),
            new LabsStep(),
            new DiagnosisStep(),
            new SoapStep(),
            new PrescriptionStep(),
            new ReferralStep(),
            new InvoiceStep(),
        ];
    }

    /** All steps in order */
    public function all(): array
    {
        return $this->steps;
    }

    /** Find a step by its id string */
    public function find(string $id): WorkflowStep
    {
        foreach ($this->steps as $step) {
            if ($step->id() === $id) return $step;
        }

        throw new InvalidArgumentException("Workflow step not found: [{$id}]");
    }

    /** Check if a step id exists */
    public function has(string $id): bool
    {
        foreach ($this->steps as $step) {
            if ($step->id() === $id) return true;
        }
        return false;
    }

    /** All step IDs in order */
    public function ids(): array
    {
        return array_map(fn(WorkflowStep $s) => $s->id(), $this->steps);
    }
}
