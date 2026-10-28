<?php

namespace Database\Factories;

use App\Enums\RoleName;
use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Role>
 */
class RoleFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'role_name' => fake()->unique()->word(),
            'description' => fake()->sentence(6),
        ];
    }

    /**
     * One of the built-in roles the application gates behaviour on.
     */
    public function named(RoleName $role): static
    {
        return $this->state(fn (array $attributes) => [
            'role_name' => $role->value,
            'description' => $role->label(),
        ]);
    }
}
