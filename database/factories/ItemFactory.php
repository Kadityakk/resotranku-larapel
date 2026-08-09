<?php

namespace Database\Factories;

use App\Models\Item;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Item>
 */
class ItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name'=> $this->faker->name(),
            'category_id' => $this->faker->numberBetween(1,2),
            'price' => $this->faker->numberBetween(5000, 100000),
            'description' => $this->faker->text(),
            'img' => fake()->randomElement([
            'https://images.unsplash.com/photo-1569718212165-3a8278d5f624',
            'https://images.unsplash.com/photo-1579871494447-9811cf80d66c',
            'https://images.unsplash.com/photo-1534256958597-7fe685cbd745',]),
            'is_active' => $this->faker->boolean(),
        ];
    }
}
