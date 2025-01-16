<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'track_stock',
        'stock_number',
        'barcode',
        'qr_code',
        'serial_number',
        'size',
        'gender',
        'discount',
        'start_date',
        'end_date',
        'base_price',
        'selling_price',
        'material',
        'weight',
        'style',
        'color',
        'capacity',
        'stock',
        'background_image_path',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
