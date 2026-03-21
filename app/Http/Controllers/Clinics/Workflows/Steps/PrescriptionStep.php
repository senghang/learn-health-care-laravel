<?php

namespace App\Http\Controllers\Clinics\Workflows\Steps;

use App\Models\PrescriptionMedicationModel;
use App\Models\PrescriptionModel;
use App\Models\VisitModel;
use Illuminate\Http\Request;

/**
 * PrescriptionStep
 *
 * Blade field names match DB column names exactly:
 *   meds[N][medicine_name]   → prescription_medications.medicine_name
 *   meds[N][strength]        → .strength
 *   meds[N][form]            → .form
 *   meds[N][method]          → .method
 *   meds[N][note]            → .note
 *   meds[N][morning]         → .morning
 *   meds[N][afternoon]       → .afternoon
 *   meds[N][evening]         → .evening
 *   meds[N][night]           → .night
 *   meds[N][days]            → .days
 *   meds[N][interval]        → .interval
 */
class PrescriptionStep extends AbstractWorkflowStep
{
    public function id(): string          { return 'prescription'; }
    public function labelKm(): string     { return 'ថ្នាំ'; }
    public function labelEn(): string     { return 'Prescription'; }
    public function icon(): string        { return '💊'; }
    public function color(): string       { return '#e91e8c'; }
    public function description(): string { return 'ថ្នាំ + កាលវិភាគព្រឹក/ថ្ងៃ/ល្ងាច/យប់'; }

    public function save(VisitModel $visit, Request $request): void
    {
        $data = $this->validate($request, [
            'prescribed_by'                => 'nullable|string|max:120',
            'prescribed_at'                => 'nullable|date',
            'meds'                         => 'required|array|min:1',
            'meds.*.medicine_name'         => 'required|string|max:200',
            'meds.*.strength'              => 'nullable|string|max:60',
            'meds.*.form'                  => 'nullable|string|max:60',
            'meds.*.method'                => 'nullable|string|max:80',
            'meds.*.note'                  => 'nullable|string',
            'meds.*.morning'               => 'nullable|numeric|min:0',
            'meds.*.afternoon'             => 'nullable|numeric|min:0',
            'meds.*.evening'               => 'nullable|numeric|min:0',
            'meds.*.night'                 => 'nullable|numeric|min:0',
            'meds.*.days'                  => 'nullable|integer|min:1',
            'meds.*.interval'              => 'nullable|string|max:40',
        ]);

        $rx = PrescriptionModel::create([
            'code'          => 'RX-' . $visit->code . '-' . now()->timestamp,
            'patient_code'  => $visit->patient_code,
            'visit_code'    => $visit->code,
            'prescribed_at' => $data['prescribed_at'] ?? now(),
            'prescribed_by' => $data['prescribed_by'] ?? null,
            'title'         => implode(', ', array_column($data['meds'], 'medicine_name')),
        ]);

        foreach ($data['meds'] as $med) {
            if (empty(trim($med['medicine_name'] ?? ''))) continue;

            PrescriptionMedicationModel::create([
                'prescription_code' => $rx->code,
                'medicine_name'     => $med['medicine_name'],
                'strength'          => $med['strength']   ?? null,
                'form'              => $med['form']        ?? null,
                'method'            => $med['method']      ?? null,
                'note'              => $med['note']        ?? null,
                'morning'           => $med['morning']     ?? 0,
                'afternoon'         => $med['afternoon']   ?? 0,
                'evening'           => $med['evening']     ?? 0,
                'night'             => $med['night']       ?? 0,
                'days'              => $med['days']        ?? null,
                'interval'          => $med['interval']    ?? null,
            ]);
        }
    }

    public function viewData(VisitModel $visit): array
    {
        $visit->loadMissing('prescriptions');
        $visit->prescriptions->each(fn($rx) => $rx->loadMissing('medications'));
        return ['prescriptions' => $visit->prescriptions];
    }
}
