<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function login(LoginRequest $request)
    {
        // Validate incoming request data
        $validatedData = $request->validated();

        $user = User::where('email', $validatedData['email'])
            ->select('id', 'name', 'email', 'password', 'gender', 'date_of_birth','phone','role_id')->first();

        // If user exists, proceed with password check
        if ($user) {
            // Verify the password
            if (Hash::check($validatedData['password'], $user->password)) {
                // If password matches, generate and return access token along with user data

                return response()->json([
                    'token' => auth()->guard('api')->attempt($validatedData),
                    'user' => $user
                ], 200);
            } else {
                // If password doesn't match, return response indicating password mismatch
                return response()->json(['password' => 'Password mismatch'], 422);
            }
        } else {
            // If user not found, return response indicating user not found
            return response()->json(['message' => 'User not found'], 422);
        }
    }
}
