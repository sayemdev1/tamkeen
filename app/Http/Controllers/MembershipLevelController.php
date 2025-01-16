<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMemberShipRequest;
use App\Http\Requests\UpdateMemberShipRequest;
use App\Models\Coupon;
use App\Models\CouponPackageUser;
use App\Models\MembershipLevel;
use App\Models\Referral;
use App\Models\User;
use DB;
use Exception;
use Illuminate\Http\Request;
use App\Services\CouponService;
use Illuminate\Support\Facades\Storage;
use App\Models\ReferralProfit;
use Log;

class MembershipLevelController extends Controller
{
  
    public function index()
    {
        $membershipLevels = MembershipLevel::all();
        foreach ($membershipLevels as $membershipLevel) {
            $membershipLevel->users_count = $membershipLevel->users()->count();
        }
        return response()->json($membershipLevels);
    }

   public function store(StoreMemberShipRequest $request)
    {
        // Initialize data array with validated request data
        $data = $request->validated();

        // Check if a file is provided in the 'icon' field
        if ($request->hasFile('icon')) {
            // Store the uploaded file in the 'membership_icon' directory within 'public'
            $path = $request->file('icon')->store('membership_icon', 'public');
    
            // Save the path in the data array for storage in the database
            $data['icon'] = "storage/" . $path;
        }
    
        // Create the membership level record with the updated data
        $membershipLevel = MembershipLevel::create($data);
    
        // Return a JSON response with the newly created record
        return response()->json($membershipLevel, 201);
    }



    public function show(MembershipLevel $membershipLevel)
    {
        return response()->json($membershipLevel);
    }

    public function update(UpdateMemberShipRequest $request, MembershipLevel $membershipLevel)
    {
        // Initialize data array with validated request data
        $data = $request->validated();
    
        // Check if a new file is provided in the 'icon' field
        if ($request->hasFile('icon')) {
            // If the membership level already has an icon, delete the existing file
            if ($membershipLevel->icon && Storage::disk('public')->exists(str_replace('storage/', '', $membershipLevel->icon))) {
                Storage::disk('public')->delete(str_replace('storage/', '', $membershipLevel->icon));
            }
    
            // Store the new uploaded file in the 'membership_icon' directory within 'public'
            $path = $request->file('icon')->store('membership_icon', 'public');
            
            // Save the path in the data array for storage in the database
            $data['icon'] = "storage/" . $path;
        }
    
        // Update the membership level record with the new data
        $membershipLevel->update($data);
    
        // Return a JSON response with the updated record
        return response()->json($membershipLevel, 200);
    }

    public function destroy(MembershipLevel $membershipLevel)
    {
        $membershipLevel->delete();
        return response()->json(['message'=>'Membership level deleted successfully'], 200);
    }


    public function subscribe(MembershipLevel $membershipLevel, Request $request, CouponService $couponService)
    {
        $user = auth()->user();
        $membershipLevel_id = $membershipLevel->id;
        $couponCode = $request->input('coupon_code');
        $totalPrice = $membershipLevel->monthly_fee;

        // Check if the user is already subscribed to this membership level
        if ($user->membership_levels()->where('membership_level_id', $membershipLevel_id)->exists()) {
            return response()->json([
                'message' => 'You are already subscribed to this membership level.',
            ], 400);
        }

        try {
            // If a coupon code is provided, apply the coupon
            if ($couponCode) {
                $couponData = $couponService->applyCoupon($couponCode, $totalPrice);

                CouponPackageUser::create([
                    'coupon_id' => $couponData['coupon']->id,
                    'package_id' => $membershipLevel_id,
                    'user_id' => $user->id,
                    'price_of_package_after_coupon' => $couponData['discounted_price'],
                    'price_of_package_before_coupon' => $totalPrice,
                ]);

                $totalPrice = $couponData['discounted_price'];
            }

            // Attach membership level to the user
            $user->membership_levels()->attach($membershipLevel_id, ['is_subscribed' => true]);

            return response()->json([
                'message' => 'Subscription successful',
                'membership_level' => $membershipLevel->level_name,
                'total_price' => $totalPrice,
                'discount_applied' => $couponCode ? true : false,
                'discount_amount' => $couponCode ? $couponData['discount_amount'] : 0,
                
            ], 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function getDetails(MembershipLevel $membershipLevel)
    {
         $authUser = auth()->user();
        // if (!($authUser && ($authUser->hasRole('admin') ))) {
        //   return response()->json(['message' => 'Unauthorized'], 403);
        // }

        $monthlyFee = $membershipLevel->monthly_fee; // Assuming the membership level has a monthly fee

        $users = $membershipLevel->users()
            ->withPivot('account', 'is_subscribed', 'activated_until', 'activated_from', 'is_active')
            ->wherePivot('is_subscribed', true)
            ->get()
            ->map(function ($user) use ($monthlyFee) {
                $pivot = $user->pivot;
                $amount = 0;

                // Condition 1: If account has a value and is_active is false, use the account value as the amount
                if ($pivot->account > 0 && !$pivot->is_active) {
                    $amount = $pivot->account;
                }

                // Condition 2 and 3: If there are valid activated_from and activated_until dates
                elseif ($pivot->activated_from && $pivot->activated_until) {
                    $activatedFrom = \Carbon\Carbon::parse($pivot->activated_from);
                    $activatedUntil = \Carbon\Carbon::parse($pivot->activated_until);

                    // Calculate the number of full months between the dates
                    $months = $activatedUntil->diffInMonths($activatedFrom);

                    // Check if the months are within the same year and month
                    if (
                        $activatedFrom->year != $activatedUntil->year ||
                        $activatedFrom->month != $activatedUntil->month
                    ) {
                        $months++; // Count both months as full if different
                    } else {
                        $months = 1; // If it's within the same month, count it as 1 month
                    }

                    // If account is 0 (Condition 2), use the months * monthly fee
                    if ($pivot->account == 0) {
                        $amount = $months * $monthlyFee;
                    }
                    // If account has a value (Condition 3), add it to the calculated months * monthly fee
                    else {
                        $amount = ($months * $monthlyFee) + $pivot->account;
                    }
                }

                // Add the calculated amount to the user object for the response
                $user->amount_paid = $amount;

                return $user;
            });

            foreach ($users as $user) {

          $profits =  ReferralProfit::where('referrer_id', $user->id)->where('membership_level_id', $membershipLevel->id)->sum('profit');

          $user->referral_profit = $profits;
            }
     

        return response()->json($users);
    }


    

    public function processReferralSubscription(MembershipLevel $membershipLevel, Request $request)
    {
        $user = auth()->user();
        $membershipLevelId = $membershipLevel->id;
        $totalPrice = $membershipLevel->monthly_fee;
    
        // Validate the request to ensure `referrer_id` is provided and valid
        $validatedData = $request->validate([
            'referrer_id' => 'nullable|exists:users,id',
        ]);
    
        // Retrieve the referrer ID from the request
        $referrerId = $validatedData['referrer_id'] ?? null;
    
        // Check if the user is already subscribed to this membership level
        if ($user->membership_levels()->where('membership_level_id', $membershipLevelId)->exists()) {
            return response()->json([
                'message' => 'You are already subscribed to this membership level.',
            ], 400);
        }
    
        try {
            // Attach membership level to the user with the provided referrer ID
            $user->membership_levels()->attach($membershipLevelId, [
                'is_subscribed' => true,
                'referrer_id' => $referrerId,
            ]);
    
            // Retrieve the ID of the newly created pivot table entry
            $membershipLevelUserId = DB::table('membership_level_user')
                ->where('user_id', $user->id)
                ->where('membership_level_id', $membershipLevelId)
                ->orderBy('created_at', 'desc') // Ensure the most recent entry is retrieved
                ->value('id');
    
            // Calculate and distribute referral profits dynamically
            if ($referrerId) {
                Log::info('Referrer ID', ['referrer' => $referrerId]);
                $this->distributeReferralProfits(
                    $user->id,
                    $referrerId,
                    $membershipLevel->id,
                    $totalPrice,
                    $membershipLevelUserId
                );
            }
    
            return response()->json([
                'message' => 'Subscription successful',
                'membership_level' => $membershipLevel->level_name,
                'total_price' => $totalPrice,
            ], 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
    
    
private function getReferrerId($user)
{
    $referral = Referral::where('referee_id', $user->id)->first();
    return $referral ? $referral->referrer_id : null;
}

private function distributeReferralProfits($userId, $currentReferrerId, $membershipLevelId, $price, $membershipLevelUserId)
{
    $level = 1;

    while ($currentReferrerId && $level <= 3) {
        // Fetch the membership level details
        $membershipLevel = DB::table('membership_levels')->where('id', $membershipLevelId)->first();

        if (!$membershipLevel) {
            Log::error('Membership level not found', ['membership_level_id' => $membershipLevelId]);
            break;
        }

        // Get the profit percentage for the current level
        $profitPercentage = match ($level) {
            1 => $membershipLevel->percentage_in_level_1 ?? 0,
            2 => $membershipLevel->percentage_in_level_2 ?? 0,
            3 => $membershipLevel->percentage_in_level_3 ?? 0,
            default => 0,
        };

        // Calculate profit
        $profit = ($profitPercentage / 100) * $price;

        // Save profit to the referral_profits table
        DB::table('referral_profits')->insert([
            'referrer_id' => $currentReferrerId,
            'user_id' => $userId,
            'membership_level_id' => $membershipLevel->id,
            'membership_level_user_id' => $membershipLevelUserId,
            'profit' => $profit,
            'level' => $level,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Move to the next referrer in the chain
        $currentReferrerId = DB::table('membership_level_user')
            ->where('user_id', $currentReferrerId)
            ->where('membership_level_id', $membershipLevelId)
            ->value('referrer_id');

        $level++;
    }
}


}
