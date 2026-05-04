<?php

namespace Database\Factories;

use App\Models\ClinicModel;
use App\Models\PatientModel;
use App\Models\PrescriptionModel;
use App\Models\VisitModel;
use Illuminate\Database\Eloquent\Factories\Factory;

class PrescriptionFactory extends Factory
{
    protected $model = PrescriptionModel::class;

    public function definition(): array
    {
        return [
            'clinic_id'        => ClinicModel::factory(),
            'code'             => 'RX' . now()->format('Ymd') . $this->faker->unique()->numerify('####'),
            'patient_code'     => PatientModel::factory(),
            'visit_code'       => null,
            'encounter_code'   => null,
            'prescribed_at'    => now(),
            'prescribed_by'    => 'Dr. ' . $this->faker->lastName(),
            'dispensed_status' => 'pending',
            'title'            => 'Prescription',
        ];
    }

    public function forVisit(VisitModel $visit): static
    {
        return $this->state([
            'clinic_id'    => $visit->clinic_id,
            'patient_code' => $visit->patient_code,
            'visit_code'   => $visit->code,
        ]);
    }

    public function forClinic(ClinicModel $clinic): static
    {
        return $this->state(['clinic_id' => $clinic->id]);
    }

    public function dispensed(): static
    {
        return $this->state([
            'dispensed_status' => 'dispensed',
            'dispensed_by'     => 'Pharmacist Test',
        ]);
    }
}
