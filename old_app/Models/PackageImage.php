<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackageImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'image',
        'package_id',
    ];

    public function package()
    {
        return $this->belongsTo(Package::class);
    }
}
