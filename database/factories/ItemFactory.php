<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Item;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Item>
 */
class ItemFactory extends Factory
{
    /**
     * Menu names a restaurant would actually sell, so seeded data reads like
     * the real thing instead of a list of person names.
     *
     * @var array<string, array<int, string>>
     */
    private const MENU_NAMES = [
        'Makanan' => [
            'Nasi Goreng Spesial', 'Ayam Bakar Madu', 'Sate Ayam Bumbu Kacang',
            'Mie Goreng Seafood', 'Soto Ayam Lamongan', 'Rendang Daging Sapi',
            'Gado-Gado Jakarta', 'Ikan Bakar Rica-Rica', 'Bakso Urat Jumbo',
            'Nasi Uduk Komplit',
        ],
        'Minuman' => [
            'Es Teh Manis', 'Jus Alpukat', 'Kopi Susu Gula Aren',
            'Es Jeruk Peras', 'Wedang Jahe Hangat', 'Es Cincau Hijau',
            'Lemon Tea Dingin', 'Cappuccino Panas',
        ],
    ];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $categoryName = fake()->randomElement(array_keys(self::MENU_NAMES));
        $isDrink = $categoryName === 'Minuman';

        return [
            'name' => fake()->unique()->randomElement(self::MENU_NAMES[$categoryName]),
            'category_id' => Category::where('cat_name', $categoryName)->value('id')
                ?? Category::factory()->create(['cat_name' => $categoryName])->id,
            'price' => $isDrink
                ? fake()->numberBetween(8, 35) * 1000
                : fake()->numberBetween(15, 85) * 1000,
            'description' => fake()->sentence(12),
            'img' => fake()->randomElement([
                'https://images.unsplash.com/photo-1569718212165-3a8278d5f624?q=80&w=800&auto=format&fit=crop',
                'https://images.unsplash.com/photo-1579871494447-9811cf80d66c?q=80&w=800&auto=format&fit=crop',
                'https://images.unsplash.com/photo-1534256958597-7fe685cbd745?q=80&w=800&auto=format&fit=crop',
                'https://images.unsplash.com/photo-1512058564366-18510be2db19?q=80&w=800&auto=format&fit=crop',
            ]),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
