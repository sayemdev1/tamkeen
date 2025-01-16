<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'coupon_type',
        'promotion_code',
        'expired_at',
        'discount_type',
        'percentage',
        'status',
        'number_of_uses', 
        'use_for',       
    ];

    public function isApplicableTo($type)
    {
        return $this->use_for === $type;
    }

    public function discounts()
    {
        return $this->morphMany(Discount::class, 'discountable');
    }

    public function orders()
    {
        return $this->belongsToMany(Order::class, 'coupon_order_user', 'coupon_id', 'order_id');    
    }
}
