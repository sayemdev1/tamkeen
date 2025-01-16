<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PyramidReferralProfit extends Model
{
    use HasFactory;

    protected $table = 'pyramid_referral_profits';

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
}
