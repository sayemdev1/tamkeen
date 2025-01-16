<?php

namespace Database\Factories;

use App\Models\CategoryProduct;
use App\Models\Category; // Import the Category model
use App\Models\Product;  // Import the Product model
use App\Models\ProductCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryProductFactory extends Factory
{
    protected $model = CategoryProduct::class;

    public function definition()
    {
        return [
            'category_id' => ProductCategory::factory(), // Create a new category if needed
            'product_id' => Product::factory(),   // Create a new product if needed
        ];
    }
}
