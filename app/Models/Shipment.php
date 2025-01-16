<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shipment extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'vehicle_id',
        'store_id',
        'shipment_type',
        'starting_route',
        'ending_route',
        'arrived_date',
        'status'
    ];

    // A shipment belongs to a company
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
    // A shipment belongs to a vehicle
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}
