<?php

namespace Database\Factories;

use App\Models\AdmissionModel;
use App\Models\BedModel;
use App\Models\ClinicModel;
use App\Models\PatientModel;
use App\Models\VisitModel;
use App\Models\WardModel;
use Illuminate\Database\Eloquent\Factories\Factory;

class AdmissionFactory extends Factory
{
    protected $model = AdmissionModel::class;

    public function definition(): array
    {
        $clinic  = ClinicModel::factory()->create();
        $patient = PatientModel::factory()->forClinic($clinic)->create();
        $visit   = VisitModel::factory()->forPatient($patient)->ipd()->create();

        return [
            'clinic_id'        => $clinic->id,
            'code'             => 'ADM' . now()->format('Ymd') . $this->faker->unique()->numerify('####'),
            'patient_code'     => $patient->code,
            'visit_code'       => $visit->code,
            'admission_type'   => $this->faker->randomElement(['Emergency', 'Elective', 'Surgical', 'Medical', 'Maternity']),
            'admission_reason' => $this->faker->sentence(),
            'attending_doctor' => 'Dr. ' . $this->faker->name(),
            'admitted_at'      => now(),
            'status'           => AdmissionModel::STATUS_ADMITTED,
        ];
    }

    public function admitted(): static
    {
        return $this->state(['status' => AdmissionModel::STATUS_ADMITTED]);
    }

    public function discharged(): static
    {
        return $this->state([
            'status'              => AdmissionModel::STATUS_DISCHARGED,
            'discharged_at'       => now(),
            'discharge_type'      => 'Normal',
            'discharge_condition' => 'Recovered',
            'discharged_by'       => 'Dr. ' . $this->faker->name(),
        ]);
    }

    public function withBed(BedModel $bed): static
    {
        return $this->state([
            'bed_id'  => $bed->id,
            'ward_id' => $bed->ward_id,
            'room_id' => $bed->room_id,
        ]);
    }

    public function forVisit(VisitModel $visit, ClinicModel $clinic): static
    {
        return $this->state([
            'clinic_id'    => $clinic->id,
            'patient_code' => $visit->patient_code,
            'visit_code'   => $visit->code,
        ]);
    }
}
