<?php

namespace Database\Factories;

use App\Models\ClinicModel;
use App\Models\MedicineModel;
use Illuminate\Database\Eloquent\Factories\Factory;

class MedicineFactory extends Factory
{
    protected $model = MedicineModel::class;

    public function definition(): array
    {
        $forms = ['Tablet', 'Capsule', 'Syrup', 'Injection', 'Cream'];
        $drugs = ['Amoxicillin', 'Paracetamol', 'Ibuprofen', 'Metformin', 'Omeprazole',
                  'Atorvastatin', 'Amlodipine', 'Metronidazole', 'Ciprofloxacin', 'Diazepam'];

        return [
            'clinic_id'   => ClinicModel::factory(),
            'code'        => 'MED-' . $this->faker->unique()->numerify('#####'),
            'name'        => $this->faker->randomElement($drugs) . ' ' . $this->faker->numerify('###mg'),
            'generic_name'=> $this->faker->word(),
            'form'        => $this->faker->randomElement($forms),
            'strength'    => $this->faker->numerify('###mg'),
            'unit'        => 'tablet',
            'price'       => $this->faker->randomFloat(2, 500, 50000),
            'stock'       => $this->faker->numberBetween(20, 500),
            'stock_alert' => 10,
            'is_active'   => true,
        ];
    }

    public function withStock(int $qty): static
    {
        return $this->state(['stock' => $qty]);
    }

    public function outOfStock(): static
    {
        return $this->state(['stock' => 0]);
    }

    public function lowStock(): static
    {
        return $this->state(['stock' => 5, 'stock_alert' => 10]);
    }

    public function forClinic(ClinicModel $clinic): static
    {
        return $this->state(['clinic_id' => $clinic->id]);
    }
}
