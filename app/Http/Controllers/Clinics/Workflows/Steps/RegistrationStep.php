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

    public function id(): string       { return 'registration'; }
    public function labelKm(): string  { return 'ការចុះឈ្មោះ'; }
    public function labelEn(): string  { return 'Registration'; }
    public function icon(): string     { return '📋'; }
    public function color(): string    { return '#4154f1'; }

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

    public function viewData(VisitModel $visit): array
    {
        return [
            'patient'   => PatientModel::where('code', $visit->patient_code)->first(),
            'address'   => PatientAddressModel::where('patient_code', $visit->patient_code)->first(),
            'encounter' => $this->findEncounter($visit),
            'isIPD'     => $visit->visit_type === 'IPD',
        ];
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function validateData(Request $request): array
    {
        return $this->validate($request, [
            // Patient demographics
            'surname'        => 'required|string|max:120',
            'name'           => 'required|string|max:120',    // patients.name = given name
            'sex'            => 'required|in:M,F',            // form field; maps to patients.gender
            'birthdate'      => 'nullable|date',
            'phone'          => 'nullable|string|max:30',
            'nationality'    => 'nullable|string|max:80',
            'occupation'     => 'nullable|string|max:120',
            'marital_status' => 'nullable|string|max:30',

            // Address (NCDD)
            'province_name'  => 'nullable|string|max:100',
            'district_name'  => 'nullable|string|max:100',
            'commune_name'   => 'nullable|string|max:100',
            'village_name'   => 'nullable|string|max:100',
            'house_number'   => 'nullable|string|max:20',
            'street_number'  => 'nullable|string|max:20',

            // Encounter
            'service_type'   => 'nullable|string|max:80',
            'ward'           => 'nullable|string|max:80',    // IPD ward name
            'bed'            => 'nullable|string|max:30',

            // Discharge
            'discharge_type' => 'nullable|string|max:80',
            'visit_outcome'  => 'nullable|string|max:80',
            'discharged_at'  => 'nullable|date',
            'followup_at'    => 'nullable|date',
        ]);
    }

    private function savePatient(VisitModel $visit, array $data): void
    {
        PatientModel::updateOrCreate(
            ['code' => $visit->patient_code],
            [
                'surname'        => $data['surname'],
                'name'           => $data['name'],         // given name → patients.name
                'gender'         => $data['sex'],          // FIXED: form sends 'sex', DB column is 'gender'
                'birthdate'      => $data['birthdate']      ?? null,
                'phone'          => $data['phone']          ?? null,
                'nationality'    => $data['nationality']    ?? null,
                'occupation'     => $data['occupation']     ?? null,
                'marital_status' => $data['marital_status'] ?? null,
            ]
        );
    }

    private function saveAddress(VisitModel $visit, array $data): void
    {
        $addressFields = Arr::only($data, [
            'province_name', 'district_name', 'commune_name',
            'village_name', 'house_number', 'street_number',
        ]);

        // Only upsert if at least one address field was submitted
        if (array_filter($addressFields)) {
            PatientAddressModel::updateOrCreate(
                ['patient_code' => $visit->patient_code],
                $addressFields
            );
        }
    }

    private function updateVisit(VisitModel $visit, array $data): void
    {
        $visit->update(array_filter([
            'surname'        => $data['surname'],
            'name'           => $data['name'],
            'discharge_type' => $data['discharge_type'] ?? null,
            'visit_outcome'  => $data['visit_outcome']  ?? null,
            'discharged_at'  => $data['discharged_at']  ?? null,
            'followup_at'    => $data['followup_at']    ?? null,
        ], fn($v) => $v !== null));
    }

    private function updateEncounter(VisitModel $visit, array $data): void
    {
        $encounter = $this->getOrCreateEncounter($visit);

        $fields = array_filter([
            'service_type' => $data['service_type'] ?? null,
            'name'         => $data['ward']         ?? null,  // encounter.name stores ward name
            'bed'          => $data['bed']           ?? null,
            'title'        => sprintf(
                '%s — %s, %s',
                $visit->visit_type,
                $data['surname'],
                $data['name']
            ),
        ], fn($v) => $v !== null && $v !== '');

        if ($fields) {
            $encounter->update($fields);
        }
    }
}
