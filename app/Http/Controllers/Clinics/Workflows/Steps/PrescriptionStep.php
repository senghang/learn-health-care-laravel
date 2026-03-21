<?php

namespace App\Http\Controllers\Clinics\Workflows\Steps;

use App\Models\PrescriptionMedicationModel;
use App\Models\PrescriptionModel;
use App\Models\VisitModel;
use Illuminate\Http\Request;

class PrescriptionStep extends AbstractWorkflowStep
{
    public function id(): string
    {
        return 'prescription';
    }

    public function labelKm(): string
    {
        return 'ថ្នាំ';
    }

    public function labelEn(): string
    {
        return 'Prescription';
    }

    public function icon(): string
    {
        return '💊';
    }

    public function color(): string
    {
        return '#e91e8c';
    }

    public function description(): string
    {
        return 'ថ្នាំ + កាលវិភាគព្រឹក/ថ្ងៃ/ល្ងាច/យប់';
    }

    public function save(VisitModel $visit, Request $request): void
    {
        $data = $this->validate($request, [
            'prescribed_by' => 'nullable|string|max:120',
            'medications' => 'required|array|min:1',
            'medications.*.medicine_name' => 'required|string|max:200',
            'medications.*.strength' => 'nullable|string|max:60',
            'medications.*.form' => 'nullable|string|max:60',
            'medications.*.method' => 'nullable|string|max:80',
            'medications.*.morning' => 'nullable|numeric|min:0',
            'medications.*.afternoon' => 'nullable|numeric|min:0',
            'medications.*.evening' => 'nullable|numeric|min:0',
            'medications.*.night' => 'nullable|numeric|min:0',
            'medications.*.days' => 'nullable|integer|min:1',
            'medications.*.interval' => 'nullable|string|max:40',
            'medications.*.note' => 'nullable|string',
        ]);

        $rx = PrescriptionModel::create([
            'code' => 'RX-' . $visit->code . '-' . now()->timestamp,
            'patient_code' => $visit->patient_code,
            'visit_code' => $visit->code,
            'prescribed_at' => now(),
            'prescribed_by' => $data['prescribed_by'] ?? null,
        ]);

        foreach ($data['medications'] as $med) {
            PrescriptionMedicationModel::create(array_merge($med, ['prescription_code' => $rx->code]));
        }
    }

    public function viewData(VisitModel $visit): array
    {
        return ['prescriptions' => $visit->prescriptions()->with('medications')->latest()->get()];
    }
}
