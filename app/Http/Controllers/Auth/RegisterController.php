<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\VendorRequest;
use App\Models\ChatGroup;
use App\Models\ChatGroupMember;
use App\Models\Referral;
use App\Models\Role;
use App\Models\Store;
use App\Models\StoreFile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tymon\JWTAuth\Facades\JWTAuth;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;


class RegisterController extends Controller
{
    public function register(RegisterRequest $request)
    {
        // Validate incoming request data
        $validatedData = $request->validated();

        // Hash the password before saving it to the database for security
        $validatedData['password'] = Hash::make($validatedData['password']);

         $role = Role::where('name', 'user')->first();

 
        $validatedData['role_id'] = $role->id;

        // Get the token from the request
        $token = $request->query('token');

        // Create the user and handle referral logic if the token is provided
        $user = $this->createUser($validatedData, $token);

        // Generate a JWT token for the newly created user
        $jwt_token = JWTAuth::fromUser($user);

        return response()->json([
            'message' => 'User successfully registered',
            'token' => $jwt_token,
            'user' => $user,
        ], 201);
    }

    private function createUser(array $validatedData, ?string $token)
    {
        if ($token) {
            try {
                // Decode the token using the correct method
                $decoded = JWT::decode($token, new Key(config('app.jwt_secret'), 'HS256'));

                // Assign the referrer ID from the decoded token
                $validatedData['referred_by'] = $decoded->referred_by_id;

                // Create a new user in the database
                $user = User::create($validatedData);

                // Create a new referral record
                Referral::create([
                    'referrer_id' => $decoded->referred_by_id,
                    'referee_id' => $user->id,
                    'package_id' => $decoded->package_id,
                    'level' => $decoded->level, // Determine the referral level
                ]);

              
                $new_group = ChatGroup::create([
                    'name' => 'chat',
                    'created_by' => $decoded->referred_by_id
                ]);
                ChatGroupMember::create([
                    'chat_group_id' => $new_group->id,
                    'user_id' => $user->id
                ]);
                ChatGroupMember::create([
                    'chat_group_id' => $new_group->id,
                    'user_id' => $decoded->referred_by_id
                ]); 
          
                return $user;
            } catch (\Exception $e) {
                return response()->json(['error' => 'Invalid token: ' . $e->getMessage()], 401);
            }
        }

        // Create a new user in the database if no token is provided
        return User::create($validatedData);
    }



    public function joinAsVendor(VendorRequest $request)
    {
        // Validate incoming request data
        $store_data = $request->validated();

        $role = Role::where('name', 'distributor')->first();

        $store_data['role_id'] = $role->id;

        // Hash the password
        $store_data['password'] = Hash::make($store_data['password']);

        // Create the user
        $user = User::create([
            'name' => $store_data['name'],
            'email' => $store_data['store_email'],
            'password' => $store_data['password'],
            'role_id' => $store_data['role_id'],
        ]);

        // Generate JWT token for the user
        $token = JWTAuth::fromUser($user);

        // Prepare store data
        $store_data['owner_id'] = $user->id;

        // Create the store
        $store = Store::create($store_data);

        // Handle file uploads if provided
        if ($request->has('legal_files')) {
            foreach ($request->legal_files as $base64File) {
                // Remove base64 prefix if present
                $base64File = preg_replace('/^data:\w+\/\w+;base64,/', '', $base64File);

                // Decode the base64 file data
                $fileData = base64_decode($base64File);

                // Determine the MIME type and file extension
                $fileType = finfo_buffer(finfo_open(), $fileData, FILEINFO_MIME_TYPE);
                $extension = explode('/', $fileType)[1] ?? 'bin'; // Default to 'bin' if no extension

                // Generate a unique file name and path
                $fileName = uniqid() . '.' . $extension;
                $filePath = "legal_files/{$fileName}";

                // Store the file in 'public/legal_files'
                Storage::disk('public')->put($filePath, $fileData);

                // Save details in the store_files table
                StoreFile::create([
                    'store_id' => $store->id, // Associate the file with the store
                    'file_name' => $fileName, // Unique generated file name
                    'file_path' =>"storage/" .$filePath, // Stored file path
                    'file_type' => $fileType // MIME type of the file
                ]);
            }
        }


        // Return response
        return response()->json([
            'message' => 'Vendor registered successfully.',
            'token' => $token,
            'user' => $user,
            'store' => $store
        ], 201);
    }

}
