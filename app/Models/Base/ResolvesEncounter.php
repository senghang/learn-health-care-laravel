<?php

namespace App\Models\Base;

use App\Models\OutInPatientModel;
use App\Models\VisitModel;

/**
 * ResolvesEncounter
 *
 * Returns the correct encounter (Outpatient OR Inpatient) based on visit_type.
 * All clinical data (Triage, VitalSign, Soap, etc.) stores encounter_code
 * which points to either outpatients. Code or inpatients.code.
 *
 * Encounter code format (deterministic, safe for firstOrCreate):
 *   OPD visits → OPD-{visit_code}
 *   IPD visits → IPD-{visit_code}
 */
trait ResolvesEncounter
{
    /**
     * Get or create the canonical encounter.
     * Used in save() — creates the record if it doesn't exist yet.
     */
    protected function getOrCreateEncounter(VisitModel $visit): OutInPatientModel
    {
        $prefix = $visit->visit_type === 'IPD' ? 'IPD' : 'OPD';
        $code = "{$prefix}-{$visit->code}";

        return OutInPatientModel::firstOrCreate(
            ['code' => $code],
            [
                'visit_code' => $visit->code,
                'visit_type' => $visit->visit_type,
                'started_at' => $visit->admitted_at ?? now(),
                'title' => "{$prefix} — {$visit->surname}, {$visit->name}",
            ]
        );
    }

    /**
     * Find existing encounter without creating.
     * Used in viewData() — read-only, no side effects.
     */
    protected function findEncounter(VisitModel $visit): ?OutInPatientModel
    {
        $prefix = $visit->visit_type === 'IPD' ? 'IPD' : 'OPD';

        return OutInPatientModel::where('code', "{$prefix}-{$visit->code}")->first()
            ?? $visit->encounters()->latest('started_at')->first();
    }

    /**
     * Get the encounter code string without loading the model.
     */
    protected function encounterCode(VisitModel $visit): string
    {
        $prefix = $visit->visit_type === 'IPD' ? 'IPD' : 'OPD';
        return "{$prefix}-{$visit->code}";
    }
}
