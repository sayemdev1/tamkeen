<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReferralsNew extends Model
{
    use HasFactory;

    protected $table = 'referrals_new';

    protected $fillable = [
        'user_id',
        'referrer_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function referrer()
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    public function membershipLevels()
    {
        return $this->belongsToMany(MembershipLevel::class, 'membership_level_user', 'user_id', 'membership_level_id')
            ->withPivot('is_subscribed');
    }
}
