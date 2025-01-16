<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    use HasFactory;
    protected $fillable = ['store_name' , 'owner_id', 'location', 'type', 'working_hours','image','store_email','store_phone','trn'];


    protected $casts = [
        'review' => 'float',
    ];


    public function getReviewAttribute($value)
    {
        return number_format($value, 2, '.', '');
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id' , 'id');
    }

    public function shipments()
    {
        return $this->hasMany(Shipment::class);
    }

    public function companies()
    {
        return $this->belongsToMany(Company::class);
    }
    public function storeFiles()
    {
        return $this->hasMany(StoreFile::class,'store_id');
    }

    public function packages()
    {
        return $this->hasMany(Package::class,'store_id');
    }
}
