<?php

namespace App\Http\Controllers\Clinics\Workflows\Steps;

use App\Models\Base\ResolvesEncounter;
use App\Models\TriageModel;
use App\Models\VisitModel;
use Illuminate\Http\Request;

class TriageStep extends AbstractWorkflowStep
{
    use ResolvesEncounter;

    public function id(): string
    {
        return 'triage';
    }

    public function labelKm(): string
    {
        return 'ពិនិត្យចូល';
    }

    public function labelEn(): string
    {
        return 'Triage';
    }

    public function icon(): string
    {
        return '🩺';
    }

    public function color(): string
    {
        return '#2eca6a';
    }

    public function description(): string
    {
        return 'ការត្អូញត្អែរ កម្ពស់ ទម្ងន់ / Chief complaint, height, weight';
    }

    public function save(VisitModel $visit, Request $request): void
    {
        $data = $this->validate($request, [
            'chief_complaint' => 'nullable|string',
            'height' => 'nullable|numeric|min:30|max:250',
            'weight' => 'nullable|numeric|min:1|max:300',
            'recorded_by' => 'nullable|string|max:120',
            'recorded_at' => 'nullable|date',
            'title' => 'nullable|string|max:120',
        ]);

        $encounter = $this->getOrCreateEncounter($visit);

        TriageModel::updateOrCreate(
            ['visit_code' => $visit->code],
            array_merge($data, [
                'patient_code' => $visit->patient_code,
                'encounter_code' => $encounter->code,
                'recorded_at' => $data['recorded_at'] ?? now(),
                'code' => 'TR-' . $visit->code,
            ])
        );
    }

    public function viewData(VisitModel $visit): array
    {
        // Pass as $triage so blade can use $triage->height etc.
        return ['triage' => $visit->triages()->latest()->first()];
    }
}
