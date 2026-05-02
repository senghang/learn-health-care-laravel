<?php

namespace Database\Factories;

use App\Models\ClinicModel;
use App\Models\PatientModel;
use Illuminate\Database\Eloquent\Factories\Factory;

class PatientFactory extends Factory
{
    protected $model = PatientModel::class;

    public function definition(): array
    {
        return [
            'clinic_id' => ClinicModel::factory(),
            'code'      => 'PT' . now()->format('Ymd') . $this->faker->unique()->numerify('####'),
            'surname'   => $this->faker->lastName(),
            'name'      => $this->faker->firstName(),
            'sex'       => $this->faker->randomElement(['M', 'F']),
            'birthdate' => $this->faker->dateTimeBetween('-80 years', '-1 year')->format('Y-m-d'),
            'phone'     => $this->faker->phoneNumber(),
            'status'    => 'Active',
        ];
    }

    public function male(): static
    {
        return $this->state(['sex' => 'M']);
    }

    public function female(): static
    {
        return $this->state(['sex' => 'F']);
    }

    public function forClinic(ClinicModel $clinic): static
    {
        return $this->state(['clinic_id' => $clinic->id]);
    }
}
