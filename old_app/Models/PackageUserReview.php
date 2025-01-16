<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackageUserReview extends Model
{
    use HasFactory;

    protected $table = 'package_user_review';
    protected $fillable = [
        'package_id',
        'user_id',
        'review',
        'rating',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }
}
