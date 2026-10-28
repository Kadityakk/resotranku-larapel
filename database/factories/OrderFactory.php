<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subtotal = fake()->numberBetween(20, 300) * 1000;
        $tax = (int) round($subtotal * config('restaurant.tax_rate'));

        return [
            'order_code' => Order::generateOrderCode(),
            'user_id' => null,
            'customer_name' => fake()->name(),
            'customer_phone' => fake()->numerify('08##########'),
            'subtotal' => $subtotal,
            'tax' => $tax,
            'grand_total' => $subtotal + $tax,
            'status' => OrderStatus::Pending,
            'table_number' => fake()->numberBetween(1, 20),
            'payment_method' => PaymentMethod::Cash,
            'payment_status' => PaymentStatus::Unpaid,
            'note' => fake()->boolean(30) ? fake()->sentence(6) : null,
        ];
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_status' => PaymentStatus::Paid,
            'paid_at' => now(),
        ]);
    }

    public function online(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_method' => PaymentMethod::Qris,
        ]);
    }

    public function status(OrderStatus $status): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => $status,
        ]);
    }
}
