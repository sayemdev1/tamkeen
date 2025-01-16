<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition()
    {
        return [
            'name' => $this->faker->word(),
            'description' => $this->faker->sentence(),
            'price' => $this->faker->numberBetween(10, 1000),
            'stock' => $this->faker->numberBetween(1, 100),
            'track_stock' => true,
            'track_stock_number' => $this->faker->numberBetween(1, 100),
            'store_id' => \App\Models\Store::factory(),
            'base_price' => $this->faker->numberBetween(5, 900),
            'rating' => $this->faker->randomFloat(1, 0, 5),
            'color' => $this->faker->safeColorName(),
            'size' => $this->faker->randomElement(['S', 'M', 'L', 'XL']),
            'barcode' => $this->faker->ean13(),
            'parent_id' => null,
        ];
    }
}
