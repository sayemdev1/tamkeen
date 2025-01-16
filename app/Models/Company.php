<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'email', 'phone', 'address', 'logo', 'enabled'];

    // A company can have many shipments
    public function shipments()
    {
        return $this->hasMany(Shipment::class);
    }
    public function stores()
    {
        return $this->belongsToMany(Store::class);
    }
}
