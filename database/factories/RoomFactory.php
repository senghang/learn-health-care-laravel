<?php

namespace Database\Factories;

use App\Models\RoomModel;
use App\Models\WardModel;
use Illuminate\Database\Eloquent\Factories\Factory;

class RoomFactory extends Factory
{
    protected $model = RoomModel::class;

    public function definition(): array
    {
        return [
            'ward_id'   => WardModel::factory(),
            'code'      => 'RM-' . $this->faker->unique()->numerify('####'),
            'name'      => 'Room ' . $this->faker->numberBetween(100, 500),
            'type'      => 'general',
            'floor'     => $this->faker->numberBetween(1, 5),
            'is_active' => true,
        ];
    }

    public function forWard(WardModel $ward): static
    {
        return $this->state(['ward_id' => $ward->id]);
    }
}
