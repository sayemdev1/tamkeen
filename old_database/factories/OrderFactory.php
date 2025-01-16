<?php

namespace Database\Factories;

use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition()
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'store_id' => \App\Models\Store::factory(),
            'total_price' => $this->faker->randomFloat(2, 20, 500),
            'order_status' => $this->faker->randomElement(['pending', 'completed', 'canceled']),
            'payment_method_id' => \App\Models\PaymentMethod::factory(),
        ];
    }
}
