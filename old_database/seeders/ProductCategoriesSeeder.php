<?php

use Illuminate\Database\Seeder;
use App\Models\ProductCategory;

class ProductCategoriesSeeder extends Seeder
{
    public function run()
    {
        // Root Category
        $parentCategory = ProductCategory::create([
            'category_name' => 'Clothing',
            'parent_id' => null,
        ]);

        // Subcategory
        ProductCategory::create([
            'category_name' => 'Men\'s Wear',
            'parent_id' => $parentCategory->id,
        ]);

        ProductCategory::create([
            'category_name' => 'Women\'s Wear',
            'parent_id' => $parentCategory->id,
        ]);
    }
}
