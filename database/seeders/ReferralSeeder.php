<?php

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\MembershipLevel;
use App\Models\ReferralsNew;

class ReferralSeeder extends Seeder
{
    public function run()
    {
        // Create users
        $users = User::factory(10)->create();

        // Create membership levels
        MembershipLevel::create([
            'level_name' => 'Gold',
            'monthly_fee' => 100,
            'percentage_in_level_1' => 10,
            'percentage_in_level_2' => 5,
            'percentage_in_level_3' => 2,
        ]);

        // Assign referrals
        foreach ($users as $key => $user) {
            if ($key > 0) {
                ReferralsNew::create([
                    'user_id' => $user->id,
                    'referrer_id' => $users[$key - 1]->id,
                ]);
            }
        }
    }
}

