<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReferralProfit extends Model
{
    use HasFactory;

    protected $fillable = [
        'referrer_id',
        'order_id',
        'profit',
        'level',
        'user_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relationship to get the referrer
    public function referrer()
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }
}
