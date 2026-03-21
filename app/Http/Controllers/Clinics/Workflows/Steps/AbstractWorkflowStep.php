<?php

namespace App\Http\Controllers\Clinics\Workflows\Steps;

use App\Models\VisitModel;
use Illuminate\Http\Request;

/**
 * AbstractWorkflowStep
 *
 * Provides sensible defaults. Concrete steps extend this and only
 * override what differs (usually save() + viewData()).
 */
abstract class AbstractWorkflowStep implements WorkflowStep
{
    public function icon(): string
    {
        return '📋';
    }

    public function description(): string
    {
        return '';
    }

    public function color(): string
    {
        return '#4154f1';
    }

    public function skippable(): bool
    {
        return true;
    }

    public function viewData(VisitModel $visit): array
    {
        return [];
    }

    /**
     * Default view convention: clinics.workflow.steps.{id}
     */
    public function view(): string
    {
        return "clinics.workflow.steps.{$this->id()}";
    }

    /**
     * Default save: do nothing (step is informational or not yet wired).
     * Concrete steps override this to validate + persist.
     */
    public function save(VisitModel $visit, Request $request): void
    {
        // no-op by default
    }

    /**
     * Helper: validate request and return validated data.
     */
    protected function validate(Request $request, array $rules): array
    {
        return $request->validate($rules);
    }
}
