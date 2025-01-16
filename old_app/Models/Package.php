<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'total_price' , 'number_of_uses','store_id'];

    public function items()
    {
        return $this->hasMany(PackageItem::class);
    }

    public function images()
    {
        return $this->hasMany(PackageImage::class);
    }

    public function reviews()
    {
        return $this->hasMany(PackageUserReview::class, 'package_id');
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function basketOrders()
    {
        return $this->belongsToMany(PackageUser::class, 'package_user', 'package_id', 'user_id');
    }
}
