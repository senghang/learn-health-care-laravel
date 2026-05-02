<?php

namespace Database\Factories;

use App\Models\ClinicModel;
use App\Models\WardModel;
use Illuminate\Database\Eloquent\Factories\Factory;

class WardFactory extends Factory
{
    protected $model = WardModel::class;

    public function definition(): array
    {
        return [
            'clinic_id' => ClinicModel::factory(),
            'code'      => 'WRD-' . $this->faker->unique()->numerify('###'),
            'name'      => $this->faker->randomElement(['General Ward', 'ICU', 'Paediatric', 'Maternity', 'Surgical']),
            'type'      => 'IPD',
            'capacity'  => $this->faker->numberBetween(5, 30),
            'is_active' => true,
        ];
    }

    public function forClinic(ClinicModel $clinic): static
    {
        return $this->state(['clinic_id' => $clinic->id]);
    }
}
