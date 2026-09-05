<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        $subtotal = fake()->randomFloat(2, 50, 2000);
        $discount = fake()->optional(0.2)->randomFloat(2, 5, 50) ?? 0;

        return [
            'customer_id' => Customer::factory(),
            'user_id' => User::factory(),
            'order_number' => Order::generateOrderNumber(),
            'status' => fake()->randomElement(['pending', 'confirmed', 'delivered']),
            'subtotal' => $subtotal,
            'discount' => $discount,
            'total' => $subtotal - $discount,
            'payment_method' => fake()->randomElement([
                'cash', 'credit_card', 'debit_card', 'pix',
            ]),
            'notes' => fake()->optional(0.2)->sentence(),
        ];
    }
}
