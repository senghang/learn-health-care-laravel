<?php

namespace App\Http\Controllers\Clinics\Workflows\Steps;

use App\Models\Base\ResolvesEncounter;
use App\Models\TriageModel;
use App\Models\VisitModel;
use Illuminate\Http\Request;

/**
 * TriageStep — enhanced with triage level + BMI auto-calculation.
 *
 * ═══════════════════════════════════════════════════════════════════════════════
 * ADDITIVE CHANGES:
 *   ✅ Added triage_level validation + save
 *   ✅ Auto-calculates BMI from height + weight
 *   ✅ Sets visit.priority from triage_level
 *   ✅ All existing behavior preserved
 * ═══════════════════════════════════════════════════════════════════════════════
 */
class TriageStep extends AbstractWorkflowStep
{
    use ResolvesEncounter;

    public function id(): string         { return 'triage'; }
    public function labelKm(): string    { return 'ពិនិត្យចូល'; }
    public function labelEn(): string    { return 'Triage'; }
    public function icon(): string       { return '🩺'; }
    public function color(): string      { return '#2eca6a'; }
    public function description(): string { return 'Chief complaint, height, weight, triage level'; }

    public function save(VisitModel $visit, Request $request): void
    {
        $data = $this->validate($request, [
            'chief_complaint' => 'nullable|string',
            'triage_level'    => 'nullable|in:Emergency,Urgent,Standard,Low',
            'height'          => 'nullable|numeric|min:30|max:250',
            'weight'          => 'nullable|numeric|min:1|max:300',
            'recorded_by'     => 'nullable|string|max:120',
            'recorded_at'     => 'nullable|date',
            'title'           => 'nullable|string|max:120',
        ]);

        $encounter = $this->getOrCreateEncounter($visit);

        // Auto-calculate BMI
        $bmi = null;
        if (!empty($data['height']) && !empty($data['weight']) && $data['height'] > 0) {
            $heightM = $data['height'] / 100;
            $bmi = round($data['weight'] / ($heightM * $heightM), 1);
        }

        TriageModel::updateOrCreate(
            ['visit_code' => $visit->code],
            array_merge($data, [
                'code'           => 'TR-' . $visit->code,
                'patient_code'   => $visit->patient_code,
                'encounter_code' => $encounter->code,
                'recorded_at'    => $data['recorded_at'] ?? now(),
                'bmi'            => $bmi,
            ])
        );

        // Propagate triage level → visit priority
        if (!empty($data['triage_level'])) {
            $visit->update(['priority' => $data['triage_level']]);
        }
    }

    public function viewData(VisitModel $visit): array
    {
        return ['triage' => $visit->triages()->latest()->first()];
    }
}
