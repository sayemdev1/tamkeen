<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Referral extends Model
{
    use HasFactory;

    protected $fillable = ['referrer_id', 'referee_id', 'package_id','level'];

    // Relationship: A referral belongs to a referrer
    public function referrer()
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    // Relationship: A referral belongs to a referee
    public function referee()
    {
        return $this->belongsTo(User::class, 'referee_id');
    }
    
    public function package()
    {
        return $this->belongsTo(MembershipLevel::class, 'package_id', 'id');
    }
}
