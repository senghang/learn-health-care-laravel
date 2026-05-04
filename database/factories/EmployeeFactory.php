<?php

namespace Database\Factories;

use App\Models\ClinicModel;
use App\Models\EmployeeModel;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmployeeFactory extends Factory
{
    protected $model = EmployeeModel::class;

    public function definition(): array
    {
        return [
            'clinic_id'     => ClinicModel::factory(),
            'code'          => 'EMP' . $this->faker->unique()->numerify('#####'),
            'surname'       => $this->faker->lastName(),
            'name'          => $this->faker->firstName(),
            'gender'        => $this->faker->randomElement(['M', 'F']),
            'birthdate'     => $this->faker->dateTimeBetween('-60 years', '-18 years')->format('Y-m-d'),
            'phone'         => $this->faker->numerify('0########'),
            'employee_type' => 'Doctor',
            'hire_date'     => now()->subYear()->format('Y-m-d'),
            'status'        => 'active',
        ];
    }

    public function forClinic(ClinicModel $clinic): static
    {
        return $this->state(['clinic_id' => $clinic->id]);
    }

    public function doctor(): static
    {
        return $this->state(['employee_type' => 'Doctor']);
    }

    public function nurse(): static
    {
        return $this->state(['employee_type' => 'Nurse']);
    }

    public function inactive(): static
    {
        return $this->state(['status' => 'Inactive']);
    }
}
