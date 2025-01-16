<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Str;

class ForgotPasswordController extends Controller
{
    /**
     * Send Reset OTP to the user's email.
     */
    public function sendResetOtp(Request $request)
{
    // Validate that the email exists in the users table
    $request->validate([
        'email' => 'required|email|exists:users,email',
    ]);

    // Find the user by email
    $user = User::where('email', $request->email)->first();

    // Generate a random OTP
    $otp = rand(100000, 999999);

    // Update the user's record with the OTP and expiry time
    $user->reset_otp = $otp;
    $user->otp_expires_at = now()->addMinutes(10); // OTP valid for 10 minutes
    $user->save();

    // Send the OTP via email
    $details = [
        'title' => 'Password Reset OTP',
        'body' => "Your OTP for password reset is: $otp. This OTP will expire in 10 minutes.",
    ];

    Mail::to($user->email)->send(new \App\Mail\SendOTPMail($details));

    return response()->json(['message' => 'OTP has been sent to your email!'], 200);
}


    /**
     * Reset the user's password using OTP.
     */
    public function resetPassword(Request $request)
    {
       $request= $request->validate([
            'email' => 'required|email|exists:users,email',
            'otp' => 'required|integer',
            'password' => 'required|string|confirmed|min:8',
        ]);

        $user = User::where('email', $request['email'])->first();

$otp=$request['otp'];
        if ($user->reset_otp !=  $otp) {
            return response()->json(['message' => 'Invalid OTP.'], 400);
        }
        
        if($user->otp_expires_at < now()){
            return response()->json(['message' => 'Expired OTP.'], 400);
        }

        // Reset password
        $user->password = Hash::make($request['password']);
        $user->reset_otp = null;
        $user->otp_expires_at = null;
        $user->save();

        return response()->json(['message' => 'Password reset successfully!']);
    }
}
