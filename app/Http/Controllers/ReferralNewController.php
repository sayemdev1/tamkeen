<?php

namespace App\Http\Controllers;

use App\Models\MembershipLevel;
use App\Models\ReferralsNew;
use App\Models\ReferralProfitsNew;
use DB;
use Illuminate\Http\Request;
use Log;

class ReferralNewController extends Controller
{
    public function subscribe(Request $request, $membershipLevelId)
    {
        $user = auth()->user();
        $membershipLevel = MembershipLevel::findOrFail($membershipLevelId);
        $referrerId = $request->input('referrer_id');

        // Check if the user is already subscribed to the specified membership level
        $isAlreadySubscribed = ReferralsNew::where('user_id', $user->id)
            ->whereHas('membershipLevels', function ($query) use ($membershipLevelId) {
                $query->where('membership_levels.id', $membershipLevelId); // Qualified 'id'
            })
            ->exists();

        if ($isAlreadySubscribed) {
            return response()->json(['message' => 'Already subscribed to this membership level.'], 400);
        }

        // Validate that the referrer exists in the referral profits table for the same membership level
        if ($referrerId) {
            $isReferrerSubscribed = DB::table('membership_level_user')
                ->where('user_id', $referrerId)
                ->where('membership_level_id', $membershipLevelId)
                ->exists();

            if (!$isReferrerSubscribed) {
                return response()->json(['message' => 'The referrer is not subscribed to this membership level.'], 400);
            }
        }

        // Add user to referrals
        ReferralsNew::create([
            'user_id' => $user->id,
            'referrer_id' => $referrerId,
        ]);

        // Distribute profits
        $this->distributeReferralProfits($user->id, $membershipLevel);

        return response()->json(['message' => 'Subscription successful.'], 200);
    }


    private function distributeReferralProfits($userId, $membershipLevel)
    {
        $referrerId = ReferralsNew::where('user_id', $userId)->value('referrer_id');
        $level = 1;

        while ($referrerId && $level <= 3) {
            // Check if the referrer is subscribed to the membership level
            $isReferrerSubscribed = ReferralsNew::where('user_id', $referrerId)
            ->whereHas('membershipLevels', function ($query) use ($membershipLevel) {
                $query->where('membership_levels.id', $membershipLevel->id); // Qualified 'id'
            })
            ->exists();
        

            if (!$isReferrerSubscribed) {
                break; // Stop the chain if the referrer is not subscribed
            }

            // Calculate profit
            $profitPercentage = match ($level) {
                1 => $membershipLevel->percentage_in_level_1,
                2 => $membershipLevel->percentage_in_level_2,
                3 => $membershipLevel->percentage_in_level_3,
                default => 0,
            };

            $profit = ($profitPercentage / 100) * $membershipLevel->monthly_fee;

            // Save profit
            ReferralProfitsNew::create([
                'referrer_id' => $referrerId,
                'user_id' => $userId,
                'membership_level_id' => $membershipLevel->id,
                'profit' => $profit,
                'level' => $level,
            ]);

            // Get the next referrer
            $referrerId = ReferralsNew::where('user_id', $referrerId)->value('referrer_id');
            $level++;
        }
    }
    public function viewReferralTree($userId)
    {
        // Fetch direct referrals of the user
        $referralTree = ReferralsNew::where('referrer_id', $userId)
            ->with('user') // Include user details
            ->get();

        foreach ($referralTree as $referral) {
            // Add total profit earned by this referrer
            $referral->total_profit = ReferralProfitsNew::where('referrer_id', $referral->user_id)->sum('profit');
        }

        return response()->json([
            'user_id' => $userId,
            'referrals' => $referralTree,
        ]);
    }
    public function viewUserProfits($userId)
    {
        $totalProfit = ReferralProfitsNew::where('referrer_id', $userId)->sum('profit');

        return response()->json([
            'user_id' => $userId,
            'total_profit' => $totalProfit,
        ]);
    }

    public function viewAllUsersProfits()
    {
        $users = DB::table('users')
            ->leftJoin('referral_profits_new', 'users.id', '=', 'referral_profits_new.referrer_id')
            ->select('users.id', 'users.name', DB::raw('SUM(referral_profits_new.profit) as total_profit'))
            ->groupBy('users.id', 'users.name')
            ->orderBy('total_profit', 'desc')
            ->get();

        return response()->json($users);
    }


}

