<?php

namespace App\Http\Controllers\Clinics\Workflows\Steps;

use App\Models\MedicalHistoryModel;
use App\Models\PhysicalExaminationModel;
use App\Models\VisitModel;
use Illuminate\Http\Request;

class HistoryStep extends AbstractWorkflowStep
{
    public function id(): string         { return 'history'; }
    public function labelKm(): string    { return 'ប្រវត្តិ'; }
    public function labelEn(): string    { return 'Med. History'; }
    public function icon(): string       { return '📖'; }
    public function color(): string      { return '#9b59b6'; }
    public function description(): string { return 'ប្រវត្តិជំងឺ & ការពិនិត្យរាងកាយ'; }

    public function save(VisitModel $visit, Request $request): void
    {
        $data = $this->validate($request, [
            'history'                     => 'nullable|array',
            'history.immunizations'       => 'nullable|string|max:500',
            'history.allergies'           => 'nullable|string|max:500',
            'history.past_surgical'       => 'nullable|string|max:500',
            'history.past_medical'        => 'nullable|string|max:500',
            'history.family'              => 'nullable|string|max:500',
            'history.current_meds'        => 'nullable|string|max:500',
            'history.hpi'                 => 'nullable|string',
            'history.social'              => 'nullable|string|max:500',
            'pe'                          => 'nullable|array',
            'pe.*'                        => 'nullable|string',
        ]);

        foreach ($data['history'] ?? [] as $name => $value) {
            if ($value === null || $value === '') continue;
            MedicalHistoryModel::updateOrCreate(
                ['patient_code' => $visit->patient_code, 'visit_code' => $visit->code, 'name' => $name],
                ['encounter_code' => null, 'value' => [$value]]
            );
        }

        foreach ($data['pe'] ?? [] as $system => $value) {
            if ($value === null || $value === '') continue;
            PhysicalExaminationModel::updateOrCreate(
                ['patient_code' => $visit->patient_code, 'visit_code' => $visit->code, 'name' => $system],
                ['encounter_code' => null, 'value' => $value, 'value_type' => 'text']
            );
        }
    }

    public function viewData(VisitModel $visit): array
    {
        $visit->loadMissing('medicalHistories', 'physicalExaminations');
        return [
            'histories'    => $visit->medicalHistories->keyBy('name'),
            'examinations' => $visit->physicalExaminations->keyBy('name'),
        ];
    }
}
