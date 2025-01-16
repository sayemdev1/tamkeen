<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReferralProfitsNew extends Model
{
    use HasFactory;

    protected $table = 'referral_profits_new';

    protected $fillable = [
        'referrer_id',
        'user_id',
        'membership_level_id',
        'profit',
        'level',
    ];

    public function referrer()
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function membershipLevel()
    {
        return $this->belongsTo(MembershipLevel::class, 'membership_level_id');
    }

    public function membershipLevels()
    {
        return $this->belongsToMany(MembershipLevel::class, 'membership_level_user', 'user_id', 'membership_level_id')
            ->withPivot('is_subscribed');
    }

}
