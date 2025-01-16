<?php

namespace App\Http\Controllers;

use App\Models\PyramidReferral;
use App\Models\PyramidReferralProfit;
use App\Models\MembershipLevel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PyramidReferralController extends Controller
{
    public function subscribe(Request $request, $membershipLevelId)
    {
        $user = auth()->user();
        $membershipLevel = MembershipLevel::findOrFail($membershipLevelId);
        $referrerId = $request->input('referrer_id');

        // Check if the user is already subscribed to this membership level
        $isAlreadySubscribed = PyramidReferral::where('user_id', $user->id)
            ->where('membership_level_id', $membershipLevelId) // Include membership level in the check
            ->exists();

        if ($isAlreadySubscribed) {
            return response()->json(['message' => 'Already subscribed to this membership level.'], 400);
        }

        // Validate referrer subscription
        if ($referrerId) {
            $isReferrerSubscribed = PyramidReferralProfit::where('user_id', $referrerId)
                ->where('membership_level_id', $membershipLevelId)
                ->exists();

            if (!$isReferrerSubscribed) {
                return response()->json(['message' => 'Referrer is not subscribed to this membership level.'], 400);
            }
        }

        // Add user to pyramid referrals
        PyramidReferral::create([
            'user_id' => $user->id,
            'referrer_id' => $referrerId,
            'membership_level_id' => $membershipLevelId, // Include membership level
        ]);

        // Distribute profits
        $this->distributeReferralProfits($user->id, $membershipLevel);

        return response()->json(['message' => 'Subscription successful.'], 200);
    }

    private function distributeReferralProfits($userId, $membershipLevel)
    {
        $referrerId = PyramidReferral::where('user_id', $userId)
            ->where('membership_level_id', $membershipLevel->id)
            ->value('referrer_id');

        \Log::info("Starting profit distribution for user ID: {$userId}, Membership Level: {$membershipLevel->id}");

        $level = 1;
        $processedReferrers = []; // Track processed referrer IDs to avoid loops

        while ($referrerId && $level <= 3) {
            // Check if the referrer has already been processed
            if (in_array($referrerId, $processedReferrers)) {
                \Log::warning("Detected loop for Referrer ID: {$referrerId} at Level: {$level}. Breaking the loop.");
                break;
            }

            // Add referrer to processed list
            $processedReferrers[] = $referrerId;

            // Fetch the profit percentage for the current level
            $profitPercentage = match ($level) {
                1 => $membershipLevel->percentage_in_level_3,
                2 => $membershipLevel->percentage_in_level_2,
                3 => $membershipLevel->percentage_in_level_1,
                default => 0,
            };

            \Log::info("Level: {$level}, Referrer ID: {$referrerId}, Profit Percentage: {$profitPercentage}");

            // Calculate profit
            $profit = ($profitPercentage / 100) * $membershipLevel->monthly_fee;

            \Log::info("Calculated Profit: {$profit} for Referrer ID: {$referrerId}");

            // Save profit in pyramid_referral_profits table
            PyramidReferralProfit::create([
                'referrer_id' => $referrerId,
                'user_id' => $userId,
                'membership_level_id' => $membershipLevel->id,
                'profit' => $profit,
                'level' => $level,
            ]);

            \Log::info("Saved Profit Entry: Referrer ID: {$referrerId}, User ID: {$userId}, Level: {$level}, Profit: {$profit}");

            // Fetch the next referrer for the chain
            $referrerId = PyramidReferral::where('user_id', $referrerId)
                ->where('membership_level_id', $membershipLevel->id) // Match membership level
                ->value('referrer_id');

            \Log::info("Next Referrer ID: {$referrerId} for Level: {$level}");

            $level++;
        }

        \Log::info("Finished profit distribution for user ID: {$userId}, Membership Level: {$membershipLevel->id}");
    }



    public function viewReferralTree($userId)
    {
        $levels = [];
    
        // Fetch referral profits grouped by level
        $profitsByLevel = DB::table('pyramid_referral_profits')
            ->select('level', DB::raw('SUM(profit) as total_profit'))
            ->where('referrer_id', $userId)
            ->groupBy('level')
            ->orderBy('level')
            ->get();
    
        // Organize referrals by levels
        foreach ($profitsByLevel as $profit) {
            $levelReferrals = PyramidReferralProfit::where('referrer_id', $userId)
                ->where('level', $profit->level)
                ->with(['user', 'membershipLevel']) // Include referred user and membership level details
                ->get();
    
            $levels[] = [
                'level' => $profit->level,
                'referrals_count' => $levelReferrals->count(),
                'total_profit' => $profit->total_profit,
                'referrals' => $levelReferrals->map(function ($referral) {
                    return [
                        'id' => $referral->id,
                        'referrer_id' => $referral->referrer_id,
                        'user_id' => $referral->user_id,
                        'membership_level_id' => $referral->membership_level_id,
                        'profit' => $referral->profit,
                        'level' => $referral->level,
                        'user' => [
                            'id' => $referral->user->id,
                            'name' => $referral->user->name,
                            'email' => $referral->user->email,
                            'phone' => $referral->user->phone,
                            'referral_code' => $referral->user->referral_code,
                        ],
                        'membership_level' => $referral->membershipLevel ? [
                            'id' => $referral->membershipLevel->id,
                            'level_name' => $referral->membershipLevel->level_name,
                            'monthly_fee' => $referral->membershipLevel->monthly_fee,
                            'description' => $referral->membershipLevel->description,
                            'percentage_in_level_1' => $referral->membershipLevel->percentage_in_level_1,
                            'percentage_in_level_2' => $referral->membershipLevel->percentage_in_level_2,
                            'percentage_in_level_3' => $referral->membershipLevel->percentage_in_level_3,
                        ] : null,
                    ];
                }),
            ];
        }
    
        return response()->json([
            'user_id' => $userId,
            'referral_tree' => $levels,
        ]);
    }
    

    public function viewUserProfits($userId)
    {
        $totalProfit = PyramidReferralProfit::where('referrer_id', $userId)->sum('profit');

        return response()->json([
            'user_id' => $userId,
            'total_profit' => $totalProfit,
        ]);
    }

    public function viewUserMemberships($userId)
    {
        $userMemberships = DB::table('membership_level_user')
            ->where('user_id', $userId)
            ->join('membership_levels', 'membership_levels.id', '=', 'membership_level_user.membership_level_id')
            ->select('membership_levels.id', 'membership_levels.level_name', 'membership_levels.monthly_fee', 'membership_level_user.created_at as subscription_date')
            ->get();

        return response()->json([
            'user_id' => $userId,
            'memberships' => $userMemberships,
        ]);
    }

    public function viewAllUsersProfits()
    {
        $users = DB::table('users')
            ->leftJoin('pyramid_referral_profits', 'users.id', '=', 'pyramid_referral_profits.referrer_id')
            ->select('users.id', 'users.name', DB::raw('SUM(pyramid_referral_profits.profit) as total_profit'))
            ->groupBy('users.id', 'users.name')
            ->orderBy('total_profit', 'desc')
            ->get();

        return response()->json($users);
    }

    public function viewReferralLevels($userId)
    {
        $levels = [];
        $currentReferrals = PyramidReferral::where('referrer_id', $userId)->pluck('user_id');

        $level = 1;
        while ($currentReferrals->isNotEmpty()) {
            $levels[] = [
                'level' => $level,
                'referrals' => $currentReferrals,
            ];

            $currentReferrals = PyramidReferral::whereIn('referrer_id', $currentReferrals)->pluck('user_id');
            $level++;
        }

        return response()->json([
            'user_id' => $userId,
            'levels' => $levels,
        ]);
    }


    public function applyReferralCode(Request $request, $membershipLevelId)
    {
        $referralCode = $request->input('referral_code');

        // Find user by referral code
        $referrer = User::where('referral_code', $referralCode)->first();

        if (!$referrer) {
            return response()->json(['message' => 'Invalid referral code.'], 400);
        }

        // Check if the referrer is subscribed to the membership level
        $isReferrerSubscribed = PyramidReferral::where('user_id', $referrer->id)
            ->where('membership_level_id', $membershipLevelId)
            ->exists();

        if (!$isReferrerSubscribed) {
            return response()->json(['message' => 'Referrer is not subscribed to this membership level.'], 400);
        }

        // Calculate total profit from pyramid sell
        $totalProfit = PyramidReferralProfit::where('referrer_id', $referrer->id)
            ->sum('profit');

        return response()->json([
            'message' => 'Referral code is valid.',
            'referrer' => [
                'id' => $referrer->id,
                'name' => $referrer->name,
                'email' => $referrer->email,
                'phone' => $referrer->phone,
                'referral_code' => $referrer->referral_code,
                'total_profit' => $totalProfit,
            ],
        ]);
    }



}
