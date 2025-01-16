<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CouponPackageUser extends Model
{
    ////////package is membership
    use HasFactory;

    protected $table = 'coupon_package_user';

    protected $fillable = [
        'coupon_id',
        'package_id',
        'user_id',
        'price_of_package_before_coupon',
        'price_of_package_after_coupon',
    ];

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }
    public function package()
    {
        return $this->belongsTo(MembershipLevel::class, 'package_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
