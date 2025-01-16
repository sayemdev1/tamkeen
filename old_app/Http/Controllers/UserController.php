<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\EditProfileRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
 
    /////for admin
    public function index()
    {
         $users = User::select('name','email', 'gender', 'date_of_birth','phone','id','role_id')->get();
    
         return response()->json([
            'message' => 'Users fetched successfully',
            'users' => UserResource::collection($users)
         ]);
        
    }
   

    public function show(User $user)
    {

        return response()->json(['user' =>new UserResource($user)]);
    }


    public function update(UpdateUserRequest $request, User $user)
    {
        $user->update($request->validated());

        
        
        return response()->json(['message' => 'User updated successfully', 'user' => new UserResource($user)], 200);
    }

   
    public function destroy(User $user)
    {
        $user->delete();
        return response()->json(['message' => 'User deleted successfully']);
    }


    ////// for user 

    public function showForUser()
    {
        $user = auth()->id();
        $membershipLevel = null;
        if(auth()->user()->referrer){
            $membershipLevel = auth()->user()->referrer->membership_levels()->where('is_active', true)->first();
        }
        
       return response(['my_profile' => new UserResource(User::find($user)), "referring_id" => $membershipLevel ? $membershipLevel->id : null]);
    }

    public function updateForUser(UpdateUserRequest $request)
    {
        $user = auth()->user();
        $user->update($request->validated());
        return response(['message' => 'User updated successfully', 'user' => new UserResource($user)], 200);
    }

    public function editProfile(EditProfileRequest $request)
    {
        $user = auth()->user();
       

        $user->update([
            $request->validated()
        ]);
        return response(['message' => 'Profile updated successfully', 'user' => $user], 200);
    }
    public function changePassword(ChangePasswordRequest $request)
    {
        $user = Auth::user();

        // Check if the provided current password matches the stored password
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['error' => 'The current password is incorrect.'], 403);
        }

        // Update with the new password
        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json(['success' => 'Password updated successfully.']);
    }

}
