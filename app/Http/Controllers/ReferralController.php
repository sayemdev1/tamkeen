<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReferRequest;
use App\Jobs\SendReferralEmail;
use App\Models\invitation;
use App\Models\MembershipLevel;
use App\Models\Referral;
use App\Models\ReferralProfit;
use App\Models\User;
use Illuminate\Http\Request;
use App\Services\JwtService; // Import the JwtService
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail; 

class ReferralController extends Controller
{
    public function referUser(ReferRequest $request)
    {
        

        $referrer = auth()->user();

        $level = $this->calculateReferralLevel($referrer);

        // Check if the referrer has reached the referral limit for their level
        if ($level >= 4) {
            return response()->json(['message' => 'You cannot refer more than four levels deep.'], 403);
        }

        // Check if the referrer has an active subscription
        if (!($referrer->membership_levels()
        ->wherePivot('is_subscribed', true)
        ->wherePivot('is_active', true)
        ->first())) {
            return response()->json(['message' => 'You must be subscribed or active to a membership level.'], 403);
        }

        // Check if the referrer can refer more users
        $referralCount = $referrer->referrals()->where('level', $level)->count();
        if ($referralCount >= 3) {
            return response()->json(['message' => 'Referral limit reached.'], 403);
        }

        // Generate JWT token with referred_by_id and package_id
        $jwtService = new JwtService();
        
        $token = $jwtService->generateToken($referrer->id, $request->package_id, $level);

        // Send email with referral link
        $referralLink = 'https://tamkeen.center/signup?token=' . $token;
        $package_name = MembershipLevel::find($request->package_id)->level_name;
        $user_name = $referrer->name;

        if(invitation::where('email', $request->email)->exists()){
            return response()->json(['message' => 'User already referred.']);
        }
        invitation::create([
            'email' => $request->email,
            'inviter_id' => $referrer->id,
        ]);
        
        // SendReferralEmail::dispatch($request->email, $referralLink, $user_name, $package_name);

        return response()->json(['message' => 'User referred successfully.','referral_link' => $referralLink]);
    }


    private function calculateReferralLevel($user)
    {
        $level = 1;
        $currentUser = $user;

        // Traverse up the referral chain until there is no referrer or max level is reached
        while ($currentUser->referrer && $level < 4) {
            $currentUser = $currentUser->referrer;
            $level++;
        }

        return $level;
    }


    public function referralDetails()
    {
        $user = auth()->user();
        
       $level = $this->calculateReferralLevel($user);

   // Combined condition check for referral depth, subscription status, and referral limit
    $activeMembership = $user->membership_levels()
    ->wherePivot('is_subscribed', true)
    ->wherePivot('is_active', true)
    ->first();

     $referralCount = $user->referrals()->where('level', $level)->count();
        $is_can_make_refer = true;
     
    if ($level >= 4 || !$activeMembership || $referralCount >= 3) 
    {
        $is_can_make_refer = false;
        
    }
        // Count referred users (registered referrals)
        $referred_users_count = $user->referrals()->distinct('referee_id')->count();

        // Count invited users who haven't registered yet
        $invited_users_count = invitation::where('inviter_id', $user->id)
            ->whereNotIn('email', function ($query) {
                $query->select('email')->from('users'); // Assuming 'email' links invitations and users
            })
            ->count();
            $package_of_user = $user->membership_levels()->where('is_subscribed', true)->where('is_active', true)->first();
            $total_profits = ReferralProfit::where('referrer_id', $user->id)->where('membership_level_id', $package_of_user->id)->sum('profit');

        return response()->json([
            'referred_users_count' => $referred_users_count,
            'invited_users_count' => $invited_users_count,
            'package_id_of_user' =>$package_of_user->id,
            'total_profits_from_active_package' => $total_profits,
            'is_can_make_refer' => $is_can_make_refer
        ]);
    }


    public function getReferralsWithProfits($userId = null)
    {
        // Get the authenticated user
        $authUser = Auth::user();

        // Check if the authenticated user has the 'admin' or 'distributor' role
        if ($authUser && ($authUser->hasRole('admin') && $userId || $authUser->hasRole('distributor') && $userId)) {
            $user = User::find($userId);
        } 
        else {
            $user = $authUser;
        }

        // Proceed with fetching referrals and profits
        $referralTree = $this->getAllReferrals($user);

        return response()->json($referralTree);
    }
    private function getAllReferrals($user)
    {
         if (!$user || !$user->id) {
        return [
            'id_of_user' => null,
            'name' => $user ? $user->name : null,
            'active_package_id_of_user' => null,
            'referrals' => [],
            'referrers' => []
        ];
    }
        // Get the user's referrals
        $referrals = Referral::where('referrer_id', $user->id)->with('referee')->get();

       $referrers = Referral::where('referee_id', auth()->user()->id)->with('referrer')->get();
    

       $package_of_user =$user->membership_levels()
            ->where('is_subscribed', true)
            ->where('is_active', true)
            ->withPivot('activated_from', 'activated_until', 'membership_level_id')
            ->first();
        // Structure the data
        $referralData = [
            'id_of_user' => $user->id,
            'name' => $user->name,
            'active_package_id_of_user' => $package_of_user ? $package_of_user->pivot->membership_level_id : null,
            'referrals' => []
        ];
 

        foreach ($referrals as $referral) {
            $referee = User::find($referral->referee_id);
            
            if ($referee) {
                $activatedFrom = $referee->membership_levels()
                ->where('is_subscribed', true)
                ->where('is_active', true)
                ->withPivot('activated_from', 'activated_until', 'membership_level_id')
                ->first();
                $referralData['referrals'][] = [
                    'id_of_referee' => $referee->id,
                    'name' => $referee->name,
                    'profits_for_referrer' => ReferralProfit::where('referrer_id',auth()->user()->id)->where('user_id', $referee->id)->sum('profit'),
                    'is_subscribed_in_package' => $referee->membership_levels()->where('is_subscribed', true)->exists(),
                    'is_active_in_package' => $referee->membership_levels()->where('is_subscribed', true)->where('is_active', true)->exists(),
                    'activated_from' => $activatedFrom ? $activatedFrom->pivot->activated_from : null,
                    'activated_until' => $activatedFrom ? $activatedFrom->pivot->activated_until : null,
                    'referrals' => $this->getAllReferrals($referee)['referrals'] 
                ];
            }
        }
        return $referralData + ['referrers' => $referrers ];
    }


}
