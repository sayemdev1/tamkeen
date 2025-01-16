<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class CheckMembershipStatus extends Command
{
    protected $signature = 'membership:check-status';

    public function handle()
    {
        $users = User::with('membership_levels')->get();

        foreach ($users as $user) {
            $membership = $user->membership_levels()->wherePivot('is_subscribed', true)->first();

            if ($membership) {
                $price_of_membership = $membership->monthly_fee;
                $months_to_activate = floor($membership->account / $price_of_membership);

                // If months_to_activate is 0 or less, deactivate membership
                if ($months_to_activate < 1) {
                    $membership->update(['is_active' => false]);
                }
            }
        }

        $this->info('Membership statuses updated.');
    }
}
