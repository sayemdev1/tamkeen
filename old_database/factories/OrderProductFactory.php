<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderProductFactory extends Factory
{
    protected $model = OrderProduct::class;

    public function definition()
    {
        return [
            'order_id' => $this->faker->numberBetween(1, 10), // Adjust based on your Order model
            'product_id' => $this->faker->numberBetween(1, 10), // Adjust based on your Product model
            'quantity' => $this->faker->numberBetween(1, 5),
            'price' => $this->faker->randomFloat(2, 1, 100),
        ];
    
    }
}
