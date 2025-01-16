<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductCategory extends Model
{
    use HasFactory;

    protected $fillable = ['category_name','parent_id' ,'color'];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'category_product', 'category_id', 'product_id');
    }

    public function parent()
    {
        return $this->belongsTo(ProductCategory::class, 'parent_id');
    }

    /**
     * Get the child categories for this category.
     */
    public function children()
    {
        return $this->hasMany(ProductCategory::class, 'parent_id');
    }

    public function images()
    {
        return $this->hasMany(CategoryImage::class, 'category_id', 'id');
    }
}
