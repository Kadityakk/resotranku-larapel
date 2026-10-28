<?php

namespace Database\Factories;

use App\Models\Item;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderItem>
 */
class OrderItemFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $price = fake()->numberBetween(10, 80) * 1000;
        $quantity = fake()->numberBetween(1, 4);
        $lineSubtotal = $price * $quantity;
        $tax = (int) round($lineSubtotal * config('restaurant.tax_rate'));

        return [
            'order_id' => Order::factory(),
            'item_id' => Item::factory(),
            'quantity' => $quantity,
            'price' => $price,
            'tax' => $tax,
            'total_price' => $lineSubtotal + $tax,
        ];
    }
}
