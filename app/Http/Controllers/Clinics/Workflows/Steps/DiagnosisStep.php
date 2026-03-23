<?php

namespace App\Http\Controllers\Clinics\Workflows\Steps;

use App\Models\DiagnosisModel;
use App\Models\VisitModel;
use Illuminate\Http\Request;

class DiagnosisStep extends AbstractWorkflowStep
{
    public function id(): string         { return 'diagnosis'; }
    public function labelKm(): string    { return 'រោគវិនិច្ឆ័យ'; }
    public function labelEn(): string    { return 'Diagnosis'; }
    public function icon(): string       { return '🎯'; }
    public function color(): string      { return '#e74c3c'; }
    public function description(): string { return 'ICD-10 — Primary, Secondary'; }

    public function save(VisitModel $visit, Request $request): void
    {
        $data = $this->validate($request, [
            'diagnoses'                => 'required|array|min:1',
            'diagnoses.*.type'         => 'required|in:Primary,Secondary,In,Out',
            'diagnoses.*.code'         => 'nullable|string|max:20',
            'diagnoses.*.name'         => 'required|string|max:200',
            'diagnoses.*.diagnosed_at' => 'nullable|date',
            'diagnoses.*.diagnosed_by' => 'nullable|string|max:120',
            'diagnoses.*.description'  => 'nullable|string',
        ]);

        // Delete and rebuild — diagnosis list can change completely on each save
        $visit->diagnoses()->forceDelete();

        foreach ($data['diagnoses'] as $diag) {
            DiagnosisModel::create([
                'patient_code'         => $visit->patient_code,
                'visit_code'           => $visit->code,
                'diagnosis_type'       => $diag['type'],
                'diagnosis_code'       => $diag['code'] ?? null,
                'diagnosis_name'       => $diag['name'],
                'diagnosis_description'=> $diag['description'] ?? null,
                'diagnosed_at'         => $diag['diagnosed_at'] ?? now(),
                'diagnosed_by'         => $diag['diagnosed_by'] ?? auth()->user()?->name,
            ]);
        }
    }

    public function viewData(VisitModel $visit): array
    {
        $visit->loadMissing('diagnoses');
        return ['diagnoses' => $visit->diagnoses];
    }
}
