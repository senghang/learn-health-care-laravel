<?php

namespace App\Http\Controllers\Clinics\Workflows\Steps;

use App\Models\Base\ResolvesEncounter;
use App\Models\PatientAddressModel;
use App\Models\PatientModel;
use App\Models\VisitModel;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class RegistrationStep extends AbstractWorkflowStep
{
    use ResolvesEncounter;

    public function id(): string
    {
        return 'registration';
    }

    public function labelKm(): string
    {
        return 'ការចុះឈ្មោះ';
    }

    public function labelEn(): string
    {
        return 'Registration';
    }

    public function icon(): string
    {
        return '📋';
    }

    public function color(): string
    {
        return '#4154f1';
    }

    public function description(): string
    {
        return 'ព័ត៌មានគ្រួសារ អាសយដ្ឋាន / Patient demographics & address';
    }

    public function save(VisitModel $visit, Request $request): void
    {
        $data = $this->validateData($request);

        DB::transaction(function () use ($visit, $data) {

            $this->savePatient($visit, $data);
            $this->saveAddress($visit, $data);
            $this->updateVisit($visit, $data);
            $this->updateEncounter($visit, $data);
        });
    }

    private function validateData(Request $request): array
    {
        return $this->validate($request, [
            'surname' => 'required|string|max:120',
            'name' => 'required|string|max:120',
            'sex' => 'required|in:M,F',
            'birthdate' => 'nullable|date',
            'phone' => 'nullable|string|max:30',
            'nationality' => 'nullable|string|max:80',
            'occupation' => 'nullable|string|max:120',
            'marital_status' => 'nullable|string|max:30',

            // Address
            'province_name' => 'nullable|string|max:100',
            'district_name' => 'nullable|string|max:100',
            'commune_name' => 'nullable|string|max:100',
            'village_name' => 'nullable|string|max:100',
            'house_number' => 'nullable|string|max:20',
            'street_number' => 'nullable|string|max:20',

            // IPD
            'service_type' => 'nullable|string|max:80',
            'ward' => 'nullable|string|max:80',
            'bed' => 'nullable|string|max:30',

            // Discharge
            'discharge_type' => 'nullable|string|max:80',
            'visit_outcome' => 'nullable|string|max:80',
            'discharged_at' => 'nullable|date',
            'followup_at' => 'nullable|date',
        ]);
    }

    private function savePatient(VisitModel $visit, array $data): void
    {
        PatientModel::updateOrCreate(
            ['code' => $visit->patient_code],
            Arr::only($data, [
                'surname',
                'name',
                'sex',
                'birthdate',
                'phone',
                'nationality',
                'occupation',
                'marital_status',
            ])
        );
    }

    private function saveAddress(VisitModel $visit, array $data): void
    {
        PatientAddressModel::updateOrCreate(
            ['patient_code' => $visit->patient_code],
            Arr::only($data, [
                'province_name',
                'district_name',
                'commune_name',
                'village_name',
                'house_number',
                'street_number',
            ])
        );
    }

    private function updateVisit(VisitModel $visit, array $data): void
    {
        $visit->update(Arr::only($data, [
            'surname',
            'name',
            'discharge_type',
            'visit_outcome',
            'discharged_at',
            'followup_at',
        ]));
    }

    private function updateEncounter(VisitModel $visit, array $data): void
    {
        $encounter = $this->getOrCreateEncounter($visit);

        $encounter->update(array_filter([
            'service_type' => $data['service_type'] ?? null,
            'ward' => $data['ward'] ?? null,
            'bed' => $data['bed'] ?? null,
            'title' => sprintf(
                '%s — %s, %s',
                $visit->visit_type,
                $data['surname'],
                $data['name']
            ),
        ], fn($v) => $v !== null && $v !== ''));
    }

    public function viewData(VisitModel $visit): array
    {
        return [
            'visit' => $visit,
            'patient' => PatientModel::where('code', $visit->patient_code)->first(),
            'address' => PatientAddressModel::where('patient_code', $visit->patient_code)->first(),
            'encounter' => $this->findEncounter($visit),
            'isIPD' => $visit->visit_type === 'IPD',
        ];
    }
}
