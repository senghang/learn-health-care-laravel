<?php

namespace Database\Factories;

use App\Models\ClinicModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ClinicFactory extends Factory
{
    protected $model = ClinicModel::class;

    public function definition(): array
    {
        $name = $this->faker->company();

        return [
            'name'      => $name,
            'subdomain' => Str::slug($name) . '-' . $this->faker->unique()->numerify('###'),
            'is_active' => true,
        ];
    }
}
