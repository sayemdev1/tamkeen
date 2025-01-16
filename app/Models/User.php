<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'gender',
        'phone',
        'date_of_birth',
        'role_id',
        'referred_by',
        'balance',
        'image',
        'referral_code', // Add referral_code here
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'email_verified_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',

    ];

    /**
     * Get the identifier that will be stored in the JWT subject claim.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key-value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }



    public function assignRole($roleName)
    {
        $role = Role::where('name', $roleName)->first();
        if ($role) {
            $this->roles()->attach($role->id);
        }
    }

    // Method to get roles as array
    public function hasRole($roleName)
    {
        return $this->role && $this->role->name === $roleName;
    }


    public function role()
    {
        return $this->belongsTo(Role::class);
    }




    public function bankingDetails()
    {
        return $this->hasOne(BankingDetail::class);
    }


    public function membership_levels()
    {
        return $this->belongsToMany(MembershipLevel::class, 'membership_level_user', 'user_id', 'membership_level_id')->withPivot('is_subscribed', 'account', 'is_active', 'activated_until');
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function store()
    {
        return $this->hasOne(Store::class, 'owner_id', 'id');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }

    public function myDiscounts()
    {
        return $this->hasMany(Discount::class, 'user_id', 'id');
    }

    public function cart()
    {
        return $this->hasOne(Cart::class);
    }

    // Relationship: User can refer many users
    public function referrals()
    {
        return $this->hasMany(Referral::class, 'referrer_id');
    }

    public function referrer()
    {
        return $this->belongsTo(User::class, 'referred_by');
    }

    public function referralProfits()
    {
        return $this->hasMany(ReferralProfit::class, 'referrer_id');
    }


    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    // Generate a unique referral code
    public static function generateReferralCode($name = null)
    {
        $randomString = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 6));
        return $name ? strtoupper(substr($name, 0, 3)) . $randomString : $randomString;
    }

    // Automatically set a referral code on user creation if not provided
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            if (empty($user->referral_code)) {
                $user->referral_code = self::generateReferralCode($user->name);
            }
        });
    }

}
