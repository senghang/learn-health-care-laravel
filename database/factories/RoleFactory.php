<?php

namespace Database\Factories;

use App\Models\ClinicModel;
use App\Models\RoleModel;
use Illuminate\Database\Eloquent\Factories\Factory;

class RoleFactory extends Factory
{
    protected $model = RoleModel::class;

    public function definition(): array
    {
        return [
            'clinic_id' => ClinicModel::factory(),
            'name'      => $this->faker->randomElement(['Admin', 'Doctor', 'Nurse', 'Pharmacist', 'Cashier']),
        ];
    }

    public function admin(): static
    {
        return $this->state(['name' => 'Admin']);
    }

    public function doctor(): static
    {
        return $this->state(['name' => 'Doctor']);
    }
}
