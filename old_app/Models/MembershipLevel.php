<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MembershipLevel extends Model
{
    use HasFactory;

    protected $fillable = ['level_name', 'monthly_fee', 'description','condition_1','condition_2','icon', 'percentage_in_level_1', 'percentage_in_level_2','percentage_in_level_3', 'color'];

    public function users()
    {
        return $this->belongsToMany(User::class, 'membership_level_user', 'membership_level_id', 'user_id');
    }

    public function referrals()
    {
        return $this->hasMany(Referral::class, 'package_id', 'id');
    }

}
