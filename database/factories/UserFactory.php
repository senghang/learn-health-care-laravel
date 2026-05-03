<?php

namespace Database\Factories;

use App\Models\ClinicModel;
use App\Models\RoleModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'clinic_id'          => ClinicModel::factory(),
            'name'               => fake()->name(),
            'email'              => fake()->unique()->safeEmail(),
            'password'           => static::$password ??= Hash::make('password'),
            'is_active'          => true,
            'remember_token'     => Str::random(10),
        ];
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }

    public function withRole(RoleModel $role): static
    {
        return $this->state([
            'clinic_id' => $role->clinic_id,
            'role_id'   => $role->id,
        ]);
    }
}
