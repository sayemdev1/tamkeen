<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackageUser extends Model
{
    ///////////  package is backet
    use HasFactory;

    protected $table = 'package_user';
    protected $fillable = [
        'package_id',
        'user_id',
        'price_of_package_before_coupon',
        'price_of_package_after_coupon',
        'coupon_id',
    ];

    public function package()
    {
        return $this->belongsTo(Package::class, 'package_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
