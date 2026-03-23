<?php

namespace App\Http\Controllers\Clinics\Workflows\Steps;

use App\Models\MedicineModel;
use App\Models\PrescriptionMedicationModel;
use App\Models\PrescriptionModel;
use App\Models\VisitModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PrescriptionStep extends AbstractWorkflowStep
{
    public function id(): string      { return 'prescription'; }
    public function labelKm(): string  { return 'ថ្នាំ'; }
    public function labelEn(): string  { return 'Prescription'; }
    public function icon(): string     { return '💊'; }
    public function color(): string    { return '#e91e8c'; }
    public function description(): string { return 'ថ្នាំ + កាលវិភាគ ព្រឹក/ថ្ងៃ/ល្ងាច/យប់'; }

    public function save(VisitModel $visit, Request $request): void
    {
        $data = $this->validate($request, [
            'prescribed_by'        => 'nullable|string|max:120',
            'prescribed_at'        => 'nullable|date',
            'meds'                 => 'nullable|array',
            'meds.*.medicine_code' => 'nullable|string|max:30',
            'meds.*.medicine_name'  => 'required_with:meds|string|max:200',
            'meds.*.strength'      => 'nullable|string|max:60',
            'meds.*.form'          => 'nullable|string|max:60',
            'meds.*.method'        => 'nullable|string|max:80',
            'meds.*.unit'          => 'nullable|string|max:40',
            'meds.*.morning'       => 'nullable|numeric|min:0',
            'meds.*.afternoon'     => 'nullable|numeric|min:0',
            'meds.*.evening'       => 'nullable|numeric|min:0',
            'meds.*.night'         => 'nullable|numeric|min:0',
            'meds.*.days'          => 'nullable|integer|min:1',
            'meds.*.interval'      => 'nullable|string|max:40',
            'meds.*.note'          => 'nullable|string|max:500',
            'dispensed_status'     => 'nullable|in:,partial,dispensed',
            'dispensed_by'         => 'nullable|string|max:120',
        ]);

        $validMeds = array_filter($data['meds'] ?? [], fn($m) => !empty(trim($m['medicine_name'] ?? '')));
        if (empty($validMeds)) return;

        DB::transaction(function () use ($visit, $data, $validMeds) {
            // forceDelete soft-deleted rows first to avoid unique key collision
            PrescriptionModel::withTrashed()
                ->where('visit_code', $visit->code)
                ->whereNotNull('deleted_at')
                ->each(fn($p) => $p->medications()->forceDelete() || $p->forceDelete());

            // updateOrCreate: safe for both first-save and re-save
            $rx = PrescriptionModel::updateOrCreate(
                ['visit_code' => $visit->code],
                [
                    'code'              => 'RX-' . $visit->code,
                    'patient_code'      => $visit->patient_code,
                    'prescribed_at'     => $data['prescribed_at'] ?? now(),
                    'prescribed_by'     => $data['prescribed_by'] ?? auth()->user()?->name,
                    'dispensed_status'  => $data['dispensed_status'] ?? null,
                    'dispensed_by'      => $data['dispensed_by'] ?? null,
                ]
            );

            // Wipe old medications and rebuild
            $rx->medications()->forceDelete();

            foreach ($validMeds as $med) {
                PrescriptionMedicationModel::create([
                    'prescription_code' => $rx->code,
                    'medication_code'   => $med['medicine_code'] ?? null,
                    'medicine_name'     => $med['medicine_name'] ?? $med['name'] ?? '',
                    'strength'          => $med['strength'] ?? null,
                    'form'              => $med['form'] ?? null,
                    'method'            => $med['method'] ?? null,
                    'unit'              => $med['unit'] ?? null,
                    'morning'           => $med['morning'] ?? 0,
                    'afternoon'         => $med['afternoon'] ?? 0,
                    'evening'           => $med['evening'] ?? 0,
                    'night'             => $med['night'] ?? 0,
                    'days'              => $med['days'] ?? null,
                    'interval'          => $med['interval'] ?? null,
                    'note'              => $med['note'] ?? null,
                ]);
            }
        });
    }

    public function viewData(VisitModel $visit): array
    {
        $rx = PrescriptionModel::where('visit_code', $visit->code)
            ->with('medications')->latest()->first();

        // Medicines catalog from Settings > Medicines
        $catalog = MedicineModel::where('clinic_id', currentClinic()->id)
            ->where('is_active', true)
            ->orderBy('form')->orderBy('name')
            ->get(['id','code','name','name_kh','generic_name','form','strength','unit','price','stock']);

        return [
            'prescription' => $rx,
            'medications'  => $rx?->medications ?? collect([]),
            'catalog'      => $catalog,
            'formOptions'  => ['Tablet'=>'Tablet','Capsule'=>'Capsule','Syrup'=>'Syrup',
                               'Injection'=>'Injection','Ointment'=>'Ointment','Drops'=>'Drops'],
        ];
    }
}
