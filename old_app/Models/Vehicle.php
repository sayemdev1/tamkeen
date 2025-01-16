<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = ['vehicle_number', 'driver_name', 'vehicle_type'];

    // A vehicle can have many shipments
    public function shipments()
    {
        return $this->hasMany(Shipment::class);
    }
}
