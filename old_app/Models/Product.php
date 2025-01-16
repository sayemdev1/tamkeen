<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'stock',
        'track_stock',
        'track_stock_number',
        'store_id',
        'cover_image',
        'background_image',
        'base_price',
        'rating',
        'discount_type',
        'discount_value',
        'start_date',
        'end_date',
        'discounted_price',
        'size',
        'color',
        'material',
        'style',
        'gender',
        'capacity',
        'weight',
        'barcode',
        'qr_code',
        'serial_number',
        'parent_id',
    ];

    protected $hidden = ['base_price'];


    public function scopeWithStockCheck($query)
    {
        // Check if the track_stock field is true for products in the query
        return $query->where(function ($query) {
            $query->where('track_stock', 0) // Include products that do not track stock
            ->orWhere(function ($query) {
                $query->where('track_stock', 1)
                ->where('stock', '>', 0); // Include products with stock > 0
            });
        });
    }


    public function categories()
    {
        return $this->belongsToMany(ProductCategory::class, 'category_product', 'product_id', 'category_id');
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function orders()
    {
        return $this->belongsToMany(Order::class, 'order_product', 'product_id', 'order_id')
        ->withPivot('quantity')
        ->withTimestamps();
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

  
    public function ratings()
    {
        return $this->hasMany(Rating::class, 'product_id', 'id');
    }
    public function calculateDiscountedPrice()
    {
        // Check if discount is active based on start and end date
        // $currentDate = now();
       

            // Calculate discount based on type
            if ($this->discount_type == 'percentage') {
                return $this->price - ($this->price * ($this->discount_value / 100));
            } elseif ($this->discount_type === 'fixed') {
                return max(0, $this->price - $this->discount_value);
            }
        

        // If no active discount, return the original price
        return $this->price;
    }

    protected static function booted()
    {
        static::saving(function ($product) {
            $product->discounted_price = $product->calculateDiscountedPrice();
        });
    }

    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }

    public function discounts()
    {
        return $this->morphMany(Discount::class, 'discountable');
    }

    public function parent()
    {
        return $this->belongsTo(Product::class, 'parent_id');
    }

    public function variants()
    {
        return $this->hasMany(Product::class, 'parent_id');
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class, 'product_id', 'id');
    }


}
