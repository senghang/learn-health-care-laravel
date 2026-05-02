<?php

namespace Database\Factories;

use App\Models\BedModel;
use App\Models\RoomModel;
use App\Models\WardModel;
use Illuminate\Database\Eloquent\Factories\Factory;

class BedFactory extends Factory
{
    protected $model = BedModel::class;

    public function definition(): array
    {
        $ward = WardModel::factory()->create();
        $room = RoomModel::factory()->forWard($ward)->create();

        return [
            'ward_id'   => $ward->id,
            'room_id'   => $room->id,
            'code'      => 'BED-' . $this->faker->unique()->numerify('####'),
            'name'      => 'Bed ' . $this->faker->numerify('##'),
            'status'    => 'available',
            'type'      => 'standard',
            'is_active' => true,
        ];
    }

    public function available(): static
    {
        return $this->state(['status' => 'available']);
    }

    public function occupied(): static
    {
        return $this->state([
            'status'               => 'occupied',
            'current_visit_code'   => 'V' . now()->format('Ymd') . '0001',
            'current_patient_code' => 'PT' . now()->format('Ymd') . '0001',
        ]);
    }

    public function cleaning(): static
    {
        return $this->state(['status' => 'cleaning']);
    }

    public function inWard(WardModel $ward, RoomModel $room): static
    {
        return $this->state(['ward_id' => $ward->id, 'room_id' => $room->id]);
    }
}
