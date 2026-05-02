<?php

namespace Database\Factories;

use App\Models\ClinicModel;
use App\Models\PatientModel;
use App\Models\VisitModel;
use Illuminate\Database\Eloquent\Factories\Factory;

class VisitFactory extends Factory
{
    protected $model = VisitModel::class;

    public function definition(): array
    {
        return [
            'clinic_id'        => ClinicModel::factory(),
            'code'             => 'V' . now()->format('Ymd') . $this->faker->unique()->numerify('####'),
            'patient_code'     => PatientModel::factory(),
            'surname'          => $this->faker->lastName(),
            'name'             => $this->faker->firstName(),
            'visit_type'       => 'OPD',
            'priority'         => 'Standard',
            'admission_status' => null,
            'done_steps'       => [],
            'skipped_steps'    => [],
            'admitted_at'      => now(),
        ];
    }

    public function opd(): static
    {
        return $this->state(['visit_type' => 'OPD', 'admission_status' => null]);
    }

    public function ipd(): static
    {
        return $this->state(['visit_type' => 'IPD', 'admission_status' => null]);
    }

    public function admitted(): static
    {
        return $this->state(['visit_type' => 'IPD', 'admission_status' => 'admitted']);
    }

    public function emergency(): static
    {
        return $this->state(['priority' => 'Emergency']);
    }

    public function forClinic(ClinicModel $clinic): static
    {
        return $this->state(['clinic_id' => $clinic->id]);
    }

    public function forPatient(PatientModel $patient): static
    {
        return $this->state([
            'clinic_id'    => $patient->clinic_id,
            'patient_code' => $patient->code,
            'surname'      => $patient->surname,
            'name'         => $patient->name,
        ]);
    }
}
