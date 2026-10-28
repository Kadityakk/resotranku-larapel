<?php

namespace Database\Factories;

use App\Enums\RoleName;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $fullname = fake()->name();

        return [
            'username' => Str::of($fullname)->slug('_')->append('_', Str::lower(Str::random(5)))->value(),
            'password' => static::$password ??= Hash::make('password'),
            'fullname' => $fullname,
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->numerify('08##########'),
            'role_id' => fn () => $this->roleId(RoleName::User),
            'email_verified_at' => now(),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function role(RoleName $role): static
    {
        return $this->state(fn (array $attributes) => [
            'role_id' => $this->roleId($role),
        ]);
    }

    public function admin(): static
    {
        return $this->role(RoleName::Admin);
    }

    public function cashier(): static
    {
        return $this->role(RoleName::Cashier);
    }

    public function chef(): static
    {
        return $this->role(RoleName::Chef);
    }

    /**
     * Reuses the seeded role when it exists so tests do not pile up duplicates.
     */
    private function roleId(RoleName $role): int
    {
        return Role::named($role)->first()?->id
            ?? Role::create([
                'role_name' => $role->value,
                'description' => $role->label(),
            ])->id;
    }
}
