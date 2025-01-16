<?php

namespace App\Observers;

use App\Models\Product;

class ProductObserver
{
    public function saving(Product $product)
    {
        $product->discounted_price = $product->calculateDiscountedPrice();
    }
}